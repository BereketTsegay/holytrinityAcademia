<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles, HasApiTokens ;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'address',
        'date_of_birth', 'gender', 'photo'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    // Relationships
    public function teacherClasses()
    {
        return $this->hasMany(ClassModel::class, 'teacher_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'teacher_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function borrowedBooks()
    {
        return $this->hasMany(BookBorrowing::class, 'student_id');
    }

    public function isStudent()
    {
        return $this->hasRole('student');
    }

    public function isTeacher()
    {
        return $this->hasRole('teacher');
    }

    public function isAdmin()
    {
        return $this->hasRole('admin');
    }
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
