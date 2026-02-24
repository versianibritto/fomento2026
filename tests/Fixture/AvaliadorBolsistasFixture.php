<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AvaliadorBolsistasFixture
 */
class AvaliadorBolsistasFixture extends TestFixture
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
                'avaliador_id' => 1,
                'bolsista' => 1,
                'tipo' => 'Lorem ipsum dolor sit amet',
                'ano' => 'Lor',
                'situacao' => 'Lorem ipsum dolor sit amet',
                'coordenador' => 1,
                'observacao' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => 1766888079,
                'deleted' => 1,
                'destaque' => 1,
                'indicado_premio_capes' => 1,
                'alteracao' => 1,
                'observacao_alteracao' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'editai_id' => 1,
                'nota' => 1,
                'parecer' => 'Lorem ipsum dolor sit amet',
                'ordem' => 1,
                'banca_id' => 1,
            ],
        ];
        parent::init();
    }
}
