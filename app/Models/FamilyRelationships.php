<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyRelationships extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_related_id',
        'relationship',
        'family_id'
    ];

    public static $arrayRelation = ['MALE', 'FEMALE', 'OTHER'];

    public static function getArrayRelation(){
        return self::$arrayRelation;
    }

    /* Relations */

    // Module Family Management
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function userRelated(){
        return $this->belongsTo(User::class, 'user_related_id');
    }

    public function family(){
        return $this->belongsTo(Family::class, 'family_id');
    }
}
