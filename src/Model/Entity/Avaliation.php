<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Avaliation Entity
 *
 * @property int $id
 * @property int|null $avaliador_bolsista_id
 * @property int|null $question_id
 * @property float|null $nota
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property string|null $observacao_avaliador
 * @property string|null $parecer
 * @property int|null $deleted
 *
 * @property \App\Model\Entity\AvaliadorBolsista $avaliador_bolsista
 * @property \App\Model\Entity\Question $question
 */
class Avaliation extends Entity
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
        'avaliador_bolsista_id' => true,
        'question_id' => true,
        'nota' => true,
        'created' => true,
        'modified' => true,
        'observacao_avaliador' => true,
        'parecer' => true,
        'deleted' => true,
        'avaliador_bolsista' => true,
        'question' => true,
    ];
}
