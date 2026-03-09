<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Vitrine Entity
 *
 * @property int $id
 * @property \Cake\I18n\DateTime|null $deleted
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property string|null $nome
 * @property string|null $anexo_edital
 * @property string|null $anexo_resultado
 * @property string|null $anexo_resultado_recurso
 * @property string|null $anexo_modelo_relatorio
 * @property string|null $anexo_modelo_consentimento
 * @property \Cake\I18n\DateTime|null $divulgacao
 * @property string|null $obs
 * @property \Cake\I18n\DateTime|null $inicio
 * @property \Cake\I18n\DateTime|null $fim
 */
class Vitrine extends Entity
{
    protected array $_accessible = [
        'deleted' => true,
        'created' => true,
        'modified' => true,
        'nome' => true,
        'anexo_edital' => true,
        'anexo_resultado' => true,
        'anexo_resultado_recurso' => true,
        'anexo_modelo_relatorio' => true,
        'anexo_modelo_consentimento' => true,
        'divulgacao' => true,
        'obs' => true,
        'inicio' => true,
        'fim' => true,
    ];
}
