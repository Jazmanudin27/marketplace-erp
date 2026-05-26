<?php

namespace App\DTOs;

class ProductDTO
{
    public function __construct(
        public readonly string $marketplaceProductId,
        public readonly string $marketplace,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $sku,
        public readonly float $price,
        public readonly int $stock,
        public readonly ?string $imageUrl,
        public readonly ?string $category,
        public readonly string $status,
        public readonly array $marketplaceData,
    ) {}

    public static function fromShopee(array $data): self
    {
        return new self(
            marketplaceProductId: $data['item_id'] ?? '',
            marketplace: 'shopee',
            name: $data['item_name'] ?? '',
            description: $data['description'] ?? null,
            sku: $data['item_sku'] ?? null,
            price: (float) ($data['price'] ?? 0),
            stock: (int) ($data['stock'] ?? 0),
            imageUrl: $data['image'] ?? null,
            category: $data['category'] ?? null,
            status: $data['status'] ?? 'active',
            marketplaceData: $data,
        );
    }

    public static function fromTikTok(array $data): self
    {
        return new self(
            marketplaceProductId: $data['id'] ?? '',
            marketplace: 'tiktok',
            name: $data['name'] ?? '',
            description: $data['description'] ?? null,
            sku: $data['sku'] ?? null,
            price: (float) ($data['price'] ?? 0),
            stock: (int) ($data['stock'] ?? 0),
            imageUrl: $data['image_url'] ?? null,
            category: $data['category'] ?? null,
            status: $data['status'] ?? 'active',
            marketplaceData: $data,
        );
    }

    public static function fromTokopedia(array $data): self
    {
        return new self(
            marketplaceProductId: $data['id'] ?? '',
            marketplace: 'tokopedia',
            name: $data['name'] ?? '',
            description: $data['description'] ?? null,
            sku: $data['sku'] ?? null,
            price: (float) ($data['price'] ?? 0),
            stock: (int) ($data['stock'] ?? 0),
            imageUrl: $data['image_url'] ?? null,
            category: $data['category'] ?? null,
            status: $data['status'] ?? 'active',
            marketplaceData: $data,
        );
    }

    public function toArray(): array
    {
        return [
            'marketplace_product_id' => $this->marketplaceProductId,
            'marketplace' => $this->marketplace,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            'price' => $this->price,
            'stock' => $this->stock,
            'image_url' => $this->imageUrl,
            'category' => $this->category,
            'status' => $this->status,
            'marketplace_data' => $this->marketplaceData,
        ];
    }
}
