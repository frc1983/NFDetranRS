<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use app\services\integration\GidCertificate;
use app\services\integration\GidXmlSigner;

$randomSeedPath = sys_get_temp_dir() . '/nfdetranrs-openssl-test.rnd';
putenv('RANDFILE=' . $randomSeedPath);

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$opensslOptions = [
    'config' => 'K:/xampp/php/extras/openssl/openssl.cnf',
    'digest_alg' => 'sha256',
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
];
$privateKey = openssl_pkey_new($opensslOptions);
$csr = openssl_csr_new([
    'countryName' => 'BR',
    'organizationName' => 'OFICINA DA MOTO 05034500000168',
    'commonName' => 'NFDetranRS Test',
], $privateKey, $opensslOptions);
$x509 = openssl_csr_sign($csr, null, $privateKey, 2, $opensslOptions);
$pkcs12 = '';
openssl_pkcs12_export($x509, $pkcs12, $privateKey, 'test-password');
$path = tempnam(sys_get_temp_dir(), 'gid-cert-');
file_put_contents($path, $pkcs12);

try {
    $certificate = GidCertificate::fromPkcs12($path, 'test-password', '05034500000168');
    $unsigned = '<?xml version="1.0"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"><SOAP-ENV:Body><pesquisarEstoqueGid><PESQUISAR_ESTOQUE Id="CDV00027"><infPesquisarEstoque><CDV><COD_CREDENCIADO>CDV00027</COD_CREDENCIADO></CDV></infPesquisarEstoque><Signature/></PESQUISAR_ESTOQUE></pesquisarEstoqueGid></SOAP-ENV:Body></SOAP-ENV:Envelope>';
    $signed = (new GidXmlSigner())->signSoapRequest($unsigned, $certificate);
    $document = new DOMDocument();
    $assert($document->loadXML($signed), 'XML assinado invalido.');
    $xpath = new DOMXPath($document);
    $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
    $signedInfo = $xpath->query('//ds:SignedInfo')->item(0);
    $signatureValue = $xpath->evaluate('string(//ds:SignatureValue)');
    $assert($signedInfo instanceof DOMElement, 'SignedInfo ausente.');
    $signatureElement = $xpath->query('//*[local-name()="Signature"]')->item(0);
    $assert($signatureElement instanceof DOMElement && $signatureElement->namespaceURI === null, 'Elemento Signature deve permanecer sem namespace conforme o WSDL.');
    $assert(openssl_verify((string) $signedInfo->C14N(false, false), base64_decode($signatureValue), $certificate->certificatePem, OPENSSL_ALGO_SHA1) === 1, 'Assinatura RSA-SHA1 invalida.');
    $assert($xpath->evaluate('string(//ds:Reference/@URI)') === '#CDV00027', 'Referencia da assinatura incorreta.');
    $assert($certificate->fingerprint() !== '', 'Fingerprint ausente.');
    echo "gid signature tests: OK\n";
} finally {
    @unlink($path);
    @unlink($randomSeedPath);
}
