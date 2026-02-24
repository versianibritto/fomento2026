<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * EditaisFixture
 */
class EditaisFixture extends TestFixture
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
                'divulgacao' => 1768540596,
                'inicio_inscricao' => 1768540596,
                'fim_inscricao' => 1768540596,
                'resultado' => 1768540596,
                'inicio_recurso' => 1768540596,
                'fim_recurso' => 1768540596,
                'resultado_recurso' => 1768540596,
                'arquivo' => 'Lorem ipsum dolor sit amet',
                'origem' => 'Lorem ipsum dolor sit amet',
                'inicio_vigencia' => 1768540596,
                'fim_vigencia' => 1768540596,
                'limitaAnoDoutorado' => 1,
                'resultado_arquivo' => 'Lorem ipsum dolor sit amet',
                'vinculos_permitidos' => 'Lorem ipsum dolor sit amet',
                'escolaridades_permitidas' => 'Lorem ipsum dolor sit amet',
                'usuario_id' => 1,
                'created' => 1768540596,
                'modified' => 1768540596,
                'deleted' => 1,
                'unidades_permitidas' => 'Lorem ipsum dolor sit amet',
                'visualizar' => 'Lorem ipsum dolor sit amet',
                'inicio_avaliar' => 1768540596,
                'fim_avaliar' => 1768540596,
                'evento' => 1,
                'programa_id' => 1,
                'link' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
