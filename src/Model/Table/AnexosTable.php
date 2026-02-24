<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Anexos Model
 *
 * @property \App\Model\Table\ProjetosTable&\Cake\ORM\Association\BelongsTo $Projetos
 * @property \App\Model\Table\ProjetoBolsistasTable&\Cake\ORM\Association\BelongsTo $ProjetoBolsistas
 * @property \App\Model\Table\AnexosTiposTable&\Cake\ORM\Association\BelongsTo $AnexosTipos
 * @property \App\Model\Table\UsuariosTable&\Cake\ORM\Association\BelongsTo $Usuarios
 * @property \App\Model\Table\RaicsTable&\Cake\ORM\Association\BelongsTo $Raics
 * @property \App\Model\Table\PdjInscricoesTable&\Cake\ORM\Association\BelongsTo $PdjInscricoes
 *
 * @method \App\Model\Entity\Anexo newEmptyEntity()
 * @method \App\Model\Entity\Anexo newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Anexo> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Anexo get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Anexo findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Anexo patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Anexo> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Anexo|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Anexo saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Anexo>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Anexo>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Anexo>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Anexo> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Anexo>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Anexo>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Anexo>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Anexo> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AnexosTable extends Table
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

        $this->setTable('anexos');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Projetos', [
            'foreignKey' => 'projeto_id',
        ]);
        $this->belongsTo('ProjetoBolsistas', [
            'foreignKey' => 'projeto_bolsista_id',
        ]);
        $this->belongsTo('AnexosTipos', [
            'foreignKey' => 'anexos_tipo_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Usuarios', [
            'foreignKey' => 'usuario_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Raics', [
            'foreignKey' => 'raic_id',
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
            ->nonNegativeInteger('projeto_id')
            ->allowEmptyString('projeto_id');

        $validator
            ->nonNegativeInteger('projeto_bolsista_id')
            ->allowEmptyString('projeto_bolsista_id');

        $validator
            ->nonNegativeInteger('anexos_tipo_id')
            ->notEmptyString('anexos_tipo_id');

        $validator
            ->scalar('anexo')
            ->maxLength('anexo', 50)
            ->allowEmptyString('anexo');

        $validator
            ->dateTime('deleted')
            ->allowEmptyDateTime('deleted');

        $validator
            ->nonNegativeInteger('usuario_id')
            ->notEmptyString('usuario_id');

        $validator
            ->integer('raic_id')
            ->allowEmptyString('raic_id');

        $validator
            ->integer('pdj_inscricoe_id')
            ->allowEmptyString('pdj_inscricoe_id');

        $validator
            ->scalar('bloco')
            ->allowEmptyString('bloco');

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
        $rules->add($rules->existsIn(['projeto_bolsista_id'], 'ProjetoBolsistas'), ['errorField' => 'projeto_bolsista_id']);
        $rules->add($rules->existsIn(['anexos_tipo_id'], 'AnexosTipos'), ['errorField' => 'anexos_tipo_id']);
        $rules->add($rules->existsIn(['usuario_id'], 'Usuarios'), ['errorField' => 'usuario_id']);
        $rules->add($rules->existsIn(['raic_id'], 'Raics'), ['errorField' => 'raic_id']);
        $rules->add($rules->existsIn(['pdj_inscricoe_id'], 'PdjInscricoes'), ['errorField' => 'pdj_inscricoe_id']);

        return $rules;
    }
}
