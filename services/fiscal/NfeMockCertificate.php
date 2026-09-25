<?php
declare(strict_types=1);
namespace app\services\fiscal;

use NFePHP\Common\Certificate;

final class NfeMockCertificate
{
    public static function create(string $cnpj): Certificate
    {
        $options = [
            'config' => 'K:/xampp/php/extras/openssl/openssl.cnf',
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];
        $privateKey = openssl_pkey_new($options);
        $csr = $privateKey ? openssl_csr_new([
            'countryName' => 'BR',
            'organizationName' => 'NFDETRANRS SIMULACAO ' . $cnpj,
            'commonName' => 'CERTIFICADO LOCAL SEM VALOR FISCAL',
        ], $privateKey, $options) : false;
        $x509 = $csr ? openssl_csr_sign($csr, null, $privateKey, 2, $options) : false;
        $pkcs12 = '';
        if (!$x509 || !openssl_pkcs12_export($x509, $pkcs12, $privateKey, 'nfdetranrs-mock')) {
            throw new \RuntimeException('Nao foi possivel criar o certificado efemero do simulador NF-e.');
        }
        return Certificate::readPfx($pkcs12, 'nfdetranrs-mock');
    }
}
