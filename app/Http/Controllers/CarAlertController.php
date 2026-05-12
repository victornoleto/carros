<?php

namespace App\Http\Controllers;

use App\Models\CarAlert;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CarAlertController extends Controller
{
    public function index(Request $request): View
    {
        $alerts = $request->user()
            ->carAlerts()
            ->latest()
            ->paginate(20);

        return view('alerts.index', compact('alerts'));
    }

    public function create(Request $request): View
    {
        $filters = $request->query();

        return view('alerts.create', compact('filters'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'filters' => ['array'],
        ]);

        $request->user()->carAlerts()->create([
            'name' => $data['name'],
            'filters' => array_filter($data['filters'] ?? [], fn ($value) => filled($value)),
            'active' => true,
        ]);

        return redirect()->route('alerts.index');
    }

    public function destroy(Request $request, CarAlert $alert): RedirectResponse
    {
        abort_unless($alert->user_id === $request->user()->id, 403);

        $alert->delete();

        return redirect()->route('alerts.index');
    }
}
