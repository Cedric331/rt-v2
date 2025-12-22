<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ResponseTemplateController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    
    return redirect()->route('login');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [ResponseTemplateController::class, 'index'])->name('dashboard');
    
    // Response Templates routes
    Route::post('response-templates', [ResponseTemplateController::class, 'store'])->name('response-templates.store');
    Route::put('response-templates/{responseTemplate}', [ResponseTemplateController::class, 'update'])->name('response-templates.update');
    Route::delete('response-templates/{responseTemplate}', [ResponseTemplateController::class, 'destroy'])->name('response-templates.destroy');
    Route::post('response-templates/{responseTemplate}/copy', [ResponseTemplateController::class, 'copy'])->name('response-templates.copy');
    
    // Categories routes
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
});

require __DIR__.'/settings.php';
