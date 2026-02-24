<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * TiException Entity
 *
 * @property int $id
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property string|null $status
 * @property int|null $classificacao_id
 * @property int|null $usuario_id
 * @property string|null $usuario_nome
 * @property string|null $usuario_email
 * @property string|null $usuario_email_alternativo
 * @property string|null $usuario_email_contato
 * @property string|null $url
 * @property string|null $host
 * @property string|null $mensagem
 * @property string|null $arquivo
 * @property int|null $linha
 * @property string|null $hash
 * @property int|null $repeticoes
 * @property bool|null $repeticao
 * @property int|null $repeticao_de_id
 * @property \Cake\I18n\DateTime|null $ultima_ocorrencia
 * @property string|null $resposta
 * @property int|null $respondido_por
 * @property \Cake\I18n\DateTime|null $respondido_em
 */
class TiException extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'created' => true,
        'modified' => true,
        'status' => true,
        'classificacao_id' => true,
        'usuario_id' => true,
        'usuario_nome' => true,
        'usuario_email' => true,
        'usuario_email_alternativo' => true,
        'usuario_email_contato' => true,
        'url' => true,
        'host' => true,
        'mensagem' => true,
        'arquivo' => true,
        'linha' => true,
        'hash' => true,
        'repeticoes' => true,
        'repeticao' => true,
        'repeticao_de_id' => true,
        'ultima_ocorrencia' => true,
        'resposta' => true,
        'respondido_por' => true,
        'respondido_em' => true,
    ];
}
