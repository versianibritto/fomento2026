<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RaicsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\RaicsTable Test Case
 */
class RaicsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\RaicsTable
     */
    protected $Raics;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Raics',
        'app.Usuarios',
        'app.ProjetoBolsistas',
        'app.Unidades',
        'app.Editais',
        'app.Gabaritos',
        'app.ProjetoAnexos',
        'app.RaicHistoricos',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Raics') ? [] : ['className' => RaicsTable::class];
        $this->Raics = $this->getTableLocator()->get('Raics', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Raics);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\RaicsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\RaicsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
