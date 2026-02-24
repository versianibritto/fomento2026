<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\VinculosTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\VinculosTable Test Case
 */
class VinculosTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\VinculosTable
     */
    protected $Vinculos;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Vinculos',
        'app.UsuarioHistoricos',
        'app.Usuarios',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Vinculos') ? [] : ['className' => VinculosTable::class];
        $this->Vinculos = $this->getTableLocator()->get('Vinculos', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Vinculos);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\VinculosTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
