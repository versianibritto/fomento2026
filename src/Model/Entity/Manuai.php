<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Manuai Entity
 *
 * @property int $id
 * @property string|null $arquivo
 * @property int|null $usuario_id
 * @property \Cake\I18n\DateTime|null $deleted
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property string|null $nome
 * @property int|null $restrito
 *
 * @property \App\Model\Entity\Usuario $usuario
 */
class Manuai extends Entity
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
        'arquivo' => true,
        'usuario_id' => true,
        'deleted' => true,
        'created' => true,
        'modified' => true,
        'nome' => true,
        'restrito' => true,
        'usuario' => true,
    ];
}
