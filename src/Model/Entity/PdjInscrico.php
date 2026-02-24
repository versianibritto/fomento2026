<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * PdjInscrico Entity
 *
 * @property int $id
 * @property int $projeto_id
 * @property int $orientador
 * @property int $bolsista
 * @property int $area_id
 * @property int|null $linha_fiocruz_id
 * @property int|null $edital_id
 * @property string|null $situacao
 * @property string|null $titulo
 * @property string|null $financiadores
 * @property string|null $palavras_chaves
 * @property string|null $resumo
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property \Cake\I18n\DateTime|null $deleted
 * @property string|null $cota
 * @property string|null $resultado
 * @property \Cake\I18n\DateTime|null $data_inicio
 * @property \Cake\I18n\DateTime|null $data_fim
 * @property int|null $vigente
 * @property string|null $origem
 * @property int|null $bolsista_anterior
 * @property int|null $motivo_cancelamento_id
 * @property string|null $justificativa_cancelamento
 * @property int|null $prorrogacao
 * @property int|null $referencia_inscricao_anterior
 * @property int|null $ordem
 * @property float|null $pontos_orientador
 * @property float|null $pontos_bolsista
 * @property int|null $fase_id
 * @property int|null $coorientador
 * @property int|null $programa_id
 *
 * @property \App\Model\Entity\Projeto $projeto
 * @property \App\Model\Entity\Usuario $usuario
 * @property \App\Model\Entity\Area $area
 * @property \App\Model\Entity\MotivoCancelamento $motivo_cancelamento
 */
class PdjInscrico extends Entity
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
        'projeto_id' => true,
        'orientador' => true,
        'bolsista' => true,
        'area_id' => true,
        'linha_fiocruz_id' => true,
        'edital_id' => true,
        'situacao' => true,
        'titulo' => true,
        'financiadores' => true,
        'palavras_chaves' => true,
        'resumo' => true,
        'created' => true,
        'modified' => true,
        'deleted' => true,
        'cota' => true,
        'resultado' => true,
        'data_inicio' => true,
        'data_fim' => true,
        'vigente' => true,
        'origem' => true,
        'bolsista_anterior' => true,
        'motivo_cancelamento_id' => true,
        'justificativa_cancelamento' => true,
        'prorrogacao' => true,
        'referencia_inscricao_anterior' => true,
        'ordem' => true,
        'pontos_orientador' => true,
        'pontos_bolsista' => true,
        'fase_id' => true,
        'coorientador' => true,
        'programa_id' => true,
        'projeto' => true,
        'usuario' => true,
        'area' => true,
        'motivo_cancelamento' => true,
    ];
}
