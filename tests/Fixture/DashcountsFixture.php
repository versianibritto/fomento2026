<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * DashcountsFixture
 */
class DashcountsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'qtd' => 1,
                'bolsista' => 1,
                'orientador' => 1,
                'coorientador' => 1,
                'fase_id' => 1,
                'bloco' => 'L',
                'vigente' => 1,
            ],
        ];
        parent::init();
    }
}
