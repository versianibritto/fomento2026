<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Feedbacks Model
 *
 * @property \App\Model\Table\UsuariosTable&\Cake\ORM\Association\BelongsTo $Usuarios
 * @property \App\Model\Table\FeedbacksTable&\Cake\ORM\Association\BelongsTo $ParentFeedbacks
 * @property \App\Model\Table\FeedbacksTable&\Cake\ORM\Association\HasMany $ChildFeedbacks
 *
 * @method \App\Model\Entity\Feedback newEmptyEntity()
 * @method \App\Model\Entity\Feedback newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Feedback> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Feedback get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Feedback findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Feedback patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Feedback> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Feedback|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Feedback saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Feedback>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Feedback>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Feedback>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Feedback> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Feedback>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Feedback>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Feedback>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Feedback> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class FeedbacksTable extends Table
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

        $this->setTable('feedbacks');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Usuarios', [
            'foreignKey' => 'usuario_id',
        ]);
        $this->belongsTo('ParentFeedbacks', [
            'className' => 'Feedbacks',
            'foreignKey' => 'parent_id',
        ]);
        $this->hasMany('ChildFeedbacks', [
            'className' => 'Feedbacks',
            'foreignKey' => 'parent_id',
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
            ->scalar('tipo')
            ->allowEmptyString('tipo');

        $validator
            ->integer('usuario_id')
            ->allowEmptyString('usuario_id');

        $validator
            ->integer('ativo')
            ->allowEmptyString('ativo');

        $validator
            ->scalar('texto')
            ->allowEmptyString('texto');

        $validator
            ->scalar('destinatario')
            ->allowEmptyString('destinatario');

        $validator
            ->integer('parent_id')
            ->allowEmptyString('parent_id');

        $validator
            ->scalar('origem')
            ->allowEmptyString('origem');

        $validator
            ->scalar('titulo')
            ->maxLength('titulo', 200)
            ->allowEmptyString('titulo');

        $validator
            ->scalar('situacao')
            ->allowEmptyString('situacao');

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
        $rules->add($rules->existsIn(['usuario_id'], 'Usuarios'), ['errorField' => 'usuario_id']);
        $rules->add($rules->existsIn(['parent_id'], 'ParentFeedbacks'), ['errorField' => 'parent_id']);

        return $rules;
    }
}
