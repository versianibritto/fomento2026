<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * AnexosTipo Entity
 *
 * @property int $id
 * @property string $nome
 * @property string|null $bloco
 * @property int|null $deleted
 * @property int|null $condicional
 * @property string|null $programa
 * @property string|null $cota
 *
 * @property \App\Model\Entity\Anexo[] $anexos
 */
class AnexosTipo extends Entity
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
        'bloco' => true,
        'deleted' => true,
        'condicional' => true,
        'programa' => true,
        'cota' => true,
        'anexos' => true,
    ];
}
