<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'part_number', 'manufacturer', 'quantity',
        'uom', 'target_price_usd', 'item_notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'target_price_usd' => 'decimal:4',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
