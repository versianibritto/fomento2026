<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Areas Model
 *
 * @property \App\Model\Table\GrandesAreasTable&\Cake\ORM\Association\BelongsTo $GrandesAreas
 * @property \App\Model\Table\AvaliadorsTable&\Cake\ORM\Association\HasMany $Avaliadors
 * @property \App\Model\Table\PdjInscricoesTable&\Cake\ORM\Association\HasMany $PdjInscricoes
 * @property \App\Model\Table\ProjetosTable&\Cake\ORM\Association\HasMany $Projetos
 * @property \App\Model\Table\SubAreasTable&\Cake\ORM\Association\HasMany $SubAreas
 *
 * @method \App\Model\Entity\Area newEmptyEntity()
 * @method \App\Model\Entity\Area newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Area> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Area get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Area findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Area patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Area> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Area|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Area saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Area>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Area>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Area>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Area> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Area>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Area>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Area>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Area> deleteManyOrFail(iterable $entities, array $options = [])
 */
class AreasTable extends Table
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

        $this->setTable('areas');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('GrandesAreas', [
            'foreignKey' => 'grandes_area_id',
        ]);
        $this->hasMany('Avaliadors', [
            'foreignKey' => 'area_id',
        ]);
        $this->hasMany('PdjInscricoes', [
            'foreignKey' => 'area_id',
        ]);
        $this->hasMany('Projetos', [
            'foreignKey' => 'area_id',
        ]);
        $this->hasMany('SubAreas', [
            'foreignKey' => 'area_id',
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
            ->scalar('codigo')
            ->maxLength('codigo', 25)
            ->allowEmptyString('codigo');

        $validator
            ->nonNegativeInteger('grandes_area_id')
            ->allowEmptyString('grandes_area_id');

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
        $rules->add($rules->existsIn(['grandes_area_id'], 'GrandesAreas'), ['errorField' => 'grandes_area_id']);

        return $rules;
    }
}
