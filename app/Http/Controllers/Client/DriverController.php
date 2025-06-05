<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:driver']);
    }

    public function toggleAvailability()
    {
        $user = Auth::user();
        
        // Don't allow toggling if driver is on a trip
        if ($user->hasActiveTrip()) {
            return back()->with('error', 'Cannot change availability while on an active trip.');
        }

        try {
            $user->update([
                'is_available' => !$user->is_available
            ]);

            $status = $user->is_available ? 'available' : 'unavailable';
            return back()->with('success', "You are now marked as {$status}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update availability status.');
        }
    }
}