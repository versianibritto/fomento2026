<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Instituicao Entity
 *
 * @property int $id
 * @property string|null $nome
 * @property string|null $sigla
 *
 * @property \App\Model\Entity\TalentosCurso[] $talentos_cursos
 * @property \App\Model\Entity\TalentosEvento[] $talentos_eventos
 * @property \App\Model\Entity\Unidade[] $unidades
 */
class Instituicao extends Entity
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
        'sigla' => true,
        'talentos_cursos' => true,
        'talentos_eventos' => true,
        'unidades' => true,
    ];
}
