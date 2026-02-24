<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Manuais Model
 *
 * @property \App\Model\Table\UsuariosTable&\Cake\ORM\Association\BelongsTo $Usuarios
 *
 * @method \App\Model\Entity\Manuai newEmptyEntity()
 * @method \App\Model\Entity\Manuai newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Manuai> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Manuai get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Manuai findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Manuai patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Manuai> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Manuai|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Manuai saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Manuai>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Manuai>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Manuai>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Manuai> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Manuai>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Manuai>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Manuai>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Manuai> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ManuaisTable extends Table
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

        $this->setTable('manuais');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Usuarios', [
            'foreignKey' => 'usuario_id',
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
            ->scalar('arquivo')
            ->maxLength('arquivo', 45)
            ->allowEmptyString('arquivo');

        $validator
            ->integer('usuario_id')
            ->allowEmptyString('usuario_id');

        $validator
            ->dateTime('deleted')
            ->allowEmptyDateTime('deleted');

        $validator
            ->scalar('nome')
            ->maxLength('nome', 100)
            ->allowEmptyString('nome');

        $validator
            ->allowEmptyString('restrito');

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

        return $rules;
    }
}
