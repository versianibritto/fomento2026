<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Certificado Entity
 *
 * @property int $id
 * @property string $codigo
 * @property int|null $bolsista_id
 * @property \Cake\I18n\FrozenTime|null $created
 * @property string|null $tipo
 *
 * @property \App\Model\Entity\Bolsista $bolsista
 */
class Certificado extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected array $_accessible = [
        'codigo' => true,
        'bolsista_id' => true,
        'created' => true,
        'tipo' => true,
        'bolsista' => true,
    ];
}
