<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\DashdetalhesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\DashdetalhesTable Test Case
 */
class DashdetalhesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\DashdetalhesTable
     */
    protected $Dashdetalhes;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Dashdetalhes',
        'app.Projetos',
        'app.Fases',
        'app.Editais',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Dashdetalhes') ? [] : ['className' => DashdetalhesTable::class];
        $this->Dashdetalhes = $this->getTableLocator()->get('Dashdetalhes', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Dashdetalhes);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\DashdetalhesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\DashdetalhesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
