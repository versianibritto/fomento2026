<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class UsuariosAcessosTable extends Table
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

        $this->setTable('usuarios_acessos');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Usuarios', [
            'foreignKey' => 'usuario_id',
            'joinType' => 'LEFT',
        ]);

        $this->addBehavior('Timestamp');
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
            ->scalar('tipo_acesso')
            ->maxLength('tipo_acesso', 1)
            ->allowEmptyString('tipo_acesso');

        $validator
            ->scalar('nome')
            ->maxLength('nome', 255)
            ->allowEmptyString('nome');

        $validator
            ->email('email')
            ->allowEmptyString('email');

        $validator
            ->email('email_alternativo')
            ->allowEmptyString('email_alternativo');

        $validator
            ->scalar('acao')
            ->maxLength('acao', 1)
            ->allowEmptyString('acao');

        return $validator;
    }
}
