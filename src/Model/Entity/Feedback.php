<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Feedback Entity
 *
 * @property int $id
 * @property string|null $tipo
 * @property int|null $usuario_id
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property int|null $ativo
 * @property string|null $texto
 * @property string|null $destinatario
 * @property int|null $parent_id
 * @property string|null $origem
 * @property string|null $titulo
 * @property string|null $situacao
 *
 * @property \App\Model\Entity\Usuario $usuario
 * @property \App\Model\Entity\ParentFeedback $parent_feedback
 * @property \App\Model\Entity\ChildFeedback[] $child_feedbacks
 */
class Feedback extends Entity
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
        'tipo' => true,
        'usuario_id' => true,
        'created' => true,
        'modified' => true,
        'ativo' => true,
        'texto' => true,
        'destinatario' => true,
        'parent_id' => true,
        'origem' => true,
        'titulo' => true,
        'situacao' => true,
        'usuario' => true,
        'parent_feedback' => true,
        'child_feedbacks' => true,
    ];
}
