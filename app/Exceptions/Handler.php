<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\VehicleReport;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
        
        $this->renderable(function (ModelNotFoundException $e, $request) {
            $modelClass = $e->getModel();
            
            // Handle Vehicle Report not found specifically
            if ($modelClass === VehicleReport::class) {
                $redirectRoute = 'admin.dashboard';
                $message = 'The vehicle report you are looking for has been deleted.';
                
                // Determine appropriate redirect based on user role
                if (auth()->check()) {
                    if (auth()->user()->hasRole('maintenance_staff')) {
                        $redirectRoute = 'maintenance.dashboard';
                    } elseif (auth()->user()->hasRole('driver')) {
                        $redirectRoute = 'driver.dashboard';
                    }
                }
                
                return redirect()->route($redirectRoute)->with('error', $message);
            }
        });
    }
}
