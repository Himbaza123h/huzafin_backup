<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class Refund extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'refund_code',
        'product_id',
        'user_id',
        'quantity',
        'refund_amount',
        'status',
        'refund_date'
    ];

    protected $casts = [
        'refund_date' => 'date',
        'refund_amount' => 'decimal:2'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
