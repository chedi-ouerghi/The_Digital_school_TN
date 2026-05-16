<?php

use Illuminate\Support\Facades\Route;

// Routes spécifiques à la section d'administration

Route::prefix('admin')->group(function () {
    // Routes pour les cours
    Route::get('/courses', 'Admin\CourseController@index')->name('admin.courses.index');
    Route::get('/courses/create', 'Admin\CourseController@create')->name('admin.courses.create');
    Route::post('/courses', 'Admin\CourseController@store')->name('admin.courses.store');

    // Routes pour les catégories
    Route::get('/categories', 'Admin\CategoryController@index')->name('admin.categories.index');
    Route::get('/categories/create', 'Admin\CategoryController@create')->name('admin.categories.create');
    Route::post('/categories', 'Admin\CategoryController@store')->name('admin.categories.store');

    // Routes pour les FAQ
    Route::get('/faqs', 'Admin\FAQController@index')->name('admin.faqs.index');
    Route::get('/faqs/create', 'Admin\FAQController@create')->name('admin.faqs.create');
    Route::post('/faqs', 'Admin\FAQController@store')->name('admin.faqs.store');

    // Routes pour les instructeurs
    Route::get('/instructors', 'Admin\InstructorController@index')->name('admin.instructors.index');
    Route::get('/instructors/create', 'Admin\InstructorController@create')->name('admin.instructors.create');
    Route::post('/instructors', 'Admin\InstructorController@store')->name('admin.instructors.store');

    // Routes pour les demandes des étudiants
    Route::get('/requests', 'Admin\StudentRequestController@index')->name('admin.requests.index');
    Route::get('/requests/create', 'Admin\StudentRequestController@create')->name('admin.requests.create');
    Route::post('/requests', 'Admin\StudentRequestController@store')->name('admin.requests.store');

    
});
