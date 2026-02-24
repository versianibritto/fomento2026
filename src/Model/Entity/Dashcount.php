<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Dashcount Entity
 *
 * @property int $qtd
 * @property int|null $bolsista
 * @property int|null $orientador
 * @property int|null $coorientador
 * @property int|null $fase_id
 * @property string|null $bloco
 * @property int|null $vigente
 *
 * @property \App\Model\Entity\Fase $fase
 */
class Dashcount extends Entity
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
        'qtd' => true,
        'bolsista' => true,
        'orientador' => true,
        'coorientador' => true,
        'fase_id' => true,
        'bloco' => true,
        'vigente' => true,
        'fase' => true,
    ];
}
