<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * AvaliationsSumulas Model
 *
 * @property \App\Model\Table\AvaliadorBolsistasTable&\Cake\ORM\Association\BelongsTo $AvaliadorBolsistas
 * @property \App\Model\Table\EditaisSumulasTable&\Cake\ORM\Association\BelongsTo $EditaisSumulas
 * @property \App\Model\Table\EditaisSumulasBlocosTable&\Cake\ORM\Association\BelongsTo $EditaisSumulasBlocos
 * @property \App\Model\Table\InscricaoSumulasTable&\Cake\ORM\Association\BelongsTo $InscricaoSumulas
 *
 * @method \App\Model\Entity\AvaliationsSumula newEmptyEntity()
 * @method \App\Model\Entity\AvaliationsSumula newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\AvaliationsSumula> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AvaliationsSumula get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\AvaliationsSumula findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\AvaliationsSumula patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\AvaliationsSumula> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AvaliationsSumula|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\AvaliationsSumula saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\AvaliationsSumula>|\Cake\ORM\ResultSetInterface<\App\Model\Entity\AvaliationsSumula> saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\AvaliationsSumula>|\Cake\ORM\ResultSetInterface<\App\Model\Entity\AvaliationsSumula> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\AvaliationsSumula>|\Cake\ORM\ResultSetInterface<\App\Model\Entity\AvaliationsSumula>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\AvaliationsSumula>|\Cake\ORM\ResultSetInterface<\App\Model\Entity\AvaliationsSumula> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AvaliationsSumulasTable extends Table
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

        $this->setTable('avaliations_sumulas');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('AvaliadorBolsistas', [
            'foreignKey' => 'avaliador_bolsista_id',
        ]);
        $this->belongsTo('EditaisSumulas', [
            'foreignKey' => 'editais_sumula_id',
        ]);
        $this->belongsTo('EditaisSumulasBlocos', [
            'foreignKey' => 'editais_sumula_bloco_id',
        ]);
        $this->belongsTo('InscricaoSumulas', [
            'foreignKey' => 'inscricao_sumula_id',
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
            ->nonNegativeInteger('avaliador_bolsista_id')
            ->allowEmptyString('avaliador_bolsista_id');

        $validator
            ->nonNegativeInteger('editais_sumula_id')
            ->allowEmptyString('editais_sumula_id');

        $validator
            ->nonNegativeInteger('editais_sumula_bloco_id')
            ->allowEmptyString('editais_sumula_bloco_id');

        $validator
            ->nonNegativeInteger('inscricao_sumula_id')
            ->allowEmptyString('inscricao_sumula_id');

        $validator
            ->numeric('nota')
            ->range('nota', [0, 99.99])
            ->allowEmptyString('nota');

        $validator
            ->scalar('observacao_avaliador')
            ->allowEmptyString('observacao_avaliador');

        $validator
            ->integer('deleted')
            ->allowEmptyString('deleted');

        $validator
            ->nonNegativeInteger('quantidade_original')
            ->allowEmptyString('quantidade_original');

        $validator
            ->nonNegativeInteger('quantidade_avaliada')
            ->allowEmptyString('quantidade_avaliada');

        $validator
            ->boolean('bolsista')
            ->allowEmptyString('bolsista');

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
        $rules->add($rules->existsIn(['avaliador_bolsista_id'], 'AvaliadorBolsistas'), ['errorField' => 'avaliador_bolsista_id']);
        $rules->add($rules->existsIn(['editais_sumula_id'], 'EditaisSumulas'), ['errorField' => 'editais_sumula_id']);
        $rules->add($rules->existsIn(['editais_sumula_bloco_id'], 'EditaisSumulasBlocos'), ['errorField' => 'editais_sumula_bloco_id']);
        $rules->add($rules->existsIn(['inscricao_sumula_id'], 'InscricaoSumulas'), ['errorField' => 'inscricao_sumula_id']);

        return $rules;
    }
}
