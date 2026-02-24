<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UnidadesFixture
 */
class UnidadesFixture extends TestFixture
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
                'sigla' => 'Lorem ipsum dolor sit amet',
                'instituicao_id' => 1,
                'coordenador' => 1,
                'logo' => 'Lorem ipsum dolor sit amet',
                'street_id' => 1,
                'numero' => 'Lorem ipsum dolor sit amet',
                'telefone' => 'Lorem ipsum dolor sit amet',
                'email' => 'Lorem ipsum dolor sit amet',
                'texto' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'whatsapp' => 'Lorem ipsum dolor sit amet',
                'subcoordenador' => 1,
                'deleted' => 1,
            ],
        ];
        parent::init();
    }
}
