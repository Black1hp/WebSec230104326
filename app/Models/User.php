<?php

namespace App\Models;

<<<<<<< HEAD
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{

=======
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasRoles;
>>>>>>> Midterm-v2

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
<<<<<<< HEAD
        'role',  // Add this line
=======
        'credit',
        'google_id',
        'google_token',
        'google_refresh_token',
>>>>>>> Midterm-v2
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
<<<<<<< HEAD
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
=======
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'credit' => 'decimal:2',
    ];

    /**
     * Get the purchases for the user.
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get the gifts given by the user.
     */
    public function givenGifts()
    {
        return $this->hasMany(Gift::class, 'giver_id');
    }

    /**
     * Get the gifts received by the user.
     */
    public function receivedGifts()
    {
        return $this->hasMany(Gift::class, 'receiver_id');
    }

    /**
     * Check if user has enough credit for a purchase
     */
    public function hasEnoughCredit($amount)
    {
        return $this->credit >= $amount;
    }

    /**
     * Deduct amount from user's credit
     */
    public function deductCredit($amount)
    {
        if ($this->hasEnoughCredit($amount)) {
            $this->credit -= $amount;
            $this->save();
            return true;
        }
        return false;
>>>>>>> Midterm-v2
    }
}
