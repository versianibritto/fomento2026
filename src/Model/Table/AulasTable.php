<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Aulas Model
 *
 * @property \App\Model\Table\CursosOferecidosTable&\Cake\ORM\Association\BelongsTo $CursosOferecidos
 * @property \App\Model\Table\CursosModulosTable&\Cake\ORM\Association\BelongsTo $CursosModulos
 * @property \App\Model\Table\AulaBolsistasTable&\Cake\ORM\Association\HasMany $AulaBolsistas
 *
 * @method \App\Model\Entity\Aula newEmptyEntity()
 * @method \App\Model\Entity\Aula newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Aula> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Aula get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Aula findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Aula patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Aula> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Aula|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Aula saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Aula>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Aula>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Aula>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Aula> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Aula>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Aula>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Aula>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Aula> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AulasTable extends Table
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

        $this->setTable('aulas');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('CursosOferecidos', [
            'foreignKey' => 'cursos_oferecido_id',
        ]);
        $this->belongsTo('CursosModulos', [
            'foreignKey' => 'cursos_modulo_id',
        ]);
        $this->hasMany('AulaBolsistas', [
            'foreignKey' => 'aula_id',
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
            ->nonNegativeInteger('user_id')
            ->allowEmptyString('user_id');

        $validator
            ->scalar('nome')
            ->maxLength('nome', 255)
            ->allowEmptyString('nome');

        $validator
            ->scalar('resumo')
            ->allowEmptyString('resumo');

        $validator
            ->scalar('tipo')
            ->allowEmptyString('tipo');

        $validator
            ->scalar('arquivo')
            ->maxLength('arquivo', 150)
            ->allowEmptyString('arquivo');

        $validator
            ->allowEmptyString('liberado');

        $validator
            ->integer('horas')
            ->allowEmptyString('horas');

        $validator
            ->integer('cursos_oferecido_id')
            ->allowEmptyString('cursos_oferecido_id');

        $validator
            ->integer('independente')
            ->allowEmptyString('independente');

        $validator
            ->scalar('docente')
            ->maxLength('docente', 45)
            ->allowEmptyString('docente');

        $validator
            ->integer('cursos_modulo_id')
            ->allowEmptyString('cursos_modulo_id');

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
        $rules->add($rules->existsIn(['cursos_oferecido_id'], 'CursosOferecidos'), ['errorField' => 'cursos_oferecido_id']);
        $rules->add($rules->existsIn(['cursos_modulo_id'], 'CursosModulos'), ['errorField' => 'cursos_modulo_id']);

        return $rules;
    }
}
