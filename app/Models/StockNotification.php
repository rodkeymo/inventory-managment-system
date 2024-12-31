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
        'alert_threshold',
        'read',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
