<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * BancaUsuarios Model
 *
 * @property \App\Model\Table\BancasTable&\Cake\ORM\Association\BelongsTo $Bancas
 * @property \App\Model\Table\AvaliadorsTable&\Cake\ORM\Association\BelongsTo $Avaliadors
 *
 * @method \App\Model\Entity\BancaUsuario newEmptyEntity()
 * @method \App\Model\Entity\BancaUsuario newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\BancaUsuario[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\BancaUsuario get($primaryKey, $options = [])
 * @method \App\Model\Entity\BancaUsuario findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\BancaUsuario patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\BancaUsuario[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\BancaUsuario|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\BancaUsuario saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\BancaUsuario[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\BancaUsuario[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\BancaUsuario[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\BancaUsuario[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class BancaUsuariosTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('banca_usuarios');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Bancas', [
            'foreignKey' => 'banca_id',
        ]);
        $this->belongsTo('Avaliadors', [
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
            ->integer('banca_id')
            ->allowEmptyString('banca_id');

        $validator
            ->dateTime('deleted')
            ->allowEmptyDateTime('deleted');

        $validator
            ->integer('avaliador_id')
            ->allowEmptyString('avaliador_id');

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
        $rules->add($rules->existsIn('banca_id', 'Bancas'), ['errorField' => 'banca_id']);
        $rules->add($rules->existsIn('avaliador_id', 'Avaliadors'), ['errorField' => 'avaliador_id']);

        return $rules;
    }
}
