<?php

use Illuminate\Support\Facades\Route;

// Routes spécifiques aux étudiants

Route::prefix('student')->group(function () {
    // Routes pour les cours
    Route::get('/courses', 'Student\CourseController@index')->name('student.courses.index');

    // Autres routes pour les étudiants...
});
