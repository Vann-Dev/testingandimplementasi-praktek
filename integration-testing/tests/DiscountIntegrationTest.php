<?php

use PHPUnit\Framework\TestCase;
use App\Product;
use App\Cart;
use App\DiscountService;

class DiscountIntegrationTest extends TestCase
{

    public function testApplyDiscount()
    {
        $product = new Product("Expensive Item", 1000000, 10);
        $cart = new Cart();
        $cart->addProduct($product, 1);

        $discountService = new DiscountService();
        $finalPrice = $discountService->applyDiscount($cart, 10);

        $this->assertEquals(900000, $finalPrice);
    }

    public function testApplyZeroDiscount()
    {
        $product = new Product("Expensive Item", 1000000, 10);
        $cart = new Cart();
        $cart->addProduct($product, 1);

        $discountService = new DiscountService();
        $finalPrice = $discountService->applyDiscount($cart, 0);

        $this->assertEquals(1000000, $finalPrice);
    }
}
