<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRecord;
use App\Models\MaintenanceSchedule;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MaintenanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of maintenance records.
     */
    public function index(Request $request)
    {
        // Redirect to the new maintenance schedules controller
        return redirect()->route('admin.maintenance-schedules.index');
    }

    /**
     * Show the form for creating a new maintenance record.
     */
    public function create()
    {
        // Redirect to the new maintenance schedules controller
        return redirect()->route('admin.maintenance-schedules.create');
    }

    /**
     * Store a newly created maintenance record.
     */
    public function store(Request $request)
    {
        // Redirect to the new maintenance schedules controller
        return redirect()->route('admin.maintenance-schedules.index');
    }

    /**
     * Display the specified maintenance record.
     */
    public function show(MaintenanceRecord $maintenance)
    {
        // Redirect to the new maintenance schedules controller
        return redirect()->route('admin.maintenance-schedules.index');
    }

    /**
     * Show the form for editing the specified maintenance record.
     */
    public function edit(MaintenanceRecord $maintenance)
    {
        // Redirect to the new maintenance schedules controller
        return redirect()->route('admin.maintenance-schedules.index');
    }

    /**
     * Update the specified maintenance record.
     */
    public function update(Request $request, MaintenanceRecord $maintenance)
    {
        // Redirect to the new maintenance schedules controller
        return redirect()->route('admin.maintenance-schedules.index');
    }

    /**
     * Remove the specified maintenance record.
     */
    public function destroy(MaintenanceRecord $maintenance)
    {
        // Redirect to the new maintenance schedules controller
        return redirect()->route('admin.maintenance-schedules.index');
    }

    /**
     * Display maintenance schedule form
     */
    public function schedule(Request $request)
    {
        // Redirect to the new maintenance schedules controller
        return redirect()->route('admin.maintenance.schedule');
    }
}
