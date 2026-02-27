<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Geral Model
 *
 * @method \App\Model\Entity\Geral newEmptyEntity()
 * @method \App\Model\Entity\Geral newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Geral> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Geral get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Geral findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Geral patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Geral> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Geral|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Geral saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Geral>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Geral>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Geral>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Geral> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Geral>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Geral>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Geral>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Geral> deleteManyOrFail(iterable $entities, array $options = [])
 */
class GeralTable extends Table
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

        $this->setTable('geral');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
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
            ->nonNegativeInteger('id')
            ->notEmptyString('id');

        $validator
            ->allowEmptyString('bolsista');

        $validator
            ->scalar('nome_bolsista')
            ->allowEmptyString('nome_bolsista');

        $validator
            ->scalar('social_bolsista')
            ->allowEmptyString('social_bolsista');

        $validator
            ->scalar('sexo')
            ->maxLength('sexo', 1)
            ->allowEmptyString('sexo');

        $validator
            ->scalar('cpf_bolsista')
            ->maxLength('cpf_bolsista', 20)
            ->allowEmptyString('cpf_bolsista');

        $validator
            ->scalar('documento')
            ->maxLength('documento', 45)
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
            ->scalar('nascimento')
            ->allowEmptyString('nascimento');

        $validator
            ->scalar('telefone')
            ->maxLength('telefone', 45)
            ->allowEmptyString('telefone');

        $validator
            ->scalar('telefone_contato')
            ->maxLength('telefone_contato', 45)
            ->allowEmptyString('telefone_contato');

        $validator
            ->scalar('celular')
            ->maxLength('celular', 45)
            ->allowEmptyString('celular');

        $validator
            ->scalar('whatsapp')
            ->maxLength('whatsapp', 45)
            ->allowEmptyString('whatsapp');

        $validator
            ->scalar('cep')
            ->maxLength('cep', 20)
            ->allowEmptyString('cep');

        $validator
            ->scalar('rua')
            ->allowEmptyString('rua');

        $validator
            ->scalar('complemento')
            ->maxLength('complemento', 150)
            ->allowEmptyString('complemento');

        $validator
            ->scalar('bairro')
            ->allowEmptyString('bairro');

        $validator
            ->scalar('cidade')
            ->allowEmptyString('cidade');

        $validator
            ->scalar('estado')
            ->maxLength('estado', 5)
            ->allowEmptyString('estado');

        $validator
            ->scalar('email_bolsista')
            ->maxLength('email_bolsista', 150)
            ->allowEmptyString('email_bolsista');

        $validator
            ->scalar('email_alternativo_bolsista')
            ->maxLength('email_alternativo_bolsista', 150)
            ->allowEmptyString('email_alternativo_bolsista');

        $validator
            ->scalar('email_contato_bolsista')
            ->maxLength('email_contato_bolsista', 150)
            ->allowEmptyString('email_contato_bolsista');

        $validator
            ->scalar('curso')
            ->maxLength('curso', 150)
            ->allowEmptyString('curso');

        $validator
            ->allowEmptyString('orientador');

        $validator
            ->scalar('nome_orientador')
            ->allowEmptyString('nome_orientador');

        $validator
            ->scalar('social_orientador')
            ->allowEmptyString('social_orientador');

        $validator
            ->scalar('telefone_orientador')
            ->maxLength('telefone_orientador', 45)
            ->allowEmptyString('telefone_orientador');

        $validator
            ->scalar('telefone_contato_orientador')
            ->maxLength('telefone_contato_orientador', 45)
            ->allowEmptyString('telefone_contato_orientador');

        $validator
            ->scalar('celular_orientador')
            ->maxLength('celular_orientador', 45)
            ->allowEmptyString('celular_orientador');

        $validator
            ->scalar('whatsapp_orientador')
            ->maxLength('whatsapp_orientador', 45)
            ->allowEmptyString('whatsapp_orientador');

        $validator
            ->scalar('email_orientador')
            ->maxLength('email_orientador', 150)
            ->allowEmptyString('email_orientador');

        $validator
            ->scalar('email_alternativo_orientador')
            ->maxLength('email_alternativo_orientador', 150)
            ->allowEmptyString('email_alternativo_orientador');

        $validator
            ->scalar('email_contato_orientador')
            ->maxLength('email_contato_orientador', 150)
            ->allowEmptyString('email_contato_orientador');

        $validator
            ->allowEmptyString('unidade_id');

        $validator
            ->scalar('unidade_orientador')
            ->allowEmptyString('unidade_orientador');

        $validator
            ->allowEmptyString('vinculo_orientador_id');

        $validator
            ->scalar('vinculo_orientador')
            ->allowEmptyString('vinculo_orientador');

        $validator
            ->allowEmptyString('coorientador');

        $validator
            ->scalar('nome_coorientador')
            ->allowEmptyString('nome_coorientador');

        $validator
            ->scalar('social_coorientador')
            ->allowEmptyString('social_coorientador');

        $validator
            ->scalar('telefone_coorientador')
            ->maxLength('telefone_coorientador', 45)
            ->allowEmptyString('telefone_coorientador');

        $validator
            ->scalar('telefone_contato_coorientador')
            ->maxLength('telefone_contato_coorientador', 45)
            ->allowEmptyString('telefone_contato_coorientador');

        $validator
            ->scalar('celular_coorientador')
            ->maxLength('celular_coorientador', 45)
            ->allowEmptyString('celular_coorientador');

        $validator
            ->scalar('whatsapp_coorientador')
            ->maxLength('whatsapp_coorientador', 45)
            ->allowEmptyString('whatsapp_coorientador');

        $validator
            ->scalar('email_coorientador')
            ->maxLength('email_coorientador', 150)
            ->allowEmptyString('email_coorientador');

        $validator
            ->scalar('email_alternativo_coorientador')
            ->maxLength('email_alternativo_coorientador', 150)
            ->allowEmptyString('email_alternativo_coorientador');

        $validator
            ->scalar('email_contato_coorientador')
            ->maxLength('email_contato_coorientador', 150)
            ->allowEmptyString('email_contato_coorientador');

        $validator
            ->allowEmptyString('unidade_id_coorientador');

        $validator
            ->scalar('unidade_coorientador')
            ->allowEmptyString('unidade_coorientador');

        $validator
            ->allowEmptyString('vinculo_coorientador_id');

        $validator
            ->scalar('vinculo_coorientador')
            ->allowEmptyString('vinculo_coorientador');

        $validator
            ->allowEmptyString('projeto_id');

        $validator
            ->scalar('projeto_orientador')
            ->allowEmptyString('projeto_orientador');

        $validator
            ->scalar('grande_area')
            ->allowEmptyString('grande_area');

        $validator
            ->scalar('area')
            ->allowEmptyString('area');

        $validator
            ->scalar('area_fiocruz')
            ->allowEmptyString('area_fiocruz');

        $validator
            ->scalar('linha')
            ->allowEmptyString('linha');

        $validator
            ->scalar('titulo_subprojeto')
            ->allowEmptyString('titulo_subprojeto');

        $validator
            ->allowEmptyString('programa_id');

        $validator
            ->scalar('programa_nome')
            ->allowEmptyString('programa_nome');

        $validator
            ->allowEmptyString('editai_id');

        $validator
            ->scalar('editai_nome')
            ->allowEmptyString('editai_nome');

        $validator
            ->scalar('inicio_vigencia')
            ->allowEmptyString('inicio_vigencia');

        $validator
            ->scalar('fim_vigencia')
            ->allowEmptyString('fim_vigencia');

        $validator
            ->scalar('ed_controller')
            ->allowEmptyString('ed_controller');

        $validator
            ->scalar('cota')
            ->maxLength('cota', 2)
            ->allowEmptyString('cota');

        $validator
            ->integer('fase_id')
            ->allowEmptyString('fase_id');

        $validator
            ->scalar('fase_nome')
            ->allowEmptyString('fase_nome');

        $validator
            ->scalar('filhos_menor')
            ->maxLength('filhos_menor', 1)
            ->allowEmptyString('filhos_menor');

        $validator
            ->scalar('origem')
            ->maxLength('origem', 1)
            ->allowEmptyString('origem');

        $validator
            ->integer('prorrogacao')
            ->allowEmptyString('prorrogacao');

        $validator
            ->integer('autorizacao')
            ->allowEmptyString('autorizacao');

        $validator
            ->integer('primeiro_periodo')
            ->allowEmptyString('primeiro_periodo');

        $validator
            ->scalar('resultado')
            ->maxLength('resultado', 1)
            ->allowEmptyString('resultado');

        $validator
            ->scalar('created')
            ->allowEmptyString('created');

        $validator
            ->scalar('data_inicio')
            ->allowEmptyString('data_inicio');

        $validator
            ->allowEmptyString('vigente');

        $validator
            ->scalar('tipo_bolsa')
            ->maxLength('tipo_bolsa', 1)
            ->allowEmptyString('tipo_bolsa');

        $validator
            ->scalar('justificativa_cancelamento')
            ->allowEmptyString('justificativa_cancelamento');

        $validator
            ->scalar('deleted')
            ->allowEmptyString('deleted');

        $validator
            ->integer('area_pdj')
            ->allowEmptyString('area_pdj');

        $validator
            ->scalar('area_pdj_nome')
            ->allowEmptyString('area_pdj_nome');

        $validator
            ->integer('bolsista_anterior')
            ->allowEmptyString('bolsista_anterior');

        $validator
            ->integer('referencia_inscricao_anterior')
            ->allowEmptyString('referencia_inscricao_anterior');

        $validator
            ->integer('troca_projeto')
            ->allowEmptyString('troca_projeto');

        $validator
            ->integer('heranca')
            ->allowEmptyString('heranca');

        return $validator;
    }
}
