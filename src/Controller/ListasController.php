<?php
declare(strict_types=1);
namespace App\Controller;
use Cake\ORM\TableRegistry;
use Cake\I18n\FrozenTime;

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

    public function buscaRaic()
    {
        $acessoNegado = $this->validarAcessoListas();
        if ($acessoNegado !== null) {
            return $acessoNegado;
        }

        $identity = $this->request->getAttribute('identity');
        $usuarioId = (int)($identity['id'] ?? 0);
        $jediCsv = (string)($identity['jedi'] ?? '');
        $isTi = in_array($usuarioId, [1, 8088], true);
        $isYoda = !empty($identity['yoda']);
        $isJedi = !$isTi && !$isYoda && $jediCsv !== '';
        $unidadesPermitidas = array_values(array_filter(array_map('trim', explode(',', $jediCsv))));

        if (!$isTi && !$isYoda && !$isJedi) {
            $this->Flash->error('Restrito à gestão e coordenação de unidade.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $unidadesTable = $this->fetchTable('Unidades');
        if ($isJedi && !empty($unidadesPermitidas)) {
            $unidades = $unidadesTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'sigla',
            ])
                ->where([
                    'Unidades.id IN' => $unidadesPermitidas,
                    'Unidades.deleted' => 0,
                ])
                ->orderBy(['Unidades.sigla' => 'ASC'])
                ->toArray();
        } else {
            $unidades = $unidadesTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'sigla',
            ])
                ->where(['Unidades.deleted' => 0])
                ->orderBy(['Unidades.sigla' => 'ASC'])
                ->toArray();
        }

        $anos = [];
        $anoAtual = (int)FrozenTime::now()->format('Y');
        for ($ano = $anoAtual; $ano >= ($anoAtual - 5); $ano--) {
            $anos[(string)$ano] = (string)$ano;
        }

        $tipoBolsa = [
            'R' => 'Renovação',
            'O' => 'Raics de Outras Agencias',
        ];

        $this->set(compact('unidades', 'anos', 'tipoBolsa', 'isTi', 'isYoda', 'isJedi'));
    }

    public function resultadoRaic()
    {
        $acessoNegado = $this->validarAcessoListas();
        if ($acessoNegado !== null) {
            return $acessoNegado;
        }

        $identity = $this->request->getAttribute('identity');
        $usuarioId = (int)($identity['id'] ?? 0);
        $jediCsv = (string)($identity['jedi'] ?? '');
        $isTi = in_array($usuarioId, [1, 8088], true);
        $isYoda = !empty($identity['yoda']);
        $isJedi = !$isTi && !$isYoda && $jediCsv !== '';
        $unidadesPermitidas = array_values(array_filter(array_map('trim', explode(',', $jediCsv))));

        if (!$isTi && !$isYoda && !$isJedi) {
            $this->Flash->error('Restrito à gestão e coordenação de unidade.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $filtros = [
            'ano' => (string)$this->request->getQuery('ano', ''),
            'agendada' => (string)$this->request->getQuery('agendada', ''),
            'unidade_id' => (string)$this->request->getQuery('unidade_id', ''),
            'certificado' => (string)$this->request->getQuery('certificado', ''),
            'tipo_bolsa' => (string)$this->request->getQuery('tipo_bolsa', ''),
        ];

        if ($filtros['ano'] === '' || !ctype_digit($filtros['ano'])) {
            $this->Flash->error('O filtro de ano é obrigatório.');
            return $this->redirect(['controller' => 'Listas', 'action' => 'buscaRaic']);
        }

        $dataLimite = FrozenTime::now()->subYears(5);
        $query = $this->fetchTable('Raics')->find()
            ->select([
                'Raics.id',
                'Raics.usuario_id',
                'Raics.orientador',
                'Raics.unidade_id',
                'Raics.tipo_bolsa',
                'Raics.tipo_apresentacao',
                'Raics.data_apresentacao',
                'Raics.presenca',
                'Raics.deleted',
                'Raics.editai_id',
                'Raics.created',
                'Raics.usuario_libera',
                'Raics.data_liberacao',
                'Raics.projeto_bolsista_id',
            ])
            ->contain([
                'Usuarios' => function ($q) {
                    return $q->select([
                        'Usuarios.id',
                        'Usuarios.nome',
                        'Usuarios.telefone',
                        'Usuarios.telefone_contato',
                        'Usuarios.celular',
                        'Usuarios.whatsapp',
                        'Usuarios.email',
                        'Usuarios.email_alternativo',
                        'Usuarios.email_contato',
                    ]);
                },
                'Orientadores' => function ($q) {
                    return $q->select([
                        'Orientadores.id',
                        'Orientadores.nome',
                        'Orientadores.telefone',
                        'Orientadores.telefone_contato',
                        'Orientadores.celular',
                        'Orientadores.whatsapp',
                        'Orientadores.email',
                        'Orientadores.email_alternativo',
                        'Orientadores.email_contato',
                    ]);
                },
                'Unidades' => function ($q) {
                    return $q->select(['Unidades.id', 'Unidades.sigla']);
                },
                'Editais' => function ($q) {
                    return $q->select(['Editais.id', 'Editais.fim_vigencia']);
                },
            ])
            ->where(['Raics.created >=' => $dataLimite])
            ->orderBy(['Raics.id' => 'DESC']);

        $unidadesTable = $this->fetchTable('Unidades');
        if ($isTi) {
            $unidades = $unidadesTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'sigla',
            ])->orderBy(['Unidades.sigla' => 'ASC'])->toArray();
        } elseif ($isYoda) {
            $query->where(['Raics.deleted' => 0]);
            $unidades = $unidadesTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'sigla',
            ])->where(['Unidades.deleted' => 0])
                ->orderBy(['Unidades.sigla' => 'ASC'])
                ->toArray();
        } else {
            $query->where(['Raics.deleted' => 0]);
            $unidades = [];
            if (!empty($unidadesPermitidas)) {
                $unidades = $unidadesTable->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'sigla',
                ])->where([
                    'Unidades.id IN' => $unidadesPermitidas,
                    'Unidades.deleted' => 0,
                ])
                    ->orderBy(['Unidades.sigla' => 'ASC'])
                    ->toArray();
            }
            if (!empty($unidadesPermitidas)) {
                $query->where(['Raics.unidade_id IN' => $unidadesPermitidas]);
            }
            if ($filtros['unidade_id'] !== '' && in_array($filtros['unidade_id'], $unidadesPermitidas, true)) {
                $query->where(['Raics.unidade_id' => $filtros['unidade_id']]);
            }
        }

        if ($isTi || $isYoda) {
            if ($filtros['unidade_id'] !== '') {
                $query->where(['Raics.unidade_id' => $filtros['unidade_id']]);
            }
        }

        if ($filtros['ano'] !== '' && ctype_digit($filtros['ano'])) {
            $query->where(function ($exp) use ($filtros) {
                return $exp->eq('YEAR(Raics.created)', (int)$filtros['ano'], 'integer');
            });
        }
        if ($filtros['agendada'] === 'S') {
            $query->where(['Raics.data_apresentacao IS NOT' => null]);
        } elseif ($filtros['agendada'] === 'N') {
            $query->where(['Raics.data_apresentacao IS' => null]);
        }
        if ($filtros['certificado'] === 'S') {
            $query->where(['Raics.presenca' => 'S']);
        } elseif ($filtros['certificado'] === 'N') {
            $query->where(function ($exp) {
                return $exp->or([
                    'Raics.presenca <>' => 'S',
                    'Raics.presenca IS' => null,
                ]);
            });
        }
        if ($filtros['tipo_bolsa'] !== '') {
            if ($filtros['tipo_bolsa'] === 'O') {
                $query->where(['Raics.tipo_bolsa IN' => ['V', 'Z']]);
            } else {
                $query->where(['Raics.tipo_bolsa' => $filtros['tipo_bolsa']]);
            }
        }

        if ($this->request->getQuery('acao') === 'excel') {
            $rows = $query->all();
            $header = [
                'id',
                'orientador',
                'orientador_telefone',
                'orientador_telefone_contato',
                'orientador_celular',
                'orientador_whatsapp',
                'orientador_email',
                'orientador_email_alternativo',
                'orientador_email_contato',
                'bolsista',
                'bolsista_telefone',
                'bolsista_telefone_contato',
                'bolsista_celular',
                'bolsista_whatsapp',
                'bolsista_email',
                'bolsista_email_alternativo',
                'bolsista_email_contato',
                'unidade_raic',
                'data_apresentacao',
                'certificado_liberado',
                'tipo_apresentacao',
                'usuario_libera',
                'data_liberacao',
                'created',
                'tipo_bolsa',
                'projeto_bolsista_id',
            ];
            $exportRows = [];
            foreach ($rows as $raic) {
                $exportRows[] = [
                    $raic->id,
                    $raic->orientadore->nome ?? '',
                    $raic->orientadore->telefone ?? '',
                    $raic->orientadore->telefone_contato ?? '',
                    $raic->orientadore->celular ?? '',
                    $raic->orientadore->whatsapp ?? '',
                    $raic->orientadore->email ?? '',
                    $raic->orientadore->email_alternativo ?? '',
                    $raic->orientadore->email_contato ?? '',
                    $raic->usuario->nome ?? '',
                    $raic->usuario->telefone ?? '',
                    $raic->usuario->telefone_contato ?? '',
                    $raic->usuario->celular ?? '',
                    $raic->usuario->whatsapp ?? '',
                    $raic->usuario->email ?? '',
                    $raic->usuario->email_alternativo ?? '',
                    $raic->usuario->email_contato ?? '',
                    $raic->unidade->sigla ?? '',
                    $raic->data_apresentacao ? $raic->data_apresentacao->i18nFormat('dd/MM/yyyy') : '',
                    strtoupper((string)($raic->presenca ?? '')) === 'S' ? 'Sim' : 'Não',
                    match (strtoupper((string)($raic->tipo_apresentacao ?? ''))) {
                        'O' => 'Oral',
                        'P' => 'Painel',
                        default => '',
                    },
                    $raic->usuario_libera ?? '',
                    $raic->data_liberacao ? $raic->data_liberacao->i18nFormat('dd/MM/yyyy HH:mm') : '',
                    $raic->created ? $raic->created->i18nFormat('dd/MM/yyyy HH:mm') : '',
                    match (strtoupper((string)($raic->tipo_bolsa ?? ''))) {
                        'R' => 'Renovação',
                        'V', 'Z' => 'Raics de Outras Agencias',
                        default => (string)($raic->tipo_bolsa ?? ''),
                    },
                    $raic->projeto_bolsista_id ?? '',
                ];
            }

            return $this->downloadCsvResponse(
                'lista_raic_' . date('Ymd_His') . '.csv',
                $header,
                $exportRows
            );
        }

        $this->paginate = [
            'limit' => 20,
            'maxLimit' => 20,
        ];
        $listas = $this->paginate($query);

        $anos = [];
        $anoAtual = (int)FrozenTime::now()->format('Y');
        for ($ano = $anoAtual; $ano >= ($anoAtual - 5); $ano--) {
            $anos[(string)$ano] = (string)$ano;
        }
        $tipoBolsa = [
            'R' => 'Renovação',
            'O' => 'Raics de Outras Agencias',
        ];

        $this->set(compact('listas', 'filtros', 'unidades', 'anos', 'tipoBolsa', 'isTi', 'isYoda', 'isJedi'));
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
