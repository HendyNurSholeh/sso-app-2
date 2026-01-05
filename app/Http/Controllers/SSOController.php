<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class SSOController extends Controller
{
    public function redirect()
    {
        $query = http_build_query([
            'client_id'     => config('services.keycloak.client_id'),
            'response_type' => 'code',
            'scope'         => 'openid profile email',
            'redirect_uri'  => url('/callback'),
        ]);

        return redirect(
            config('services.keycloak.base_url')
            . '/realms/' . config('services.keycloak.realm')
            . '/protocol/openid-connect/auth?' . $query
        );
    }

    public function callback(Request $request)
    {
        $token = Http::asForm()->post(
            config('services.keycloak.base_url')
            . '/realms/' . config('services.keycloak.realm')
            . '/protocol/openid-connect/token',
            [
                'grant_type'   => 'authorization_code',
                'client_id'    => config('services.keycloak.client_id'),
                'code'         => $request->code,
                'redirect_uri' => url('/callback'),
            ]
        )->json();

         $roles = $token['access_token']
            ? json_decode(base64_decode(explode('.', $token['access_token'])[1]), true)
                ['resource_access'][config('services.keycloak.client_id')]['roles'] ?? []
            : [];

        session([
            'id_token' => $token['id_token'],
            'roles' => $roles
        ]);

        $userInfo = Http::withToken($token['access_token'])->get(
            config('services.keycloak.base_url')
            . '/realms/' . config('services.keycloak.realm')
            . '/protocol/openid-connect/userinfo'
        )->json();

        $user = \App\Models\User::firstOrCreate(
            ['email' => $userInfo['email'] ?? $userInfo['sub']],
            ['name'  => $userInfo['name'] ?? 'SSO User']
        );

        Auth::login($user);

        return redirect('/dashboard');
    }
}
