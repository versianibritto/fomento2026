<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Vinculo Entity
 *
 * @property int $id
 * @property string|null $nome
 * @property int|null $servidor
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property int|null $deleted
 *
 * @property \App\Model\Entity\UsuarioHistorico[] $usuario_historicos
 * @property \App\Model\Entity\Usuario[] $usuarios
 */
class Vinculo extends Entity
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
        'servidor' => true,
        'created' => true,
        'modified' => true,
        'deleted' => true,
        'usuario_historicos' => true,
        'usuarios' => true,
    ];
}
