<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MaintenanceSchedule;
use App\Models\MaintenanceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MechanicController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of mechanics.
     */
    public function index(Request $request)
    {
        // Start with a base query
        $query = User::role('maintenance_staff')->with([
            'maintenanceRecords' => function($query) {
                $query->select('id', 'maintenance_staff_id', 'status')
                    ->where('status', 'completed');
            },
            'maintenanceSchedules' => function($query) {
                $query->select('id', 'assigned_to', 'status')
                    ->where('status', 'pending');
            }
        ]);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('specialization', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Specialization filter
        if ($request->filled('specialization')) {
            $query->where('specialization', $request->specialization);
        }

        // Get mechanics with pagination
        $mechanics = $query->latest()->paginate(5)->withQueryString();

        // Calculate stats using proper role querying
        $stats = [
            'total_mechanics' => User::role('maintenance_staff')->count(),
            'active_tasks' => MaintenanceSchedule::where('status', 'pending')->count(),
            'completed_tasks' => MaintenanceRecord::where('status', 'completed')->count()
        ];

        // Get unique specializations for filter
        $specializations = User::role('maintenance_staff')
            ->distinct()
            ->pluck('specialization');

        return view('admin.mechanics.index', compact('mechanics', 'stats', 'specializations'));
    }

    /**
     * Show the form for creating a new mechanic.
     */
    public function create()
    {
        return view('admin.mechanics.create');
    }

    /**
     * Store a newly created mechanic.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised(), 
                'confirmed'
            ],
            'phone' => ['required', 'string', 'max:20'],
            'specialization' => ['required', 'string', 'max:255']
        ]);

        try {
            DB::beginTransaction();

            $now = now();
            $mechanic = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'specialization' => $request->specialization,
                'status' => 'active',
                'approval_status' => 'approved',
                'approved_at' => $now,
                'status_changed_at' => $now,
                'last_active_at' => $now
            ]);

            $mechanic->assignRole('maintenance_staff');

            DB::commit();

            return redirect()->route('admin.mechanics.show', $mechanic)
                ->with('status', 'Mechanic created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create mechanic. Please try again.');
        }
    }

    /**
     * Display the specified mechanic.
     */
    public function show($id)
    {
        $mechanic = $this->getMechanic($id);

        $mechanic->load([
            'maintenanceRecords' => function($query) {
                $query->with(['vehicle.brand', 'vehicle.type'])
                    ->latest()
                    ->take(5);
            },
            'maintenanceSchedules' => function($query) {
                $query->with(['vehicle.brand', 'vehicle.type'])
                    ->where('status', 'pending')
                    ->orderBy('scheduled_date');
            }
        ]);

        return view('admin.mechanics.show', compact('mechanic'));
    }

    /**
     * Show the form for editing the specified mechanic.
     */
    public function edit($id)
    {
        $mechanic = $this->getMechanic($id);
        return view('admin.mechanics.edit', compact('mechanic'));
    }

    /**
     * Update the specified mechanic.
     */
    public function update(Request $request, $id)
    {
        $mechanic = $this->getMechanic($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $mechanic->id],
            'phone' => ['required', 'string', 'max:20'],
            'specialization' => ['required', 'string', 'max:255'],
            'status' => ['sometimes', 'required', 'in:active,inactive']
        ]);

        try {
            DB::beginTransaction();

            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'specialization' => $request->specialization,
            ];

            // Update status if changed
            if ($request->filled('status') && $request->status !== $mechanic->status) {
                $updateData['status'] = $request->status;
                $updateData['status_changed_at'] = now();
            }

            $mechanic->update($updateData);

            if ($request->filled('password')) {
                $request->validate([
                    'password' => ['string', Password::min(8)
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                        ->uncompromised(), 
                        'confirmed'
                    ]
                ]);
                
                $mechanic->update([
                    'password' => Hash::make($request->password)
                ]);
            }

            DB::commit();

            return redirect()->route('admin.mechanics.show', $mechanic)
                ->with('status', 'Mechanic updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update mechanic. Please try again.');
        }
    }

    /**
     * Remove the specified mechanic.
     */
    public function destroy($id)
    {
        $mechanic = $this->getMechanic($id);

        if ($mechanic->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Check if mechanic has any active tasks
        if ($mechanic->maintenanceSchedules()->where('status', 'pending')->exists()) {
            return back()->with('error', 'Cannot delete mechanic with pending maintenance tasks. Please reassign or complete the tasks first.');
        }

        try {
            DB::beginTransaction();

            // Archive completed maintenance records
            MaintenanceRecord::where('mechanic_id', $mechanic->id)
                ->update(['archived_mechanic_name' => $mechanic->name]);

            // Remove role first
            $mechanic->removeRole('maintenance_staff');
            $mechanic->delete();

            DB::commit();

            return redirect()->route('admin.mechanics.index')
                ->with('status', 'Mechanic deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete mechanic. Please try again.');
        }
    }

    /**
     * Toggle mechanic status.
     */
    public function toggleStatus($id)
    {
        $mechanic = $this->getMechanic($id);
        
        if ($mechanic->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own status.');
        }

        $newStatus = $mechanic->status === 'active' ? 'inactive' : 'active';
        
        try {
            DB::beginTransaction();

            // If being set to inactive, check for pending tasks
            if ($newStatus === 'inactive') {
                $pendingTasks = $mechanic->maintenanceSchedules()
                    ->where('status', 'pending')
                    ->count();

                if ($pendingTasks > 0) {
                    return back()->with('error', 
                        "Cannot deactivate mechanic with {$pendingTasks} pending tasks. " .
                        "Please reassign or complete the tasks first."
                    );
                }
            }

            $mechanic->update([
                'status' => $newStatus,
                'status_changed_at' => now()
            ]);

            DB::commit();
            return back()->with('status', "Mechanic status updated to {$newStatus}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update mechanic status.');
        }
    }

    /**
     * Reassign tasks to another mechanic.
     */
    public function reassignTasks(Request $request, $id)
    {
        $mechanic = $this->getMechanic($id);
        
        $request->validate([
            'new_mechanic_id' => ['required', 'exists:users,id'],
            'task_ids' => ['required', 'array'],
            'task_ids.*' => ['exists:maintenance_schedules,id']
        ]);

        $newMechanic = $this->getMechanic($request->new_mechanic_id);

        if ($newMechanic->status !== 'active') {
            return back()->with('error', 'Cannot reassign tasks to an inactive mechanic.');
        }

        try {
            DB::beginTransaction();

            MaintenanceSchedule::whereIn('id', $request->task_ids)
                ->where('mechanic_id', $mechanic->id)
                ->where('status', 'pending')
                ->update([
                    'mechanic_id' => $newMechanic->id,
                    'reassigned_at' => Carbon::now(),
                    'reassigned_by' => auth()->id(),
                    'reassignment_notes' => $request->notes
                ]);

            DB::commit();
            return back()->with('status', 'Tasks reassigned successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reassign tasks.');
        }
    }

    /**
     * Get mechanic user with role verification.
     */
    private function getMechanic($id)
    {
        $mechanic = User::role('maintenance_staff')->find($id);

        if (!$mechanic) {
            abort(404, 'Mechanic not found');
        }

        return $mechanic;
    }
} 