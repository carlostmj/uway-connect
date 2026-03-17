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

        // Aqui voce dispara um job/queue para montar a exportacao.
        // Exemplo:
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
