<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class UsuarioHistoricosTable extends Table
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

        $this->setTable('usuarios_historicos');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Usuarios', [
            'foreignKey' => 'usuario_id',
            'joinType' => 'LEFT',
        ]);

        $this->belongsTo('Alterador', [
            'className' => 'Usuarios',
            'foreignKey' => 'alterado_por',
            'joinType' => 'LEFT',
        ]);

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created' => 'new',
                ],
            ],
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
            ->integer('alterado_por')
            ->allowEmptyString('alterado_por');

        $validator
            ->scalar('contexto')
            ->maxLength('contexto', 1)
            ->allowEmptyString('contexto');

        $validator
            ->scalar('origem_acesso')
            ->maxLength('origem_acesso', 1)
            ->allowEmptyString('origem_acesso');

        $validator
            ->allowEmptyString('diff_json');

        return $validator;
    }
}
