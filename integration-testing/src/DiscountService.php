<?php

namespace App;

class DiscountService
{
    public function applyDiscount(Cart $cart, float $percent): float
    {
        $total = $cart->getTotalPrice();
        if ($percent <= 0) {
            return $total;
        }

        $discountAmount = $total * ($percent / 100);
        return $total - $discountAmount;
    }
}
