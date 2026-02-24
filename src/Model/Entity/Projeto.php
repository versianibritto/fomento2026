<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Projeto Entity
 *
 * @property int $id
 * @property int $usuario_id
 * @property string $titulo
 * @property string|null $tipo_projeto
 * @property string|null $ano_inicio
 * @property int|null $duracao
 * @property string|null $financiamento
 * @property int|null $area_id
 * @property string|null $resumo
 * @property string|null $objetivos
 * @property string|null $parecer_comite
 * @property string|null $autorizacao_sisgen
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property string|null $situacao
 * @property \Cake\I18n\DateTime|null $data_situacao
 * @property string|null $justificativa_situacao
 * @property int|null $id_pibiti
 * @property int|null $linha_id
 * @property string|null $descricao
 * @property string|null $metodologia
 * @property string|null $vinculo_pdti
 * @property string|null $deposito_patentes
 * @property string|null $anexos
 * @property \Cake\I18n\DateTime|null $deleted
 * @property string|null $palavras_chaves
 *
 * @property \App\Model\Entity\Usuario $usuario
 * @property \App\Model\Entity\Area $area
 * @property \App\Model\Entity\Linha $linha
 * @property \App\Model\Entity\AvaliadorProjeto[] $avaliador_projetos
 * @property \App\Model\Entity\PdjInscrico[] $pdj_inscricoes
 * @property \App\Model\Entity\ProjetoAnexo[] $projeto_anexos
 * @property \App\Model\Entity\ProjetoBolsista[] $projeto_bolsistas
 * @property \App\Model\Entity\ProjetoKeyword[] $projeto_keywords
 * @property \App\Model\Entity\ProjetosDado[] $projetos_dados
 */
class Projeto extends Entity
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
        'usuario_id' => true,
        'titulo' => true,
        'tipo_projeto' => true,
        'ano_inicio' => true,
        'duracao' => true,
        'financiamento' => true,
        'area_id' => true,
        'resumo' => true,
        'objetivos' => true,
        'parecer_comite' => true,
        'autorizacao_sisgen' => true,
        'created' => true,
        'modified' => true,
        'situacao' => true,
        'data_situacao' => true,
        'justificativa_situacao' => true,
        'id_pibiti' => true,
        'linha_id' => true,
        'descricao' => true,
        'metodologia' => true,
        'vinculo_pdti' => true,
        'deposito_patentes' => true,
        'anexos' => true,
        'deleted' => true,
        'palavras_chaves' => true,
        'usuario' => true,
        'area' => true,
        'linha' => true,
        'avaliador_projetos' => true,
        'pdj_inscricoes' => true,
        'projeto_anexos' => true,
        'projeto_bolsistas' => true,
        'projeto_keywords' => true,
        'projetos_dados' => true,
    ];
}
