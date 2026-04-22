<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Dashcountavaliadores Model
 *
 * @method \App\Model\Entity\Dashcountavaliadore newEmptyEntity()
 * @method \App\Model\Entity\Dashcountavaliadore newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Dashcountavaliadore> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Dashcountavaliadore get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Dashcountavaliadore findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Dashcountavaliadore patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Dashcountavaliadore> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Dashcountavaliadore|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Dashcountavaliadore saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Dashcountavaliadore>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Dashcountavaliadore>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Dashcountavaliadore>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Dashcountavaliadore> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Dashcountavaliadore>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Dashcountavaliadore>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Dashcountavaliadore>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Dashcountavaliadore> deleteManyOrFail(iterable $entities, array $options = [])
 */
class DashcountavaliadoresTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('dashcountavaliadores');
        $this->setDisplayField('usuario_nome');
        $this->setPrimaryKey(['usuario_id', 'ano', 'situacao', 'deleted']);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->allowEmptyString('usuario_id');

        $validator
            ->scalar('usuario_nome')
            ->allowEmptyString('usuario_nome');

        $validator
            ->scalar('ano')
            ->maxLength('ano', 4)
            ->allowEmptyString('ano');

        $validator
            ->scalar('situacao')
            ->maxLength('situacao', 1)
            ->allowEmptyString('situacao');

        $validator
            ->allowEmptyString('deleted');

        $validator
            ->nonNegativeInteger('qtd_inscricoes')
            ->allowEmptyString('qtd_inscricoes');

        return $validator;
    }
}
