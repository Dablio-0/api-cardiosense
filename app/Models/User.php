<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'sex',
        'date_birth'
    ];

    public static $arraySex = ['FATHER', 'MOTHER', 'SPOUSE', 'CHILD', 'GRANDPARENT', 'GRANDCHILD'];

    public static function getArraySex(){
        return self::$arraySex;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /* Relations */

    // Module Family Management
    public function familyRelationships(){
        return $this->hasMany(FamilyRelationships::class, 'user_id');
    }

    public function familyRelationshipsRelated(){
        return $this->hasMany(FamilyRelationships::class, 'user_related_id');
    }

    public function families(){
        return $this->hasOne(Family::class, 'user_adm_id');
    }
}
