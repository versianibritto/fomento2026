<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * AulaBolsistas Model
 *
 * @property \App\Model\Table\UsuariosTable&\Cake\ORM\Association\BelongsTo $Usuarios
 * @property \App\Model\Table\AulasTable&\Cake\ORM\Association\BelongsTo $Aulas
 * @property \App\Model\Table\CursosBolsistasTable&\Cake\ORM\Association\BelongsTo $CursosBolsistas
 *
 * @method \App\Model\Entity\AulaBolsista newEmptyEntity()
 * @method \App\Model\Entity\AulaBolsista newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\AulaBolsista> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AulaBolsista get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\AulaBolsista findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\AulaBolsista patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\AulaBolsista> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AulaBolsista|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\AulaBolsista saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\AulaBolsista>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\AulaBolsista>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\AulaBolsista>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\AulaBolsista> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\AulaBolsista>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\AulaBolsista>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\AulaBolsista>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\AulaBolsista> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AulaBolsistasTable extends Table
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

        $this->setTable('aula_bolsistas');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Usuarios', [
            'foreignKey' => 'usuario_id',
        ]);
        $this->belongsTo('Aulas', [
            'foreignKey' => 'aula_id',
        ]);
        $this->belongsTo('CursosBolsistas', [
            'foreignKey' => 'cursos_bolsista_id',
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
            ->nonNegativeInteger('aula_id')
            ->allowEmptyString('aula_id');

        $validator
            ->numeric('porcentagem')
            ->greaterThanOrEqual('porcentagem', 0)
            ->allowEmptyString('porcentagem');

        $validator
            ->numeric('nota')
            ->greaterThanOrEqual('nota', 0)
            ->allowEmptyString('nota');

        $validator
            ->integer('curso')
            ->allowEmptyString('curso');

        $validator
            ->integer('cursos_bolsista_id')
            ->allowEmptyString('cursos_bolsista_id');

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
        $rules->add($rules->existsIn(['aula_id'], 'Aulas'), ['errorField' => 'aula_id']);
        $rules->add($rules->existsIn(['cursos_bolsista_id'], 'CursosBolsistas'), ['errorField' => 'cursos_bolsista_id']);

        return $rules;
    }
}
