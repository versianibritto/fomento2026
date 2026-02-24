<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\WorkshopsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\WorkshopsTable Test Case
 */
class WorkshopsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\WorkshopsTable
     */
    protected $Workshops;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Workshops',
        'app.Usuarios',
        'app.Unidades',
        'app.Editais',
        'app.PdjInscricoes',
        'app.WorkshopHistoricos',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Workshops') ? [] : ['className' => WorkshopsTable::class];
        $this->Workshops = $this->getTableLocator()->get('Workshops', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Workshops);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\WorkshopsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\WorkshopsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
