<?php
declare(strict_types=1);

use yii\db\Migration;

final class m260923_000003_expand_gid_stock extends Migration
{
    public function safeUp(): void
    {
        $table = '{{%estoque_gid}}';
        $this->addColumn($table, 'nome_originario', $this->string(255)->after('descricao'));
        $this->addColumn($table, 'marca', $this->string(100)->after('nome_originario'));
        $this->addColumn($table, 'modelo', $this->string(100)->after('marca'));
        $this->addColumn($table, 'ano_modelo', $this->smallInteger()->unsigned()->after('modelo'));
        $this->addColumn($table, 'tipo_veiculo', $this->string(50)->after('ano_modelo'));
        $this->addColumn($table, 'placa_veiculo', $this->string(10)->after('tipo_veiculo'));
        $this->addColumn($table, 'chassi_veiculo', $this->string(21)->after('placa_veiculo'));
        $this->addColumn($table, 'grupo_gid', $this->integer()->unsigned()->after('chassi_veiculo'));
        $this->addColumn($table, 'situacao_peca', $this->smallInteger()->unsigned()->after('grupo_gid'));
        $this->addColumn($table, 'peca_acoplada', $this->char(1)->after('situacao_peca'));
        $this->addColumn($table, 'codigo_nota', $this->string(5)->after('peca_acoplada'));
        $this->addColumn($table, 'item_controlado', $this->char(1)->after('codigo_nota'));
        $this->addColumn($table, 'observacao', $this->text()->after('item_controlado'));
        $this->createIndex('idx_estoque_peca', $table, ['descricao', 'marca', 'modelo']);
        $this->createIndex('idx_estoque_placa', $table, 'placa_veiculo');
    }

    public function safeDown(): bool
    {
        echo "Migration GID nao reversivel automaticamente para preservar o estoque sincronizado.\n";
        return false;
    }
}
