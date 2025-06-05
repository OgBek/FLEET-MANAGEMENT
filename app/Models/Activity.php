<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'subject_type',
        'subject_id',
        'type',
        'description'
    ];

    /**
     * Get the user that performed the activity.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department associated with the activity.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the subject of the activity.
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Create a new activity log entry.
     */
    public static function log($user, $subject, $type, $description)
    {
        return static::create([
            'user_id' => $user->id,
            'department_id' => $user->department_id,
            'subject_type' => get_class($subject),
            'subject_id' => $subject->id,
            'type' => $type,
            'description' => $description,
        ]);
    }
} 