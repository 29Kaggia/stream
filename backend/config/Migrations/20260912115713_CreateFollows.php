<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateFollows extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('follows');

        $table
            ->addColumn('user_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('channel_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'null' => false,
            ])
            ->addIndex(['user_id', 'channel_id'], [
                'unique' => true,
            ])
            ->addIndex(['channel_id'])
            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('channel_id', 'channels', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}