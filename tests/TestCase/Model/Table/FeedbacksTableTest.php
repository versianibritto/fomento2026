<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\FeedbacksTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\FeedbacksTable Test Case
 */
class FeedbacksTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\FeedbacksTable
     */
    protected $Feedbacks;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Feedbacks',
        'app.Usuarios',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Feedbacks') ? [] : ['className' => FeedbacksTable::class];
        $this->Feedbacks = $this->getTableLocator()->get('Feedbacks', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Feedbacks);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\FeedbacksTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\FeedbacksTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
