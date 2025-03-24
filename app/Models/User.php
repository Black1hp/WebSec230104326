<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'credit',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'credit' => 'decimal:2',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function purchasedProducts()
    {
        return $this->belongsToMany(Product::class, 'purchases')
            ->withPivot('quantity', 'price_paid')
            ->withTimestamps();
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isEmployee()
    {
        return $this->role === 'employee';
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    public function hasSufficientCredit($amount)
    {
        return $this->credit >= $amount;
    }

    public function deductCredit($amount)
    {
        if ($this->hasSufficientCredit($amount)) {
            $this->credit -= $amount;
            $this->save();
            return true;
        }
        return false;
    }

    public function addCredit($amount)
    {
        $this->credit += $amount;
        $this->save();
        return true;
    }
}
