<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateDonations extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('donations');

        $table
            ->addColumn('user_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('channel_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('amount', 'decimal', [
                'precision' => 12,
                'scale' => 2,
                'null' => false,
            ])
            ->addColumn('currency', 'string', [
                'limit' => 3,
                'default' => 'KES',
                'null' => false,
            ])
            ->addColumn('message', 'text', [
                'null' => true,
            ])
            ->addColumn('payment_method', 'string', [
                'limit' => 30,
                'default' => 'mpesa',
                'null' => false,
            ])
            ->addColumn('status', 'string', [
                'limit' => 20,
                'default' => 'pending',
                'null' => false,
            ])
            ->addColumn('provider_reference', 'string', [
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'null' => false,
            ])
            ->addIndex(['user_id'])
            ->addIndex(['channel_id'])
            ->addIndex(['status'])
            ->addIndex(['provider_reference'], [
                'unique' => true,
            ])
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