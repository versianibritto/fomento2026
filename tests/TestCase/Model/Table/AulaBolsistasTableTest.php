<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AulaBolsistasTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AulaBolsistasTable Test Case
 */
class AulaBolsistasTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\AulaBolsistasTable
     */
    protected $AulaBolsistas;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AulaBolsistas',
        'app.Usuarios',
        'app.Aulas',
        'app.CursosBolsistas',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('AulaBolsistas') ? [] : ['className' => AulaBolsistasTable::class];
        $this->AulaBolsistas = $this->getTableLocator()->get('AulaBolsistas', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->AulaBolsistas);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\AulaBolsistasTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\AulaBolsistasTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
