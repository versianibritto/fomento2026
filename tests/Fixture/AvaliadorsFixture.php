<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AvaliadorsFixture
 */
class AvaliadorsFixture extends TestFixture
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
                'usuario_id' => 1,
                'grandes_area_id' => 1,
                'area_id' => 1,
                'sub_area_id' => 1,
                'especialidade_id' => 1,
                'areas_fiocruz_id' => 1,
                'linha_id' => 1,
                'ano_convite' => '',
                'ano_aceite' => '',
                'voluntario' => '',
                'unidade_id' => 1,
                'tipo_avaliador' => 'Lorem ipsum dolor sit amet',
                'deleted' => 1,
                'editai_id' => 1,
                'aceite' => 1,
            ],
        ];
        parent::init();
    }
}
