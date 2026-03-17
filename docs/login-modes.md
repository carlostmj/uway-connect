# Modos de login com UWAY AUTH

O `uway-connect` suporta bem 2 estrategias de integracao.

## 1. Login com dados internos do app

Use quando o seu sistema mantem sua propria tabela de usuarios e quer sincronizar/criar a conta local a partir do UWAY AUTH.

Fluxo:

1. o usuario autentica no UWAY AUTH
2. o app recebe `access_token`
3. o app chama `userinfo`
4. o app cria ou atualiza o usuario interno
5. o app abre sessao local propria

Exemplo Laravel:

```php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use CarlosTMJ\UwayConnect\Laravel\UwayConnectFacade as UwayConnect;

class UwayInternalLoginController
{
    public function redirect(Request $request)
    {
        $auth = UwayConnect::createAuthorizationRequest();

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
```

Esse modo e o melhor quando:

- o app tem regras proprias de negocio
- o app precisa persistir usuarios localmente
- o app usa autorizacao e perfis internos

## 2. Login usando somente os dados do AUTH

Use quando o app nao quer manter usuario local e so precisa confiar no UWAY AUTH como origem unica da identidade.

Fluxo:

1. o usuario autentica no UWAY AUTH
2. o app recebe `access_token`
3. o app chama `userinfo`
4. o app guarda perfil e token na sessao
5. o app opera em cima da identidade remota

Exemplo Laravel:

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use CarlosTMJ\UwayConnect\Laravel\UwayConnectFacade as UwayConnect;

class UwayAuthOnlyLoginController
{
    public function redirect(Request $request)
    {
        $auth = UwayConnect::createAuthorizationRequest();

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

        $request->session()->put('uway_auth.token', $tokenSet->raw);
        $request->session()->put('uway_auth.user', $profile);

        return redirect('/dashboard');
    }
}
```

Esse modo e o melhor quando:

- o app e fino e nao quer manter cadastro proprio
- o AUTH deve ser a unica fonte de identidade
- o app so precisa de sessao e claims remotos

## Qual escolher?

- use `interno` quando o app tem usuarios e dominio proprio
- use `AUTH-only` quando o app quer delegar identidade 100% ao UWAY AUTH

O SDK nao te obriga a um unico modelo. Ele entrega o fluxo OAuth/OIDC, e o app consumidor escolhe como tratar a identidade depois do callback.
