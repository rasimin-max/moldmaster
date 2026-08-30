<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\AuditLog;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        AuditLog::log(
            action: 'login',
            description: "User {$user->name} logged in",
            modelType: get_class($user),
            modelId: $user->getKey(),
        );
    }
}
