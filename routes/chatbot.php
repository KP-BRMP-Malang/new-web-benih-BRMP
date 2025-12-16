<?php

use App\Http\Controllers\Api\ChatbotController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Chatbot API Routes
|--------------------------------------------------------------------------
|
| Routes for the chatbot API endpoints.
|
*/

Route::prefix('chat')->group(function () {
    // Main chat endpoint
    Route::post('/', [ChatbotController::class, 'chat'])->name('api.chat');

    // Get chat history
    Route::get('/history', [ChatbotController::class, 'history'])->name('api.chat.history');

    // Clear chat history
    Route::delete('/history', [ChatbotController::class, 'clearHistory'])->name('api.chat.clear');

    // Health check
    Route::get('/health', [ChatbotController::class, 'health'])->name('api.chat.health');
});
