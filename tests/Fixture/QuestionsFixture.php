<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * QuestionsFixture
 */
class QuestionsFixture extends TestFixture
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
                'ano' => 'Lor',
                'tipo' => 'Lorem ipsum dolor sit amet',
                'questao' => 'Lorem ipsum dolor sit amet',
                'limite_min' => 1,
                'limite_max' => 1,
                'prametros' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'editai_id' => 1,
                'deleted' => 1,
            ],
        ];
        parent::init();
    }
}
