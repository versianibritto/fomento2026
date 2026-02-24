<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Erratas Model
 *
 * @property \App\Model\Table\EditaisTable&\Cake\ORM\Association\BelongsTo $Editais
 *
 * @method \App\Model\Entity\Errata newEmptyEntity()
 * @method \App\Model\Entity\Errata newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Errata> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Errata get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Errata findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Errata patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Errata> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Errata|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Errata saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Errata>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Errata>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Errata>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Errata> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Errata>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Errata>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Errata>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Errata> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ErratasTable extends Table
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

        $this->setTable('erratas');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Editais', [
            'foreignKey' => 'editai_id',
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
            ->nonNegativeInteger('editai_id')
            ->allowEmptyString('editai_id');

        $validator
            ->scalar('introducao')
            ->maxLength('introducao', 4294967295)
            ->allowEmptyString('introducao');

        $validator
            ->scalar('onde_le')
            ->maxLength('onde_le', 4294967295)
            ->allowEmptyString('onde_le');

        $validator
            ->scalar('leia_se')
            ->maxLength('leia_se', 4294967295)
            ->allowEmptyString('leia_se');

        $validator
            ->scalar('arquivo')
            ->maxLength('arquivo', 45)
            ->allowEmptyString('arquivo');

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
        $rules->add($rules->existsIn(['editai_id'], 'Editais'), ['errorField' => 'editai_id']);

        return $rules;
    }
}
