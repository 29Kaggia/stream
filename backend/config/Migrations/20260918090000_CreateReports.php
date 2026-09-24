<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateReports extends BaseMigration
{
    public function change(): void
    {
        $this->table('reports')
            ->addColumn('reporter_id', 'integer', ['null' => false])
            ->addColumn('reported_user_id', 'integer', ['null' => true])
            ->addColumn('stream_id', 'integer', ['null' => true])
            ->addColumn('reason', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('details', 'text', ['null' => true])
            ->addColumn('status', 'string', ['limit' => 20, 'default' => 'open', 'null' => false])
            ->addColumn('created', 'datetime', ['null' => false])
            ->addColumn('modified', 'datetime', ['null' => false])
            ->addIndex(['status'])
            ->addIndex(['reported_user_id'])
            ->addIndex(['stream_id'])
            ->addForeignKey('reporter_id', 'users', 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('reported_user_id', 'users', 'id', ['delete' => 'SET_NULL'])
            ->addForeignKey('stream_id', 'streams', 'id', ['delete' => 'SET_NULL'])
            ->create();
    }
}
