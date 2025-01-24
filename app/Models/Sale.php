<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Sale extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'sale_code',
        'customer_id',
        'product_id',
        'user_id',
        'quantity',
        'sale_price',
        'status',
        'sale_date',
        'status'
    ];

    protected $casts = [
        'sale_date' => 'date',
        'sale_price' => 'decimal:2'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
