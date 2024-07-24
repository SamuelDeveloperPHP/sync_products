<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\EstoqueCategoriaPrincipal;
use App\Models\EstoqueCategoriaPrimaria;
use App\Models\EstoqueCategoriaSecundaria;
use App\Models\Estoque;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class SyncProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(): void
    {
        // Define o tempo máximo de execução do script para 0 (ilimitado)
        set_time_limit(0);

        $client = new Client(['verify' => false]);

        // Passo 1: Obter Categorias Principais
        $treeUrl = 'https://www.leroymerlin.com.br/api/v3/categories/tree';
        $treeResponse = $client->request('GET', $treeUrl);
        $treeData = json_decode($treeResponse->getBody(), true);

        // Acessar a chave "data" e depois "results"
        foreach ($treeData['data']['results'] as $result) {
            foreach ($result['items'] as $item) {
                $categoriaPrincipal = EstoqueCategoriaPrincipal::updateOrCreate(
                    [
                        'id_categoy_father' => $item['id']
                    ],
                    [
                        'nome_categoria_princ' => $item['name']
                    ]
                );

                // Passo 2: Obter Categorias Primárias
                $primariaUrl = "https://www.leroymerlin.com.br/api/boitata/v1/modularContents/{$item['id']}/modules?page=1&device=desktop";
                $primariaResponse = $client->request('GET', $primariaUrl);
                $primariaData = json_decode($primariaResponse->getBody(), true);

                if (isset($primariaData['results'][0]['items']) && is_array($primariaData['results'][0]['items'])) {
                    foreach ($primariaData['results'][0]['items'] as $primariaItem) {
                        $categoriaPrimaria = EstoqueCategoriaPrimaria::updateOrCreate(
                            [
                                'id_categoy_first' => $primariaItem['id']
                            ],
                            [
                                'id_categoy_father' => $item['id'],
                                'id_categoria_principal' => $categoriaPrincipal->id,
                                'nome_categoria_primaria' => $primariaItem['name']
                            ]
                        );

                        // Passo 3: Obter Categorias Secundárias
                        $secundariaUrl = "https://www.leroymerlin.com.br/api/boitata/v1/modularContents/{$primariaItem['id']}/modules?page=1&device=desktop";
                        $secundariaResponse = $client->request('GET', $secundariaUrl);
                        $secundariaData = json_decode($secundariaResponse->getBody(), true);

                        // Verificar se secundariaData está vazio
                        if (isset($secundariaData['results'][0]['items']) && is_array($secundariaData['results'][0]['items']) && !empty($secundariaData['results'][0]['items'])) {
                            foreach ($secundariaData['results'][0]['items'] as $secundariaItem) {
                                $categoriaSecundaria = EstoqueCategoriaSecundaria::updateOrCreate(
                                    [
                                        'id_categoy_secondary' => $secundariaItem['id']
                                    ],
                                    [
                                        'id_categoy_father' => $item['id'],
                                        'id_categoy_first' => $categoriaPrimaria->id,
                                        'id_categoria_principal' => $categoriaPrincipal->id,
                                        'id_categoria_primaria' => $categoriaPrimaria->id,
                                        'nome_categoria_secundaria' => $secundariaItem['name']
                                    ]
                                );

                                // Passo 4: Obter Produtos
                                $produtosUrl = "https://www.leroymerlin.com.br/api/boitata/v1/categories/{$secundariaItem['id']}/products?perPage=36";
                                try {
                                    $produtosResponse = $client->request('GET', $produtosUrl);
                                    $produtosData = json_decode($produtosResponse->getBody(), true);

                                    if (isset($produtosData['products']) && is_array($produtosData['products'])) {
                                        foreach ($produtosData['products'] as $product) {
                                            $imageUrl = $product['picture'];
                                            $targetDir = public_path("build/assets/images/produtos/{$product['id']}");

                                            // Baixar e salvar a imagem
                                            $savedImagePath = $this->downloadImage($imageUrl, $targetDir);

                                            //dd($this->user->email);

                                            // Obter o nome da imagem a partir do caminho salvo
                                            $imageName = basename($savedImagePath);

                                            Estoque::updateOrCreate(
                                                [
                                                    'id_produto' => $product['id'],
                                                    'nome_produto' => $product['name']
                                                ],
                                                [
                                                    'id_categoy_father' => $item['id'],
                                                    'id_categoy_first' => $categoriaPrimaria->id,
                                                    'id_categoy_secondary' => $secundariaItem['id'],
                                                    'id_categoria_principal' => $categoriaPrincipal->id,
                                                    'id_categoria_primaria' => $categoriaPrimaria->id,
                                                    'id_categoria_secundaria' => $secundariaItem['id'],
                                                    'nome_produto' => $product['name'],
                                                    'id_marca' => $product['brand'],
                                                    'usuario' => $this->user->email,
                                                    'valor_unitario' => $this->parsePrice($product['price']['from']),
                                                    'unidade' => $product['unit'],
                                                    'status_produto' => 1,
                                                    'image' => $imageName,
                                                ]
                                            );
                                        }
                                    }
                                } catch (RequestException $e) {
                                    if ($e->hasResponse() && $e->getResponse()->getStatusCode() == 404) {
                                        Log::warning('Produtos não encontrados para a categoria: ' . $secundariaItem['id']);
                                        continue;
                                    } else {
                                        throw $e;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function downloadImage($imageUrl, $targetDir)
    {
        $client = new Client(['verify' => false]);

        try {
            // Fazer a requisição HTTP para obter a imagem
            $response = $client->get($imageUrl);
            $content = $response->getBody()->getContents();

            // Obter o nome da imagem a partir da URL
            $imageName = basename($imageUrl);

            // Definir o caminho completo para salvar a imagem
            $imagePath = $targetDir . '/' . $imageName;

            // Criar o diretório se não existir
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // Salvar a imagem no disco
            file_put_contents($imagePath, $content);

            return $imagePath;
        } catch (\Exception $e) {
            // Lidar com erros
            Log::error('Erro ao baixar a imagem: ' . $e->getMessage());
            return null;
        }
    }

    private function parsePrice($price)
    {
        $integers = isset($price['integers']) ? $price['integers'] : '0';
        $decimals = isset($price['decimals']) ? $price['decimals'] : '00';
        return floatval($integers . '.' . $decimals);
    }
}
