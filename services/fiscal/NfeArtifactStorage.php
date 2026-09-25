<?php
declare(strict_types=1);
namespace app\services\fiscal;

use NFePHP\DA\NFe\Danfe;

final class NfeArtifactStorage
{
    public function __construct(private string $basePath)
    {
    }

    /** @return array{xml:string,pdf:string} */
    public function store(string $xml, string $accessKey, \DateTimeInterface $issuedAt): array
    {
        if (!preg_match('/^\d{44}$/', $accessKey)) {
            throw new \InvalidArgumentException('Chave invalida para armazenamento da NF-e.');
        }
        $directory = rtrim($this->basePath, '/\\') . DIRECTORY_SEPARATOR . $issuedAt->format('Y') . DIRECTORY_SEPARATOR . $issuedAt->format('m');
        if (!is_dir($directory) && !mkdir($directory, 0770, true) && !is_dir($directory)) {
            throw new \RuntimeException('Nao foi possivel criar o diretorio protegido de NF-e.');
        }
        $xmlPath = $directory . DIRECTORY_SEPARATOR . $accessKey . '-nfe.xml';
        $pdfPath = $directory . DIRECTORY_SEPARATOR . $accessKey . '-danfe.pdf';
        if (file_put_contents($xmlPath, $xml, LOCK_EX) === false) {
            throw new \RuntimeException('Nao foi possivel armazenar o XML simulado.');
        }
        try {
            $danfe = new Danfe($xml);
            $danfe->creditsIntegratorFooter('SIMULACAO LOCAL NFDETRANRS - SEM VALOR FISCAL', false);
            $pdf = $danfe->render();
            if (file_put_contents($pdfPath, $pdf, LOCK_EX) === false) {
                throw new \RuntimeException('Nao foi possivel armazenar o DANFE simulado.');
            }
        } catch (\Throwable $exception) {
            @unlink($xmlPath);
            throw $exception;
        }
        return ['xml' => $xmlPath, 'pdf' => $pdfPath];
    }
}
