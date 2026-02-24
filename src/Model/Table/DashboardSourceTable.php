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
            WHERE pb.deleted = 0 AND e.fim_vigencia + INTERVAL 1 DAY > NOW()
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
            WHERE pb.deleted = 0 AND e.fim_vigencia + INTERVAL 1 DAY > NOW()
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

    public function findDetalhes(Query $query, array $options)
    {
        $sql = "
            SELECT 
                pb.id           AS id,
                pb.bolsista     AS bolsista,
                b.nome          AS nome_bolsista,
                pb.orientador   AS orientador,
                o.nome          AS nome_orientador,
                pb.coorientador AS coorientador,
                c.nome          AS nome_coorientador,
                pb.projeto_id   AS projeto_id,
                pb.fase_id      AS fase_id,
                f.nome          AS nome_fase,
                f.bloco         AS bloco,
                pb.vigente      AS vigente,
                pb.editai_id    AS editai_id,
                e.nome          AS nome_edital,
                p.sigla         AS nome_programa
            FROM projeto_bolsistas pb
            LEFT JOIN usuarios b on b.id=pb.bolsista
            LEFT JOIN usuarios o on o.id=pb.orientador
            LEFT JOIN usuarios c on c.id=pb.coorientador
            LEFT JOIN fases f ON f.id = pb.fase_id
            LEFT JOIN editais e on e.id=pb.editai_id
            LEFT JOIN programas p on p.id=e.programa_id
            WHERE pb.deleted = 0

            UNION ALL

            SELECT
                pdj.id           AS id,
                pdj.bolsista     AS bolsista,
                b.nome           AS nome_bolsista,
                pdj.orientador   AS orientador,
                o.nome           AS nome_orientador,
                pdj.coorientador AS coorientador,
                c.nome           AS nome_coorientador,
                pdj.projeto_id   AS projeto_id,
                pdj.fase_id      AS fase_id,
                f.nome          AS nome_fase,
                f.bloco          AS bloco,
                pdj.vigente      AS vigente,
                pdj.editai_id    AS editai_id,
                e.nome           AS nome_edital,
                p.sigla          AS nome_programa
            FROM pdj_inscricoes pdj
            LEFT JOIN usuarios b on b.id=pdj.bolsista
            LEFT JOIN usuarios o on o.id=pdj.orientador
            LEFT JOIN usuarios c on c.id=pdj.coorientador
            LEFT JOIN fases f ON f.id = pdj.fase_id
            LEFT JOIN editais e on e.id=pdj.editai_id
            LEFT JOIN programas p on p.id=e.programa_id
            WHERE pdj.deleted IS NULL

        ";

      return $query
        ->select([])              // 👈 ZERA qualquer select automático
        ->disableAutoFields()     // 👈 impede DashboardSource.*
        ->from(['Detalhes' => "({$sql})"])
        ->select([
        'id'                => 'Detalhes.id',
        'bolsista'           => 'Detalhes.bolsista',
        'nome_bolsista'      => 'Detalhes.nome_bolsista',
        'orientador'         => 'Detalhes.orientador',
        'nome_orientador'    => 'Detalhes.nome_orientador',
        'coorientador'       => 'Detalhes.coorientador',
        'nome_coorientador'  => 'Detalhes.nome_coorientador',
        'projeto_id'         => 'Detalhes.projeto_id',
        'fase_id'            => 'Detalhes.fase_id',
        'nome_fase'          => 'Detalhes.nome_fase',
        'bloco'              => 'Detalhes.bloco',
        'vigente'            => 'Detalhes.vigente',
        'editai_id'          => 'Detalhes.editai_id',
        'nome_edital'        => 'Detalhes.nome_edital',
        'nome_programa'      => 'Detalhes.nome_programa',
    ])

    ->enableHydration(false);


    }
}
