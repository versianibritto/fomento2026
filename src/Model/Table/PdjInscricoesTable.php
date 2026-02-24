<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;


class PdjInscricoesTable extends Table
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

        $this->setTable('pdj_inscricoes');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Projetos', [
            'foreignKey' => 'projeto_id',
            'joinType' => 'INNER',
        ]);

        
        $this->belongsTo('Editais', [
            'foreignKey' => 'editai_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('GrandesAreas', [
            'foreignKey' => 'area_id',
            'joinType' => 'LEFT',
        ]);

        $this->belongsTo('Substitutospdj', [
            'className' => 'PdjInscricoes',
            'foreignKey' => 'bolsista_anterior',
            'joinType' => 'LEFT',
        ]);

        $this->belongsTo('MotivoCancelamentos', [
            'foreignKey' => 'motivo_cancelamento_id',
        ]);

        $this->hasMany('PdjHistoricos', [
            'foreignKey' => 'pdj_inscricoe_id',
        ]);

        $this->hasOne('Workshops', [
            'foreignKey' => 'pdj_inscricoe_id',
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
        $this->belongsTo('Coorientadores', [
            'className' => 'Usuarios',
            'foreignKey' => 'coorientador',
            'joinType' => 'LEFT',
        ]);
        

        $this->belongsTo('Referencias', [
            'className' => 'ProjetoBolsistas',
            'foreignKey' => 'referencia_inscricao_anterior',
            'joinType' => 'LEFT',
        ]);

        $this->hasMany('AvaliadorBolsistas', [
            'foreignKey' => 'bolsista',
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
            ->integer('projeto_id')
            ->allowEmptyString('projeto_id');

        $validator
            ->integer('orientador')
            ->notEmptyString('orientador');
        
        $validator
            ->integer('coorientador')
            ->notEmptyString('coorientador');

        $validator
            ->integer('bolsista')
            ->requirePresence('bolsista', 'create')
            ->notEmptyString('bolsista');

        $validator
            ->integer('area_id')
            ->notEmptyString('area_id');

        $validator
            ->integer('linha_fiocruz_id')
            ->allowEmptyString('linha_fiocruz_id');

        $validator
            ->integer('editai_id')
            ->allowEmptyString('editai_id');

        $validator
            ->scalar('situacao')
            ->allowEmptyString('situacao');

        $validator
            ->scalar('titulo')
            ->allowEmptyString('titulo');

        $validator
            ->scalar('financiadores')
            ->allowEmptyString('financiadores');

        $validator
            ->scalar('palavras_chaves')
            ->allowEmptyString('palavras_chaves');

        $validator
            ->scalar('resumo')
            ->maxLength('resumo', 4294967295)
            ->allowEmptyString('resumo');

        $validator
            ->dateTime('deleted')
            ->allowEmptyDateTime('deleted');

        $validator
            ->scalar('cota')
            ->allowEmptyString('cota');

        $validator
            ->boolean('vigente')
            ->notEmptyString('vigente');

        $validator
            ->scalar('resultado')
            ->allowEmptyString('resultado');

        $validator
            ->dateTime('data_inicio')
            ->allowEmptyDateTime('data_inicio');

        $validator
            ->dateTime('data_fim')
            ->allowEmptyDateTime('data_fim');

        $validator
            ->scalar('origem')
            ->allowEmptyString('origem');

        $validator
            ->integer('programa_id')
            ->allowEmptyString('programa_id');

        $validator
            ->integer('bolsista_anterior')
            ->allowEmptyString('bolsista_anterior');
        
        $validator
            ->scalar('justificativa_cancelamento')
            ->allowEmptyString('justificativa_cancelamento');

        $validator
            ->boolean('prorrogacao')
            ->notEmptyString('prorrogacao');

        $validator
            ->integer('referencia_inscricao_anterior')
            ->allowEmptyString('referencia_inscricao_anterior');

        $validator
            ->integer('ordem')
            ->allowEmptyString('ordem');

        $validator
            ->numeric('pontos_orientador')
            ->greaterThanOrEqual('pontos_orientador', 0)
            ->allowEmptyString('pontos_orientador');

        $validator
            ->numeric('pontos_bolsista')
            ->greaterThanOrEqual('pontos_bolsista', 0)
            ->allowEmptyString('pontos_bolsista');
        

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
        $rules->add($rules->existsIn('projeto_id', 'Projetos'), ['errorField' => 'projeto_id']);
        $rules->add($rules->existsIn('usuario_id', 'Usuarios'), ['errorField' => 'usuario_id']);
        $rules->add($rules->existsIn('area_id', 'GrandesAreas'), ['errorField' => 'area_id']);

        return $rules;
    }
}
