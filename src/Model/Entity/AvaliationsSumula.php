<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AvaliationsSumula Entity
 *
 * @property int $id
 * @property int|null $avaliador_bolsista_id
 * @property int|null $editais_sumula_id
 * @property int|null $editais_sumula_bloco_id
 * @property int|null $inscricao_sumula_id
 * @property float|null $nota
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property string|null $observacao_avaliador
 * @property int|null $deleted
 * @property int|null $quantidade_original
 * @property int|null $quantidade_avaliada
 * @property int|null $bolsista
 *
 * @property \App\Model\Entity\AvaliadorBolsista $avaliador_bolsista
 * @property \App\Model\Entity\EditaisSumula $editais_sumula
 * @property \App\Model\Entity\EditaisSumulasBloco $editais_sumulas_bloco
 * @property \App\Model\Entity\InscricaoSumula $inscricao_sumula
 */
class AvaliationsSumula extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'avaliador_bolsista_id' => true,
        'editais_sumula_id' => true,
        'editais_sumula_bloco_id' => true,
        'inscricao_sumula_id' => true,
        'nota' => true,
        'created' => true,
        'modified' => true,
        'observacao_avaliador' => true,
        'deleted' => true,
        'quantidade_original' => true,
        'quantidade_avaliada' => true,
        'bolsista' => true,
        'avaliador_bolsista' => true,
        'editais_sumula' => true,
        'editais_sumulas_bloco' => true,
        'inscricao_sumula' => true,
    ];
}
