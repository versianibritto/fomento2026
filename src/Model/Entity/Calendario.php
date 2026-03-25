<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Calendario Entity
 *
 * @property int $id
 * @property string|null $tipo
 * @property string|null $descricao
 * @property \Cake\I18n\Date|null $dia
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property \Cake\I18n\DateTime|null $deleted
 */
class Calendario extends Entity
{
    protected array $_accessible = [
        'tipo' => true,
        'descricao' => true,
        'dia' => true,
        'created' => true,
        'modified' => true,
        'deleted' => true,
    ];
}
