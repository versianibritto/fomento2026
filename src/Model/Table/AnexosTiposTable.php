<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * AnexosTipos Model
 *
 * @property \App\Model\Table\AnexosTable&\Cake\ORM\Association\HasMany $Anexos
 *
 * @method \App\Model\Entity\AnexosTipo newEmptyEntity()
 * @method \App\Model\Entity\AnexosTipo newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\AnexosTipo> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AnexosTipo get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\AnexosTipo findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\AnexosTipo patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\AnexosTipo> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AnexosTipo|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\AnexosTipo saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\AnexosTipo>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\AnexosTipo>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\AnexosTipo>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\AnexosTipo> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\AnexosTipo>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\AnexosTipo>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\AnexosTipo>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\AnexosTipo> deleteManyOrFail(iterable $entities, array $options = [])
 */
class AnexosTiposTable extends Table
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

        $this->setTable('anexos_tipos');
        $this->setDisplayField('nome');
        $this->setPrimaryKey('id');

        $this->hasMany('Anexos', [
            'foreignKey' => 'anexos_tipo_id',
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
            ->requirePresence('nome', 'create')
            ->notEmptyString('nome');

        $validator
            ->scalar('bloco')
            ->allowEmptyString('bloco');

        $validator
            ->allowEmptyString('deleted');

        $validator
            ->integer('condicional')
            ->allowEmptyString('condicional');

        $validator
            ->scalar('programa')
            ->maxLength('programa', 45)
            ->allowEmptyString('programa');

        $validator
            ->scalar('cota')
            ->maxLength('cota', 45)
            ->allowEmptyString('cota');

        return $validator;
    }
}
