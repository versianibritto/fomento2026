<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * SuporteStatusHistorico Entity
 *
 * @property int $id
 * @property int $suporte_id
 * @property int|null $ramo
 * @property int $usuario_id
 * @property int|null $status_anterior_id
 * @property int $status_novo_id
 * @property \Cake\I18n\DateTime|null $created
 */
class SuporteStatusHistorico extends Entity
{
    protected array $_accessible = [
        'suporte_id' => true,
        'ramo' => true,
        'usuario_id' => true,
        'status_anterior_id' => true,
        'status_novo_id' => true,
        'created' => true,
    ];
}
