<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateChatMessages extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('chat_messages');

        $table
            ->addColumn('stream_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('message', 'text', [
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'null' => false,
            ])
            ->addIndex(['stream_id'])
            ->addIndex(['user_id'])
            ->addIndex(['stream_id', 'created'])
            ->addForeignKey('stream_id', 'streams', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}