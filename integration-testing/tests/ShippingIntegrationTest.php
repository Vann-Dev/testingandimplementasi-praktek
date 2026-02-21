<?php

use PHPUnit\Framework\TestCase;
use App\Product;
use App\Cart;
use App\ShippingService;

class ShippingIntegrationTest extends TestCase
{

    public function testFreeShippingForHighTotal()
    {
        $product = new Product("Expensive", 600000, 1);
        $cart = new Cart();
        $cart->addProduct($product, 1);

        $shippingService = new ShippingService();
        $cost = $shippingService->calculateCost($cart);

        $this->assertEquals(0, $cost);
    }

    public function testShippingCostForLowTotal()
    {
        $product = new Product("Cheap", 200000, 1);
        $cart = new Cart();
        $cart->addProduct($product, 1);

        $shippingService = new ShippingService();
        $cost = $shippingService->calculateCost($cart);

        $this->assertEquals(20000, $cost);
    }
}
