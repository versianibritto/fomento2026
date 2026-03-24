<?php
declare(strict_types=1);
namespace App\Controller;
use Cake\ORM\TableRegistry;

class ListasController extends AppController 
{
    protected $Geral;
    private const PADRAO_INSCRICAO_EXCLUIR = [
        'bolsista',
        'orientador',
        'unidade_id',
        'vinculo_orientador_id',
        'coorientador',
        'unidade_id_coorientador',
        'vinculo_coorientador_id',
        'programa_id',
        'editai_id',
        'ed_controller',
        'fase_id',
        'area_pdj',
        'inicio_vigencia',
        'fim_vigencia',
        'deleted',
    ];
    private const PADRAO_INSCRICAO_LABELS = [
        'data_inicio' => 'data_inicio',
        'primeira_bolsa' => 'data de inicio da primeira bolsa',
    ];

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Geral = TableRegistry::getTableLocator()->get('Geral');
    }

    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setLayout('admin');

        
    }

    public function busca($tipo = null)
    {
        $tipo = strtoupper((string)$tipo);
        if ($tipo === '') {
            $tipo = 'T';
        }
        $acessoNegado = $this->validarAcessoListas();
        if ($acessoNegado !== null) {
            return $acessoNegado;
        }

        $identity = $this->request->getAttribute('identity');
        [$prog, ] = $this->getProgramasPermitidos($identity);

        $situacao = $this->fetchTable('Fases')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])->orderBy(['Fases.nome' => 'ASC'])->toArray();
        $programa = [];
        $origem = $this->origem ?? [];
        $cotas = $this->cota ?? [];
        $this->set(compact('situacao', 'programa', 'origem', 'cotas', 'tipo', 'prog'));
    }

    public function resultado($tipo = null)
    {
        $tipo = strtoupper((string)$tipo);
        if ($tipo === '') {
            $tipo = 'V';
        }
        $acessoNegado = $this->validarAcessoListas();
        if ($acessoNegado !== null) {
            return $acessoNegado;
        }

        $identity = $this->request->getAttribute('identity');
        [$prog, $permitidasIds] = $this->getProgramasPermitidos($identity);
        $conditions = $this->buildResultadoConditions($tipo, $permitidasIds);
        if ($conditions === null) {
            return $this->redirect(['action' => 'busca', $tipo]);
        }

        $lista = $this->Geral->find('all')
            ->where([$conditions])
            ->order(['nome_orientador' => 'ASC']);
        $listas = $this->paginate($lista, ['limit'=>10]);

        if ($this->request->getQuery('acao') === 'excel') {
            $excelQuery = $this->Geral->find('all')
                ->where([$conditions])
                ->order(['nome_orientador' => 'ASC']);
            $excelQuery->limit(null);
            $rows = $excelQuery->all();

            $columns = $this->getPadraoInscricaoColumns($rows);
            $header = array_map(static function ($column) {
                return self::PADRAO_INSCRICAO_LABELS[$column] ?? $column;
            }, $columns);
            $origemMap = $this->origem ?? [];
            $tipoBolsaMap = $this->fonte ?? [];
            $resultadoMap = $this->resultado ?? [];
            $cotaMap = $this->cota ?? [];
            $sexoMap = $this->sexo ?? [];
            $documentoMap = $this->documentos ?? [];
            $exportRows = [];

            foreach ($rows as $item) {
                $data = $item->toArray();
                $row = [];
                foreach ($columns as $col) {
                    $val = $data[$col] ?? '';
                    if ($col === 'origem') {
                        $key = strtoupper((string)$val);
                        $val = $origemMap[$key] ?? $val;
                    } elseif ($col === 'tipo_bolsa') {
                        $key = strtoupper((string)$val);
                        $val = $tipoBolsaMap[$key] ?? $val;
                    } elseif ($col === 'resultado') {
                        $key = strtoupper((string)$val);
                        $val = $resultadoMap[$key] ?? $val;
                    } elseif ($col === 'sexo') {
                        $key = strtoupper((string)$val);
                        $val = $sexoMap[$key] ?? $val;
                    } elseif ($col === 'documento') {
                        $key = strtoupper((string)$val);
                        $val = $documentoMap[$key] ?? $val;
                    } elseif ($col === 'cota') {
                        $key = strtoupper((string)$val);
                        $val = $cotaMap[$key] ?? $val;
                    } elseif (in_array($col, ['heranca', 'troca_projeto', 'vigente', 'autorizacao', 'prorrogacao'], true)) {
                        $val = ($val === '' || $val === null) ? '' : (((int)$val === 1) ? 'Sim' : 'Não');
                    } elseif ($col === 'primeiro_periodo') {
                        $val = ($val === '' || $val === null) ? 'Não informado' : (((int)$val === 1) ? 'Sim' : 'Não');
                    }
                    $row[] = $val;
                }
                $exportRows[] = $row;
            }

            return $this->downloadCsvResponse(
                'lista_' . $tipo . '_' . date('Ymd_His') . '.csv',
                $header,
                $exportRows
            );
        }

        $situacao = $this->fetchTable('Fases')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])->orderBy(['Fases.nome' => 'ASC'])->toArray();

        $this->set([
            'listas' => $listas,
            'situacao' => $situacao,
            'programa' => [],
            'origem' => $this->origem ?? [],
            'cotas' => $this->cota ?? [],
            'tipo' => $tipo,
            'prog' => $prog,
        ]);
    }

    private function getProgramasPermitidos($identity): array
    {
        $data = $this->identityToArray($identity);
        $ehYoda = !empty($data['yoda']);
        $padauanRaw = (string)($data['padauan'] ?? '');

        $programasTable = $this->fetchTable('Programas');
        $programasRows = $programasTable->find()
            ->select(['id', 'sigla', 'letra'])
            ->where(['deleted' => 0])
            ->orderBY(['sigla' => 'ASC'])
            ->all();

        $prog = [];
        $permitidasIds = [];
        if (!$ehYoda && $padauanRaw !== '') {
            $permitidos = array_filter(array_map('trim', explode(',', $padauanRaw)));
            $permitidosNumeric = [];
            $permitidosStr = [];
            foreach ($permitidos as $item) {
                if (ctype_digit($item)) {
                    $permitidosNumeric[] = (int)$item;
                } else {
                    $permitidosStr[] = $item;
                }
            }
            foreach ($programasRows as $row) {
                $letra = (string)($row->letra ?? '');
                $sigla = (string)($row->sigla ?? '');
                $id = (int)$row->id;
                $permitido = false;
                if ($id && in_array($id, $permitidosNumeric, true)) {
                    $permitido = true;
                }
                if ($letra !== '' && in_array($letra, $permitidosStr, true)) {
                    $permitido = true;
                }
                if ($sigla !== '' && in_array($sigla, $permitidosStr, true)) {
                    $permitido = true;
                }
                if ($permitido) {
                    $prog[$id] = $sigla;
                    $permitidasIds[] = $id;
                }
            }
        } else {
            foreach ($programasRows as $row) {
                $prog[(int)$row->id] = (string)$row->sigla;
                $permitidasIds[] = (int)$row->id;
            }
        }

        return [$prog, $permitidasIds];
    }

    private function identityToArray($identity): array
    {
        if (is_array($identity)) {
            return $identity;
        }
        if (is_object($identity) && method_exists($identity, 'get')) {
            return [
                'yoda' => $identity->get('yoda'),
                'jedi' => $identity->get('jedi'),
                'padauan' => $identity->get('padauan'),
            ];
        }
        return [];
    }

    private function getPadraoInscricaoColumns($rows): array
    {
        $columns = $this->Geral->getSchema()->columns();
        if (empty($columns)) {
            $first = $rows->first();
            $columns = $first ? array_keys($first->toArray()) : [];
        }

        return array_values(array_filter($columns, static function ($col) {
            return !in_array($col, self::PADRAO_INSCRICAO_EXCLUIR, true);
        }));
    }

    private function validarAcessoListas()
    {
        $identity = $this->request->getAttribute('identity');
        if (
            !$identity['yoda'] &&
            $identity['jedi'] == null &&
            $identity['padauan'] == null
        ) {
            $this->Flash->error('Restrito a administradores');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        return null;
    }

    private function buildResultadoConditions(string $tipo, array $permitidasIds): ?array
    {
        $identityData = $this->identityToArray($this->request->getAttribute('identity'));
        $ehYoda = !empty($identityData['yoda']);
        $jediRaw = (string)($identityData['jedi'] ?? '');
        $padauanRaw = (string)($identityData['padauan'] ?? '');
        $dados = $this->request->getQueryParams();
        $conditions = [['Geral.deleted IS' => null]];

        if (!empty($dados['programa'])) {
            $programaId = (int)$dados['programa'];
            if ($padauanRaw !== '' && !in_array($programaId, $permitidasIds, true)) {
                $this->Flash->error('Você não tem permissão neste programa');
                return null;
            }
            $conditions[] = ['Geral.programa_id' => $programaId];
        } elseif ($padauanRaw !== '') {
            if (!empty($permitidasIds)) {
                $conditions[] = ['Geral.programa_id IN' => array_values(array_unique($permitidasIds))];
            } else {
                $conditions[] = ['Geral.programa_id' => -1];
            }
        }

        if (!$ehYoda && $padauanRaw === '' && $jediRaw !== '') {
            $conditions[] = ['Geral.unidade_id IN (' . $jediRaw . ')'];
        }

        if (!empty($dados['fase_id'])) {
            $conditions[] = ['Geral.fase_id' => (int)$dados['fase_id']];
        }

        if (!empty($dados['origem'])) {
            $conditions[] = ['Geral.origem' => (string)$dados['origem']];
        }

        if (!empty($dados['cota'])) {
            $conditions[] = ['Geral.cota' => (string)$dados['cota']];
        }

        if ($tipo === 'V') {
            $conditions[] = ['Geral.vigente' => 1];
        } elseif ($tipo === 'A') {
            $conditions[] = ['Geral.inicio_vigencia > DATE_ADD(NOW(), INTERVAL 1 DAY)'];
        } elseif ($tipo === 'T') {
            $conditions[] = ['Geral.fim_vigencia > DATE_ADD(NOW(), INTERVAL 1 DAY)'];
        }

        return $conditions;
    }

    public function limpar($secao, $action )
    {
        $this->request->getSession()->delete($secao);
        return $this->redirect(['action'=>$action]);
    }
}
