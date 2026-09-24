<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateCategories extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('categories');

        $table
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
            ->addColumn('created', 'datetime', [
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'null' => false,
            ])
            ->addIndex(['name'], ['unique' => true])
            ->addIndex(['slug'], ['unique' => true])
            ->create();
    }
}