<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Questions Model
 *
 * @property \App\Model\Table\EditaisTable&\Cake\ORM\Association\BelongsTo $Editais
 * @property \App\Model\Table\AvaliationsTable&\Cake\ORM\Association\HasMany $Avaliations
 *
 * @method \App\Model\Entity\Question newEmptyEntity()
 * @method \App\Model\Entity\Question newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Question> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Question get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Question findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Question patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Question> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Question|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Question saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Question>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Question>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Question>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Question> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Question>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Question>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Question>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Question> deleteManyOrFail(iterable $entities, array $options = [])
 */
class QuestionsTable extends Table
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

        $this->setTable('questions');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Editais', [
            'foreignKey' => 'editai_id',
        ]);
        $this->hasMany('Avaliations', [
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
            ->scalar('ano')
            ->maxLength('ano', 5)
            ->allowEmptyString('ano');

        $validator
            ->scalar('tipo')
            ->allowEmptyString('tipo');

        $validator
            ->scalar('questao')
            ->maxLength('questao', 255)
            ->allowEmptyString('questao');

        $validator
            ->numeric('limite_min')
            ->greaterThanOrEqual('limite_min', 0)
            ->allowEmptyString('limite_min');

        $validator
            ->numeric('limite_max')
            ->greaterThanOrEqual('limite_max', 0)
            ->allowEmptyString('limite_max');

        $validator
            ->scalar('prametros')
            ->allowEmptyString('prametros');

        $validator
            ->integer('editai_id')
            ->allowEmptyString('editai_id');

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
        $rules->add($rules->existsIn(['editai_id'], 'Editais'), ['errorField' => 'editai_id']);

        return $rules;
    }
}
