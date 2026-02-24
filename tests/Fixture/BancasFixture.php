<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * BancasFixture
 */
class BancasFixture extends TestFixture
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
                'id' => 1,
                'nome' => 'Lorem ipsum dolor sit amet',
                'data' => '2026-01-06',
                'periodo' => 'Lorem ipsum dolor sit amet',
                'deleted' => 1767660156,
                'grandes_areas_id' => 1,
                'editai_id' => 1,
                'evento' => 1,
            ],
        ];
        parent::init();
    }
}
