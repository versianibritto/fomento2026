<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\DashdetalhescompletosTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\DashdetalhescompletosTable Test Case
 */
class DashdetalhescompletosTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\DashdetalhescompletosTable
     */
    protected $Dashdetalhescompletos;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Dashdetalhescompletos',
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
        $config = $this->getTableLocator()->exists('Dashdetalhescompletos') ? [] : ['className' => DashdetalhescompletosTable::class];
        $this->Dashdetalhescompletos = $this->getTableLocator()->get('Dashdetalhescompletos', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Dashdetalhescompletos);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\DashdetalhescompletosTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\DashdetalhescompletosTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
