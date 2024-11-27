<?php

// Override Following urls
//Route::get('login', 'Auth\LoginController@showLoginForm')->name('login')->middleware('sub-domain-check');
//Route::get('email-verification/{code}', 'Auth\LoginController@getEmailVerification')->name('front.get-email-verification');
//Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
//Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
//
//Route::group(['middleware' => ['sub-domain-check', 'disable-frontend'], 'as' => 'front.', 'namespace' => '\App\Http\Controllers\Front'], function () {
//
//    Route::get('/', 'HomeController@index')->name('home');
//
//    Route::get('/contact', 'HomeController@contact')->name('contact');
//    Route::post('/contact-us', 'HomeController@contactUs')->name('contact-us');
//
//    Route::get('/feature', ['uses' => 'HomeController@feature'])->name('feature');
//    Route::get('/pricing', ['uses' => 'HomeController@pricing'])->name('pricing');
//
//    Route::resource('/signup', 'RegisterController', ['only' => ['index', 'store']]);
//});
//
//// New Routes specific to sub-domain
//Route::group(['middleware' => 'sub-domain-check'], function () {
//
//    Route::get('signin', 'SubdomainController@workspace')->name('front.workspace');
//    Route::get('forgot-company', 'SubdomainController@forgotCompany')->name('front.forgot-company');
//    Route::post('forgot-company', 'SubdomainController@submitForgotCompany')->name('front.submit-forgot-password');
//    Route::get('super-admin-login', 'Auth\LoginController@showSuperAdminLogin')->name('front.super-admin-login');
//});
//

//Route::get('push-notify-iframe', ['uses' => 'SubdomainController@iframe'])->name('push-notify-iframe');


use App\Http\Controllers\HomeController;
use App\Http\Controllers\SuperAdmin\CompanyRegisterController;
use App\Http\Controllers\SuperAdmin\FrontendController;
use App\Http\Middleware\DisableFrontend;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\ConfirmablePasswordController;
use Laravel\Fortify\Http\Controllers\ConfirmedPasswordStatusController;
use Laravel\Fortify\Http\Controllers\ConfirmedTwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\EmailVerificationNotificationController;
use Laravel\Fortify\Http\Controllers\EmailVerificationPromptController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\PasswordController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\ProfileInformationController;
use Laravel\Fortify\Http\Controllers\RecoveryCodeController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController;
use Laravel\Fortify\Http\Controllers\TwoFactorSecretKeyController;
use Laravel\Fortify\Http\Controllers\VerifyEmailController;
use Modules\Subdomain\Http\Controllers\SubdomainController;
use Modules\Subdomain\Http\Middleware\SubdomainCheck;

Route::group(['middleware' => ['web', SubdomainCheck::class, DisableFrontend::class]], function () {
    Route::get('/', [FrontendController::class, 'index'])->name('front.home');
    Route::get('/contact', [FrontendController::class, 'contact'])->name('front.contact');
    Route::post('/contact-us', [FrontendController::class, 'contactUs'])->name('front.contact-us');
    Route::get('/features', [FrontendController::class, 'feature'])->name('front.feature');
    Route::get('/pricing', [FrontendController::class, 'pricing'])->name('front.pricing');
//    Route::get('/signup', [SubdomainController::class, 'loadSignUpPage'])->name('front.signup.index');
    Route::post('check-domain', [SubdomainController::class, 'checkDomain'])->name('front.check-domain');

});

Route::group(['middleware' => ['web', SubdomainCheck::class]], function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login')->middleware('guest');
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
});

Route::group(['middleware' => SubdomainCheck::class], function () {
    Route::get('/super-admin-login', [AuthenticatedSessionController::class, 'create'])->middleware('guest');
    Route::post('/super-admin-login', [AuthenticatedSessionController::class, 'store'])->middleware('guest');
    Route::get('forgot-company', 'SubdomainController@forgotCompany')->name('front.forgot-company')->middleware('guest');
    Route::post('forgot-company', 'SubdomainController@submitForgotCompany')->name('front.submit-forgot-password')->middleware('guest');
    Route::get('signin', [SubdomainController::class, 'workspace'])->name('front.workspace');
    Route::get('signup', [CompanyRegisterController::class,'index'])->name('front.signup.index');
});


Route::group(['middleware' => 'auth', 'prefix' => 'account'], function () {
    Route::get('banned-subdomains', ['uses' => 'BannedSubdomainController@bannedDomain'])->name('super-admin.get.banned-subdomains');
    Route::put('banned-subdomains', ['uses' => 'BannedSubdomainController@bannedDomainSubmit'])->name('super-admin.post.banned-subdomains');
    Route::delete('banned-subdomains/{keyIndex}', ['uses' => 'BannedSubdomainController@deleteBannedDomain'])->name('super-admin.banned-subdomains.destroy');
    Route::post('notify/domain', [SubdomainController::class, 'notifyDomain'])->name('notify.domain');
});


