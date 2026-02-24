<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UnidadesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\UnidadesTable Test Case
 */
class UnidadesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\UnidadesTable
     */
    protected $Unidades;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Unidades',
        'app.Instituicaos',
        'app.Streets',
        'app.Avaliadors',
        'app.Editais',
        'app.Raics',
        'app.UnidadeEquipes',
        'app.UsuarioHistoricos',
        'app.Usuarios',
        'app.Workshops',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Unidades') ? [] : ['className' => UnidadesTable::class];
        $this->Unidades = $this->getTableLocator()->get('Unidades', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Unidades);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\UnidadesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\UnidadesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
