<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Editais Model
 *
 * @property \App\Model\Table\UsuariosTable&\Cake\ORM\Association\BelongsTo $Usuarios
 * @property \App\Model\Table\ProgramasTable&\Cake\ORM\Association\BelongsTo $Programas
 * @property \App\Model\Table\AvaliadorBolsistasTable&\Cake\ORM\Association\HasMany $AvaliadorBolsistas
 * @property \App\Model\Table\AvaliadorsTable&\Cake\ORM\Association\HasMany $Avaliadors
 * @property \App\Model\Table\BancasTable&\Cake\ORM\Association\HasMany $Bancas
 * @property \App\Model\Table\CertificadosTable&\Cake\ORM\Association\HasMany $Certificados
 * @property \App\Model\Table\EditaisPrazosTable&\Cake\ORM\Association\HasMany $EditaisPrazos
 * @property \App\Model\Table\ErratasTable&\Cake\ORM\Association\HasMany $Erratas
 * @property \App\Model\Table\EditaisSumulasTable&\Cake\ORM\Association\HasMany $EditaisSumulas
 * @property \App\Model\Table\ProjetoBolsistasTable&\Cake\ORM\Association\HasMany $ProjetoBolsistas
 * @property \App\Model\Table\QuestionsTable&\Cake\ORM\Association\HasMany $Questions
 * @property \App\Model\Table\RaicsTable&\Cake\ORM\Association\HasMany $Raics
 * @property \App\Model\Table\WorkshopsTable&\Cake\ORM\Association\HasMany $Workshops
 *
 * @method \App\Model\Entity\Editai newEmptyEntity()
 * @method \App\Model\Entity\Editai newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Editai> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Editai get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Editai findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Editai patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Editai> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Editai|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Editai saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Editai>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Editai>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Editai>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Editai> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Editai>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Editai>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Editai>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Editai> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class EditaisTable extends Table
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

        $this->setTable('editais');
        $this->setDisplayField('nome');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Usuarios', [
            'foreignKey' => 'usuario_id',
        ]);
        $this->belongsTo('Programas', [
            'foreignKey' => 'programa_id',
        ]);
        $this->hasMany('AvaliadorBolsistas', [
            'foreignKey' => 'editai_id',
        ]);
        $this->hasMany('Avaliadors', [
            'foreignKey' => 'editai_id',
        ]);
        $this->hasMany('Bancas', [
            'foreignKey' => 'editai_id',
        ]);
        $this->hasMany('Certificados', [
            'foreignKey' => 'editai_id',
        ]);
        $this->hasMany('Dashdetalhes', [
            'foreignKey' => 'editai_id',
        ]);
        $this->hasMany('Dashyodacounts', [
            'foreignKey' => 'editai_id',
        ]);
        $this->hasMany('EditaisPrazos', [
            'foreignKey' => 'editai_id',
        ]);
        $this->hasMany('Erratas', [
            'foreignKey' => 'editai_id',
        ]);
        $this->hasMany('EditaisSumulas', [
            'foreignKey' => 'editai_id',
        ]);
        $this->hasMany('PdjInscricoes', [
            'foreignKey' => 'editai_id',
        ]);
        $this->hasMany('ProjetoBolsistas', [
            'foreignKey' => 'editai_id',
        ]);
        $this->hasMany('Questions', [
            'foreignKey' => 'editai_id',
        ]);
        $this->hasMany('Raics', [
            'foreignKey' => 'editai_id',
        ]);
        $this->hasMany('Workshops', [
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
            ->scalar('nome')
            ->maxLength('nome', 45)
            ->allowEmptyString('nome');

        $validator
            ->dateTime('divulgacao')
            ->allowEmptyDateTime('divulgacao');

        $validator
            ->dateTime('inicio_inscricao')
            ->allowEmptyDateTime('inicio_inscricao');

        $validator
            ->dateTime('fim_inscricao')
            ->allowEmptyDateTime('fim_inscricao');

        $validator
            ->dateTime('resultado')
            ->allowEmptyDateTime('resultado');

        $validator
            ->dateTime('inicio_recurso')
            ->allowEmptyDateTime('inicio_recurso');

        $validator
            ->dateTime('fim_recurso')
            ->allowEmptyDateTime('fim_recurso');

        $validator
            ->dateTime('resultado_recurso')
            ->allowEmptyDateTime('resultado_recurso');

        $validator
            ->scalar('arquivo')
            ->maxLength('arquivo', 45)
            ->allowEmptyString('arquivo');

        $validator
            ->scalar('origem')
            ->allowEmptyString('origem');

        $validator
            ->dateTime('inicio_vigencia')
            ->allowEmptyDateTime('inicio_vigencia');

        $validator
            ->dateTime('fim_vigencia')
            ->allowEmptyDateTime('fim_vigencia');

        $validator
            ->nonNegativeInteger('limitaAnoDoutorado')
            ->allowEmptyString('limitaAnoDoutorado');

        $validator
            ->scalar('resultado_arquivo')
            ->maxLength('resultado_arquivo', 45)
            ->allowEmptyString('resultado_arquivo');

        $validator
            ->scalar('vinculos_permitidos')
            ->maxLength('vinculos_permitidos', 45)
            ->allowEmptyString('vinculos_permitidos');

        $validator
            ->scalar('escolaridades_permitidas')
            ->maxLength('escolaridades_permitidas', 45)
            ->allowEmptyString('escolaridades_permitidas');

        $validator
            ->integer('usuario_id')
            ->allowEmptyString('usuario_id');

        $validator
            ->allowEmptyString('deleted');

        $validator
            ->scalar('unidades_permitidas')
            ->maxLength('unidades_permitidas', 300)
            ->allowEmptyString('unidades_permitidas');

        $validator
            ->scalar('visualizar')
            ->allowEmptyString('visualizar');

        $validator
            ->dateTime('inicio_avaliar')
            ->allowEmptyDateTime('inicio_avaliar');

        $validator
            ->dateTime('fim_avaliar')
            ->allowEmptyDateTime('fim_avaliar');

        $validator
            ->integer('evento')
            ->allowEmptyString('evento');

        $validator
            ->integer('programa_id')
            ->allowEmptyString('programa_id');

        $validator
            ->scalar('link')
            ->maxLength('link', 45)
            ->allowEmptyString('link');

        $validator
            ->scalar('cpf_permitidos')
            ->allowEmptyString('cpf_permitidos');

        $validator
            ->scalar('modelo_cons_bols')
            ->maxLength('modelo_cons_bols', 45)
            ->allowEmptyString('modelo_cons_bols');

        $validator
            ->scalar('modelo_cons_coor')
            ->maxLength('modelo_cons_coor', 45)
            ->allowEmptyString('modelo_cons_coor');

        $validator
            ->scalar('modelo_relat_bols')
            ->maxLength('modelo_relat_bols', 45)
            ->allowEmptyString('modelo_relat_bols');

        $validator
            ->scalar('controller')
            ->maxLength('controller', 45)
            ->allowEmptyString('controller');

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
        $rules->add($rules->existsIn(['programa_id'], 'Programas'), ['errorField' => 'programa_id']);

        return $rules;
    }
}
