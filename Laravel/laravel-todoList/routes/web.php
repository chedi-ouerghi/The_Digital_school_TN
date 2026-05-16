<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;

// Your existing routes
Route::get('/', [TaskController::class, 'index'])->name('tasks.index');

Route::get('/tasks', [TaskController::class, 'todolist'])->name('tasks.todolist');
Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
Route::get('/tasks/{id}', [TaskController::class, 'getById']);

// User Authentication Routes
Route::get('/register', [UserController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [UserController::class, 'register']);

Route::get('/login', [UserController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UserController::class, 'login']);

// Profile Route
Route::get('/profile', [UserController::class, 'getProfile'])->name('profile');

Route::get('/news', 'NewsController@index')->name('news.index');




Route::middleware(['auth'])->group(function () {
    // Routes pour les actions de l'administrateur
    Route::delete('/users/{id}', [UserController::class, 'deleteUser'])->name('delete.user');
    Route::get('/users', [UserController::class, 'getAllUsers'])->name('all.users');
});
