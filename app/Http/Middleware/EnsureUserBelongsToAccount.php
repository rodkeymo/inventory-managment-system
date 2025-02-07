<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToAccount
{
    protected $models = [
        'account' => \App\Models\Account::class,
        'category' => \App\Models\Category::class,
        'customer' => \App\Models\Customer::class,
        'order' => \App\Models\Order::class,
        'orderDetails' => \App\Models\OrderDetails::class,
        'product' => \App\Models\Product::class,
        'purchase' => \App\Models\Purchase::class,
        'purchaseDetails' => \App\Models\PurchaseDetails::class,
        'quotation' => \App\Models\Quotation::class,
        'quotationDetails' => \App\Models\QuotationDetails::class,
        'role' => \App\Models\Role::class,
        'stockNotification' => \App\Models\StockNotification::class,
        'supplier' => \App\Models\Supplier::class,
        'unit' => \App\Models\Unit::class,
        'user' => \App\Models\User::class,
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        $route = $request->route();
        
        foreach ($this->models as $key => $modelClass) {
            $resource = $route->parameter($key);
            
            if ($resource && $resource instanceof $modelClass) {
                if (!property_exists($resource, 'account_id') || $resource->account_id !== $user->account_id) {
                    abort(403, 'Unauthorized access.');
                }
                break;
            }
        }

        return $next($request);
    }
}