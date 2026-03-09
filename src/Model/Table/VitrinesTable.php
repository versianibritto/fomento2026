<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class VitrinesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('vitrines');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Erratas', [
            'foreignKey' => 'vitrine_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->nonNegativeInteger('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->dateTime('deleted')
            ->allowEmptyDateTime('deleted');

        $validator
            ->dateTime('created')
            ->allowEmptyDateTime('created');

        $validator
            ->dateTime('modified')
            ->allowEmptyDateTime('modified');

        $validator
            ->scalar('nome')
            ->allowEmptyString('nome');

        $validator
            ->scalar('anexo_edital')
            ->maxLength('anexo_edital', 45)
            ->allowEmptyString('anexo_edital');

        $validator
            ->scalar('anexo_resultado')
            ->maxLength('anexo_resultado', 45)
            ->allowEmptyString('anexo_resultado');

        $validator
            ->scalar('anexo_resultado_recurso')
            ->maxLength('anexo_resultado_recurso', 45)
            ->allowEmptyString('anexo_resultado_recurso');

        $validator
            ->scalar('anexo_modelo_relatorio')
            ->maxLength('anexo_modelo_relatorio', 45)
            ->allowEmptyString('anexo_modelo_relatorio');

        $validator
            ->scalar('anexo_modelo_consentimento')
            ->maxLength('anexo_modelo_consentimento', 45)
            ->allowEmptyString('anexo_modelo_consentimento');

        $validator
            ->dateTime('divulgacao')
            ->allowEmptyDateTime('divulgacao');

        $validator
            ->dateTime('inicio')
            ->allowEmptyDateTime('inicio');

        $validator
            ->dateTime('fim')
            ->allowEmptyDateTime('fim');

        $validator
            ->scalar('obs')
            ->allowEmptyString('obs');

        return $validator;
    }
}
