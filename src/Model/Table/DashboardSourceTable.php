<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Datasource\ConnectionManager;

class DashboardSourceTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        // Nome “falso” de tabela
        $this->setTable('dashboard_source'); 

        // Sem chave primária
        //$this->setPrimaryKey(null);
        $this->setPrimaryKey([]);


        // Desativa schema, Cake não tenta inspecionar colunas
        $this->setSchema([]);
    }


    public function findBase(Query $query, array $options)
    {
        $sql = "
            SELECT 
                count(pb.id)    AS qtd,
                pb.bolsista     AS bolsista,
                pb.orientador   AS orientador,
                pb.coorientador AS coorientador,
                pb.fase_id      AS fase_id,
                f.bloco         AS bloco,
                pb.vigente      AS vigente,
                e.inicio_vigencia AS inicio_vigencia,
                e.fim_vigencia AS fim_vigencia

            FROM projeto_bolsistas pb
            LEFT JOIN fases f ON f.id = pb.fase_id
            LEFT JOIN editais e on e.id=pb.editai_id
            WHERE pb.deleted IS NULL AND e.fim_vigencia + INTERVAL 1 DAY > NOW()
            group by pb.bolsista, pb.orientador, pb.coorientador, pb.fase_id, f.bloco, pb.vigente, e.inicio_vigencia, e.fim_vigencia

            UNION ALL

            SELECT
                count(pdj.id) AS qtd,
                pdj.bolsista     AS bolsista,
                pdj.orientador   AS orientador,
                pdj.coorientador AS coorientador,
                pdj.fase_id      AS fase_id,
                f.bloco          AS bloco,
                pdj.vigente      AS vigente,
                e.inicio_vigencia AS inicio_vigencia,
                e.fim_vigencia AS fim_vigencia
            FROM pdj_inscricoes pdj
            LEFT JOIN fases f ON f.id = pdj.fase_id
            LEFT JOIN editais e on e.id=pdj.editai_id
            WHERE pdj.deleted IS NULL AND e.fim_vigencia + INTERVAL 1 DAY > NOW()
            group by pdj.bolsista, pdj.orientador, pdj.coorientador, pdj.fase_id, f.bloco, pdj.vigente, e.inicio_vigencia, e.fim_vigencia

        ";

        return $query
            ->select([])              
            ->disableAutoFields()     
            ->from(['CountInsc' => "({$sql})"])
            ->select([
                'qtd' =>'CountInsc.qtd',
                'bolsista' =>'CountInsc.bolsista',
                'orientador' =>'CountInsc.orientador',
                'coorientador' =>'CountInsc.coorientador',
                'fase_id' =>'CountInsc.fase_id',
                'bloco' =>'CountInsc.bloco',
                'vigente' =>'CountInsc.vigente',
                'inicio_vigencia' =>'CountInsc.inicio_vigencia',
                'fim_vigencia' =>'CountInsc.fim_vigencia'
            ])
            ->enableHydration(false);
    }

    public function findBaseyoda(Query $query, array $options)
    {
        $sql = "
            SELECT 
                count(pb.id) AS qtd,
                pb.fase_id      AS fase_id,
                f.bloco         AS bloco,
                pb.vigente      AS vigente,
                e.inicio_vigencia AS inicio_vigencia,
				e.fim_vigencia AS fim_vigencia,
                e.programa_id as programa_id
            FROM projeto_bolsistas pb
            LEFT JOIN fases f ON f.id = pb.fase_id
            LEFT JOIN editais e on e.id=pb.editai_id
            WHERE pb.deleted IS NULL AND e.fim_vigencia + INTERVAL 1 DAY > NOW()
            group by pb.fase_id, f.bloco, pb.vigente, e.inicio_vigencia, e.fim_vigencia, e.programa_id

            UNION ALL

            SELECT
                count(pdj.id) AS qtd,
                pdj.fase_id      AS fase_id,
                f.bloco          AS bloco,
                pdj.vigente      AS vigente,
                e.inicio_vigencia AS inicio_vigencia,
				e.fim_vigencia AS fim_vigencia,
                e.programa_id as programa_id


            FROM pdj_inscricoes pdj
            LEFT JOIN fases f ON f.id = pdj.fase_id
            LEFT JOIN editais e on e.id=pdj.editai_id
            WHERE pdj.deleted IS NULL AND e.fim_vigencia + INTERVAL 1 DAY > NOW()
            group by pdj.fase_id, f.bloco, pdj.vigente, e.inicio_vigencia, e.fim_vigencia, e.programa_id
        ";

        return $query
            ->select([])              
            ->disableAutoFields()     
            ->from(['CountInsc' => "({$sql})"])
            ->select([
                'qtd' => 'CountInsc.qtd',
                'fase_id' => 'CountInsc.fase_id',
                'bloco' => 'CountInsc.bloco',
                'vigente' => 'CountInsc.vigente',
                'inicio_vigencia' => 'CountInsc.inicio_vigencia',
                'fim_vigencia' => 'CountInsc.fim_vigencia',
                'programa_id' => 'CountInsc.programa_id',

            ])
            ->enableHydration(false);
    }

}
