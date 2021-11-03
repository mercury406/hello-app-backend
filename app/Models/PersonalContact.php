<?php

namespace App\Models;

use App\Models\Greeting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PersonalContact extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Location of contact
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function location() {
        return $this->hasOne(Location::class, 'contact_id', 'contact_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function greetings() {
        return $this->hasMany(Greeting::class, 'from', 'contact_id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner() {
        return $this->belongsTo(ContactOwner::class, 'uid', 'contact_id');
    }
}
