<?php
declare(strict_types=1);
namespace app\services\integration;

use app\services\fiscal\NfeAccessKey;
use app\services\fiscal\NfeConfigurationValidator;
use app\services\fiscal\NfeDraftValidator;
use yii\base\Component;

final class MockSefazClient extends Component implements SefazClientInterface
{
    public array $configuration = [];

    public function authorize(array $invoice, string $idempotencyKey, string $correlationId): IntegrationResult
    {
        if (($this->configuration['environment'] ?? null) !== 'homologation') {
            throw new \RuntimeException('O simulador SEFAZ somente funciona em homologacao.');
        }
        (new NfeConfigurationValidator())->assertValid($this->configuration, false);
        (new NfeDraftValidator())->assertValid($invoice);
        $issuedAt = new \DateTimeImmutable((string) $invoice['issuedAt']);
        $numericCode = substr(hash('sha256', $idempotencyKey), 0, 8);
        $numericCode = str_pad((string) (hexdec($numericCode) % 100000000), 8, '0', STR_PAD_LEFT);
        $key = NfeAccessKey::generate(
            (int) $invoice['stateCode'],
            $issuedAt,
            (string) $invoice['issuer']['cnpj'],
            55,
            (int) ($invoice['series'] ?? $this->configuration['series']),
            (int) $invoice['number'],
            1,
            $numericCode
        );
        $protocol = 'SIM' . substr(hash('sha256', $correlationId . $key), 0, 12);
        return new IntegrationResult(true, $protocol, '100-SIMULADO', 'Autorizacao simulada; sem valor fiscal.', [
            'accessKey' => $key,
            'protocol' => $protocol,
            'environment' => 'homologation',
            'simulated' => true,
        ]);
    }

    public function cancel(string $accessKey, string $reason, string $idempotencyKey, string $correlationId): IntegrationResult
    {
        if (!preg_match('/^\d{44}$/', $accessKey) || mb_strlen(trim($reason)) < 15) {
            return new IntegrationResult(false, null, 'SIM-VALIDATION', 'Chave invalida ou justificativa com menos de 15 caracteres.');
        }
        return new IntegrationResult(true, 'SIM-CAN-' . substr(hash('sha256', $idempotencyKey), 0, 12), '135-SIMULADO', 'Cancelamento simulado; sem valor fiscal.', ['simulated' => true]);
    }
}
