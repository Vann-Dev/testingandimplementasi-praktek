<?php

use PHPUnit\Framework\TestCase;
use App\Product;
use App\Cart;
use App\OrderService;

class OrderIntegrationTest extends TestCase
{

    public function testAddProductAndCalculateTotal()
    {
        $product1 = new Product("Laptop", 5000000, 10);
        $product2 = new Product("Mouse", 50000, 20);

        $cart = new Cart();
        $cart->addProduct($product1, 1);
        $cart->addProduct($product2, 2);

        $this->assertEquals(5100000, $cart->getTotalPrice());
    }

    public function testSuccessfulCheckout()
    {
        $product = new Product("Laptop", 5000000, 10);
        $cart = new Cart();
        $cart->addProduct($product, 2);

        $orderService = new OrderService();
        $result = $orderService->checkout($cart);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals(8, $product->getStock());
        $this->assertTrue($cart->isEmpty());
        $this->assertEquals(10000000, $result['total']);
    }

    public function testCheckoutFailsWhenStockInsufficient()
    {
        $product = new Product("Laptop", 5000000, 1);
        $cart = new Cart();
        $cart->addProduct($product, 2);

        $orderService = new OrderService();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Stock not sufficient");

        $orderService->checkout($cart);
    }

    public function testCheckoutFailsWhenCartEmpty()
    {
        $cart = new Cart();
        $orderService = new OrderService();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cart is empty");

        $orderService->checkout($cart);
    }

    public function testAddSameProductMultipleTimes()
    {
        $product = new Product("Laptop", 5000, 10);
        $cart = new Cart();

        $cart->addProduct($product, 1);
        $cart->addProduct($product, 2);

        $items = $cart->getItems();
        $this->assertEquals(3, $items[$product->getName()]['quantity']);
        $this->assertEquals(15000, $cart->getTotalPrice());
    }

    public function testRemoveProductFromCart()
    {
        $product1 = new Product("Laptop", 5000, 10);
        $product2 = new Product("Mouse", 100, 10);

        $cart = new Cart();
        $cart->addProduct($product1);
        $cart->addProduct($product2);

        $cart->removeProduct($product1);

        $items = $cart->getItems();
        $this->assertCount(1, $items);
        $this->assertEquals(100, $cart->getTotalPrice());
    }
}
