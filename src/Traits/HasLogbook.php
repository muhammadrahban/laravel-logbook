<?php

namespace Rahban\LaravelLogbook\Traits;

use Rahban\LaravelLogbook\Facades\Logbook;

trait HasLogbook
{
    /**
     * Log a custom event
     */
    public function logEvent(string $eventName, array $data = []): void
    {
        Logbook::event($eventName, $data, $this->getKey());
    }

    /**
     * Log user login event
     */
    public function logLogin(array $additionalData = []): void
    {
        $this->logEvent('user.login', array_merge([
            'user_id' => $this->getKey(),
            'email' => $this->email ?? null,
            'timestamp' => now()->toISOString(),
        ], $additionalData));
    }

    /**
     * Log user logout event
     */
    public function logLogout(array $additionalData = []): void
    {
        $this->logEvent('user.logout', array_merge([
            'user_id' => $this->getKey(),
            'email' => $this->email ?? null,
            'timestamp' => now()->toISOString(),
        ], $additionalData));
    }

    /**
     * Log user action
     */
    public function logAction(string $action, array $data = []): void
    {
        $this->logEvent("user.{$action}", array_merge([
            'user_id' => $this->getKey(),
            'action' => $action,
        ], $data));
    }
}
