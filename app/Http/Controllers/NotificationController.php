<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->paginate(10)->withQueryString();

        return Inertia::render('Notifications/Index', [
            'notifications' => [
                'data' => $notifications->getCollection()->map(function ($n) {
                    return [
                        'id' => $n->id,
                        'type' => $n->type,
                        'data' => $n->data,
                        'read_at' => $n->read_at?->toDateTimeString(),
                        'created_at' => $n->created_at?->toDateTimeString(),
                        'created_at_human' => $n->created_at?->diffForHumans(),
                    ];
                })->values()->all(),
                'meta' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ],
                'links' => [
                    'prev' => $notifications->previousPageUrl(),
                    'next' => $notifications->nextPageUrl(),
                ],
            ],
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, string $id): RedirectResponse
    {
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }

        if ($request->header('X-Inertia')) {
            return redirect()->back();
        }

        return redirect()->route('notifications.index');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        if ($request->header('X-Inertia')) {
            return redirect()->back();
        }

        return redirect()->route('notifications.index');
    }
}
