<?php
declare(strict_types=1);
namespace app\services\fiscal;

final class NfeDraftValidator
{
    public function assertValid(array $invoice): void
    {
        foreach (['stateCode', 'issuedAt', 'issuer', 'recipient', 'items', 'total'] as $field) {
            if (!array_key_exists($field, $invoice)) {
                throw new \InvalidArgumentException("Campo obrigatorio ausente na NF-e: {$field}.");
            }
        }
        if (!is_array($invoice['items']) || count($invoice['items']) < 1 || count($invoice['items']) > 100) {
            throw new \InvalidArgumentException('A NF-e deve possuir entre 1 e 100 itens neste sistema.');
        }
        $issuerCnpj = preg_replace('/\D+/', '', (string) ($invoice['issuer']['cnpj'] ?? ''));
        if (strlen($issuerCnpj) !== 14 || empty($invoice['issuer']['stateRegistration']) || !in_array((int) ($invoice['issuer']['taxRegime'] ?? 0), [1, 2, 3, 4], true)) {
            throw new \InvalidArgumentException('Emitente fiscal incompleto: informe CNPJ, IE e CRT validos.');
        }
        $recipientDocument = preg_replace('/\D+/', '', (string) ($invoice['recipient']['document'] ?? ''));
        if (!in_array(strlen($recipientDocument), [11, 14], true) || trim((string) ($invoice['recipient']['name'] ?? '')) === '') {
            throw new \InvalidArgumentException('Destinatario deve possuir CPF/CNPJ e nome.');
        }

        $sum = 0;
        foreach ($invoice['items'] as $index => $item) {
            $number = $index + 1;
            foreach (['code', 'description', 'ncm', 'cfop', 'unit', 'quantity', 'unitPrice', 'total', 'origin', 'pisCst', 'cofinsCst'] as $field) {
                if (!isset($item[$field]) || $item[$field] === '') {
                    throw new \InvalidArgumentException("Item {$number}: campo fiscal {$field} ausente.");
                }
            }
            if (!preg_match('/^\d{8}$/', preg_replace('/\D+/', '', (string) $item['ncm']))) {
                throw new \InvalidArgumentException("Item {$number}: NCM deve possuir 8 digitos.");
            }
            if (!preg_match('/^\d{4}$/', (string) $item['cfop'])) {
                throw new \InvalidArgumentException("Item {$number}: CFOP deve possuir 4 digitos.");
            }
            if (!isset($item['icmsCst']) && !isset($item['csosn'])) {
                throw new \InvalidArgumentException("Item {$number}: informe CST de ICMS ou CSOSN.");
            }
            $expected = round((float) $item['quantity'] * (float) $item['unitPrice'], 2);
            if (abs($expected - (float) $item['total']) > 0.009) {
                throw new \InvalidArgumentException("Item {$number}: total difere de quantidade x valor unitario.");
            }
            $sum += (int) round((float) $item['total'] * 100);
        }
        if ($sum !== (int) round((float) $invoice['total'] * 100)) {
            throw new \InvalidArgumentException('Total da NF-e difere da soma dos itens.');
        }
    }
}
