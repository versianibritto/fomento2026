<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Table\DashboardSourceTable;
use Cake\ORM\Query;
use Cake\I18n\FrozenTime;



class DashboardService
{
    protected DashboardSourceTable $DashboardSource;

    public function __construct(DashboardSourceTable $dashboardSource)
    {
        $this->DashboardSource = $dashboardSource;
    }

    public function buildBase(object $user): array
    {
        $base = $this->DashboardSource->find('base')->toArray(); // traga tudo como array
        $userId = (int)$user->id;

        // filtra só os registros relacionados ao usuário
        $userRows = array_filter($base, fn($row) =>
            $row['bolsista'] == $userId ||
            $row['orientador'] == $userId ||
            $row['coorientador'] == $userId
        );

        // agora soma por condição
        $bolsasAtivas = 
        array_sum(array_map(fn($r) => $r['vigente'] == 1 ? $r['qtd'] : 0, $userRows));

        $inscricoesAndamento = array_sum(array_map(fn($r) =>
            $r['bloco'] == 'I' ? $r['qtd'] : 0, $userRows));

        $finalizadas = array_sum(array_map(fn($r) => 
            in_array($r['bloco'], ['F', 'H']) ? $r['qtd'] : 0, 
            $userRows
            ));

        return [
            'bolsasAtivas' => $bolsasAtivas,
            'inscricoesAndamento' => $inscricoesAndamento,
            'finalizadas' => $finalizadas,
            'total'=>($inscricoesAndamento + $finalizadas),
        ];
    }



    public function getInscricoesDetalhadas(int $userId, string $tipo): array
    {
        $query = $this->DashboardSource
            ->find('detalhes')
            ->where([
                'OR' => [
                    'bolsista'     => $userId,
                    'orientador'   => $userId,
                    'coorientador' => $userId
                ]
            ]);

        switch (strtoupper($tipo)) {
            case 'A':
                $query->where(['bloco IN' => ['I', 'F', 'H']]);
                break;

            case 'V':
                $query->where(['vigente' => 1]);
                break;

            default:
                throw new \InvalidArgumentException("Tipo inválido: {$tipo}");
        }

        return $query
            ->orderBy(['Detalhes.id' => 'ASC']) 
            ->toArray();
    }



public function buildYoda(): array
{
    $base = $this->DashboardSource->find('baseyoda')->toArray(); // tudo como array

    $result = [
        'pdj' => [
            'bolsasAtivas' => 0,
            'andamento' => 0,
            'finalizadas' => 0,
            'homologadasAceito' => 0,
            'homologadasRecusado' => 0,
            'cancelamentos' => 0,
            'substituicoes' => 0,
        ],
        'ic' => [
            'bolsasAtivas' => 0,
            'andamento' => 0,
            'finalizadas' => 0,
            'homologadasAceito' => 0,
            'homologadasRecusado' => 0,
            'cancelamentos' => 0,
            'substituicoes' => 0,
        ],
    ];

    $hoje = FrozenTime::now();

    foreach ($base as $r) {
        $grupo = $r['programa_id'] == 1 ? 'pdj' : 'ic';

        // bolsas ativas
        if ($r['vigente'] == 1) {
            $result[$grupo]['bolsasAtivas'] += $r['qtd'];
        }

        // andamento
        if (($r['bloco'] === 'I')&& $r['inicio_vigencia'] < $hoje ) {
            $result[$grupo]['andamento'] += $r['qtd'];
        }

        // finalizadas
        if ((in_array($r['bloco'], ['F','H'])) && $r['inicio_vigencia'] < $hoje ) {
            $result[$grupo]['finalizadas'] += $r['qtd'];
        }

        // homologações
        if ($r['fase_id'] == 6 && $r['inicio_vigencia'] < $hoje) {
            $result[$grupo]['homologadasAceito'] += $r['qtd'];
        }
        if ($r['fase_id'] == 7 && $r['inicio_vigencia'] < $hoje) {
            $result[$grupo]['homologadasRecusado'] += $r['qtd'];
        }

        // cancelamentos
        if (in_array($r['fase_id'], [12,21])) {
            $result[$grupo]['cancelamentos'] += $r['qtd'];
        }

        // substituições
        if (in_array($r['fase_id'], [15])) {
            $result[$grupo]['substituicoes'] += $r['qtd'];
        }
    }

    //dd($result);
    return $result;
}








}
