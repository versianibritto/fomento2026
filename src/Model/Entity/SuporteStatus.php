<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * SuporteStatus Entity
 *
 * @property int $id
 * @property string $nome
 * @property string $codigo
 * @property int $ativo
 */
class SuporteStatus extends Entity
{
    protected array $_accessible = [
        'nome' => true,
        'codigo' => true,
        'ativo' => true,
    ];
}
