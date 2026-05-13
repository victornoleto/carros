"""HTTP clients with browser-like TLS fingerprinting."""

from __future__ import annotations

from dataclasses import dataclass


@dataclass(frozen=True)
class FetchResult:
    status_code: int
    headers: dict[str, str]
    text: str
    final_url: str


class FetchError(RuntimeError):
    """Fetch failure with enough context for the API response."""


def fetch_with_curl_cffi(url: str, headers: dict[str, str], timeout_seconds: float) -> FetchResult:
    """Run a GET request using curl_cffi browser impersonation."""
    try:
        from curl_cffi import requests as curl_requests
    except ImportError as exc:
        raise FetchError("curl_cffi is not installed") from exc

    response = curl_requests.get(
        url,
        headers=headers,
        timeout=timeout_seconds,
        impersonate="chrome",
        allow_redirects=True,
    )

    return FetchResult(
        status_code=response.status_code,
        headers={str(k).lower(): str(v) for k, v in response.headers.items()},
        text=response.text,
        final_url=response.url,
    )


def fetch_with_curl_cffi_proxy(
    url: str,
    headers: dict[str, str],
    timeout_seconds: float,
    proxy_url: str,
) -> FetchResult:
    """Run a GET request using curl_cffi through an upstream proxy."""
    try:
        from curl_cffi import requests as curl_requests
    except ImportError as exc:
        raise FetchError("curl_cffi is not installed") from exc

    response = curl_requests.get(
        url,
        headers=headers,
        timeout=timeout_seconds,
        impersonate="chrome",
        allow_redirects=True,
        proxies={
            "http": proxy_url,
            "https": proxy_url,
        },
    )

    return FetchResult(
        status_code=response.status_code,
        headers={str(k).lower(): str(v) for k, v in response.headers.items()},
        text=response.text,
        final_url=response.url,
    )
