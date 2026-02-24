<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\LinhasTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\LinhasTable Test Case
 */
class LinhasTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\LinhasTable
     */
    protected $Linhas;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Linhas',
        'app.Avaliadors',
        'app.Projetos',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Linhas') ? [] : ['className' => LinhasTable::class];
        $this->Linhas = $this->getTableLocator()->get('Linhas', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Linhas);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\LinhasTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
