<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * WorkshopHistorico Entity
 *
 * @property int $id
 * @property int|null $workshop_id
 * @property int|null $usuario_id
 * @property string|null $alteracao
 * @property string|null $justificativa
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Workshop $workshop
 * @property \App\Model\Entity\Usuario $usuario
 */
class WorkshopHistorico extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'workshop_id' => true,
        'usuario_id' => true,
        'alteracao' => true,
        'justificativa' => true,
        'created' => true,
        'modified' => true,
        'workshop' => true,
        'usuario' => true,
    ];
}
