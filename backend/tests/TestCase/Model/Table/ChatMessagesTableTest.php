<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ChatMessagesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ChatMessagesTable Test Case
 */
class ChatMessagesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ChatMessagesTable
     */
    protected $ChatMessages;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.ChatMessages',
        'app.Streams',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('ChatMessages') ? [] : ['className' => ChatMessagesTable::class];
        $this->ChatMessages = $this->getTableLocator()->get('ChatMessages', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ChatMessages);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ChatMessagesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ChatMessagesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
