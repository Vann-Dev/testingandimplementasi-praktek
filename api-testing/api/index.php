<?php

header('Content-Type: application/json');

$dataFile = __DIR__ . '/products.json';

function readProducts(string $file)
{
    if (!file_exists($file)) {
        file_put_contents($file, '[]');
    }
    $content = file_get_contents($file);
    return json_decode($content, true) ?? [];
}

function saveProducts(string $file, array $products)
{
    file_put_contents($file, json_encode(array_values($products), JSON_PRETTY_PRINT));
}

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rtrim($uri, '/');

if ($uri === '/products' || $uri === '') {

    if ($method === 'GET') {
        $products = readProducts($dataFile);
        http_response_code(200);
        echo json_encode(['status' => 'success', 'data' => array_values($products)]);
    } elseif ($method === 'POST') {
        $body = json_decode(file_get_contents('php://input'), true);

        if (empty($body['name']) || !isset($body['price'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Fields name and price are required']);
            exit;
        }

        $products  = readProducts($dataFile);
        $maxId     = 0;
        foreach ($products as $p) {
            if ($p['id'] > $maxId) $maxId = $p['id'];
        }

        $newProduct = [
            'id'    => $maxId + 1,
            'name'  => $body['name'],
            'price' => (int) $body['price'],
        ];

        $products[] = $newProduct;
        saveProducts($dataFile, $products);

        http_response_code(201);
        echo json_encode(['status' => 'success', 'data' => $newProduct]);
    } else {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    }
} elseif (preg_match('#^/products/search$#', $uri)) {

    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        exit;
    }

    $name     = $_GET['name'] ?? '';
    $products = readProducts($dataFile);
    $results  = [];

    foreach ($products as $p) {
        if (stripos($p['name'], $name) !== false) {
            $results[] = $p;
        }
    }

    http_response_code(200);
    echo json_encode(['status' => 'success', 'data' => $results]);
} elseif (preg_match('#^/products/count$#', $uri)) {

    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        exit;
    }

    $products = readProducts($dataFile);
    http_response_code(200);
    echo json_encode(['status' => 'success', 'count' => count($products)]);
} elseif (preg_match('#^/products/(\d+)$#', $uri, $matches)) {

    $id       = (int) $matches[1];
    $products = readProducts($dataFile);

    $index = null;
    foreach ($products as $i => $p) {
        if ($p['id'] === $id) {
            $index = $i;
            break;
        }
    }

    if ($method === 'GET') {
        if ($index === null) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Product not found']);
            exit;
        }
        http_response_code(200);
        echo json_encode(['status' => 'success', 'data' => $products[$index]]);
    } elseif ($method === 'PUT') {
        if ($index === null) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Product not found']);
            exit;
        }

        $body = json_decode(file_get_contents('php://input'), true);

        if (isset($body['name']))  $products[$index]['name']  = $body['name'];
        if (isset($body['price'])) $products[$index]['price'] = (int) $body['price'];

        saveProducts($dataFile, $products);

        http_response_code(200);
        echo json_encode(['status' => 'success', 'data' => $products[$index]]);
    } elseif ($method === 'DELETE') {
        if ($index === null) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Product not found']);
            exit;
        }

        array_splice($products, $index, 1);
        saveProducts($dataFile, $products);

        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Product deleted']);
    } else {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    }
} else {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Endpoint not found']);
}
