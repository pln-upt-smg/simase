<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DataImported extends Notification implements ShouldQueue
{
    use Queueable;

    private string $title, $description, $time;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $name, ?int $count = null)
    {
        $name = trim($name);
        $count = is_null($count) ? '' : " sebanyak $count baris";
        $this->title = "$name berhasil diimpor";
        $this->description = "Anda telah berhasil mengimpor data $name$count.";
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via(): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description
        ];
    }
}
