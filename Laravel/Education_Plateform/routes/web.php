<?php
// routes/web.php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\InstructorController;
use App\Http\Controllers\Admin\StudentRequestController;
use App\Http\Controllers\CustomAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/courses', [App\Http\Controllers\Admin\CourseController::class, 'indexstudent'])->name("cours"); // Utiliser le contrôleur pour afficher la liste des cours

Route::get('/courses/{id}', [App\Http\Controllers\Admin\CourseController::class, 'show'])->name("cours.show"); // Afficher un cours spécifique en fonction de son ID



Route::get('/register', [CustomAuthController::class, 'registration'])->name('register');
Route::post('/register', [CustomAuthController::class, 'customRegistration']);

Route::get('/login', [CustomAuthController::class, 'index'])->name('login');
Route::post('/login', [CustomAuthController::class, 'customLogin'])->name('login.custom');

Route::post('/logout', [CustomAuthController::class, 'signOut'])->name('logout');

// Ajouter la route pour la page de profil
Route::get('/profile', [CustomAuthController::class, 'profile'])->name('profile');




// admin 

Route::prefix('admin')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('admin.categories.create');
    Route::post('/categories/store', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::get('/categories/{id}', [CategoryController::class, 'show'])->name('admin.categories.show');
    Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/categories/{id}/update', [CategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('/categories/{id}/destroy', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');
});


// instructor 
Route::prefix('admin')->group(function () {
    Route::get('/instructors', [InstructorController::class, 'index'])->name('admin.instructors.index');
    Route::get('/instructors/create', [InstructorController::class, 'create'])->name('admin.instructors.create');
    Route::post('/instructors/store', [InstructorController::class, 'store'])->name('admin.instructors.store');
    Route::get('/instructors/{id}', [InstructorController::class, 'show'])->name('admin.instructors.show');
    Route::get('/instructors/{id}/edit', [InstructorController::class, 'edit'])->name('admin.instructors.edit');
    Route::put('/instructors/{id}/update', [InstructorController::class, 'update'])->name('admin.instructors.update');
    Route::delete('/instructors/{id}/destroy', [InstructorController::class, 'destroy'])->name('admin.instructors.destroy');
});


//  StudentRequestController
Route::prefix('admin')->group(function () {
    Route::get('/student-requests', [StudentRequestController::class, 'index'])->name('student_requests.index');
    Route::put('/student-requests/{id}/accept', [StudentRequestController::class, 'accept'])->name('student_requests.accept');
    Route::put('/student-requests/{id}/reject', [StudentRequestController::class, 'reject'])->name('student_requests.reject');
});


// cours 

// Routes pour le contrôleur CourseController
Route::prefix('admin')->group(function () {
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
    Route::get('/courses/{id}', [CourseController::class, 'show'])->name('courses.show');
    Route::get('/courses/{id}/edit', [CourseController::class, 'edit'])->name('courses.edit');
    Route::put('/courses/{id}', [CourseController::class, 'update'])->name('courses.update');
    Route::delete('/courses/{id}', [CourseController::class, 'destroy'])->name('courses.destroy');
});
