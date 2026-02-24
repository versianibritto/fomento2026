<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AvaliadorsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AvaliadorsTable Test Case
 */
class AvaliadorsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\AvaliadorsTable
     */
    protected $Avaliadors;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Avaliadors',
        'app.Usuarios',
        'app.GrandesAreas',
        'app.Areas',
        'app.SubAreas',
        'app.Especialidades',
        'app.Linhas',
        'app.Unidades',
        'app.Editais',
        'app.AvaliadorBolsistas',
        'app.AvaliadorProjetos',
        'app.BancaUsuarios',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Avaliadors') ? [] : ['className' => AvaliadorsTable::class];
        $this->Avaliadors = $this->getTableLocator()->get('Avaliadors', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Avaliadors);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\AvaliadorsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\AvaliadorsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
