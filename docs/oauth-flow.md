# Fluxo OAuth 2.1 (Authorization Code + PKCE)

## 1) Gerar PKCE e state

- `code_verifier` (43-128 chars)
- `code_challenge = BASE64URL(SHA256(verifier))`
- `state` aleatorio para proteger contra CSRF

## 2) Redirecionar

Endpoint: `/oauth/authorize`

Parametros obrigatorios:
- `response_type=code`
- `client_id`
- `redirect_uri`
- `scope`
- `state`
- `code_challenge`
- `code_challenge_method=S256`

Opcional (Cadastro com UWAY):
- `screen=signup` (abre direto a tela de cadastro quando o usuario nao esta logado)

## 3) Callback

- validar `state`
- pegar `code`
- trocar `code` + `code_verifier` por tokens em `/oauth/token`

## 4) Userinfo

GET `/oauth/userinfo` com header `Authorization: Bearer <access_token>`

## Erros comuns

- `invalid_grant`: code expirado ou verifier incorreto
- `invalid_client`: client_id/secret invalidos
- `invalid_scope`: escopo nao permitido para o client
- `state invalido`: state diferente do salvo na sessao



