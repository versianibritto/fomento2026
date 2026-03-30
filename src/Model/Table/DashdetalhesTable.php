<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Dashdetalhes Model
 *
 * @property \App\Model\Table\ProjetosTable&\Cake\ORM\Association\BelongsTo $Projetos
 * @property \App\Model\Table\FasesTable&\Cake\ORM\Association\BelongsTo $Fases
 * @property \App\Model\Table\EditaisTable&\Cake\ORM\Association\BelongsTo $Editais
 *
 * @method \App\Model\Entity\Dashdetalhe newEmptyEntity()
 * @method \App\Model\Entity\Dashdetalhe newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Dashdetalhe> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Dashdetalhe get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Dashdetalhe findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Dashdetalhe patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Dashdetalhe> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Dashdetalhe|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Dashdetalhe saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Dashdetalhe>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Dashdetalhe>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Dashdetalhe>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Dashdetalhe> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Dashdetalhe>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Dashdetalhe>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Dashdetalhe>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Dashdetalhe> deleteManyOrFail(iterable $entities, array $options = [])
 */
class DashdetalhesTable extends Table
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

        $this->setTable('dashdetalhes');

        $this->belongsTo('Projetos', [
            'foreignKey' => 'projeto_id',
        ]);
        $this->belongsTo('Fases', [
            'foreignKey' => 'fase_id',
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
            ->nonNegativeInteger('id')
            ->notEmptyString('id');

        $validator
            ->allowEmptyString('bolsista');

        $validator
            ->scalar('nome_bolsista')
            ->maxLength('nome_bolsista', 150)
            ->allowEmptyString('nome_bolsista');

        $validator
            ->allowEmptyString('orientador');

        $validator
            ->scalar('nome_orientador')
            ->maxLength('nome_orientador', 150)
            ->allowEmptyString('nome_orientador');

        $validator
            ->allowEmptyString('coorientador');

        $validator
            ->scalar('nome_coorientador')
            ->maxLength('nome_coorientador', 150)
            ->allowEmptyString('nome_coorientador');

        $validator
            ->allowEmptyString('projeto_id');

        $validator
            ->integer('fase_id')
            ->allowEmptyString('fase_id');

        $validator
            ->scalar('nome_fase')
            ->maxLength('nome_fase', 45)
            ->allowEmptyString('nome_fase');

        $validator
            ->scalar('bloco')
            ->maxLength('bloco', 1)
            ->allowEmptyString('bloco');

        $validator
            ->allowEmptyString('vigente');

        $validator
            ->scalar('origem')
            ->maxLength('origem', 1)
            ->allowEmptyString('origem');

        $validator
            ->allowEmptyString('editai_id');

        $validator
            ->scalar('nome_edital')
            ->maxLength('nome_edital', 45)
            ->allowEmptyString('nome_edital');

        $validator
            ->scalar('controller')
            ->maxLength('controller', 45)
            ->allowEmptyString('controller');

        $validator
            ->scalar('nome_programa')
            ->maxLength('nome_programa', 45)
            ->allowEmptyString('nome_programa');

        $validator
            ->dateTime('data_inicio')
            ->allowEmptyDateTime('data_inicio');

        $validator
            ->dateTime('data_fim')
            ->allowEmptyDateTime('data_fim');

        $validator
            ->integer('programa_id')
            ->allowEmptyString('programa_id');

        $validator
            ->dateTime('fim_vigencia')
            ->allowEmptyDateTime('fim_vigencia');

        $validator
            ->notEmptyString('ativo');

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
        $rules->add($rules->existsIn(['projeto_id'], 'Projetos'), ['errorField' => 'projeto_id']);
        $rules->add($rules->existsIn(['fase_id'], 'Fases'), ['errorField' => 'fase_id']);
        $rules->add($rules->existsIn(['editai_id'], 'Editais'), ['errorField' => 'editai_id']);

        return $rules;
    }
}
