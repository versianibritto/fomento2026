<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * EditaisWks Model
 *
 * @method \App\Model\Entity\EditaisWk newEmptyEntity()
 * @method \App\Model\Entity\EditaisWk newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\EditaisWk> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EditaisWk get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\EditaisWk findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\EditaisWk patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\EditaisWk> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EditaisWk|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\EditaisWk saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\EditaisWk>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\EditaisWk>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\EditaisWk>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\EditaisWk> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\EditaisWk>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\EditaisWk>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\EditaisWk>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\EditaisWk> deleteManyOrFail(iterable $entities, array $options = [])
 */
class EditaisWksTable extends Table
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

        $this->setTable('editais_wks');
        $this->setDisplayField('nome');
        $this->setPrimaryKey('int');

        $this->hasMany('EditaisPrazos', [
            'foreignKey' => 'editais_wk_id',
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

        return $validator;
    }
}
