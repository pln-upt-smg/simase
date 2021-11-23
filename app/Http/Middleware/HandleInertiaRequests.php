<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
	/**
	 * The root template that's loaded on the first page visit.
	 *
	 * @see https://inertiajs.com/server-side-setup#root-template
	 * @var string
	 */
	protected $rootView = 'app';

	/**
	 * Determines the current asset version.
	 *
	 * @see https://inertiajs.com/asset-versioning
	 * @param Request $request
	 * @return string|null
	 */
	public function version(Request $request): ?string
	{
		return parent::version($request);
	}

	/**
	 * Defines the props that are shared by default.
	 *
	 * @see https://inertiajs.com/shared-data
	 * @param Request $request
	 * @return array
	 */
	public function share(Request $request): array
	{
		$notifications = [];
		$unreadNotificationCount = 0;
		if (!is_null(auth()->user())) {
			$notifications = auth()->user()->notifications->take(6)->map(function ($item) {
				return [
					'id' => $item->id,
					'title' => $item->data['title'] ?? null,
					'description' => $item->data['description'] ?? null,
					'time' => $item->created_at->diffForHumans(),
					'created_at' => $item->created_at
				];
			});
			$unreadNotificationCount = auth()->user()->unreadNotifications->count();
		}
		return array_merge(parent::share($request), [
			'notifications' => $notifications,
			'unreadNotificationCount' => $unreadNotificationCount
		]);
	}
}
