<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class UsuariosTable extends Table
{
   
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('usuarios');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

       
        $this->addBehavior('Timestamp');

      
        $this->belongsTo('Streets', [
            'foreignKey' => 'street_id',
        ]);
        $this->belongsTo('Escolaridades', [
            'foreignKey' => 'escolaridade_id',
        ]);
        $this->belongsTo('Unidades', [
            'foreignKey' => 'unidade_id',
        ]);
        $this->belongsTo('Instituicaos', [
            'foreignKey' => 'instituicao_curso',
        ]);
        $this->hasMany('Projetos', [
            'foreignKey' => 'usuario_id',
        ]);
        $this->hasMany('AulaBolsistas', [
            'foreignKey' => 'usuario_id',
        ]);
        
        $this->belongsTo('Vinculos', [
            'foreignKey' => 'vinculo_id',
        ]);
    }

  
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->nonNegativeInteger('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('nome')
            ->maxLength('nome', 150)
            ->allowEmptyString('nome');

        $validator
            ->scalar('cpf')
            ->maxLength('cpf', 25)
            ->requirePresence('cpf', 'create')
            ->notEmptyString('cpf');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email');
        
        $validator
            ->email('email_alternativo')
            ->allowEmptyString('email_alternativo');

        $validator
            ->email('email_contato')
            ->allowEmptyString('email_contato');

        $validator
            ->scalar('password')
            ->maxLength('password', 150)
            ->allowEmptyString('password');

        $validator
            ->scalar('documento')
            ->allowEmptyString('documento');

        $validator
            ->scalar('documento_numero')
            ->maxLength('documento_numero', 45)
            ->allowEmptyString('documento_numero');

        $validator
            ->scalar('documento_emissor')
            ->maxLength('documento_emissor', 45)
            ->allowEmptyString('documento_emissor');

        $validator
            ->scalar('documento_uf_emissor')
            ->maxLength('documento_uf_emissor', 45)
            ->allowEmptyString('documento_uf_emissor');

        $validator
            ->date('documento_emissao')
            ->allowEmptyDate('documento_emissao');

        $validator
            ->date('data_nascimento')
            ->allowEmptyDate('data_nascimento');

        $validator
            ->scalar('sexo')
            ->allowEmptyString('sexo');

        $validator
            ->scalar('filhos_menor')
            ->maxLength('filhos_menor', 1)
            ->allowEmptyString('filhos_menor');

        $validator
            ->scalar('lattes')
            ->maxLength('lattes', 255)
            ->allowEmptyString('lattes');

        $validator
            ->scalar('telefone')
            ->maxLength('telefone', 25)
            ->allowEmptyString('telefone');

        $validator
            ->scalar('telefone_contato')
            ->maxLength('telefone_contato', 25)
            ->allowEmptyString('telefone_contato');

        $validator
            ->scalar('celular')
            ->maxLength('celular', 25)
            ->allowEmptyString('celular');

        $validator
            ->scalar('whatsapp')
            ->maxLength('whatsapp', 25)
            ->allowEmptyString('whatsapp');

        $validator
            ->scalar('numero')
            ->maxLength('numero', 50)
            ->allowEmptyString('numero');

        $validator
            ->scalar('complemento')
            ->maxLength('complemento', 50)
            ->allowEmptyString('complemento');

        $validator
            ->dateTime('ultimoLogin')
            ->allowEmptyDateTime('ultimoLogin');

        $validator
            ->dateTime('loginAtual')
            ->allowEmptyDateTime('loginAtual');

        $validator
            ->allowEmptyString('active');

        $validator
            ->boolean('yoda')
            ->notEmptyString('yoda');

        $validator
            ->scalar('jedi')
            ->maxLength('jedi', 300)
            ->allowEmptyString('jedi');

        $validator
            ->scalar('padauan')
            ->maxLength('padauan', 300)
            ->allowEmptyString('padauan');

        $validator
            ->scalar('password_reset_token')
            ->maxLength('password_reset_token', 120)
            ->allowEmptyString('password_reset_token');

        $validator
            ->dateTime('password_reset_token_validade')
            ->allowEmptyDateTime('password_reset_token_validade');

        $validator
            ->scalar('curso')
            ->maxLength('curso', 45)
            ->allowEmptyString('curso');

        $validator
            ->scalar('ano_conclusao')
            ->maxLength('ano_conclusao', 4)
            ->allowEmptyString('ano_conclusao');

        $validator
            ->boolean('em_curso')
            ->allowEmptyString('em_curso');

        $validator
            ->scalar('instituicao_curso')
            ->maxLength('instituicao_curso', 120)
            ->allowEmptyString('instituicao_curso');


        $validator
            ->scalar('nome_social')
            ->maxLength('nome_social', 255)
            ->allowEmptyString('nome_social');

        $validator
            ->scalar('raca')
            ->allowEmptyString('raca');
        
        $validator
            ->scalar('matricula_siape')
            ->maxLength('matricula_siape', 45)
            ->allowEmptyString('matricula_siape');

        $validator
            ->scalar('departamento')
            ->maxLength('departamento', 45)
            ->allowEmptyString('departamento');

        $validator
            ->scalar('laboratorio')
            ->maxLength('laboratorio', 45)
            ->allowEmptyString('laboratorio');
        
        $validator
            ->scalar('ic')
            ->allowEmptyString('ic');

        $validator
            ->scalar('deficiencia')
            ->allowEmptyString('deficiencia');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['cpf']), ['errorField' => 'cpf']);

        return $rules;
    }
}
