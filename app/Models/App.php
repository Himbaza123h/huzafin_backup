<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class App extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'logo', 'status'];

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class);
    }
}
