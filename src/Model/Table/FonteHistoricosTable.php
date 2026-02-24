<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * FonteHistoricos Model
 *
 * @property \App\Model\Table\UsuariosTable&\Cake\ORM\Association\BelongsTo $Usuarios
 *
 * @method \App\Model\Entity\FonteHistorico newEmptyEntity()
 * @method \App\Model\Entity\FonteHistorico newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\FonteHistorico[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\FonteHistorico get($primaryKey, $options = [])
 * @method \App\Model\Entity\FonteHistorico findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\FonteHistorico patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\FonteHistorico[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\FonteHistorico|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FonteHistorico saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FonteHistorico[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FonteHistorico[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\FonteHistorico[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FonteHistorico[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class FonteHistoricosTable extends Table
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

        $this->setTable('fonte_historicos');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Usuarios', [
            'foreignKey' => 'usuario_id',
        ]);
        $this->belongsTo('ProjetoBolsistas', [
            'foreignKey' => 'projeto_bolsista_id',
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
            ->scalar('fonte_original')
            ->allowEmptyString('fonte_original');

        $validator
            ->scalar('fonte_atual')
            ->allowEmptyString('fonte_atual');

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
        $rules->add($rules->existsIn('usuario_id', 'Usuarios'), ['errorField' => 'usuario_id']);
        $rules->add($rules->existsIn('projeto_bolsista_id', 'ProjetoBolsistas'), ['errorField' => 'projeto_bolsista_id']);

        return $rules;
    }
}
