<?php
declare(strict_types=1);

namespace App\Service;

use Cake\ORM\TableRegistry;
use InvalidArgumentException;
use Cake\ORM\Query;
use Cake\Database\Expression\QueryExpression;
use Cake\I18n\FrozenTime;




class DashboardUserService
{
    protected $Dashcounts;
    protected $Dashdetalhes;


    public function __construct()
    {
        $locator = TableRegistry::getTableLocator();

        $this->Dashcounts = $locator->get('Dashcounts');
        $this->Dashdetalhes = $locator->get('Dashdetalhes');
    }
 

    
    public function contador(?int $userId = null)
    {

        $dashcountTable = TableRegistry::getTableLocator()
            ->get('Dashcounts');

        $query = $dashcountTable->find();

        // conceito: se userId != null, filtra pelo usuário
        if ($userId !== null) {
            $query->where([
                'OR' => [
                    'bolsista'     => $userId,
                    'orientador'   => $userId,
                    'coorientador' => $userId,
                ]
            ]);
        }


        $rows = $query
            ->enableHydration(false)
            ->toArray();



        
        $dashboard = [
            'bolsasAtivas' => 0,
            'andamento'    => 0,
            'finalizadas'  => 0,
            'inscricoesTotal' => 0,
            'total'        => 0,
            'cancel'       => 0,
            'subst'        => 0,
            'aceito'       => 0,
            'recusado'     => 0,
            
        ];

       
        foreach ($rows as $r) {
            $qtd = (int)$r['qtd'];

            if ((int)$r['vigente'] === 1) {
                $dashboard['bolsasAtivas'] += $qtd;
            }

            if ($r['bloco'] === 'I') {
                $dashboard['andamento'] += $qtd;
            }

            if (in_array($r['bloco'], ['F', 'H', 'R'], true)) {
                $dashboard['finalizadas'] += $qtd;
            }

            if (in_array($r['bloco'], ['I', 'F', 'H', 'R'], true)) {
                $dashboard['inscricoesTotal'] += $qtd;
            }

             if (in_array($r['fase_id'], [12, 21], true)) {
                $dashboard['cancel'] += $qtd;
            }

             if (in_array($r['fase_id'], [15], true)) {
                $dashboard['subst'] += $qtd;
            }

             if (in_array($r['fase_id'], [6], true)) {
                $dashboard['aceito'] += $qtd;
            }

             if (in_array($r['fase_id'], [7], true)) {
                $dashboard['recusado'] += $qtd;
            }
        }


        $dashboard['total'] =
            $dashboard['andamento'] + $dashboard['finalizadas'];

        return $dashboard;

    }

    public function detalhes(?array $conditions = null, ?int $userId = null, ?object $identity = null): \Cake\ORM\Query
    {
        $query = $this->Dashdetalhes->find();

        // Filtro por usuário (quando aplicável)
        if ($userId !== null) {
            $query->where([
                'OR' => [
                    'bolsista'     => $userId,
                    'orientador'   => $userId,
                    'coorientador' => $userId,
                ]
            ]);
        }

        if ($identity && !in_array($identity->id, [1, 8088])) {
            $conditions = $conditions ?? [];
            $conditions['Dashdetalhes.ativo'] = 1;
        }

        // Condições adicionais (fase, programa, edital, ativo etc.)
        if (!empty($conditions)) {
            $query->where($conditions);
        }

        return $query;
    }

    public function relatorio(
        ?array $conditions = null,
        ?int $userId = null,
        ?object $identity = null,
        ?array $papel = null,
        ?array $programa = null,
        ?array $situacao = null,
        bool $incluirDeletados = false
    ): Query {
        $query = $this->Dashdetalhes->find();

        // ===============================
        // Papel: se algum marcado, filtra apenas os selecionados; se null ou vazio, ignora (traz todos)
        // ===============================
        if (!empty($papel) && $userId !== null) {
            $query->where(function (QueryExpression $exp) use ($papel, $userId) {
                $or = [];
                if (in_array('bolsista', $papel)) $or['Dashdetalhes.bolsista'] = $userId;
                if (in_array('orientador', $papel)) $or['Dashdetalhes.orientador'] = $userId;
                if (in_array('coorientador', $papel)) $or['Dashdetalhes.coorientador'] = $userId;

                return $exp->or($or);
            });
        } elseif ($userId !== null) {
            // Se é consulta de gestão individual, mas nenhum papel marcado: traz todos os papéis
            $query->where([
                'OR' => [
                    'Dashdetalhes.bolsista'     => $userId,
                    'Dashdetalhes.orientador'   => $userId,
                    'Dashdetalhes.coorientador' => $userId,
                ]
            ]);
        }

        // ===============================
        // Programa: se null ou vazio, ignora (traz todos)
        // ===============================
        if (!empty($programa)) {
            $query->where(['Dashdetalhes.programa_id IN' => $programa]);
        }

        // ===============================
        // Situação: mapeia cada checkbox para condição específica
        // ===============================
        if (!empty($situacao)) {
            $query->where(function (QueryExpression $exp) use ($situacao) {

                $statusConditions = [];

                if (in_array('vigente', $situacao)) {
                    $statusConditions[] = ['Dashdetalhes.vigente' => 1];
                }

                if (in_array('egresso', $situacao)) {
                    $statusConditions[] = ['Dashdetalhes.fase_id IN' => [13, 14, 17, 22]];
                }

                if (in_array('nao_efetivado', $situacao)) {
                    $statusConditions[] = ['Dashdetalhes.data_inicio IS' => null];
                }

                if (in_array('suspenso', $situacao)) {
                    $statusConditions[] = ['Dashdetalhes.fase_id' => 20];
                }

                return $exp->or($statusConditions);
            });
        }

        // ===============================
        // Somente ativos para usuários comuns
        // ===============================
        if (!$incluirDeletados && $identity && !in_array($identity->id, [1, 8088])) {
            $query->where(['Dashdetalhes.ativo' => 1]);
        }

        // ===============================
        // Filtro de vigência
        // ===============================
        $query->where(function (QueryExpression $exp) {
            return $exp->gt('Dashdetalhes.fim_vigencia', FrozenTime::now());
        });

        // ===============================
        // Condições adicionais passadas
        // ===============================
        if (!empty($conditions)) {
            $query->where($conditions);
        }

        return $query;
    }






    
}
