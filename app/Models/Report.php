<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasUuids;

    protected $fillable = [
        'report_name',
        'report_type',
        'product_id',
        'purchase_id',
        'sale_id',
        'refund_id',
        'user_id',
        'additional_notes'
    ];

    protected $casts = [
        'additional_notes' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function refund()
    {
        return $this->belongsTo(Refund::class);
    }
}
