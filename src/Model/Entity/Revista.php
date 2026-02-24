<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Revista Entity
 *
 * @property int $id
 * @property string|null $nome
 * @property string|null $edicao
 * @property int|null $ano
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 * @property int|null $deleted
 * @property \Cake\I18n\FrozenTime|null $data_publicacao
 *
 * @property \App\Model\Entity\ProjetoBolsista[] $projeto_bolsistas
 */
class Revista extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected array $_accessible = [
        'nome' => true,
        'edicao' => true,
        'ano' => true,
        'created' => true,
        'modified' => true,
        'deleted' => true,
        'data_publicacao' => true,
        'projeto_bolsistas' => true,
        'texto' =>true,
    ];
}
