<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Avaliations Model
 *
 * @property \App\Model\Table\AvaliadorBolsistasTable&\Cake\ORM\Association\BelongsTo $AvaliadorBolsistas
 * @property \App\Model\Table\QuestionsTable&\Cake\ORM\Association\BelongsTo $Questions
 *
 * @method \App\Model\Entity\Avaliation newEmptyEntity()
 * @method \App\Model\Entity\Avaliation newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Avaliation> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Avaliation get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Avaliation findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Avaliation patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Avaliation> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Avaliation|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Avaliation saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Avaliation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Avaliation>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Avaliation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Avaliation> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Avaliation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Avaliation>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Avaliation>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Avaliation> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AvaliationsTable extends Table
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

        $this->setTable('avaliations');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('AvaliadorBolsistas', [
            'foreignKey' => 'avaliador_bolsista_id',
        ]);
        $this->belongsTo('Questions', [
            'foreignKey' => 'question_id',
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
            ->nonNegativeInteger('avaliador_bolsista_id')
            ->allowEmptyString('avaliador_bolsista_id');

        $validator
            ->nonNegativeInteger('question_id')
            ->allowEmptyString('question_id');

        $validator
            ->numeric('nota')
            ->greaterThanOrEqual('nota', 0)
            ->allowEmptyString('nota');

        $validator
            ->scalar('observacao_avaliador')
            ->allowEmptyString('observacao_avaliador');

        $validator
            ->scalar('parecer')
            ->allowEmptyString('parecer');

        $validator
            ->integer('deleted')
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
        $rules->add($rules->existsIn(['avaliador_bolsista_id'], 'AvaliadorBolsistas'), ['errorField' => 'avaliador_bolsista_id']);
        $rules->add($rules->existsIn(['question_id'], 'Questions'), ['errorField' => 'question_id']);

        return $rules;
    }
}
