<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * WorkshopsFixture
 */
class WorkshopsFixture extends TestFixture
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
                'orientador' => 1,
                'projeto_orientador' => 1,
                'created' => 1767660144,
                'tipo_bolsa' => 'Lorem ipsum dolor sit amet',
                'data_apresentacao' => '2026-01-06',
                'local_apresentacao' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'tipo_apresentacao' => 'Lorem ipsum dolor sit amet',
                'nota_final' => 1,
                'presenca' => 'Lorem ipsum dolor sit amet',
                'observacao_avaliador' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'destaque' => 1,
                'indicado_premio_capes' => 1,
                'deleted' => 1,
                'unidade_id' => 1,
                'editai_id' => 1,
                'evento' => 1,
                'usuario_cadastro' => 1,
                'usuario_libera' => 1,
                'data_liberacao' => 1767660144,
                'pdj_inscricoe_id' => 1,
            ],
        ];
        parent::init();
    }
}
