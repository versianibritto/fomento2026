<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * EditaisSumula Entity
 *
 * @property int $id
 * @property int|null $editai_id
 * @property string|null $sumula
 * @property string|null $parametro
 * @property int|null $editais_sumula_bloco_id
 * @property \Cake\I18n\DateTime|null $deleted
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Editai $editai
 * @property \App\Model\Entity\EditaisSumulasBloco $editais_sumulas_bloco
 */
class EditaisSumula extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'editai_id' => true,
        'sumula' => true,
        'parametro' => true,
        'editais_sumula_bloco_id' => true,
        'deleted' => true,
        'created' => true,
        'modified' => true,
        'editai' => true,
        'editais_sumulas_bloco' => true,
    ];
}
