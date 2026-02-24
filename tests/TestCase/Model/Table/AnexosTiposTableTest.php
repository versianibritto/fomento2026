<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AnexosTiposTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AnexosTiposTable Test Case
 */
class AnexosTiposTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\AnexosTiposTable
     */
    protected $AnexosTipos;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.AnexosTipos',
        'app.Anexos',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('AnexosTipos') ? [] : ['className' => AnexosTiposTable::class];
        $this->AnexosTipos = $this->getTableLocator()->get('AnexosTipos', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->AnexosTipos);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\AnexosTiposTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
