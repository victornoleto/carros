<?php

namespace App\Jobs;

use App\Enums\CarProviderEnum;
use App\Exceptions\CarProcessIgnoreException;
use App\Models\Car;
use App\Services\CarProcessService;
use App\Traits\CarProviderTrait;
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

    use CarProviderTrait;

    public CarProviderEnum $provider;

    public CarProcessService $processService;

    public function __construct(
        public string $brand,
        public string $model,
        public string|array $data,
    ) {
        $this->setProviderByClassName();
        
        $this->processService = $this->provider->getProcessService([
            'brand' => $this->brand,
            'model' => $this->model,
            'data' => $data
        ]);
    }

    public function handle(): void
    {
        try {

            $data = $this->processService->getData();

            $this->validate($data);

            $data['active'] = true;

            $car = Car::updateOrCreate(
                [
                    'provider' => $data['provider'],
                    'provider_id' => $data['provider_id']
                ],
                $data
            );

            if ($car->wasRecentlyCreated) {
                $this->log('Car created: #'.$car->id);

            } elseif ($car->isDirty()) {
                $this->log('Car updated: #'.$car->id.' - '.json_encode($car->getChanges()));
            }

        } catch (\Exception $e) {

            if ($e instanceof CarProcessIgnoreException) {
                
                $this->log('Ignored: '.$e->getMessage(), 'warning');

                return;

            } else {

                $identifier = is_string($this->data) ? $this->data : json_encode($this->data);

                $this->log('Failed: '.$identifier, 'error');
            }

            throw $e;
        }
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
        $logMessage = sprintf(
            '[car-process][%s][%s][%s] %s',
            $this->provider->value,
            $this->brand,
            $this->model,
            $message
        );

        Log::$channel($logMessage);
    }
}
