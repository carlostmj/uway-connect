<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use CarlosTMJ\UwayConnect\Laravel\UwayConnectFacade as UwayConnect;

class UwayAuthOnlyLoginController
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

        $request->session()->put('uway_auth.token', $tokenSet->raw);
        $request->session()->put('uway_auth.user', $profile);

        return redirect('/dashboard');
    }
}
