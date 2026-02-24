<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AulaBolsista Entity
 *
 * @property int $id
 * @property int|null $usuario_id
 * @property int|null $aula_id
 * @property float|null $porcentagem
 * @property float|null $nota
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property int|null $curso
 * @property int|null $cursos_bolsista_id
 *
 * @property \App\Model\Entity\Usuario $usuario
 * @property \App\Model\Entity\Aula $aula
 * @property \App\Model\Entity\CursosBolsista $cursos_bolsista
 */
class AulaBolsista extends Entity
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
        'aula_id' => true,
        'porcentagem' => true,
        'nota' => true,
        'created' => true,
        'modified' => true,
        'curso' => true,
        'cursos_bolsista_id' => true,
        'usuario' => true,
        'aula' => true,
        'cursos_bolsista' => true,
    ];
}
