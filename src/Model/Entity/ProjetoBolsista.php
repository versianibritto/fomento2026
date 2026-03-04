<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ProjetoBolsista Entity
 *
 * @property int $id
 * @property int $editai_id
 * @property int|null $projeto_id
 * @property int|null $bolsista
 * @property \Cake\I18n\Date|null $data_primeira
 * @property \Cake\I18n\DateTime|null $data_inicio
 * @property \Cake\I18n\DateTime|null $data_fim
 * @property int|null $orientador
 * @property int|null $coorientador
 * @property int|null $estagio_previo
 * @property int|null $provoc
 * @property int|null $pibi_antes
 * @property string|null $matricula
 * @property string|null $historico
 * @property float|null $cr_acumulado
 * @property string|null $sp_titulo
 * @property string|null $sp_resumo
 * @property string|null $palavras_chaves
 * @property string|null $sp_objetivos
 * @property string|null $sp_projeto
 * @property string|null $anexo
 * @property string|null $codigo_declaracao
 * @property string|null $relatorio
 * @property string|null $egresso
 * @property \Cake\I18n\DateTime|null $relatorio_entregue
 * @property string|null $situacao
 * @property int|null $bolsista_anterior
 * @property \Cake\I18n\DateTime|null $data_substituicao
 * @property int|null $substituicao_confirmador
 * @property \Cake\I18n\DateTime|null $data_sub_confirmacao
 * @property float|null $nota_final
 * @property float|null $pontos_orientador
 * @property string|null $justificativa
 * @property bool|null $segunda_cota
 * @property int|null $motivo_cancelamento_id
 * @property string|null $justificativa_cancelamento
 * @property \Cake\I18n\DateTime|null $data_cancelamento
 * @property \Cake\I18n\DateTime|null $data_cancela_confirmacao
 * @property int|null $cancelamento_confirmador
 * @property bool|null $premiado
 * @property string|null $tipo_bolsa
 * @property string|null $origem
 * @property string|null $programa
 * @property \Cake\I18n\DateTime|null $modified
 * @property bool $vigente
 * @property string|null $resultado
 * @property string|null $cota
 * @property string|null $atestado
 * @property \Cake\I18n\DateTime|null $created
 * @property int|null $projetos_dado_id
 * @property string|null $relatorio_final
 * @property int|null $revista_id
 * @property int|null $autorizacao
 * @property string|null $autorizacao_anexo
 * @property \Cake\I18n\DateTime|null $data_resposta_bolsista
 * @property \Cake\I18n\DateTime|null $data_inclusao_bolsista
 * @property string|null $resposta_bolsista
 * @property string|null $justificativa_recusa_bolsista
 * @property \Cake\I18n\DateTime|null $data_resposta_coorientador
 * @property string|null $resposta_coorientador
 * @property string|null $justificativa_recusa_coorientador
 * @property int|null $revista_orientador
 * @property int|null $revista_bolsista
 * @property string|null $anexo_rg
 * @property string|null $filhos_menor
 * @property int|null $referencia_raic
 * @property string|null $anexo_rg_responsavel
 * @property \Cake\I18n\Date|null $data_fim_cancelamento
 * @property int|null $apresentar_raic
 * @property int|null $referencia_inscricao_anterior
 * @property string|null $subprojeto_renovacao
 * @property string|null $justificativa_alteracao
 * @property int|null $ordem
 * @property int|null $prorrogacao
 * @property string|null $resumo_relatorio
 * @property int|null $fase_id
 * @property int|null $programa_id
 * @property int|null $primeiro_periodo
 * @property bool|null $troca_projeto
 * @property bool|null $heranca
 * @property float|null $pontos_bolsista
 * @property int|null $area_pdj
 * @property \Cake\I18n\DateTime|null $deleted
 * @property int|null $matriz
 * @property int|null $pdj_inscricoe_id
 * @property int|null $ano_doutorado
 * @property bool|null $recem_servidor
 * @property string|null $justificativa_bolsa
 *
 * @property \App\Model\Entity\Editai $editai
 * @property \App\Model\Entity\Projeto $projeto
 * @property \App\Model\Entity\Usuario $usuario
 * @property \App\Model\Entity\MotivoCancelamento $motivo_cancelamento
 * @property \App\Model\Entity\Area $area
 * @property \App\Model\Entity\ProjetoBolsista $matriz_projeto_bolsista
 * @property \App\Model\Entity\PdjInscrico $pdj_inscrico
 * @property \App\Model\Entity\ProjetosDado $projetos_dado
 * @property \App\Model\Entity\Revista $revista
 * @property \App\Model\Entity\FonteHistorico[] $fonte_historicos
 * @property \App\Model\Entity\Gabarito[] $gabaritos
 * @property \App\Model\Entity\GabaritosBolsista[] $gabaritos_bolsistas
 * @property \App\Model\Entity\HistoricoCoorientadore[] $historico_coorientadores
 * @property \App\Model\Entity\ProjetoAnexo[] $projeto_anexos
 * @property \App\Model\Entity\Raic[] $raics
 * @property \App\Model\Entity\SituacaoHistorico[] $situacao_historicos
 */
class ProjetoBolsista extends Entity
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
        'projeto_id' => true,
        'bolsista' => true,
        'data_primeira' => true,
        'data_inicio' => true,
        'data_fim' => true,
        'orientador' => true,
        'coorientador' => true,
        'estagio_previo' => true,
        'provoc' => true,
        'pibi_antes' => true,
        'matricula' => true,
        'historico' => true,
        'cr_acumulado' => true,
        'sp_titulo' => true,
        'sp_resumo' => true,
        'palavras_chaves' => true,
        'sp_objetivos' => true,
        'sp_projeto' => true,
        'anexo' => true,
        'codigo_declaracao' => true,
        'relatorio' => true,
        'egresso' => true,
        'relatorio_entregue' => true,
        'situacao' => true,
        'bolsista_anterior' => true,
        'data_substituicao' => true,
        'substituicao_confirmador' => true,
        'data_sub_confirmacao' => true,
        'nota_final' => true,
        'pontos_orientador' => true,
        'justificativa' => true,
        'segunda_cota' => true,
        'motivo_cancelamento_id' => true,
        'justificativa_cancelamento' => true,
        'data_cancelamento' => true,
        'data_cancela_confirmacao' => true,
        'cancelamento_confirmador' => true,
        'premiado' => true,
        'tipo_bolsa' => true,
        'origem' => true,
        'programa' => true,
        'modified' => true,
        'vigente' => true,
        'resultado' => true,
        'cota' => true,
        'atestado' => true,
        'created' => true,
        'projetos_dado_id' => true,
        'relatorio_final' => true,
        'revista_id' => true,
        'autorizacao' => true,
        'autorizacao_anexo' => true,
        'data_resposta_bolsista' => true,
        'data_inclusao_bolsista' => true,
        'resposta_bolsista' => true,
        'justificativa_recusa_bolsista' => true,
        'data_resposta_coorientador' => true,
        'resposta_coorientador' => true,
        'justificativa_recusa_coorientador' => true,
        'revista_orientador' => true,
        'revista_bolsista' => true,
        'anexo_rg' => true,
        'filhos_menor' => true,
        'referencia_raic' => true,
        'anexo_rg_responsavel' => true,
        'data_fim_cancelamento' => true,
        'apresentar_raic' => true,
        'referencia_inscricao_anterior' => true,
        'subprojeto_renovacao' => true,
        'justificativa_alteracao' => true,
        'ordem' => true,
        'prorrogacao' => true,
        'resumo_relatorio' => true,
        'fase_id' => true,
        'programa_id' => true,
        'primeiro_periodo' => true,
        'troca_projeto' => true,
        'heranca' => true,
        'pontos_bolsista' => true,
        'area_pdj' => true,
        'deleted' => true,
        'matriz' => true,
        'pdj_inscricoe_id' => true,
        'ano_doutorado' => true,
        'recem_servidor' => true,
        'justificativa_bolsa' => true,
        'editai' => true,
        'projeto' => true,
        'usuario' => true,
        'motivo_cancelamento' => true,
        'area' => true,
        'matriz_projeto_bolsista' => true,
        'pdj_inscrico' => true,
        'projetos_dado' => true,
        'revista' => true,
        'fonte_historicos' => true,
        'gabaritos' => true,
        'gabaritos_bolsistas' => true,
        'historico_coorientadores' => true,
        'projeto_anexos' => true,
        'raics' => true,
        'situacao_historicos' => true,
    ];
}
