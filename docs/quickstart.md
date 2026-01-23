# Quickstart (OAuth completo)

## 1) Instale

```bash
composer require carlostmj/uway-connect
```

## 2) Configure

```php
use CarlosTMJ\UwayConnect\Config;
use CarlosTMJ\UwayConnect\UwayConnect;

$uway = new UwayConnect(new Config(
    baseUrl: 'https://auth.uway.com.br',
    clientId: 'SEU_CLIENT_ID',
    clientSecret: null,
    redirectUri: 'https://seu-app.com/auth/uway/callback',
    defaultScopes: ['openid', 'profile', 'email']
));
```

## 3) Redirecione o usuario (PKCE + state)

```php
session_start();

$auth = $uway->createAuthorizationRequest();

$_SESSION['uway_state'] = $auth->state;
$_SESSION['uway_verifier'] = $auth->codeVerifier;

header('Location: '.$auth->url);
exit;
```

### 3.1) Cadastro com UWAY (opcional)

Use quando quiser abrir direto a tela de cadastro do UWAY Auth.

```php
session_start();

$auth = $uway->createSignupRequest(
    scopes: ['openid', 'profile', 'email']
);

$_SESSION['uway_state'] = $auth->state;
$_SESSION['uway_verifier'] = $auth->codeVerifier;

header('Location: '.$auth->url);
exit;
```

## 4) Callback

```php
session_start();

$tokenSet = $uway->exchangeCodeFromCallback(
    $_GET,
    $_SESSION['uway_state'] ?? '',
    $_SESSION['uway_verifier'] ?? ''
);

$profile = $uway->userInfo($tokenSet->accessToken);
```




