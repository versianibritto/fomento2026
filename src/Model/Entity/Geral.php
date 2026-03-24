<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Geral Entity
 *
 * @property int $id
 * @property int|null $bolsista
 * @property string|null $nome_bolsista
 * @property string|null $social_bolsista
 * @property string|null $sexo
 * @property string|null $cpf_bolsista
 * @property string|null $documento
 * @property string|null $documento_numero
 * @property string|null $documento_emissor
 * @property string|null $nascimento
 * @property string|null $telefone
 * @property string|null $telefone_contato
 * @property string|null $celular
 * @property string|null $whatsapp
 * @property string|null $cep
 * @property string|null $rua
 * @property string|null $complemento
 * @property string|null $bairro
 * @property string|null $cidade
 * @property string|null $estado
 * @property string|null $email_bolsista
 * @property string|null $email_alternativo_bolsista
 * @property string|null $email_contato_bolsista
 * @property string|null $curso
 * @property int|null $orientador
 * @property string|null $nome_orientador
 * @property string|null $social_orientador
 * @property string|null $telefone_orientador
 * @property string|null $telefone_contato_orientador
 * @property string|null $celular_orientador
 * @property string|null $whatsapp_orientador
 * @property string|null $email_orientador
 * @property string|null $email_alternativo_orientador
 * @property string|null $email_contato_orientador
 * @property int|null $unidade_id
 * @property string|null $unidade_orientador
 * @property int|null $vinculo_orientador_id
 * @property string|null $vinculo_orientador
 * @property int|null $coorientador
 * @property string|null $nome_coorientador
 * @property string|null $social_coorientador
 * @property string|null $telefone_coorientador
 * @property string|null $telefone_contato_coorientador
 * @property string|null $celular_coorientador
 * @property string|null $whatsapp_coorientador
 * @property string|null $email_coorientador
 * @property string|null $email_alternativo_coorientador
 * @property string|null $email_contato_coorientador
 * @property int|null $unidade_id_coorientador
 * @property string|null $unidade_coorientador
 * @property int|null $vinculo_coorientador_id
 * @property string|null $vinculo_coorientador
 * @property int|null $projeto_id
 * @property string|null $projeto_orientador
 * @property string|null $grande_area
 * @property string|null $area
 * @property string|null $area_fiocruz
 * @property string|null $linha
 * @property string|null $titulo_subprojeto
 * @property int|null $programa_id
 * @property string|null $programa_nome
 * @property int|null $editai_id
 * @property string|null $editai_nome
 * @property string|null $inicio_vigencia
 * @property string|null $fim_vigencia
 * @property string|null $ed_controller
 * @property string|null $cota
 * @property int|null $fase_id
 * @property string|null $fase_nome
 * @property string|null $filhos_menor
 * @property string|null $origem
 * @property int|null $prorrogacao
 * @property int|null $autorizacao
 * @property int|null $primeiro_periodo
 * @property string|null $resultado
 * @property string|null $created
 * @property string|null $data_inicio
 * @property string|null $primeira_bolsa
 * @property int|null $vigente
 * @property string|null $tipo_bolsa
 * @property string|null $justificativa_cancelamento
 * @property string|null $deleted
 * @property int|null $area_pdj
 * @property string|null $area_pdj_nome
 * @property int|null $bolsista_anterior
 * @property int|null $referencia_inscricao_anterior
 * @property int|null $troca_projeto
 * @property int|null $heranca
 */
class Geral extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'id' => true,
        'bolsista' => true,
        'nome_bolsista' => true,
        'social_bolsista' => true,
        'sexo' => true,
        'cpf_bolsista' => true,
        'documento' => true,
        'documento_numero' => true,
        'documento_emissor' => true,
        'nascimento' => true,
        'telefone' => true,
        'telefone_contato' => true,
        'celular' => true,
        'whatsapp' => true,
        'cep' => true,
        'rua' => true,
        'complemento' => true,
        'bairro' => true,
        'cidade' => true,
        'estado' => true,
        'email_bolsista' => true,
        'email_alternativo_bolsista' => true,
        'email_contato_bolsista' => true,
        'curso' => true,
        'orientador' => true,
        'nome_orientador' => true,
        'social_orientador' => true,
        'telefone_orientador' => true,
        'telefone_contato_orientador' => true,
        'celular_orientador' => true,
        'whatsapp_orientador' => true,
        'email_orientador' => true,
        'email_alternativo_orientador' => true,
        'email_contato_orientador' => true,
        'unidade_id' => true,
        'unidade_orientador' => true,
        'vinculo_orientador_id' => true,
        'vinculo_orientador' => true,
        'coorientador' => true,
        'nome_coorientador' => true,
        'social_coorientador' => true,
        'telefone_coorientador' => true,
        'telefone_contato_coorientador' => true,
        'celular_coorientador' => true,
        'whatsapp_coorientador' => true,
        'email_coorientador' => true,
        'email_alternativo_coorientador' => true,
        'email_contato_coorientador' => true,
        'unidade_id_coorientador' => true,
        'unidade_coorientador' => true,
        'vinculo_coorientador_id' => true,
        'vinculo_coorientador' => true,
        'projeto_id' => true,
        'projeto_orientador' => true,
        'grande_area' => true,
        'area' => true,
        'area_fiocruz' => true,
        'linha' => true,
        'titulo_subprojeto' => true,
        'programa_id' => true,
        'programa_nome' => true,
        'editai_id' => true,
        'editai_nome' => true,
        'inicio_vigencia' => true,
        'fim_vigencia' => true,
        'ed_controller' => true,
        'cota' => true,
        'fase_id' => true,
        'fase_nome' => true,
        'filhos_menor' => true,
        'origem' => true,
        'prorrogacao' => true,
        'autorizacao' => true,
        'primeiro_periodo' => true,
        'resultado' => true,
        'created' => true,
        'data_inicio' => true,
        'primeira_bolsa' => true,
        'vigente' => true,
        'tipo_bolsa' => true,
        'justificativa_cancelamento' => true,
        'deleted' => true,
        'area_pdj' => true,
        'area_pdj_nome' => true,
        'bolsista_anterior' => true,
        'referencia_inscricao_anterior' => true,
        'troca_projeto' => true,
        'heranca' => true,
    ];
}
