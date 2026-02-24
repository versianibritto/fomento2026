<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class TiExceptionsTable extends Table
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

        $this->setTable('ti_exceptions');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('TiTipos', [
            'foreignKey' => 'classificacao_id',
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
            ->scalar('status')
            ->maxLength('status', 1)
            ->allowEmptyString('status');

        $validator
            ->integer('classificacao_id')
            ->allowEmptyString('classificacao_id');

        $validator
            ->integer('usuario_id')
            ->allowEmptyString('usuario_id');

        $validator
            ->scalar('usuario_nome')
            ->maxLength('usuario_nome', 150)
            ->allowEmptyString('usuario_nome');

        $validator
            ->email('usuario_email')
            ->allowEmptyString('usuario_email');

        $validator
            ->email('usuario_email_alternativo')
            ->allowEmptyString('usuario_email_alternativo');

        $validator
            ->email('usuario_email_contato')
            ->allowEmptyString('usuario_email_contato');

        $validator
            ->scalar('url')
            ->maxLength('url', 255)
            ->allowEmptyString('url');

        $validator
            ->scalar('host')
            ->maxLength('host', 150)
            ->allowEmptyString('host');

        $validator
            ->allowEmptyString('mensagem');

        $validator
            ->scalar('arquivo')
            ->maxLength('arquivo', 255)
            ->allowEmptyString('arquivo');

        $validator
            ->integer('linha')
            ->allowEmptyString('linha');

        $validator
            ->scalar('hash')
            ->maxLength('hash', 64)
            ->allowEmptyString('hash');

        $validator
            ->integer('repeticoes')
            ->allowEmptyString('repeticoes');

        $validator
            ->boolean('repeticao')
            ->allowEmptyString('repeticao');

        $validator
            ->integer('repeticao_de_id')
            ->allowEmptyString('repeticao_de_id');

        $validator
            ->dateTime('ultima_ocorrencia')
            ->allowEmptyDateTime('ultima_ocorrencia');

        $validator
            ->allowEmptyString('resposta');

        $validator
            ->integer('respondido_por')
            ->allowEmptyString('respondido_por');

        $validator
            ->dateTime('respondido_em')
            ->allowEmptyDateTime('respondido_em');

        return $validator;
    }
}
