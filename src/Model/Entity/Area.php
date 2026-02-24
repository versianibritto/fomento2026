<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Area Entity
 *
 * @property int $id
 * @property string|null $nome
 * @property string|null $codigo
 * @property int|null $grandes_area_id
 *
 * @property \App\Model\Entity\GrandesArea $grandes_area
 * @property \App\Model\Entity\Avaliador[] $avaliadors
 * @property \App\Model\Entity\PdjInscrico[] $pdj_inscricoes
 * @property \App\Model\Entity\Projeto[] $projetos
 * @property \App\Model\Entity\SubArea[] $sub_areas
 */
class Area extends Entity
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
        'codigo' => true,
        'grandes_area_id' => true,
        'grandes_area' => true,
        'avaliadors' => true,
        'pdj_inscricoes' => true,
        'projetos' => true,
        'sub_areas' => true,
    ];
}
