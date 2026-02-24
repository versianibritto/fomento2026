<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ManuaisFixture
 */
class ManuaisFixture extends TestFixture
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
                'arquivo' => 'Lorem ipsum dolor sit amet',
                'usuario_id' => 1,
                'deleted' => 1766877159,
                'created' => 1766877159,
                'modified' => 1766877159,
                'nome' => 'Lorem ipsum dolor sit amet',
                'restrito' => 1,
            ],
        ];
        parent::init();
    }
}
