<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GreatSchool extends Model
{
    use HasFactory;
    use Uuids;

    protected $table = "great_schools";


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'phone',
        'name',
        'address',
        'state',
        'picture',
        'localgovernment',
        'description',
        'population',
        'male_or_female_or_both',
        'day_or_boarding_or_both',
        'private_or_government_or_both',
        'token',
        'status',
        'rated',
        'user_id',
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
