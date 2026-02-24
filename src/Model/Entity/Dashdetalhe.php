<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Dashdetalhe Entity
 *
 * @property int $id
 * @property int|null $bolsista
 * @property string|null $nome_bolsista
 * @property int|null $orientador
 * @property string|null $nome_orientador
 * @property int|null $coorientador
 * @property string|null $nome_coorientador
 * @property int|null $projeto_id
 * @property int|null $fase_id
 * @property string|null $nome_fase
 * @property string|null $bloco
 * @property int|null $vigente
 * @property int|null $editai_id
 * @property string|null $nome_edital
 * @property string|null $controller
 * @property string|null $nome_programa
 * @property \Cake\I18n\DateTime|null $data_inicio
 * @property \Cake\I18n\DateTime|null $data_fim
 * @property \Cake\I18n\DateTime|null $fim_vigencia
 * @property int $ativo
 *
 * @property \App\Model\Entity\Projeto $projeto
 * @property \App\Model\Entity\Fase $fase
 * @property \App\Model\Entity\Editai $editai
 */
class Dashdetalhe extends Entity
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
        'id' => true,
        'bolsista' => true,
        'nome_bolsista' => true,
        'orientador' => true,
        'nome_orientador' => true,
        'coorientador' => true,
        'nome_coorientador' => true,
        'projeto_id' => true,
        'fase_id' => true,
        'nome_fase' => true,
        'bloco' => true,
        'vigente' => true,
        'editai_id' => true,
        'nome_edital' => true,
        'controller' => true,
        'nome_programa' => true,
        'data_inicio' => true,
        'data_fim' => true,
        'fim_vigencia' => true,
        'ativo' => true,
        'projeto' => true,
        'fase' => true,
        'editai' => true,
    ];
}
