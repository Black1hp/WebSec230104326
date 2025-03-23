<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine whether the user can view any products.
     */
    public function viewAny(User $user)
    {
        return true; // Allow all users to view product listings
    }

    /**
     * Determine whether the user can view the product.
     */
    public function view(User $user, Product $product)
    {
        return true; // Allow all users to view individual products
    }

    public function create(User $user)
    {
        return $user->role === 'admin'; // Only admins can create products
    }

    public function update(User $user, Product $product)
    {
        return $user->role === 'admin'; // Only admins can update products
    }

    public function delete(User $user, Product $product)
    {
        return $user->role === 'admin'; // Only admins can delete products
    }
}
