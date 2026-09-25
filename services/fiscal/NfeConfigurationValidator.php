<?php
declare(strict_types=1);
namespace app\services\fiscal;

final class NfeConfigurationValidator
{
    public function errors(array $configuration, bool $requireCertificate = true): array
    {
        $errors = [];
        $required = [
            'issuer.cnpj' => 'CNPJ do emitente',
            'issuer.legalName' => 'razao social',
            'issuer.stateRegistration' => 'Inscricao Estadual',
            'issuer.taxRegime' => 'CRT/regime tributario',
            'issuer.cityCode' => 'codigo IBGE do municipio',
            'issuer.city' => 'municipio',
            'issuer.street' => 'logradouro',
            'issuer.number' => 'numero',
            'issuer.district' => 'bairro',
            'issuer.postalCode' => 'CEP',
        ];
        foreach ($required as $path => $label) {
            $value = $this->get($configuration, $path);
            if ($value === null || $value === '' || $value === 0) {
                $errors[] = "Configure {$label} ({$path}).";
            }
        }

        $cnpj = (string) $this->get($configuration, 'issuer.cnpj');
        if ($cnpj !== '' && !preg_match('/^\d{14}$/', $cnpj)) {
            $errors[] = 'O CNPJ do emitente deve possuir 14 digitos.';
        }
        $crt = (int) $this->get($configuration, 'issuer.taxRegime');
        if ($crt !== 0 && !in_array($crt, [1, 2, 3, 4], true)) {
            $errors[] = 'O CRT deve ser 1, 2, 3 ou 4.';
        }
        if (!in_array($configuration['environment'] ?? null, ['homologation', 'production'], true)) {
            $errors[] = 'Ambiente NF-e invalido.';
        }
        if (($configuration['environment'] ?? null) === 'production' && ($configuration['transport'] ?? null) === 'mock') {
            $errors[] = 'O simulador nao pode ser usado em producao.';
        }
        if ($requireCertificate) {
            $path = (string) ($configuration['certificatePath'] ?? '');
            if ($path === '' || !is_readable($path)) {
                $errors[] = 'Certificado A1/PFX da NF-e nao encontrado ou sem leitura.';
            }
        }
        return $errors;
    }

    public function assertValid(array $configuration, bool $requireCertificate = true): void
    {
        $errors = $this->errors($configuration, $requireCertificate);
        if ($errors !== []) {
            throw new \RuntimeException(implode(' ', $errors));
        }
    }

    private function get(array $values, string $path)
    {
        foreach (explode('.', $path) as $segment) {
            if (!is_array($values) || !array_key_exists($segment, $values)) {
                return null;
            }
            $values = $values[$segment];
        }
        return $values;
    }
}
