<?php

use App\Http\Controllers\FileUploadController;
use Illuminate\Support\Facades\Route;

Route::post('/upload', [FileUploadController::class, 'upload']);
Route::get('/uploads', [FileUploadController::class, 'index']);
Route::get('/upload/{id}/status', [FileUploadController::class, 'status']);

Route::view('/', 'welcome');