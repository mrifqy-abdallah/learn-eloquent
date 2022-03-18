<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hamster extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function pivotUsers()
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }
}
