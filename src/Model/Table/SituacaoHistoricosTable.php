<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class SituacaoHistoricosTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('situacao_historicos');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('ProjetoBolsistas', [
            'foreignKey' => 'projeto_bolsista_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Usuarios', [
            'foreignKey' => 'usuario_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Editais', [
            'foreignKey' => 'editai_id',
            'joinType' => 'LEFT',
        ]);
        $this->belongsTo('FaseOriginal', [
            'className' => 'Fases',
            'foreignKey' => 'fase_original',
            'joinType' => 'LEFT',
            'propertyName' => 'fase_original_ref',
        ]);
        $this->belongsTo('FaseAtual', [
            'className' => 'Fases',
            'foreignKey' => 'fase_atual',
            'joinType' => 'LEFT',
            'propertyName' => 'fase_atual_ref',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->integer('projeto_bolsista_id')
            ->notEmptyString('projeto_bolsista_id');

        $validator
            ->integer('usuario_id')
            ->notEmptyString('usuario_id');

        $validator
            ->integer('editai_id')
            ->allowEmptyString('editai_id');

        $validator
            ->integer('fase_original')
            ->allowEmptyString('fase_original');

        $validator
            ->integer('fase_atual')
            ->allowEmptyString('fase_atual');

        $validator
            ->scalar('justificativa')
            ->maxLength('justificativa', 255)
            ->allowEmptyString('justificativa');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['projeto_bolsista_id'], 'ProjetoBolsistas'), ['errorField' => 'projeto_bolsista_id']);
        $rules->add($rules->existsIn(['usuario_id'], 'Usuarios'), ['errorField' => 'usuario_id']);
        $rules->add($rules->existsIn(['editai_id'], 'Editais'), ['errorField' => 'editai_id']);
        $rules->add($rules->existsIn(['fase_original'], 'FaseOriginal'), ['errorField' => 'fase_original']);
        $rules->add($rules->existsIn(['fase_atual'], 'FaseAtual'), ['errorField' => 'fase_atual']);

        return $rules;
    }
}
