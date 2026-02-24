<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AvaliadorBolsistasTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AvaliadorBolsistasTable Test Case
 */
class AvaliadorBolsistasTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\AvaliadorBolsistasTable
     */
    protected $AvaliadorBolsistas;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AvaliadorBolsistas',
        'app.Avaliadors',
        'app.Editais',
        'app.Bancas',
        'app.Avaliations',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('AvaliadorBolsistas') ? [] : ['className' => AvaliadorBolsistasTable::class];
        $this->AvaliadorBolsistas = $this->getTableLocator()->get('AvaliadorBolsistas', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->AvaliadorBolsistas);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\AvaliadorBolsistasTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\AvaliadorBolsistasTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
