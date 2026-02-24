<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\BancasTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\BancasTable Test Case
 */
class BancasTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\BancasTable
     */
    protected $Bancas;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Bancas',
        'app.GrandesAreas',
        'app.Editais',
        'app.AvaliadorBolsistas',
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
        $config = $this->getTableLocator()->exists('Bancas') ? [] : ['className' => BancasTable::class];
        $this->Bancas = $this->getTableLocator()->get('Bancas', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Bancas);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\BancasTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\BancasTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
