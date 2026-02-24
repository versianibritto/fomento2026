<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * PdjInscricoesFixture
 */
class PdjInscricoesFixture extends TestFixture
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
                'orientador' => 1,
                'bolsista' => 1,
                'area_id' => 1,
                'linha_fiocruz_id' => 1,
                'edital_id' => 1,
                'situacao' => 'Lorem ipsum dolor sit amet',
                'titulo' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'financiadores' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'palavras_chaves' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'resumo' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => 1766902764,
                'modified' => 1766902764,
                'deleted' => 1766902764,
                'cota' => 'Lorem ipsum dolor sit amet',
                'resultado' => 'Lorem ipsum dolor sit amet',
                'data_inicio' => 1766902764,
                'data_fim' => 1766902764,
                'vigente' => 1,
                'origem' => 'Lorem ipsum dolor sit amet',
                'bolsista_anterior' => 1,
                'motivo_cancelamento_id' => 1,
                'justificativa_cancelamento' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'prorrogacao' => 1,
                'referencia_inscricao_anterior' => 1,
                'ordem' => 1,
                'pontos_orientador' => 1,
                'pontos_bolsista' => 1,
                'fase_id' => 1,
                'coorientador' => 1,
            ],
        ];
        parent::init();
    }
}
