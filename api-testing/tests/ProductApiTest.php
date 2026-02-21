<?php

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class ProductApiTest extends TestCase
{
    private Client $client;
    private string $dataFile;
    private array $originalData;

    protected function setUp(): void
    {
        $this->client   = new Client([
            'base_uri'    => 'http://localhost:8080',
            'http_errors' => false,
        ]);

        $this->dataFile   = __DIR__ . '/../api/products.json';
        $this->originalData = json_decode(file_get_contents($this->dataFile), true);
    }

    protected function tearDown(): void
    {
        file_put_contents($this->dataFile, json_encode($this->originalData, JSON_PRETTY_PRINT));
    }

    public function testGetAllProducts(): void
    {
        $response = $this->client->get('/products');
        $body     = json_decode($response->getBody(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', $body['status']);
        $this->assertCount(3, $body['data']);
    }

    public function testGetProductById(): void
    {
        $response = $this->client->get('/products/1');
        $body     = json_decode($response->getBody(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', $body['status']);
        $this->assertEquals(1, $body['data']['id']);
        $this->assertEquals('Laptop', $body['data']['name']);
    }

    public function testGetProductNotFound(): void
    {
        $response = $this->client->get('/products/999');
        $body     = json_decode($response->getBody(), true);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('error', $body['status']);
    }

    public function testCreateProduct(): void
    {
        $response = $this->client->post('/products', [
            'json' => ['name' => 'Monitor', 'price' => 3000000],
        ]);
        $body = json_decode($response->getBody(), true);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('success', $body['status']);
        $this->assertEquals('Monitor', $body['data']['name']);
        $this->assertEquals(3000000, $body['data']['price']);
    }

    public function testCreateProductMissingField(): void
    {
        $response = $this->client->post('/products', [
            'json' => ['name' => 'Monitor'],
        ]);
        $body = json_decode($response->getBody(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('error', $body['status']);
    }

    public function testUpdateProduct(): void
    {
        $response = $this->client->put('/products/2', [
            'json' => ['name' => 'Gaming Mouse', 'price' => 500000],
        ]);
        $body = json_decode($response->getBody(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', $body['status']);
        $this->assertEquals('Gaming Mouse', $body['data']['name']);
        $this->assertEquals(500000, $body['data']['price']);
    }

    public function testDeleteProduct(): void
    {
        $deleteResponse = $this->client->delete('/products/3');
        $deleteBody     = json_decode($deleteResponse->getBody(), true);

        $this->assertEquals(200, $deleteResponse->getStatusCode());
        $this->assertEquals('success', $deleteBody['status']);

        $getResponse = $this->client->get('/products/3');
        $this->assertEquals(404, $getResponse->getStatusCode());
    }

    public function testDeleteProductNotFound(): void
    {
        $response = $this->client->delete('/products/999');
        $body     = json_decode($response->getBody(), true);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('error', $body['status']);
    }

    public function testSearchProduct(): void
    {
        $response = $this->client->get('/products/search', [
            'query' => ['name' => 'laptop'],
        ]);
        $body = json_decode($response->getBody(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', $body['status']);
        $this->assertCount(1, $body['data']);
        $this->assertEquals('Laptop', $body['data'][0]['name']);
    }

    public function testSearchProductNotFound(): void
    {
        $response = $this->client->get('/products/search', [
            'query' => ['name' => 'xyz'],
        ]);
        $body = json_decode($response->getBody(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', $body['status']);
        $this->assertCount(0, $body['data']);
    }

    public function testProductCount(): void
    {
        $response = $this->client->get('/products/count');
        $body     = json_decode($response->getBody(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('success', $body['status']);
        $this->assertEquals(3, $body['count']);
    }
}
