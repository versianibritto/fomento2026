<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * VinculosFixture
 */
class VinculosFixture extends TestFixture
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
                'servidor' => 1,
                'created' => 1767660113,
                'modified' => 1767660113,
                'deleted' => 1,
            ],
        ];
        parent::init();
    }
}
