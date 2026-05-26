<?php

namespace App\Services\Order;

use App\Models\Order\Order;

class OrderService
{
    /**
     * Create new order.
     */
    public function createOrder(array $data): Order
    {
        return Order::create($data);
    }

    /**
     * Update order status.
     */
    public function updateOrderStatus(Order $order, string $status): Order
    {
        $order->update(['status' => $status]);
        return $order;
    }

    /**
     * Calculate order total.
     */
    public function calculateTotal(Order $order): float
    {
        return $order->items()->sum(\DB::raw('price * quantity'));
    }

    /**
     * Mark order as shipped.
     */
    public function markAsShipped(Order $order)
    {
        return $this->updateOrderStatus($order, 'shipped');
    }

    /**
     * Mark order as delivered.
     */
    public function markAsDelivered(Order $order)
    {
        return $this->updateOrderStatus($order, 'delivered');
    }
}
