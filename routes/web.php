<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\API\ProgramController;
use App\Http\Controllers\API\ApplicationController;
use App\Http\Controllers\API\RewardController;
use App\Http\Controllers\API\RedemptionController;
use App\Http\Controllers\API\SupportTicketController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminProgramController;
use App\Http\Controllers\Admin\AdminApplicationController;
use App\Http\Controllers\Admin\AdminRewardController;
use App\Http\Controllers\Admin\AdminRedemptionController;
use App\Http\Controllers\Admin\AdminSupportTicketController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\AdminProgramRequisiteController;
use App\Http\Controllers\Admin\AdminUserProgramRequisiteController;
use App\Http\Controllers\Admin\CurrencyController;

Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

// Programs (publicly accessible)
Route::get('/programs', [ProgramController::class, 'index'])->name('programs.index');
Route::get('/programs/{program}', [ProgramController::class, 'show'])->name('programs.show');

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Applications
    Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('/applications/create', [ApplicationController::class, 'create'])->name('applications.create');
    Route::post('/applications', [ApplicationController::class, 'store'])->name('applications.store');
    Route::get('/applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
    
    // Rewards
    Route::get('/rewards', [RewardController::class, 'index'])->name('rewards.index');
    Route::post('/redemptions', [RedemptionController::class, 'store'])->name('redemptions.store');
    
    // Support Tickets
    Route::resource('support-tickets', SupportTicketController::class);
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    
    // Admin Routes
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
        
        // Users Management
        Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
        Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('admin.users.show');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
        Route::get('/users/export', [UserController::class, 'export'])->name('admin.users.export');
        
        // Programs Management
        Route::get('/programs', [AdminProgramController::class, 'index'])->name('admin.programs.index');
        Route::get('/programs/create', [AdminProgramController::class, 'create'])->name('admin.programs.create');
        Route::post('/programs', [AdminProgramController::class, 'store'])->name('admin.programs.store');
        Route::get('/programs/{program}', [AdminProgramController::class, 'show'])->name('admin.programs.show');
        Route::get('/programs/{program}/edit', [AdminProgramController::class, 'edit'])->name('admin.programs.edit');
        Route::put('/programs/{program}', [AdminProgramController::class, 'update'])->name('admin.programs.update');
        Route::delete('/programs/{program}', [AdminProgramController::class, 'destroy'])->name('admin.programs.destroy');
        Route::get('/programs/export', [AdminProgramController::class, 'export'])->name('admin.programs.export');
        
        // Program Requisites Management
        Route::get('/programs/{program}/requisites', [AdminProgramRequisiteController::class, 'index'])->name('admin.programs.requisites.index');
        Route::get('/programs/{program}/requisites/create', [AdminProgramRequisiteController::class, 'create'])->name('admin.programs.requisites.create');
        Route::post('/programs/{program}/requisites', [AdminProgramRequisiteController::class, 'store'])->name('admin.programs.requisites.store');
        Route::get('/programs/{program}/requisites/{requisite}/edit', [AdminProgramRequisiteController::class, 'edit'])->name('admin.programs.requisites.edit');
        Route::put('/programs/{program}/requisites/{requisite}', [AdminProgramRequisiteController::class, 'update'])->name('admin.programs.requisites.update');
        Route::delete('/programs/{program}/requisites/{requisite}', [AdminProgramRequisiteController::class, 'destroy'])->name('admin.programs.requisites.destroy');
        Route::post('/programs/{program}/requisites/order', [AdminProgramRequisiteController::class, 'updateOrder'])->name('admin.programs.requisites.updateOrder');
        
        // Applications Management
        Route::get('/applications', [AdminApplicationController::class, 'index'])->name('admin.applications.index');
        Route::get('/applications/{application}', [AdminApplicationController::class, 'show'])->name('admin.applications.show');
        Route::post('/applications/{application}/review', [AdminApplicationController::class, 'review'])->name('admin.applications.review');
        Route::post('/applications/{application}/approve', [AdminApplicationController::class, 'approve'])->name('admin.applications.approve');
        Route::post('/applications/{application}/reject', [AdminApplicationController::class, 'reject'])->name('admin.applications.reject');
        Route::post('/applications/{application}/notes', [AdminApplicationController::class, 'storeNote'])->name('admin.applications.notes.store');
        Route::delete('/applications/notes/{note}', [AdminApplicationController::class, 'destroyNote'])->name('admin.applications.notes.destroy');
        Route::get('/applications/export', [AdminApplicationController::class, 'export'])->name('admin.applications.export');
        
        // Application Requisites Management
        Route::get('/applications/{application}/requisites', [AdminUserProgramRequisiteController::class, 'index'])->name('admin.applications.requisites.index');
        Route::put('/applications/requisites/{requisite}/verify', [AdminUserProgramRequisiteController::class, 'verify'])->name('admin.applications.requisites.verify');
        Route::put('/applications/requisites/{requisite}/reject', [AdminUserProgramRequisiteController::class, 'reject'])->name('admin.applications.requisites.reject');
        Route::put('/applications/requisites/{requisite}/reset', [AdminUserProgramRequisiteController::class, 'reset'])->name('admin.applications.requisites.reset');
        
        // Documents Management
        Route::get('/documents', [AdminApplicationController::class, 'documents'])->name('admin.documents.index');
        Route::post('/application-documents/{document}/verify', [AdminApplicationController::class, 'verifyDocument'])->name('admin.documents.verify');
        Route::post('/application-documents/{document}/reject', [AdminApplicationController::class, 'rejectDocument'])->name('admin.documents.reject');
        
        // Rewards Management
        Route::get('/rewards', [AdminRewardController::class, 'index'])->name('admin.rewards.index');
        Route::get('/rewards/create', [AdminRewardController::class, 'create'])->name('admin.rewards.create');
        Route::post('/rewards', [AdminRewardController::class, 'store'])->name('admin.rewards.store');
        Route::get('/rewards/{reward}', [AdminRewardController::class, 'show'])->name('admin.rewards.show');
        Route::get('/rewards/{reward}/edit', [AdminRewardController::class, 'edit'])->name('admin.rewards.edit');
        Route::put('/rewards/{reward}', [AdminRewardController::class, 'update'])->name('admin.rewards.update');
        Route::delete('/rewards/{reward}', [AdminRewardController::class, 'destroy'])->name('admin.rewards.destroy');
        Route::get('/rewards/export', [AdminRewardController::class, 'export'])->name('admin.rewards.export');
        
        // Redemptions Management
        Route::get('/redemptions', [AdminRedemptionController::class, 'index'])->name('admin.redemptions.index');
        Route::get('/redemptions/{redemption}', [AdminRedemptionController::class, 'show'])->name('admin.redemptions.show');
        Route::post('/redemptions/{redemption}/approve', [AdminRedemptionController::class, 'approve'])->name('admin.redemptions.approve');
        Route::post('/redemptions/{redemption}/reject', [AdminRedemptionController::class, 'reject'])->name('admin.redemptions.reject');
        Route::post('/redemptions/{redemption}/delivery', [AdminRedemptionController::class, 'updateDelivery'])->name('admin.redemptions.delivery');
        Route::post('/redemptions/{redemption}/notes', [AdminRedemptionController::class, 'storeNote'])->name('admin.redemptions.notes.store');
        Route::delete('/redemptions/notes/{note}', [AdminRedemptionController::class, 'destroyNote'])->name('admin.redemptions.notes.destroy');
        Route::get('/redemptions/export', [AdminRedemptionController::class, 'export'])->name('admin.redemptions.export');
        
        // Points Management
        Route::get('/points', [AdminRedemptionController::class, 'points'])->name('admin.points.index');
        
        // Support Tickets Management
        Route::get('/support', [AdminSupportTicketController::class, 'index'])->name('admin.support.index');
        Route::get('/support/{ticket}', [AdminSupportTicketController::class, 'show'])->name('admin.support.show');
        Route::post('/support/{ticket}/reply', [AdminSupportTicketController::class, 'reply'])->name('admin.support.reply');
        Route::put('/support/{ticket}/status', [AdminSupportTicketController::class, 'changeStatus'])->name('admin.support.changeStatus');
        Route::put('/support/{ticket}/priority', [AdminSupportTicketController::class, 'changePriority'])->name('admin.support.changePriority');
        Route::put('/support/{ticket}/assign', [AdminSupportTicketController::class, 'assign'])->name('admin.support.assign');
        Route::put('/support/{ticket}/close', [AdminSupportTicketController::class, 'close'])->name('admin.support.close');
        Route::put('/support/{ticket}/reopen', [AdminSupportTicketController::class, 'reopen'])->name('admin.support.reopen');
        Route::get('/support/export', [AdminSupportTicketController::class, 'export'])->name('admin.support.export');
        
        // Notifications Management
        Route::get('/notifications', [AdminSupportTicketController::class, 'notifications'])->name('admin.notifications.index');
        Route::post('/notifications', [AdminSupportTicketController::class, 'storeNotification'])->name('admin.notifications.store');
        
        // Currencies Management (Valores)
        Route::resource('currencies', CurrencyController::class)->names([
            'index' => 'admin.currencies.index',
            'create' => 'admin.currencies.create',
            'store' => 'admin.currencies.store',
            'show' => 'admin.currencies.show',
            'edit' => 'admin.currencies.edit',
            'update' => 'admin.currencies.update',
            'destroy' => 'admin.currencies.destroy',
        ]);
        Route::post('/currencies/update-rates', [CurrencyController::class, 'updateRates'])->name('admin.currencies.updateRates');

        // Finance Management
        Route::get('/finance', [\App\Http\Controllers\Admin\AdminFinanceController::class, 'index'])->name('admin.finance.index');
        Route::get('/finance/payments', [\App\Http\Controllers\Admin\AdminFinanceController::class, 'payments'])->name('admin.finance.payments');
        Route::get('/finance/payments/create', [\App\Http\Controllers\Admin\AdminFinanceController::class, 'createPayment'])->name('admin.finance.payments.create');
        Route::post('/finance/payments', [\App\Http\Controllers\Admin\AdminFinanceController::class, 'storePayment'])->name('admin.finance.payments.store');
        Route::post('/finance/payments/{payment}/verify', [\App\Http\Controllers\Admin\AdminFinanceController::class, 'verifyPayment'])->name('admin.finance.payments.verify');
        Route::post('/finance/payments/{payment}/reject', [\App\Http\Controllers\Admin\AdminFinanceController::class, 'rejectPayment'])->name('admin.finance.payments.reject');
        Route::get('/finance/payment-requisites', [\App\Http\Controllers\Admin\AdminFinanceController::class, 'getPaymentRequisites'])->name('admin.finance.payment-requisites');
        Route::get('/finance/report', [\App\Http\Controllers\Admin\AdminFinanceController::class, 'report'])->name('admin.finance.report');
        Route::get('/finance/report/export', [\App\Http\Controllers\Admin\AdminFinanceController::class, 'exportReport'])->name('admin.finance.report.export');
        
        // Financial Transactions Management
        Route::get('/finance/transactions', [\App\Http\Controllers\Admin\AdminFinanceController::class, 'transactions'])->name('admin.finance.transactions');
        Route::get('/finance/transactions/create', [\App\Http\Controllers\Admin\AdminFinanceController::class, 'createTransaction'])->name('admin.finance.transactions.create');
        Route::post('/finance/transactions', [\App\Http\Controllers\Admin\AdminFinanceController::class, 'storeTransaction'])->name('admin.finance.transactions.store');
        Route::get('/finance/transactions/{transaction}', [\App\Http\Controllers\Admin\AdminFinanceController::class, 'showTransaction'])->name('admin.finance.transactions.show');
        Route::get('/finance/transactions/{transaction}/edit', [\App\Http\Controllers\Admin\AdminFinanceController::class, 'editTransaction'])->name('admin.finance.transactions.edit');
        Route::put('/finance/transactions/{transaction}', [\App\Http\Controllers\Admin\AdminFinanceController::class, 'updateTransaction'])->name('admin.finance.transactions.update');
        Route::delete('/finance/transactions/{transaction}', [\App\Http\Controllers\Admin\AdminFinanceController::class, 'destroyTransaction'])->name('admin.finance.transactions.destroy');
        
        // Reports
        Route::get('/reports/applications', [ReportController::class, 'applications'])->name('admin.reports.applications');
        Route::get('/reports/applications/export', [ReportController::class, 'exportApplications'])->name('admin.reports.applications.export');
        Route::get('/reports/users', [ReportController::class, 'users'])->name('admin.reports.users');
        Route::get('/reports/users/export', [ReportController::class, 'exportUsers'])->name('admin.reports.users.export');
        Route::get('/reports/rewards', [ReportController::class, 'rewards'])->name('admin.reports.rewards');
        Route::get('/reports/rewards/export', [ReportController::class, 'exportRewards'])->name('admin.reports.rewards.export');
        
        // Financial Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports.index');
        Route::get('/reports/programs', [ReportController::class, 'programs'])->name('admin.reports.programs');
        Route::get('/reports/currencies', [ReportController::class, 'currencies'])->name('admin.reports.currencies');
        Route::get('/reports/monthly', [ReportController::class, 'monthly'])->name('admin.reports.monthly');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('admin.reports.export');
        
        // System Settings
        Route::get('/settings', [\App\Http\Controllers\Admin\SystemSettingController::class, 'index'])->name('admin.settings.index');
        Route::get('/settings/general', [\App\Http\Controllers\Admin\SystemSettingController::class, 'general'])->name('admin.settings.general');
        Route::post('/settings/general', [\App\Http\Controllers\Admin\SystemSettingController::class, 'updateGeneral'])->name('admin.settings.general.update');
        Route::get('/settings/whatsapp', [\App\Http\Controllers\Admin\SystemSettingController::class, 'whatsapp'])->name('admin.settings.whatsapp');
        Route::post('/settings/whatsapp', [\App\Http\Controllers\Admin\SystemSettingController::class, 'updateWhatsapp'])->name('admin.settings.whatsapp.update');
        Route::put('/settings/{key}', [\App\Http\Controllers\Admin\SystemSettingController::class, 'update'])->name('admin.settings.update');

        // Program Forms
        Route::prefix('programs/{program}/forms')->name('admin.programs.forms.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ProgramFormController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\ProgramFormController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\ProgramFormController::class, 'store'])->name('store');
            Route::post('/from-template', [\App\Http\Controllers\Admin\ProgramFormController::class, 'createFromTemplate'])->name('from-template');
            Route::get('/{form}', [\App\Http\Controllers\Admin\ProgramFormController::class, 'show'])->name('show');
            Route::get('/{form}/edit', [\App\Http\Controllers\Admin\ProgramFormController::class, 'edit'])->name('edit');
            Route::put('/{form}', [\App\Http\Controllers\Admin\ProgramFormController::class, 'update'])->name('update');
            Route::delete('/{form}', [\App\Http\Controllers\Admin\ProgramFormController::class, 'destroy'])->name('destroy');
            Route::post('/{form}/toggle-active', [\App\Http\Controllers\Admin\ProgramFormController::class, 'toggleActive'])->name('toggle-active');
            Route::post('/{form}/toggle-published', [\App\Http\Controllers\Admin\ProgramFormController::class, 'togglePublished'])->name('toggle-published');
            Route::post('/{form}/clone', [\App\Http\Controllers\Admin\ProgramFormController::class, 'clone'])->name('clone');
            Route::get('/{form}/preview', [\App\Http\Controllers\Admin\ProgramFormController::class, 'preview'])->name('preview');
            Route::get('/{form}/submissions', [\App\Http\Controllers\Admin\ProgramFormController::class, 'submissions'])->name('submissions');
            Route::get('/{form}/submissions/{submission}', [\App\Http\Controllers\Admin\ProgramFormController::class, 'showSubmission'])->name('submission');
            Route::post('/{form}/submissions/{submission}/review', [\App\Http\Controllers\Admin\ProgramFormController::class, 'reviewSubmission'])->name('submission.review');
        });
        
        // Form Templates
        Route::get('/templates/{template}/data', [\App\Http\Controllers\Admin\ProgramFormController::class, 'getTemplateData'])->name('admin.templates.data');
        Route::post('/settings/clear-cache', [\App\Http\Controllers\Admin\SystemSettingController::class, 'clearCache'])->name('admin.settings.clearCache');

        // Program Assignments Management
        Route::get('/assignments', [\App\Http\Controllers\Admin\ProgramAssignmentController::class, 'index'])->name('admin.assignments.index');
        Route::get('/assignments/create', [\App\Http\Controllers\Admin\ProgramAssignmentController::class, 'create'])->name('admin.assignments.create');
        Route::post('/assignments', [\App\Http\Controllers\Admin\ProgramAssignmentController::class, 'store'])->name('admin.assignments.store');
        Route::get('/assignments/{assignment}', [\App\Http\Controllers\Admin\ProgramAssignmentController::class, 'show'])->name('admin.assignments.show');
        Route::get('/assignments/{assignment}/edit', [\App\Http\Controllers\Admin\ProgramAssignmentController::class, 'edit'])->name('admin.assignments.edit');
        Route::put('/assignments/{assignment}', [\App\Http\Controllers\Admin\ProgramAssignmentController::class, 'update'])->name('admin.assignments.update');
        Route::delete('/assignments/{assignment}', [\App\Http\Controllers\Admin\ProgramAssignmentController::class, 'destroy'])->name('admin.assignments.destroy');
        Route::post('/assignments/bulk-assign', [\App\Http\Controllers\Admin\ProgramAssignmentController::class, 'bulkAssign'])->name('admin.assignments.bulk-assign');
        Route::get('/assignments/stats', [\App\Http\Controllers\Admin\ProgramAssignmentController::class, 'getStats'])->name('admin.assignments.stats');
    });
});
