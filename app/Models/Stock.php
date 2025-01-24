<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Stock extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'stock';

    protected $fillable = ['product_id', 'quantity', 'opening_stock'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    
}
