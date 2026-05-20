<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * BancaUsuario Entity
 *
 * @property int $id
 * @property int|null $banca_id
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 * @property \Cake\I18n\FrozenTime|null $deleted
 * @property int|null $avaliador_id
 *
 * @property \App\Model\Entity\Banca $banca
 * @property \App\Model\Entity\Avaliador $avaliador
 */
class BancaUsuario extends Entity
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
        'banca_id' => true,
        'created' => true,
        'modified' => true,
        'deleted' => true,
        'avaliador_id' => true,
        'banca' => true,
        'avaliador' => true,
    ];
}
