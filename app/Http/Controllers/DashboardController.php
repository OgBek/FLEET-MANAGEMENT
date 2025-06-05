<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return match(true) {
            $user->hasRole('admin') => redirect()->route('admin.dashboard'),
            $user->hasAnyRole(['department_head', 'department_staff']) => redirect()->route('client.dashboard'),
            $user->hasRole('driver') => redirect()->route('driver.dashboard'),
            $user->hasRole('maintenance_staff') => redirect()->route('maintenance.dashboard'),
            default => redirect('/'),
        };
    }
} 