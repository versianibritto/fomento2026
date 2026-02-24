<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * EditaisPrazo Entity
 *
 * @property int $id
 * @property int|null $editai_id
 * @property int|null $usuario_id
 * @property int|null $editais_wk_id
 * @property string|null $cpf
 * @property string|null $inscricao
 * @property string|null $tabela
 * @property \Cake\I18n\DateTime|null $inicio
 * @property \Cake\I18n\DateTime|null $fim
 * @property int|null $deleted
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Editai $editai
 * @property \App\Model\Entity\EditaisWk $editais_wk
 * @property \App\Model\Entity\Usuario $usuario
 */
class EditaisPrazo extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'editai_id' => true,
        'usuario_id' => true,
        'editais_wk_id' => true,
        'cpf' => true,
        'inscricao' => true,
        'tabela' => true,
        'inicio' => true,
        'fim' => true,
        'deleted' => true,
        'created' => true,
        'modified' => true,
        'editai' => true,
        'editais_wk' => true,
        'usuario' => true,
    ];
}
