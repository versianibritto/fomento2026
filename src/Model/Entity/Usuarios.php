<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Authentication\IdentityInterface;
use Cake\ORM\Entity;


class User extends Entity 
{
    
    protected array $_accessible = [
        'nome' => true,
        'cpf' => true,
        'email' => true,
        'password' => true,
        'documento' => true,
        'documento_numero' => true,
        'documento_emissor' => true,
        'documento_uf_emissor' => true,
        'documento_emissao' => true,
        'data_nascimento' => true,
        'sexo' => true,
        'lattes' => true,
        'telefone' => true,
        'telefone_contato' => true,
        'celular' => true,
        'whatsapp' => true,
        'street_id' => true,
        'numero' => true,
        'complemento' => true,
        'created' => true,
        'modified' => true,
        'ultimoLogin' => true,
        'loginAtual' => true,
        'active' => true,
        'yoda' => true,
        'jedi' => true,
        'password_reset_token' => true,
        'password_reset_token_validade' => true,
        'escolaridade_id' => true,
        'curso' => true,
        'ano_conclusao' => true,
        'em_curso' => true,
        'vinculo_id' => true,
        'matricula_siape' => true,
        'instituicao_curso' => true,
        'unidade_id' => true,
        'street' => true,
        'escolaridade' => true,
        'unidade' => true,
        'nome_social' => true,
        'last_data_update_date' => true,
        'raca'=>true,
        'vinculo' => true,
        'departamento' => true,
        'laboratorio' => true,
        'ic' => true,
        'email_alternativo' => true,
        'email_contato' => true,
        'deficiencia' => true,
        'padauan' => true,






    ];

    
    protected array $_hidden = [
        'password',
    ];


    /**
     *  Authentication\IndentityInterface method
     */
    public function getOriginalData()
    {
        return $this;
    }

    public function getIdentifier()
    {
        return $this->id;
    }

    /*
    protected function _setPassword(string $password) : ?string
    {
        if (strlen($password) > 0) {
            return (new DefaultPasswordHasher())->hash($password);
        }
    }
        */
    protected function _setPassword(string $password): ?string
    {
        if (strlen($password) > 0) {
            return (new DefaultPasswordHasher())->hash($password);
        }

        return null;
    }


    protected function _setCpf(string $cpf) : ?string
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
        return $cpf;
    }
}
