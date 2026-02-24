<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AvaliationsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AvaliationsTable Test Case
 */
class AvaliationsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\AvaliationsTable
     */
    protected $Avaliations;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Avaliations',
        'app.AvaliadorBolsistas',
        'app.Questions',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Avaliations') ? [] : ['className' => AvaliationsTable::class];
        $this->Avaliations = $this->getTableLocator()->get('Avaliations', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Avaliations);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\AvaliationsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\AvaliationsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
