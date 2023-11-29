<?php

namespace App\Helpers;


use Illuminate\Support\Facades\DB;
use Nette\Utils\Helpers;

class Lesson extends Helpers{

    private static function core($my_learners, $lesson_subject_id, $type, $lesson_subject_timetable_id){
        $learners = [];
        foreach($my_learners as $my_learner){

            $learner['name'] = $my_learner->learners_name;
            $learner['dob'] = $my_learner->learners_dob;
            $learner['gender'] = $my_learner->learners_gender;
            if($type == 1){

                if($lesson_subject_id === false){
                    $learner_lessons = DB::table('lesson_learner As ll')
                    ->leftJoin('lessons As ls', function($join){
                        $join->on('ll.lesson_id', '=', 'ls.id');
                    })
                    ->leftJoin('lesson_subjects As subject', function($join){
                        $join->on('ll.id', '=', 'subject.lesson_learner_id');
                    })
                    ->leftJoin('parent_user As parent', function($join){
                        $join->on('parent.id', '=', 'ls.parent_id');
                    })
                    ->leftJoin('tutors As tutor', function($join){
                        $join->on('tutor.id', '=', 'ls.tutor_id');
                    })
                    ->leftJoin('users As uu', function($join){
                        $join->on('uu.id', '=', 'tutor.user_id');
                    })
                    ->leftJoin('users As u', function($join){
                        $join->on('u.id', '=', 'parent.user_id');
                    })
                    ->where(
                    [
                            ['ll.learner_id', '=', $my_learner->id]
                        ]
                    )
                    ->select(
                        'subject.learner_tutor_gender as tutor_gender', 'subject.learner_tutor_type as tutor_type', 'subject.id as lesson_subject_id', 
                        'll.id as lesson_learner_id', 'll.learners_description as learners_description', 'ls.lesson_address as lesson_address',  'ls.id as lesson_id', 
                        'll.lesson_commence as lesson_commence', 'ls.lesson_goals as lesson_goals', 'ls.lesson_mode as lesson_mode', 'ls.lesson_period as lesson_period',
                        'u.email as parent_email', 'u.address as parent_address', 'u.id as parent_id', 'uu.email as tutor_email', 'uu.address as tutor_address', 'uu.id as tutor_id'
                    )
                    ->get();
                }else{
    
                    $learner_lessons = DB::table('lesson_learner As ll')
                    ->leftJoin('lessons As ls', function($join){
                        $join->on('ll.lesson_id', '=', 'ls.id');
                    })
                    ->leftJoin('lesson_subjects As subject', function($join){
                        $join->on('ll.id', '=', 'subject.lesson_learner_id');
                    })
                    ->leftJoin('parent_user As parent', function($join){
                        $join->on('parent.id', '=', 'ls.parent_id');
                    })
                    ->leftJoin('users As u', function($join){
                        $join->on('u.id', '=', 'parent.user_id');
                    })
                    ->where(
                    [
                            ['ll.learner_id', '=', $my_learner->id],
                            ['subject.id', '=', $lesson_subject_id],
                        ]
                    )
                    ->select(
                        'subject.learner_tutor_gender as tutor_gender', 'subject.learner_tutor_type as tutor_type', 
                        'll.id as lesson_learner_id', 'll.learners_description as learners_description', 'ls.lesson_address as lesson_address',  'ls.id as lesson_id', 
                        'll.lesson_commence as lesson_commence', 'ls.lesson_goals as lesson_goals', 'ls.lesson_mode as lesson_mode', 'ls.lesson_period as lesson_period',
                        'u.email as parent_email', 'u.address as parent_address', 'u.id as parent_id'
                    )
                    ->get();
                }
            }elseif($type == 2){
                $learner_lessons = DB::table('lesson_learner As ll')
           
                ->leftJoin('lessons As ls', function($join){
                    $join->on('ll.lesson_id', '=', 'ls.id');
                })
                ->leftJoin('lesson_subjects As subject', function($join){
                    $join->on('ll.id', '=', 'subject.lesson_learner_id');
                })
                ->leftJoin('lesson_subjects_timetable As lst', function($join){
                    $join->on('lst.lesson_subject_id', '=', 'subject.id');
                })
                ->leftJoin('parent_user As parent', function($join){
                    $join->on('parent.id', '=', 'ls.parent_id');
                })
                ->leftJoin('users As u', function($join){
                    $join->on('u.id', '=', 'parent.user_id');
                })
                
                ->where(
                [
                        ['ll.learner_id', '=', $my_learner->id],
                        ['lst.id', '=', $lesson_subject_timetable_id],
                    ]
                )
                ->select(
                    'subject.learner_tutor_gender as tutor_gender', 'subject.learner_tutor_type as tutor_type', 
                    'll.id as lesson_learner_id', 'll.learners_description as learners_description', 'ls.lesson_address as lesson_address', 'ls.id as lesson_id', 
                    'll.lesson_commence as lesson_commence', 'ls.lesson_goals as lesson_goals', 'ls.lesson_mode as lesson_mode', 'ls.lesson_period as lesson_period',
                    'u.email as parent_email', 'u.address as parent_address', 'u.id as parent_id'
                )
                ->get();
            }elseif($type == 3){
                $learner_lessons = DB::table('lesson_learner As ll')
           
                ->leftJoin('lessons As ls', function($join){
                    $join->on('ll.lesson_id', '=', 'ls.id');
                })
                ->leftJoin('lesson_subjects As subject', function($join){
                    $join->on('ll.id', '=', 'subject.lesson_learner_id');
                })
                ->leftJoin('parent_user As parent', function($join){
                    $join->on('parent.id', '=', 'ls.parent_id');
                })
                ->leftJoin('users As u', function($join){
                    $join->on('u.id', '=', 'parent.user_id');
                })
                ->where(
                [
                        ['ll.learner_id', '=', $my_learner->learner_id],
                    ]
                )
                ->select(
                    'subject.learner_tutor_gender as tutor_gender', 'subject.learner_tutor_type as tutor_type', 
                    'll.id as lesson_learner_id', 'll.learners_description as learners_description', 'ls.lesson_address as lesson_address', 'ls.id as lesson_id', 
                    'll.lesson_commence as lesson_commence', 'ls.lesson_goals as lesson_goals', 'ls.lesson_mode as lesson_mode', 'ls.lesson_period as lesson_period',
                    'u.email as parent_email', 'u.address as parent_address', 'u.id as parent_id'
                )
                ->get();
            }elseif($type == 4){
                $learner_lessons = DB::table('lesson_learner As ll')
           
                ->leftJoin('lessons As ls', function($join){
                    $join->on('ll.lesson_id', '=', 'ls.id');
                })
                ->leftJoin('lesson_subjects As subject', function($join){
                    $join->on('ll.id', '=', 'subject.lesson_learner_id');
                })
                ->leftJoin('parent_user As parent', function($join){
                    $join->on('parent.id', '=', 'ls.parent_id');
                })
                ->leftJoin('users As u', function($join){
                    $join->on('u.id', '=', 'parent.user_id');
                })
                ->where(
                [
                        ['ll.learner_id', '=', $my_learner->learner_id],
                        ['subject.id', '=', $lesson_subject_id],
                    ]
                )
                ->select(
                    'subject.learner_tutor_gender as tutor_gender', 'subject.learner_tutor_type as tutor_type', 
                    'll.id as lesson_learner_id', 'll.learners_description as learners_description', 'ls.lesson_address as lesson_address',  'ls.id as lesson_id', 
                    'll.lesson_commence as lesson_commence', 'ls.lesson_goals as lesson_goals', 'ls.lesson_mode as lesson_mode', 'ls.lesson_period as lesson_period',
                    'u.email as parent_email', 'u.address as parent_address', 'u.id as parent_id'
                )
                ->get();
            }


            $pack = 0;

            foreach($learner_lessons as $learner_lesson){
                $pack += 1;
                $my_subjects = DB::table('lesson_subjects As ls')
                ->leftJoin('tutors As t', function($join){
                    $join->on('ls.tutor_id', '=', 't.id');
                })
                ->leftJoin('users As u', function($join){
                    $join->on('t.user_id', '=', 'u.id');
                })
                ->leftJoin('subjects As s', function($join){
                    $join->on('ls.subject_id', '=', 's.id');
                })
                ->leftJoin('education_levels As el', function($join){
                    $join->on('s.education_levels_id', '=', 'el.id');
                })
                ->where(
                    [
                        ['ls.lesson_learner_id', '=', $learner_lesson->lesson_learner_id],
                    ]
                )
                ->select(
                    't.user_id as tutor_user_id', 'u.first_name as tutor_firstname', 'u.last_name as tutor_lastname', 
                    'u.phone as tutor_contact', 's.name as subject_name', 'el.name as level_name',
                    'ls.learner_status as parent_status', 'ls.tutor_status as tutor_status', 'ls.id as lesson_subject_id',
                    
                )->get();


                foreach($my_subjects as $my_subject){

                    if($my_subject->parent_status === 'completed' && $my_subject->tutor_status === 'completed' ){
                        $learner_lesson->subjects['completed']['subject_'.$pack]['details'] =  $my_subject;
                    }else{
                        $learner_lesson->subjects['pending']['subject_'.$pack]['details'] =  $my_subject;
                    }


                    $my_periods = DB::table('lesson_subjects_timetable As lst')
                    ->leftJoin('lesson_day As ld', function($join){
                        $join->on('lst.lesson_day_id', '=', 'ld.id');
                    })
                    ->where(
                        [
                            ['lst.lesson_subject_id', '=', $my_subject->lesson_subject_id],
                        ]
                    )
                    ->select(
                        'lst.lesson_day_hours as lesson_hours', 'lst.lesson_day_start_time as lesson_starts_by', 
                        'lst.id as lesson_subject_timetable_id', 'ld.day_name as day_name'
                    )->get();

                    if($my_subject->parent_status === 'completed' && $my_subject->tutor_status === 'completed' ){
                        $learner_lesson->subjects['completed']['subject_'.$pack]['periods'] =  $my_periods;
                    }else{
                        $learner_lesson->subjects['pending']['subject_'.$pack]['periods'] =  $my_periods;
                    }

                    $feedback = DB::table('lesson_feedback as lf')
                    ->leftJoin('users As u', function($join){
                        $join->on('u.id', '=', 'lf.user_id');
                    })
                
                    ->where(
                        [
                            ['lf.lesson_subject_id', '=', $my_subject->lesson_subject_id],
                        ]
                    )->select(
                        'u.first_name as user_firstname', 'u.last_name as user_lastname', 'u.user_type', 'u.id as user_id',
                        'lf.feedback', 'lf.id as feedback_id',
                    )->get();

                    $feedbacks = [];
    
                    foreach($feedback as $feedback_value){
                        $packey = [];
                        $packey['feedback'] = $feedback_value;
                        $feedbacks_reply = DB::table('lesson_feedback_reply as lfr')
                        ->leftJoin('users As u', function($join){
                            $join->on('u.id', '=', 'lfr.user_id');
                        })
                        ->where(
                            [
                                ['lfr.feedback_id', '=',  $feedback_value->feedback_id],
                            ]
                        )
                        ->orWhere(
                            [
                                ['lfr.feedback_id', '=',  $feedback_value->feedback_id],
                            ]
                        )
                        ->select(
                            'u.first_name as user_firstname', 'u.last_name as user_lastname', 'u.user_type', 'u.id as user_id',
                            'lfr.response_reply', 'lfr.id as feedback_reply_id',
                        )->get();
        
                        $packey['reply'] = $feedbacks_reply;
                        array_push($feedbacks, $packey);
                    }
                    if($my_subject->parent_status === 'completed' && $my_subject->tutor_status === 'completed' ){
                        $learner_lesson->subjects['completed']['subject_'.$pack]['feedbacks'] =  $feedbacks;
                    }else{
                        $learner_lesson->subjects['pending']['subject_'.$pack]['feedbacks'] =  $feedbacks;
                    }
                }
            }
            $learner['lessons'] = isset($learner_lesson)?$learner_lesson:[];
            if(array_search($learner, $learners) === false){
                array_push($learners, $learner);
            }
        }
        return $learners;
    }

    public static function dashboard($parent_id){
    
        $my_learners = DB::table('learners As l')
        ->where(
            [
                ['parent_id', '=', $parent_id],
            ]
        )->get();

       
        $learners = Lesson::core($my_learners, false, 1, false);
        return $learners;
    }


    public static function lessons($parent_id, $lesson_subject_id){
    
        $my_learners = DB::table('learners As l')
        ->where(
            [
                ['parent_id', '=', $parent_id],
            ]
        )->get();

        $learners = [];

        $learners = Lesson::core($my_learners, $lesson_subject_id, 1, false);

        return $learners;
    }

    public static function lesson($parent_id, $lesson_subject_timetable_id){
    
        $my_learners = DB::table('learners As l')
        ->where(
            [
                ['parent_id', '=', $parent_id],
            ]
        )->get();

        $learners = [];

        $learners = Lesson::core($my_learners, false, 2, $lesson_subject_timetable_id);

        return $learners;
    }

    public static function tutor($tutor_id){
    
        $my_learners = DB::table('lesson_subjects As ls')
        ->leftJoin('lesson_learner As ll', function($join){
            $join->on('ll.id', '=', 'ls.lesson_learner_id');
        })
        ->leftJoin('learners As lns', function($join){
            $join->on('lns.id', '=', 'll.learner_id');
        })
        ->where(
            [
                ['ls.tutor_id', '=', $tutor_id],
            ]
        )
        ->select('lns.id as learner_id', 'lns.learners_name as learners_name', 'lns.learners_gender as learners_gender', 'lns.learners_dob as learners_dob')
        ->get();

        $learners = Lesson::core($my_learners, false, 3, false);
        
        return $learners;
    }

    public static function tutor_lessons($tutor_id, $lesson_subject_id){
    
        $my_learners = DB::table('lesson_subjects As ls')
        ->leftJoin('lesson_learner As ll', function($join){
            $join->on('ll.id', '=', 'ls.lesson_learner_id');
        })
        ->leftJoin('learners As lns', function($join){
            $join->on('lns.id', '=', 'll.learner_id');
        })
        ->where(
            [
                ['ls.tutor_id', '=', $tutor_id],
                ['ls.id', '=', $lesson_subject_id],

            ]
        )
        ->select('lns.id as learner_id', 'lns.learners_name as learners_name', 'lns.learners_gender as learners_gender', 'lns.learners_dob as learners_dob')
        ->get();

        $learners = Lesson::core($my_learners, $lesson_subject_id, 4, false);
        return $learners;
    }
}