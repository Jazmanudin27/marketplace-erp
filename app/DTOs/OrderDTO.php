<?php

namespace App\DTOs;

class OrderDTO
{
    public function __construct(
        public readonly string $marketplaceOrderId,
        public readonly string $marketplace,
        public readonly string $orderNumber,
        public readonly string $customerName,
        public readonly ?string $customerEmail,
        public readonly ?string $customerPhone,
        public readonly array $shippingAddress,
        public readonly float $subtotal,
        public readonly float $shippingFee,
        public readonly float $totalAmount,
        public readonly string $paymentMethod,
        public readonly string $paymentStatus,
        public readonly string $orderStatus,
        public readonly string $orderDate,
        public readonly array $items,
        public readonly array $marketplaceData,
    ) {
    }

    public static function fromShopee(array $data): self
    {
        return new self(
            marketplaceOrderId: $data['order_sn'] ?? '',
            marketplace: 'shopee',
            orderNumber: $data['order_sn'] ?? '',
            customerName: $data['buyer_username'] ?? '',
            customerEmail: null,
            customerPhone: $data['recipient_phone'] ?? null,
            shippingAddress: [
                'name' => $data['recipient_name'] ?? '',
                'phone' => $data['recipient_phone'] ?? '',
                'address' => $data['recipient_address'] ?? '',
                'city' => $data['recipient_city'] ?? '',
                'province' => $data['recipient_state'] ?? '',
                'postal_code' => $data['recipient_zipcode'] ?? '',
            ],
            subtotal: (float) ($data['total_amount'] ?? 0),
            shippingFee: (float) ($data['shipping_fee'] ?? 0),
            totalAmount: (float) ($data['total_amount'] ?? 0),
            paymentMethod: $data['payment_method'] ?? '',
            paymentStatus: $data['payment_status'] ?? 'pending',
            orderStatus: $data['order_status'] ?? 'pending',
            orderDate: $data['create_time'] ?? now()->toDateTimeString(),
            items: $data['item_list'] ?? [],
            marketplaceData: $data,
        );
    }

    public static function fromTikTok(array $data): self
    {
        return new self(
            marketplaceOrderId: (string) ($data['id'] ?? ''),

            marketplace: 'tiktok',

            orderNumber: (string) ($data['id'] ?? ''),

            customerName: $data['recipient_address']['name']
            ?? $data['buyer_email']
            ?? 'Customer',

            customerEmail: $data['buyer_email'] ?? null,

            customerPhone: $data['recipient_address']['phone_number'] ?? null,

            shippingAddress: [
                'name' => $data['recipient_address']['name'] ?? '',
                'phone' => $data['recipient_address']['phone_number'] ?? '',
                'address' => $data['recipient_address']['full_address'] ?? '',
                'city' => '',
                'province' => $data['recipient_address']['region_code'] ?? '',
                'postal_code' => $data['recipient_address']['postal_code'] ?? '',
            ],

            subtotal: (float) ($data['payment']['sub_total'] ?? 0),

            shippingFee: (float) ($data['payment']['shipping_fee'] ?? 0),

            totalAmount: (float) ($data['payment']['total_amount'] ?? 0),

            paymentMethod: $data['payment_method_name'] ?? '',

            paymentStatus: match ($data['status'] ?? '') {
                'COMPLETED', 'DELIVERED' => 'paid',
                'AWAITING_SHIPMENT' => 'paid',
                default => 'pending',
            },

            orderStatus: strtolower($data['status'] ?? 'pending'),

            orderDate: isset($data['create_time'])
            ? date('Y-m-d H:i:s', (int) $data['create_time'])
            : now()->toDateTimeString(),

            items: $data['line_items'] ?? [],

            marketplaceData: $data,
        );
    }

    public static function fromTokopedia(array $data): self
    {
        return new self(
            marketplaceOrderId: $data['order_id'] ?? '',
            marketplace: 'tokopedia',
            orderNumber: $data['invoice_number'] ?? '',
            customerName: $data['customer_name'] ?? '',
            customerEmail: $data['customer_email'] ?? null,
            customerPhone: $data['customer_phone'] ?? null,
            shippingAddress: [
                'name' => $data['shipping_address']['name'] ?? '',
                'phone' => $data['shipping_address']['phone'] ?? '',
                'address' => $data['shipping_address']['address'] ?? '',
                'city' => $data['shipping_address']['city'] ?? '',
                'province' => $data['shipping_address']['province'] ?? '',
                'postal_code' => $data['shipping_address']['postal_code'] ?? '',
            ],
            subtotal: (float) ($data['subtotal'] ?? 0),
            shippingFee: (float) ($data['shipping_fee'] ?? 0),
            totalAmount: (float) ($data['total_amount'] ?? 0),
            paymentMethod: $data['payment_method'] ?? '',
            paymentStatus: $data['payment_status'] ?? 'pending',
            orderStatus: $data['order_status'] ?? 'pending',
            orderDate: $data['order_date'] ?? now()->toDateTimeString(),
            items: $data['items'] ?? [],
            marketplaceData: $data,
        );
    }

    public function toArray(): array
    {
        return [
            'marketplace_order_id' => $this->marketplaceOrderId,
            'marketplace' => $this->marketplace,
            'order_number' => $this->orderNumber,
            'customer_name' => $this->customerName,
            'customer_email' => $this->customerEmail,
            'customer_phone' => $this->customerPhone,
            'shipping_address' => json_encode($this->shippingAddress),
            'subtotal' => $this->subtotal,
            'shipping_fee' => $this->shippingFee,
            'total_amount' => $this->totalAmount,
            'payment_method' => $this->paymentMethod,
            'payment_status' => $this->paymentStatus,
            'order_status' => $this->orderStatus,
            'order_date' => $this->orderDate,
            'marketplace_data' => $this->marketplaceData,
        ];
    }
}
