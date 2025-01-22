<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;

Route::get('files-download/{id}', [FileUploadController::class, 'download'])->name('files.download');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
