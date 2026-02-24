<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Editai Entity
 *
 * @property int $id
 * @property string|null $nome
 * @property \Cake\I18n\DateTime|null $divulgacao
 * @property \Cake\I18n\DateTime|null $inicio_inscricao
 * @property \Cake\I18n\DateTime|null $fim_inscricao
 * @property \Cake\I18n\DateTime|null $resultado
 * @property \Cake\I18n\DateTime|null $inicio_recurso
 * @property \Cake\I18n\DateTime|null $fim_recurso
 * @property \Cake\I18n\DateTime|null $resultado_recurso
 * @property string|null $arquivo
 * @property string|null $origem
 * @property \Cake\I18n\DateTime|null $inicio_vigencia
 * @property \Cake\I18n\DateTime|null $fim_vigencia
 * @property int|null $limitaAnoDoutorado
 * @property string|null $resultado_arquivo
 * @property string|null $vinculos_permitidos
 * @property string|null $escolaridades_permitidas
 * @property int|null $usuario_id
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property int|null $deleted
 * @property string|null $unidades_permitidas
 * @property string|null $visualizar
 * @property \Cake\I18n\DateTime|null $inicio_avaliar
 * @property \Cake\I18n\DateTime|null $fim_avaliar
 * @property int|null $evento
 * @property int|null $programa_id
 * @property string|null $link
 * @property string|null $cpf_permitidos
 * @property string|null $modelo_cons_bols
 * @property string|null $modelo_cons_coor
 * @property string|null $modelo_relat_bols
 * @property string|null $controller
 *
 * @property \App\Model\Entity\Usuario $usuario
 * @property \App\Model\Entity\Programa $programa
 * @property \App\Model\Entity\AvaliadorBolsista[] $avaliador_bolsistas
 * @property \App\Model\Entity\Avaliador[] $avaliadors
 * @property \App\Model\Entity\Banca[] $bancas
 * @property \App\Model\Entity\Certificado[] $certificados
 * @property \App\Model\Entity\Errata[] $erratas
 * @property \App\Model\Entity\EditaisSumula[] $editais_sumulas
 * @property \App\Model\Entity\EditaisPrazo[] $editais_prazos
 * @property \App\Model\Entity\ProjetoBolsista[] $projeto_bolsistas
 * @property \App\Model\Entity\Question[] $questions
 * @property \App\Model\Entity\Raic[] $raics
 * @property \App\Model\Entity\Workshop[] $workshops
 */
class Editai extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'nome' => true,
        'divulgacao' => true,
        'inicio_inscricao' => true,
        'fim_inscricao' => true,
        'resultado' => true,
        'inicio_recurso' => true,
        'fim_recurso' => true,
        'resultado_recurso' => true,
        'arquivo' => true,
        'origem' => true,
        'inicio_vigencia' => true,
        'fim_vigencia' => true,
        'limitaAnoDoutorado' => true,
        'resultado_arquivo' => true,
        'vinculos_permitidos' => true,
        'escolaridades_permitidas' => true,
        'usuario_id' => true,
        'created' => true,
        'modified' => true,
        'deleted' => true,
        'unidades_permitidas' => true,
        'visualizar' => true,
        'inicio_avaliar' => true,
        'fim_avaliar' => true,
        'evento' => true,
        'programa_id' => true,
        'link' => true,
        'cpf_permitidos' => true,
        'modelo_cons_bols' => true,
        'modelo_cons_coor' => true,
        'modelo_relat_bols' => true,
        'controller' => true,
        'usuario' => true,
        'programa' => true,
        'avaliador_bolsistas' => true,
        'avaliadors' => true,
        'bancas' => true,
        'certificados' => true,
        'erratas' => true,
        'editais_sumulas' => true,
        'editais_prazos' => true,
        'projeto_bolsistas' => true,
        'questions' => true,
        'raics' => true,
        'workshops' => true,
    ];
}
