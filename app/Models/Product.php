<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model  {

	protected $fillable = [
        'code',
        'name',
        'price',
        'amount',
        'model',
        'description',
        'photo'
    ];
    
    /**
     * Get the purchases for the product.
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
    
    /**
     * Get the count of likes for this product.
     * Only counts likes from purchases that haven't been returned
     */
    public function getLikesCountAttribute()
    {
        return $this->purchases()
            ->where('liked', true)
            ->where('status', '!=', 'returned')
            ->count();
    }
    
    /**
     * Check if the current user has liked this product.
     * Only considers likes from non-returned purchases to be valid
     */
    public function isLikedByUser()
    {
        if (!auth()->check()) {
            return false;
        }
        
        return $this->purchases()
            ->where('user_id', auth()->id())
            ->where('liked', true)
            ->where('status', '!=', 'returned')
            ->exists();
    }
    
    /**
     * Get the active like for this product from the current user
     * Returns the first liked purchase by the user for this product
     * Only considers non-returned purchases
     */
    public function getUserLike()
    {
        if (!auth()->check()) {
            return null;
        }
        
        return $this->purchases()
            ->where('user_id', auth()->id())
            ->where('liked', true)
            ->where('status', '!=', 'returned')
            ->first();
    }
}