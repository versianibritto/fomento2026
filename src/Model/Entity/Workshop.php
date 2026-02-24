<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Workshop Entity
 *
 * @property int $id
 * @property int|null $usuario_id
 * @property int|null $orientador
 * @property int|null $projeto_orientador
 * @property \Cake\I18n\DateTime|null $created
 * @property string|null $tipo_bolsa
 * @property \Cake\I18n\Date|null $data_apresentacao
 * @property string|null $local_apresentacao
 * @property string|null $tipo_apresentacao
 * @property float|null $nota_final
 * @property string|null $presenca
 * @property string|null $observacao_avaliador
 * @property int|null $destaque
 * @property int|null $indicado_premio_capes
 * @property int $deleted
 * @property int|null $unidade_id
 * @property int|null $editai_id
 * @property int|null $evento
 * @property int|null $usuario_cadastro
 * @property int|null $usuario_libera
 * @property \Cake\I18n\DateTime|null $data_liberacao
 * @property int|null $pdj_inscricoe_id
 *
 * @property \App\Model\Entity\Usuario $usuario
 * @property \App\Model\Entity\Unidade $unidade
 * @property \App\Model\Entity\Editai $editai
 * @property \App\Model\Entity\PdjInscrico $pdj_inscrico
 * @property \App\Model\Entity\WorkshopHistorico[] $workshop_historicos
 */
class Workshop extends Entity
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
        'usuario_id' => true,
        'orientador' => true,
        'projeto_orientador' => true,
        'created' => true,
        'tipo_bolsa' => true,
        'data_apresentacao' => true,
        'local_apresentacao' => true,
        'tipo_apresentacao' => true,
        'nota_final' => true,
        'presenca' => true,
        'observacao_avaliador' => true,
        'destaque' => true,
        'indicado_premio_capes' => true,
        'deleted' => true,
        'unidade_id' => true,
        'editai_id' => true,
        'evento' => true,
        'usuario_cadastro' => true,
        'usuario_libera' => true,
        'data_liberacao' => true,
        'pdj_inscricoe_id' => true,
        'usuario' => true,
        'unidade' => true,
        'editai' => true,
        'pdj_inscrico' => true,
        'workshop_historicos' => true,
    ];
}
