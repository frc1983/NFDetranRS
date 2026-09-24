<?php
declare(strict_types=1);

namespace app\commands;

use app\services\integration\GidInventorySynchronizer;
use app\services\integration\GidSoapClient;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

final class GidController extends Controller
{
    public function actionDoctor(): int
    {
        $client = Yii::$app->gidClient;
        if (!$client instanceof GidSoapClient) {
            $this->stderr("Cliente GID real nao configurado.\n");
            return ExitCode::CONFIG;
        }
        try {
            $certificate = $client->certificate();
            $client->assertWsdlAvailable();
            $this->stdout("CDV: " . Yii::$app->params['company']['cdvCode'] . "\n");
            $this->stdout("CNPJ: " . Yii::$app->params['company']['cnpj'] . "\n");
            $this->stdout("Ambiente: " . Yii::$app->params['gid']['environment'] . "\n");
            $this->stdout("WSDL: " . Yii::$app->params['gid']['wsdlUrl'] . "\n");
            $this->stdout("Certificado SHA-256: " . $certificate->fingerprint() . "\n");
            $this->stdout("Valido ate: " . ($certificate->expiresAt()?->format(DATE_ATOM) ?? 'desconhecido') . "\n");
            $this->stdout("Certificado e WSDL GID prontos para teste.\n");
            return ExitCode::OK;
        } catch (\Throwable $exception) {
            $this->stderr($exception->getMessage() . "\n");
            return ExitCode::CONFIG;
        }
    }

    public function actionSync(): int
    {
        try {
            $result = (new GidInventorySynchronizer(Yii::$app->gidClient))->synchronize();
            $this->stdout(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n");
            return ExitCode::OK;
        } catch (\Throwable $exception) {
            $this->stderr($exception->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
}
