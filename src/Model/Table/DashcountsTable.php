<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Dashcounts Model
 *
 * @property \App\Model\Table\FasesTable&\Cake\ORM\Association\BelongsTo $Fases
 *
 * @method \App\Model\Entity\Dashcount newEmptyEntity()
 * @method \App\Model\Entity\Dashcount newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Dashcount> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Dashcount get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Dashcount findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Dashcount patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Dashcount> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Dashcount|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Dashcount saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Dashcount>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Dashcount>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Dashcount>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Dashcount> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Dashcount>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Dashcount>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Dashcount>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Dashcount> deleteManyOrFail(iterable $entities, array $options = [])
 */
class DashcountsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('dashcounts');

        $this->belongsTo('Fases', [
            'foreignKey' => 'fase_id',
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
            ->notEmptyString('qtd');

        $validator
            ->allowEmptyString('bolsista');

        $validator
            ->allowEmptyString('orientador');

        $validator
            ->allowEmptyString('coorientador');

        $validator
            ->integer('fase_id')
            ->allowEmptyString('fase_id');

        $validator
            ->scalar('bloco')
            ->maxLength('bloco', 1)
            ->allowEmptyString('bloco');

        $validator
            ->allowEmptyString('vigente');

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
        $rules->add($rules->existsIn(['fase_id'], 'Fases'), ['errorField' => 'fase_id']);

        return $rules;
    }
}
