<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AreasTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AreasTable Test Case
 */
class AreasTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\AreasTable
     */
    protected $Areas;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Areas',
        'app.GrandesAreas',
        'app.Avaliadors',
        'app.PdjInscricoes',
        'app.Projetos',
        'app.SubAreas',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Areas') ? [] : ['className' => AreasTable::class];
        $this->Areas = $this->getTableLocator()->get('Areas', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Areas);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\AreasTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\AreasTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
