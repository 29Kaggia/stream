<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateStreamViewers extends BaseMigration
{
    public function change(): void
    {
        $this->table('stream_viewers')
            ->addColumn('stream_id', 'integer', ['null' => false])
            // Hash of a browser-generated session id; raw browser ids never reach storage.
            ->addColumn('session_hash', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('expires_at', 'datetime', ['null' => false])
            ->addColumn('created', 'datetime', ['null' => false])
            ->addColumn('modified', 'datetime', ['null' => false])
            ->addIndex(['stream_id', 'session_hash'], ['unique' => true])
            ->addIndex(['expires_at'])
            ->addForeignKey('stream_id', 'streams', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
