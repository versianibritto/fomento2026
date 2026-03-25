<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * SubAreas Model
 *
 * @property \App\Model\Table\AreasTable&\Cake\ORM\Association\BelongsTo $Areas
 * @property \App\Model\Table\EspecialidadesTable&\Cake\ORM\Association\HasMany $Especialidades
 *
 * @method \App\Model\Entity\SubArea newEmptyEntity()
 * @method \App\Model\Entity\SubArea newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\SubArea[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\SubArea get($primaryKey, $options = [])
 * @method \App\Model\Entity\SubArea findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\SubArea patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\SubArea[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\SubArea|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SubArea saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\SubArea[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\SubArea[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\SubArea[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\SubArea[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class SubAreasTable extends Table
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

        $this->setTable('sub_areas');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Areas', [
            'foreignKey' => 'area_id',
        ]);
        $this->hasMany('Especialidades', [
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
        $rules->add($rules->existsIn('area_id', 'Areas'), ['errorField' => 'area_id']);

        return $rules;
    }
}
