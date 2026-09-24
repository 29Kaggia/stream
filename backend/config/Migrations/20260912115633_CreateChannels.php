<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateChannels extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('channels');

        $table
            ->addColumn('user_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('category_id', 'integer', [
                'null' => true,
            ])
            ->addColumn('name', 'string', [
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('slug', 'string', [
                'limit' => 120,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'null' => true,
            ])
            ->addColumn('is_live', 'boolean', [
                'default' => false,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'null' => false,
            ])
            ->addIndex(['user_id'], ['unique' => true])
            ->addIndex(['category_id'])
            ->addIndex(['slug'], ['unique' => true])
            ->addIndex(['is_live'])
            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('category_id', 'categories', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}