<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_family',
        'user_adm_id'
    ];

    /* Relations */

    // Module Family Management
    public function userAdm(){
        return $this->belongsTo(User::class, 'user_adm_id');
    }

    public function familyRelationships(){
        return $this->hasMany(FamilyRelationships::class, 'family_id');
    }

    public function userBPMHistory(){
        return $this->hasMany(UserBPMHistory::class, 'family_id');
    }
}
