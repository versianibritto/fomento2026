<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Workshops Model
 *
 * @property \App\Model\Table\UsuariosTable&\Cake\ORM\Association\BelongsTo $Usuarios
 * @property \App\Model\Table\UsuariosTable&\Cake\ORM\Association\BelongsTo $Bolsistas
 * @property \App\Model\Table\UnidadesTable&\Cake\ORM\Association\BelongsTo $Unidades
 * @property \App\Model\Table\EditaisTable&\Cake\ORM\Association\BelongsTo $Editais
 * @property \App\Model\Table\PdjInscricoesTable&\Cake\ORM\Association\BelongsTo $PdjInscricoes
 * @property \App\Model\Table\ProjetoBolsistasTable&\Cake\ORM\Association\BelongsTo $ProjetoBolsistas
 * @property \App\Model\Table\WorkshopHistoricosTable&\Cake\ORM\Association\HasMany $WorkshopHistoricos
 *
 * @method \App\Model\Entity\Workshop newEmptyEntity()
 * @method \App\Model\Entity\Workshop newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Workshop> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Workshop get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Workshop findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Workshop patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Workshop> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Workshop|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Workshop saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Workshop>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Workshop>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Workshop>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Workshop> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Workshop>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Workshop>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Workshop>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Workshop> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class WorkshopsTable extends Table
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

        $this->setTable('workshops');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Usuarios', [
            'foreignKey' => 'bolsista',
            'joinType' => 'LEFT',
        ]);
        $this->belongsTo('Bolsistas', [
            'className' => 'Usuarios',
            'foreignKey' => 'bolsista',
            'joinType' => 'LEFT',
            'propertyName' => 'bolsista_usuario',
        ]);
        $this->belongsTo('Orientadores', [
            'className' => 'Usuarios',
            'foreignKey' => 'orientador',
            'propertyName' => 'orientadore',
        ]);
        $this->belongsTo('Unidades', [
            'foreignKey' => 'unidade_id',
        ]);
        $this->belongsTo('Editais', [
            'foreignKey' => 'editai_id',
        ]);
        $this->belongsTo('PdjInscricoes', [
            'foreignKey' => 'pdj_inscricoe_id',
        ]);
        $this->belongsTo('ProjetoBolsistas', [
            'foreignKey' => 'projeto_bolsista_id',
        ]);
        $this->belongsTo('Projetos', [
            'foreignKey' => 'projeto_orientador',
        ]);
        $this->belongsTo('Cadastro', [
            'className' => 'Usuarios',
            'foreignKey' => 'usuario_cadastro',
        ]);
        $this->belongsTo('Libera', [
            'className' => 'Usuarios',
            'foreignKey' => 'usuario_libera',
        ]);
        $this->hasMany('WorkshopHistoricos', [
            'foreignKey' => 'workshop_id',
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
            ->integer('bolsista')
            ->allowEmptyString('bolsista');

        $validator
            ->integer('orientador')
            ->allowEmptyString('orientador');

        $validator
            ->integer('projeto_orientador')
            ->allowEmptyString('projeto_orientador');

        $validator
            ->scalar('tipo_bolsa')
            ->allowEmptyString('tipo_bolsa');

        $validator
            ->date('data_apresentacao')
            ->allowEmptyDate('data_apresentacao');

        $validator
            ->scalar('local_apresentacao')
            ->allowEmptyString('local_apresentacao');

        $validator
            ->scalar('tipo_apresentacao')
            ->allowEmptyString('tipo_apresentacao');

        $validator
            ->numeric('nota_final')
            ->allowEmptyString('nota_final');

        $validator
            ->scalar('presenca')
            ->allowEmptyString('presenca');

        $validator
            ->scalar('observacao_avaliador')
            ->allowEmptyString('observacao_avaliador');

        $validator
            ->allowEmptyString('destaque');

        $validator
            ->allowEmptyString('indicado_premio_capes');

        $validator
            ->integer('deleted')
            ->notEmptyString('deleted');

        $validator
            ->integer('unidade_id')
            ->allowEmptyString('unidade_id');

        $validator
            ->integer('editai_id')
            ->allowEmptyString('editai_id');

        $validator
            ->integer('evento')
            ->allowEmptyString('evento');

        $validator
            ->integer('usuario_cadastro')
            ->allowEmptyString('usuario_cadastro');

        $validator
            ->integer('usuario_libera')
            ->allowEmptyString('usuario_libera');

        $validator
            ->dateTime('data_liberacao')
            ->allowEmptyDateTime('data_liberacao');

        $validator
            ->integer('pdj_inscricoe_id')
            ->allowEmptyString('pdj_inscricoe_id');

        $validator
            ->integer('projeto_bolsista_id')
            ->allowEmptyString('projeto_bolsista_id');

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
        $rules->add($rules->existsIn(['bolsista'], 'Bolsistas'), ['errorField' => 'bolsista']);
        $rules->add($rules->existsIn(['orientador'], 'Orientadores'), ['errorField' => 'orientador']);
        $rules->add($rules->existsIn(['unidade_id'], 'Unidades'), ['errorField' => 'unidade_id']);
        $rules->add($rules->existsIn(['editai_id'], 'Editais'), ['errorField' => 'editai_id']);
        $rules->add($rules->existsIn(['pdj_inscricoe_id'], 'PdjInscricoes'), ['errorField' => 'pdj_inscricoe_id']);
        $rules->add($rules->existsIn(['projeto_bolsista_id'], 'ProjetoBolsistas'), ['errorField' => 'projeto_bolsista_id']);

        return $rules;
    }
}
