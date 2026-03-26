<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * RaicHistorico Entity
 *
 * @property int $id
 * @property int|null $raic_id
 * @property int|null $usuario_id
 * @property string|null $alteracao
 * @property string|null $justificativa
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 *
 * @property \App\Model\Entity\Raic $raic
 * @property \App\Model\Entity\Usuario $usuario
 */
class RaicHistorico extends Entity
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
        'raic_id' => true,
        'usuario_id' => true,
        'alteracao' => true,
        'justificativa' => true,
        'created' => true,
        'modified' => true,
        'raic' => true,
        'usuario' => true,
    ];
}
