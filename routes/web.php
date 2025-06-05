<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Driver\DashboardController as DriverDashboardController;
use App\Http\Controllers\Maintenance\DashboardController as MaintenanceDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Admin\VehicleCategoryController;
use App\Http\Controllers\Admin\VehicleTypeController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\MaintenanceScheduleController;
use App\Http\Controllers\Admin\ServiceRequestController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DepartmentHeadDashboardController;
use App\Http\Middleware\CheckRole;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\MechanicController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Client\TripController;
use App\Http\Controllers\Client\DriverController;
use App\Http\Controllers\Admin\FeedbackController;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Driver\NotificationController as DriverNotificationController;
use App\Http\Controllers\Maintenance\NotificationController as MaintenanceNotificationController;
use App\Http\Controllers\Client\NotificationController as ClientNotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// Multi-session authentication routes
Route::middleware(['auth'])->group(function () {
    Route::get('/session-selector', [App\Http\Controllers\MultiSessionController::class, 'showSelector'])->name('session.selector');
    Route::post('/session-set', [App\Http\Controllers\MultiSessionController::class, 'setSession'])->name('session.set');
    Route::post('/session-clear', [App\Http\Controllers\MultiSessionController::class, 'clearSession'])->name('session.clear');
    Route::post('/session-switch', [App\Http\Controllers\MultiSessionController::class, 'switchSession'])->name('session.switch');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
    Route::get('register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);
    
    // Security Questions Routes for Registration
    Route::get('security-questions/setup/{userId}', [App\Http\Controllers\Auth\SecurityQuestionController::class, 'showSetupForm'])->name('security-questions.setup');
    Route::post('security-questions/store/{userId}', [App\Http\Controllers\Auth\SecurityQuestionController::class, 'store'])->name('security-questions.store');
    
    // Password Reset with Security Questions
    Route::get('password/reset/security', [App\Http\Controllers\Auth\PasswordResetController::class, 'showForgotForm'])->name('password.security.request');
    Route::post('password/reset/security', [App\Http\Controllers\Auth\PasswordResetController::class, 'findAccount'])->name('password.security.email');
    Route::post('password/reset/security/verify', [App\Http\Controllers\Auth\PasswordResetController::class, 'verifyAnswers'])->name('password.security.verify');
    Route::get('password/reset/security/{token}', [App\Http\Controllers\Auth\PasswordResetController::class, 'showResetForm'])->name('password.security.reset');
    Route::post('password/reset/security/update', [App\Http\Controllers\Auth\PasswordResetController::class, 'reset'])->name('password.security.update');
    Route::get('password/reset/contact', [App\Http\Controllers\Auth\PasswordResetController::class, 'showContactInfo'])->name('password.contact');
});

Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Main Dashboard Route - Will redirect based on role
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'check.approval'])->name('dashboard');

// Admin Routes
Route::middleware(['auth', 'check.approval', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Test route for checking notification recipients
    Route::get('dashboard/test-notification-users', function() {
        $user = auth()->user();
        $userRoles = $user->getRoleNames();
        
        $users = \App\Models\User::role(['department_head', 'department_staff'])->get();
        return response()->json([
            'current_user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $userRoles
            ],
            'department_users' => $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),
                    'department' => $user->department ? $user->department->name : null
                ];
            })
        ]);
    })->name('test-notification-users');
    
    // Notification routes
    Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/mark-as-read', [AdminNotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [AdminNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::delete('/notifications/{notification}', [AdminNotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications', [AdminNotificationController::class, 'clearAll'])->name('notifications.clear-all');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Admin Security Questions Routes
    Route::get('/profile/security-questions/update', [App\Http\Controllers\Auth\SecurityQuestionController::class, 'showUpdateForm'])->name('profile.security-questions.update');
    Route::post('/profile/security-questions/update', [App\Http\Controllers\Auth\SecurityQuestionController::class, 'update'])->name('profile.security-questions.update.post');
    
    // Users Management
    Route::resource('users', UserController::class);
    Route::post('users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
    Route::post('users/{user}/reject', [UserController::class, 'reject'])->name('users.reject');
    
    // Vehicle Management
    Route::resource('vehicles', VehicleController::class);
    Route::delete('vehicles/{vehicle}/image', [VehicleController::class, 'removeImage'])->name('vehicles.remove-image');
    Route::resource('vehicle-categories', VehicleCategoryController::class);
    // Route::resource('vehicle-types', VehicleTypeController::class);
    // Route::resource('vehicle-brands', VehicleBrandController::class);
    Route::resource('vehicle-reports', \App\Http\Controllers\Admin\VehicleReportController::class);
    Route::patch('vehicle-reports/{id}/status', [\App\Http\Controllers\Admin\VehicleReportController::class, 'updateStatus'])->name('vehicle-reports.update-status');
    Route::post('vehicle-reports/{id}/complete', [\App\Http\Controllers\Admin\VehicleReportController::class, 'complete'])->name('vehicle-reports.complete');
    
    // Booking Management
    Route::post('bookings/{booking}/approve', [BookingController::class, 'approve'])->name('bookings.approve');
    Route::post('bookings/{booking}/reject', [BookingController::class, 'reject'])->name('bookings.reject');
    Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::resource('bookings', BookingController::class);
    
    // Maintenance Management
    Route::get('maintenance/schedule', [MaintenanceScheduleController::class, 'schedule'])->name('maintenance.schedule');
    Route::get('maintenance', [MaintenanceScheduleController::class, 'redirectToSchedules'])->name('maintenance.index');
    Route::get('maintenance/create', [MaintenanceScheduleController::class, 'create'])->name('maintenance.create');
    Route::post('maintenance', [MaintenanceScheduleController::class, 'store'])->name('maintenance.store');
    Route::get('maintenance/{id}/edit', [MaintenanceScheduleController::class, 'editRedirect'])->name('maintenance.edit');
    Route::put('maintenance/{id}', [MaintenanceScheduleController::class, 'update'])->name('maintenance.update');
    Route::delete('maintenance/{id}', [MaintenanceScheduleController::class, 'destroy'])->name('maintenance.destroy');
    
    // Maintenance Schedules
    Route::resource('maintenance-schedules', MaintenanceScheduleController::class);
    
    // Department Management
    Route::resource('departments', DepartmentController::class);
    
    // Vehicle Brands Management
    Route::resource('vehicle-brands', \App\Http\Controllers\Admin\VehicleBrandController::class)->except(['show']);

    // Mechanics Management
    Route::resource('mechanics', MechanicController::class);

    // Feedback Management
    Route::resource('feedback', FeedbackController::class);
    Route::post('feedback/{feedback}/approve', [FeedbackController::class, 'approve'])->name('feedback.approve');
    Route::post('feedback/{feedback}/reject', [FeedbackController::class, 'reject'])->name('feedback.reject');
    Route::get('feedback/create', [FeedbackController::class, 'create'])->name('feedback.create');
    Route::post('feedback', [FeedbackController::class, 'store'])->name('feedback.store');

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/bookings', [ReportController::class, 'bookings'])->name('bookings');
        Route::get('/maintenance', [ReportController::class, 'maintenance'])->name('maintenance');
        Route::get('/vehicles', [ReportController::class, 'vehicles'])->name('vehicles');
        Route::get('/departments', [ReportController::class, 'departments'])->name('departments');
        Route::get('/users', [ReportController::class, 'users'])->name('users');
        Route::get('/export/{type}', [ReportController::class, 'export'])->name('export');
    });

    Route::delete('/vehicles/{vehicle}/photo', [VehicleController::class, 'deletePhoto'])->name('vehicles.delete-photo');

    // Service Requests
    Route::resource('service-requests', ServiceRequestController::class);
    Route::get('service-requests/create', [ServiceRequestController::class, 'create'])->name('service-requests.create');
    Route::post('service-requests', [ServiceRequestController::class, 'store'])->name('service-requests.store');
    Route::post('service-requests/{serviceRequest}/approve', [ServiceRequestController::class, 'approve'])->name('service-requests.approve');
    Route::post('service-requests/{serviceRequest}/reject', [ServiceRequestController::class, 'reject'])->name('service-requests.reject');
});

// Driver Routes
Route::middleware(['auth', 'role:driver', 'check.approval'])->prefix('driver')->name('driver.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Driver\DashboardController::class, 'index'])->name('dashboard');
    
    // Notification routes
    Route::get('/notifications', [DriverNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}', [DriverNotificationController::class, 'show'])->name('notifications.show');
    Route::post('/notifications/{notification}/mark-as-read', [DriverNotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [DriverNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::delete('/notifications/{notification}', [DriverNotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications', [DriverNotificationController::class, 'clearAll'])->name('notifications.clear-all');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Exit Clearance Tickets
    Route::get('/exit-clearance-tickets', [\App\Http\Controllers\Driver\BookingController::class, 'exitClearanceTickets'])
        ->name('tickets.index');
    Route::get('/exit-clearance-tickets/{ticketNumber}', [\App\Http\Controllers\Driver\BookingController::class, 'showExitClearanceTicket'])
        ->name('tickets.show');

    // Driver Security Questions Routes
    Route::get('/profile/security-questions/update', [App\Http\Controllers\Auth\SecurityQuestionController::class, 'showUpdateForm'])->name('profile.security-questions.update');
    Route::post('/profile/security-questions/update', [App\Http\Controllers\Auth\SecurityQuestionController::class, 'update'])->name('profile.security-questions.update.post');
    
    Route::get('/schedule', [App\Http\Controllers\Driver\ScheduleController::class, 'schedule'])->name('schedule');
    Route::get('/trips', [App\Http\Controllers\Driver\TripController::class, 'index'])->name('trips.index');
    Route::get('/trips/{trip}', [App\Http\Controllers\Driver\TripController::class, 'show'])->name('trips.show');
    Route::post('/trips/{booking}/start', [App\Http\Controllers\Driver\TripController::class, 'start'])->name('trips.start');
    Route::post('/trips/{booking}/complete', [App\Http\Controllers\Driver\TripController::class, 'complete'])->name('trips.complete');

    // Feedback routes
    Route::get('/feedback', [App\Http\Controllers\Driver\FeedbackController::class, 'index'])->name('feedback.index');
    Route::get('/feedback/{feedback}', [App\Http\Controllers\Driver\FeedbackController::class, 'show'])->name('feedback.show');

    // Vehicle Routes
    Route::get('/vehicles', [\App\Http\Controllers\Driver\VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/vehicles/{vehicle}', [\App\Http\Controllers\Driver\VehicleController::class, 'show'])->name('vehicles.show');

    // Vehicle Reports
    Route::resource('vehicle-reports', App\Http\Controllers\Driver\VehicleReportController::class);
});

// Client (Department Head/Staff) Routes
Route::middleware(['auth', 'check.approval'])->prefix('client')->name('client.')->group(function () {
    // Routes accessible by both department_head and department_staff
    Route::middleware(['role:department_head,department_staff'])->group(function () {
        Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::delete('/profile/photo', [ProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');
        
        // Client Security Questions Routes
        Route::get('/profile/security-questions/update', [App\Http\Controllers\Auth\SecurityQuestionController::class, 'showUpdateForm'])->name('profile.security-questions.update');
        Route::post('/profile/security-questions/update', [App\Http\Controllers\Auth\SecurityQuestionController::class, 'update'])->name('profile.security-questions.update.post');
        
        // Vehicle routes
        Route::get('/vehicles', [App\Http\Controllers\Client\VehicleController::class, 'index'])->name('vehicles.index');
        Route::get('/vehicles/{vehicle}', [App\Http\Controllers\Client\VehicleController::class, 'show'])->name('vehicles.show');
    });
    
    // Notification routes
    Route::get('/notifications', [ClientNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}', [ClientNotificationController::class, 'show'])->name('notifications.show');
    Route::post('/notifications/{notification}/mark-as-read', [ClientNotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [ClientNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::delete('/notifications/{notification}', [ClientNotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications', [ClientNotificationController::class, 'clearAll'])->name('notifications.clear-all');
    
    Route::resource('bookings', App\Http\Controllers\Client\BookingController::class);
    Route::get('bookings/check-availability/{vehicleId}', [App\Http\Controllers\Client\BookingController::class, 'checkAvailability'])->name('bookings.check-availability');
    Route::resource('approvals', App\Http\Controllers\Client\ApprovalController::class);
    Route::post('approvals/{booking}/approve', [App\Http\Controllers\Client\ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('approvals/{booking}/reject', [App\Http\Controllers\Client\ApprovalController::class, 'reject'])->name('approvals.reject');
    Route::put('bookings/{booking}/cancel', [App\Http\Controllers\Client\BookingController::class, 'cancel'])->name('bookings.cancel');
    
    // Trip routes
    Route::post('trips/{booking}/start', [TripController::class, 'start'])->name('trips.start');
    Route::post('trips/{booking}/complete', [TripController::class, 'complete'])->name('trips.complete');
    
    // Driver routes
    Route::post('drivers/toggle-availability', [DriverController::class, 'toggleAvailability'])->name('drivers.toggle-availability');
    
    // Feedback routes
    Route::resource('feedback', App\Http\Controllers\Client\FeedbackController::class);
});

// Maintenance Staff Routes
Route::middleware(['auth', 'role:maintenance_staff', 'check.approval'])->prefix('maintenance')->name('maintenance.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Maintenance\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Maintenance Security Questions Routes
    Route::get('/profile/security-questions/update', [App\Http\Controllers\Auth\SecurityQuestionController::class, 'showUpdateForm'])->name('profile.security-questions.update');
    Route::post('/profile/security-questions/update', [App\Http\Controllers\Auth\SecurityQuestionController::class, 'update'])->name('profile.security-questions.update.post');
    
    // Notification routes
    Route::get('/notifications', [MaintenanceNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}', [MaintenanceNotificationController::class, 'show'])->name('notifications.show');
    Route::post('/notifications/{notification}/mark-as-read', [MaintenanceNotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [MaintenanceNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::delete('/notifications/{notification}', [MaintenanceNotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications', [MaintenanceNotificationController::class, 'clearAll'])->name('notifications.clear-all');
    
    Route::get('/schedule', [App\Http\Controllers\Maintenance\ScheduleController::class, 'index'])->name('schedule');
    Route::get('/schedules', [App\Http\Controllers\Maintenance\ScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/schedules/create', [App\Http\Controllers\Maintenance\ScheduleController::class, 'create'])->name('schedules.create');
    Route::post('/schedules', [App\Http\Controllers\Maintenance\ScheduleController::class, 'store'])->name('schedules.store');
    Route::get('/schedules/{schedule}', [App\Http\Controllers\Maintenance\ScheduleController::class, 'show'])->name('schedules.show');
    Route::get('/schedules/{schedule}/edit', [App\Http\Controllers\Maintenance\ScheduleController::class, 'edit'])->name('schedules.edit');
    Route::post('/schedules/{schedule}/start', [App\Http\Controllers\Maintenance\ScheduleController::class, 'start'])->name('schedules.start');
    Route::post('/schedules/{schedule}/complete', [App\Http\Controllers\Maintenance\ScheduleController::class, 'complete'])->name('schedules.complete');
    
    Route::get('/tasks', [\App\Http\Controllers\Maintenance\TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/create', [\App\Http\Controllers\Maintenance\TaskController::class, 'create'])->name('tasks.create');
    Route::post('/tasks', [\App\Http\Controllers\Maintenance\TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}', [\App\Http\Controllers\Maintenance\TaskController::class, 'show'])->name('tasks.show');
    Route::get('/tasks/{task}/edit', [\App\Http\Controllers\Maintenance\TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{task}', [\App\Http\Controllers\Maintenance\TaskController::class, 'update'])->name('tasks.update');
    Route::post('tasks/{task}/complete', [\App\Http\Controllers\Maintenance\TaskController::class, 'complete'])->name('tasks.complete');
    Route::post('tasks/{task}/start', [\App\Http\Controllers\Maintenance\TaskController::class, 'start'])->name('tasks.start');
    
    // Service Requests
    Route::resource('service-requests', \App\Http\Controllers\Maintenance\ServiceRequestController::class);
    Route::post('service-requests/{serviceRequest}/start', [\App\Http\Controllers\Maintenance\ServiceRequestController::class, 'startWork'])->name('service-requests.start');
    Route::post('service-requests/{serviceRequest}/complete', [\App\Http\Controllers\Maintenance\ServiceRequestController::class, 'complete'])->name('service-requests.complete');
    
    // Vehicle Reports
    Route::get('vehicle-reports', [\App\Http\Controllers\Maintenance\VehicleReportController::class, 'index'])->name('vehicle-reports.index');
    Route::get('vehicle-reports/{id}', [\App\Http\Controllers\Maintenance\VehicleReportController::class, 'show'])->name('vehicle-reports.show');
    Route::patch('vehicle-reports/{id}/status', [\App\Http\Controllers\Maintenance\VehicleReportController::class, 'updateStatus'])->name('vehicle-reports.update-status');
});

// Storage file access route
Route::get('storage/{path}', function($path) {
    $storagePath = storage_path('app/public/' . $path);
    if (!file_exists($storagePath)) {
        abort(404);
    }
    
    // Get file extension
    $extension = pathinfo($storagePath, PATHINFO_EXTENSION);
    
    // Set content type based on file extension
    $contentType = match(strtolower($extension)) {
        'jpg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        default => 'application/octet-stream',
    };
    
    return response()->file($storagePath, [
        'Content-Type' => $contentType,
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, OPTIONS',
        'Access-Control-Allow-Headers' => 'Origin, Content-Type, Accept',
        'Cache-Control' => 'public, max-age=3600',
    ]);
})->where('path', '.*')->name('storage.show');

// Storage file serving route
Route::get('/storage/{path}', function (string $path) {
    // Verify the file exists
    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }

    $file = Storage::disk('public')->path($path);
    $mimeType = Storage::disk('public')->mimeType($path);

    return response()->file($file, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=3600',
    ]);
})->where('path', '.*')->name('storage.serve');

// Notification Routes - This is now handled by role-specific routes above
// Admin: /admin/notifications/*
// Driver: /driver/notifications/*
// Client: /client/notifications/*
// Maintenance: /maintenance/notifications/*

// Test Notifications Route (Remove in production)
Route::get('/test-notifications', function () {
    $user = auth()->user();
    
    // Test booking notification
    $booking = \App\Models\Booking::first();
    if ($booking) {
        $user->notify(new \App\Notifications\BookingStatusUpdated(
            $booking,
            'pending',
            'approved',
            'Your booking has been approved by the administrator'
        ));
    }
    
    // Test new vehicle notification
    $vehicle = \App\Models\Vehicle::first();
    if ($vehicle) {
        $user->notify(new \App\Notifications\NewVehicleNotification($vehicle));
    }
    
    // Test driver assignment
    if ($booking && $user->hasRole('driver')) {
        $user->notify(new \App\Notifications\BookingAssignedToDriver($booking));
    }
    
    return redirect()->back()->with('success', 'Test notifications sent!');
})->middleware(['auth', 'role:admin'])->name('test-notifications');
