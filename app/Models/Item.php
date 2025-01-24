<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'item_classification_code',
        'packaging_unit_code',
        'package',
        'quantity',
        'uom',
        'rate',
        'amount',
        'tax_type',
        'tax_rate',
        'taxable_amount',
        'tax_amount',
        'discount_rate',
        'discount_amount',
        'external_id',
        'invoice_id'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
