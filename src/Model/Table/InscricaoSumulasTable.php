<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * InscricaoSumulas Model
 *
 * @property \App\Model\Table\EditaisSumulasTable&\Cake\ORM\Association\BelongsTo $EditaisSumulas
 * @property \App\Model\Table\EditaisTable&\Cake\ORM\Association\BelongsTo $Editais
 * @property \App\Model\Table\EditaisSumulasBlocosTable&\Cake\ORM\Association\BelongsTo $EditaisSumulasBlocos
 * @property \App\Model\Table\ProjetoBolsistasTable&\Cake\ORM\Association\BelongsTo $ProjetoBolsistas
 * @property \App\Model\Table\PdjInscricoesTable&\Cake\ORM\Association\BelongsTo $PdjInscricoes
 *
 * @method \App\Model\Entity\InscricaoSumula newEmptyEntity()
 * @method \App\Model\Entity\InscricaoSumula newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\InscricaoSumula> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\InscricaoSumula get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\InscricaoSumula findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\InscricaoSumula patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\InscricaoSumula> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\InscricaoSumula|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\InscricaoSumula saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\InscricaoSumula>|\Cake\ORM\ResultSetInterface<\App\Model\Entity\InscricaoSumula>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\InscricaoSumula>|\Cake\ORM\ResultSetInterface<\App\Model\Entity\InscricaoSumula> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\InscricaoSumula>|\Cake\ORM\ResultSetInterface<\App\Model\Entity\InscricaoSumula>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\InscricaoSumula>|\Cake\ORM\ResultSetInterface<\App\Model\Entity\InscricaoSumula> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class InscricaoSumulasTable extends Table
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

        $this->setTable('inscricao_sumulas');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('EditaisSumulas', [
            'foreignKey' => 'editais_sumula_id',
        ]);
        $this->belongsTo('Editais', [
            'foreignKey' => 'editai_id',
        ]);
        $this->belongsTo('EditaisSumulasBlocos', [
            'foreignKey' => 'editais_sumula_bloco_id',
        ]);
        $this->belongsTo('ProjetoBolsistas', [
            'foreignKey' => 'projeto_bolsista_id',
        ]);
        $this->belongsTo('PdjInscricoes', [
            'foreignKey' => 'pdj_inscricoe_id',
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
            ->nonNegativeInteger('editais_sumula_id')
            ->allowEmptyString('editais_sumula_id');

        $validator
            ->nonNegativeInteger('editai_id')
            ->allowEmptyString('editai_id');

        $validator
            ->nonNegativeInteger('editais_sumula_bloco_id')
            ->allowEmptyString('editais_sumula_bloco_id');

        $validator
            ->nonNegativeInteger('projeto_bolsista_id')
            ->allowEmptyString('projeto_bolsista_id');

        $validator
            ->nonNegativeInteger('pdj_inscricoe_id')
            ->allowEmptyString('pdj_inscricoe_id');

        $validator
            ->integer('quantidade')
            ->allowEmptyString('quantidade');

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
        $rules->add($rules->existsIn(['editais_sumula_id'], 'EditaisSumulas'), ['errorField' => 'editais_sumula_id']);
        $rules->add($rules->existsIn(['editai_id'], 'Editais'), ['errorField' => 'editai_id']);
        $rules->add($rules->existsIn(['editais_sumula_bloco_id'], 'EditaisSumulasBlocos'), ['errorField' => 'editais_sumula_bloco_id']);
        $rules->add($rules->existsIn(['projeto_bolsista_id'], 'ProjetoBolsistas'), ['errorField' => 'projeto_bolsista_id']);
        $rules->add($rules->existsIn(['pdj_inscricoe_id'], 'PdjInscricoes'), ['errorField' => 'pdj_inscricoe_id']);

        return $rules;
    }
}

