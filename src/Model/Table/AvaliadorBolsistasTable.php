<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * AvaliadorBolsistas Model
 *
 * @property \App\Model\Table\AvaliadorsTable&\Cake\ORM\Association\BelongsTo $Avaliadors
 * @property \App\Model\Table\UsuariosTable&\Cake\ORM\Association\BelongsTo $Usuarios
 * @property \App\Model\Table\RaicsTable&\Cake\ORM\Association\BelongsTo $Raics
 * @property \App\Model\Table\WorkshopsTable&\Cake\ORM\Association\BelongsTo $Workshops
 * @property \App\Model\Table\ProjetoBolsistasTable&\Cake\ORM\Association\BelongsTo $ProjetoBolsistas
 * @property \App\Model\Table\EditaisTable&\Cake\ORM\Association\BelongsTo $Editais
 * @property \App\Model\Table\BancasTable&\Cake\ORM\Association\BelongsTo $Bancas
 * @property \App\Model\Table\AvaliationsTable&\Cake\ORM\Association\HasMany $Avaliations
 *
 * @method \App\Model\Entity\AvaliadorBolsista newEmptyEntity()
 * @method \App\Model\Entity\AvaliadorBolsista newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\AvaliadorBolsista> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AvaliadorBolsista get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\AvaliadorBolsista findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\AvaliadorBolsista patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\AvaliadorBolsista> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AvaliadorBolsista|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\AvaliadorBolsista saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\AvaliadorBolsista>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\AvaliadorBolsista>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\AvaliadorBolsista>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\AvaliadorBolsista> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\AvaliadorBolsista>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\AvaliadorBolsista>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\AvaliadorBolsista>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\AvaliadorBolsista> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AvaliadorBolsistasTable extends Table
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

        $this->setTable('avaliador_bolsistas');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Avaliadors', [
            'foreignKey' => 'avaliador_id',
        ]);
        $this->belongsTo('Usuarios', [
            'foreignKey' => 'usuario_id',
        ]);
        $this->belongsTo('Raics', [
            'foreignKey' => 'raic_id',
        ]);
        $this->belongsTo('Workshops', [
            'foreignKey' => 'workshop_id',
        ]);
        $this->belongsTo('ProjetoBolsistas', [
            'foreignKey' => 'projeto_bolsista_id',
        ]);
        $this->belongsTo('Editais', [
            'foreignKey' => 'editai_id',
        ]);
        $this->belongsTo('Bancas', [
            'foreignKey' => 'banca_id',
        ]);
        $this->hasMany('Avaliations', [
            'foreignKey' => 'avaliador_bolsista_id',
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
            ->nonNegativeInteger('avaliador_id')
            ->allowEmptyString('avaliador_id');

        $validator
            ->nonNegativeInteger('bolsista')
            ->allowEmptyString('bolsista');

        $validator
            ->nonNegativeInteger('usuario_id')
            ->allowEmptyString('usuario_id');

        $validator
            ->nonNegativeInteger('raic_id')
            ->allowEmptyString('raic_id');

        $validator
            ->nonNegativeInteger('workshop_id')
            ->allowEmptyString('workshop_id');

        $validator
            ->nonNegativeInteger('projeto_bolsista_id')
            ->allowEmptyString('projeto_bolsista_id');

        $validator
            ->scalar('tipo')
            ->allowEmptyString('tipo');

        $validator
            ->scalar('ano')
            ->maxLength('ano', 5)
            ->allowEmptyString('ano');

        $validator
            ->scalar('situacao')
            ->allowEmptyString('situacao');

        $validator
            ->boolean('coordenador')
            ->allowEmptyString('coordenador');

        $validator
            ->scalar('observacao')
            ->allowEmptyString('observacao');

        $validator
            ->integer('deleted')
            ->notEmptyString('deleted');

        $validator
            ->allowEmptyString('destaque');

        $validator
            ->allowEmptyString('indicado_premio_capes');

        $validator
            ->allowEmptyString('alteracao');

        $validator
            ->scalar('observacao_alteracao')
            ->allowEmptyString('observacao_alteracao');

        $validator
            ->integer('editai_id')
            ->allowEmptyString('editai_id');

        $validator
            ->numeric('nota')
            ->allowEmptyString('nota');

        $validator
            ->scalar('parecer')
            ->maxLength('parecer', 45)
            ->allowEmptyString('parecer');

        $validator
            ->integer('ordem')
            ->allowEmptyString('ordem');

        $validator
            ->integer('banca_id')
            ->allowEmptyString('banca_id');

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
        $rules->add($rules->existsIn(['avaliador_id'], 'Avaliadors'), ['errorField' => 'avaliador_id']);
        $rules->add($rules->existsIn(['usuario_id'], 'Usuarios'), ['errorField' => 'usuario_id']);
        $rules->add($rules->existsIn(['raic_id'], 'Raics'), ['errorField' => 'raic_id']);
        $rules->add($rules->existsIn(['workshop_id'], 'Workshops'), ['errorField' => 'workshop_id']);
        $rules->add($rules->existsIn(['projeto_bolsista_id'], 'ProjetoBolsistas'), ['errorField' => 'projeto_bolsista_id']);
        $rules->add($rules->existsIn(['editai_id'], 'Editais'), ['errorField' => 'editai_id']);
        $rules->add($rules->existsIn(['banca_id'], 'Bancas'), ['errorField' => 'banca_id']);

        return $rules;
    }
}
