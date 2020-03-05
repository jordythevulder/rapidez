<?php

namespace App\Console\Commands;

use App\Jobs\IndexProductJob;
use App\Product;
use App\Store;
use Cviebrock\LaravelElasticsearch\Manager as Elasticsearch;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Illuminate\Console\Command;

class IndexProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'index:products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index the products in Elasticsearch';

    protected int $chunkSize = 1000;

    protected Elasticsearch $elasticsearch;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Elasticsearch $elasticsearch)
    {
        parent::__construct();

        $this->elasticsearch = $elasticsearch;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach (Store::all() as $store) {
            $this->line('Store: '.$store->name);
            config()->set('shop.store', $store->store_id);

            $this->createIndexIfNeeded('products_' . $store->store_id);

            $productQuery = Product::where('visibility', 4);

            $bar = $this->output->createProgressBar($productQuery->count());
            $bar->start();

            $productQuery->chunk($this->chunkSize, function ($products) use ($store, $bar) {
                foreach ($products as $product) {
                    $data = ['store' => $store->store_id];
                    foreach (config('shop.attributes') as $attribute => $index) {
                        if ($index) {
                            $data[$attribute] = $product->$attribute;
                        }
                    }
                    IndexProductJob::dispatch($data);
                }

                $bar->advance($this->chunkSize);
            });

            $bar->finish();
            $this->line('');
        }
        $this->info('Done!');
    }

    public function createIndexIfNeeded(string $index): void
    {
        try {
            $this->elasticsearch->cat()->indices(['index' => $index]);
        } catch (Missing404Exception $e) {
            $this->elasticsearch->indices()->create([
                'index' => $index,
                'body'  => [
                    'mappings' => [
                        'properties' => [
                            'price' => [
                                'type' => 'double',
                            ]
                        ]
                    ]
                ]
            ]);
        }
    }
}