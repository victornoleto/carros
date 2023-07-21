<?php

namespace App\Console\Commands;

use App\Enums\CarProviderEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;

class QueuesSizeCommand extends Command
{
    protected $signature = 'queues:size {--live}';

    public function handle()
    {
        $live = $this->option('live');

        while (true) {

            $providers = CarProviderEnum::getInstances();
    
            $data = [];
    
            $queues = [
                'cars-sync',
                'cars-process'
            ];

            foreach ($queues as $queueName) {

                $size = Queue::size($queueName);

                $data[] = [
                    'queue' => $queueName,
                    'size' => $size,
                ];
            }
    
            $this->table(array_keys($data[0]), $data);

            if ($live) {
                sleep(1);
                system('clear');

            } else {
                break;
            }
        }
    }
}
