<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;


class UnidadesTable extends Table
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

        $this->setTable('unidades');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Instituicaos', [
            'foreignKey' => 'instituicao_id',
        ]);
        $this->belongsTo('Streets', [
            'foreignKey' => 'street_id',
        ]);
        $this->hasMany('Avaliadors', [
            'foreignKey' => 'unidade_id',
        ]);
        $this->hasMany('Editais', [
            'foreignKey' => 'unidade_id',
        ]);
        $this->hasMany('Raics', [
            'foreignKey' => 'unidade_id',
        ]);
        $this->hasMany('UnidadeEquipes', [
            'foreignKey' => 'unidade_id',
        ]);
        $this->hasMany('UsuarioHistoricos', [
            'foreignKey' => 'unidade_id',
        ]);
        $this->hasMany('Usuarios', [
            'foreignKey' => 'unidade_id',
        ]);
        $this->hasMany('Workshops', [
            'foreignKey' => 'unidade_id',
        ]);
        $this->belongsTo('Coordenadores', [
            'className' => 'Usuarios',
            'foreignKey' => 'coordenador',
            'joinType' => 'LEFT',
        ]);
        $this->belongsTo('Subcoordenadores', [
            'className' => 'Usuarios',
            'foreignKey' => 'subcoordenador',
            'joinType' => 'LEFT',
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
            ->scalar('sigla')
            ->maxLength('sigla', 45)
            ->allowEmptyString('sigla');

        $validator
            ->integer('instituicao_id')
            ->allowEmptyString('instituicao_id');

        $validator
            ->integer('coordenador')
            ->allowEmptyString('coordenador');

        $validator
            ->scalar('logo')
            ->maxLength('logo', 45)
            ->allowEmptyString('logo');

        $validator
            ->integer('street_id')
            ->allowEmptyString('street_id');

        $validator
            ->scalar('numero')
            ->maxLength('numero', 45)
            ->allowEmptyString('numero');

        $validator
            ->scalar('telefone')
            ->maxLength('telefone', 45)
            ->allowEmptyString('telefone');

        $validator
            ->email('email')
            ->allowEmptyString('email');

        $validator
            ->scalar('texto')
            ->allowEmptyString('texto');

        $validator
            ->scalar('whatsapp')
            ->maxLength('whatsapp', 45)
            ->allowEmptyString('whatsapp');

        $validator
            ->integer('subcoordenador')
            ->allowEmptyString('subcoordenador');

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
        //$rules->add($rules->existsIn(['instituicao_id'], 'Instituicaos'), ['errorField' => 'instituicao_id']);
        //$rules->add($rules->existsIn(['street_id'], 'Streets'), ['errorField' => 'street_id']);

        return $rules;
    }
}
