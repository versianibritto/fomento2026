<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * EditaisWk Entity
 *
 * @property int $int
 * @property string|null $nome
 */
class EditaisWk extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'nome' => true,
    ];
}
