<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Errata Entity
 *
 * @property int $id
 * @property int|null $editai_id
 * @property int|null $vitrine_id
 * @property string|null $introducao
 * @property string|null $onde_le
 * @property string|null $leia_se
 * @property \Cake\I18n\DateTime|null $created
 * @property string|null $arquivo
 *
 * @property \App\Model\Entity\Editai $editai
 */
class Errata extends Entity
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
        'editai_id' => true,
        'vitrine_id' => true,
        'introducao' => true,
        'onde_le' => true,
        'leia_se' => true,
        'created' => true,
        'arquivo' => true,
        'editai' => true,
    ];
}
