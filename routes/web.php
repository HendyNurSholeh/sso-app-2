<?php

use App\Http\Controllers\SSOController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [SSOController::class, 'redirect'])->name('login');
Route::get('/callback', [SSOController::class, 'callback']);

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



Route::post('/sso/backchannel-logout', function (Request $request) {

    $logoutToken = $request->input('logout_token');

    if (!$logoutToken) {
        return response()->json(['error' => 'logout_token missing'], 400);
    }

    /*
     * BEST PRACTICE (Minimal Validation)
     * - Validasi issuer (iss)
     * - Validasi audience (aud)
     * - Pastikan event backchannel-logout ada
     */

    // TODO (opsional production):
    // - Verify JWT signature via JWKS Keycloak

    // Hapus session lokal
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();

    return response()->json(['status' => 'logged_out']);
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);



