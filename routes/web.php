<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::post('/upload', [App\Http\Controllers\PersonController::class, 'uploadHomeOwners'])->name('upload');
