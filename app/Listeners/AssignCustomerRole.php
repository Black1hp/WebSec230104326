<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AssignCustomerRole
{
    public function handle(Registered $event): void
    {
        $user = $event->user;
        $user->role = 'customer';
        $user->save();
    }
}
