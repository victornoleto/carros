<?php

namespace App\Jobs;

use App\Models\Car;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWebmotorsResultJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $data)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        try {

            $odometer = $this->data['Specification']['Odometer'];
            $price = $this->data['Prices']['Price'];

            $odometer = ceil($odometer / 1000) * 1000;
            $price = ceil($price / 1000) * 1000;

            $car = Car::updateOrCreate(
                [
                    'webmotors_id' => $this->data['UniqueId']
                ],
                [
                    'brand' => $this->data['Specification']['Make']['Value'],
                    'model' => $this->data['Specification']['Model']['Value'],
                    'version' => $this->data['Specification']['Version']['Value'],
                    'year_fabrication' => $this->data['Specification']['YearFabrication'],
                    'year_model' => $this->data['Specification']['YearModel'],
                    'price' => $price,
                    'odometer' => $odometer,
                    'state' => $this->data['Seller']['State'],
                    'city' => $this->data['Seller']['City'],
                ]
            );

            Log::debug("[SYNC][{$car->brand}][{$car->model}][{$car->id}] Car saved");

        } catch (\Exception $e) {
            Log::error("[SYNC][{$this->data['UniqueId']}] ".$e->getMessage());
        }
    }
}
