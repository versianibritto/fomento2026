<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * TiTipo Entity
 *
 * @property int $id
 * @property string|null $tipo
 * @property string|null $nome
 * @property int|null $deleted
 */
class TiTipo extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'tipo' => true,
        'nome' => true,
        'deleted' => true,
    ];
}
