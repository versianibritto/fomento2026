<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * EditaisSumulasBlocos Model
 *
 * @property \App\Model\Table\EditaisSumulasTable&\Cake\ORM\Association\HasMany $EditaisSumulas
 * @method \App\Model\Entity\EditaisSumulasBloco newEmptyEntity()
 * @method \App\Model\Entity\EditaisSumulasBloco newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\EditaisSumulasBloco> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EditaisSumulasBloco get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\EditaisSumulasBloco findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\EditaisSumulasBloco patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\EditaisSumulasBloco> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EditaisSumulasBloco|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\EditaisSumulasBloco saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\EditaisSumulasBloco>|\Cake\ORM\ResultSetInterface<\App\Model\Entity\EditaisSumulasBloco> saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\EditaisSumulasBloco>|\Cake\ORM\ResultSetInterface<\App\Model\Entity\EditaisSumulasBloco> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\EditaisSumulasBloco>|\Cake\ORM\ResultSetInterface<\App\Model\Entity\EditaisSumulasBloco>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\EditaisSumulasBloco>|\Cake\ORM\ResultSetInterface<\App\Model\Entity\EditaisSumulasBloco> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class EditaisSumulasBlocosTable extends Table
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

        $this->setTable('editais_sumulas_blocos');
        $this->setDisplayField('nome');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('EditaisSumulas', [
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
            ->scalar('nome')
            ->maxLength('nome', 45)
            ->allowEmptyString('nome');

        $validator
            ->allowEmptyString('deleted');

        return $validator;
    }
}
