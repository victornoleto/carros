<?php

namespace App\Jobs\Olx;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Processar página de anúncio da Olx.
 *
 * @author Victor Noleto <victornoleto@sysout.com.br>
 * @since 11/07/2023 
 * @version 1.0.0
 */
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
            
            $contents = $node->html();

            OlxProcessCarJob::dispatch($this->brand, $this->model, $contents)
                ->onQueue('olx:process');
        });
    }

    private function log(string $message, string $channel = 'debug'): void {

        Log::$channel('[OlxProcessPage]['.$this->brand.']['.$this->model.']['.$this->page.'] '.$message);
    }
}
