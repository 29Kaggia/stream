<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * StreamsFixture
 */
class StreamsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'channel_id' => 1,
                'title' => 'Lorem ipsum dolor sit amet',
                'stream_key' => 'Lorem ipsum dolor sit amet',
                'status' => 'Lorem ipsum dolor ',
                'viewer_count' => 1,
                'thumbnail_url' => 'Lorem ipsum dolor sit amet',
                'started_at' => '2026-09-12 12:23:21',
                'ended_at' => '2026-09-12 12:23:21',
                'created' => '2026-09-12 12:23:21',
                'modified' => '2026-09-12 12:23:21',
            ],
        ];
        parent::init();
    }
}
