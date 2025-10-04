<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'description', 'head_of_department'];

    public function classes()
    {
        return $this->hasMany(ClassModel::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
}
