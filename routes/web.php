<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::name('contacts.')->group(function () {
    Route::get('/', [ContactController::class, 'index'])->name('index');
    Route::post('/contacts/confirm', [ContactController::class, 'confirm'])->name('confirm');
    Route::post('/contacts', [ContactController::class, 'store'])->name('store');
    Route::get('/thanks', [ContactController::class, 'thanks'])->name('thanks');
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/contacts/{contact}', [AdminController::class, 'show'])->name('show');
    Route::delete('/contacts/{contact}', [AdminController::class, 'destroy'])->name('destroy');
});
