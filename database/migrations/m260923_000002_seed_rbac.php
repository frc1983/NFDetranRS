<?php
declare(strict_types=1);
use yii\db\Migration;

final class m260923_000002_seed_rbac extends Migration
{
    public function safeUp(): void
    {
        $now = time();
        $permissions = ['dashboard.visualizar', 'estoque.visualizar', 'estoque.sincronizar', 'venda.visualizar', 'venda.criar', 'nfe.visualizar', 'nfe.emitir', 'auditoria.visualizar', 'configuracao.gerenciar'];
        foreach ($permissions as $name) { $this->insert('{{%auth_item}}', compact('name') + ['type' => 2, 'created_at' => $now, 'updated_at' => $now]); }
        foreach (['operador', 'fiscal', 'auditor', 'administrador'] as $name) { $this->insert('{{%auth_item}}', compact('name') + ['type' => 1, 'created_at' => $now, 'updated_at' => $now]); }
        $map = [
            'operador' => ['dashboard.visualizar', 'estoque.visualizar', 'venda.visualizar', 'venda.criar'],
            'fiscal' => ['dashboard.visualizar', 'estoque.visualizar', 'venda.visualizar', 'nfe.visualizar', 'nfe.emitir'],
            'auditor' => ['dashboard.visualizar', 'estoque.visualizar', 'venda.visualizar', 'nfe.visualizar', 'auditoria.visualizar'],
            'administrador' => ['operador', 'fiscal', 'auditor', 'estoque.sincronizar', 'configuracao.gerenciar'],
        ];
        foreach ($map as $parent => $children) { foreach ($children as $child) { $this->insert('{{%auth_item_child}}', compact('parent', 'child')); } }
    }
    public function safeDown(): bool
    {
        echo "Reversao automatica de papeis desativada para preservar permissoes existentes.\n";
        return false;
    }
}
