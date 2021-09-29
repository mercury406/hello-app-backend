<?php

namespace App\Models;

use App\Models\Location;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactOwner extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = "contact_owners";

    public function contacts()
    {
        return $this->hasMany(Contact::class, "owner_uid");
    }

    protected $primaryKey = "uid";
    protected $keyType = "string";

    public function locations() {
        return $this->hasMany(Location::class, "contact_id");
    }

    // public function lastOne() {
    //     return $this->locations()->order_by('created_at', "DESC")->first();
    // }

}