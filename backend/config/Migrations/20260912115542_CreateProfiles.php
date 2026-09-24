<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateProfiles extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('profiles');

        $table
            ->addColumn('user_id', 'integer', [
                'null' => false,
            ])
            ->addColumn('display_name', 'string', [
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('bio', 'text', [
                'null' => true,
            ])
            ->addColumn('avatar_url', 'string', [
                'limit' => 500,
                'null' => true,
            ])
            ->addColumn('banner_url', 'string', [
                'limit' => 500,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'null' => false,
            ])
            ->addIndex(['user_id'], [
                'unique' => true,
            ])
            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}