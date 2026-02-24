<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AulaBolsistasFixture
 */
class AulaBolsistasFixture extends TestFixture
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
                'aula_id' => 1,
                'porcentagem' => 1,
                'nota' => 1,
                'created' => 1766888017,
                'modified' => 1766888017,
                'curso' => 1,
                'cursos_bolsista_id' => 1,
            ],
        ];
        parent::init();
    }
}
