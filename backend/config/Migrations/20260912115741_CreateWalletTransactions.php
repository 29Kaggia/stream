<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateWalletTransactions extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('wallet_transactions');

        $table
            ->addColumn('channel_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('donation_id', 'integer', [
                'null' => true,
            ])
            ->addColumn('type', 'string', [
                'limit' => 30,
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
            ->addColumn('description', 'string', [
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('reference', 'string', [
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('status', 'string', [
                'limit' => 20,
                'default' => 'completed',
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'null' => false,
            ])
            ->addIndex(['channel_id'])
            ->addIndex(['donation_id'])
            ->addIndex(['reference'], [
                'unique' => true,
            ])
            ->addIndex(['type'])
            ->addForeignKey('channel_id', 'channels', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('donation_id', 'donations', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}