<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Product extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'entry_code',
        'category_id',
        'name',
        'track_stock',
        'opening_stock',
        'unit_price',
        'purchase_price',
        'description',
        'status'
    ];

    // Ensure ID is visible in JSON
    protected $hidden = [
        'deleted_at'
    ];

    // Cast attributes to their proper types
    protected $casts = [
        'track_stock' => 'boolean',
        'opening_stock' => 'integer',
        'unit_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
    ];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }


    public function stock()
    {
        return $this->hasOne(Stock::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }
}
