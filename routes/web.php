<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;

Route::get('/', function () {
    return view('about');
})->name('home');

// Pages
Route::view('/about', 'about')->name('about');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class , 'showLogin'])->name('login');
    Route::get('/signup', [AuthController::class , 'showSignup'])->name('signup');
});
Route::post('/login', [AuthController::class , 'login'])->middleware(['login.throttle'])->name('login.submit');
Route::match (['get', 'post'], '/logout', [AuthController::class , 'logout'])->name('logout');
Route::post('/signup', [AuthController::class , 'signup'])->name('signup.submit');
Route::post('/signup-ajax', [AuthController::class , 'signupAjax'])->middleware('throttle:10,1')->name('signup.ajax');

// Doctor Apply (Guest or Auth)
Route::get('/doctor/apply', [\App\Http\Controllers\DoctorApplicationController::class , 'create'])->name('doctor.apply');
Route::post('/doctor/apply', [\App\Http\Controllers\DoctorApplicationController::class , 'store'])->name('doctor.apply.store');
Route::post('/doctor/apply/reapply', [\App\Http\Controllers\DoctorApplicationController::class , 'reapply'])->name('doctor.apply.reapply');

// Dashboard
Route::middleware('auth')->group(function () {
    Route::get('/userdashboard', [\App\Http\Controllers\DashboardController::class , 'index'])->name('user.dashboard');
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class , 'index']);

    // Dashboard feed API – throttled to 60 requests/minute
    Route::get('/api/dashboard/feed', [ProfileController::class , 'dashboardFeed'])
        ->middleware('throttle:60,1')
        ->name('dashboard.feed');

    // Search API
    Route::get('/api/search/users', [ProfileController::class , 'searchUsers'])
        ->name('dashboard.search.users');
    // Network API
    Route::get('/api/profile/{user}/network', [ProfileController::class , 'network'])
        ->name('profile.network');

    // Notifications API
    Route::get('/api/notifications', [NotificationController::class , 'index'])
        ->name('notifications.index');
    Route::post('/api/notifications/read-all', [NotificationController::class , 'markAllRead'])
        ->name('notifications.readAll');
    Route::post('/api/notifications/{notification}/read', [NotificationController::class , 'markRead'])
        ->name('notifications.read');

    // Support Groups
    Route::get('/groups', [\App\Http\Controllers\GroupController::class , 'index'])->name('groups.index');
    Route::get('/groups/create', [\App\Http\Controllers\GroupController::class , 'create'])->name('groups.create');
    Route::post('/groups', [\App\Http\Controllers\GroupController::class , 'store'])->name('groups.store');
    Route::get('/groups/{id}', [\App\Http\Controllers\GroupController::class , 'show'])->name('groups.show');
    Route::get('/groups/{id}/edit', [\App\Http\Controllers\GroupController::class , 'edit'])->name('groups.edit');
    Route::post('/groups/{id}/join', [\App\Http\Controllers\GroupController::class , 'join'])->name('groups.join');
    Route::post('/groups/{id}/leave', [\App\Http\Controllers\GroupController::class , 'leave'])->name('groups.leave');
    Route::put('/groups/{id}', [\App\Http\Controllers\GroupController::class , 'update'])->name('groups.update');
    Route::delete('/groups/{id}', [\App\Http\Controllers\GroupController::class , 'destroy'])->name('groups.destroy');

    // Group Cover Photo
    Route::post('/groups/{id}/update-cover', [\App\Http\Controllers\GroupController::class , 'updateCoverPhoto'])->name('groups.update.cover');
    Route::post('/groups/{id}/delete-cover', [\App\Http\Controllers\GroupController::class , 'deleteCoverPhoto'])->name('groups.delete.cover');
    Route::post('/groups/{id}/share', [\App\Http\Controllers\GroupController::class , 'share'])->name('groups.share');

    // Resources
    Route::resource('resources', \App\Http\Controllers\ResourceController::class);
    Route::post('resources/{resource}/share', [\App\Http\Controllers\ResourceController::class, 'share'])->name('resources.share');
    Route::post('resources/{resource}/join', [\App\Http\Controllers\ResourceController::class, 'join'])->name('resources.join');
    Route::delete('resources/{resource}/join', [\App\Http\Controllers\ResourceController::class, 'unjoin'])->name('resources.unjoin');
    Route::get('/resource-file/{path}', [\App\Http\Controllers\ResourceController::class, 'serveFile'])
        ->where('path', '.+')
        ->name('resource.file');
    Route::post('/resources/upload-media', [\App\Http\Controllers\ResourceController::class, 'uploadMedia'])->name('resources.upload-media');

    // Messenger API
    Route::get('/api/messenger/conversations', [\App\Http\Controllers\ChatController::class, 'getConversations']);
    Route::delete('/api/messenger/conversations/{conversation}', [\App\Http\Controllers\ChatController::class, 'deleteConversation']);
    Route::post('/api/messenger/conversations/{id}/archive', [\App\Http\Controllers\ChatController::class, 'archiveConversation']);
    Route::post('/api/messenger/conversations/{id}/unarchive', [\App\Http\Controllers\ChatController::class, 'unarchiveConversation']);
    Route::post('/api/messenger/settings/active-status', [\App\Http\Controllers\ChatController::class, 'toggleActiveStatus']);
    Route::get('/api/messenger/messages/{conversation}', [\App\Http\Controllers\ChatController::class, 'getMessages']);
    Route::post('/api/messenger/send', [\App\Http\Controllers\ChatController::class, 'sendMessage']);
    Route::get('/api/messenger/search', [\App\Http\Controllers\ChatController::class, 'searchUsers']);
    Route::post('/api/messenger/typing', [\App\Http\Controllers\ChatController::class, 'setTyping']);
    Route::get('/api/messenger/typing/{conversation}', [\App\Http\Controllers\ChatController::class, 'getTyping']);
    Route::get('/api/unread-counts', [\App\Http\Controllers\ChatController::class, 'unreadCounts']);

    // Get Help AI
    Route::post('/api/help/chat', [\App\Http\Controllers\HelpRequestController::class, 'chat']);
    Route::get('/api/help/doctors', [\App\Http\Controllers\HelpRequestController::class, 'findDoctors']);
    Route::post('/api/help/request', [\App\Http\Controllers\HelpRequestController::class, 'requestConversation']);
    Route::get('/api/help/request/{id}/status', [\App\Http\Controllers\HelpRequestController::class, 'getRequestStatus']);
    Route::get('/api/help/pending', [\App\Http\Controllers\HelpRequestController::class, 'pendingRequests']);
    Route::post('/api/help/accept/{id}', [\App\Http\Controllers\HelpRequestController::class, 'acceptRequest']);
    Route::post('/api/help/decline/{id}', [\App\Http\Controllers\HelpRequestController::class, 'declineRequest']);
    Route::post('/api/help/toggle-status', [\App\Http\Controllers\HelpRequestController::class, 'toggleStatus']);

    // Schedule Management (Doctors Only)
    Route::middleware(['auth'])->prefix('doctor/schedule')->group(function () {
        Route::get('/', [App\Http\Controllers\DoctorScheduleController::class, 'index'])->name('doctor.schedule.index');
        Route::post('/update', [App\Http\Controllers\DoctorScheduleController::class, 'update'])->name('doctor.schedule.update');
        Route::post('/toggle', [App\Http\Controllers\DoctorScheduleController::class, 'toggle'])->name('doctor.schedule.toggle');
    });

    // Appointment Scheduling
    Route::prefix('appointments')->group(function () {
        Route::get('/create', [App\Http\Controllers\AppointmentController::class, 'create'])->name('appointments.create');
        Route::get('/{appointment}', [App\Http\Controllers\AppointmentController::class, 'show'])->name('appointments.show');
        Route::get('/api/events', [App\Http\Controllers\AppointmentController::class, 'getEvents'])->name('appointments.events');
        Route::post('/api/store', [App\Http\Controllers\AppointmentController::class, 'store'])->name('appointments.store');
        Route::post('/api/update/{appointment}', [App\Http\Controllers\AppointmentController::class, 'update'])->name('appointments.update');
        Route::post('/api/respond/{invitation}', [App\Http\Controllers\AppointmentController::class, 'respond'])->name('appointments.respond');
        Route::delete('/api/destroy/{appointment}', [App\Http\Controllers\AppointmentController::class, 'destroy'])->name('appointments.destroy');
        Route::get('/api/check-conflicts', [App\Http\Controllers\AppointmentController::class, 'checkConflicts'])->name('appointments.check-conflicts');
    });
});

// ── Profile ────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::post('/profile/ai-recommendation', [ProfileController::class, 'updateAiRecommendation'])->name('profile.updateAiRecommendation');
    Route::get('/posts/{post}', [ProfileController::class , 'showPost'])->name('posts.show');

    // View profile (own or others)
    Route::get('/profile/{id}', [ProfileController::class , 'show'])->name('profile.show');
    Route::post('/profile/{user}/follow', [ProfileController::class , 'toggleFollow'])->name('profile.follow');

    // Edit own profile info & photo
    Route::post('/profile/update-info', [ProfileController::class , 'updateInfo'])->name('profile.update.info');
    Route::post('/profile/update-photo', [ProfileController::class , 'updatePhoto'])->name('profile.update.photo');
    Route::post('/profile/delete-photo', [ProfileController::class , 'deletePhoto'])->name('profile.delete.photo');

    // Edit own cover photo
    Route::post('/profile/update-cover', [ProfileController::class , 'updateCoverPhoto'])->name('profile.update.cover');
    Route::post('/profile/delete-cover', [ProfileController::class , 'deleteCoverPhoto'])->name('profile.delete.cover');

    // Posts
    Route::post('/profile/posts', [ProfileController::class , 'storePost'])->name('profile.posts.store');
    Route::put('/profile/posts/{post}', [ProfileController::class , 'updatePost'])->name('profile.posts.update');
    Route::delete('/profile/posts/{post}', [ProfileController::class , 'destroyPost'])->name('profile.posts.destroy');

    // Reactions
    Route::post('/profile/posts/{post}/like', [ProfileController::class , 'toggleLike'])->name('profile.posts.like');
    Route::post('/profile/posts/{post}/share', [ProfileController::class , 'sharePost'])->name('profile.posts.share');
    Route::post('/profile/posts/{post}/save', [ProfileController::class , 'toggleSave'])->name('profile.posts.save');

    // Comments
    Route::post('/profile/posts/{post}/comments', [ProfileController::class , 'storeComment'])->name('profile.comments.store');
    Route::delete('/profile/comments/{comment}', [ProfileController::class , 'destroyComment'])->name('profile.comments.destroy');
});

// Admin Routes
Route::group(['prefix' => 'admin'], function () {

    // Admin Auth
    Route::middleware('guest:admin')->group(function () {
            Route::get('/login', [\App\Http\Controllers\AdminAuthController::class , 'showLogin'])->name('admin.login');
            Route::post('/login', [\App\Http\Controllers\AdminAuthController::class , 'login'])->middleware('login.throttle')->name('admin.login.submit');

            Route::get('/signup', [\App\Http\Controllers\AdminAuthController::class , 'showSignup'])->name('admin.signup');
            Route::post('/signup', [\App\Http\Controllers\AdminAuthController::class , 'signup'])->name('admin.signup.submit');
        }
        );

        Route::match (['get', 'post'], '/logout', [\App\Http\Controllers\AdminAuthController::class , 'logout'])->name('admin.logout');

        // Protected Admin Routes
        Route::middleware(['auth:admin', 'admin.security'])->group(function () {
            // Dashboard/Applications
            Route::get('/applications', [\App\Http\Controllers\AdminApplicationController::class , 'index'])->name('admin.applications.index');
            Route::get('/applications/{id}', [\App\Http\Controllers\AdminApplicationController::class , 'show'])->name('admin.applications.show');
            Route::post('/applications/{id}/approve', [\App\Http\Controllers\AdminApplicationController::class , 'approve'])->name('admin.applications.approve');
            Route::post('/applications/{id}/reject', [\App\Http\Controllers\AdminApplicationController::class , 'reject'])->name('admin.applications.reject');

            // Profile
            Route::get('/profile', [\App\Http\Controllers\AdminProfileController::class , 'show'])->name('admin.profile');
            Route::post('/profile', [\App\Http\Controllers\AdminProfileController::class , 'update'])->name('admin.profile.update');
            Route::post('/profile/update-photo', [\App\Http\Controllers\AdminProfileController::class , 'updatePhoto'])->name('admin.profile.update.photo');
            Route::post('/profile/delete-photo', [\App\Http\Controllers\AdminProfileController::class , 'deletePhoto'])->name('admin.profile.delete.photo');


            // Professional Titles
            Route::get('/professional-titles', [\App\Http\Controllers\Admin\ProfessionalTitleController::class, 'index'])->name('admin.professional-titles.index');
            Route::post('/professional-titles', [\App\Http\Controllers\Admin\ProfessionalTitleController::class, 'store'])->name('admin.professional-titles.store');
            Route::put('/professional-titles/{professional_title}', [\App\Http\Controllers\Admin\ProfessionalTitleController::class, 'update'])->name('admin.professional-titles.update');
            Route::delete('/professional-titles/{professional_title}', [\App\Http\Controllers\Admin\ProfessionalTitleController::class, 'destroy'])->name('admin.professional-titles.destroy');

            // Daily Affirmations
            Route::get('/daily-affirmations', [\App\Http\Controllers\Admin\DailyAffirmationController::class, 'index'])->name('admin.daily-affirmations.index');
            Route::post('/daily-affirmations', [\App\Http\Controllers\Admin\DailyAffirmationController::class, 'store'])->name('admin.daily-affirmations.store');
            Route::put('/daily-affirmations/{daily_affirmation}', [\App\Http\Controllers\Admin\DailyAffirmationController::class, 'update'])->name('admin.daily-affirmations.update');
            Route::delete('/daily-affirmations/{daily_affirmation}', [\App\Http\Controllers\Admin\DailyAffirmationController::class, 'destroy'])->name('admin.daily-affirmations.destroy');
            Route::post('/daily-affirmations/{daily_affirmation}/publish-now', [\App\Http\Controllers\Admin\DailyAffirmationController::class, 'publishNow'])->name('admin.daily-affirmations.publish-now');

            // Admin Messages (Drawer API)
            Route::get('/api/messenger/conversations', [\App\Http\Controllers\Admin\AdminMessageController::class, 'apiConversations'])->name('admin.messages.api.conversations');
            Route::get('/api/messenger/search', [\App\Http\Controllers\Admin\AdminMessageController::class, 'apiSearch'])->name('admin.messages.api.search');
            Route::get('/api/messenger/messages/{id}', [\App\Http\Controllers\Admin\AdminMessageController::class, 'apiMessages'])->name('admin.messages.api.messages');
            Route::post('/api/messenger/send', [\App\Http\Controllers\Admin\AdminMessageController::class, 'apiSend'])->name('admin.messages.api.send');


            // Admin Notifications
            Route::get('/notifications', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'index'])->name('admin.notifications.index');
            Route::get('/notifications/unread-count', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'unreadCount'])->name('admin.notifications.unread');
            Route::post('/notifications/{id}/read', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'markRead'])->name('admin.notifications.read');
        }
        );
    });
