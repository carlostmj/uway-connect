<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use CarlosTMJ\UwayConnect\Laravel\UwayConnectFacade as UwayConnect;

class UwayInternalLoginController
{
    public function redirect(Request $request): RedirectResponse
    {
        $auth = UwayConnect::createAuthorizationRequest();

        $request->session()->put('uway_state', $auth->state);
        $request->session()->put('uway_verifier', $auth->codeVerifier);

        return redirect()->away($auth->url);
    }

    public function callback(Request $request): RedirectResponse
    {
        $tokenSet = UwayConnect::exchangeCodeFromCallback(
            $request->query(),
            (string) $request->session()->pull('uway_state'),
            (string) $request->session()->pull('uway_verifier')
        );

        $profile = UwayConnect::userInfo($tokenSet->accessToken);

        $user = User::query()->updateOrCreate(
            ['email' => $profile['email'] ?? null],
            [
                'name' => $profile['name'] ?? 'Conta UWAY',
                'email' => $profile['email'] ?? null,
                'uway_user_id' => $profile['sub'],
            ]
        );

        Auth::login($user);

        return redirect('/dashboard');
    }
}
