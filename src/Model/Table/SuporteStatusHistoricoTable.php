<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class SuporteStatusHistoricoTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('suporte_status_historico');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('SuporteChamados', [
            'foreignKey' => 'suporte_id',
        ]);
        $this->belongsTo('Usuarios', [
            'foreignKey' => 'usuario_id',
        ]);
        $this->belongsTo('StatusAnterior', [
            'className' => 'SuporteStatus',
            'foreignKey' => 'status_anterior_id',
        ]);
        $this->belongsTo('StatusNovo', [
            'className' => 'SuporteStatus',
            'foreignKey' => 'status_novo_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('suporte_id')
            ->notEmptyString('suporte_id');

        $validator
            ->integer('ramo')
            ->allowEmptyString('ramo');

        $validator
            ->integer('usuario_id')
            ->notEmptyString('usuario_id');

        $validator
            ->integer('status_anterior_id')
            ->allowEmptyString('status_anterior_id');

        $validator
            ->integer('status_novo_id')
            ->notEmptyString('status_novo_id');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        return $rules;
    }
}
