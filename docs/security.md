# Seguranca

## Boas praticas

- Sempre use HTTPS
- Use PKCE para clients publicos
- Valide o `state` no callback
- Guarde `code_verifier` em sessao (nunca no banco)
- Nao logue tokens completos
- Limite escopos ao minimo necessario

## Refresh token

- Guarde em storage seguro
- Revogue ao fazer logout



