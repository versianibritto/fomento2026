<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Streets Model
 *
 * @property \App\Model\Table\DistrictsTable&\Cake\ORM\Association\BelongsTo $Districts
 * @property \App\Model\Table\UsuariosTable&\Cake\ORM\Association\HasMany $Usuarios
 *
 * @method \App\Model\Entity\Street newEmptyEntity()
 * @method \App\Model\Entity\Street newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Street[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Street get($primaryKey, $options = [])
 * @method \App\Model\Entity\Street findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Street patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Street[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Street|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Street saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Street[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Street[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Street[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Street[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class StreetsTable extends Table
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

        $this->setTable('streets');
        $this->setDisplayField('nome');
        $this->setPrimaryKey('id');

        $this->belongsTo('Districts', [
            'foreignKey' => 'district_id',
        ]);
        $this->hasMany('Unidades', [
            'foreignKey' => 'street_id',
        ]);
        $this->hasMany('Users', [
            'foreignKey' => 'street_id',
        ]);
        $this->hasMany('Usuarios', [
            'foreignKey' => 'street_id',
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
            ->maxLength('nome', 255)
            ->allowEmptyString('nome');

        $validator
            ->scalar('cep')
            ->maxLength('cep', 20)
            ->allowEmptyString('cep');

        $validator
            ->integer('district_id')
            ->allowEmptyString('district_id');

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
        $rules->add($rules->existsIn('district_id', 'Districts'), ['errorField' => 'district_id']);

        return $rules;
    }
}
