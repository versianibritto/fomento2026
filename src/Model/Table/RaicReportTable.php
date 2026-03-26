<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * RaicReport Model
 *
 * @property \App\Model\Table\RaicsTable&\Cake\ORM\Association\BelongsTo $Raics
 *
 * @method \App\Model\Entity\RaicReport newEmptyEntity()
 * @method \App\Model\Entity\RaicReport newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\RaicReport[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RaicReport get($primaryKey, $options = [])
 * @method \App\Model\Entity\RaicReport findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\RaicReport patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RaicReport[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RaicReport|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RaicReport saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RaicReport[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RaicReport[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\RaicReport[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RaicReport[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class RaicReportTable extends Table
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

        $this->setTable('raic_report');

        $this->belongsTo('Raics', [
            'foreignKey' => 'raic_id',
            'joinType' => 'INNER',
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
            ->integer('raic_projeto_bolsista_id')
            ->allowEmptyString('raic_projeto_bolsista_id');

        $validator
            ->scalar('pb_situacao')
            ->allowEmptyString('pb_situacao');

        $validator
            ->integer('raic_usuario_id')
            ->allowEmptyString('raic_usuario_id');

        $validator
            ->scalar('nome_bolsista')
            ->maxLength('nome_bolsista', 150)
            ->allowEmptyString('nome_bolsista');

        $validator
            ->scalar('email_bolsista')
            ->maxLength('email_bolsista', 100)
            ->allowEmptyString('email_bolsista');

        $validator
            ->scalar('email_alternativo_bolsista')
            ->maxLength('email_alternativo_bolsista', 100)
            ->allowEmptyString('email_alternativo_bolsista');

        $validator
            ->integer('raic_orientador_id')
            ->allowEmptyString('raic_orientador_id');

        $validator
            ->scalar('nome_orientador')
            ->maxLength('nome_orientador', 150)
            ->allowEmptyString('nome_orientador');

        $validator
            ->scalar('unidade_orientador')
            ->maxLength('unidade_orientador', 45)
            ->allowEmptyString('unidade_orientador');

        $validator
            ->scalar('email_orientador')
            ->maxLength('email_orientador', 100)
            ->allowEmptyString('email_orientador');

        $validator
            ->scalar('email_alternativo_orientador')
            ->maxLength('email_alternativo_orientador', 100)
            ->allowEmptyString('email_alternativo_orientador');

        $validator
            ->nonNegativeInteger('pb_projeto_id')
            ->allowEmptyString('pb_projeto_id');

        $validator
            ->nonNegativeInteger('raic_id')
            ->notEmptyString('raic_id');

        $validator
            ->date('raic_data_apresentacao')
            ->allowEmptyDate('raic_data_apresentacao');

        $validator
            ->scalar('raic_tipo_bolsa')
            ->allowEmptyString('raic_tipo_bolsa');

        $validator
            ->integer('raic_unidade_id')
            ->allowEmptyString('raic_unidade_id');

        $validator
            ->scalar('raic_unidade')
            ->maxLength('raic_unidade', 45)
            ->allowEmptyString('raic_unidade');

        $validator
            ->nonNegativeInteger('raic_orientador_unidade_id')
            ->allowEmptyString('raic_orientador_unidade_id');

        $validator
            ->dateTime('raic_created')
            ->allowEmptyDateTime('raic_created');

        $validator
            ->dateTime('pb_created')
            ->allowEmptyDateTime('pb_created');

        $validator
            ->integer('raic_editai_id')
            ->allowEmptyString('raic_editai_id');

        $validator
            ->scalar('raic_edital')
            ->maxLength('raic_edital', 45)
            ->allowEmptyString('raic_edital');

        $validator
            ->scalar('edital_tipo')
            ->allowEmptyString('edital_tipo');

        $validator
            ->integer('pb_deleted')
            ->allowEmptyString('pb_deleted');

        $validator
            ->integer('raic_deleted')
            ->notEmptyString('raic_deleted');

        $validator
            ->scalar('certificado')
            ->allowEmptyString('certificado');

        $validator
            ->scalar('liberado_por')
            ->maxLength('liberado_por', 150)
            ->allowEmptyString('liberado_por');

        $validator
            ->dateTime('data_liberacao')
            ->allowEmptyDateTime('data_liberacao');

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
        $rules->add($rules->existsIn('raic_id', 'Raics'), ['errorField' => 'raic_id']);

        return $rules;
    }
}
