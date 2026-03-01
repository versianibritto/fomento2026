<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * SuporteCategoria Entity
 *
 * @property int $id
 * @property string $nome
 * @property int $ativo
 */
class SuporteCategoria extends Entity
{
    protected array $_accessible = [
        'nome' => true,
        'ativo' => true,
    ];
}
