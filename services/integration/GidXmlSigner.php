<?php
declare(strict_types=1);

namespace app\services\integration;

final class GidXmlSigner
{
    private const DSIG = 'http://www.w3.org/2000/09/xmldsig#';
    private const C14N = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';

    public function signSoapRequest(string $xml, GidCertificate $certificate): string
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->preserveWhiteSpace = false;
        if (!$document->loadXML($xml, LIBXML_NONET | LIBXML_NOBLANKS)) {
            throw new \RuntimeException('O SOAP gerado para o GID nao e um XML valido.');
        }

        $xpath = new \DOMXPath($document);
        $targets = $xpath->query('//*[@Id]');
        if ($targets === false || $targets->length !== 1 || !$targets->item(0) instanceof \DOMElement) {
            throw new \RuntimeException('Nao foi encontrado um unico elemento com Id para assinatura GID.');
        }
        $target = $targets->item(0);
        $id = $target->getAttribute('Id');

        foreach (iterator_to_array($target->childNodes) as $child) {
            if ($child instanceof \DOMElement && $child->localName === 'Signature') {
                $target->removeChild($child);
            }
        }

        $digestValue = base64_encode(sha1((string) $target->C14N(false, false), true));
        // The WSDL declares the local Signature element as an unqualified field
        // whose type is ds:SignatureType; only its children use the DSig namespace.
        $signature = $document->createElement('Signature');
        $signedInfo = $document->createElementNS(self::DSIG, 'ds:SignedInfo');
        $canonicalization = $document->createElementNS(self::DSIG, 'ds:CanonicalizationMethod');
        $canonicalization->setAttribute('Algorithm', self::C14N);
        $signatureMethod = $document->createElementNS(self::DSIG, 'ds:SignatureMethod');
        $signatureMethod->setAttribute('Algorithm', self::DSIG . 'rsa-sha1');
        $reference = $document->createElementNS(self::DSIG, 'ds:Reference');
        $reference->setAttribute('URI', '#' . $id);
        $transforms = $document->createElementNS(self::DSIG, 'ds:Transforms');
        foreach ([self::DSIG . 'enveloped-signature', self::C14N] as $algorithm) {
            $transform = $document->createElementNS(self::DSIG, 'ds:Transform');
            $transform->setAttribute('Algorithm', $algorithm);
            $transforms->appendChild($transform);
        }
        $digestMethod = $document->createElementNS(self::DSIG, 'ds:DigestMethod');
        $digestMethod->setAttribute('Algorithm', self::DSIG . 'sha1');
        $digest = $document->createElementNS(self::DSIG, 'ds:DigestValue', $digestValue);
        $reference->appendChild($transforms);
        $reference->appendChild($digestMethod);
        $reference->appendChild($digest);
        $signedInfo->appendChild($canonicalization);
        $signedInfo->appendChild($signatureMethod);
        $signedInfo->appendChild($reference);
        $signature->appendChild($signedInfo);
        $target->appendChild($signature);

        $rawSignature = '';
        if (!openssl_sign((string) $signedInfo->C14N(false, false), $rawSignature, $certificate->privateKey, OPENSSL_ALGO_SHA1)) {
            throw new \RuntimeException('Falha ao assinar a mensagem XML do GID.');
        }

        $signature->appendChild($document->createElementNS(self::DSIG, 'ds:SignatureValue', base64_encode($rawSignature)));
        $keyInfo = $document->createElementNS(self::DSIG, 'ds:KeyInfo');
        $x509Data = $document->createElementNS(self::DSIG, 'ds:X509Data');
        $x509Data->appendChild($document->createElementNS(self::DSIG, 'ds:X509Certificate', $certificate->certificateBase64));
        $keyInfo->appendChild($x509Data);
        $signature->appendChild($keyInfo);

        return (string) $document->saveXML();
    }
}
