<?php

namespace App\Jobs;

use App\Enums\CarProviderEnum;
use App\Exceptions\CarProcessIgnoreException;
use App\Models\Car;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

abstract class CarProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $brand,
        public string $model,
        public $adResult,
    ) {
    }

    abstract public function getProvider(): CarProviderEnum;

    abstract public function onCarSaved(Car $car): void;

    public function handle(): void
    {
        try {

            //$this->log('Starting');

            $data = $this->getAdData();

            //$this->log('Data obtained');

            $this->validate($data);

            //$this->log('Data validated');

            $data['active'] = true;

            $car = Car::updateOrCreate(
                [
                    'provider' => $data['provider'],
                    'provider_id' => $data['provider_id']
                ],
                $data
            );

            $this->log('Car saved: #'.$car->id);

            $this->onCarSaved($car);

        } catch (\Exception $e) {

            if ($e instanceof CarProcessIgnoreException) {
                $this->log('Ignored: '.$e->getMessage(), 'warning');
                return;

            } else {

                $identifier = is_string($this->adResult) ? $this->adResult : json_encode($this->adResult);

                $this->log('Failed: '.$identifier, 'error');
            }

            throw $e;
        }
    }

    private function getAdData(): array
    {
        $provider = $this->getProvider();

        $service = $provider->getSyncService();

        $data = $service->getAdData($this->brand, $this->model, $this->adResult);

        return $data;
    }

    private function validate(array $data): void
    {
        $maxYear = date('Y') + 1;

        $maxPrice = 500_000;

        $maxOdometer = 300_000;

        $rules = [
            'brand' => ['required', 'string'],
            'model' => ['required', 'string'],
            'version' => ['nullable', 'string'],
            'year' => ['required', 'integer', 'min:1960', 'max:'.$maxYear],
            'year_model' => ['nullable', 'integer', 'min:1960', 'max:'.$maxYear],
            'price' => ['required', 'numeric', 'min:0', 'max:'.$maxPrice],
            'odometer' => ['required', 'integer', 'min:0', 'max:'.$maxOdometer],
            'state' => ['required', 'string', 'max:2'],
            'city' => ['required', 'string'],
            'provider' => ['required', 'string', Rule::in(CarProviderEnum::getValues())],
            'provider_id' => ['required', 'string'],
            'provider_updated_at' => ['required', 'date_format:Y-m-d H:i:s'],
            'provider_url' => ['nullable', 'string'],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {

            $errors = $validator->errors()->all();

            throw new CarProcessIgnoreException(implode('; ', $errors));
        }
    }

    private function log(string $message, string $channel = 'debug'): void
    {
        $provider = $this->getProvider();

        $logMessage = sprintf(
            '[car-process][%s][%s][%s] %s',
            $provider->value,
            $this->brand,
            $this->model,
            $message
        );

        Log::$channel($logMessage);
    }
}
