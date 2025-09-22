<?php

use Illuminate\Support\Facades\Route;
use Rahban\LaravelLogbook\Http\Controllers\LogbookController;
use Rahban\LaravelLogbook\Http\Controllers\LogbookManagementController;

Route::middleware(['web', 'logbook.auth'])->group(function () {
    Route::get('/', [LogbookController::class, 'dashboard'])->name('logbook.dashboard');
    Route::get('/tracks', [LogbookController::class, 'tracks'])->name('logbook.tracks');
    Route::get('/tracks/{id}', [LogbookController::class, 'show'])->name('logbook.show');

    Route::prefix('manage')->name('logbook.manage.')->group(function () {
        Route::get('/', [LogbookManagementController::class, 'index'])->name('index');
        Route::delete('/cleanup/days/{days}', [LogbookManagementController::class, 'cleanupByDays'])->name('cleanup.days');
        Route::delete('/cleanup/range', [LogbookManagementController::class, 'cleanupByRange'])->name('cleanup.range');
        Route::delete('/cleanup/all', [LogbookManagementController::class, 'cleanupAll'])->name('cleanup.all');
    });
});
