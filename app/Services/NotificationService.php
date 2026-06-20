<?php

namespace App\Services;

use App\Models\User;

class NotificationService
{
    public function send(User $user, string $type, string $message, ?string $link = null): void
    {
        $user->appNotifications()->create([
            'type' => $type,
            'message' => $message,
            'link' => $link,
        ]);
    }
}
