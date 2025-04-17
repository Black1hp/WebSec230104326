<?php
<<<<<<< HEAD

=======
>>>>>>> Midterm-v2
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

<<<<<<< HEAD
class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'code',
        'name',
        'price',
=======
class Product extends Model  {

	protected $fillable = [
        'code',
        'name',
        'price',
        'amount',
>>>>>>> Midterm-v2
        'model',
        'description',
        'photo'
    ];
<<<<<<< HEAD
}
=======
    
    /**
     * Get the purchases for the product.
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
>>>>>>> Midterm-v2
