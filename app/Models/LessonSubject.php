<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LessonSubject extends Model
{
    use HasFactory;
    use Uuids;

    protected $table = "lesson_subjects";

    /**
    * The attributes that are mass assignable.
    *
    * @var array<int, string>ß
    */
    protected $fillable = [
        'parent_id',
        'learner_id',
        'learner_lesson_id',
        'education_level_id',
        'education_level_subject_id',
        'learner_lesson_tutor_gender',
        'learner_lesson_tutor_type',
        'learner_lesson_status',
        'tutor_id',
        'tutor_lesson_status',
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
