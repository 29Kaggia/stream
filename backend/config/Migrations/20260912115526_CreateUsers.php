<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateUsers extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('users');

        $table
            ->addColumn('username', 'string', [
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('email', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('password', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('role', 'string', [
                'limit' => 20,
                'default' => 'viewer',
                'null' => false,
            ])
            ->addColumn('status', 'string', [
                'limit' => 20,
                'default' => 'active',
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'null' => false,
            ])
            ->addIndex(['username'], [
                'unique' => true,
            ])
            ->addIndex(['email'], [
                'unique' => true,
            ])
            ->addIndex(['role'])
            ->addIndex(['status'])
            ->create();
    }
}