<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LessonSubjectTimetable extends Model
{
    use HasFactory;
    use Uuids;

    protected $table = "lesson_subjects_timetable";

    /**
    * The attributes that are mass assignable.
    *
    * @var array<int, string>ß
    */
    protected $fillable = [
        'lesson_subject_id',
        'lesson_day_id',
        'lesson_day_hours',
        'lesson_day_start_time',
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
