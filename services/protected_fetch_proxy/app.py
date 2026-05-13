"""Local FastAPI service for fetching allowlisted protected URLs.

Run this service bound to 127.0.0.1. It is not an open proxy: it only accepts
GET requests to explicit allowlisted hosts and requires a shared local token.
"""

from __future__ import annotations

import json
import os
import time
from datetime import UTC, datetime
from pathlib import Path
from typing import Any
from urllib.parse import urlparse

from dotenv import load_dotenv
from fastapi import FastAPI, Header, HTTPException
from pydantic import BaseModel, Field

from .clients import FetchError, FetchResult, fetch_with_curl_cffi, fetch_with_curl_cffi_proxy

load_dotenv()
load_dotenv(Path(__file__).resolve().parent / ".env")

DEFAULT_ALLOWED_HOSTS = "www.olx.com.br,olx.com.br,www.webmotors.com.br,webmotors.com.br"
DEFAULT_ALLOWED_HEADERS = {
    "accept",
    "accept-language",
    "cache-control",
    "origin",
    "pragma",
    "priority",
    "referer",
    "sec-ch-ua",
    "sec-ch-ua-mobile",
    "sec-ch-ua-platform",
    "sec-fetch-dest",
    "sec-fetch-mode",
    "sec-fetch-site",
    "sec-fetch-user",
    "upgrade-insecure-requests",
    "user-agent",
}

app = FastAPI(title="Protected Fetch Proxy", version="0.1.0")
_LAST_REQUEST_AT = 0.0
_NEXT_PROXY_INDEX = 0


class FetchRequest(BaseModel):
    url: str = Field(..., min_length=8)
    headers: dict[str, str] = Field(default_factory=dict)
    timeout_seconds: float = Field(default=30, ge=1, le=120)


class FetchResponse(BaseModel):
    status_code: int
    content_type: str
    final_url: str
    fetched_at: str
    body: Any
    body_is_json: bool


@app.get("/health")
def health() -> dict[str, Any]:
    return {
        "ok": True,
        "allowed_hosts": sorted(allowed_hosts()),
        "upstream_proxy_count": len(upstream_proxy_urls()),
        "min_interval_seconds": min_interval_seconds(),
    }


@app.post("/fetch", response_model=FetchResponse)
def fetch(request: FetchRequest, x_proxy_token: str | None = Header(default=None)) -> FetchResponse:
    require_token(x_proxy_token)
    validate_url(request.url)
    throttle()
    headers = sanitize_headers(request.headers)

    try:
        result = fetch_url(request.url, headers=headers, timeout_seconds=request.timeout_seconds)
    except FetchError as exc:
        raise HTTPException(status_code=500, detail=str(exc)) from exc

    content_type = result.headers.get("content-type", "")
    body, body_is_json = parse_body(result.text, content_type)

    return FetchResponse(
        status_code=result.status_code,
        content_type=content_type,
        final_url=result.final_url,
        fetched_at=datetime.now(UTC).isoformat(timespec="seconds"),
        body=body,
        body_is_json=body_is_json,
    )


def require_token(provided_token: str | None) -> None:
    expected = os.getenv("PROTECTED_FETCH_PROXY_TOKEN")
    if not expected:
        raise HTTPException(status_code=500, detail="PROTECTED_FETCH_PROXY_TOKEN is not configured")
    if provided_token != expected:
        raise HTTPException(status_code=401, detail="invalid token")


def allowed_hosts() -> set[str]:
    raw = os.getenv("PROTECTED_FETCH_ALLOWED_HOSTS", DEFAULT_ALLOWED_HOSTS)
    return {host.strip().lower() for host in raw.split(",") if host.strip()}


def upstream_proxy_urls() -> list[str]:
    raw = os.getenv("PROTECTED_FETCH_UPSTREAM_PROXY_URLS") or os.getenv("PROTECTED_FETCH_UPSTREAM_PROXY_URL", "")
    return [url.strip() for url in raw.split(",") if url.strip()]


def retry_statuses() -> set[int]:
    raw = os.getenv("PROTECTED_FETCH_UPSTREAM_PROXY_RETRY_STATUSES", "403,429")
    statuses: set[int] = set()
    for value in raw.split(","):
        try:
            statuses.add(int(value.strip()))
        except ValueError:
            continue
    return statuses


def fetch_url(url: str, headers: dict[str, str], timeout_seconds: float) -> FetchResult:
    proxies = upstream_proxy_urls_for_request()
    if not proxies:
        return fetch_with_curl_cffi(url, headers=headers, timeout_seconds=timeout_seconds)

    retryable = retry_statuses()
    last_result: FetchResult | None = None
    last_error: FetchError | None = None

    for proxy_url in proxies:
        try:
            result = fetch_with_curl_cffi_proxy(
                url,
                headers=headers,
                timeout_seconds=timeout_seconds,
                proxy_url=proxy_url,
            )
        except FetchError as exc:
            last_error = exc
            continue

        last_result = result
        if result.status_code not in retryable:
            return result

        retry_sleep()

    if last_result is not None:
        return last_result
    if last_error is not None:
        raise last_error

    return fetch_with_curl_cffi(url, headers=headers, timeout_seconds=timeout_seconds)


def upstream_proxy_urls_for_request() -> list[str]:
    global _NEXT_PROXY_INDEX

    proxies = upstream_proxy_urls()
    if len(proxies) <= 1:
        return proxies

    start = _NEXT_PROXY_INDEX % len(proxies)
    _NEXT_PROXY_INDEX += 1

    return proxies[start:] + proxies[:start]


def validate_url(url: str) -> None:
    parsed = urlparse(url)
    if parsed.scheme != "https":
        raise HTTPException(status_code=400, detail="only https is allowed")

    host = (parsed.hostname or "").lower()
    if host not in allowed_hosts():
        raise HTTPException(status_code=403, detail=f"host is not allowed: {host}")

    if parsed.username or parsed.password:
        raise HTTPException(status_code=400, detail="credentials in URL are not allowed")


def sanitize_headers(headers: dict[str, str]) -> dict[str, str]:
    out: dict[str, str] = {}
    max_value_len = int(os.getenv("PROTECTED_FETCH_MAX_HEADER_VALUE_LENGTH", "4096"))

    for key, value in headers.items():
        normalized = key.strip().lower()
        if normalized not in DEFAULT_ALLOWED_HEADERS:
            continue
        if not isinstance(value, str):
            continue
        out[normalized] = value[:max_value_len]

    return out


def throttle() -> None:
    global _LAST_REQUEST_AT

    min_interval = min_interval_seconds()
    elapsed = time.monotonic() - _LAST_REQUEST_AT
    if elapsed < min_interval:
        time.sleep(min_interval - elapsed)
    _LAST_REQUEST_AT = time.monotonic()


def min_interval_seconds() -> float:
    return float(os.getenv("PROTECTED_FETCH_MIN_INTERVAL_SECONDS", "1.0"))


def retry_sleep() -> None:
    seconds = float(os.getenv("PROTECTED_FETCH_UPSTREAM_PROXY_RETRY_SLEEP_SECONDS", "1.0"))
    if seconds > 0:
        time.sleep(seconds)


def parse_body(text: str, content_type: str) -> tuple[Any, bool]:
    max_body_bytes = int(os.getenv("PROTECTED_FETCH_MAX_BODY_BYTES", str(8 * 1024 * 1024)))
    if len(text.encode("utf-8", errors="ignore")) > max_body_bytes:
        raise HTTPException(status_code=502, detail="response exceeded body size limit")

    if "json" in content_type.lower():
        try:
            return json.loads(text), True
        except json.JSONDecodeError:
            return text, False

    return text, False
