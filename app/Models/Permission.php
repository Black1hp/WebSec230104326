<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['name', 'feature'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}
