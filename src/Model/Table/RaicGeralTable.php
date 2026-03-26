<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

/**
 * RaicGeral Model
 *
 * @method \App\Model\Entity\RaicGeral newEmptyEntity()
 * @method \App\Model\Entity\RaicGeral newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\RaicGeral> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RaicGeral get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\RaicGeral findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\RaicGeral patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\RaicGeral> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RaicGeral|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\RaicGeral saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\RaicGeral>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RaicGeral>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\RaicGeral>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RaicGeral> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\RaicGeral>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RaicGeral>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\RaicGeral>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RaicGeral> deleteManyOrFail(iterable $entities, array $options = [])
 */
class RaicGeralTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('raic_geral');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
    }
}
