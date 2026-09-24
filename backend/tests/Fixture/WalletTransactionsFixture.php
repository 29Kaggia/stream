<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * WalletTransactionsFixture
 */
class WalletTransactionsFixture extends TestFixture
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
                'donation_id' => 1,
                'type' => 'Lorem ipsum dolor sit amet',
                'amount' => 1.5,
                'currency' => 'L',
                'description' => 'Lorem ipsum dolor sit amet',
                'reference' => 'Lorem ipsum dolor sit amet',
                'status' => 'Lorem ipsum dolor ',
                'created' => '2026-09-12 12:24:09',
                'modified' => '2026-09-12 12:24:09',
            ],
        ];
        parent::init();
    }
}
