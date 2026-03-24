<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Mensagem Entity
 *
 * @property int $id
 * @property string|null $titulo
 * @property string|null $testo
 * @property string|null $imagem
 * @property string|null $tipo
 * @property \Cake\I18n\DateTime|null $inicio
 * @property \Cake\I18n\DateTime|null $fim
 * @property \Cake\I18n\DateTime|null $deleted
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 */
class Mensagem extends Entity
{
    protected array $_accessible = [
        'titulo' => true,
        'testo' => true,
        'imagem' => true,
        'tipo' => true,
        'inicio' => true,
        'fim' => true,
        'deleted' => true,
        'created' => true,
        'modified' => true,
    ];
}
