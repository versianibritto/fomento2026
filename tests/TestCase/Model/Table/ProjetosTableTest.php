<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ProjetosTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ProjetosTable Test Case
 */
class ProjetosTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ProjetosTable
     */
    protected $Projetos;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Projetos',
        'app.Usuarios',
        'app.Areas',
        'app.Linhas',
        'app.AvaliadorProjetos',
        'app.PdjInscricoes',
        'app.ProjetoAnexos',
        'app.ProjetoBolsistas',
        'app.ProjetoKeywords',
        'app.ProjetosDados',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Projetos') ? [] : ['className' => ProjetosTable::class];
        $this->Projetos = $this->getTableLocator()->get('Projetos', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Projetos);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ProjetosTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ProjetosTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
