<?php

use App\Http\Middleware\ActiveUser;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Tutors\TutorController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\Parent\ParantController;
use App\Http\Controllers\Admin\BookStoreController;
use App\Http\Controllers\Tutors\TutorAuthController;
use App\Http\Controllers\Parent\ParentAuthController;
use App\Http\Controllers\Admin\GreatSchoolsController;
use App\Http\Controllers\Schools\GreatSchoolController;
use App\Http\Controllers\Admin\LevelsSubjectsController;
use App\Http\Controllers\BookStore\RequestBookController;
use App\Http\Controllers\BookStore\BookStoreGuestController;
use App\Http\Controllers\Schools\GreatSchoolGuestController;
use App\Http\Controllers\BookStore\RegisterBookStoreController;
use App\Http\Controllers\Schools\StateLocalGovernmentController;
use App\Http\Controllers\Admin\TutorController as AdminTutorController;
use App\Http\Controllers\Admin\ParentController as AdminParentController;

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
Route::post('/v1/bookshop/registration', [RegisterBookStoreController::class, 'signup'])->name('book.store.signup');

Route::get('/v1/public/levels', [PublicController::class, 'viewLevels'])->name('levels.view');
Route::get('/v1/public/level', [PublicController::class, 'viewLevel'])->name('level.view');
Route::get('/v1/public/subjects', [PublicController::class, 'viewSubjects'])->name('subjects.view');
Route::get('/v1/public/subject', [PublicController::class, 'viewSubject'])->name('subject.view');
Route::get('/v1/public/level/subject', [PublicController::class, 'viewLevelSubject'])->name('levelsubject.view');
Route::get('/v1/public/lesson/days', [PublicController::class, 'viewLessonDays'])->name('lessondays.view');
Route::get('/v1/public/bookstore/categories', [BookStoreGuestController::class, 'bookstoreCategories'])->name('bookstore.categories');
Route::get('/v1/public/bookstore/books/search', [BookStoreGuestController::class, 'books_search'])->name('bookstore.books_search');
Route::get('/v1/public/bookstore/books', [BookStoreGuestController::class, 'books'])->name('bookstore.books');
Route::get('/v1/public/bookstore/book', [BookStoreGuestController::class, 'book'])->name('bookstore.book');
Route::post('/v1/public/bookstore/request/random', [RequestBookController::class, 'request_book_random'])->name('bookstore.request_book_random');
Route::post('/v1/public/bookstore/request', [RequestBookController::class, 'request_book'])->name('bookstore.request');

Route::get('/v1/public/schools/statelga', [StateLocalGovernmentController::class, 'data'])->name('schools.state.lga');
Route::get('/v1/public/schools/rated', [GreatSchoolGuestController::class, 'toprated'])->name('schools.toprated');
Route::get('/v1/public/schools/search', [GreatSchoolGuestController::class, 'search'])->name('schools.search');
Route::post('/v1/public/schools/request', [GreatSchoolGuestController::class, 'request'])->name('schools.request');


Route::post('/v1/user/registration/verify', [AuthController::class, 'send_registration_verification_email'])->name('user.verify')->middleware(ActiveUser::class);
Route::post('/v1/user/login', [AuthController::class, 'login'])->name('user.login')->middleware(ActiveUser::class);
Route::post('/v1/user/password/forgot', [AuthController::class, 'forgotPassword'])->name('user.password.forgot')->middleware(ActiveUser::class);
Route::post('/v1/user/password/reset', [AuthController::class, 'resetPassword'])->name('user.password.reset')->middleware(ActiveUser::class);



Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/v1/user/logout', [AuthController::class, 'logout'])->name('user.logout');
    Route::group(['middleware' => ['ActiveUser']], function(){

        // Admin - Users Routes
        Route::get('/v1/admin/users', [AdminController::class, 'getUsers'])->name('users');
        Route::get('/v1/admin/user', [AdminController::class, 'getUser'])->name('user');
        Route::delete('/v1/admin/user/delete', [AdminController::class, 'removeUser'])->name('user.remove');
        Route::get('/v1/admin/user/deactivate', [AdminController::class, 'deactivateUser'])->name('user.deactivate');
        Route::get('/v1/admin/user/activate', [AdminController::class, 'activateUser'])->name('user.activate');
        Route::get('/v1/admin/make/admin', [AdminController::class, 'makeAdmin'])->name('user.makeAdmin');
        Route::get('/v1/admin/cancel/admin', [AdminController::class, 'cancelAdmin'])->name('user.cancelAdmin');

        // Admin - Education Levels Routes
        Route::post('/v1/admin/level', [LevelsSubjectsController::class, 'createLevel'])->name('level.create');
        Route::put('/v1/admin/level', [LevelsSubjectsController::class, 'editLevel'])->name('level.edit');
        Route::delete('/v1/admin/level', [LevelsSubjectsController::class, 'deleteLevel'])->name('level.delete');

        // Admin - Subject Routes
        Route::post('/v1/admin/subject', [LevelsSubjectsController::class, 'createSubject'])->name('subjects.create');
        Route::put('/v1/admin/subject', [LevelsSubjectsController::class, 'editSubject'])->name('subjects.edit');
        Route::delete('/v1/admin/subject', [LevelsSubjectsController::class, 'deleteSubject'])->name('subjects.delete');

        // Admin - Lesson Day Routes
        Route::post('/v1/admin/lessonday', [LevelsSubjectsController::class, 'createDay'])->name('day.create');
        Route::put('/v1/admin/lessonday', [LevelsSubjectsController::class, 'editDay'])->name('day.edit');
        Route::delete('/v1/admin/lessonday', [LevelsSubjectsController::class, 'deleteDay'])->name('day.delete');
    

        // Admin - Tutors Routes
        Route::get('/v1/admin/tutors', [AdminTutorController::class, 'getTutors'])->name('tutors');
        Route::get('/v1/admin/tutor', [AdminTutorController::class, 'getTutorDetails'])->name('tutor.details');
        Route::post('/v1/admin/tutor/approve', [AdminTutorController::class, 'approveTutor'])->name('tutor.approve');
        Route::post('/v1/admin/tutor/decline', [AdminTutorController::class, 'declineTutor'])->name('tutor.decline');
        Route::post('/v1/admin/tutor/subject', [LevelsSubjectsController::class, 'addSubjectToTutor'])->name('tutor.subjects.create');
        Route::put('/v1/admin/tutor/subject', [LevelsSubjectsController::class, 'editTutorSubject'])->name('tutor.subjects.edit');
        Route::delete('/v1/admin/tutor/subject', [LevelsSubjectsController::class, 'deleteTutorSubject'])->name('tutor.subjects.delete');
        Route::post('/v1/admin/tutor/search', [AdminTutorController::class, 'searchTutor'])->name('tutor.search');
        
        
        // Admin - Parent Routes
        Route::get('/v1/admin/parents', [AdminParentController::class, 'getParents'])->name('parent');
        Route::get('/v1/admin/parent', [AdminParentController::class, 'getParentDetails'])->name('parent.details');
        Route::post('/v1/admin/parent/search', [AdminParentController::class, 'searchParent'])->name('parent.search');


        // Admin - Lesson Routes

        Route::get('/v1/admin/lessons', [LessonController::class, 'lessons'])->name('admin.learners.lessons');
        Route::get('/v1/admin/lesson', [LessonController::class, 'lesson'])->name('admin.learners.lesson');
        Route::post('/v1/admin/lesson/feedback', [LessonController::class, 'feedback'])->name('admin.lesson.feedback');
        Route::post('/v1/admin/lesson/feedback/reply', [LessonController::class, 'feedback_reply'])->name('admin.lesson.feedback.reply');
        Route::put('/v1/admin/lesson/close', [LessonController::class, 'complete_lesson'])->name('admin.lesson.close');
        Route::post('/v1/admin/lesson/create', [LessonController::class, 'add_lesson'])->name('admin.lesson.create');
        Route::delete('/v1/admin/lesson/remove', [LessonController::class, 'remove_lesson'])->name('admin.lesson.remove');
        Route::put('/v1/admin/lesson/tutor', [LessonController::class, 'add_tutor'])->name('admin.lesson.merge');
        Route::put('/v1/admin/lesson/tutor/remove', [LessonController::class, 'remove_tutor'])->name('admin.lesson.unmerge');
        Route::put('/v1/admin/lesson/tutor/replace', [LessonController::class, 'replace_tutor'])->name('admin.lesson.replace');


        // Admin - Book Store Routes
        
        Route::get('/v1/admin/bookstores', [BookStoreController::class, 'bookstores'])->name('bookshop.stores');
        Route::get('/v1/admin/bookstore', [BookStoreController::class, 'bookstore'])->name('bookshop.store');
        Route::delete('/v1/admin/bookstore', [BookStoreController::class, 'removebookstore'])->name('bookshop.store.remove');
        Route::get('/v1/admin/bookstore/categories', [BookStoreController::class, 'bookstoreCategories'])->name('bookshop.categories');
        Route::get('/v1/admin/bookstore/category', [BookStoreController::class, 'bookstoreCategory'])->name('bookshop.category');
        Route::put('/v1/admin/bookstore/category', [BookStoreController::class, 'bookstoreCategoryUpdate'])->name('bookshop.category.update');
        Route::delete('/v1/admin/bookstore/category', [BookStoreController::class, 'bookstoreCategoryRemove'])->name('bookshop.category.remove');
        Route::post('/v1/admin/bookstore/category', [BookStoreController::class, 'bookstoreAddCategory'])->name('bookshop.category.add');
        Route::get('/v1/admin/bookstore/requests', [BookStoreController::class, 'bookstorerequests'])->name('bookshop.requests');
        Route::get('/v1/admin/bookstore/request', [BookStoreController::class, 'bookstorerequest'])->name('bookshop.request');
        Route::get('/v1/admin/bookstore/user/request', [BookStoreController::class, 'bookrequestbookstore'])->name('bookshop.requests.bookstore');
        Route::get('/v1/admin/bookstore/request/randoms', [BookStoreController::class, 'bookrequests'])->name('bookshop.requests.bookrequests');
        Route::get('/v1/admin/bookstore/request/random', [BookStoreController::class, 'bookrequest'])->name('bookshop.requests.bookrequest');
        Route::get('/v1/admin/bookstore/books', [BookStoreController::class, 'bookstorebooks'])->name('bookshop.requests.bookstorebooks');
        Route::get('/v1/admin/bookstore/book', [BookStoreController::class, 'bookstorebook'])->name('bookshop.requests.bookstorebook');
        Route::post('/v1/admin/bookstore/book', [BookStoreController::class, 'bookstorebookapprove'])->name('bookshop.requests.bookstorebookapprove');
        Route::put('/v1/admin/bookstore/book', [BookStoreController::class, 'bookstorebookrevoke'])->name('bookshop.requests.bookstorebookrevoke');
        Route::delete('/v1/admin/bookstore/book', [BookStoreController::class, 'bookstorebookremove'])->name('bookshop.requests.bookstorebookremove');
        Route::post('/v1/admin/bookstore/request', [BookStoreController::class, 'completeRequest'])->name('bookshop.requests.completeRequest');
        Route::put('/v1/admin/bookstore/request', [BookStoreController::class, 'reopenRequest'])->name('bookshop.requests.reopenRequest');
        Route::delete('/v1/admin/bookstore/request', [BookStoreController::class, 'removeRequest'])->name('bookshop.requests.removeRequest');
        Route::post('/v1/admin/bookstore/randomrequest', [BookStoreController::class, 'completeRequest1'])->name('bookshop.requests.completeRequest1');
        Route::put('/v1/admin/bookstore/randomrequest', [BookStoreController::class, 'reopenRequest1'])->name('bookshop.requests.reopenRequest1');
        Route::delete('/v1/admin/bookstore/randomrequest', [BookStoreController::class, 'removeRequest1'])->name('bookshop.requests.removeRequest1');


        // admin - Great Schools Routes
        
        Route::get('/v1/admin/schools', [GreatSchoolsController::class, 'schools'])->name('schools.schools');
        Route::post('/v1/admin/school', [GreatSchoolsController::class, 'signup'])->name('schools.school.add');
        Route::get('/v1/admin/school', [GreatSchoolsController::class, 'school'])->name('schools.school.view');
        Route::put('/v1/admin/school', [GreatSchoolsController::class, 'school_updates'])->name('schools.school.updates');
        Route::delete('/v1/admin/school', [GreatSchoolsController::class, 'school_remove'])->name('schools.school.remove');
        Route::post('/v1/admin/school/approve', [GreatSchoolsController::class, 'school_approve'])->name('schools.school.school_approve');
        Route::post('/v1/admin/school/decline', [GreatSchoolsController::class, 'school_disapprove'])->name('schools.school.school_disapprove');
        Route::get('/v1/admin/school/requests', [GreatSchoolsController::class, 'schoool_requests'])->name('schools.school.schoool_requests');
        Route::get('/v1/admin/school/request', [GreatSchoolsController::class, 'schoool_request'])->name('schools.school.schoool_request');
        Route::post('/v1/admin/school/request', [GreatSchoolsController::class, 'remove_schoool_request'])->name('schools.school.remove_schoool_request');
        Route::post('/v1/admin/school/rate', [GreatSchoolsController::class, 'school_rate'])->name('schools.school.school_rate');
        




        // Users Routes
        Route::get('/v1/user', [ProfileController::class, 'details'])->name('user.details');
        Route::put('/v1/user/password', [ProfileController::class, 'changePassword'])->name('user.password');
        Route::put('/v1/user/update', [ProfileController::class, 'updateDetails'])->name('user.update');
        Route::post('/v1/user/photo', [ProfileController::class, 'updatePhoto'])->name('user.photo');

        // Tutors Routes
        Route::get('/v1/tutor', [TutorController::class, 'details'])->name('tutor.details');
        Route::get('/v1/tutor/lesson', [TutorController::class, 'lesson'])->name('tutor.learners.lesson');
        Route::post('/v1/tutor/lesson/feedback', [TutorController::class, 'feedback'])->name('tutor.lesson.feedback');
        Route::post('/v1/tutor/lesson/feedback/reply', [TutorController::class, 'feedback_reply'])->name('tutor.lesson.feedback.reply');
        Route::put('/v1/tutor/lesson/close', [TutorController::class, 'complete_lesson'])->name('tutor.lesson.close');
        Route::put('/v1/tutor/activity/active', [TutorController::class, 'activity_badge'])->name('tutor.activity.active');
        Route::put('/v1/tutor/activity/inactive', [TutorController::class, 'activity_badge_remove'])->name('tutor.activity.inactive');
        Route::post('/v1/tutor/certificate', [TutorController::class, 'add_certification'])->name('tutor.certificate');
        
        

        // Parent Routes
        Route::group(['middleware' => 'ParentsAuthRoutes'], function(){
            Route::get('/v1/parent', [ParantController::class, 'details'])->name('parent.details');
            Route::get('/v1/parent/lessons', [ParantController::class, 'lessons'])->name('parent.learners.lessons');
            Route::get('/v1/parent/lesson', [ParantController::class, 'lesson'])->name('parent.learners.lesson');
            Route::post('/v1/parent/lesson/feedback', [ParantController::class, 'feedback'])->name('parent.lesson.feedback');
            Route::post('/v1/parent/lesson/feedback/reply', [ParantController::class, 'feedback_reply'])->name('parent.lesson.feedback.reply');
            Route::put('/v1/parent/lesson/close', [ParantController::class, 'complete_lesson'])->name('parent.lesson.close');
            Route::post('/v1/parent/lesson/create', [ParantController::class, 'add_lesson'])->name('parent.lesson.create');
            Route::delete('/v1/parent/lesson/remove', [ParantController::class, 'remove_lesson'])->name('parent.lesson.remove');
            Route::post('/v1/parent/learner/create', [ParantController::class, 'add_learner'])->name('parent.learner.create');
            Route::delete('/v1/parent/learner/remove', [ParantController::class, 'remove_learner'])->name('parent.learner.remove');
            Route::get('/v1/parent/learners', [ParantController::class, 'learners'])->name('parent.learners');
            Route::get('/v1/parent/learner', [ParantController::class, 'learner'])->name('parent.learner');
            Route::post('/v1/parent/lesson/learner/create', [ParantController::class, 'add_learner_to_lesson'])->name('parent.lesson.learner.add');
            Route::delete('/v1/parent/lesson/learner/remove', [ParantController::class, 'remove_learner_from_lesson'])->name('parent.lesson.learner.remove');
            Route::post('/v1/parent/lesson/learner/subject/add', [ParantController::class, 'add_lesson_subject'])->name('parent.lesson.learner.subject.add');
        });

        // Book Store Routes
        Route::group(['middleware' => 'BookStoreAuthRoutes'], function(){
            Route::get('/v1/bookstore/books', [RegisterBookStoreController::class, 'books'])->name('bookstore.books');
            Route::get('/v1/bookstore/book', [RegisterBookStoreController::class, 'book'])->name('bookstore.book');
            Route::put('/v1/bookstore/book', [RegisterBookStoreController::class, 'book_updates'])->name('bookstore.book.update');
            Route::post('/v1/bookstore/book', [RegisterBookStoreController::class, 'book_add'])->name('bookstore.book.add');
            Route::delete('/v1/bookstore/book', [RegisterBookStoreController::class, 'book_remove'])->name('bookstore.book.remove');
        });


        // Schools Route
        Route::group(['middleware' => 'SchoolAuthRoutes'], function(){
            Route::get('/v1/schools/dashboard', [GreatSchoolController::class, 'dashboard'])->name('schools.dashboard');
            Route::get('/v1/schools/request', [GreatSchoolController::class, 'request'])->name('schools.request');
            Route::put('/v1/schools/approve', [GreatSchoolController::class, 'approve_request'])->name('schools.approve_request');
            Route::post('/v1/schools/decline', [GreatSchoolController::class, 'decline_request'])->name('schools.decline_request');
        });
    
    });
});