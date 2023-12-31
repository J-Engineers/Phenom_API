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
        'lesson_learner_id',
        'subject_id',
        'learner_tutor_gender',
        'learner_tutor_type',
        'learner_status',
        'tutor_id',
        'tutor_status',
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
