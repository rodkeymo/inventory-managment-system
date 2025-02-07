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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get the authenticated user
        $user = Auth::user();

        // Restrict orders based on the user's account_id
        $orders = Order::where('account_id', $user->account_id)->count();
        $completedOrders = Order::where('account_id', $user->account_id)
            ->where('order_status', OrderStatus::COMPLETE)
            ->whereDate('created_at', today())
            ->count();

        // Sales Calculation
        // Daily sales
        $totalSalesToday = number_format(Order::where('account_id', $user->account_id)
            ->whereDate('created_at', today())
            ->sum('pay'));

        // Weekly sales
        $totalSalesWeekly = number_format(Order::where('account_id', $user->account_id)
            ->whereBetween('created_at', [
                Carbon::now()->startOfWeek()->format('Y-m-d'),
                Carbon::now()->endOfWeek()->format('Y-m-d')
            ])
            ->sum('pay'));

        // Monthly sales
        $totalSalesMonthly = number_format(Order::where('account_id', $user->account_id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('pay'));

        // Profit Calculations
        $startOfDay = Carbon::today()->startOfDay(); // 00:00:00
        $endOfDay = Carbon::today()->endOfDay(); // 23:59:59
        $dailyProfits = $this->calculateProfits($startOfDay, $endOfDay, $user->account_id);

        $weeklyProfits = $this->calculateProfits(
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek(),
            $user->account_id
        );

        $monthlyProfits = $this->calculateProfits(
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth(),
            $user->account_id
        );

        // Restrict products based on account_id
        $products = Product::where('account_id', $user->account_id)->count();

        // Restrict purchases based on account_id
        $purchases = Purchase::where('account_id', $user->account_id)->count();
        $todayPurchases = Purchase::where('account_id', $user->account_id)
            ->where('date', today())
            ->get()
            ->count();

        // Restrict categories based on account_id
        $categories = Category::where('account_id', $user->account_id)->count();

        // Restrict quotations based on account_id
        $quotations = Quotation::where('account_id', $user->account_id)->count();
        $todayQuotations = Quotation::where('account_id', $user->account_id)
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
            'totalSalesWeekly' => $totalSalesWeekly,
            'totalSalesMonthly' => $totalSalesMonthly,
            'dailyProfits' => $dailyProfits,
            'weeklyProfits' => $weeklyProfits,
            'monthlyProfits' => $monthlyProfits,
            'categories' => $categories,
            'quotations' => $quotations,
            'todayQuotations' => $todayQuotations,
        ]);
    }

    private function calculateProfits($start_date, $end_date, $account_id)
    {
        // Calculate total buying price for the specified date range and account
        $buyingPriceTotalAmount = DB::table('order_details')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->where('products.account_id', $account_id)  // Filter by account_id
            ->whereBetween('order_details.created_at', [$start_date, $end_date])
            ->sum(DB::raw('FLOOR((products.buying_price / 100) * order_details.quantity)'));

        // Calculate total selling price for the specified date range and account
        $sellingPriceTotalAmount = DB::table('order_details')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->where('products.account_id', $account_id)  // Filter by account_id
            ->whereBetween('order_details.created_at', [$start_date, $end_date])
            ->sum(DB::raw('FLOOR(order_details.sold_at * order_details.quantity)'));

        // Calculate the profit
        $profit = $sellingPriceTotalAmount - $buyingPriceTotalAmount;
        $formattedProfit = number_format($profit);

        return $formattedProfit;
    }
}
