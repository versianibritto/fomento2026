<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Anexo Entity
 *
 * @property int $id
 * @property int|null $projeto_id
 * @property int|null $projeto_bolsista_id
 * @property int $anexos_tipo_id
 * @property string|null $anexo
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $deleted
 * @property int $usuario_id
 * @property int|null $raic_id
 * @property int|null $pdj_inscricoe_id
 * @property string|null $bloco
 *
 * @property \App\Model\Entity\Projeto $projeto
 * @property \App\Model\Entity\ProjetoBolsista $projeto_bolsista
 * @property \App\Model\Entity\AnexosTipo $anexos_tipo
 * @property \App\Model\Entity\Usuario $usuario
 * @property \App\Model\Entity\Raic $raic
 * @property \App\Model\Entity\PdjInscrico $pdj_inscrico
 */
class Anexo extends Entity
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
        'projeto_bolsista_id' => true,
        'anexos_tipo_id' => true,
        'anexo' => true,
        'created' => true,
        'deleted' => true,
        'usuario_id' => true,
        'raic_id' => true,
        'pdj_inscricoe_id' => true,
        'bloco' => true,
        'projeto' => true,
        'projeto_bolsista' => true,
        'anexos_tipo' => true,
        'usuario' => true,
        'raic' => true,
        'pdj_inscrico' => true,
    ];
}
