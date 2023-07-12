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

class CarProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $brand,
        public string $model,
        public string $provider,
        public string $identifier
    ) {
    }

    public function handle(): void
    {
        try {

            $data = $this->getAdData();

            $this->validate($data);

            $data['active'] = true;

            $car = Car::updateOrCreate(
                [
                    'provider' => $data['provider'],
                    'provider_id' => $data['provider_id']
                ],
                $data
            );

            $this->log('Car saved: #'.$car->id, 'debug');

            $this->onCarSaved($car);

        } catch (\Exception $e) {

            $shouldIgnore = $e instanceof CarProcessIgnoreException;

            $this->log('Failed: '.$this->identifier, $shouldIgnore ? 'warning' : 'error');

            if ($shouldIgnore) {
                $this->log('Ignored: '.$e->getMessage(), 'warning');
                return;
            }

            throw $e;
        }
    }

    public function getAdData(): array
    {
        throw new CarProcessIgnoreException('Not implemented');
    }

    public function onCarSaved(Car $car): void
    {
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
        Log::$channel('[CAR-PROCESS-JOB]['.$this->provider.']['.$this->brand.']['.$this->model.'] '.$message);
    }
}
