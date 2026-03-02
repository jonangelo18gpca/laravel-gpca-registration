<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\DelegateController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [RegistrationController::class, 'homepageView'])->name('homepage.view');

Route::prefix('admin')->group(function () {
    Route::middleware(['isAdmin'])->group(function () {
        Route::get('/logout', [AdminController::class, 'logout'])->name('admin.logout');
        Route::get('/dashboard', [AdminController::class, 'dashboardView'])->name('admin.dashboard.view');

        Route::prefix('event')->group(function () {
            Route::get('/', [EventController::class, 'manageEventView'])->name('admin.event.view');
            Route::get('/add', [EventController::class, 'addEventView'])->name('admin.event.add.view');
            Route::post('/add', [EventController::class, 'addEvent'])->name('admin.event.add.post');

            Route::prefix('{eventCategory}/{eventId}')->group(function () {
                Route::get('/edit', [EventController::class, 'eventEditView'])->name('admin.event.edit.view');
                Route::post('/edit', [EventController::class, 'updateEvent'])->name('admin.event.edit.post');

                Route::post('/update-status/{eventStatus}', [EventController::class, 'updateEventStatus'])->name('admin.event.update.status.post');

                Route::get('/dashboard', [EventController::class, 'eventDashboardView'])->name('admin.event.dashboard.view');
                Route::get('/detail', [EventController::class, 'eventDetailView'])->name('admin.event.detail.view');
                Route::get('/registration-type', [EventController::class, 'eventRegistrationType'])->name('admin.event.registration-type.view');
                Route::get('/delegate-fees', [EventController::class, 'eventDelegateFeesView'])->name('admin.event.delegate-fees.view');
                Route::get('/promo-code', [EventController::class, 'eventPromoCodeView'])->name('admin.event.promo-codes.view');
                Route::get('/promo-code/export', [EventController::class, 'exportListOfPromoCodes'])->name('admin.event.promo-codes.export.data');
                
                Route::get('/promo-code/template', [EventController::class, 'downloadPromoCodeTemplate'])
                    ->name('admin.event.promo-codes.template');

                Route::prefix('registrant')->group(function () {
                    Route::get('/', [RegistrationController::class, 'eventRegistrantsView'])->name('admin.event.registrants.view');
                    Route::get('/export', [RegistrationController::class, 'registrantsExportData'])->name('admin.event.registrants.exportData');
                    Route::get('/{registrantId}', [RegistrationController::class, 'registrantDetailView'])->name('admin.event.registrants.detail.view');
                    Route::get('/{registrantId}/view-invoice', [RegistrationController::class, 'generateAdminInvoice'])->name('admin.event.registrants.view.invoice');
                });
                Route::prefix('delegate')->group(function () {
                    Route::get('/', [DelegateController::class, 'eventDelegateView'])->name('admin.event.delegates.view');
                    Route::get('/add-to-grip', [DelegateController::class, 'addDelegatesToGripView'])->name('admin.add.delegates.to.grip.view');
                    Route::get('/update-logs', [DelegateController::class, 'updateLogsView'])->name('admin.update.logs.view');
                    Route::get('/{delegateType}/{delegateId}', [DelegateController::class, 'delegateDetailView'])->name('admin.event.delegates.detail.view');
                    Route::get('/{delegateType}/{delegateId}/print-badge', [DelegateController::class, 'delegateDetailPrintBadge'])->name('admin.event.delegates.detail.printBadge');
                    Route::get('/{delegateType}/{delegateId}/scan-badge', [DelegateController::class, 'delegateDetailScanBadge'])->name('admin.event.delegates.detail.scanBadge');
                });
                Route::get('/email-broadcast/{badgeCategory}', [DelegateController::class, 'eventEmailBroadcastView'])->name('admin.event.email-broadcast.view');

                Route::prefix('printed-badge')->group(function () {
                    Route::get('/', [DelegateController::class, 'printedBadgeListView'])->name('admin.printed.badge.list.view');
                });

                Route::prefix('scanned-delegate')->group(function () {
                    Route::get('/', [DelegateController::class, 'scannedDelegateListView'])->name('admin.scanned.delegate.list.view');
                    Route::get('/export-all', [DelegateController::class, 'scannedDelegateExportAllData'])->name('admin.event.scanned.delegate.exportAllData');
                    Route::get('/export-categorized', [DelegateController::class, 'scannedDelegateExportCategorizedData'])->name('admin.event.scanned.delegate.exportCategorizedData');
                    Route::get('/categorized', [DelegateController::class, 'scannedDelegateListCategorizedView'])->name('admin.scanned.delegate.categorized.list.view');
                });
                // Route::get('/onsite/register/', [RegistrationController::class, 'eventOnsiteRegistrationView'])->name('admin.event.onsite.register.view');

                Route::get('/scan-qr', [DelegateController::class, 'scanQrView'])->name('scan.qr');
            });
        });

        Route::get('/member', [MemberController::class, 'manageMemberView'])->name('admin.member.view');
        Route::get('/member/export', [MemberController::class, 'exportListOfMembers'])->name('admin.member.export.data');
        Route::get('/delegate', [DelegateController::class, 'manageDelegateView'])->name('admin.delegate.view');
    });


    Route::get('/login', [AdminController::class, 'loginView'])->name('admin.login.view');
    Route::post('/login', [AdminController::class, 'login'])->name('admin.login.post');
});

Route::prefix('register/{eventYear}/{eventCategory}/{eventId}')->group(function () {
    Route::get('/', [RegistrationController::class, 'registrationView'])->name('register.view');
    Route::get('/otp', [RegistrationController::class, 'registrationOTPView'])->name('register.otp.view');
    Route::get('/{mainDelegateId}/{status}/loading', [RegistrationController::class, 'registrationLoadingView'])->name('register.loading.view');
    Route::get('/{mainDelegateId}/success', [RegistrationController::class, 'registrationSuccessView'])->name('register.success.view');
    Route::get('/{mainDelegateId}/failed', [RegistrationController::class, 'registrationFailedView'])->name('register.failed.view');
});



Route::prefix('event/{eventCategory}/{eventId}/digital-helper')->group(function () {
    Route::get('/', [DelegateController::class, 'digitalHelper'])->name('digital.helper.view');
    Route::get('/faq', [DelegateController::class, 'digitalHelperFAQ'])->name('digital.helper.faq.view');
});

Route::post('capturePayment', [RegistrationController::class, 'capturePayment'])->name('register.capture.payment');

Route::get('/{eventCategory}/{eventId}/view-invoice/{registrantId}', [RegistrationController::class, 'generatePublicInvoice'])->name('generate-public-invoice');
Route::get('/{eventCategory}/{eventId}/print-badge/{delegateType}/{delegateId}', [DelegateController::class, 'delegateDetailPublicPrintBadge'])->name('public-print-badge');

Route::get('/download-file/{documentId}', [RegistrationController::class, 'downloadFile'])->name('download-file');
Route::get('/{eventCategory}/{eventId}/download-file/{documentId}', [RegistrationController::class, 'eventDownloadFile'])->name('event-download-file');

// Route::get('/fast-track', function (){
//     return view('home.fast_track');
// })->name('fast-track');

// Route::get('/phpinfo', function () {
//     phpinfo();
// });