<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

// permission management (developer)
Route::middleware(['auth', 'can:read-permissions'])->group(function () {
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('/permissions/{id}', [PermissionController::class, 'edit'])->name('permissions.edit');
});

Route::middleware(['auth', 'can:create-permissions'])->group(function () {
    Route::get('/permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
});

Route::middleware(['auth', 'can:update-permissions'])->group(function () {
    Route::put('/permissions/{id}', [PermissionController::class, 'update'])->name('permissions.update');
    Route::patch('/permissions/{id}', [PermissionController::class, 'update']);
});

Route::middleware(['auth', 'can:delete-permissions'])->group(function () {
    Route::delete('/permissions/{id}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
});

// below permissions routes
Route::middleware(['auth','can:read-roles'])->group(function(){
    Route::get('/roles',[RoleController::class,'index'])->name('roles.index');
});
Route::middleware(['auth','can:create-roles'])->group(function(){
    Route::post('/roles',[RoleController::class,'store'])->name('roles.store');
});
Route::middleware(['auth','can:update-roles'])->group(function(){
    Route::put('/roles/{id}',[RoleController::class,'update'])->name('roles.update');
    Route::patch('/roles/{id}',[RoleController::class,'update']);
});
Route::middleware(['auth','can:delete-roles'])->group(function(){
    Route::delete('/roles/{id}',[RoleController::class,'destroy'])->name('roles.destroy');
});

// below roles routes
Route::middleware(['auth','can:read-tenant-users'])->group(function(){
    Route::get('/users',[UserController::class,'index'])->name('users.index');
});
Route::middleware(['auth','can:create-tenant-users'])->group(function(){
    Route::post('/users',[UserController::class,'store'])->name('users.store');
});
Route::middleware(['auth','can:update-tenant-users'])->group(function(){
    Route::put('/users/{id}',[UserController::class,'update'])->name('users.update');
    Route::patch('/users/{id}',[UserController::class,'update']);
});
Route::middleware(['auth','can:delete-tenant-users'])->group(function(){
    Route::delete('/users/{id}',[UserController::class,'destroy'])->name('users.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
