<?php

namespace App\Console\Commands;

use App\Constants\OrderConstants\OrderPaymentMethod;
use App\Constants\OrderConstants\OrderStatus;
use App\Models\Order;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class OrderCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(OrderService $orderService)
    {
        $currentDateTime = Carbon::now();

        $orders = Order::where('status', OrderStatus::PAYING)
            ->where('ended_at', '<', $currentDateTime)
            ->where('payment_method', OrderPaymentMethod::BANK)
            ->get();

        foreach($orders as $order) {
            $orderService->orderCancel($order->id);
        }

        Log::info('Updated status of ' . count($orders) . ' orders from PAYING to CANCEL.');
    }
}
