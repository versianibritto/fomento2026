<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Aula Entity
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $nome
 * @property string|null $resumo
 * @property string|null $tipo
 * @property string|null $arquivo
 * @property int|null $liberado
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property int|null $horas
 * @property int|null $cursos_oferecido_id
 * @property int|null $independente
 * @property string|null $docente
 * @property int|null $cursos_modulo_id
 *
 * @property \App\Model\Entity\CursosOferecido $cursos_oferecido
 * @property \App\Model\Entity\CursosModulo $cursos_modulo
 * @property \App\Model\Entity\AulaBolsista[] $aula_bolsistas
 */
class Aula extends Entity
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
        'user_id' => true,
        'nome' => true,
        'resumo' => true,
        'tipo' => true,
        'arquivo' => true,
        'liberado' => true,
        'created' => true,
        'modified' => true,
        'horas' => true,
        'cursos_oferecido_id' => true,
        'independente' => true,
        'docente' => true,
        'cursos_modulo_id' => true,
        'cursos_oferecido' => true,
        'cursos_modulo' => true,
        'aula_bolsistas' => true,
    ];
}
