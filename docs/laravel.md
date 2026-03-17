# Laravel (completo)

## Publicar config

```bash
php artisan vendor:publish --tag=uway-connect
```

## .env

```
UWAY_AUTH_BASE_URL=https://auth.uway.com.br
UWAY_AUTH_CLIENT_ID=...
UWAY_AUTH_CLIENT_SECRET=...
UWAY_AUTH_REDIRECT_URI=https://seu-app.com/auth/uway/callback
UWAY_AUTH_SCOPES="basic openid"
```

## Rotas

```php
use App\Http\Controllers\UwayAuthController;

Route::get('/auth/uway', [UwayAuthController::class, 'redirect'])->name('uway.redirect');
Route::get('/auth/uway/signup', [UwayAuthController::class, 'signup'])->name('uway.signup');
Route::get('/auth/uway/callback', [UwayAuthController::class, 'callback'])->name('uway.callback');
```

## Controller

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use CarlosTMJ\UwayConnect\Laravel\UwayConnectFacade as UwayConnect;

class UwayAuthController extends Controller
{
    public function redirect(Request $request)
    {
        $auth = UwayConnect::createAuthorizationRequest();

        $request->session()->put('uway_state', $auth->state);
        $request->session()->put('uway_verifier', $auth->codeVerifier);

        return redirect()->away($auth->url);
    }

    public function signup(Request $request)
    {
        $auth = UwayConnect::createSignupRequest();

        $request->session()->put('uway_state', $auth->state);
        $request->session()->put('uway_verifier', $auth->codeVerifier);

        return redirect()->away($auth->url);
    }

    public function callback(Request $request)
    {
        $tokenSet = UwayConnect::exchangeCodeFromCallback(
            $request->query(),
            (string) $request->session()->pull('uway_state'),
            (string) $request->session()->pull('uway_verifier')
        );

        $profile = UwayConnect::userInfo($tokenSet->accessToken);

        // crie ou autentique o usuario aqui
        return response()->json($profile);
    }
}
```

## Observacoes

- Sempre use HTTPS
- Para apps publicos, nao envie client_secret
- Guarde state/verifier somente em sessao
- O AUTH devolve `success/error` com `data`, e o SDK ja desempacota isso
- O discovery do AUTH inclui atalhos para `/account`, `/account/profile` e `/account/security`



