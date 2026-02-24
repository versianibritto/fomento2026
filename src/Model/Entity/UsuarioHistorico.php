<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * UsuarioHistorico Entity
 *
 * @property int $id
 * @property \Cake\I18n\DateTime|null $created
 * @property int|null $usuario_id
 * @property int|null $alterado_por
 * @property string|null $contexto
 * @property string|null $origem_acesso
 * @property array|string|null $diff_json
 *
 * @property \App\Model\Entity\Usuario|null $usuario
 * @property \App\Model\Entity\Usuario|null $alterador
 */
class UsuarioHistorico extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'created' => true,
        'usuario_id' => true,
        'alterado_por' => true,
        'contexto' => true,
        'origem_acesso' => true,
        'diff_json' => true,
        'usuario' => true,
        'alterador' => true,
    ];
}
