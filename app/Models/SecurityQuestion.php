<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\UserSecurityAnswer;

class SecurityQuestion extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'question',
        'is_active',
    ];
    
    /**
     * Get the users that have answered this security question.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_security_answers')
            ->withPivot('answer', 'answer_hash')
            ->withTimestamps();
    }
    
    /**
     * Get the answers associated with this security question.
     */
    public function userSecurityAnswers()
    {
        return $this->hasMany(UserSecurityAnswer::class);
    }
}
