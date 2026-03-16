<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;

class GraficoController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
    }

    public function inscricoesEmAndamento()
    {
        $this->viewBuilder()->setLayout('adminlight');
        if (!$this->request->getAttribute('identity')['yoda']) {
            $this->Flash->error('Restrito a administradores');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $programaId = $this->request->getQuery('programa_id') ?? [];
        if (!is_array($programaId)) {
            $programaId = $programaId === '' ? [] : [$programaId];
        }
        $faseId = $this->request->getQuery('fase_id') ?? [];
        if (!is_array($faseId)) {
            $faseId = $faseId === '' ? [] : [$faseId];
        }

        $dashcountTable = TableRegistry::getTableLocator()->get('Dashyodacounts');
        $hoje = FrozenTime::now();
        $qtdQuery = $dashcountTable->find()
            ->select([
                'Dashyodacounts.qtd',
                'Dashyodacounts.fase_id',
                'Dashyodacounts.bloco',
                'Dashyodacounts.programa_id',
                'Dashyodacounts.editai_id',
                'nome_edital' => 'Editais.nome',
                'nome_fase' => 'Fases.nome',
                'sigla_programa' => 'Programas.sigla',
            ])
            ->contain(['Editais', 'Fases', 'Programas'])
            ->enableHydration(false);
        $qtdQuery->where(['Dashyodacounts.bloco IN' => ['I', 'F', 'H', 'R']]);
        $qtdQuery->where(['Dashyodacounts.inicio_vigencia >' => $hoje]);
        if (!empty($programaId)) {
            $qtdQuery->where(['Dashyodacounts.programa_id IN' => $programaId]);
        }
        if (!empty($faseId)) {
            $qtdQuery->where(['Dashyodacounts.fase_id IN' => $faseId]);
        }
        $qtd = $qtdQuery->toArray();

        $programas = [];
        $programaTotais = [];
        foreach ($qtd as $q) {
            $programa = $q['sigla_programa'] ?? $q['programa_id'] ?? null;
            $bloco = $q['bloco'] ?? null;
            $quantidade = (int)($q['qtd'] ?? 0);
            if ($programa === null) {
                continue;
            }

            if (!isset($programaTotais[$programa])) {
                $programaTotais[$programa] = 0;
            }
            $programaTotais[$programa] += $quantidade;

            if (!isset($programas[$programa])) {
                $programas[$programa] = [];
            }

            if ($bloco === 'I') {
                $programas[$programa]['N']['N'] = ($programas[$programa]['N']['N'] ?? 0) + $quantidade;
            } elseif (in_array($bloco, ['F', 'H'], true)) {
                $programas[$programa]['R']['F'] = ($programas[$programa]['R']['F'] ?? 0) + $quantidade;
            }
        }

        $editais = [];
        foreach ($qtd as $q) {
            $edital = $q['nome_edital'] ?? ('Edital ' . ($q['editai_id'] ?? ''));
            $bloco = $q['bloco'] ?? null;
            $quantidade = (int)($q['qtd'] ?? 0);

            if (!isset($editais[$edital])) {
                $editais[$edital] = [];
            }

            if ($bloco === 'I') {
                $editais[$edital]['I'] = ($editais[$edital]['I'] ?? 0) + $quantidade;
            } elseif (in_array($bloco, ['F', 'H'], true)) {
                $editais[$edital]['F'] = ($editais[$edital]['F'] ?? 0) + $quantidade;
            }
        }

        $chartLabels = [];
        $chartSeries = [];
        foreach ($qtd as $q) {
            $bloco = $q['bloco'] ?? null;
            if (!in_array($bloco, ['F', 'I', 'H', 'R'], true)) {
                continue;
            }
            $edital = $q['nome_edital'] ?? ('Edital ' . ($q['editai_id'] ?? ''));
            $faseNome = $q['nome_fase'] ?? null;
            $faseId = $q['fase_id'] ?? null;
            $faseLabel = $faseNome ?: ($faseId === null ? 'Sem fase' : ('Fase ' . $faseId));
            $serieLabel = $faseLabel . ' (' . $bloco . ')';
            $chartLabels[$edital] = true;
            if (!isset($chartSeries[$serieLabel])) {
                $chartSeries[$serieLabel] = [];
            }
            $chartSeries[$serieLabel][$edital] = ($chartSeries[$serieLabel][$edital] ?? 0) + (int)($q['qtd'] ?? 0);
        }

        $chartLabels = array_keys($chartLabels);
        $chartDatasets = [];
        foreach ($chartSeries as $label => $dataByEdital) {
            $data = [];
            foreach ($chartLabels as $edital) {
                $data[] = $dataByEdital[$edital] ?? 0;
            }
            $chartDatasets[] = [
                'label' => $label,
                'data' => $data,
            ];
        }

        $programasList = [];
        $programasRows = $dashcountTable->find()
            ->select([
                'Dashyodacounts.programa_id',
                'sigla_programa' => 'Programas.sigla',
            ])
            ->contain(['Programas'])
            ->where(['Dashyodacounts.programa_id IS NOT' => null])
            ->where(['Dashyodacounts.bloco IN' => ['I', 'F', 'H', 'R']])
            ->where(['Dashyodacounts.inicio_vigencia >' => $hoje])
            ->groupBy(['Dashyodacounts.programa_id', 'Programas.sigla'])
            ->orderBy(['Programas.sigla' => 'ASC'])
            ->enableHydration(false)
            ->toArray();
        foreach ($programasRows as $row) {
            $programaIdRow = $row['programa_id'] ?? $row['Dashyodacounts']['programa_id'] ?? null;
            if ($programaIdRow === null) {
                continue;
            }
            $siglaPrograma = $row['sigla_programa'] ?? $row['Programas']['sigla'] ?? null;
            $programasList[$programaIdRow] = $siglaPrograma ?: ('Programa ' . $programaIdRow);
        }

        $fasesList = [];
        $fasesRows = $dashcountTable->find()
            ->select([
                'Dashyodacounts.fase_id',
                'nome_fase' => 'Fases.nome',
            ])
            ->contain(['Fases'])
            ->where(['Dashyodacounts.fase_id IS NOT' => null])
            ->where(['Dashyodacounts.bloco IN' => ['I', 'F', 'H', 'R']])
            ->where(['Dashyodacounts.inicio_vigencia >' => $hoje])
            ->groupBy(['Dashyodacounts.fase_id', 'Fases.nome'])
            ->orderBy(['Fases.nome' => 'ASC'])
            ->enableHydration(false)
            ->toArray();
        foreach ($fasesRows as $row) {
            $faseIdRow = $row['fase_id'] ?? $row['Dashyodacounts']['fase_id'] ?? null;
            if ($faseIdRow === null) {
                continue;
            }
            $nomeFase = $row['nome_fase'] ?? $row['Fases']['nome'] ?? null;
            $fasesList[$faseIdRow] = $nomeFase ?: ('Fase ' . $faseIdRow);
        }

        $this->set(compact(
            'editais',
            'qtd',
            'programas',
            'chartLabels',
            'chartDatasets',
            'programasList',
            'programaId',
            'fasesList',
            'faseId',
            'programaTotais'
        ));
    }
}
