<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Convites Model
 *
 * @property \App\Model\Table\UsuariosTable&\Cake\ORM\Association\BelongsTo $Usuarios
 * @property \App\Model\Table\UsuariosTable&\Cake\ORM\Association\BelongsTo $Cadastradores
 * @property \App\Model\Table\UsuariosTable&\Cake\ORM\Association\BelongsTo $Deletadores
 *
 * @method \App\Model\Entity\Convite newEmptyEntity()
 * @method \App\Model\Entity\Convite newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Convite> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Convite get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Convite patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Convite> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Convite|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Convite saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 */
class ConvitesTable extends Table
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

        $this->setTable('convites');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Usuarios', [
            'foreignKey' => 'usuario_id',
            'joinType' => 'LEFT',
        ]);
        $this->belongsTo('Cadastradores', [
            'className' => 'Usuarios',
            'foreignKey' => 'cadastrado_por',
            'joinType' => 'LEFT',
            'propertyName' => 'cadastrador',
        ]);
        $this->belongsTo('Deletadores', [
            'className' => 'Usuarios',
            'foreignKey' => 'deletado_por',
            'joinType' => 'LEFT',
            'propertyName' => 'deletador',
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
            ->nonNegativeInteger('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->nonNegativeInteger('usuario_id')
            ->allowEmptyString('usuario_id');

        $validator
            ->integer('ano')
            ->allowEmptyString('ano');

        $validator
            ->integer('aceite')
            ->allowEmptyString('aceite');

        $validator
            ->scalar('editais')
            ->maxLength('editais', 45)
            ->allowEmptyString('editais');

        $validator
            ->nonNegativeInteger('cadastrado_por')
            ->allowEmptyString('cadastrado_por');

        $validator
            ->nonNegativeInteger('deletado_por')
            ->allowEmptyString('deletado_por');

        $validator
            ->dateTime('deleted')
            ->allowEmptyDateTime('deleted');

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
        $rules->add($rules->existsIn(['cadastrado_por'], 'Cadastradores'), ['errorField' => 'cadastrado_por']);
        $rules->add($rules->existsIn(['deletado_por'], 'Deletadores'), ['errorField' => 'deletado_por']);

        return $rules;
    }
}
