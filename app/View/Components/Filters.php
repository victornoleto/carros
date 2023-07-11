<?php

namespace App\View\Components;

use App\Models\OlxCar;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;

class Filters extends Component
{
    public array $states;
    public array $cities;
    public array $models;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->states = array_column(
            DB::select('select distinct state from olx_cars order by state'),
            'state'
        );

        $this->cities = OlxCar::query()
            ->groupBy('state', 'city')
            ->select('state', 'city')
            ->orderBy('state')
            ->orderBy('city')
            ->when(request()->states, function ($query) {
                $query->whereIn('state', request()->get('states'));
            })
            ->get()
                ->map(function ($item) {
                    return [
                        'state' => $item->state,
                        'name' => $item->city,
                        'text' => $item->city . '/' . $item->state
                    ];
                })
                ->toArray();

        $this->models = OlxCar::query()
            ->groupBy('brand', 'model')
            ->select('brand', 'model')
            ->orderBy('brand')
            ->orderBy('model')
            ->get()
            ->map(function ($item) {
                return [
                    'brand' => $item->brand,
                    'name' => $item->model,
                    'text' => $item->brand . ' ' . $item->model
                ];
            })
            ->toArray();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.filters');
    }
}
