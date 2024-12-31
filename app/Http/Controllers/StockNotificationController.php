<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockNotification;

class StockNotificationController extends Controller
{
    public function index()
    {
        // Fetch all notifications ordered by latest
        $notifications = StockNotification::orderBy('created_at', 'desc')->get();
        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        // Mark a specific notification as read
        $notification = StockNotification::findOrFail($id);
        $notification->update(['read' => true]);

        return redirect()->route('notifications.index')->with('success', 'Notification marked as read.');
    }
}
