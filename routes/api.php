<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\LevelsSubjectsController;
use App\Http\Controllers\Admin\TutorController as AdminTutorController;
use App\Http\Controllers\Admin\ParentController as AdminParentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Parent\ParantController;
use App\Http\Controllers\Parent\ParentAuthController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\Tutors\TutorAuthController;
use App\Http\Controllers\Tutors\TutorController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Middleware\ActiveUser;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



Route::post('/v1/user/registration', [AuthController::class, 'register'])->name('user.register');
Route::post('/v1/tutor/registration', [TutorAuthController::class, 'signup'])->name('tutor.signup');
Route::post('/v1/parent/registration', [ParentAuthController::class, 'signup'])->name('parent.signup');

Route::get('/v1/public/levels', [PublicController::class, 'viewLevels'])->name('levels.view');
Route::get('/v1/public/level', [PublicController::class, 'viewLevel'])->name('level.view');
Route::get('/v1/public/subjects', [PublicController::class, 'viewSubjects'])->name('subjects.view');
Route::get('/v1/public/subject', [PublicController::class, 'viewSubject'])->name('subject.view');
Route::get('/v1/public/level/subject', [PublicController::class, 'viewLevelSubject'])->name('levelsubject.view');
Route::get('/v1/public/lesson/days', [PublicController::class, 'viewLessonDays'])->name('lessondays.view');


Route::post('/v1/user/registration/verify', [AuthController::class, 'send_registration_verification_email'])->name('user.verify')->middleware(ActiveUser::class);
Route::post('/v1/user/login', [AuthController::class, 'login'])->name('user.login')->middleware(ActiveUser::class);
Route::post('/v1/user/password/forgot', [AuthController::class, 'forgotPassword'])->name('user.password.forgot')->middleware(ActiveUser::class);
Route::post('/v1/user/password/reset', [AuthController::class, 'resetPassword'])->name('user.password.reset')->middleware(ActiveUser::class);



Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/v1/user/logout', [AuthController::class, 'logout'])->name('user.logout');

    // Admin - Users Routes
    Route::get('/v1/admin/users', [AdminController::class, 'getUsers'])->name('users')->middleware(ActiveUser::class);
    Route::get('/v1/admin/user', [AdminController::class, 'getUser'])->name('user')->middleware(ActiveUser::class);
    Route::delete('/v1/admin/user/delete', [AdminController::class, 'removeUser'])->name('user.remove')->middleware(ActiveUser::class);
    Route::get('/v1/admin/user/deactivate', [AdminController::class, 'deactivateUser'])->name('user.deactivate')->middleware(ActiveUser::class);
    Route::get('/v1/admin/user/activate', [AdminController::class, 'activateUser'])->name('user.activate')->middleware(ActiveUser::class);
    Route::get('/v1/admin/make/admin', [AdminController::class, 'makeAdmin'])->name('user.makeAdmin')->middleware(ActiveUser::class);
    Route::get('/v1/admin/cancel/admin', [AdminController::class, 'cancelAdmin'])->name('user.cancelAdmin')->middleware(ActiveUser::class);

    // Admin - Education Levels Routes
    Route::post('/v1/admin/level', [LevelsSubjectsController::class, 'createLevel'])->name('level.create')->middleware(ActiveUser::class);
    Route::put('/v1/admin/level', [LevelsSubjectsController::class, 'editLevel'])->name('level.edit')->middleware(ActiveUser::class);
    Route::delete('/v1/admin/level', [LevelsSubjectsController::class, 'deleteLevel'])->name('level.delete')->middleware(ActiveUser::class);

    // Admin - Subject Routes
    Route::post('/v1/admin/subject', [LevelsSubjectsController::class, 'createSubject'])->name('subjects.create')->middleware(ActiveUser::class);
    Route::put('/v1/admin/subject', [LevelsSubjectsController::class, 'editSubject'])->name('subjects.edit')->middleware(ActiveUser::class);
    Route::delete('/v1/admin/subject', [LevelsSubjectsController::class, 'deleteSubject'])->name('subjects.delete')->middleware(ActiveUser::class);

    // Admin - Lesson Day Routes
    Route::post('/v1/admin/lessonday', [LevelsSubjectsController::class, 'createDay'])->name('day.create')->middleware(ActiveUser::class);
    Route::put('/v1/admin/lessonday', [LevelsSubjectsController::class, 'editDay'])->name('day.edit')->middleware(ActiveUser::class);
    Route::delete('/v1/admin/lessonday', [LevelsSubjectsController::class, 'deleteDay'])->name('day.delete')->middleware(ActiveUser::class);
 

    // Admin - Tutors Routes
    Route::get('/v1/admin/tutors', [AdminTutorController::class, 'getTutors'])->name('tutors')->middleware(ActiveUser::class);
    Route::get('/v1/admin/tutor', [AdminTutorController::class, 'getTutorDetails'])->name('tutor.details')->middleware(ActiveUser::class);
    Route::post('/v1/admin/tutor/approve', [AdminTutorController::class, 'approveTutor'])->name('tutor.approve')->middleware(ActiveUser::class);
    Route::post('/v1/admin/tutor/decline', [AdminTutorController::class, 'declineTutor'])->name('tutor.decline')->middleware(ActiveUser::class);
    Route::post('/v1/admin/tutor/subject', [LevelsSubjectsController::class, 'addSubjectToTutor'])->name('tutor.subjects.create')->middleware(ActiveUser::class);
    Route::put('/v1/admin/tutor/subject', [LevelsSubjectsController::class, 'editTutorSubject'])->name('tutor.subjects.edit')->middleware(ActiveUser::class);
    Route::delete('/v1/admin/tutor/subject', [LevelsSubjectsController::class, 'deleteTutorSubject'])->name('tutor.subjects.delete')->middleware(ActiveUser::class);
    Route::post('/v1/admin/tutor/search', [AdminTutorController::class, 'searchTutor'])->name('tutor.search')->middleware(ActiveUser::class);
    
    
    // Admin - Parent Routes
    Route::get('/v1/admin/parents', [AdminParentController::class, 'getParents'])->name('parent')->middleware(ActiveUser::class);
    Route::get('/v1/admin/parent', [AdminParentController::class, 'getParentDetails'])->name('parent.details')->middleware(ActiveUser::class);
    Route::post('/v1/admin/parent/search', [AdminParentController::class, 'searchParent'])->name('parent.search')->middleware(ActiveUser::class);


    // Admin - Lesson Routes

    Route::get('/v1/admin/lesson', [LessonController::class, 'lesson'])->name('admin.learners.lesson')->middleware(ActiveUser::class);
    Route::post('/v1/admin/lesson/feedback', [LessonController::class, 'feedback'])->name('admin.lesson.feedback')->middleware(ActiveUser::class);
    Route::post('/v1/admin/lesson/feedback/reply', [LessonController::class, 'feedback_reply'])->name('admin.lesson.feedback.reply')->middleware(ActiveUser::class);
    Route::put('/v1/admin/lesson/close', [LessonController::class, 'complete_lesson'])->name('admin.lesson.close')->middleware(ActiveUser::class);
    Route::post('/v1/admin/lesson/create', [LessonController::class, 'add_lesson'])->name('admin.lesson.create')->middleware(ActiveUser::class);
    Route::delete('/v1/admin/lesson/remove', [LessonController::class, 'remove_lesson'])->name('admin.lesson.remove')->middleware(ActiveUser::class);
    Route::put('/v1/admin/lesson/tutor', [LessonController::class, 'add_tutor'])->name('admin.lesson.merge')->middleware(ActiveUser::class);
    Route::put('/v1/admin/lesson/tutor/remove', [LessonController::class, 'remove_tutor'])->name('admin.lesson.unmerge')->middleware(ActiveUser::class);
    Route::put('/v1/admin/lesson/tutor/replace', [LessonController::class, 'replace_tutor'])->name('admin.lesson.replace')->middleware(ActiveUser::class);
    

    // Users Routes
    Route::get('/v1/user', [ProfileController::class, 'details'])->name('user.details')->middleware(ActiveUser::class);
    Route::put('/v1/user/password', [ProfileController::class, 'changePassword'])->name('user.password')->middleware(ActiveUser::class);
    Route::put('/v1/user/update', [ProfileController::class, 'updateDetails'])->name('user.update')->middleware(ActiveUser::class);
    Route::post('/v1/user/photo', [ProfileController::class, 'updatePhoto'])->name('user.photo')->middleware(ActiveUser::class);

    // Tutors Routes
    Route::get('/v1/tutor', [TutorController::class, 'details'])->name('tutor.details')->middleware(ActiveUser::class);
    Route::get('/v1/tutor/lesson', [TutorController::class, 'lesson'])->name('tutor.learners.lesson')->middleware(ActiveUser::class);
    Route::post('/v1/tutor/lesson/feedback', [TutorController::class, 'feedback'])->name('tutor.lesson.feedback')->middleware(ActiveUser::class);
    Route::post('/v1/tutor/lesson/feedback/reply', [TutorController::class, 'feedback_reply'])->name('tutor.lesson.feedback.reply')->middleware(ActiveUser::class);
    Route::put('/v1/tutor/lesson/close', [TutorController::class, 'complete_lesson'])->name('tutor.lesson.close')->middleware(ActiveUser::class);
   

    // Parent Routes
    Route::get('/v1/parent', [ParantController::class, 'details'])->name('parent.details')->middleware(ActiveUser::class);
    Route::get('/v1/parent/lesson', [ParantController::class, 'lesson'])->name('parent.learners.lesson')->middleware(ActiveUser::class);
    Route::post('/v1/parent/lesson/feedback', [ParantController::class, 'feedback'])->name('parent.lesson.feedback')->middleware(ActiveUser::class);
    Route::post('/v1/parent/lesson/feedback/reply', [ParantController::class, 'feedback_reply'])->name('parent.lesson.feedback.reply')->middleware(ActiveUser::class);
    Route::put('/v1/parent/lesson/close', [ParantController::class, 'complete_lesson'])->name('parent.lesson.close')->middleware(ActiveUser::class);
    Route::post('/v1/parent/lesson/create', [ParantController::class, 'add_lesson'])->name('parent.lesson.create')->middleware(ActiveUser::class);
    Route::delete('/v1/parent/lesson/remove', [ParantController::class, 'remove_lesson'])->name('parent.lesson.remove')->middleware(ActiveUser::class);
    
});