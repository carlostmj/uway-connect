# Exportacao de dados entre apps

O UWAY AUTH consegue consolidar dados do usuario vindos de apps conectados, mas isso so funciona quando cada app implementa o contrato padrao de exportacao.

## Endpoint obrigatorio

Todo app participante deve publicar:

```text
/.well-known/uway-user-export
```

Esse endpoint deve responder JSON com:

```json
{
  "service": "uway-user-export",
  "version": "1.0",
  "endpoints": {
    "start": "/internal/user-exports",
    "status": "/internal/user-exports/{exportId}",
    "manifest": "/internal/user-exports/{exportId}/manifest",
    "file": "/internal/user-exports/{exportId}/files/{fileId}",
    "callback": "/internal/user-exports/callbacks/auth"
  },
  "auth": {
    "method": "client_credentials",
    "scope": "internal.user_exports.read"
  },
  "user_identifier": {
    "primary": "uway_user_id",
    "fallback": ["email"]
  }
}
```

No AUTH, a validacao atual exige pelo menos:

- `service`
- `version`
- `endpoints.start`
- `endpoints.status`
- `endpoints.manifest`
- `endpoints.file`

## Builder no SDK

O `uway-connect` inclui um helper para gerar esse documento:

```php
use CarlosTMJ\UwayConnect\ExportCapabilityDocument;

$document = ExportCapabilityDocument::make([
    'start' => '/internal/user-exports',
    'status' => '/internal/user-exports/{exportId}',
    'manifest' => '/internal/user-exports/{exportId}/manifest',
    'file' => '/internal/user-exports/{exportId}/files/{fileId}',
    'callback' => '/internal/user-exports/callbacks/auth',
]);

return response()->json($document->toArray());
```

## Exemplo Laravel completo

Rotas sugeridas:

```php
use App\Http\Controllers\Internal\UserExportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->group(function (): void {
    Route::get('/.well-known/uway-user-export', [UserExportController::class, 'capability']);
    Route::post('/internal/user-exports', [UserExportController::class, 'start'])
        ->name('internal.user-exports.start');
    Route::get('/internal/user-exports/{exportId}', [UserExportController::class, 'status'])
        ->name('internal.user-exports.status');
    Route::get('/internal/user-exports/{exportId}/manifest', [UserExportController::class, 'manifest'])
        ->name('internal.user-exports.manifest');
    Route::get('/internal/user-exports/{exportId}/files/{fileId}', [UserExportController::class, 'file'])
        ->name('internal.user-exports.file');
});
```

Controller completo:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Internal;

use CarlosTMJ\UwayConnect\ExportCapabilityDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserExportController extends Controller
{
    public function capability(): JsonResponse
    {
        $document = ExportCapabilityDocument::make([
            'start' => '/internal/user-exports',
            'status' => '/internal/user-exports/{exportId}',
            'manifest' => '/internal/user-exports/{exportId}/manifest',
            'file' => '/internal/user-exports/{exportId}/files/{fileId}',
            'callback' => '/internal/user-exports/callbacks/auth',
        ]);

        return response()->json($document->toArray());
    }

    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'request_id' => ['required', 'string', 'max:100'],
            'user.uway_user_id' => ['required', 'string', 'max:100'],
            'user.email' => ['nullable', 'email'],
            'scopes' => ['sometimes', 'array'],
            'scopes.*' => ['string'],
            'requested_at' => ['nullable', 'string'],
            'callback_url' => ['nullable', 'url'],
        ]);

        // GeneratePartnerExportJob::dispatch($validated);

        return response()->json([
            'status' => 'accepted',
            'export_id' => 'exp_demo_123',
            'estimated_ready_in_seconds' => 120,
        ], 202);
    }

    public function status(string $exportId): JsonResponse
    {
        return response()->json([
            'export_id' => $exportId,
            'status' => 'completed',
            'manifest_url' => route('internal.user-exports.manifest', ['exportId' => $exportId]),
            'expires_at' => now()->addDays(3)->toIso8601String(),
        ]);
    }

    public function manifest(string $exportId): JsonResponse
    {
        return response()->json([
            'app' => [
                'key' => 'crm_x',
                'name' => 'CRM X',
            ],
            'user' => [
                'uway_user_id' => 'uuid-do-usuario',
            ],
            'datasets' => [
                'contacts' => [
                    ['id' => 'c_1', 'name' => 'Maria Silva', 'email' => 'maria@example.com'],
                ],
                'activities' => [
                    ['id' => 'a_1', 'type' => 'note', 'created_at' => now()->toIso8601String()],
                ],
            ],
            'files' => [
                [
                    'external_id' => 'file_123',
                    'name' => 'contrato.pdf',
                    'size_bytes' => 245760,
                    'mime' => 'application/pdf',
                    'category' => 'documents',
                    'label' => 'Contrato principal',
                    'download_url' => route('internal.user-exports.file', [
                        'exportId' => $exportId,
                        'fileId' => 'file_123',
                    ]),
                ],
            ],
        ]);
    }

    public function file(string $exportId, string $fileId): BinaryFileResponse
    {
        $absolutePath = storage_path('app/private/user-exports/demo/contrato.pdf');

        return Response::download($absolutePath, 'contrato.pdf');
    }
}
```

Esse exemplo mostra:

- documento `/.well-known/uway-user-export`
- endpoint `start`
- endpoint `status`
- endpoint `manifest`
- endpoint `file`
- payload minimo esperado pelo AUTH

## Fluxo esperado

1. O usuario solicita exportacao no AUTH.
2. O AUTH descobre quais apps conectados participam da exportacao.
3. O AUTH consulta `/.well-known/uway-user-export` de cada app.
4. O AUTH chama o endpoint `start`.
5. O AUTH consulta `status` ou recebe callback.
6. O AUTH baixa o `manifest`.
7. O AUTH baixa os arquivos do endpoint `file`.
8. O AUTH consolida tudo em um unico pacote.

## Manifesto esperado

O manifesto do app deve devolver:

- datasets estruturados do usuario naquele app
- lista de arquivos exportaveis
- metadados minimos de cada arquivo

Exemplo:

```json
{
  "app": {
    "key": "crm_x",
    "name": "CRM X"
  },
  "user": {
    "uway_user_id": "uuid-do-usuario"
  },
  "datasets": {
    "contacts": [],
    "activities": []
  },
  "files": [
    {
      "external_id": "file_123",
      "name": "contrato.pdf",
      "size_bytes": 245760,
      "mime": "application/pdf",
      "category": "documents",
      "label": "Contrato principal",
      "download_url": "https://app.example.com/internal/user-exports/exp_1/files/file_123"
    }
  ]
}
```

## Identificacao do usuario

O ideal no ecossistema UWAY e que todos os apps armazenem:

- `uway_user_id` como identificador primario
- `email` apenas como fallback

Sem isso, a exportacao cruzada fica fragil.

## Recomendacoes

- use autenticacao service-to-service
- prefira `client_credentials`
- nao dependa da sessao do usuario
- limite o tempo de vida dos downloads
- registre auditoria da exportacao
- trate falha parcial por app sem quebrar a exportacao inteira
