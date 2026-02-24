<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * EditaisSumulas Model
 *
 * @property \App\Model\Table\EditaisTable&\Cake\ORM\Association\BelongsTo $Editais
 * @property \App\Model\Table\EditaisSumulasBlocosTable&\Cake\ORM\Association\BelongsTo $EditaisSumulasBlocos
 *
 * @method \App\Model\Entity\EditaisSumula newEmptyEntity()
 * @method \App\Model\Entity\EditaisSumula newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\EditaisSumula> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EditaisSumula get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\EditaisSumula findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\EditaisSumula patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\EditaisSumula> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EditaisSumula|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\EditaisSumula saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\EditaisSumula>|\Cake\ORM\ResultSetInterface<\App\Model\Entity\EditaisSumula> saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\EditaisSumula>|\Cake\ORM\ResultSetInterface<\App\Model\Entity\EditaisSumula> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\EditaisSumula>|\Cake\ORM\ResultSetInterface<\App\Model\Entity\EditaisSumula>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\EditaisSumula>|\Cake\ORM\ResultSetInterface<\App\Model\Entity\EditaisSumula> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class EditaisSumulasTable extends Table
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

        $this->setTable('editais_sumulas');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Editais', [
            'foreignKey' => 'editai_id',
        ]);
        $this->belongsTo('EditaisSumulasBlocos', [
            'foreignKey' => 'editais_sumula_bloco_id',
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
            ->nonNegativeInteger('editais_sumula_bloco_id')
            ->allowEmptyString('editais_sumula_bloco_id');

        $validator
            ->scalar('sumula')
            ->allowEmptyString('sumula');

        $validator
            ->scalar('parametro')
            ->allowEmptyString('parametro');

        $validator
            ->allowEmptyString('deleted');

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
        $rules->add($rules->existsIn(['editais_sumula_bloco_id'], 'EditaisSumulasBlocos'), ['errorField' => 'editais_sumula_bloco_id']);

        return $rules;
    }
}
