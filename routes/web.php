<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\AutoReplyRuleController;
use App\Http\Controllers\AutoReplyLogController;
use App\Http\Controllers\KbArticleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Dashboard (ReplyAI)
Route::get('/', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
Route::get('/analytics', [App\Http\Controllers\AnalyticsController::class, 'index'])->name('analytics.index');
Route::get('/contacts', [App\Http\Controllers\ContactController::class, 'index'])->name('contacts.index');
Route::get('/settings', function() { return view('pages.settings.index'); })->name('settings.index');
Route::get('/simulator', function() { return view('pages.simulator.index'); })->name('simulator.index');

// ============================
// INBOX INSTAGRAM
// ============================
Route::get('/inbox', [InboxController::class, 'index'])->name('inbox');

// kirim pesan manual
Route::post('/inbox/send', [InboxController::class, 'send'])->name('inbox.send');

// polling messages (auto refresh)
Route::get('/inbox/poll-messages', [InboxController::class, 'pollMessages'])
    ->name('inbox.poll.messages');

// polling conversations (opsional buat sidebar update)
Route::get('/inbox/poll-conversations', [InboxController::class, 'pollConversations'])
    ->name('inbox.poll.conversations');
// ✅ endpoint check pesan terbaru (AJAX)
Route::get('/inbox/check-latest', [InboxController::class, 'checkLatest'])
    ->name('inbox.checkLatest');
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox');
    Route::post('/inbox/send', [InboxController::class, 'send'])->name('inbox.send');
    Route::get('/inbox/has-new', [InboxController::class, 'hasNew'])->name('inbox.hasNew');
// route lain (tailadmin default) kalau mau tetap ada:
Route::get('/calendar', fn() => view('pages.calender', ['title' => 'Calendar']))->name('calendar');
Route::get('/profile', fn() => view('pages.profile', ['title' => 'Profile']))->name('profile');
Route::get('/form-elements', fn() => view('pages.form.form-elements', ['title' => 'Form Elements']))->name('form-elements');
Route::get('/basic-tables', fn() => view('pages.tables.basic-tables', ['title' => 'Basic Tables']))->name('basic-tables');
Route::get('/blank', fn() => view('pages.blank', ['title' => 'Blank']))->name('blank');
Route::get('/error-404', fn() => view('pages.errors.error-404', ['title' => 'Error 404']))->name('error-404');
Route::get('/line-chart', fn() => view('pages.chart.line-chart', ['title' => 'Line Chart']))->name('line-chart');
Route::get('/bar-chart', fn() => view('pages.chart.bar-chart', ['title' => 'Bar Chart']))->name('bar-chart');
Route::get('/signin', fn() => view('pages.auth.signin', ['title' => 'Sign In']))->name('signin');
Route::get('/signup', fn() => view('pages.auth.signup', ['title' => 'Sign Up']))->name('signup');
Route::get('/alerts', fn() => view('pages.ui-elements.alerts', ['title' => 'Alerts']))->name('alerts');
Route::get('/avatars', fn() => view('pages.ui-elements.avatars', ['title' => 'Avatars']))->name('avatars');
Route::get('/badge', fn() => view('pages.ui-elements.badges', ['title' => 'Badges']))->name('badges');
Route::get('/buttons', fn() => view('pages.ui-elements.buttons', ['title' => 'Buttons']))->name('buttons');
Route::get('/image', fn() => view('pages.ui-elements.images', ['title' => 'Images']))->name('images');
Route::get('/videos', fn() => view('pages.ui-elements.videos', ['title' => 'Videos']))->name('videos');



// Rules (modal CRUD)
Route::get('/rules', [AutoReplyRuleController::class, 'index'])->name('rules.index');
Route::post('/rules', [AutoReplyRuleController::class, 'store'])->name('rules.store');
Route::put('/rules/{rule}', [AutoReplyRuleController::class, 'update'])->name('rules.update');
Route::delete('/rules/{rule}', [AutoReplyRuleController::class, 'destroy'])->name('rules.destroy');
Route::patch('/rules/{rule}/toggle', [AutoReplyRuleController::class, 'toggle'])->name('rules.toggle');

Route::get('/logs', [AutoReplyLogController::class, 'index'])->name('logs');

// Rules (tabel rules yg tadi)
Route::get('/rules', [AutoReplyRuleController::class, 'index'])->name('rules.index');

// ✅ Logs
Route::get('/logs', [AutoReplyLogController::class, 'index'])->name('logs.index');

// AJAX CRUD (no redirect)
Route::post('/rules', [AutoReplyRuleController::class, 'storeAjax'])->name('rules.store');
Route::patch('/rules/{rule}', [AutoReplyRuleController::class, 'updateAjax'])->name('rules.update');
Route::delete('/rules/{rule}', [AutoReplyRuleController::class, 'destroyAjax'])->name('rules.destroy');
Route::patch('/rules/{rule}/toggle', [AutoReplyRuleController::class, 'toggleAjax'])->name('rules.toggle');
Route::post('/rules/test', [AutoReplyRuleController::class, 'testAjax'])
    ->name('rules.test');



// KB
Route::get('/kb', [KbArticleController::class, 'index'])->name('kb.index');
Route::post('/kb', [KbArticleController::class, 'store'])->name('kb.store');
Route::post('/kb/import-url', [KbArticleController::class, 'importUrl'])->name('kb.importUrl');
Route::patch('/kb/{kb}/toggle', [KbArticleController::class, 'toggle'])->name('kb.toggle');
Route::delete('/kb/{kb}', [KbArticleController::class, 'destroy'])->name('kb.destroy');

// AI preview test rules (punyamu tadi)
Route::post('/rules/test', [AutoReplyRuleController::class, 'testAjax'])->name('rules.test');



Route::get('/kb', [KbArticleController::class, 'index'])->name('kb.index');
Route::post('/kb/import-url', [KbArticleController::class, 'importUrl'])->name('kb.importUrl');
Route::patch('/kb/{kb}/toggle', [KbArticleController::class, 'toggle'])->name('kb.toggle');
Route::delete('/kb/{kb}', [KbArticleController::class, 'destroy'])->name('kb.destroy');

// ✅ NEW: test AI dari KB
Route::post('/kb/test-ai', [KbArticleController::class, 'testAi'])->name('kb.testAi');



Route::get('/auto-reply-logs', [AutoReplyLogController::class, 'index'])
    ->name('auto-reply-logs.index');
