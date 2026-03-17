# PHP puro (completo)

## Setup

```php
use CarlosTMJ\UwayConnect\Config;
use CarlosTMJ\UwayConnect\UwayConnect;

$uway = new UwayConnect(new Config(
    baseUrl: 'https://auth.uway.com.br',
    clientId: 'SEU_CLIENT_ID',
    clientSecret: null,
    redirectUri: 'https://seu-app.com/auth/uway/callback',
    defaultScopes: ['basic', 'openid']
));
```

## Rota /login

```php
session_start();

$auth = $uway->createAuthorizationRequest([
    'basic', 'openid'
]);

$_SESSION['uway_state'] = $auth->state;
$_SESSION['uway_verifier'] = $auth->codeVerifier;

header('Location: '.$auth->url);
exit;
```

## Rota /signup (Cadastro com UWAY)

```php
session_start();

$auth = $uway->createSignupRequest([
    'basic', 'openid'
]);

$_SESSION['uway_state'] = $auth->state;
$_SESSION['uway_verifier'] = $auth->codeVerifier;

header('Location: '.$auth->url);
exit;
```

## Rota /callback

```php
session_start();

try {
    $tokenSet = $uway->exchangeCodeFromCallback(
        $_GET,
        $_SESSION['uway_state'] ?? '',
        $_SESSION['uway_verifier'] ?? ''
    );

    $profile = $uway->userInfo($tokenSet->accessToken);

    // autentique seu usuario aqui
} catch (\CarlosTMJ\UwayConnect\UwayConnectException $e) {
    // tratar erro
}
```

## Refresh token

```php
$newTokens = $uway->refreshToken($tokenSet->refreshToken);
```

## Discovery do AUTH

```php
$discovery = $uway->discovery();

$accountCenter = $discovery->accountCenterEndpoint;
```

## Erros comuns

- `state invalido`: state diferente do salvo na sessao
- `authorization code ausente`: callback sem `code`
- `invalid_grant`: verifier incorreto ou code expirado



