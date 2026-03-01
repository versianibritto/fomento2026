<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * SuporteChamado Entity
 *
 * @property int $id
 * @property int|null $ramo
 * @property int|null $parent_id
 * @property int $usuario_id
 * @property int|null $destinatario_id
 * @property int $para_outro
 * @property int|null $categoria_id
 * @property int $status_id
 * @property int|null $classificacao_final_id
 * @property string $origem
 * @property string $texto
 * @property string|null $anexo_1
 * @property string|null $anexo_2
 * @property string|null $anexo_3
 * @property int $reaberto
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property \Cake\I18n\DateTime|null $finalizado
 */
class SuporteChamado extends Entity
{
    protected array $_accessible = [
        'ramo' => true,
        'parent_id' => true,
        'usuario_id' => true,
        'destinatario_id' => true,
        'para_outro' => true,
        'categoria_id' => true,
        'status_id' => true,
        'classificacao_final_id' => true,
        'origem' => true,
        'texto' => true,
        'anexo_1' => true,
        'anexo_2' => true,
        'anexo_3' => true,
        'reaberto' => true,
        'created' => true,
        'modified' => true,
        'finalizado' => true,
    ];
}
