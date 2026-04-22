<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Dashyodacount Entity
 *
 * @property int $qtd
 * @property int|null $fase_id
 * @property string|null $bloco
 * @property int|null $vigente
 * @property int|null $programa_id
 * @property int|null $editai_id
 * @property \Cake\I18n\FrozenTime|null $inicio_vigencia
 * @property \Cake\I18n\FrozenTime|null $fim_vigencia
 * @property string|null $homologado
 *
 * @property \App\Model\Entity\Fase $fase
 * @property \App\Model\Entity\Programa $programa
 * @property \App\Model\Entity\Editai $editai
 */
class Dashyodacount extends Entity
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
        'fase_id' => true,
        'bloco' => true,
        'vigente' => true,
        'programa_id' => true,
        'editai_id' => true,
        'inicio_vigencia' => true,
        'fim_vigencia' => true,
        'homologado' => true,
        'fase' => true,
        'programa' => true,
        'editai' => true,
    ];
}
