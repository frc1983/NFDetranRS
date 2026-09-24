<?php
declare(strict_types=1);

namespace app\services\integration;

final class GidCertificate
{
    private function __construct(
        public \OpenSSLAsymmetricKey $privateKey,
        public string $certificatePem,
        public string $certificateBase64,
        public array $details,
    ) {
    }

    public static function fromPkcs12(string $path, string $password, string $expectedCnpj): self
    {
        if ($path === '' || !is_file($path) || !is_readable($path)) {
            throw new \RuntimeException('Certificado GID A1 nao encontrado ou sem permissao de leitura. Configure GID_CERT_PATH.');
        }

        $contents = file_get_contents($path);
        $certificates = [];
        if ($contents === false || !openssl_pkcs12_read($contents, $certificates, $password)) {
            throw new \RuntimeException('Nao foi possivel abrir o certificado GID. Verifique o arquivo A1 e a senha.');
        }

        $privateKey = openssl_pkey_get_private((string) ($certificates['pkey'] ?? ''));
        $certificatePem = (string) ($certificates['cert'] ?? '');
        $details = openssl_x509_parse($certificatePem) ?: [];
        if (!$privateKey || $certificatePem === '' || $details === []) {
            throw new \RuntimeException('O PKCS#12 nao contem certificado e chave privada validos.');
        }

        $now = time();
        if (($details['validFrom_time_t'] ?? PHP_INT_MAX) > $now || ($details['validTo_time_t'] ?? 0) <= $now) {
            throw new \RuntimeException('O certificado GID ainda nao e valido ou esta expirado.');
        }

        $expectedCnpj = preg_replace('/\D+/', '', $expectedCnpj) ?? '';
        $searchableDetails = preg_replace('/\D+/', '', json_encode($details, JSON_UNESCAPED_UNICODE) ?: '') ?? '';
        if (strlen($expectedCnpj) !== 14 || !str_contains($searchableDetails, $expectedCnpj)) {
            throw new \RuntimeException('O CNPJ do certificado nao corresponde ao CNPJ do CDV configurado.');
        }

        $certificateBase64 = preg_replace('/-----BEGIN CERTIFICATE-----|-----END CERTIFICATE-----|\s+/', '', $certificatePem) ?? '';
        return new self($privateKey, $certificatePem, $certificateBase64, $details);
    }

    public function expiresAt(): ?\DateTimeImmutable
    {
        $timestamp = (int) ($this->details['validTo_time_t'] ?? 0);
        return $timestamp > 0 ? (new \DateTimeImmutable())->setTimestamp($timestamp) : null;
    }

    public function fingerprint(): string
    {
        return (string) (openssl_x509_fingerprint($this->certificatePem, 'sha256') ?: '');
    }
}
