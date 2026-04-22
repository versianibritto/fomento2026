<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Dashyodacounts Model
 *
 * @property \App\Model\Table\FasesTable&\Cake\ORM\Association\BelongsTo $Fases
 * @property \App\Model\Table\ProgramasTable&\Cake\ORM\Association\BelongsTo $Programas
 * @property \App\Model\Table\EditaisTable&\Cake\ORM\Association\BelongsTo $Editais
 *
 * @method \App\Model\Entity\Dashyodacount newEmptyEntity()
 * @method \App\Model\Entity\Dashyodacount newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Dashyodacount> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Dashyodacount get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Dashyodacount findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Dashyodacount patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Dashyodacount> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Dashyodacount|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Dashyodacount saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Dashyodacount>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Dashyodacount>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Dashyodacount>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Dashyodacount> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Dashyodacount>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Dashyodacount>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Dashyodacount>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Dashyodacount> deleteManyOrFail(iterable $entities, array $options = [])
 */
class DashyodacountsTable extends Table
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

        $this->setTable('dashyodacounts');

        $this->belongsTo('Fases', [
            'foreignKey' => 'fase_id',
        ]);
        $this->belongsTo('Programas', [
            'foreignKey' => 'programa_id',
        ]);
        $this->belongsTo('Editais', [
            'foreignKey' => 'editai_id',
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
            ->notEmptyString('qtd');

        $validator
            ->integer('fase_id')
            ->allowEmptyString('fase_id');

        $validator
            ->scalar('bloco')
            ->maxLength('bloco', 1)
            ->allowEmptyString('bloco');

        $validator
            ->allowEmptyString('vigente');

        $validator
            ->integer('programa_id')
            ->allowEmptyString('programa_id');

        $validator
            ->allowEmptyString('editai_id');

        $validator
            ->dateTime('inicio_vigencia')
            ->allowEmptyDateTime('inicio_vigencia');

        $validator
            ->dateTime('fim_vigencia')
            ->allowEmptyDateTime('fim_vigencia');

        $validator
            ->scalar('homologado')
            ->maxLength('homologado', 1)
            ->allowEmptyString('homologado');

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
        $rules->add($rules->existsIn(['fase_id'], 'Fases'), ['errorField' => 'fase_id']);
        $rules->add($rules->existsIn(['programa_id'], 'Programas'), ['errorField' => 'programa_id']);
        $rules->add($rules->existsIn(['editai_id'], 'Editais'), ['errorField' => 'editai_id']);

        return $rules;
    }
}
