# Changelog

## 0.3.0 - 2026-03-16

- Alinhamento dos defaults e da documentacao com os escopos reais do UWAY AUTH
- Suporte a discovery OpenID (`/.well-known/openid-configuration`)
- Suporte a `client_credentials` no SDK
- Fallback de logger no provider Laravel para evitar erro de canal ausente
- Testes reais de fluxo PHP e integracao Laravel 12 com Testbench

## 0.2.0 - 2026-01-23

- Helpers completos para PKCE + state
- Metodo exchangeCodeFromCallback
- Suporte a cadastro com UWAY (createSignupRequest)
- Documentacao completa para PHP e Laravel

## 0.1.0 - 2026-01-23

- Primeira versao da SDK UWAY Connect
- Fluxo OAuth 2.1 com PKCE
- Suporte a Laravel via Service Provider + Facade

