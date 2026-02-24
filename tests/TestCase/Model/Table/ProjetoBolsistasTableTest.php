<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ProjetoBolsistasTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ProjetoBolsistasTable Test Case
 */
class ProjetoBolsistasTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ProjetoBolsistasTable
     */
    protected $ProjetoBolsistas;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.ProjetoBolsistas',
        'app.Editais',
        'app.Projetos',
        'app.Usuarios',
        'app.MotivoCancelamentos',
        'app.ProjetosDados',
        'app.Revistas',
        'app.FonteHistoricos',
        'app.Gabaritos',
        'app.GabaritosBolsistas',
        'app.HistoricoCoorientadores',
        'app.ProjetoAnexos',
        'app.Raics',
        'app.SituacaoHistoricos',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('ProjetoBolsistas') ? [] : ['className' => ProjetoBolsistasTable::class];
        $this->ProjetoBolsistas = $this->getTableLocator()->get('ProjetoBolsistas', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ProjetoBolsistas);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ProjetoBolsistasTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ProjetoBolsistasTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
