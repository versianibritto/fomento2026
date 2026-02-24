<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Bancas Model
 *
 * @property \App\Model\Table\GrandesAreasTable&\Cake\ORM\Association\BelongsTo $GrandesAreas
 * @property \App\Model\Table\EditaisTable&\Cake\ORM\Association\BelongsTo $Editais
 * @property \App\Model\Table\AvaliadorBolsistasTable&\Cake\ORM\Association\HasMany $AvaliadorBolsistas
 * @property \App\Model\Table\BancaUsuariosTable&\Cake\ORM\Association\HasMany $BancaUsuarios
 *
 * @method \App\Model\Entity\Banca newEmptyEntity()
 * @method \App\Model\Entity\Banca newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Banca> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Banca get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Banca findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Banca patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Banca> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Banca|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Banca saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Banca>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Banca>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Banca>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Banca> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Banca>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Banca>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Banca>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Banca> deleteManyOrFail(iterable $entities, array $options = [])
 */
class BancasTable extends Table
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

        $this->setTable('bancas');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('GrandesAreas', [
            'foreignKey' => 'grandes_areas_id',
        ]);
        $this->belongsTo('Editais', [
            'foreignKey' => 'editai_id',
        ]);
        $this->hasMany('AvaliadorBolsistas', [
            'foreignKey' => 'banca_id',
        ]);
        $this->hasMany('BancaUsuarios', [
            'foreignKey' => 'banca_id',
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
            ->maxLength('nome', 150)
            ->allowEmptyString('nome');

        $validator
            ->date('data')
            ->allowEmptyDate('data');

        $validator
            ->scalar('periodo')
            ->allowEmptyString('periodo');

        $validator
            ->dateTime('deleted')
            ->allowEmptyDateTime('deleted');

        $validator
            ->integer('grandes_areas_id')
            ->allowEmptyString('grandes_areas_id');

        $validator
            ->integer('editai_id')
            ->allowEmptyString('editai_id');

        $validator
            ->integer('evento')
            ->allowEmptyString('evento');

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
        $rules->add($rules->existsIn(['grandes_areas_id'], 'GrandesAreas'), ['errorField' => 'grandes_areas_id']);
        $rules->add($rules->existsIn(['editai_id'], 'Editais'), ['errorField' => 'editai_id']);

        return $rules;
    }
}
