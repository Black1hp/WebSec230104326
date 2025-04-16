<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gift extends Model
{
    protected $fillable = [
        'giver_id',
        'receiver_id',
        'amount',
        'message',
        'gift_given_at'
    ];

    protected $casts = [
        'gift_given_at' => 'datetime',
        'amount' => 'decimal:2'
    ];

    /**
     * Get the user who gave the gift.
     */
    public function giver()
    {
        return $this->belongsTo(User::class, 'giver_id');
    }

    /**
     * Get the user who received the gift.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
} 