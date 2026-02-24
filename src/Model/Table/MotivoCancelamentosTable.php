<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MotivoCancelamentos Model
 *
 * @property \App\Model\Table\ProjetoBolsistasTable&\Cake\ORM\Association\HasMany $ProjetoBolsistas
 *
 * @method \App\Model\Entity\MotivoCancelamento newEmptyEntity()
 * @method \App\Model\Entity\MotivoCancelamento newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\MotivoCancelamento[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MotivoCancelamento get($primaryKey, $options = [])
 * @method \App\Model\Entity\MotivoCancelamento findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\MotivoCancelamento patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MotivoCancelamento[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MotivoCancelamento|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MotivoCancelamento saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MotivoCancelamento[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\MotivoCancelamento[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\MotivoCancelamento[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\MotivoCancelamento[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class MotivoCancelamentosTable extends Table
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

        $this->setTable('motivo_cancelamentos');
        $this->setDisplayField('nome');
        $this->setPrimaryKey('id');

        $this->hasMany('ProjetoBolsistas', [
            'foreignKey' => 'motivo_cancelamento_id',
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
            ->maxLength('nome', 155)
            ->allowEmptyString('nome');

        return $validator;
    }
}
