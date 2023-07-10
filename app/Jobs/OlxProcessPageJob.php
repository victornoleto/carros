<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class OlxProcessPageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Crawler $node;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $brand,
        public string $model,
        public int $page,
        public string $contents
    ) {
    }
    
    public function handle(): void {

        $this->node = new Crawler($this->contents);

        $ads = $this->node
            ->filter('#ad-list > li:not(.sponsored)');

        $this->log('Processing '.count($ads).' ads...');

        $ads->reduce(function(Crawler $node, $i) {
            $this->processAd($node->html());
        });
    }

    private function processAd(string $contents) {

        $job = new OlxProcessCarJob($this->brand, $this->model, $contents);

        dispatch($job)->onQueue('olx-process-car');
    }

    private function log(string $message, string $channel = 'debug'): void {

        Log::$channel('[OlxProcessPage]['.$this->brand.']['.$this->model.']['.$this->page.'] '.$message);
    }
}
