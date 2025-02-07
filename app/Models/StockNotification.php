<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_name',
        'current_quantity',
        'account_id',
        'alert_threshold',
        'read',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
