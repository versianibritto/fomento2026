<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class MensagensTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('mensagens');
        $this->setDisplayField('titulo');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->nonNegativeInteger('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('titulo')
            ->allowEmptyString('titulo');

        $validator
            ->scalar('testo')
            ->allowEmptyString('testo');

        $validator
            ->scalar('imagem')
            ->maxLength('imagem', 255)
            ->allowEmptyString('imagem');

        $validator
            ->scalar('tipo')
            ->inList('tipo', ['I', 'E'])
            ->requirePresence('tipo', 'create')
            ->notEmptyString('tipo');

        $validator
            ->dateTime('inicio')
            ->allowEmptyDateTime('inicio');

        $validator
            ->dateTime('fim')
            ->allowEmptyDateTime('fim');

        $validator
            ->dateTime('deleted')
            ->allowEmptyDateTime('deleted');

        $validator
            ->dateTime('created')
            ->allowEmptyDateTime('created');

        $validator
            ->dateTime('modified')
            ->allowEmptyDateTime('modified');

        $validator->add('fim', 'periodoValido', [
            'rule' => function ($value, $context) {
                $inicio = $context['data']['inicio'] ?? null;
                if (empty($value) || empty($inicio)) {
                    return true;
                }

                return strtotime((string)$value) >= strtotime((string)$inicio);
            },
            'message' => 'A data final deve ser maior ou igual à data inicial.',
        ]);

        $validator->add('titulo', 'conteudoObrigatorio', [
            'rule' => function ($value, $context) {
                $titulo = trim((string)($context['data']['titulo'] ?? ''));
                $texto = trim((string)($context['data']['testo'] ?? ''));
                $imagem = $context['data']['imagem'] ?? null;

                $temImagem = false;
                if (is_string($imagem)) {
                    $temImagem = trim($imagem) !== '';
                } elseif (is_object($imagem) && method_exists($imagem, 'getClientFilename')) {
                    $temImagem = (string)$imagem->getClientFilename() !== '';
                }

                return $titulo !== '' || $texto !== '' || $temImagem;
            },
            'message' => 'Informe ao menos um título, texto HTML ou imagem para a mensagem.',
        ]);

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        return $rules;
    }
}
