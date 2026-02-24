<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * EditaisPrazos Model
 *
 * @property \App\Model\Table\EditaisTable&\Cake\ORM\Association\BelongsTo $Editais
 * @property \App\Model\Table\EditaisWksTable&\Cake\ORM\Association\BelongsTo $EditaisWks
 * @property \App\Model\Table\UsuariosTable&\Cake\ORM\Association\BelongsTo $Usuarios
 *
 * @method \App\Model\Entity\EditaisPrazo newEmptyEntity()
 * @method \App\Model\Entity\EditaisPrazo newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\EditaisPrazo> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EditaisPrazo get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\EditaisPrazo findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\EditaisPrazo patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\EditaisPrazo> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EditaisPrazo|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\EditaisPrazo saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\EditaisPrazo>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\EditaisPrazo>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\EditaisPrazo>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\EditaisPrazo> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\EditaisPrazo>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\EditaisPrazo>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\EditaisPrazo>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\EditaisPrazo> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class EditaisPrazosTable extends Table
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

        $this->setTable('editais_prazos');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Editais', [
            'foreignKey' => 'editai_id',
        ]);
        $this->belongsTo('EditaisWks', [
            'foreignKey' => 'editais_wk_id',
        ]);
        $this->belongsTo('Usuarios', [
            'foreignKey' => 'usuario_id',
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
            ->nonNegativeInteger('editai_id')
            ->allowEmptyString('editai_id');

        $validator
            ->nonNegativeInteger('usuario_id')
            ->allowEmptyString('usuario_id');

        $validator
            ->nonNegativeInteger('editais_wk_id')
            ->allowEmptyString('editais_wk_id');

        $validator
            ->scalar('cpf')
            ->maxLength('cpf', 300)
            ->allowEmptyString('cpf');

        $validator
            ->scalar('inscricao')
            ->maxLength('inscricao', 300)
            ->allowEmptyString('inscricao');

        $validator
            ->scalar('tabela')
            ->maxLength('tabela', 45)
            ->allowEmptyString('tabela');

        $validator
            ->dateTime('inicio')
            ->allowEmptyDateTime('inicio');

        $validator
            ->dateTime('fim')
            ->allowEmptyDateTime('fim');

        $validator
            ->allowEmptyString('deleted');

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
        $rules->add($rules->existsIn(['editai_id'], 'Editais'), ['errorField' => 'editai_id']);
        $rules->add($rules->existsIn(['editais_wk_id'], 'EditaisWks'), ['errorField' => 'editais_wk_id']);
        $rules->add($rules->existsIn(['usuario_id'], 'Usuarios'), ['errorField' => 'usuario_id']);

        return $rules;
    }
}
