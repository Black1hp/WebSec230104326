<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'model',
        'price',
        'stock',
        'description',
        'photo'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($product) {
            if (empty($product->code)) {
                // Generate a random code with prefix PROD- followed by 8 random characters
                $product->code = 'PROD-' . strtoupper(substr(md5(uniqid()), 0, 8));
            }
        });
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function buyers()
    {
        return $this->belongsToMany(User::class, 'purchases')
            ->withPivot('quantity', 'price_paid')
            ->withTimestamps();
    }

    public function isInStock()
    {
        return $this->stock > 0;
    }
}
