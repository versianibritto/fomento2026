<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ProgramasTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ProgramasTable Test Case
 */
class ProgramasTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ProgramasTable
     */
    protected $Programas;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Programas',
        'app.Dashyodacounts',
        'app.Editais',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Programas') ? [] : ['className' => ProgramasTable::class];
        $this->Programas = $this->getTableLocator()->get('Programas', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Programas);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ProgramasTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
