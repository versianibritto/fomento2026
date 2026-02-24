<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * FonteHistorico Entity
 *
 * @property int $id
 * @property int|null $usuario_id
 * @property string|null $fonte_original
 * @property string|null $fonte_atual
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 * @property int|null $projeto_bolsista_id
 *
 * @property \App\Model\Entity\Usuario $usuario
 */
class FonteHistorico extends Entity
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
        'usuario_id' => true,
        'fonte_original' => true,
        'fonte_atual' => true,
        'created' => true,
        'modified' => true,
        'projeto_bolsista_id' => true,
        'usuario' => true,
    ];
}
