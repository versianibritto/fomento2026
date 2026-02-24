<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Avaliadors Model
 *
 * @property \App\Model\Table\UsuariosTable&\Cake\ORM\Association\BelongsTo $Usuarios
 * @property \App\Model\Table\GrandesAreasTable&\Cake\ORM\Association\BelongsTo $GrandesAreas
 * @property \App\Model\Table\AreasTable&\Cake\ORM\Association\BelongsTo $Areas
 * @property \App\Model\Table\SubAreasTable&\Cake\ORM\Association\BelongsTo $SubAreas
 * @property \App\Model\Table\EspecialidadesTable&\Cake\ORM\Association\BelongsTo $Especialidades
 * @property \App\Model\Table\LinhasTable&\Cake\ORM\Association\BelongsTo $Linhas
 * @property \App\Model\Table\UnidadesTable&\Cake\ORM\Association\BelongsTo $Unidades
 * @property \App\Model\Table\EditaisTable&\Cake\ORM\Association\BelongsTo $Editais
 * @property \App\Model\Table\AvaliadorBolsistasTable&\Cake\ORM\Association\HasMany $AvaliadorBolsistas
 * @property \App\Model\Table\AvaliadorProjetosTable&\Cake\ORM\Association\HasMany $AvaliadorProjetos
 * @property \App\Model\Table\BancaUsuariosTable&\Cake\ORM\Association\HasMany $BancaUsuarios
 *
 * @method \App\Model\Entity\Avaliador newEmptyEntity()
 * @method \App\Model\Entity\Avaliador newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Avaliador> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Avaliador get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Avaliador findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Avaliador patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Avaliador> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Avaliador|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Avaliador saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Avaliador>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Avaliador>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Avaliador>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Avaliador> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Avaliador>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Avaliador>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Avaliador>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Avaliador> deleteManyOrFail(iterable $entities, array $options = [])
 */
class AvaliadorsTable extends Table
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

        $this->setTable('avaliadors');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Usuarios', [
            'foreignKey' => 'usuario_id',
        ]);
        $this->belongsTo('GrandesAreas', [
            'foreignKey' => 'grandes_area_id',
        ]);
        $this->belongsTo('Areas', [
            'foreignKey' => 'area_id',
        ]);
        $this->belongsTo('SubAreas', [
            'foreignKey' => 'sub_area_id',
        ]);
        $this->belongsTo('Especialidades', [
            'foreignKey' => 'especialidade_id',
        ]);
        $this->belongsTo('Linhas', [
            'foreignKey' => 'linha_id',
        ]);
        $this->belongsTo('Unidades', [
            'foreignKey' => 'unidade_id',
        ]);
        $this->belongsTo('Editais', [
            'foreignKey' => 'editai_id',
        ]);
        $this->hasMany('AvaliadorBolsistas', [
            'foreignKey' => 'avaliador_id',
        ]);
        $this->hasMany('AvaliadorProjetos', [
            'foreignKey' => 'avaliador_id',
        ]);
        $this->hasMany('BancaUsuarios', [
            'foreignKey' => 'avaliador_id',
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
            ->allowEmptyString('usuario_id');

        $validator
            ->nonNegativeInteger('grandes_area_id')
            ->allowEmptyString('grandes_area_id');

        $validator
            ->nonNegativeInteger('area_id')
            ->allowEmptyString('area_id');

        $validator
            ->nonNegativeInteger('sub_area_id')
            ->allowEmptyString('sub_area_id');

        $validator
            ->nonNegativeInteger('especialidade_id')
            ->allowEmptyString('especialidade_id');

        $validator
            ->nonNegativeInteger('areas_fiocruz_id')
            ->allowEmptyString('areas_fiocruz_id');

        $validator
            ->nonNegativeInteger('linha_id')
            ->allowEmptyString('linha_id');

        $validator
            ->scalar('ano_convite')
            ->maxLength('ano_convite', 4)
            ->allowEmptyString('ano_convite');

        $validator
            ->scalar('ano_aceite')
            ->maxLength('ano_aceite', 4)
            ->allowEmptyString('ano_aceite');

        $validator
            ->scalar('voluntario')
            ->maxLength('voluntario', 4)
            ->allowEmptyString('voluntario');

        $validator
            ->integer('unidade_id')
            ->allowEmptyString('unidade_id');

        $validator
            ->scalar('tipo_avaliador')
            ->allowEmptyString('tipo_avaliador');

        $validator
            ->integer('deleted')
            ->notEmptyString('deleted');

        $validator
            ->integer('editai_id')
            ->allowEmptyString('editai_id');

        $validator
            ->integer('aceite')
            ->notEmptyString('aceite');

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
        $rules->add($rules->existsIn(['grandes_area_id'], 'GrandesAreas'), ['errorField' => 'grandes_area_id']);
        $rules->add($rules->existsIn(['area_id'], 'Areas'), ['errorField' => 'area_id']);
        $rules->add($rules->existsIn(['sub_area_id'], 'SubAreas'), ['errorField' => 'sub_area_id']);
        $rules->add($rules->existsIn(['especialidade_id'], 'Especialidades'), ['errorField' => 'especialidade_id']);
        $rules->add($rules->existsIn(['linha_id'], 'Linhas'), ['errorField' => 'linha_id']);
        $rules->add($rules->existsIn(['unidade_id'], 'Unidades'), ['errorField' => 'unidade_id']);
        $rules->add($rules->existsIn(['editai_id'], 'Editais'), ['errorField' => 'editai_id']);

        return $rules;
    }
}
