<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['name', 'code', 'description', 'department_id'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function classes()
    {
        return $this->belongsToMany(ClassModel::class, 'class_subject')
                    ->withPivot('teacher_id')
                    ->withTimestamps();
    }
}
