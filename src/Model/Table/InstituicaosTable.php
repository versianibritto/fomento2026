<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Instituicaos Model
 *
 * @property \App\Model\Table\TalentosCursosTable&\Cake\ORM\Association\HasMany $TalentosCursos
 * @property \App\Model\Table\TalentosEventosTable&\Cake\ORM\Association\HasMany $TalentosEventos
 * @property \App\Model\Table\UnidadesTable&\Cake\ORM\Association\HasMany $Unidades
 *
 * @method \App\Model\Entity\Instituicao newEmptyEntity()
 * @method \App\Model\Entity\Instituicao newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Instituicao> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Instituicao get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Instituicao findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Instituicao patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Instituicao> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Instituicao|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Instituicao saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Instituicao>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Instituicao>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Instituicao>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Instituicao> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Instituicao>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Instituicao>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Instituicao>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Instituicao> deleteManyOrFail(iterable $entities, array $options = [])
 */
class InstituicaosTable extends Table
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

        $this->setTable('instituicaos');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->hasMany('TalentosCursos', [
            'foreignKey' => 'instituicao_id',
        ]);
        $this->hasMany('TalentosEventos', [
            'foreignKey' => 'instituicao_id',
        ]);
        $this->hasMany('Unidades', [
            'foreignKey' => 'instituicao_id',
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
            ->maxLength('nome', 255)
            ->allowEmptyString('nome');

        $validator
            ->scalar('sigla')
            ->maxLength('sigla', 45)
            ->allowEmptyString('sigla');

        return $validator;
    }
}
