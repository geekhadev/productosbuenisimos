<?php

namespace App\Actions\Sales\Orders;

use App\Models\Sales\Order;

class DeleteOrderAction
{
    public function execute(Order $order): void
    {
        $order->delete();
    }
}
