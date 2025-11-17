<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    /**
     * Display a listing of alerts.
     */
    public function index(Request $request)
    {
        $query = Alert::with('employee');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('is_read')) {
            $query->where('is_read', $request->is_read === '1');
        }

        $alerts = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('alerts.index', compact('alerts'));
    }

    /**
     * Mark alert as read.
     */
    public function markAsRead(Alert $alert)
    {
        $alert->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Alerte marquée comme lue.',
        ]);
    }

    /**
     * Mark all alerts as read.
     */
    public function markAllAsRead()
    {
        Alert::where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Toutes les alertes ont été marquées comme lues.',
        ]);
    }

    /**
     * Get unread alerts count.
     */
    public function unreadCount()
    {
        $count = Alert::where('is_read', false)->count();

        return response()->json([
            'count' => $count,
        ]);
    }
}
