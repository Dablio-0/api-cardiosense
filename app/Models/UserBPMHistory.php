<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBPMHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'bpm_interval_average',
        'bpm_interval_max',
        'bpm_interval_min',
        'user_id',
        'family_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function family()
    {
        return $this->belongsTo(Family::class);
    }

}
