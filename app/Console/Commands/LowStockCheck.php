<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\StockNotification;

class LowStockCheck extends Command
{
    protected $signature = 'stock:check';
    protected $description = 'Check for low-stock products and create notifications';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Fetch products with low stock
        $lowStockProducts = Product::whereColumn('quantity', '<=', 'quantity_alert')->get();

        if ($lowStockProducts->isEmpty()) {
            $this->info('No low-stock products found.');
            return;
        }

        foreach ($lowStockProducts as $product) {
            // Check if a notification for this product already exists
            $exists = StockNotification::where('product_id', $product->id)->where('read', false)->exists();

            if (!$exists) {
                // Create a new notification
                StockNotification::create([
                    'product_id' => $product->id,
                    'product_name' => $product->name, // Use `name` field from the Product model
                    'current_quantity' => $product->quantity,
                    'alert_threshold' => $product->quantity_alert,
                ]);
            }
        }

        $this->info('Low-stock notifications logged successfully.');
    }
}
