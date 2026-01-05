<?php

use App\Http\Controllers\SSOController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [SSOController::class, 'redirect'])->name('login');
Route::get('/callback', [SSOController::class, 'callback']);

Route::middleware(['auth', 'role:VIEWER'])->group(function () {
    Route::get('/viewer', fn () => 'VIEWER Page');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    });
});

Route::get('/logout', function () {
    $idToken = session('id_token');
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    $logoutUrl =
        config('services.keycloak.base_url')
        . '/realms/' . config('services.keycloak.realm')
        . '/protocol/openid-connect/logout'
        . '?id_token_hint=' . $idToken
        . '&post_logout_redirect_uri=' . urlencode(url('/'));
    return redirect($logoutUrl);
});