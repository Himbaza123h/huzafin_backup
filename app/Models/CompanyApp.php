<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyApp extends Model
{
    use HasFactory;
    protected $fillable = ['app_id', 'company_id', 'authentication_app', 'access_key', 'username', 'password', 'status'];

    public function companyEndpoints(): HasMany
    {
        return $this->hasMany(CompanyEndpoint::class);
    }
}
