<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AnexosTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AnexosTable Test Case
 */
class AnexosTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\AnexosTable
     */
    protected $Anexos;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Anexos',
        'app.Projetos',
        'app.ProjetoBolsistas',
        'app.AnexosTipos',
        'app.Usuarios',
        'app.Raics',
        'app.PdjInscricoes',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Anexos') ? [] : ['className' => AnexosTable::class];
        $this->Anexos = $this->getTableLocator()->get('Anexos', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Anexos);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\AnexosTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\AnexosTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
