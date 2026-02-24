<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Revistas Model
 *
 * @property \App\Model\Table\ProjetoBolsistasTable&\Cake\ORM\Association\HasMany $ProjetoBolsistas
 *
 * @method \App\Model\Entity\Revista newEmptyEntity()
 * @method \App\Model\Entity\Revista newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Revista[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Revista get($primaryKey, $options = [])
 * @method \App\Model\Entity\Revista findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Revista patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Revista[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Revista|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Revista saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Revista[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Revista[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Revista[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Revista[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RevistasTable extends Table
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

        $this->setTable('revistas');
        $this->setDisplayField('nome');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('ProjetoBolsistas', [
            'foreignKey' => 'revista_id',
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
            ->maxLength('nome', 255)
            ->allowEmptyString('nome');

        $validator
            ->scalar('edicao')
            ->maxLength('edicao', 45)
            ->allowEmptyString('edicao');

        $validator
            ->integer('ano')
            ->allowEmptyString('ano');

        $validator
            ->integer('deleted')
            ->allowEmptyString('deleted');

        $validator
            ->dateTime('data_publicacao')
            ->allowEmptyDateTime('data_publicacao');

        $validator
            ->scalar('texto')
            ->maxLength('texto', 4294967295)
            ->allowEmptyString('texto');

        return $validator;
    }
}
