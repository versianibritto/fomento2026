<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * GrandesAreas Model
 *
 * @property \App\Model\Table\AreasTable&\Cake\ORM\Association\HasMany $Areas
 *
 * @method \App\Model\Entity\GrandesArea newEmptyEntity()
 * @method \App\Model\Entity\GrandesArea newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\GrandesArea[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\GrandesArea get($primaryKey, $options = [])
 * @method \App\Model\Entity\GrandesArea findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\GrandesArea patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\GrandesArea[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\GrandesArea|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\GrandesArea saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\GrandesArea[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\GrandesArea[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\GrandesArea[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\GrandesArea[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class GrandesAreasTable extends Table
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

        $this->setTable('grandes_areas');
        $this->setDisplayField('nome');
        $this->setPrimaryKey('id');

        $this->hasMany('Areas', [
            'foreignKey' => 'grandes_area_id',
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
            ->maxLength('nome', 45)
            ->allowEmptyString('nome');

        $validator
            ->scalar('codigo')
            ->maxLength('codigo', 25)
            ->allowEmptyString('codigo');

        return $validator;
    }
}
