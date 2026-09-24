<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\FollowsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\FollowsTable Test Case
 */
class FollowsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\FollowsTable
     */
    protected $Follows;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Follows',
        'app.Users',
        'app.Channels',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Follows') ? [] : ['className' => FollowsTable::class];
        $this->Follows = $this->getTableLocator()->get('Follows', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Follows);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\FollowsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\FollowsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
