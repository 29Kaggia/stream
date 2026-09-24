<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateStreams extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('streams');

        $table
            ->addColumn('channel_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('title', 'string', [
                'limit' => 200,
                'null' => false,
            ])
            ->addColumn('stream_key', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('status', 'string', [
                'limit' => 20,
                'default' => 'offline',
                'null' => false,
            ])
            ->addColumn('viewer_count', 'integer', [
                'default' => 0,
                'null' => false,
            ])
            ->addColumn('thumbnail_url', 'string', [
                'limit' => 500,
                'null' => true,
            ])
            ->addColumn('started_at', 'datetime', [
                'null' => true,
            ])
            ->addColumn('ended_at', 'datetime', [
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'null' => false,
            ])
            ->addIndex(['channel_id'])
            ->addIndex(['stream_key'], [
                'unique' => true,
            ])
            ->addIndex(['status'])
            ->addForeignKey('channel_id', 'channels', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}