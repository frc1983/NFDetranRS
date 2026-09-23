<?php
declare(strict_types=1);
namespace app\services\integration;
final class StubGidClient implements GidClientInterface
{
    public function fetchInventory(string $correlationId): iterable
    {
        throw new \RuntimeException('Integracao GID nao configurada; credenciais devem ficar fora do codigo.');
    }
}
