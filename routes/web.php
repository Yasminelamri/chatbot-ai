<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

Route::get('/', function () {
    return redirect()->route('chat.index');
});

// Route de test simple
Route::get('/test', function () {
    return 'Hello World - Test route fonctionne !';
});

// Route de test Tailwind CSS
Route::get('/test-tailwind', function () {
    return view('test-tailwind');
})->name('test.tailwind');

// Route de test Inertia.js
Route::get('/test-inertia', function () {
    return view('test-inertia');
})->name('test.inertia');

// Route publique sans authentification
Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
Route::get('/public-chat', [ChatController::class, 'index'])->name('chat.public');
Route::get('/chat/new', [ChatController::class, 'new'])->name('chat.new');
Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');
Route::post('/public-chat/send', [ChatController::class, 'send'])->name('chat.public.send');
Route::put('/chat/messages/{message}', [ChatController::class, 'update'])->name('chat.update');
Route::delete('/chat/messages/{message}', [ChatController::class, 'destroy'])->name('chat.destroy');
Route::post('/chat/clear', [ChatController::class, 'clear'])->name('chat.clear');
Route::post('/chat/conversations/{conversation}/archive', function (\App\Models\Conversation $conversation) {
    $conversation->update(['archived' => true]);
    return back();
})->name('chat.conversations.archive');
Route::post('/chat/conversations/{conversation}/unarchive', function (\App\Models\Conversation $conversation) {
    $conversation->update(['archived' => false]);
    return back();
})->name('chat.conversations.unarchive');
Route::delete('/chat/conversations/{conversation}', function (\App\Models\Conversation $conversation) {
    $conversation->messages()->delete();
    $conversation->delete();
    return back();
})->name('chat.conversations.delete');
Route::put('/chat/conversations/{conversation}', [ChatController::class, 'updateConversation'])->name('chat.conversations.update');

// Stats & Exports
Route::get('/chat/stats', [ChatController::class, 'stats'])->name('chat.stats');
Route::get('/chat/export/conversations', [ChatController::class, 'exportConversationsCsv'])->name('chat.export.conversations');
Route::get('/chat/export/faq', [ChatController::class, 'exportFaqCsv'])->name('chat.export.faq');

// Routes pour la gestion FAQ (réservées aux membres Jadara)
Route::middleware(['auth'])->prefix('faq')->name('faq.')->group(function () {
    Route::get('/management', [\App\Http\Controllers\FaqManagementController::class, 'index'])->name('management');
    Route::post('/management', [\App\Http\Controllers\FaqManagementController::class, 'store']);
    Route::get('/management/{faq}', [\App\Http\Controllers\FaqManagementController::class, 'show']);
    Route::put('/management/{faq}', [\App\Http\Controllers\FaqManagementController::class, 'update']);
    Route::delete('/management/{faq}', [\App\Http\Controllers\FaqManagementController::class, 'destroy']);
    Route::get('/management/export', [\App\Http\Controllers\FaqManagementController::class, 'export'])->name('export');
    Route::post('/management/import', [\App\Http\Controllers\FaqManagementController::class, 'import'])->name('import');
});

// API Routes pour la FAQ
Route::prefix('api')->group(function () {
    Route::get('/faq', [\App\Http\Controllers\Api\FaqController::class, 'index']);
    Route::get('/faq/{id}', [\App\Http\Controllers\Api\FaqController::class, 'show']);
    Route::get('/faq/search', [\App\Http\Controllers\Api\FaqController::class, 'search']);
});
