<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::name('contacts.')->group(function () {
    Route::get('/', [ContactController::class, 'index'])->name('index');
    Route::post('/contacts/confirm', [ContactController::class, 'confirm'])->name('confirm');
    Route::post('/contacts', [ContactController::class, 'store'])->name('store');
    Route::get('/thanks', [ContactController::class, 'thanks'])->name('thanks');
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/{contact}', [AdminController::class, 'show'])->name('contacts.show');
    Route::delete('/contacts/{contact}', [AdminController::class, 'destroy'])->name('contacts.destroy');

    Route::post('/tags', [TagController::class, 'store'])->name('tags.store');
    Route::get('/tags/{tag}/edit', [TagController::class, 'edit'])->name('tags.edit');
    Route::put('/tags/{tag}', [TagController::class, 'update'])->name('tags.update');
    Route::delete('/tags/{tag}', [TagController::class, 'destroy'])->name('tags.delete');
});
