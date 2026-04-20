<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AvaliadorBolsista Entity
 *
 * @property int $id
 * @property int|null $avaliador_id
 * @property int|null $bolsista
 * @property int|null $usuario_id
 * @property int|null $raic_id
 * @property int|null $workshop_id
 * @property int|null $projeto_bolsista_id
 * @property string|null $tipo
 * @property string|null $ano
 * @property string|null $situacao
 * @property bool|null $coordenador
 * @property string|null $observacao
 * @property \Cake\I18n\DateTime|null $created
 * @property int $deleted
 * @property int|null $destaque
 * @property int|null $indicado_premio_capes
 * @property int|null $alteracao
 * @property string|null $observacao_alteracao
 * @property int|null $editai_id
 * @property float|null $nota
 * @property string|null $parecer
 * @property int|null $ordem
 * @property int|null $banca_id
 *
 * @property \App\Model\Entity\Avaliador $avaliador
 * @property \App\Model\Entity\User $usuario
 * @property \App\Model\Entity\Raic $raic
 * @property \App\Model\Entity\Workshop $workshop
 * @property \App\Model\Entity\ProjetoBolsista $projeto_bolsista
 * @property \App\Model\Entity\Editai $editai
 * @property \App\Model\Entity\Banca $banca
 * @property \App\Model\Entity\Avaliation[] $avaliations
 */
class AvaliadorBolsista extends Entity
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
        'avaliador_id' => true,
        'bolsista' => true,
        'usuario_id' => true,
        'raic_id' => true,
        'workshop_id' => true,
        'projeto_bolsista_id' => true,
        'tipo' => true,
        'ano' => true,
        'situacao' => true,
        'coordenador' => true,
        'observacao' => true,
        'created' => true,
        'deleted' => true,
        'destaque' => true,
        'indicado_premio_capes' => true,
        'alteracao' => true,
        'observacao_alteracao' => true,
        'editai_id' => true,
        'nota' => true,
        'parecer' => true,
        'ordem' => true,
        'banca_id' => true,
        'avaliador' => true,
        'usuario' => true,
        'raic' => true,
        'workshop' => true,
        'projeto_bolsista' => true,
        'editai' => true,
        'banca' => true,
        'avaliations' => true,
    ];
}
