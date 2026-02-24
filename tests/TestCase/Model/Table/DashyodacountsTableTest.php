<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\DashyodacountsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\DashyodacountsTable Test Case
 */
class DashyodacountsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\DashyodacountsTable
     */
    protected $Dashyodacounts;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Dashyodacounts',
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
        $config = $this->getTableLocator()->exists('Dashyodacounts') ? [] : ['className' => DashyodacountsTable::class];
        $this->Dashyodacounts = $this->getTableLocator()->get('Dashyodacounts', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Dashyodacounts);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\DashyodacountsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\DashyodacountsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
