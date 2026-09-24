<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ChannelsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ChannelsTable Test Case
 */
class ChannelsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ChannelsTable
     */
    protected $Channels;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Channels',
        'app.Users',
        'app.Categories',
        'app.Donations',
        'app.Follows',
        'app.Streams',
        'app.WalletTransactions',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Channels') ? [] : ['className' => ChannelsTable::class];
        $this->Channels = $this->getTableLocator()->get('Channels', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Channels);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ChannelsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ChannelsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
