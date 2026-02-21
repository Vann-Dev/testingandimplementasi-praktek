<?php

namespace App;

class ShippingService
{
    public function calculateCost(Cart $cart): float
    {
        $total = $cart->getTotalPrice();

        if ($total > 500000) {
            return 0;
        } else {
            return 20000;
        }
    }
}
