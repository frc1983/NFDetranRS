<?php
declare(strict_types=1);
namespace app\commands;

use app\services\fiscal\NfeConfigurationValidator;
use yii\console\Controller;
use yii\console\ExitCode;
use Yii;

final class NfeController extends Controller
{
    public function actionDoctor(): int
    {
        $configuration = Yii::$app->params['nfe'];
        $this->stdout("Ambiente: {$configuration['environment']}\n");
        $this->stdout("Transporte: {$configuration['transport']}\n");
        $this->stdout("Layout: {$configuration['layoutVersion']} / {$configuration['schemaPackage']}\n");
        $this->stdout("Autorizacao: {$configuration['authorizationUrl']}\n");

        $requireCertificate = $configuration['transport'] === 'sefaz';
        $errors = (new NfeConfigurationValidator())->errors($configuration, $requireCertificate);
        if ($errors !== []) {
            foreach ($errors as $error) {
                $this->stderr("[PENDENTE] {$error}\n");
            }
            return ExitCode::CONFIG;
        }
        $mode = $configuration['transport'] === 'mock' ? 'simulador local' : 'conexao SEFAZ';
        $this->stdout("Configuracao fiscal pronta para {$mode}.\n");
        return ExitCode::OK;
    }
}
