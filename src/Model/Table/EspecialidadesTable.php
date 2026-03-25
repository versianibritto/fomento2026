<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Especialidades Model
 *
 * @property \App\Model\Table\SubAreasTable&\Cake\ORM\Association\BelongsTo $SubAreas
 *
 * @method \App\Model\Entity\Especialidade newEmptyEntity()
 * @method \App\Model\Entity\Especialidade newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Especialidade[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Especialidade get($primaryKey, $options = [])
 * @method \App\Model\Entity\Especialidade findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Especialidade patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Especialidade[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Especialidade|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Especialidade saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Especialidade[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Especialidade[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Especialidade[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Especialidade[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class EspecialidadesTable extends Table
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

        $this->setTable('especialidades');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('SubAreas', [
            'foreignKey' => 'sub_area_id',
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
            ->scalar('nome')
            ->maxLength('nome', 150)
            ->allowEmptyString('nome');

        $validator
            ->scalar('codigo')
            ->maxLength('codigo', 25)
            ->allowEmptyString('codigo');

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
        $rules->add($rules->existsIn('sub_area_id', 'SubAreas'), ['errorField' => 'sub_area_id']);

        return $rules;
    }
}
