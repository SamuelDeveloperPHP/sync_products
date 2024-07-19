<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Brand;
use GuzzleHttp\Client;

class SyncProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $client = new Client(['verify' => false]);
        $moduleUrl = 'https://www.leroymerlin.com.br/api/boitata/v1/modularContents/ad3d3c6b8bf673a358b03e56/modules?page=1&device=desktop';

        try {
            $moduleResponse = $client->request('GET', $moduleUrl);
            $moduleJson = $moduleResponse->getBody()->getContents();
            $categoryIds = $this->getCategoryIds($moduleJson);

            foreach ($categoryIds as $codigo_categoria) {
                $url = 'https://www.leroymerlin.com.br/api/boitata/v1/categories/' . $codigo_categoria . '/products?perPage=36';

                try {
                    $response = $client->request('GET', $url);
                    $json = $response->getBody()->getContents();
                    $products = $this->scrapeProducts($json);

                    foreach ($products as $product) {
                        $category = Category::firstOrCreate(['name' => $product['category']]);
                        $subcategory = Subcategory::firstOrCreate(['name' => $product['subcategory'], 'category_id' => $category->id]);
                        $brand = Brand::firstOrCreate(['name' => $product['brand']]);

                        Product::updateOrCreate(
                            ['title' => $product['title']],
                            [
                                'category_id' => $category->id,
                                'subcategory_id' => $subcategory->id,
                                'brand_id' => $brand->id,
                                'description' => $product['description'],
                                'price' => $product['price'],
                                'picture' => $product['picture']
                            ]
                        );
                    }
                } catch (\GuzzleHttp\Exception\RequestException $e) {
                    \Log::error('RequestException: ' . $e->getMessage());
                }
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            \Log::error('RequestException: ' . $e->getMessage());
        }
    }

    private function getCategoryIds($json)
    {
        $data = json_decode($json, true);
        $categoryIds = [];

        if (isset($data['results'][0]['items'])) {
            $items = $data['results'][0]['items'];
            foreach ($items as $key => $item) {
                if (in_array($key, [0, 1, 2, 3, 4, 5, 9, 7])) {
                    $categoryIds[] = $item['id'];
                }
            }
        }

        return $categoryIds;
    }

    private function scrapeProducts($json)
    {
        $products = [];
        $data = json_decode($json, true);

        if (isset($data['products']) && is_array($data['products'])) {
            foreach ($data['products'] as $product) {
                $products[] = [
                    'title' => $product['name'],
                    'category' => $product['category'], // Ajuste conforme necessário
                    'subcategory' => '', // Ajustar conforme necessário
                    'brand' => $product['brand'],
                    'description' => '', // Extrair conforme necessário
                    'price' => $this->parsePrice($product['price']['to']),
                    'picture' => $product['picture'],
                ];
            }
        }

        return $products;
    }

    private function parsePrice($price)
    {
        $integers = isset($price['integers']) ? $price['integers'] : '0';
        $decimals = isset($price['decimals']) ? $price['decimals'] : '00';
        return floatval($integers . '.' . $decimals);
    }
}
