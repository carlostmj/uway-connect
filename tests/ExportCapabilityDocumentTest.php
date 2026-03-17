<?php

declare(strict_types=1);

namespace CarlosTMJ\UwayConnect\Tests;

use PHPUnit\Framework\TestCase;
use CarlosTMJ\UwayConnect\ExportCapabilityDocument;

final class ExportCapabilityDocumentTest extends TestCase
{
    public function testBuildsStandardExportCapabilityDocument(): void
    {
        $document = ExportCapabilityDocument::make([
            'start' => '/internal/user-exports',
            'status' => '/internal/user-exports/{exportId}',
            'manifest' => '/internal/user-exports/{exportId}/manifest',
            'file' => '/internal/user-exports/{exportId}/files/{fileId}',
        ]);

        $payload = $document->toArray();

        $this->assertSame('uway-user-export', $payload['service']);
        $this->assertSame('1.0', $payload['version']);
        $this->assertSame('/internal/user-exports', $payload['endpoints']['start']);
        $this->assertSame('client_credentials', $payload['auth']['method']);
        $this->assertSame('uway_user_id', $payload['user_identifier']['primary']);
    }
}
