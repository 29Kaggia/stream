<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddMuxFieldsToStreams extends BaseMigration
{
    public function change(): void
    {
        $this->table('streams')
            ->addColumn('mux_live_stream_id', 'string', ['limit' => 255, 'null' => true, 'after' => 'stream_key'])
            ->addColumn('playback_id', 'string', ['limit' => 255, 'null' => true, 'after' => 'mux_live_stream_id'])
            ->addIndex(['mux_live_stream_id'], ['unique' => true])
            ->update();
    }
}
