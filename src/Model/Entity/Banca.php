<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Banca Entity
 *
 * @property int $id
 * @property string|null $nome
 * @property \Cake\I18n\Date|null $data
 * @property string|null $periodo
 * @property \Cake\I18n\DateTime|null $deleted
 * @property int|null $grandes_areas_id
 * @property int|null $editai_id
 * @property int|null $evento
 *
 * @property \App\Model\Entity\GrandesArea $grandes_area
 * @property \App\Model\Entity\Editai $editai
 * @property \App\Model\Entity\AvaliadorBolsista[] $avaliador_bolsistas
 * @property \App\Model\Entity\BancaUsuario[] $banca_usuarios
 */
class Banca extends Entity
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
        'nome' => true,
        'data' => true,
        'periodo' => true,
        'deleted' => true,
        'grandes_areas_id' => true,
        'editai_id' => true,
        'evento' => true,
        'grandes_area' => true,
        'editai' => true,
        'avaliador_bolsistas' => true,
        'banca_usuarios' => true,
    ];
}
