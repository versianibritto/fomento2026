<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\DashcountsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\DashcountsTable Test Case
 */
class DashcountsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\DashcountsTable
     */
    protected $Dashcounts;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Dashcounts',
        'app.Fases',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Dashcounts') ? [] : ['className' => DashcountsTable::class];
        $this->Dashcounts = $this->getTableLocator()->get('Dashcounts', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Dashcounts);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\DashcountsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\DashcountsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
