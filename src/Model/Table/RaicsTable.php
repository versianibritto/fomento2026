<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Raics Model
 *
 * @property \App\Model\Table\UsuariosTable&\Cake\ORM\Association\BelongsTo $Usuarios
 * @property \App\Model\Table\ProjetoBolsistasTable&\Cake\ORM\Association\BelongsTo $ProjetoBolsistas
 * @property \App\Model\Table\UnidadesTable&\Cake\ORM\Association\BelongsTo $Unidades
 * @property \App\Model\Table\EditaisTable&\Cake\ORM\Association\BelongsTo $Editais
 * @property \App\Model\Table\GabaritosTable&\Cake\ORM\Association\HasMany $Gabaritos
 * @property \App\Model\Table\ProjetoAnexosTable&\Cake\ORM\Association\HasMany $ProjetoAnexos
 * @property \App\Model\Table\RaicHistoricosTable&\Cake\ORM\Association\HasMany $RaicHistoricos
 *
 * @method \App\Model\Entity\Raic newEmptyEntity()
 * @method \App\Model\Entity\Raic newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Raic> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Raic get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Raic findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Raic patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Raic> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Raic|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Raic saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Raic>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Raic>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Raic>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Raic> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Raic>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Raic>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Raic>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Raic> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RaicsTable extends Table
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

        $this->setTable('raics');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Usuarios', [
            'foreignKey' => 'usuario_id',
        ]);
        $this->belongsTo('Orientadores', [
            'className' => 'Usuarios',
            'foreignKey' => 'orientador',
            'propertyName' => 'orientadore',
        ]);
        $this->belongsTo('Coorientadores', [
            'className' => 'Usuarios',
            'foreignKey' => 'coorientador',
            'propertyName' => 'coorientadore',
        ]);
        $this->belongsTo('Projetos', [
            'foreignKey' => 'projeto_orientador',
        ]);
        $this->belongsTo('ProjetoBolsistas', [
            'foreignKey' => 'projeto_bolsista_id',
        ]);
        $this->belongsTo('Unidades', [
            'foreignKey' => 'unidade_id',
        ]);
        $this->belongsTo('Editais', [
            'foreignKey' => 'editai_id',
        ]);
        $this->belongsTo('Cadastro', [
            'className' => 'Usuarios',
            'foreignKey' => 'usuario_cadastro',
        ]);
        $this->belongsTo('Libera', [
            'className' => 'Usuarios',
            'foreignKey' => 'usuario_libera',
        ]);
        $this->hasMany('Gabaritos', [
            'foreignKey' => 'raic_id',
        ]);
        $this->hasMany('ProjetoAnexos', [
            'foreignKey' => 'raic_id',
        ]);
        $this->hasMany('RaicHistoricos', [
            'foreignKey' => 'raic_id',
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
            ->integer('usuario_id')
            ->allowEmptyString('usuario_id');

        $validator
            ->integer('orientador')
            ->allowEmptyString('orientador');

        $validator
            ->integer('coorientador')
            ->allowEmptyString('coorientador');

        $validator
            ->integer('projeto_orientador')
            ->allowEmptyString('projeto_orientador');

        $validator
            ->scalar('resumo')
            ->maxLength('resumo', 4294967295)
            ->allowEmptyString('resumo');

        $validator
            ->scalar('objetivos')
            ->maxLength('objetivos', 4294967295)
            ->allowEmptyString('objetivos');

        $validator
            ->scalar('descricao')
            ->maxLength('descricao', 4294967295)
            ->allowEmptyString('descricao');

        $validator
            ->scalar('titulo')
            ->allowEmptyString('titulo');

        $validator
            ->scalar('relatorio')
            ->maxLength('relatorio', 45)
            ->allowEmptyString('relatorio');

        $validator
            ->scalar('anexo')
            ->maxLength('anexo', 50)
            ->allowEmptyString('anexo');

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
            ->integer('projeto_bolsista_id')
            ->allowEmptyString('projeto_bolsista_id');

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
            ->integer('usuario_cadastro')
            ->allowEmptyString('usuario_cadastro');

        $validator
            ->integer('usuario_libera')
            ->allowEmptyString('usuario_libera');

        $validator
            ->dateTime('data_liberacao')
            ->allowEmptyDateTime('data_liberacao');

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
        $rules->add($rules->existsIn(['usuario_id'], 'Usuarios'), ['errorField' => 'usuario_id']);
        $rules->add($rules->existsIn(['orientador'], 'Orientadores'), ['errorField' => 'orientador']);
        $rules->add($rules->existsIn(['coorientador'], 'Coorientadores'), ['errorField' => 'coorientador']);
        $rules->add($rules->existsIn(['projeto_orientador'], 'Projetos'), ['errorField' => 'projeto_orientador']);
        $rules->add($rules->existsIn(['projeto_bolsista_id'], 'ProjetoBolsistas'), ['errorField' => 'projeto_bolsista_id']);
        $rules->add($rules->existsIn(['unidade_id'], 'Unidades'), ['errorField' => 'unidade_id']);
        $rules->add($rules->existsIn(['editai_id'], 'Editais'), ['errorField' => 'editai_id']);
        $rules->add($rules->existsIn(['usuario_cadastro'], 'Cadastro'), ['errorField' => 'usuario_cadastro']);
        $rules->add($rules->existsIn(['usuario_libera'], 'Libera'), ['errorField' => 'usuario_libera']);

        return $rules;
    }
}
