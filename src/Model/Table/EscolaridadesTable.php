<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Escolaridades Model
 *
 * @property \App\Model\Table\DiplomasTable&\Cake\ORM\Association\HasMany $Diplomas
 * @property \App\Model\Table\UsuarioHistoricosTable&\Cake\ORM\Association\HasMany $UsuarioHistoricos
 * @property \App\Model\Table\UsuariosTable&\Cake\ORM\Association\HasMany $Usuarios
 *
 * @method \App\Model\Entity\Escolaridade newEmptyEntity()
 * @method \App\Model\Entity\Escolaridade newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Escolaridade> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Escolaridade get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Escolaridade findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Escolaridade patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Escolaridade> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Escolaridade|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Escolaridade saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Escolaridade>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Escolaridade>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Escolaridade>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Escolaridade> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Escolaridade>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Escolaridade>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Escolaridade>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Escolaridade> deleteManyOrFail(iterable $entities, array $options = [])
 */
class EscolaridadesTable extends Table
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

        $this->setTable('escolaridades');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->hasMany('Diplomas', [
            'foreignKey' => 'escolaridade_id',
        ]);
        $this->hasMany('UsuarioHistoricos', [
            'foreignKey' => 'escolaridade_id',
        ]);
        $this->hasMany('Usuarios', [
            'foreignKey' => 'escolaridade_id',
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
