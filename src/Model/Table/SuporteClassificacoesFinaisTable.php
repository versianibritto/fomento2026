<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class SuporteClassificacoesFinaisTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('suporte_classificacoes_finais');
        $this->setDisplayField('nome');
        $this->setPrimaryKey('id');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('nome')
            ->maxLength('nome', 80)
            ->notEmptyString('nome');

        $validator
            ->boolean('ativo')
            ->notEmptyString('ativo');

        return $validator;
    }
}
