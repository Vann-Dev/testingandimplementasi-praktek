<?php

namespace App;

use Exception;

class OrderService
{
    public function checkout(Cart $cart): array
    {
        if ($cart->isEmpty()) {
            throw new Exception("Cart is empty");
        }

        $items = $cart->getItems();
        $total = $cart->getTotalPrice();

        foreach ($items as $item) {
            /** @var Product $product */
            $product = $item['product'];
            $quantity = $item['quantity'];

            $product->reduceStock($quantity);
        }

        $cart->clear();

        return [
            'status' => 'success',
            'items' => $items,
            'total' => $total,
            'message' => 'Order successfully processed'
        ];
    }
}
