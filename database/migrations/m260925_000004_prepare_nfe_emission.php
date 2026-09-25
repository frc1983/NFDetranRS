<?php
declare(strict_types=1);
use yii\db\Migration;

final class m260925_000004_prepare_nfe_emission extends Migration
{
    private string $opt = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

    public function safeUp(): void
    {
        $this->addColumn('{{%estoque_gid}}', 'ncm', $this->char(8)->after('descricao'));
        $this->addColumn('{{%estoque_gid}}', 'cest', $this->char(7)->after('ncm'));
        $this->addColumn('{{%estoque_gid}}', 'cfop', $this->char(4)->after('cest'));
        $this->addColumn('{{%estoque_gid}}', 'unidade_comercial', $this->string(6)->after('cfop'));
        $this->addColumn('{{%estoque_gid}}', 'origem_mercadoria', $this->char(1)->after('unidade_comercial'));
        $this->addColumn('{{%estoque_gid}}', 'icms_cst', $this->char(3)->after('origem_mercadoria'));
        $this->addColumn('{{%estoque_gid}}', 'pis_cst', $this->char(2)->after('icms_cst'));
        $this->addColumn('{{%estoque_gid}}', 'cofins_cst', $this->char(2)->after('pis_cst'));

        $this->addColumn('{{%nfe}}', 'ambiente', "ENUM('homologation','production') NOT NULL DEFAULT 'homologation' AFTER [[lote]]");
        $this->addColumn('{{%nfe}}', 'modelo', $this->smallInteger()->unsigned()->notNull()->defaultValue(55)->after('ambiente'));
        $this->addColumn('{{%nfe}}', 'serie', $this->integer()->unsigned()->after('modelo'));
        $this->addColumn('{{%nfe}}', 'numero', $this->integer()->unsigned()->after('serie'));
        $this->addColumn('{{%nfe}}', 'simulada', $this->boolean()->notNull()->defaultValue(false)->after('status'));
        $this->addColumn('{{%nfe}}', 'xml_hash', $this->char(64)->after('xml_caminho'));
        $this->createIndex('uq_nfe_numeracao', '{{%nfe}}', ['ambiente', 'modelo', 'serie', 'numero'], true);

        $this->createTable('{{%nfe_numeracao}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'ambiente' => "ENUM('homologation','production') NOT NULL",
            'modelo' => $this->smallInteger()->unsigned()->notNull()->defaultValue(55),
            'serie' => $this->integer()->unsigned()->notNull(),
            'ultimo_numero' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $this->opt);
        $this->createIndex('uq_nfe_numeracao_ambiente', '{{%nfe_numeracao}}', ['ambiente', 'modelo', 'serie'], true);
        $this->batchInsert('{{%nfe_numeracao}}', ['ambiente', 'modelo', 'serie', 'ultimo_numero'], [
            ['homologation', 55, 1, 0],
            ['production', 55, 1, 0],
        ]);
    }

    public function safeDown(): bool
    {
        echo "Migration fiscal nao reversivel automaticamente para preservar numeracao e documentos.\n";
        return false;
    }
}
