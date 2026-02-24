<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * UsuarioAcesso Entity
 *
 * @property int $id
 * @property int|null $usuario_id
 * @property string|null $tipo_acesso
 * @property string|null $nome
 * @property string|null $email
 * @property string|null $email_alternativo
 * @property string|null $acao
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Usuario|null $usuario
 */
class UsuarioAcesso extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'usuario_id' => true,
        'tipo_acesso' => true,
        'nome' => true,
        'email' => true,
        'email_alternativo' => true,
        'acao' => true,
        'created' => true,
        'modified' => true,
        'usuario' => true,
    ];
}
