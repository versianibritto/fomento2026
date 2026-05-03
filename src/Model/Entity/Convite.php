<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Convite Entity
 *
 * @property int $id
 * @property int|null $usuario_id
 * @property int|null $ano
 * @property int|null $aceite
 * @property string|null $editais
 * @property int|null $cadastrado_por
 * @property int|null $deletado_por
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property \Cake\I18n\DateTime|null $deleted
 *
 * @property \App\Model\Entity\Usuario|null $usuario
 * @property \App\Model\Entity\Usuario|null $cadastrador
 * @property \App\Model\Entity\Usuario|null $deletador
 */
class Convite extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'usuario_id' => true,
        'ano' => true,
        'aceite' => true,
        'editais' => true,
        'cadastrado_por' => true,
        'deletado_por' => true,
        'created' => true,
        'modified' => true,
        'deleted' => true,
        'usuario' => true,
        'cadastrador' => true,
        'deletador' => true,
    ];
}
