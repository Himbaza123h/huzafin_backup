<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StkInvoice extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'invoice_number',
        'type',
        'user_id',
        'entity_id',
        'purchase_id',
        'sale_id',
        'download_count',
        'status',
        'total_amount',
        'notes'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'download_count' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'entity_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'entity_id');
    }
}


