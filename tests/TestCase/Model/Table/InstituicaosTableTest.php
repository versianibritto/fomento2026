<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\InstituicaosTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\InstituicaosTable Test Case
 */
class InstituicaosTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\InstituicaosTable
     */
    protected $Instituicaos;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Instituicaos',
        'app.TalentosCursos',
        'app.TalentosEventos',
        'app.Unidades',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Instituicaos') ? [] : ['className' => InstituicaosTable::class];
        $this->Instituicaos = $this->getTableLocator()->get('Instituicaos', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Instituicaos);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\InstituicaosTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
