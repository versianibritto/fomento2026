<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * RaicGeral Entity
 *
 * @property int $id
 * @property int|null $bolsista
 * @property string|null $nome_bolsista
 * @property string|null $telefone
 * @property string|null $telefone_contato
 * @property string|null $celular
 * @property string|null $whatsapp
 * @property string|null $email_bolsista
 * @property string|null $email_alternativo_bolsista
 * @property string|null $email_contato_bolsista
 * @property int|null $orientador
 * @property string|null $nome_orientador
 * @property string|null $telefone_orientador
 * @property string|null $telefone_contato_orientador
 * @property string|null $celular_orientador
 * @property string|null $whatsapp_orientador
 * @property string|null $email_orientador
 * @property string|null $email_alternativo_orientador
 * @property string|null $email_contato_orientador
 * @property int|null $projeto_id
 * @property \Cake\I18n\Date|null $data_apresentacao
 * @property string|null $titulo
 * @property string|null $tipo_bolsa
 * @property string|null $presenca
 * @property int|null $raic_deleted
 * @property int|null $unidade_id
 * @property string|null $sigla
 * @property int|null $projeto_bolsista_id
 * @property int|null $fase_id
 * @property string|null $nome_fase
 * @property int|null $vigente
 * @property int|null $editai_id
 * @property string|null $nome_edital
 * @property string|null $nome_programa
 * @property int|null $programa_id
 * @property \Cake\I18n\DateTime|null $fim_vigencia
 * @property string|null $justificativa_cancelamento
 * @property int|null $pb_deleted
 */
class RaicGeral extends Entity
{
    protected array $_accessible = [
        '*' => true,
        'id' => true,
    ];
}
