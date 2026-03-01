<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class SuporteChamadosTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('suporte_chamados');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Usuarios', [
            'foreignKey' => 'usuario_id',
        ]);
        $this->belongsTo('Destinatarios', [
            'className' => 'Usuarios',
            'foreignKey' => 'destinatario_id',
        ]);
        $this->belongsTo('SuporteCategorias', [
            'foreignKey' => 'categoria_id',
        ]);
        $this->belongsTo('SuporteStatus', [
            'foreignKey' => 'status_id',
        ]);
        $this->belongsTo('SuporteClassificacoesFinais', [
            'foreignKey' => 'classificacao_final_id',
        ]);
        $this->belongsTo('ParentChamados', [
            'className' => 'SuporteChamados',
            'foreignKey' => 'parent_id',
        ]);
        $this->hasMany('ChildChamados', [
            'className' => 'SuporteChamados',
            'foreignKey' => 'parent_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('ramo')
            ->allowEmptyString('ramo');

        $validator
            ->integer('parent_id')
            ->allowEmptyString('parent_id');

        $validator
            ->integer('usuario_id')
            ->notEmptyString('usuario_id');

        $validator
            ->integer('destinatario_id')
            ->allowEmptyString('destinatario_id');

        $validator
            ->boolean('para_outro')
            ->notEmptyString('para_outro');

        $validator
            ->integer('categoria_id')
            ->allowEmptyString('categoria_id');

        $validator
            ->integer('status_id')
            ->notEmptyString('status_id');

        $validator
            ->integer('classificacao_final_id')
            ->allowEmptyString('classificacao_final_id');

        $validator
            ->scalar('origem')
            ->notEmptyString('origem');

        $validator
            ->scalar('texto')
            ->notEmptyString('texto');

        $validator
            ->scalar('anexo_1')
            ->maxLength('anexo_1', 255)
            ->allowEmptyString('anexo_1');

        $validator
            ->scalar('anexo_2')
            ->maxLength('anexo_2', 255)
            ->allowEmptyString('anexo_2');

        $validator
            ->scalar('anexo_3')
            ->maxLength('anexo_3', 255)
            ->allowEmptyString('anexo_3');

        $validator
            ->boolean('reaberto')
            ->notEmptyString('reaberto');

        $validator
            ->dateTime('finalizado')
            ->allowEmptyDateTime('finalizado');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        return $rules;
    }
}
