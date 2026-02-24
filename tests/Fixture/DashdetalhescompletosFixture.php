<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * DashdetalhescompletosFixture
 */
class DashdetalhescompletosFixture extends TestFixture
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
                'bolsista' => 1,
                'nome_bolsista' => 'Lorem ipsum dolor sit amet',
                'orientador' => 1,
                'nome_orientador' => 'Lorem ipsum dolor sit amet',
                'coorientador' => 1,
                'nome_coorientador' => 'Lorem ipsum dolor sit amet',
                'projeto_id' => 1,
                'fase_id' => 1,
                'nome_fase' => 'Lorem ipsum dolor sit amet',
                'bloco' => 'L',
                'vigente' => 1,
                'editai_id' => 1,
                'nome_edital' => 'Lorem ipsum dolor sit amet',
                'nome_programa' => 'Lorem ipsum dolor sit amet',
                'data_inicio' => 1768059700,
                'data_fim' => 1768059700,
                'fim_vigencia' => 1768059700,
                'ativo' => 1,
            ],
        ];
        parent::init();
    }
}
