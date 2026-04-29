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
        'homologado' => 'homologado',
        'homologado_data' => 'data_homologacao',
        'homologado_por' => 'usuario_homologacao',
        'homologado_justificativa' => 'justificativa_homologacao',
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
        ])
            ->where(['Fases.deleted' => 0])
            ->orderBy(['Fases.nome' => 'ASC'])
            ->toArray();
        $homologacao = [
            'T' => 'Todas',
            'P' => 'Não verificadas',
            'S' => 'Homologadas',
            'N' => 'Não homologadas',
        ];
        $herancaOptions = [
            'T' => 'Todas',
            'S' => 'Sim',
            'N' => 'Não',
        ];
        $programa = [];
        $origem = $this->origem ?? [];
        $cotas = $this->cota ?? [];
        $this->set(compact('situacao', 'programa', 'origem', 'cotas', 'tipo', 'prog', 'homologacao', 'herancaOptions'));
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
            'tem_relatorio' => (string)$this->request->getQuery('tem_relatorio', ''),
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
                'Raics.relatorio',
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
        if ($filtros['tem_relatorio'] === 'S') {
            $query->where([
                'Raics.relatorio IS NOT' => null,
                'Raics.relatorio <>' => '',
            ]);
        } elseif ($filtros['tem_relatorio'] === 'N') {
            $query->where(function ($exp) {
                return $exp->or([
                    'Raics.relatorio IS' => null,
                    'Raics.relatorio' => '',
                ]);
            });
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
                'tem_relatorio',
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
                    trim((string)($raic->relatorio ?? '')) !== '' ? 'Sim' : 'Não',
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

    public function listaAvaliadoresRaic()
    {
        $this->request->allowMethod(['get']);

        $identity = $this->getIdentityAtual();
        $jedi = '';
        if (is_array($identity)) {
            $jedi = (string)($identity['jedi'] ?? '');
        } elseif ($identity !== null) {
            $jedi = (string)($identity->jedi ?? '');
        }
        if ($jedi === '') {
            $jedi = (string)($this->request->getAttribute('identity')['jedi'] ?? '');
        }

        if (!$this->ehYoda() && $jedi === '') {
            $this->Flash->error('Área restrita à Coordenação da Unidade e à Gestão de Fomento.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }

        $unidades = $this->obterUnidadesDisponiveisAvaliadores();
        $filtros = $this->obterFiltrosListaAvaliadoresRaic($unidades);
        $anosOptions = $this->obterAnosListaAvaliadoresPorTipo('R');
        if (!isset($anosOptions[$filtros['ano']])) {
            $filtros['ano'] = (int)array_key_first($anosOptions);
        }

        $query = $this->montarQueryListaAvaliadoresRaic($filtros);

        if ((int)$this->request->getQuery('exportar', 0) === 1) {
            return $this->exportarListaAvaliadoresRaicCsv($query);
        }

        $avaliadores = $this->paginate($query, ['limit' => 20]);

        $this->set(compact('avaliadores', 'anosOptions', 'unidades', 'filtros'));
    }

    public function listaAvaliadoresNova()
    {
        $this->request->allowMethod(['get']);

        if (!$this->ehYoda()) {
            $this->Flash->error('Área restrita à Gestão de Fomento.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }

        $filtros = $this->obterFiltrosListaAvaliadoresNova();
        $anosOptions = $this->obterAnosListaAvaliadoresPorTipo('N');
        if (!isset($anosOptions[$filtros['ano']])) {
            $filtros['ano'] = (int)array_key_first($anosOptions);
        }

        $editais = $this->obterEditaisListaAvaliadoresNova();
        if ($filtros['editai_id'] !== 0 && !isset($editais[$filtros['editai_id']])) {
            $filtros['editai_id'] = 0;
        }

        $grandesAreas = $this->obterGrandesAreasRestritasAvaliadores();
        if ($filtros['grandes_area_id'] !== 0 && !isset($grandesAreas[$filtros['grandes_area_id']])) {
            $filtros['grandes_area_id'] = 0;
        }

        $areas = $this->obterAreasPorGrandeAreaAvaliadores((int)$filtros['grandes_area_id']);
        if ($filtros['area_id'] !== 0 && !isset($areas[$filtros['area_id']])) {
            $filtros['area_id'] = 0;
        }

        $query = $this->montarQueryListaAvaliadoresNova($filtros);

        if ((int)$this->request->getQuery('exportar', 0) === 1) {
            return $this->exportarListaAvaliadoresNovaCsv($query);
        }

        $avaliadores = $this->paginate($query, ['limit' => 20]);

        $this->set(compact('avaliadores', 'anosOptions', 'editais', 'grandesAreas', 'areas', 'filtros'));
    }

    public function listaInscricoesAvaliadores()
    {
        $this->request->allowMethod(['get']);

        if (!$this->ehYoda()) {
            $this->Flash->error('Área restrita à Gestão de Fomento.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }

        $filtros = $this->obterFiltrosListaInscricoesAvaliadores();
        $editais = $this->obterEditaisAbertosInscricoesAvaliadores();
        if ($filtros['editai_id'] !== 0 && !isset($editais[$filtros['editai_id']])) {
            $filtros['editai_id'] = 0;
        }

        $grandesAreas = $this->obterGrandesAreasRestritasAvaliadores();
        if ($filtros['grandes_area_id'] !== 0 && !isset($grandesAreas[$filtros['grandes_area_id']])) {
            $filtros['grandes_area_id'] = 0;
        }

        $areas = $this->obterAreasPorGrandeAreaAvaliadores((int)$filtros['grandes_area_id']);
        if ($filtros['area_id'] !== 0 && !isset($areas[$filtros['area_id']])) {
            $filtros['area_id'] = 0;
        }

        $statusOptions = [
            'vinculado' => 'Vinculado (2 avaliadores)',
            'nao_vinculado' => 'Não vinculado (0 avaliadores)',
        ];
        if ($filtros['status_vinculo'] !== '' && !isset($statusOptions[$filtros['status_vinculo']])) {
            $filtros['status_vinculo'] = '';
        }

        $homologadoOptions = [
            'P' => 'Não verificada',
            'S' => 'Homologada',
            'N' => 'Não homologada',
        ];
        if ($filtros['homologado'] !== '' && !isset($homologadoOptions[$filtros['homologado']])) {
            $filtros['homologado'] = '';
        }

        $query = $this->montarQueryListaInscricoesAvaliadores($filtros);
        $inscricoes = $this->paginate($query, ['limit' => 12]);

        $this->set(compact('inscricoes', 'editais', 'grandesAreas', 'areas', 'filtros', 'statusOptions', 'homologadoOptions'));
    }

    public function dashcountavaliadores()
    {
        $this->request->allowMethod(['get']);

        if (!$this->ehYoda()) {
            $this->Flash->error('Área restrita à Gestão de Fomento.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }

        $dashcountavaliadoresTable = $this->fetchTable('Dashcountavaliadores');
        $anosOptions = $this->obterAnosListaAvaliadoresPorTipo('N');

        $ano = trim((string)$this->request->getQuery('ano', ''));
        $ordenacao = trim((string)$this->request->getQuery('ordenacao', 'nome'));
        $totalMinimo = trim((string)$this->request->getQuery('total_minimo', ''));
        $totalMaximo = trim((string)$this->request->getQuery('total_maximo', ''));
        $buscaSolicitada = $ano !== '';
        $registros = null;

        $ordenacoesValidas = [
            'nome',
            'total',
        ];
        if (!in_array($ordenacao, $ordenacoesValidas, true)) {
            $ordenacao = 'nome';
        }
        if ($totalMinimo !== '' && !ctype_digit($totalMinimo)) {
            $totalMinimo = '';
        }
        if ($totalMaximo !== '' && !ctype_digit($totalMaximo)) {
            $totalMaximo = '';
        }

        if ($buscaSolicitada) {
            if (!isset($anosOptions[$ano])) {
                $this->Flash->error('Selecione um ano válido para realizar a consulta.');
                return $this->redirect(['controller' => 'Listas', 'action' => 'dashcountavaliadores']);
            }

            $avaliadoresBase = $this->fetchTable('Avaliadors')->find()
                ->select([
                    'usuario_id' => 'Avaliadors.usuario_id',
                    'usuario_nome' => 'Usuarios.nome',
                    'ano' => 'Avaliadors.ano_convite',
                ])
                ->leftJoinWith('Usuarios')
                ->where([
                    'Avaliadors.deleted' => 0,
                    'Avaliadors.tipo_avaliador' => 'N',
                    'Avaliadors.ano_convite' => $ano,
                ])
                ->groupBy([
                    'Avaliadors.usuario_id',
                    'Usuarios.nome',
                    'Avaliadors.ano_convite',
                ])
                ->orderBy([
                    'Usuarios.nome' => 'ASC',
                ])
                ->enableHydration(false)
                ->toArray();

            $cargas = $dashcountavaliadoresTable->find()
                ->where(['Dashcountavaliadores.ano' => $ano])
                ->enableHydration(false)
                ->toArray();

            $resumoPorUsuario = [];
            foreach ($avaliadoresBase as $avaliador) {
                $usuarioId = (int)($avaliador['usuario_id'] ?? 0);
                $chave = $usuarioId . '|' . (string)$ano;
                $resumoPorUsuario[$chave] = [
                    'usuario_id' => $usuarioId,
                    'usuario_nome' => (string)($avaliador['usuario_nome'] ?? 'Não informado'),
                    'ano' => (string)$ano,
                    'finalizadas' => 0,
                    'aguardando' => 0,
                    'deletadas' => 0,
                    'total' => 0,
                ];
            }

            foreach ($cargas as $carga) {
                $usuarioId = (int)($carga['usuario_id'] ?? 0);
                $chave = $usuarioId . '|' . (string)$ano;
                if (!isset($resumoPorUsuario[$chave])) {
                    $resumoPorUsuario[$chave] = [
                        'usuario_id' => $usuarioId,
                        'usuario_nome' => (string)($carga['usuario_nome'] ?? 'Não informado'),
                        'ano' => (string)$ano,
                        'finalizadas' => 0,
                        'aguardando' => 0,
                        'deletadas' => 0,
                        'total' => 0,
                    ];
                }

                $situacao = (string)($carga['situacao'] ?? '');
                $deleted = (int)($carga['deleted'] ?? 0);
                $qtd = (int)($carga['qtd_inscricoes'] ?? 0);

                if ($deleted === 1) {
                    $resumoPorUsuario[$chave]['deletadas'] += $qtd;
                    continue;
                }

                if ($situacao === 'F') {
                    $resumoPorUsuario[$chave]['finalizadas'] += $qtd;
                } elseif ($situacao === 'E') {
                    $resumoPorUsuario[$chave]['aguardando'] += $qtd;
                }
                $resumoPorUsuario[$chave]['total'] += $qtd;
            }

            $registros = array_values($resumoPorUsuario);
            if ($totalMinimo !== '' || $totalMaximo !== '') {
                $minimo = $totalMinimo !== '' ? (int)$totalMinimo : null;
                $maximo = $totalMaximo !== '' ? (int)$totalMaximo : null;
                $registros = array_values(array_filter($registros, function (array $registro) use ($minimo, $maximo): bool {
                    $total = (int)($registro['total'] ?? 0);
                    if ($minimo !== null && $total < $minimo) {
                        return false;
                    }
                    if ($maximo !== null && $total > $maximo) {
                        return false;
                    }
                    return true;
                }));
            }
            usort($registros, function (array $a, array $b) use ($ordenacao): int {
                if ($ordenacao === 'total') {
                    $comparacaoTotal = ((int)$b['total']) <=> ((int)$a['total']);
                    if ($comparacaoTotal !== 0) {
                        return $comparacaoTotal;
                    }
                }

                return strcasecmp((string)$a['usuario_nome'], (string)$b['usuario_nome']);
            });
        }

        $filtros = [
            'ano' => $ano,
            'ordenacao' => $ordenacao,
            'total_minimo' => $totalMinimo,
            'total_maximo' => $totalMaximo,
        ];
        $ordenacaoOptions = [
            'nome' => 'Nome',
            'total' => 'Maior total',
        ];
        $this->set(compact('anosOptions', 'filtros', 'buscaSolicitada', 'registros', 'ordenacaoOptions'));
    }

    protected function montarQueryListaAvaliadoresRaic(array $filtros)
    {
        $query = $this->fetchTable('Avaliadors')->find()
            ->contain([
                'Usuarios' => ['Unidades', 'Vinculos'],
                'GrandesAreas',
                'Areas',
                'Editais',
                'Unidades',
            ])
            ->leftJoinWith('Usuarios')
            ->leftJoinWith('Editais')
            ->leftJoinWith('Unidades')
            ->where([
                'Avaliadors.deleted' => 0,
                'Avaliadors.tipo_avaliador' => 'R',
                'Avaliadors.ano_convite' => (string)$filtros['ano'],
            ])
            ->orderBy([
                'Usuarios.nome' => 'ASC',
                'Editais.nome' => 'ASC',
                'Unidades.sigla' => 'ASC',
            ]);

        if ((int)$filtros['unidade_id'] > 0) {
            $query->where(['Avaliadors.unidade_id' => (int)$filtros['unidade_id']]);
        } elseif (!$this->ehYoda()) {
            $idsJedi = $this->obterIdsJediAvaliadores();
            if ($idsJedi === []) {
                $query->where(['1 = 0']);
            } else {
                $query->where(['Avaliadors.unidade_id IN' => $idsJedi]);
            }
        }

        if ((string)$filtros['nome'] !== '') {
            $query->where([
                'Usuarios.nome LIKE' => '%' . (string)$filtros['nome'] . '%',
            ]);
        }

        return $query;
    }

    protected function montarQueryListaAvaliadoresNova(array $filtros)
    {
        $query = $this->fetchTable('Avaliadors')->find()
            ->contain([
                'Usuarios' => ['Unidades', 'Vinculos'],
                'GrandesAreas',
                'Areas',
                'Editais',
            ])
            ->leftJoinWith('Usuarios')
            ->leftJoinWith('Editais')
            ->leftJoinWith('GrandesAreas')
            ->leftJoinWith('Areas')
            ->where([
                'Avaliadors.deleted' => 0,
                'Avaliadors.tipo_avaliador' => 'N',
                'Avaliadors.ano_convite' => (string)$filtros['ano'],
            ])
            ->orderBy([
                'Usuarios.nome' => 'ASC',
                'Editais.nome' => 'ASC',
            ]);

        if ((string)$filtros['nome'] !== '') {
            $query->where([
                'Usuarios.nome LIKE' => '%' . (string)$filtros['nome'] . '%',
            ]);
        }

        if ((int)$filtros['editai_id'] > 0) {
            $query->where(['Avaliadors.editai_id' => (int)$filtros['editai_id']]);
        }

        if ((int)$filtros['grandes_area_id'] > 0) {
            $query->where(['Avaliadors.grandes_area_id' => (int)$filtros['grandes_area_id']]);
        }

        if ((int)$filtros['area_id'] > 0) {
            $query->where(['Avaliadors.area_id' => (int)$filtros['area_id']]);
        }

        return $query;
    }

    protected function obterFiltrosListaAvaliadoresRaic(array $unidades): array
    {
        $anoAtual = (int)date('Y');
        $filtros = [
            'ano' => (int)$this->request->getQuery('ano', $anoAtual),
            'unidade_id' => (int)$this->request->getQuery('unidade_id', 0),
            'nome' => trim((string)$this->request->getQuery('nome', '')),
        ];

        if ($filtros['unidade_id'] !== 0 && !isset($unidades[$filtros['unidade_id']])) {
            $filtros['unidade_id'] = 0;
        }

        return $filtros;
    }

    protected function obterFiltrosListaAvaliadoresNova(): array
    {
        $anoAtual = (int)date('Y');

        return [
            'ano' => (int)$this->request->getQuery('ano', $anoAtual),
            'nome' => trim((string)$this->request->getQuery('nome', '')),
            'editai_id' => (int)$this->request->getQuery('editai_id', 0),
            'grandes_area_id' => (int)$this->request->getQuery('grandes_area_id', 0),
            'area_id' => (int)$this->request->getQuery('area_id', 0),
        ];
    }

    protected function obterFiltrosListaInscricoesAvaliadores(): array
    {
        $statusVinculo = $this->request->getQuery('status_vinculo');
        if ($statusVinculo === null) {
            $statusVinculo = 'nao_vinculado';
        }

        return [
            'editai_id' => (int)$this->request->getQuery('editai_id', 0),
            'status_vinculo' => trim((string)$statusVinculo),
            'homologado' => strtoupper(trim((string)$this->request->getQuery('homologado', ''))),
            'grandes_area_id' => (int)$this->request->getQuery('grandes_area_id', 0),
            'area_id' => (int)$this->request->getQuery('area_id', 0),
        ];
    }

    protected function obterAnosListaAvaliadoresPorTipo(string $tipoAvaliador): array
    {
        $anoAtual = (int)date('Y');
        $anos = $this->fetchTable('Avaliadors')->find()
            ->select(['ano_convite'])
            ->where([
                'Avaliadors.deleted' => 0,
                'Avaliadors.tipo_avaliador' => $tipoAvaliador,
                'Avaliadors.ano_convite IS NOT' => null,
                'Avaliadors.ano_convite <>' => '',
            ])
            ->distinct(['ano_convite'])
            ->orderBy(['Avaliadors.ano_convite' => 'DESC'])
            ->enableHydration(false)
            ->all()
            ->extract('ano_convite')
            ->toList();

        $anosOptions = [];
        foreach ($anos as $ano) {
            $anoInt = (int)$ano;
            if ($anoInt > 0) {
                $anosOptions[$anoInt] = (string)$anoInt;
            }
        }
        if ($anosOptions === []) {
            $anosOptions[$anoAtual] = (string)$anoAtual;
        }

        return $anosOptions;
    }

    protected function exportarListaAvaliadoresRaicCsv($query)
    {
        $avaliadores = $query->all();
        $header = [
            'nome_avaliador',
            'cpf',
            'grande_area',
            'area',
            'ano',
            'edital',
            'unidade_avaliacao',
            'vinculo_usuario',
            'unidade_cadastro_usuario',
            'telefone',
            'telefone_contato',
            'celular',
            'whatsapp',
            'email',
            'email_alternativo',
            'email_contato',
        ];

        $fh = fopen('php://temp', 'r+');
        fwrite($fh, "\xEF\xBB\xBF");
        fputcsv($fh, $header, ';');

        foreach ($avaliadores as $avaliador) {
            $cpf = (string)($avaliador->usuario->cpf ?? '');
            if ($cpf !== '') {
                $cpf = "\t" . $cpf;
            }

            $row = [
                $avaliador->usuario->nome ?? '',
                $cpf,
                $avaliador->grandes_area->nome ?? '',
                $avaliador->area->nome ?? '',
                $avaliador->ano_convite ?? '',
                $avaliador->editai->nome ?? '',
                $avaliador->unidade->sigla ?? '',
                $avaliador->usuario->vinculo->nome ?? '',
                $avaliador->usuario->unidade->sigla ?? '',
                $avaliador->usuario->telefone ?? '',
                $avaliador->usuario->telefone_contato ?? '',
                $avaliador->usuario->celular ?? '',
                $avaliador->usuario->whatsapp ?? '',
                $avaliador->usuario->email ?? '',
                $avaliador->usuario->email_alternativo ?? '',
                $avaliador->usuario->email_contato ?? '',
            ];
            $row = array_map(function ($value): string {
                $text = (string)$value;
                $text = str_replace(["\r\n", "\n", "\r", "\0"], ' ', $text);
                return $text;
            }, $row);
            fputcsv($fh, $row, ';');
        }

        rewind($fh);
        $csv = (string)stream_get_contents($fh);
        fclose($fh);

        return $this->response
            ->withType('csv')
            ->withCharset('UTF-8')
            ->withStringBody($csv)
            ->withDownload('avaliadores_raic_' . date('Ymd_His') . '.csv');
    }

    protected function exportarListaAvaliadoresNovaCsv($query)
    {
        $avaliadores = $query->all();
        $header = [
            'nome_avaliador',
            'cpf',
            'grande_area',
            'area',
            'ano',
            'edital',
            'vinculo_usuario',
            'unidade_cadastro_usuario',
            'telefone',
            'telefone_contato',
            'celular',
            'whatsapp',
            'email',
            'email_alternativo',
            'email_contato',
        ];

        $fh = fopen('php://temp', 'r+');
        fwrite($fh, "\xEF\xBB\xBF");
        fputcsv($fh, $header, ';');

        foreach ($avaliadores as $avaliador) {
            $cpf = (string)($avaliador->usuario->cpf ?? '');
            if ($cpf !== '') {
                $cpf = "\t" . $cpf;
            }

            $row = [
                $avaliador->usuario->nome ?? '',
                $cpf,
                $avaliador->grandes_area->nome ?? '',
                $avaliador->area->nome ?? '',
                $avaliador->ano_convite ?? '',
                $avaliador->editai->nome ?? '',
                $avaliador->usuario->vinculo->nome ?? '',
                $avaliador->usuario->unidade->sigla ?? '',
                $avaliador->usuario->telefone ?? '',
                $avaliador->usuario->telefone_contato ?? '',
                $avaliador->usuario->celular ?? '',
                $avaliador->usuario->whatsapp ?? '',
                $avaliador->usuario->email ?? '',
                $avaliador->usuario->email_alternativo ?? '',
                $avaliador->usuario->email_contato ?? '',
            ];
            $row = array_map(function ($value): string {
                $text = (string)$value;
                $text = str_replace(["\r\n", "\n", "\r", "\0"], ' ', $text);
                return $text;
            }, $row);
            fputcsv($fh, $row, ';');
        }

        rewind($fh);
        $csv = (string)stream_get_contents($fh);
        fclose($fh);

        return $this->response
            ->withType('csv')
            ->withCharset('UTF-8')
            ->withStringBody($csv)
            ->withDownload('avaliadores_nova_' . date('Ymd_His') . '.csv');
    }

    protected function obterEditaisListaAvaliadoresNova(): array
    {
        return $this->fetchTable('Editais')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where([
                'Editais.deleted' => 0,
                'Editais.origem IN' => ['N', 'R', 'J', 'W'],
            ])
            ->orderBy(['Editais.nome' => 'ASC'])
            ->toArray();
    }

    protected function obterEditaisAbertosInscricoesAvaliadores(): array
    {
        return $this->fetchTable('Editais')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where([
                'Editais.deleted' => 0,
                'Editais.origem IN' => ['N', 'R'],
                'Editais.inicio_avaliar < NOW()',
                'Editais.fim_avaliar > NOW()',
            ])
            ->orderBy(['Editais.nome' => 'ASC'])
            ->toArray();
    }

    protected function obterGrandesAreasRestritasAvaliadores(): array
    {
        return $this->fetchTable('GrandesAreas')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where(['GrandesAreas.id <' => 10])
            ->orderBy(['GrandesAreas.nome' => 'ASC'])
            ->toArray();
    }

    protected function obterAreasPorGrandeAreaAvaliadores(int $grandeAreaId): array
    {
        if ($grandeAreaId <= 0) {
            return [];
        }

        return $this->fetchTable('Areas')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where(['Areas.grandes_area_id' => $grandeAreaId])
            ->orderBy(['Areas.nome' => 'ASC'])
            ->toArray();
    }

    protected function montarQueryListaInscricoesAvaliadores(array $filtros)
    {
        $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $query = $projetoBolsistas->find()
            ->select([
                'ProjetoBolsistas.id',
                'ProjetoBolsistas.editai_id',
                'ProjetoBolsistas.projeto_id',
                'ProjetoBolsistas.bolsista',
                'ProjetoBolsistas.orientador',
                'ProjetoBolsistas.origem',
                'ProjetoBolsistas.sp_titulo',
                'ProjetoBolsistas.homologado',
                'ProjetoBolsistas.created',
                'projeto_titulo' => 'Projetos.titulo',
                'area_nome' => 'Areas.nome',
                'grande_area_nome' => 'GrandesAreas.nome',
                'total_avaliadores' => $projetoBolsistas->find()->newExpr('COALESCE(av_vinculos.total_avaliadores, 0)'),
                'avaliadores_nomes' => $projetoBolsistas->find()->newExpr('av_vinculos.avaliadores_nomes'),
                'avaliadores_status' => $projetoBolsistas->find()->newExpr('av_vinculos.avaliadores_status'),
            ])
            ->contain([
                'Editais' => function ($q) {
                    return $q->select(['Editais.id', 'Editais.nome', 'Editais.origem']);
                },
                'Bolsistas' => function ($q) {
                    return $q->select(['Bolsistas.id', 'Bolsistas.nome', 'Bolsistas.cpf', 'Bolsistas.unidade_id', 'Bolsistas.vinculo_id'])
                        ->contain([
                            'Unidades' => function ($qu) {
                                return $qu->select(['Unidades.id', 'Unidades.sigla']);
                            },
                            'Vinculos' => function ($qu) {
                                return $qu->select(['Vinculos.id', 'Vinculos.nome']);
                            },
                        ]);
                },
                'Coorientadores' => function ($q) {
                    return $q->select(['Coorientadores.id', 'Coorientadores.nome']);
                },
                'Orientadores' => function ($q) {
                    return $q->select(['Orientadores.id', 'Orientadores.nome', 'Orientadores.cpf', 'Orientadores.unidade_id', 'Orientadores.vinculo_id'])
                        ->contain([
                            'Unidades' => function ($qu) {
                                return $qu->select(['Unidades.id', 'Unidades.sigla']);
                            },
                            'Vinculos' => function ($qu) {
                                return $qu->select(['Vinculos.id', 'Vinculos.nome']);
                            },
                        ]);
                },
                'Projetos' => function ($q) {
                    return $q->select(['Projetos.id', 'Projetos.titulo', 'Projetos.area_id'])
                        ->contain([
                            'Areas' => function ($qu) {
                                return $qu->select(['Areas.id', 'Areas.nome', 'Areas.grandes_area_id'])
                                    ->contain([
                                        'GrandesAreas' => function ($qg) {
                                            return $qg->select(['GrandesAreas.id', 'GrandesAreas.nome']);
                                        },
                                    ]);
                            },
                        ]);
                },
            ])
            ->innerJoinWith('Editais')
            ->leftJoinWith('Bolsistas')
            ->leftJoinWith('Orientadores')
            ->leftJoinWith('Projetos.Areas.GrandesAreas')
            ->leftJoin(
                [
                    'av_vinculos' => '(
                        SELECT
                            ab.projeto_bolsista_id,
                            COUNT(*) AS total_avaliadores,
                            GROUP_CONCAT(DISTINCT u.nome ORDER BY ab.ordem, u.nome SEPARATOR " | ") AS avaliadores_nomes,
                            GROUP_CONCAT(
                                DISTINCT CONCAT(
                                    COALESCE(u.nome, ""),
                                    "||",
                                    COALESCE(ab.situacao, "")
                                )
                                ORDER BY ab.ordem, u.nome
                                SEPARATOR " | "
                            ) AS avaliadores_status
                        FROM avaliador_bolsistas ab
                        LEFT JOIN usuarios u ON u.id = ab.usuario_id
                        WHERE ab.projeto_bolsista_id IS NOT NULL
                          AND ab.deleted = 0
                        GROUP BY ab.projeto_bolsista_id
                    )',
                ],
                'av_vinculos.projeto_bolsista_id = ProjetoBolsistas.id'
            )
            ->where([
                'ProjetoBolsistas.deleted IS' => null,
                'ProjetoBolsistas.fase_id' => 4,
                'Editais.deleted' => 0,
                'Editais.origem IN' => ['N', 'R'],
                'Editais.inicio_avaliar < NOW()',
                'Editais.fim_avaliar > NOW()',
            ])
            ->orderBy([
                'Editais.nome' => 'ASC',
            'Bolsistas.nome' => 'ASC',
            'ProjetoBolsistas.id' => 'DESC',
        ]);

        if (in_array($filtros['homologado'] ?? '', ['S', 'N'], true)) {
            $query->where(['ProjetoBolsistas.homologado' => $filtros['homologado']]);
        } elseif (($filtros['homologado'] ?? '') === 'P') {
            $query->where(['ProjetoBolsistas.homologado IS' => null]);
        }

        if ((int)$filtros['editai_id'] > 0) {
            $query->where(['ProjetoBolsistas.editai_id' => (int)$filtros['editai_id']]);
        }

        if ((int)$filtros['grandes_area_id'] > 0) {
            $query->where(['Areas.grandes_area_id' => (int)$filtros['grandes_area_id']]);
        }

        if ((int)$filtros['area_id'] > 0) {
            $query->where(['Projetos.area_id' => (int)$filtros['area_id']]);
        }

        if ($filtros['status_vinculo'] === 'vinculado') {
            $query->where(['COALESCE(av_vinculos.total_avaliadores, 0) = 2']);
        } elseif ($filtros['status_vinculo'] === 'nao_vinculado') {
            $query->where(['COALESCE(av_vinculos.total_avaliadores, 0) = 0']);
        }

        return $query;
    }

    protected function obterUnidadesDisponiveisAvaliadores(): array
    {
        $query = $this->fetchTable('Unidades')->find('list', [
            'keyField' => 'id',
            'valueField' => 'sigla',
        ])
            ->where(['Unidades.deleted' => 0])
            ->orderBy(['Unidades.sigla' => 'ASC']);

        if ($this->ehYoda()) {
            return $query->toArray();
        }

        $idsJedi = $this->obterIdsJediAvaliadores();
        if ($idsJedi === []) {
            return [];
        }

        return $query->where(['Unidades.id IN' => $idsJedi])->toArray();
    }

    protected function obterIdsJediAvaliadores(): array
    {
        $identity = $this->getIdentityAtual();
        $jedi = '';
        if (is_array($identity)) {
            $jedi = (string)($identity['jedi'] ?? '');
        } elseif ($identity !== null) {
            $jedi = (string)($identity->jedi ?? '');
        }
        if ($jedi === '') {
            $jedi = (string)($this->request->getAttribute('identity')['jedi'] ?? '');
        }

        return array_values(array_filter(array_map('intval', array_map('trim', explode(',', $jedi)))));
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
                    } elseif ($col === 'homologado') {
                        $key = strtoupper((string)$val);
                        $val = match ($key) {
                            'S' => 'Homologada',
                            'N' => 'Não homologada',
                            default => 'Não verificada',
                        };
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
        ])
            ->where(['Fases.deleted' => 0])
            ->orderBy(['Fases.nome' => 'ASC'])
            ->toArray();
        $homologacao = [
            'T' => 'Todas',
            'P' => 'Não verificadas',
            'S' => 'Homologadas',
            'N' => 'Não homologadas',
        ];
        $herancaOptions = [
            'T' => 'Todas',
            'S' => 'Sim',
            'N' => 'Não',
        ];

        $this->set([
            'listas' => $listas,
            'situacao' => $situacao,
            'programa' => [],
            'origem' => $this->origem ?? [],
            'cotas' => $this->cota ?? [],
            'tipo' => $tipo,
            'prog' => $prog,
            'homologacao' => $homologacao,
            'herancaOptions' => $herancaOptions,
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

        $columns = array_values(array_filter($columns, static function ($col) {
            return !in_array($col, self::PADRAO_INSCRICAO_EXCLUIR, true);
        }));

        $colunasHomologacao = [
            'homologado',
            'homologado_data',
            'homologado_por',
            'homologado_justificativa',
        ];
        foreach ($colunasHomologacao as $colunaHomologacao) {
            if (!in_array($colunaHomologacao, $columns, true)) {
                $columns[] = $colunaHomologacao;
            }
        }

        return $columns;
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

        $homologado = strtoupper(trim((string)($dados['homologado'] ?? 'T')));
        if ($homologado === 'S') {
            $conditions[] = ['Geral.homologado' => 'S'];
        } elseif ($homologado === 'N') {
            $conditions[] = ['Geral.homologado' => 'N'];
        } elseif ($homologado === 'P') {
            $conditions[] = ['Geral.homologado IS' => null];
        }

        if (!empty($dados['origem'])) {
            $conditions[] = ['Geral.origem' => (string)$dados['origem']];
        }

        if (!empty($dados['cota'])) {
            $conditions[] = ['Geral.cota' => (string)$dados['cota']];
        }

        $heranca = strtoupper(trim((string)($dados['heranca'] ?? 'T')));
        if ($heranca === 'S') {
            $conditions[] = ['Geral.heranca' => 1];
        } elseif ($heranca === 'N') {
            $conditions[] = [
                'OR' => [
                    ['Geral.heranca' => 0],
                    ['Geral.heranca IS' => null],
                ],
            ];
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
