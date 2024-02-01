<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GreatSchoolRequest extends Model
{
    use HasFactory;
    use Uuids;

    protected $table = "great_school_requests";


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'dob',
        'phone',
        'address',
        'email',
        'gender',
        'description',
        'picture',
        'transcript',
        'prev_school',
        'prev_school_note',
        'token',
        'great_school_id',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        
    ];
}
