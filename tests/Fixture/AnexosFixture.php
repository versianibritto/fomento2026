<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AnexosFixture
 */
class AnexosFixture extends TestFixture
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
                'projeto_id' => 1,
                'projeto_bolsista_id' => 1,
                'anexos_tipo_id' => 1,
                'anexo' => 'Lorem ipsum dolor sit amet',
                'created' => 1768793841,
                'deleted' => 1768793841,
                'usuario_id' => 1,
                'raic_id' => 1,
                'pdj_inscricoe_id' => 1,
                'bloco' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
