<?php
declare(strict_types=1);
use yii\db\Migration;

final class m260925_000005_expand_nfe_recipient_and_artifacts extends Migration
{
    public function safeUp(): void
    {
        $this->batchInsert('{{%configuracao}}', ['chave', 'valor', 'secreta', 'descricao'], [
            ['recipient.state_registration', null, 0, 'Inscricao Estadual do destinatario'],
            ['recipient.street', null, 0, 'Logradouro do destinatario'],
            ['recipient.number', null, 0, 'Numero do destinatario'],
            ['recipient.complement', null, 0, 'Complemento do destinatario'],
            ['recipient.district', null, 0, 'Bairro do destinatario'],
            ['recipient.city_code', null, 0, 'Codigo IBGE do municipio do destinatario'],
            ['recipient.city', null, 0, 'Municipio do destinatario'],
            ['recipient.state', 'RS', 0, 'UF do destinatario'],
            ['recipient.postal_code', null, 0, 'CEP do destinatario'],
            ['recipient.email', null, 0, 'E-mail do destinatario'],
            ['recipient.phone', null, 0, 'Telefone do destinatario'],
        ]);

        $table = '{{%venda}}';
        $this->addColumn($table, 'destinatario_ie', $this->string(14)->after('destinatario_nome'));
        $this->addColumn($table, 'destinatario_logradouro', $this->string(190)->after('destinatario_ie'));
        $this->addColumn($table, 'destinatario_numero', $this->string(20)->after('destinatario_logradouro'));
        $this->addColumn($table, 'destinatario_complemento', $this->string(100)->after('destinatario_numero'));
        $this->addColumn($table, 'destinatario_bairro', $this->string(100)->after('destinatario_complemento'));
        $this->addColumn($table, 'destinatario_municipio_codigo', $this->integer()->unsigned()->after('destinatario_bairro'));
        $this->addColumn($table, 'destinatario_municipio', $this->string(100)->after('destinatario_municipio_codigo'));
        $this->addColumn($table, 'destinatario_uf', $this->char(2)->after('destinatario_municipio'));
        $this->addColumn($table, 'destinatario_cep', $this->char(8)->after('destinatario_uf'));
        $this->addColumn($table, 'destinatario_email', $this->string(190)->after('destinatario_cep'));
        $this->addColumn($table, 'destinatario_telefone', $this->string(20)->after('destinatario_email'));
        $this->addColumn('{{%nfe}}', 'danfe_caminho', $this->string(500)->after('xml_caminho'));
    }

    public function safeDown(): bool
    {
        echo "Migration fiscal nao reversivel automaticamente para preservar snapshots e documentos.\n";
        return false;
    }
}
