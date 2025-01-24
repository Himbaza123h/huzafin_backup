<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceTrial extends Model
{
    use HasFactory;

    protected $fillable = ['invoice_number', 'receiver'];
}
