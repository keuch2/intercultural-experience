<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminParticipantController;
use App\Http\Controllers\Admin\AdminProgramController;
use App\Http\Controllers\Admin\IeProgramController;
use App\Http\Controllers\Admin\YfuProgramController;
use App\Http\Controllers\Admin\AdminApplicationController;
use App\Http\Controllers\Admin\AdminDocumentController;
use App\Http\Controllers\Admin\AdminRewardController;
use App\Http\Controllers\Admin\AdminRedemptionController;
use App\Http\Controllers\Admin\AdminSupportTicketController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminProgramRequisiteController;
use App\Http\Controllers\Admin\AdminUserProgramRequisiteController;
use App\Http\Controllers\Admin\AdminCurrencyController;
use App\Http\Controllers\Admin\AdminAgentController;
use App\Http\Controllers\Agent\AgentController;

// Redirect root to admin login
Route::get('/', function () {
    return redirect()->route('login');
});

// Only admin authentication routes needed
Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('password/reset', [App\Http\Controllers\Auth\PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [App\Http\Controllers\Auth\PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [App\Http\Controllers\Auth\PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [App\Http\Controllers\Auth\PasswordResetController::class, 'reset'])->name('password.update');

// Agent Routes - Protected by auth and agent middleware
Route::middleware(['auth', 'agent'])->prefix('agent')->group(function () {
    // Dashboard
    Route::get('/', [AgentController::class, 'dashboard'])->name('agent.dashboard');
    
    // Participants Management
    Route::get('/participants', [AgentController::class, 'myParticipants'])->name('agent.participants.index');
    Route::get('/participants/create', [AgentController::class, 'createParticipant'])->name('agent.participants.create');
    Route::post('/participants', [AgentController::class, 'storeParticipant'])->name('agent.participants.store');
    Route::get('/participants/{id}', [AgentController::class, 'showParticipant'])->name('agent.participants.show');
    
    // Program Assignment
    Route::get('/participants/{id}/assign-program', [AgentController::class, 'assignProgramForm'])->name('agent.participants.assign-program');
    Route::post('/participants/{id}/assign-program', [AgentController::class, 'assignProgram'])->name('agent.participants.assign-program.store');
});

// Admin Routes - Protected by auth, admin middleware, and activity logging
Route::middleware(['auth', 'admin', 'activity.log'])->prefix('admin')->group(function () {
        // Dashboard
        Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        
        // Users Management
        Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('admin.users.show');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
        Route::get('/users/export', [AdminUserController::class, 'export'])->name('admin.users.export');
        
        // Participants Management
        Route::get('/participants', [AdminParticipantController::class, 'index'])->name('admin.participants.index');
        Route::get('/participants/create', [AdminParticipantController::class, 'create'])->name('admin.participants.create');
        Route::post('/participants', [AdminParticipantController::class, 'store'])->name('admin.participants.store');
        Route::get('/participants/export', [AdminParticipantController::class, 'export'])->name('admin.participants.export');
        Route::get('/participants/{participant}', [AdminParticipantController::class, 'show'])->name('admin.participants.show');
        Route::get('/participants/{participant}/edit', [AdminParticipantController::class, 'edit'])->name('admin.participants.edit');
        Route::put('/participants/{participant}', [AdminParticipantController::class, 'update'])->name('admin.participants.update');
        Route::delete('/participants/{participant}', [AdminParticipantController::class, 'destroy'])->name('admin.participants.destroy');
        
        // Agents Management
        Route::get('/agents', [AdminAgentController::class, 'index'])->name('admin.agents.index');
        Route::get('/agents/create', [AdminAgentController::class, 'create'])->name('admin.agents.create');
        Route::post('/agents', [AdminAgentController::class, 'store'])->name('admin.agents.store');
        Route::get('/agents/{id}', [AdminAgentController::class, 'show'])->name('admin.agents.show');
        Route::get('/agents/{id}/edit', [AdminAgentController::class, 'edit'])->name('admin.agents.edit');
        Route::put('/agents/{id}', [AdminAgentController::class, 'update'])->name('admin.agents.update');
        Route::delete('/agents/{id}', [AdminAgentController::class, 'destroy'])->name('admin.agents.destroy');
        Route::post('/agents/{id}/reset-password', [AdminAgentController::class, 'resetPassword'])->name('admin.agents.reset-password');
        
        // Bulk Import Management
        Route::get('/bulk-import', [App\Http\Controllers\Admin\AdminBulkImportController::class, 'index'])->name('admin.bulk-import.index');
        Route::get('/bulk-import/template/{type}', [App\Http\Controllers\Admin\AdminBulkImportController::class, 'downloadTemplate'])->name('admin.bulk-import.template');
        Route::post('/bulk-import/preview', [App\Http\Controllers\Admin\AdminBulkImportController::class, 'preview'])->name('admin.bulk-import.preview');
        Route::post('/bulk-import/import', [App\Http\Controllers\Admin\AdminBulkImportController::class, 'import'])->name('admin.bulk-import.import');
        
        // Invoices Management
        Route::get('/invoices', [App\Http\Controllers\Admin\AdminInvoiceController::class, 'index'])->name('admin.invoices.index');
        Route::get('/invoices/create', [App\Http\Controllers\Admin\AdminInvoiceController::class, 'create'])->name('admin.invoices.create');
        Route::post('/invoices', [App\Http\Controllers\Admin\AdminInvoiceController::class, 'store'])->name('admin.invoices.store');
        Route::get('/invoices/{id}', [App\Http\Controllers\Admin\AdminInvoiceController::class, 'show'])->name('admin.invoices.show');
        Route::get('/invoices/{id}/download', [App\Http\Controllers\Admin\AdminInvoiceController::class, 'downloadPDF'])->name('admin.invoices.download');
        Route::post('/invoices/{id}/mark-paid', [App\Http\Controllers\Admin\AdminInvoiceController::class, 'markAsPaid'])->name('admin.invoices.mark-paid');
        Route::put('/invoices/{id}/cancel', [App\Http\Controllers\Admin\AdminInvoiceController::class, 'cancel'])->name('admin.invoices.cancel');
        
        // Activity Logs Management
        Route::get('/activity-logs', [App\Http\Controllers\Admin\AdminActivityLogController::class, 'index'])->name('admin.activity-logs.index');
        Route::get('/activity-logs/{activityLog}', [App\Http\Controllers\Admin\AdminActivityLogController::class, 'show'])->name('admin.activity-logs.show');
        Route::get('/activity-logs/stats/data', [App\Http\Controllers\Admin\AdminActivityLogController::class, 'stats'])->name('admin.activity-logs.stats');
        
        // IE Programs Management
        Route::get('/ie-programs', [IeProgramController::class, 'index'])->name('admin.ie-programs.index');
        Route::get('/ie-programs/create', [IeProgramController::class, 'create'])->name('admin.ie-programs.create');
        Route::post('/ie-programs', [IeProgramController::class, 'store'])->name('admin.ie-programs.store');
        Route::get('/ie-programs/{program}', [IeProgramController::class, 'show'])->name('admin.ie-programs.show');
        Route::get('/ie-programs/{program}/edit', [IeProgramController::class, 'edit'])->name('admin.ie-programs.edit');
        Route::put('/ie-programs/{program}', [IeProgramController::class, 'update'])->name('admin.ie-programs.update');
        Route::delete('/ie-programs/{program}', [IeProgramController::class, 'destroy'])->name('admin.ie-programs.destroy');
        
        // YFU Programs Management
        Route::get('/yfu-programs', [YfuProgramController::class, 'index'])->name('admin.yfu-programs.index');
        Route::get('/yfu-programs/create', [YfuProgramController::class, 'create'])->name('admin.yfu-programs.create');
        Route::post('/yfu-programs', [YfuProgramController::class, 'store'])->name('admin.yfu-programs.store');
        Route::get('/yfu-programs/{program}', [YfuProgramController::class, 'show'])->name('admin.yfu-programs.show');
        Route::get('/yfu-programs/{program}/edit', [YfuProgramController::class, 'edit'])->name('admin.yfu-programs.edit');
        Route::put('/yfu-programs/{program}', [YfuProgramController::class, 'update'])->name('admin.yfu-programs.update');
        Route::delete('/yfu-programs/{program}', [YfuProgramController::class, 'destroy'])->name('admin.yfu-programs.destroy');
        
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
        
        Route::get('/applications/{application}/requisites', [AdminUserProgramRequisiteController::class, 'index'])->name('admin.applications.requisites.index');
        Route::put('/applications/requisites/{requisite}/verify', [AdminUserProgramRequisiteController::class, 'verify'])->name('admin.applications.requisites.verify');
        Route::put('/applications/requisites/{requisite}/reject', [AdminUserProgramRequisiteController::class, 'reject'])->name('admin.applications.requisites.reject');
        Route::put('/applications/requisites/{requisite}/reset', [AdminUserProgramRequisiteController::class, 'reset'])->name('admin.applications.requisites.reset');
        
        // Documents Management
        Route::get('/documents', [AdminDocumentController::class, 'index'])->name('admin.documents.index');
        Route::get('/documents/{document}', [AdminDocumentController::class, 'show'])->name('admin.documents.show');
        Route::post('/documents/{document}/verify', [AdminDocumentController::class, 'verify'])->name('admin.documents.verify');
        Route::post('/documents/{document}/reject', [AdminDocumentController::class, 'reject'])->name('admin.documents.reject');
        Route::get('/documents/{document}/download', [AdminDocumentController::class, 'download'])->name('admin.documents.download');
        Route::delete('/documents/{document}', [AdminDocumentController::class, 'destroy'])->name('admin.documents.destroy');
        
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
        Route::resource('currencies', AdminCurrencyController::class)->names([
            'index' => 'admin.currencies.index',
            'create' => 'admin.currencies.create',
            'store' => 'admin.currencies.store',
            'show' => 'admin.currencies.show',
            'edit' => 'admin.currencies.edit',
            'update' => 'admin.currencies.update',
            'destroy' => 'admin.currencies.destroy',
        ]);
        Route::post('/currencies/update-rates', [AdminCurrencyController::class, 'updateRates'])->name('admin.currencies.updateRates');
        Route::get('/currencies/rates/api', [AdminCurrencyController::class, 'getRates'])->name('admin.currencies.getRates');
        Route::post('/currencies/convert', [AdminCurrencyController::class, 'convert'])->name('admin.currencies.convert');

        // Finance Management
        Route::get('/finance', [\App\Http\Controllers\Admin\AdminFinanceController::class, 'index'])->name('admin.finance.index');
        Route::get('/finance/payments', [\App\Http\Controllers\Admin\AdminFinanceController::class, 'payments'])->name('admin.finance.payments');
        Route::get('/finance/payments/create', [\App\Http\Controllers\Admin\AdminFinanceController::class, 'createPayment'])->name('admin.finance.payments.create');
        Route::post('/finance/payments', [\App\Http\Controllers\Admin\AdminFinanceController::class, 'storePayment'])->name('admin.finance.payments.store');
        Route::post('/finance/payments/{payment}/verify', [\App\Http\Controllers\Admin\AdminFinanceController::class, 'verifyPayment'])->name('admin.finance.payments.verify');
        Route::post('/finance/payments/{payment}/reject', [\App\Http\Controllers\Admin\AdminFinanceController::class, 'rejectPayment'])->name('admin.finance.payments.reject');
        Route::post('/finance/payments/{payment}/pending', [\App\Http\Controllers\Admin\AdminFinanceController::class, 'pendingPayment'])->name('admin.finance.payments.pending');
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
        Route::get('/reports/applications', [AdminReportController::class, 'applications'])->name('admin.reports.applications');
        Route::get('/reports/applications/export', [AdminReportController::class, 'exportApplications'])->name('admin.reports.applications.export');
        Route::get('/reports/users', [AdminReportController::class, 'users'])->name('admin.reports.users');
        Route::get('/reports/users/export', [AdminReportController::class, 'exportUsers'])->name('admin.reports.users.export');
        Route::get('/reports/rewards', [AdminReportController::class, 'rewards'])->name('admin.reports.rewards');
        Route::get('/reports/rewards/export', [AdminReportController::class, 'exportRewards'])->name('admin.reports.rewards.export');
        
        // Program Reports (NEW)
        Route::get('/reports/programs', [\App\Http\Controllers\Admin\AdminProgramReportController::class, 'index'])->name('admin.reports.programs.index');
        Route::get('/reports/programs/{program}', [\App\Http\Controllers\Admin\AdminProgramReportController::class, 'programDetail'])->name('admin.reports.programs.detail');
        Route::get('/reports/programs/{program}/export', [\App\Http\Controllers\Admin\AdminProgramReportController::class, 'exportProgram'])->name('admin.reports.programs.export');
        Route::get('/reports/users-overview', [\App\Http\Controllers\Admin\AdminProgramReportController::class, 'usersOverview'])->name('admin.reports.users.overview');
        Route::get('/reports/users/{user}/detail', [\App\Http\Controllers\Admin\AdminProgramReportController::class, 'userDetail'])->name('admin.reports.users.detail');
        Route::get('/reports/users/export-all', [\App\Http\Controllers\Admin\AdminProgramReportController::class, 'exportUsers'])->name('admin.reports.users.export.all');
        
        // Financial Reports
        Route::get('/reports', [AdminReportController::class, 'index'])->name('admin.reports.index');
        Route::get('/reports/programs', [AdminReportController::class, 'programs'])->name('admin.reports.programs');
        Route::get('/reports/currencies', [AdminReportController::class, 'currencies'])->name('admin.reports.currencies');
        Route::get('/reports/monthly', [AdminReportController::class, 'monthly'])->name('admin.reports.monthly');
        Route::get('/reports/export', [AdminReportController::class, 'export'])->name('admin.reports.export');
        
        // Activity Logs (NEW)
        Route::get('/activity-logs', [\App\Http\Controllers\Admin\AdminActivityLogController::class, 'index'])->name('admin.activity-logs.index');
        Route::get('/activity-logs/{activityLog}', [\App\Http\Controllers\Admin\AdminActivityLogController::class, 'show'])->name('admin.activity-logs.show');
        Route::get('/api/activity-logs/model', [\App\Http\Controllers\Admin\AdminActivityLogController::class, 'getModelLogs'])->name('admin.activity-logs.model');
        Route::get('/api/activity-logs/stats', [\App\Http\Controllers\Admin\AdminActivityLogController::class, 'stats'])->name('admin.activity-logs.stats');
        Route::post('/api/activity-logs/cleanup', [\App\Http\Controllers\Admin\AdminActivityLogController::class, 'cleanup'])->name('admin.activity-logs.cleanup');
        Route::get('/api/activity-logs/export', [\App\Http\Controllers\Admin\AdminActivityLogController::class, 'export'])->name('admin.activity-logs.export');

        // System Settings
        Route::get('/settings', [\App\Http\Controllers\Admin\AdminSystemSettingController::class, 'index'])->name('admin.settings.index');
        Route::post('/settings', [\App\Http\Controllers\Admin\AdminSystemSettingController::class, 'update'])->name('admin.settings.update');
        Route::get('/settings/cache/clear', [\App\Http\Controllers\Admin\AdminSystemSettingController::class, 'clearCache'])->name('admin.settings.cache.clear');
        Route::get('/settings/logs', [\App\Http\Controllers\Admin\AdminSystemSettingController::class, 'logs'])->name('admin.settings.logs');
        Route::get('/settings/backup', [\App\Http\Controllers\Admin\AdminSystemSettingController::class, 'backup'])->name('admin.settings.backup');
        Route::post('/settings/backup', [\App\Http\Controllers\Admin\AdminSystemSettingController::class, 'createBackup'])->name('admin.settings.backup.create');

        // Program Forms
        Route::prefix('programs/{program}/forms')->name('admin.programs.forms.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AdminProgramFormController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\AdminProgramFormController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\AdminProgramFormController::class, 'store'])->name('store');
            Route::post('/from-template', [\App\Http\Controllers\Admin\AdminProgramFormController::class, 'createFromTemplate'])->name('from-template');
            Route::get('/{form}', [\App\Http\Controllers\Admin\AdminProgramFormController::class, 'show'])->name('show');
            Route::get('/{form}/edit', [\App\Http\Controllers\Admin\AdminProgramFormController::class, 'edit'])->name('edit');
            Route::put('/{form}', [\App\Http\Controllers\Admin\AdminProgramFormController::class, 'update'])->name('update');
            Route::delete('/{form}', [\App\Http\Controllers\Admin\AdminProgramFormController::class, 'destroy'])->name('destroy');
            Route::post('/{form}/toggle-active', [\App\Http\Controllers\Admin\AdminProgramFormController::class, 'toggleActive'])->name('toggle-active');
            Route::post('/{form}/toggle-published', [\App\Http\Controllers\Admin\AdminProgramFormController::class, 'togglePublished'])->name('toggle-published');
            Route::post('/{form}/clone', [\App\Http\Controllers\Admin\AdminProgramFormController::class, 'clone'])->name('clone');
            Route::get('/{form}/preview', [\App\Http\Controllers\Admin\AdminProgramFormController::class, 'preview'])->name('preview');
            Route::get('/{form}/submissions', [\App\Http\Controllers\Admin\AdminProgramFormController::class, 'submissions'])->name('submissions');
            Route::get('/{form}/submissions/{submission}', [\App\Http\Controllers\Admin\AdminProgramFormController::class, 'showSubmission'])->name('submission');
            Route::post('/{form}/submissions/{submission}/review', [\App\Http\Controllers\Admin\AdminProgramFormController::class, 'reviewSubmission'])->name('submission.review');
        });
        
        // Form Templates
        Route::get('/templates/{template}/data', [\App\Http\Controllers\Admin\AdminProgramFormController::class, 'getTemplateData'])->name('admin.templates.data');
        Route::post('/settings/clear-cache', [\App\Http\Controllers\Admin\AdminSystemSettingController::class, 'clearCache'])->name('admin.settings.clearCache');

        // Program Assignments Management
        Route::get('/assignments', [\App\Http\Controllers\Admin\AdminProgramAssignmentController::class, 'index'])->name('admin.assignments.index');
        Route::get('/assignments/create', [\App\Http\Controllers\Admin\AdminProgramAssignmentController::class, 'create'])->name('admin.assignments.create');
        Route::post('/assignments', [\App\Http\Controllers\Admin\AdminProgramAssignmentController::class, 'store'])->name('admin.assignments.store');
        Route::get('/assignments/{assignment}', [\App\Http\Controllers\Admin\AdminProgramAssignmentController::class, 'show'])->name('admin.assignments.show');
        Route::get('/assignments/{assignment}/edit', [\App\Http\Controllers\Admin\AdminProgramAssignmentController::class, 'edit'])->name('admin.assignments.edit');
        Route::put('/assignments/{assignment}', [\App\Http\Controllers\Admin\AdminProgramAssignmentController::class, 'update'])->name('admin.assignments.update');
        Route::delete('/assignments/{assignment}', [\App\Http\Controllers\Admin\AdminProgramAssignmentController::class, 'destroy'])->name('admin.assignments.destroy');
        Route::post('/assignments/bulk-assign', [\App\Http\Controllers\Admin\AdminProgramAssignmentController::class, 'bulkAssign'])->name('admin.assignments.bulk-assign');
        Route::get('/assignments/stats', [\App\Http\Controllers\Admin\AdminProgramAssignmentController::class, 'getStats'])->name('admin.assignments.stats');
    });
