<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PdjInscricoesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PdjInscricoesTable Test Case
 */
class PdjInscricoesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\PdjInscricoesTable
     */
    protected $PdjInscricoes;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.PdjInscricoes',
        'app.Projetos',
        'app.Usuarios',
        'app.Areas',
        'app.MotivoCancelamentos',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('PdjInscricoes') ? [] : ['className' => PdjInscricoesTable::class];
        $this->PdjInscricoes = $this->getTableLocator()->get('PdjInscricoes', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->PdjInscricoes);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\PdjInscricoesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\PdjInscricoesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
