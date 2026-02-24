<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * FeedbacksFixture
 */
class FeedbacksFixture extends TestFixture
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
                'tipo' => 'Lorem ipsum dolor sit amet',
                'usuario_id' => 1,
                'created' => 1766885568,
                'modified' => 1766885568,
                'ativo' => 1,
                'texto' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'destinatario' => 'Lorem ipsum dolor sit amet',
                'parent_id' => 1,
                'origem' => 'Lorem ipsum dolor sit amet',
                'titulo' => 'Lorem ipsum dolor sit amet',
                'situacao' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
