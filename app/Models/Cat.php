<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cat extends Model
{
    use HasFactory;

    /**
     * Bind query to 'age' greater than x
     * 
     * Function name must start with 'scope'
     * Call it statically, like so:
     * 
     * Cat::ageGreaterThan(6)
     */
    function scopeAgeGreaterThan($query, $age) {
        return $query->where('age', '>', $age);
    }

    
    /**
     * Bind query to 'age' greater than x
     * Same idea as above, but with different code sintax
     * 
     * Call it like so:
     * 
     * Cat::staticAgeGreaterThan(6)
     */
    public static function staticAgeGreaterThan($age) {
        return self::where('age', '>', $age);
    }

    
    /**
     * Return scopeAgeGreaterThan with value of '6'
     * 
     * Call it like so:
     * 
     * (new Cat)->catsRequiringAntiRabbitBiteShot()
     */
    function catsRequiringAntiRabbitBiteShot(){
        return $this->ageGreaterThan(6);
    }
}
