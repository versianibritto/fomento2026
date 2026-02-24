<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Raic Entity
 *
 * @property int $id
 * @property int|null $usuario_id
 * @property int|null $orientador
 * @property int|null $coorientador
 * @property int|null $projeto_orientador
 * @property string|null $resumo
 * @property string|null $objetivos
 * @property string|null $descricao
 * @property string|null $titulo
 * @property string|null $relatorio
 * @property string|null $anexo
 * @property \Cake\I18n\DateTime|null $created
 * @property string|null $tipo_bolsa
 * @property \Cake\I18n\Date|null $data_apresentacao
 * @property string|null $local_apresentacao
 * @property string|null $tipo_apresentacao
 * @property float|null $nota_final
 * @property string|null $presenca
 * @property string|null $observacao_avaliador
 * @property int|null $destaque
 * @property int|null $indicado_premio_capes
 * @property int|null $projeto_bolsista_id
 * @property int $deleted
 * @property int|null $unidade_id
 * @property int|null $editai_id
 * @property int|null $usuario_cadastro
 * @property int|null $usuario_libera
 * @property \Cake\I18n\DateTime|null $data_liberacao
 *
 * @property \App\Model\Entity\Usuario $usuario
 * @property \App\Model\Entity\ProjetoBolsista $projeto_bolsista
 * @property \App\Model\Entity\Unidade $unidade
 * @property \App\Model\Entity\Editai $editai
 * @property \App\Model\Entity\Gabarito[] $gabaritos
 * @property \App\Model\Entity\ProjetoAnexo[] $projeto_anexos
 * @property \App\Model\Entity\RaicHistorico[] $raic_historicos
 */
class Raic extends Entity
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
        'usuario_id' => true,
        'orientador' => true,
        'coorientador' => true,
        'projeto_orientador' => true,
        'resumo' => true,
        'objetivos' => true,
        'descricao' => true,
        'titulo' => true,
        'relatorio' => true,
        'anexo' => true,
        'created' => true,
        'tipo_bolsa' => true,
        'data_apresentacao' => true,
        'local_apresentacao' => true,
        'tipo_apresentacao' => true,
        'nota_final' => true,
        'presenca' => true,
        'observacao_avaliador' => true,
        'destaque' => true,
        'indicado_premio_capes' => true,
        'projeto_bolsista_id' => true,
        'deleted' => true,
        'unidade_id' => true,
        'editai_id' => true,
        'usuario_cadastro' => true,
        'usuario_libera' => true,
        'data_liberacao' => true,
        'usuario' => true,
        'projeto_bolsista' => true,
        'unidade' => true,
        'editai' => true,
        'gabaritos' => true,
        'projeto_anexos' => true,
        'raic_historicos' => true,
    ];
}
