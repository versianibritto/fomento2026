<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * InscricaoSumula Entity
 *
 * @property int $id
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property int|null $editais_sumula_id
 * @property int|null $editai_id
 * @property int|null $editais_sumula_bloco_id
 * @property int|null $projeto_bolsista_id
 * @property int|null $pdj_inscricoe_id
 * @property int|null $quantidade
 *
 * @property \App\Model\Entity\EditaisSumula $editais_sumula
 * @property \App\Model\Entity\Editai $editai
 * @property \App\Model\Entity\EditaisSumulasBloco $editais_sumulas_bloco
 * @property \App\Model\Entity\ProjetoBolsista $projeto_bolsista
 * @property \App\Model\Entity\PdjInscrico $pdj_inscrico
 */
class InscricaoSumula extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'created' => true,
        'modified' => true,
        'editais_sumula_id' => true,
        'editai_id' => true,
        'editais_sumula_bloco_id' => true,
        'projeto_bolsista_id' => true,
        'pdj_inscricoe_id' => true,
        'quantidade' => true,
        'editais_sumula' => true,
        'editai' => true,
        'editais_sumulas_bloco' => true,
        'projeto_bolsista' => true,
        'pdj_inscrico' => true,
    ];
}

