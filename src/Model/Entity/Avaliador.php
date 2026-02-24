<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Avaliador Entity
 *
 * @property int $id
 * @property int|null $usuario_id
 * @property int|null $grandes_area_id
 * @property int|null $area_id
 * @property int|null $sub_area_id
 * @property int|null $especialidade_id
 * @property int|null $areas_fiocruz_id
 * @property int|null $linha_id
 * @property string|null $ano_convite
 * @property string|null $ano_aceite
 * @property string|null $voluntario
 * @property int|null $unidade_id
 * @property string|null $tipo_avaliador
 * @property int $deleted
 * @property int|null $editai_id
 * @property int $aceite
 *
 * @property \App\Model\Entity\Usuario $usuario
 * @property \App\Model\Entity\GrandesArea $grandes_area
 * @property \App\Model\Entity\Area $area
 * @property \App\Model\Entity\SubArea $sub_area
 * @property \App\Model\Entity\Especialidade $especialidade
 * @property \App\Model\Entity\Linha $linha
 * @property \App\Model\Entity\Unidade $unidade
 * @property \App\Model\Entity\Editai $editai
 * @property \App\Model\Entity\AvaliadorBolsista[] $avaliador_bolsistas
 * @property \App\Model\Entity\AvaliadorProjeto[] $avaliador_projetos
 * @property \App\Model\Entity\BancaUsuario[] $banca_usuarios
 */
class Avaliador extends Entity
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
        'grandes_area_id' => true,
        'area_id' => true,
        'sub_area_id' => true,
        'especialidade_id' => true,
        'areas_fiocruz_id' => true,
        'linha_id' => true,
        'ano_convite' => true,
        'ano_aceite' => true,
        'voluntario' => true,
        'unidade_id' => true,
        'tipo_avaliador' => true,
        'deleted' => true,
        'editai_id' => true,
        'aceite' => true,
        'usuario' => true,
        'grandes_area' => true,
        'area' => true,
        'sub_area' => true,
        'especialidade' => true,
        'linha' => true,
        'unidade' => true,
        'editai' => true,
        'avaliador_bolsistas' => true,
        'avaliador_projetos' => true,
        'banca_usuarios' => true,
    ];
}
