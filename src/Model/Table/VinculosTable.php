<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Vinculos Model
 *
 * @property \App\Model\Table\UsuarioHistoricosTable&\Cake\ORM\Association\HasMany $UsuarioHistoricos
 * @property \App\Model\Table\UsuariosTable&\Cake\ORM\Association\HasMany $Usuarios
 *
 * @method \App\Model\Entity\Vinculo newEmptyEntity()
 * @method \App\Model\Entity\Vinculo newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Vinculo> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Vinculo get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Vinculo findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Vinculo patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Vinculo> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Vinculo|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Vinculo saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Vinculo>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Vinculo>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Vinculo>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Vinculo> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Vinculo>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Vinculo>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Vinculo>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Vinculo> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class VinculosTable extends Table
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

        $this->setTable('vinculos');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('UsuarioHistoricos', [
            'foreignKey' => 'vinculo_id',
        ]);
        $this->hasMany('Usuarios', [
            'foreignKey' => 'vinculo_id',
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
            ->allowEmptyString('servidor');

        $validator
            ->allowEmptyString('deleted');

        return $validator;
    }
}
