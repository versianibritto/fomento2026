<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\EditaisTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\EditaisTable Test Case
 */
class EditaisTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\EditaisTable
     */
    protected $Editais;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Editais',
        'app.Usuarios',
        'app.Programas',
        'app.AvaliadorBolsistas',
        'app.Avaliadors',
        'app.Bancas',
        'app.Certificados',
        'app.Erratas',
        'app.EditaisPrazos',
        'app.ProjetoBolsistas',
        'app.Questions',
        'app.Raics',
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
        $config = $this->getTableLocator()->exists('Editais') ? [] : ['className' => EditaisTable::class];
        $this->Editais = $this->getTableLocator()->get('Editais', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Editais);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\EditaisTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\EditaisTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
