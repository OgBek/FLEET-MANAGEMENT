<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Feedback;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::with(['brand', 'type'])
            ->where('status', 'available')
            ->latest()
            ->take(3)
            ->get();

        $testimonials = Feedback::with('user')
            ->where('is_approved', true)
            ->where('is_public', true)
            ->where('rating', '>=', 4)
            ->latest()
            ->take(3)
            ->get();

        return view('welcome', compact('vehicles', 'testimonials'));
    }
} 