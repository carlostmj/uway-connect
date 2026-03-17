# UWAY Connect

SDK oficial para integrar OAuth 2.1 e OpenID Connect do **UWAY Auth** em PHP puro e Laravel.

- Fluxo Authorization Code + PKCE (OAuth 2.1)
- `client_credentials` para integrações server-to-server
- Helpers para state/PKCE
- Discovery OpenID do AUTH (`/.well-known/openid-configuration`)
- Troca de code por tokens e `userinfo`
- Integração simples no Laravel

## Instalacao

```bash
composer require carlostmj/uway-connect
```

## Fluxo completo (PHP puro)

### 1) Criar instancia

```php
use CarlosTMJ\UwayConnect\Config;
use CarlosTMJ\UwayConnect\UwayConnect;

$uway = new UwayConnect(new Config(
    baseUrl: 'https://auth.uway.com.br',
    clientId: 'SEU_CLIENT_ID',
    clientSecret: null, // public client (SPA/mobile)
    redirectUri: 'https://seu-app.com/auth/uway/callback',
    defaultScopes: ['basic', 'openid']
));
```

### 2) Gerar URL de consentimento (PKCE + state)

```php
session_start();

$request = $uway->createAuthorizationRequest([
    'basic', 'openid'
]);

// guarde na sessao
$_SESSION['uway_state'] = $request->state;
$_SESSION['uway_verifier'] = $request->codeVerifier;

header('Location: '.$request->url);
exit;
```


### 2.1) Cadastro com UWAY (opcional)

```php
session_start();

$request = $uway->createSignupRequest([
    'basic', 'openid'
]);

$_SESSION['uway_state'] = $request->state;
$_SESSION['uway_verifier'] = $request->codeVerifier;

header('Location: '.$request->url);
exit;
```

### 3) Callback: validar state e trocar code por token

```php
session_start();

$tokenSet = $uway->exchangeCodeFromCallback(
    $_GET,
    $_SESSION['uway_state'] ?? '',
    $_SESSION['uway_verifier'] ?? ''
);

$user = $uway->userInfo($tokenSet->accessToken);
```

## Laravel (exemplo completo)

### .env

```
UWAY_AUTH_BASE_URL=https://auth.uway.com.br
UWAY_AUTH_CLIENT_ID=...
UWAY_AUTH_CLIENT_SECRET=...
UWAY_AUTH_REDIRECT_URI=https://seu-app.com/auth/uway/callback
UWAY_AUTH_SCOPES="basic openid"
```

### Rotas

```php
Route::get('/auth/uway', [UwayAuthController::class, 'redirect'])->name('uway.redirect');
Route::get('/auth/uway/signup', [UwayAuthController::class, 'signup'])->name('uway.signup');
Route::get('/auth/uway/callback', [UwayAuthController::class, 'callback'])->name('uway.callback');
```

### Controller

```php
use Illuminate\Http\Request;
use CarlosTMJ\UwayConnect\Laravel\UwayConnectFacade as UwayConnect;

class UwayAuthController
{
    public function redirect(Request $request)
    {
        $authRequest = UwayConnect::createAuthorizationRequest();

        $request->session()->put('uway_state', $authRequest->state);
        $request->session()->put('uway_verifier', $authRequest->codeVerifier);

        return redirect()->away($authRequest->url);
    }


    public function signup(Request $request)
    {
        $authRequest = UwayConnect::createSignupRequest();

        $request->session()->put('uway_state', $authRequest->state);
        $request->session()->put('uway_verifier', $authRequest->codeVerifier);

        return redirect()->away($authRequest->url);
    }

    public function callback(Request $request)
    {
        $tokenSet = UwayConnect::exchangeCodeFromCallback(
            $request->query(),
            (string) $request->session()->pull('uway_state'),
            (string) $request->session()->pull('uway_verifier')
        );

        $profile = UwayConnect::userInfo($tokenSet->accessToken);

        // aqui voce cria/loga o usuario
        return response()->json($profile);
    }
}
```

## Discovery do AUTH

```php
$discovery = $uway->discovery();

$discovery->accountCenterEndpoint;   // /account
$discovery->accountProfileEndpoint;  // /account/profile
$discovery->accountSecurityEndpoint; // /account/security
```

## Client credentials

```php
$tokens = $uway->clientCredentialsToken(['basic']);
```

## Tratamento de erros

Qualquer falha gera `UwayConnectException` com:
- `statusCode`
- `payload` (dados brutos do endpoint)

```php
try {
    $tokenSet = $uway->exchangeCodeFromCallback($_GET, $state, $verifier);
} catch (\CarlosTMJ\UwayConnect\UwayConnectException $e) {
    // log e tratamento
}
```

## Documentacao completa

Veja `docs/` para:
- `quickstart.md`
- `oauth-flow.md`
- `php.md`
- `laravel.md`
- `security.md`

## Licenca

MIT




