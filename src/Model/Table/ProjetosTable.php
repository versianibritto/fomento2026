<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Projetos Model
 *
 * @property \App\Model\Table\UsuariosTable&\Cake\ORM\Association\BelongsTo $Usuarios
 * @property \App\Model\Table\AreasTable&\Cake\ORM\Association\BelongsTo $Areas
 * @property \App\Model\Table\LinhasTable&\Cake\ORM\Association\BelongsTo $Linhas
 * @property \App\Model\Table\AvaliadorProjetosTable&\Cake\ORM\Association\HasMany $AvaliadorProjetos
 * @property \App\Model\Table\PdjInscricoesTable&\Cake\ORM\Association\HasMany $PdjInscricoes
 * @property \App\Model\Table\ProjetoAnexosTable&\Cake\ORM\Association\HasMany $ProjetoAnexos
 * @property \App\Model\Table\ProjetoBolsistasTable&\Cake\ORM\Association\HasMany $ProjetoBolsistas
 * @property \App\Model\Table\ProjetoKeywordsTable&\Cake\ORM\Association\HasMany $ProjetoKeywords
 * @property \App\Model\Table\ProjetosDadosTable&\Cake\ORM\Association\HasMany $ProjetosDados
 *
 * @method \App\Model\Entity\Projeto newEmptyEntity()
 * @method \App\Model\Entity\Projeto newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Projeto> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Projeto get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Projeto findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Projeto patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Projeto> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Projeto|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Projeto saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Projeto>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Projeto>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Projeto>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Projeto> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Projeto>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Projeto>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Projeto>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Projeto> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ProjetosTable extends Table
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

        $this->setTable('projetos');
        $this->setDisplayField('titulo');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Usuarios', [
            'foreignKey' => 'usuario_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Areas', [
            'foreignKey' => 'area_id',
        ]);
        $this->belongsTo('Linhas', [
            'foreignKey' => 'linha_id',
        ]);
        $this->hasMany('AvaliadorProjetos', [
            'foreignKey' => 'projeto_id',
        ]);
        $this->hasMany('PdjInscricoes', [
            'foreignKey' => 'projeto_id',
        ]);
        $this->hasMany('ProjetoAnexos', [
            'foreignKey' => 'projeto_id',
        ]);
        $this->hasMany('ProjetoBolsistas', [
            'foreignKey' => 'projeto_id',
        ]);
        $this->hasMany('ProjetoKeywords', [
            'foreignKey' => 'projeto_id',
        ]);
        $this->hasMany('ProjetosDados', [
            'foreignKey' => 'projeto_id',
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
            ->nonNegativeInteger('usuario_id')
            ->notEmptyString('usuario_id');

        $validator
            ->scalar('titulo')
            ->maxLength('titulo', 255)
            ->requirePresence('titulo', 'create')
            ->notEmptyString('titulo');

        $validator
            ->scalar('tipo_projeto')
            ->allowEmptyString('tipo_projeto');

        $validator
            ->scalar('ano_inicio')
            ->maxLength('ano_inicio', 4)
            ->allowEmptyString('ano_inicio');

        $validator
            ->nonNegativeInteger('duracao')
            ->allowEmptyString('duracao');

        $validator
            ->scalar('financiamento')
            ->allowEmptyString('financiamento');

        $validator
            ->nonNegativeInteger('area_id')
            ->allowEmptyString('area_id');

        $validator
            ->scalar('resumo')
            ->allowEmptyString('resumo');

        $validator
            ->scalar('objetivos')
            ->allowEmptyString('objetivos');

        $validator
            ->scalar('parecer_comite')
            ->maxLength('parecer_comite', 45)
            ->allowEmptyString('parecer_comite');

        $validator
            ->scalar('autorizacao_sisgen')
            ->maxLength('autorizacao_sisgen', 45)
            ->allowEmptyString('autorizacao_sisgen');

        $validator
            ->scalar('situacao')
            ->allowEmptyString('situacao');

        $validator
            ->dateTime('data_situacao')
            ->allowEmptyDateTime('data_situacao');

        $validator
            ->scalar('justificativa_situacao')
            ->allowEmptyString('justificativa_situacao');

        $validator
            ->nonNegativeInteger('id_pibiti')
            ->allowEmptyString('id_pibiti');

        $validator
            ->nonNegativeInteger('linha_id')
            ->allowEmptyString('linha_id');

        $validator
            ->scalar('descricao')
            ->maxLength('descricao', 4294967295)
            ->allowEmptyString('descricao');

        $validator
            ->scalar('metodologia')
            ->maxLength('metodologia', 4294967295)
            ->allowEmptyString('metodologia');

        $validator
            ->scalar('vinculo_pdti')
            ->maxLength('vinculo_pdti', 45)
            ->allowEmptyString('vinculo_pdti');

        $validator
            ->scalar('deposito_patentes')
            ->maxLength('deposito_patentes', 45)
            ->allowEmptyString('deposito_patentes');

        $validator
            ->scalar('anexos')
            ->maxLength('anexos', 45)
            ->allowEmptyString('anexos');

        $validator
            ->dateTime('deleted')
            ->allowEmptyDateTime('deleted');

        $validator
            ->scalar('palavras_chaves')
            ->allowEmptyString('palavras_chaves');

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
        $rules->add($rules->existsIn(['area_id'], 'Areas'), ['errorField' => 'area_id']);
        $rules->add($rules->existsIn(['linha_id'], 'Linhas'), ['errorField' => 'linha_id']);

        return $rules;
    }
}
