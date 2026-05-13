<?php

namespace App\View\Components;

use App\Models\Car;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;
use Throwable;

class Filters extends Component
{
    public array $states;

    public array $cities;

    public array $models;

    public int $count;

    public int $total;

    /**
     * Create a new component instance.
     */
    public function __construct(int $count = 0, int $total = 0)
    {
        $this->count = $count;
        $this->total = $total;

        try {
            $this->states = array_column(
                DB::select('select distinct state from cars order by state'),
                'state'
            );

            $this->cities = Car::query()
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
                        'text' => $item->city.'/'.$item->state,
                    ];
                })
                ->toArray();

            $this->models = Car::query()
                ->groupBy('brand', 'model')
                ->select('brand', 'model')
                ->orderBy('brand')
                ->orderBy('model')
                ->get()
                ->map(function ($item) {
                    return [
                        'brand' => $item->brand,
                        'name' => $item->model,
                        'text' => $item->brand.' '.$item->model,
                    ];
                })
                ->toArray();
        } catch (Throwable $exception) {
            report($exception);

            $this->states = [];
            $this->cities = [];
            $this->models = [];
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.filters');
    }
}
