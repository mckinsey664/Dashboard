<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'order_code', 'overall_code', 'inquiry_mail', 'region',
        'date_received', 'sent_to_client', 'notes_to_purchasing', 'notes_to_elias',
        'ref', 'priority',
    ];

    protected $casts = [
        'date_received' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
