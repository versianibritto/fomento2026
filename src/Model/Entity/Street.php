<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Street Entity
 *
 * @property int $id
 * @property string|null $nome
 * @property string|null $cep
 * @property int|null $district_id
 *
 * @property \App\Model\Entity\District $district
 * @property \App\Model\Entity\Usuario[] $usuarios
 */
class Street extends Entity
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
        'cep' => true,
        'district_id' => true,
        'district' => true,
        'usuarios' => true,
    ];
}
