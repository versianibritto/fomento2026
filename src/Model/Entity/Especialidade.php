<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Especialidade Entity
 *
 * @property int $id
 * @property string|null $nome
 * @property string|null $codigo
 * @property int|null $sub_area_id
 *
 * @property \App\Model\Entity\SubArea $sub_area
 */
class Especialidade extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'nome' => true,
        'codigo' => true,
        'sub_area_id' => true,
        'sub_area' => true,
    ];
}
