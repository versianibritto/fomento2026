<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AulasFixture
 */
class AulasFixture extends TestFixture
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
                'user_id' => 1,
                'nome' => 'Lorem ipsum dolor sit amet',
                'resumo' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'tipo' => 'Lorem ipsum dolor sit amet',
                'arquivo' => 'Lorem ipsum dolor sit amet',
                'liberado' => 1,
                'created' => 1766888027,
                'modified' => 1766888027,
                'horas' => 1,
                'cursos_oferecido_id' => 1,
                'independente' => 1,
                'docente' => 'Lorem ipsum dolor sit amet',
                'cursos_modulo_id' => 1,
            ],
        ];
        parent::init();
    }
}
