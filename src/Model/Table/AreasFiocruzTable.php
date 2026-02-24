<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * AreasFiocruz Model
 *
 * @method \App\Model\Entity\AreasFiocruz newEmptyEntity()
 * @method \App\Model\Entity\AreasFiocruz newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\AreasFiocruz[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AreasFiocruz get($primaryKey, $options = [])
 * @method \App\Model\Entity\AreasFiocruz findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\AreasFiocruz patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AreasFiocruz[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AreasFiocruz|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AreasFiocruz saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AreasFiocruz[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AreasFiocruz[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\AreasFiocruz[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AreasFiocruz[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class AreasFiocruzTable extends Table
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

        $this->setTable('areas_fiocruz');
        $this->setDisplayField('nome');
        $this->setPrimaryKey('id');

        $this->hasMany('Linhas', [
            'foreignKey' => 'areas_fiocruz_id',
            'joinType' => 'LEFT',

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
            ->maxLength('nome', 250)
            ->allowEmptyString('nome');

        return $validator;
    }
}
