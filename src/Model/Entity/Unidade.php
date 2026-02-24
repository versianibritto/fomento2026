<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Unidade extends Entity
{
   
    protected array $_accessible = [
        'nome' => true,
        'sigla' => true,
        'instituicao_id' => true,
        'coordenador' => true,
        'logo' => true,
        'street_id' => true,
        'numero' => true,
        'telefone' => true,
        'email' => true,
        'texto' => true,
        'whatsapp' => true,
        'subcoordenador' => true,
        'deleted' => true,
        'instituicao' => true,
        'street' => true,
        'avaliadors' => true,
        'editais' => true,
        'raics' => true,
        'unidade_equipes' => true,
        'usuario_historicos' => true,
        'usuarios' => true,
        'workshops' => true,
    ];
}
