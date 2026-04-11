<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RSSImportError extends Notification
{
    public function __construct(
        public readonly string $feedName,
        public readonly string $feedUrl,
        public readonly string $errorMessage,
    ) {}

    /**
     * @return array<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('RSS Feed Import Failed: '.$this->feedName)
            ->line('The RSS feed import failed for: '.$this->feedName)
            ->line('Feed URL: '.$this->feedUrl)
            ->line('Error: '.$this->errorMessage);
    }
}
