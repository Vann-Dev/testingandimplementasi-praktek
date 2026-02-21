<?php

namespace App;

class Cart
{
    private array $items = [];

    public function addProduct(Product $product, int $quantity = 1): void
    {
        $productName = $product->getName();

        if (isset($this->items[$productName])) {
            $this->items[$productName]['quantity'] += $quantity;
        } else {
            $this->items[$productName] = [
                'product' => $product,
                'quantity' => $quantity
            ];
        }
    }

    public function removeProduct(Product $product): void
    {
        $productName = $product->getName();
        if (isset($this->items[$productName])) {
            unset($this->items[$productName]);
        }
    }

    public function getTotalPrice(): int
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item['product']->getPrice() * $item['quantity'];
        }
        return $total;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function clear(): void
    {
        $this->items = [];
    }
}
