<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * DonationsFixture
 */
class DonationsFixture extends TestFixture
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
                'user_id' => 1,
                'channel_id' => 1,
                'amount' => 1.5,
                'currency' => 'L',
                'message' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'payment_method' => 'Lorem ipsum dolor sit amet',
                'status' => 'Lorem ipsum dolor ',
                'provider_reference' => 'Lorem ipsum dolor sit amet',
                'created' => '2026-09-12 12:23:59',
                'modified' => '2026-09-12 12:23:59',
            ],
        ];
        parent::init();
    }
}
