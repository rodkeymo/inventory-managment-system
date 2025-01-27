<?php

namespace App\Http\Controllers\Dashboards;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Quotation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $orders = Order::count();
        $completedOrders = Order::where('order_status', OrderStatus::COMPLETE)
            ->whereDate('created_at', today())
            ->count();

            //Sales Calculation
            //daily sales
        $totalSalesToday = number_format (Order::whereDate('created_at', today())
            ->sum('pay'));
             //weekly sales
        $totalSalesWeekly = number_format(Order::whereBetween('created_at', [
            Carbon::now()->startOfWeek()->format('Y-m-d'),
            Carbon::now()->endOfWeek()->format('Y-m-d')
        ])->sum('pay'));
        
             //monthly sales
        $totalSalesMonthly = number_format(Order::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('pay'));

        $startOfDay = Carbon::today()->startOfDay(); // 00:00:00
        $endOfDay = Carbon::today()->endOfDay(); // 23:59:59
        
        $dailyProfits = $this->calculateProfits($startOfDay, $endOfDay);
        
        $weeklyProfits = $this->calculateProfits(
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        );
        $monthlyProfits = $this->calculateProfits(
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        );

        $products = Product::count();

        $purchases = Purchase::count();
        $todayPurchases = Purchase::query()
            ->where('date', today())
            ->get()
            ->count();

        $categories = Category::count();

        $quotations = Quotation::count();
        $todayQuotations = Quotation::query()
            ->where('date', today()->format('Y-m-d'))
            ->get()
            ->count();

        return view('dashboard', [
            'products' => $products,
            'orders' => $orders,
            'completedOrders' => $completedOrders,
            'purchases' => $purchases,
            'todayPurchases' => $todayPurchases,
            'totalSalesToday' => $totalSalesToday,
            'totalSalesWeekly'=> $totalSalesWeekly,
            'totalSalesMonthly'=> $totalSalesMonthly,
            'dailyProfits' => $dailyProfits,
            'weeklyProfits' => $weeklyProfits,
            'monthlyProfits' => $monthlyProfits,
            'categories' => $categories,
            'quotations' => $quotations,
            'todayQuotations' => $todayQuotations,
        ]);
    }
    
    private function calculateProfits($start_date, $end_date)
    {
        // Calculate total buying price for the specified date range
        $buyingPriceTotalAmount = DB::table('order_details')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->whereBetween('order_details.created_at', [$start_date, $end_date])  // Filter for the date range
            ->sum(DB::raw('FLOOR((products.buying_price / 100) * order_details.quantity)'));
        
        // Calculate total selling price for the specified date range
        $sellingPriceTotalAmount = DB::table('order_details')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->whereBetween('order_details.created_at', [$start_date, $end_date])  // Filter for the date range
            ->sum(DB::raw('FLOOR(order_details.sold_at * order_details.quantity)'));
    
        // Calculate the profit
        $profit = $sellingPriceTotalAmount - $buyingPriceTotalAmount;
        $formattedProfit = number_format($profit);
    
        return $formattedProfit;
    }

}
