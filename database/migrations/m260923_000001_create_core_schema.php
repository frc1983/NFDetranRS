<?php
declare(strict_types=1);
use yii\db\Migration;

final class m260923_000001_create_core_schema extends Migration
{
    private string $opt = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
    public function up(): void
    {
        $this->createTable('{{%usuario}}', [
            'id' => $this->bigPrimaryKey()->unsigned(), 'nome' => $this->string(150)->notNull(),
            'email' => $this->string(190)->notNull()->unique(), 'password_hash' => $this->string(255)->notNull(),
            'auth_key' => $this->string(64)->notNull(), 'status' => "ENUM('ativo','inativo','bloqueado') NOT NULL DEFAULT 'ativo'",
            'ultimo_login_em' => $this->dateTime(), 'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $this->opt);
        $this->createTable('{{%configuracao}}', [
            'id' => $this->bigPrimaryKey()->unsigned(), 'chave' => $this->string(190)->notNull()->unique(),
            'valor' => $this->text(), 'valor_cifrado' => $this->binary(), 'secreta' => $this->boolean()->notNull()->defaultValue(false),
            'descricao' => $this->string(255), 'updated_by' => $this->bigInteger()->unsigned(),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $this->opt);
        $this->addForeignKey('fk_config_usuario', '{{%configuracao}}', 'updated_by', '{{%usuario}}', 'id', 'SET NULL', 'RESTRICT');
        $this->batchInsert('{{%configuracao}}', ['chave', 'valor', 'secreta', 'descricao'], [['recipient.document', null, 0, 'Documento do destinatario fixo'], ['recipient.name', null, 0, 'Nome do destinatario fixo']]);
        $this->createTable('{{%estoque_gid}}', [
            'id' => $this->bigPrimaryKey()->unsigned(), 'gid_id' => $this->string(100)->notNull()->unique(),
            'codigo' => $this->string(100)->notNull(), 'descricao' => $this->string(255)->notNull(),
            'quantidade_disponivel' => $this->decimal(15, 4)->notNull()->defaultValue(0), 'valor_unitario' => $this->decimal(15, 2)->notNull()->defaultValue(0),
            'payload_hash' => $this->char(64)->notNull(), 'sincronizado_em' => $this->dateTime()->notNull(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $this->opt);
        $this->createIndex('idx_estoque_codigo', '{{%estoque_gid}}', 'codigo');
        $this->createTable('{{%venda}}', [
            'id' => $this->bigPrimaryKey()->unsigned(), 'numero' => $this->string(30)->notNull()->unique(),
            'status' => "ENUM('rascunho','confirmada','faturando','concluida','cancelada') NOT NULL DEFAULT 'rascunho'",
            'destinatario_documento' => $this->string(20)->notNull(), 'destinatario_nome' => $this->string(190)->notNull(),
            'valor_total' => $this->decimal(15, 2)->notNull()->defaultValue(0), 'idempotency_key' => $this->string(100)->notNull()->unique(),
            'correlation_id' => $this->char(36)->notNull(), 'created_by' => $this->bigInteger()->unsigned()->notNull(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $this->opt);
        $this->addForeignKey('fk_venda_usuario', '{{%venda}}', 'created_by', '{{%usuario}}', 'id', 'RESTRICT', 'RESTRICT');
        $this->createIndex('idx_venda_correlation', '{{%venda}}', 'correlation_id');
        $this->createTable('{{%venda_item}}', [
            'id' => $this->bigPrimaryKey()->unsigned(), 'venda_id' => $this->bigInteger()->unsigned()->notNull(),
            'estoque_gid_id' => $this->bigInteger()->unsigned()->notNull(), 'quantidade' => $this->decimal(15, 4)->notNull(),
            'valor_unitario' => $this->decimal(15, 2)->notNull(), 'valor_total' => $this->decimal(15, 2)->notNull(),
        ], $this->opt);
        $this->addForeignKey('fk_vi_venda', '{{%venda_item}}', 'venda_id', '{{%venda}}', 'id', 'CASCADE', 'RESTRICT');
        $this->addForeignKey('fk_vi_estoque', '{{%venda_item}}', 'estoque_gid_id', '{{%estoque_gid}}', 'id', 'RESTRICT', 'RESTRICT');
        $this->createTable('{{%nfe}}', [
            'id' => $this->bigPrimaryKey()->unsigned(), 'venda_id' => $this->bigInteger()->unsigned()->notNull(),
            'lote' => $this->integer()->unsigned()->notNull(), 'status' => "ENUM('pendente','processando','autorizada','rejeitada','cancelada') NOT NULL DEFAULT 'pendente'",
            'chave_acesso' => $this->char(44)->unique(), 'protocolo' => $this->string(50), 'xml_caminho' => $this->string(500),
            'valor_total' => $this->decimal(15, 2)->notNull()->defaultValue(0), 'idempotency_key' => $this->string(100)->notNull()->unique(),
            'correlation_id' => $this->char(36)->notNull(), 'tentativas' => $this->smallInteger()->unsigned()->notNull()->defaultValue(0),
            'proxima_tentativa_em' => $this->dateTime(), 'ultimo_erro' => $this->text(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $this->opt);
        $this->addForeignKey('fk_nfe_venda', '{{%nfe}}', 'venda_id', '{{%venda}}', 'id', 'RESTRICT', 'RESTRICT');
        $this->createIndex('uq_nfe_venda_lote', '{{%nfe}}', ['venda_id', 'lote'], true);
        $this->createIndex('idx_nfe_retry', '{{%nfe}}', ['status', 'proxima_tentativa_em']);
        $this->createIndex('idx_nfe_correlation', '{{%nfe}}', 'correlation_id');
        $this->createTable('{{%nfe_item}}', [
            'id' => $this->bigPrimaryKey()->unsigned(), 'nfe_id' => $this->bigInteger()->unsigned()->notNull(),
            'venda_item_id' => $this->bigInteger()->unsigned()->notNull(), 'ordem' => $this->smallInteger()->unsigned()->notNull(),
        ], $this->opt);
        $this->addForeignKey('fk_ni_nfe', '{{%nfe_item}}', 'nfe_id', '{{%nfe}}', 'id', 'CASCADE', 'RESTRICT');
        $this->addForeignKey('fk_ni_vi', '{{%nfe_item}}', 'venda_item_id', '{{%venda_item}}', 'id', 'RESTRICT', 'RESTRICT');
        $this->createIndex('uq_ni_item', '{{%nfe_item}}', 'venda_item_id', true);
        $this->createIndex('uq_ni_ordem', '{{%nfe_item}}', ['nfe_id', 'ordem'], true);
        $this->execute('ALTER TABLE {{%nfe_item}} ADD CONSTRAINT [[ck_ni_ordem]] CHECK ([[ordem]] BETWEEN 1 AND 100)');
        $this->createTable('{{%integracao_log}}', [
            'id' => $this->bigPrimaryKey()->unsigned(), 'integracao' => "ENUM('gid','sefaz') NOT NULL", 'operacao' => $this->string(100)->notNull(),
            'status' => "ENUM('iniciada','sucesso','erro','retry') NOT NULL", 'idempotency_key' => $this->string(100)->notNull(),
            'correlation_id' => $this->char(36)->notNull(), 'tentativa' => $this->smallInteger()->unsigned()->notNull()->defaultValue(1),
            'http_status' => $this->smallInteger()->unsigned(), 'request_hash' => $this->char(64), 'response_hash' => $this->char(64),
            'erro_codigo' => $this->string(100), 'erro_mensagem' => $this->text(), 'duracao_ms' => $this->integer()->unsigned(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $this->opt);
        $this->createIndex('uq_int_attempt_event', '{{%integracao_log}}', ['integracao', 'operacao', 'idempotency_key', 'tentativa', 'status'], true);
        $this->createIndex('idx_int_corr', '{{%integracao_log}}', 'correlation_id');
        $this->createTable('{{%auditoria_usuario}}', [
            'id' => $this->bigPrimaryKey()->unsigned(), 'usuario_id' => $this->bigInteger()->unsigned(),
            'acao' => $this->string(100)->notNull(), 'entidade' => $this->string(100)->notNull(), 'entidade_id' => $this->string(100),
            'dados_anteriores_hash' => $this->char(64), 'dados_novos_hash' => $this->char(64), 'ip_hash' => $this->char(64),
            'user_agent_hash' => $this->char(64), 'correlation_id' => $this->char(36)->notNull(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $this->opt);
        $this->addForeignKey('fk_audit_user', '{{%auditoria_usuario}}', 'usuario_id', '{{%usuario}}', 'id', 'RESTRICT', 'RESTRICT');
        $this->createIndex('idx_audit_entity', '{{%auditoria_usuario}}', ['entidade', 'entidade_id']);
        $this->createIndex('idx_audit_corr', '{{%auditoria_usuario}}', 'correlation_id');
        $this->rbac();
        foreach (['auditoria_usuario' => 'Auditoria imutavel', 'integracao_log' => 'Log de integracao imutavel'] as $table => $msg) {
            foreach (['update', 'delete'] as $verb) {
                $this->execute("CREATE TRIGGER trg_{$table}_no_{$verb} BEFORE {$verb} ON {{%{$table}}} FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='{$msg}'");
            }
        }
    }
    private function rbac(): void
    {
        $this->createTable('{{%auth_rule}}', ['name' => $this->string(64)->notNull(), 'data' => $this->binary(), 'created_at' => $this->integer(), 'updated_at' => $this->integer(), 'PRIMARY KEY ([[name]])'], $this->opt);
        $this->createTable('{{%auth_item}}', ['name' => $this->string(64)->notNull(), 'type' => $this->smallInteger()->notNull(), 'description' => $this->text(), 'rule_name' => $this->string(64), 'data' => $this->binary(), 'created_at' => $this->integer(), 'updated_at' => $this->integer(), 'PRIMARY KEY ([[name]])'], $this->opt);
        $this->createIndex('idx_auth_type', '{{%auth_item}}', 'type');
        $this->addForeignKey('fk_auth_rule', '{{%auth_item}}', 'rule_name', '{{%auth_rule}}', 'name', 'SET NULL', 'CASCADE');
        $this->createTable('{{%auth_item_child}}', ['parent' => $this->string(64)->notNull(), 'child' => $this->string(64)->notNull(), 'PRIMARY KEY ([[parent]], [[child]])'], $this->opt);
        $this->addForeignKey('fk_auth_parent', '{{%auth_item_child}}', 'parent', '{{%auth_item}}', 'name', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_auth_child', '{{%auth_item_child}}', 'child', '{{%auth_item}}', 'name', 'CASCADE', 'CASCADE');
        $this->createTable('{{%auth_assignment}}', ['item_name' => $this->string(64)->notNull(), 'user_id' => $this->string(64)->notNull(), 'created_at' => $this->integer(), 'PRIMARY KEY ([[item_name]], [[user_id]])'], $this->opt);
        $this->addForeignKey('fk_auth_assignment', '{{%auth_assignment}}', 'item_name', '{{%auth_item}}', 'name', 'CASCADE', 'CASCADE');
    }
    public function down(): bool
    {
        echo "Migration inicial nao reversivel automaticamente: preserva dados fiscais e auditoria.\n";
        return false;
    }
}
