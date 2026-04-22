<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Dashcountavaliadore Entity
 *
 * @property int|null $usuario_id
 * @property string|null $usuario_nome
 * @property string|null $ano
 * @property string|null $situacao
 * @property int|null $deleted
 * @property int|null $qtd_inscricoes
 */
class Dashcountavaliadore extends Entity
{
    protected array $_accessible = [
        'usuario_id' => true,
        'usuario_nome' => true,
        'ano' => true,
        'situacao' => true,
        'deleted' => true,
        'qtd_inscricoes' => true,
    ];
}
