<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use App\Models\AuditLog;

class LogSuccessfulLogout
{
    public function handle(Logout $event): void
    {
        $user = $event->user;

        if ($user) {
            AuditLog::log(
                action: 'logout',
                description: "User {$user->name} logged out",
                modelType: get_class($user),
                modelId: $user->getKey(),
            );
        }
    }
}
