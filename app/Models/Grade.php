<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $table = 'grades';

    // Fill this array with columns you want to mass assign
    protected $fillable = [
        'user_id',
        'subject',
        'mark', // changed from 'score'
    ];
}
