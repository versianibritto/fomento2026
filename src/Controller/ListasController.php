<?php
declare(strict_types=1);
namespace App\Controller;
use Cake\I18n\FrozenDate;

use Cake\ORM\TableRegistry;

class ListasController extends AppController 
{
    protected $Geral;

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
        if((!$this->request->getAttribute('identity')['yoda']) && 
        ($this->request->getAttribute('identity')['jedi']==null) &&
        ($this->request->getAttribute('identity')['padauan']==null)
        ){
            $this->Flash->error('Restrito a administradores');
            return $this->redirect(['controller'=>'Index', 'action'=>'dashboard']);
        }

        $identity = $this->request->getAttribute('identity');
        $identityData = $this->identityToArray($identity);
        [$prog, $permitidasIds] = $this->getProgramasPermitidos($identity);

        $situacao = $this->fetchTable('Fases')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])->order(['Fases.nome' => 'ASC'])->toArray();
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
        if((!$this->request->getAttribute('identity')['yoda']) && 
        ($this->request->getAttribute('identity')['jedi']==null) &&
        ($this->request->getAttribute('identity')['padauan']==null)
        ){
            $this->Flash->error('Restrito a administradores');
            return $this->redirect(['controller'=>'Index', 'action'=>'dashboard']);
        }

        $identity = $this->request->getAttribute('identity');
        $identityData = $this->identityToArray($identity);
        $ehYoda = !empty($identityData['yoda']);
        $jediRaw = (string)($identityData['jedi'] ?? '');
        $padauanRaw = (string)($identityData['padauan'] ?? '');

        [$prog, $permitidasIds] = $this->getProgramasPermitidos($identity);

        $dados = $this->request->getQueryParams();
        $w = [];
        $w[] = ['Geral.deleted IS' => null];

        if (!empty($dados['programa'])) {
            $programaId = (int)$dados['programa'];
            if ($padauanRaw !== '' && !in_array($programaId, $permitidasIds, true)) {
                $this->Flash->error('Você não tem permissão neste programa');
                return $this->redirect(['action' => 'busca', $tipo]);
            }
            $w[] = ['Geral.programa_id' => $programaId];
        } elseif ($padauanRaw !== '') {
            if (!empty($permitidasIds)) {
                $w[] = ['Geral.programa_id IN' => array_values(array_unique($permitidasIds))];
            } else {
                $w[] = ['Geral.programa_id' => -1];
            }
        }

        if (!$ehYoda && $padauanRaw === '' && $jediRaw !== '') {
            $w[] = ['Geral.unidade_id IN (' . $jediRaw . ')'];
        }

        if (!empty($dados['fase_id'])) {
            $w[] = ['Geral.fase_id' => (int)$dados['fase_id']];
        }

        if (!empty($dados['origem'])) {
            $w[] = ['Geral.origem' => (string)$dados['origem']];
        }

        if (!empty($dados['cota'])) {
            $w[] = ['Geral.cota' => (string)$dados['cota']];
        }

        $conditions = [];
        if ($tipo === 'V') {
            $conditions[] = ['Geral.vigente' => 1];
        } elseif ($tipo === 'A') {
            $conditions[] = ['Geral.inicio_vigencia > DATE_ADD(NOW(), INTERVAL 1 DAY)'];
        } elseif ($tipo === 'T') {
            $conditions[] = ['Geral.fim_vigencia > DATE_ADD(NOW(), INTERVAL 1 DAY)'];
        }
        if (!empty($conditions)) {
            $w = array_merge($w, $conditions);
        }

        $lista = $this->Geral->find('all')
            ->where([$w])
            ->order(['nome_orientador' => 'ASC']);
        $listas = $this->paginate($lista, ['limit'=>10]);

        if ($this->request->getQuery('acao') === 'excel') {
            $excelQuery = $this->Geral->find('all')
                ->where([$w])
                ->order(['nome_orientador' => 'ASC']);
            $excelQuery->limit(null);
            $rows = $excelQuery->all();

            $columns = $this->Geral->getSchema()->columns();
            if (empty($columns)) {
                $first = $rows->first();
                $columns = $first ? array_keys($first->toArray()) : [];
            }
            $excluir = [
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
            $columns = array_values(array_filter($columns, static function ($col) use ($excluir) {
                return !in_array($col, $excluir, true);
            }));

            $fh = fopen('php://temp', 'r+');
            fputcsv($fh, $columns, ';');

            $origemMap = $this->origem ?? [];
            $tipoBolsaMap = $this->fonte ?? [];
            $resultadoMap = $this->resultado ?? [];
            $cotaMap = $this->cota ?? [];
            $sexoMap = $this->sexo ?? [];
            $documentoMap = $this->documentos ?? [];

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
                        if ($val === '' || $val === null) {
                            $val = '';
                        } else {
                            $val = ((int)$val === 1) ? 'Sim' : 'Não';
                        }
                    } elseif ($col === 'primeiro_periodo') {
                        if ($val === '' || $val === null) {
                            $val = 'Não informado';
                        } else {
                            $val = ((int)$val === 1) ? 'Sim' : 'Não';
                        }
                    }
                    $val = $this->normalizeCsvValue($val);
                    $row[] = $val;
                }
                fputcsv($fh, $row, ';');
            }

            rewind($fh);
            $csv = stream_get_contents($fh);
            fclose($fh);

            $filename = 'lista_' . $tipo . '_' . date('Ymd_His') . '.csv';
            $this->response = $this->response
                ->withType('csv')
                ->withDownload($filename);
            $this->response->getBody()->write($csv);
            return $this->response;
        }

        $situacao = $this->fetchTable('Fases')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])->order(['Fases.nome' => 'ASC'])->toArray();
        $programa = [];
        $origem = $this->origem ?? [];
        $cotas = $this->cota ?? [];

        $this->set(compact('listas','situacao', 'programa', 'origem', 'cotas', 'tipo', 'prog'));
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
            ->order(['sigla' => 'ASC'])
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

    public function limpar($secao, $action )
    {

        $this->request->getSession()->delete($secao);
        $busca = null;
       // $w = [];
        return $this->redirect(['action'=>$action]);
    }

    
    
    
}
