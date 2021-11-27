<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DataTruncated extends Notification implements ShouldQueue
{
	use Queueable;

	private string $title, $description;

	/**
	 * Create a new notification instance.
	 *
	 * @param string $name
	 */
	public function __construct(string $name)
	{
		$name = trim($name);
		$this->title = "Semua $name berhasil dihapus";
		$this->description = "Anda telah berhasil menghapus semua data $name.";
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
