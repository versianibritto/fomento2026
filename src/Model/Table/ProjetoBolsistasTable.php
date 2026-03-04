<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\Rule\ExistsIn;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;


class ProjetoBolsistasTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('projeto_bolsistas');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Editais', [
            'foreignKey' => 'editai_id',
            'joinType' => 'LEFT',
        ]);
        $this->belongsTo('Fases', [
            'foreignKey' => 'fase_id',
            'joinType' => 'LEFT',
        ]);
        
        $this->belongsTo('Projetos', [
            'foreignKey' => 'projeto_id',
        ]);

        $this->belongsTo('MotivoCancelamentos', [
            'foreignKey' => 'motivo_cancelamento_id',
        ]);
        $this->belongsTo('Areas', [
            'foreignKey' => 'area_pdj',
            'joinType' => 'LEFT',
        ]);
        $this->belongsTo('Bolsistas', [
            'className' => 'Usuarios',
            'foreignKey' => 'bolsista',
            'joinType' => 'LEFT',
            'propertyName' => 'bolsista_usuario',
        ]);
        $this->belongsTo('Orientadores', [
            'className' => 'Usuarios',
            'foreignKey' => 'orientador',
            'joinType' => 'LEFT',
        ]);
        $this->belongsTo('Usuarios', [
            'foreignKey' => 'usuario_id',
            'joinType' => 'LEFT',
        ]);
        $this->belongsTo('Coorientadores', [
            'className' => 'Usuarios',
            'foreignKey' => 'coorientador',
            'joinType' => 'LEFT',
        ]);
        $this->belongsTo('Substitutos', [
            'className' => 'ProjetoBolsistas',
            'foreignKey' => 'bolsista_anterior',
            'joinType' => 'LEFT',
        ]);
        $this->hasMany('Gabaritos', [
            'foreignKey' => 'raic_id',
        ]);
        $this->hasMany('AvaliadorBolsistas', [
            'foreignKey' => 'bolsista',
        ]);
        $this->belongsTo('Revistas', [
            'foreignKey' => 'revista_id',
            'joinType' => 'LEFT',
        ]);

        $this->belongsTo('Referencias', [
            'className' => 'ProjetoBolsistas',
            'foreignKey' => 'referencia_inscricao_anterior',
            'joinType' => 'LEFT',
        ]);
        $this->belongsTo('Matrizes', [
            'className' => 'ProjetoBolsistas',
            'foreignKey' => 'matriz',
            'joinType' => 'LEFT',
            'propertyName' => 'matriz_projeto_bolsista',
        ]);
        $this->belongsTo('PdjInscricoes', [
            'foreignKey' => 'pdj_inscricoe_id',
            'joinType' => 'LEFT',
        ]);

        // $this->belongsTo('ProjetosDados', [
        //     'foreignKey' => 'projetos_dado_id',
        // ]);
        $this->hasMany('ProjetoAnexos', [
            'foreignKey' => 'projeto_bolsista_id',
        ]);
        $this->hasMany('Anexos', [
            'foreignKey' => 'projeto_bolsista_id',
        ]);
        $this->hasMany('SituacaoHistoricos', [
            'foreignKey' => 'projeto_bolsista_id',
        ]);
        $this->hasMany('FonteHistoricos', [
            'foreignKey' => 'projeto_bolsista_id',
        ]);

    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->nonNegativeInteger('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->date('data_primeira')
            ->allowEmptyDate('data_primeira');

        $validator
            ->dateTime('data_inicio')
            ->allowEmptyDateTime('data_inicio');

        $validator
            ->dateTime('data_fim')
            ->allowEmptyDateTime('data_fim');

        $validator
            ->nonNegativeInteger('orientador')
            ->allowEmptyString('orientador');

        $validator
            ->nonNegativeInteger('coorientador')
            ->allowEmptyString('coorientador');

        $validator
            ->boolean('estagio_previo')
            ->allowEmptyString('estagio_previo');

        $validator
            ->boolean('provoc')
            ->allowEmptyString('provoc');

        $validator
            ->boolean('pibi_antes')
            ->allowEmptyString('pibi_antes');

        $validator
            ->scalar('matricula')
            ->maxLength('matricula', 45)
            ->allowEmptyString('matricula');

        $validator
            ->scalar('historico')
            ->allowEmptyString('historico');

        $validator
            ->numeric('cr_acumulado')
            ->greaterThanOrEqual('cr_acumulado', 0)
            ->allowEmptyString('cr_acumulado');

        $validator
            ->scalar('sp_titulo')
            ->allowEmptyString('sp_titulo');

        $validator
            ->scalar('sp_resumo')
            ->maxLength('sp_resumo', 4294967295)
            ->allowEmptyString('sp_resumo');

        $validator
            ->scalar('sp_objetivos')
            ->maxLength('sp_objetivos', 4294967295)
            ->allowEmptyString('sp_objetivos');

        $validator
            ->scalar('sp_projeto')
            ->maxLength('sp_projeto', 4294967295)
            ->allowEmptyString('sp_projeto');

        $validator
            ->scalar('anexo')
            ->maxLength('anexo', 50)
            ->allowEmptyString('anexo');

        $validator
            ->scalar('codigo_declaracao')
            ->maxLength('codigo_declaracao', 100)
            ->allowEmptyString('codigo_declaracao');

        $validator
            ->scalar('relatorio')
            ->maxLength('relatorio', 45)
            ->allowEmptyString('relatorio');

        $validator
            ->scalar('egresso')
            ->maxLength('egresso', 45)
            ->allowEmptyString('egresso');

        $validator
            ->scalar('relatorio_final')
            ->maxLength('relatorio_final', 45)
            ->allowEmptyString('relatorio_final');

        $validator
            ->dateTime('relatorio_entregue')
            ->allowEmptyDateTime('relatorio_entregue');

        $validator
            ->scalar('situacao')
            ->allowEmptyString('situacao');

        $validator
            ->integer('bolsista_anterior')
            ->allowEmptyString('bolsista_anterior');

        $validator
            ->dateTime('data_substituicao')
            ->allowEmptyDateTime('data_substituicao');

        $validator
            ->nonNegativeInteger('substituicao_confirmador')
            ->allowEmptyString('substituicao_confirmador');

        $validator
            ->dateTime('data_sub_confirmacao')
            ->allowEmptyDateTime('data_sub_confirmacao');

        $validator
            ->numeric('nota_final')
            ->allowEmptyString('nota_final');

        $validator
            ->numeric('pontos_orientador')
            ->greaterThanOrEqual('pontos_orientador', 0)
            ->allowEmptyString('pontos_orientador');

        $validator
            ->scalar('justificativa')
            ->allowEmptyString('justificativa');

        $validator
            ->boolean('segunda_cota')
            ->allowEmptyString('segunda_cota');

        $validator
            ->scalar('justificativa_cancelamento')
            ->allowEmptyString('justificativa_cancelamento');

        $validator
            ->dateTime('data_cancelamento')
            ->allowEmptyDateTime('data_cancelamento');

        $validator
            ->dateTime('data_cancela_confirmacao')
            ->allowEmptyDateTime('data_cancela_confirmacao');

        $validator
            ->nonNegativeInteger('cancelamento_confirmador')
            ->allowEmptyString('cancelamento_confirmador');

        $validator
            ->boolean('premiado')
            ->allowEmptyString('premiado');

        $validator
            ->scalar('tipo_bolsa')
            ->allowEmptyString('tipo_bolsa');

        $validator
            ->scalar('origem')
            ->allowEmptyString('origem');

        $validator
            ->scalar('programa')
            ->allowEmptyString('programa');

        $validator
            ->integer('programa_id')
            ->allowEmptyString('programa_id');

        $validator
            ->integer('primeiro_periodo')
            ->allowEmptyString('primeiro_periodo');

        $validator
            ->boolean('troca_projeto')
            ->allowEmptyString('troca_projeto');

        $validator
            ->boolean('heranca')
            ->allowEmptyString('heranca');

        $validator
            ->numeric('pontos_bolsista')
            ->greaterThanOrEqual('pontos_bolsista', 0)
            ->allowEmptyString('pontos_bolsista');

        $validator
            ->integer('area_pdj')
            ->allowEmptyString('area_pdj');

        $validator
            ->dateTime('deleted')
            ->allowEmptyDateTime('deleted');

        $validator
            ->integer('matriz')
            ->allowEmptyString('matriz');

        $validator
            ->integer('pdj_inscricoe_id')
            ->allowEmptyString('pdj_inscricoe_id');

        $validator
            ->integer('ano_doutorado')
            ->allowEmptyString('ano_doutorado');

        $validator
            ->boolean('recem_servidor')
            ->allowEmptyString('recem_servidor');

        $validator
            ->scalar('justificativa_bolsa')
            ->maxLength('justificativa_bolsa', 4000)
            ->allowEmptyString('justificativa_bolsa');

        $validator
            ->boolean('vigente')
            ->notEmptyString('vigente');

        $validator
            ->scalar('resultado')
            ->allowEmptyString('resultado');

        $validator
            ->scalar('cota')
            ->allowEmptyString('cota');

        $validator
            ->scalar('atestado')
            ->allowEmptyString('atestado');


        $validator
            ->integer('autorizacao')
            ->notEmptyString('autorizacao');

        $validator
            ->scalar('autorizacao_anexo')
            ->maxLength('autorizacao_anexo', 45)
            ->allowEmptyString('autorizacao_anexo');
        
        $validator
            ->scalar('data_resposta_bolsista')
            ->allowEmptyDateTime('data_resposta_bolsista');

        $validator
            ->scalar('resposta_bolsista')
            ->allowEmptyString('resposta_bolsista');

        $validator
            ->scalar('justificativa_recusa_bolsista')
            ->maxLength('justificativa_recusa_bolsista', 4294967295)
            ->allowEmptyString('justificativa_recusa_bolsista');

        $validator
            ->scalar('data_resposta_coorientador')
            ->allowEmptyDateTime('data_resposta_coorientador');

        $validator
            ->scalar('resposta_coorientador')
            ->allowEmptyString('resposta_coorientador');

        $validator
            ->scalar('justificativa_recusa_coorientador')
            ->maxLength('justificativa_recusa_coorientador', 4294967295)
            ->allowEmptyString('justificativa_recusa_coorientador');

        $validator
            ->scalar('revista_orientador')
            ->allowEmptyString('revista_orientador');

        $validator
            ->scalar('revista_bolsista')
            ->allowEmptyString('revista_bolsista');
        
        $validator
            ->scalar('anexo_rg')
            ->maxLength('anexo_rg', 45)
            ->allowEmptyString('anexo_rg');
        
        $validator
            ->scalar('filhos_menor')
            ->maxLength('filhos_menor', 1)
            ->allowEmptyString('filhos_menor');

        $validator
            ->scalar('referencia_raic')
            ->allowEmptyString('referencia_raic');
        
        $validator
            ->scalar('anexo_rg_responsavel')
            ->maxLength('anexo_rg_responsavel', 45)
            ->allowEmptyString('anexo_rg_responsavel');

        $validator
            ->date('data_fim_cancelamento')
            ->allowEmptyDate('data_fim_cancelamento');

        $validator
            ->scalar('justificativa_alteracao')
            ->maxLength('justificativa_alteracao', 4294967295)
            ->allowEmptyString('justificativa_alteracao');

        $validator
            ->scalar('subprojeto_renovacao')
            ->allowEmptyString('subprojeto_renovacao');

        $validator
            ->integer('referencia_inscricao_anterior')
            ->allowEmptyString('referencia_inscricao_anterior');
        
        $validator
            ->scalar('apresentar_raic')
            ->allowEmptyString('apresentar_raic');

        $validator
            ->scalar('palavras_chave')
            ->maxLength('palavras_chave', 4294967295)
            ->allowEmptyString('palavras_chave');

        $validator
            ->integer('ordem')
            ->allowEmptyString('ordem');

        $validator
            ->boolean('prorrogacao')
            ->notEmptyString('prorrogacao');

        $validator
            ->scalar('resumo_relatorio')
            ->maxLength('resumo_relatorio', 4294967295)
            ->allowEmptyString('resumo_relatorio');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn('editai_id', 'Editais'), ['errorField' => 'editai_id']);
        $rules->add($rules->existsIn('projeto_id', 'Projetos'), ['errorField' => 'projeto_id']);
        $rules->add($rules->existsIn('area_pdj', 'Areas'), ['errorField' => 'area_pdj']);
        $rules->add($rules->existsIn('matriz', 'Matrizes'), ['errorField' => 'matriz']);
        $rules->add($rules->existsIn('pdj_inscricoe_id', 'PdjInscricoes'), ['errorField' => 'pdj_inscricoe_id']);
     

        return $rules;
    }
}
