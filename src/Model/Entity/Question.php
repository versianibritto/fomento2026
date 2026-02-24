<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Question Entity
 *
 * @property int $id
 * @property string|null $ano
 * @property string|null $tipo
 * @property string|null $questao
 * @property float|null $limite_min
 * @property float|null $limite_max
 * @property string|null $prametros
 * @property int|null $editai_id
 * @property int|null $deleted
 *
 * @property \App\Model\Entity\Editai $editai
 * @property \App\Model\Entity\Avaliation[] $avaliations
 */
class Question extends Entity
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
        'ano' => true,
        'tipo' => true,
        'questao' => true,
        'limite_min' => true,
        'limite_max' => true,
        'prametros' => true,
        'editai_id' => true,
        'deleted' => true,
        'editai' => true,
        'avaliations' => true,
    ];
}
