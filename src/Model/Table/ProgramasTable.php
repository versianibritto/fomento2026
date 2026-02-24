<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Programas Model
 *
 * @property \App\Model\Table\DashyodacountsTable&\Cake\ORM\Association\HasMany $Dashyodacounts
 * @property \App\Model\Table\EditaisTable&\Cake\ORM\Association\HasMany $Editais
 *
 * @method \App\Model\Entity\Programa newEmptyEntity()
 * @method \App\Model\Entity\Programa newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Programa> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Programa get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Programa findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Programa patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Programa> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Programa|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Programa saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Programa>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Programa>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Programa>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Programa> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Programa>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Programa>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Programa>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Programa> deleteManyOrFail(iterable $entities, array $options = [])
 */
class ProgramasTable extends Table
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

        $this->setTable('programas');
        $this->setDisplayField('sigla');
        $this->setPrimaryKey('id');

        $this->hasMany('Dashyodacounts', [
            'foreignKey' => 'programa_id',
        ]);
        $this->hasMany('Editais', [
            'foreignKey' => 'programa_id',
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
            ->scalar('sigla')
            ->maxLength('sigla', 45)
            ->allowEmptyString('sigla');

        $validator
            ->scalar('nome')
            ->maxLength('nome', 300)
            ->allowEmptyString('nome');

        $validator
            ->scalar('letra')
            ->allowEmptyString('letra');

        $validator
            ->integer('deleted')
            ->allowEmptyString('deleted');

        return $validator;
    }
}
