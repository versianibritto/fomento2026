<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * EditaisSumulasBloco Entity
 *
 * @property int $id
 * @property string|null $nome
 * @property \Cake\I18n\DateTime|null $deleted
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\EditaisSumula[] $editais_sumulas
 */
class EditaisSumulasBloco extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'nome' => true,
        'deleted' => true,
        'created' => true,
        'modified' => true,
        'editais_sumulas' => true,
    ];
}
