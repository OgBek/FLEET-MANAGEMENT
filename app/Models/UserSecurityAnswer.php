<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\SecurityQuestion;

class UserSecurityAnswer extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'security_question_id',
        'answer',
        'hashed_answer',
    ];
    
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'hashed_answer',
    ];
    
    /**
     * Get the user that owns the security answer.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the security question that owns the answer.
     */
    public function securityQuestion()
    {
        return $this->belongsTo(SecurityQuestion::class);
    }
}
