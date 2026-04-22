<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;

class RestritoController extends AppController
{
    protected $TiExceptions;
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setLayout('admin');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        if (!$this->ehTi()) {
            $this->Flash->error('Acesso restrito a TI.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }
    }

    public function index()
    {
        $atalhos = [
            [
                'titulo' => 'Usuarios',
                'descricao' => 'Gestao e manutencao de usuarios',
                'url' => ['controller' => 'Users', 'action' => 'index'],
                'class' => 'btn-primary',
                'icon' => 'fas fa-users',
            ],
            [
                'titulo' => 'Cadastrar Usuario',
                'descricao' => 'Cadastro manual de usuario (TI)',
                'url' => ['controller' => 'Users', 'action' => 'cadastrarUsuarioCpf'],
                'class' => 'btn-warning',
                'icon' => 'fas fa-user-plus',
            ],
            [
                'titulo' => 'Erros do Sistema',
                'descricao' => 'Excecoes registradas e suporte',
                'url' => ['controller' => 'Restrito', 'action' => 'erros'],
                'class' => 'btn-danger',
                'icon' => 'fas fa-bug',
            ],
            [
                'titulo' => 'Editais',
                'descricao' => 'Cadastro e gestao de editais',
                'url' => ['controller' => 'Editais', 'action' => 'lista'],
                'class' => 'btn-success',
                'icon' => 'fas fa-bullhorn',
            ],
            [
                'titulo' => 'Modelos em Lote',
                'descricao' => 'Aplicar um mesmo modelo em varios editais',
                'url' => ['controller' => 'Editais', 'action' => 'lista', '#' => 'carga-modelo-lote'],
                'class' => 'btn-primary',
                'icon' => 'fas fa-upload',
            ],
            [
                'titulo' => 'Blocos de Sumula',
                'descricao' => 'Gerencie blocos vinculados aos editais',
                'url' => ['controller' => 'Editais', 'action' => 'sumulasblocos'],
                'class' => 'btn-info',
                'icon' => 'fas fa-layer-group',
            ],
            [
                'titulo' => 'Upload de Arquivos',
                'descricao' => 'Enviar arquivo para pastas internas de uploads',
                'url' => ['controller' => 'Restrito', 'action' => 'uploadArquivos'],
                'class' => 'btn-secondary',
                'icon' => 'fas fa-file-upload',
            ],
            [
                'titulo' => 'Vitrines',
                'descricao' => 'Manutenção de vitrines (criar, alterar e deletar)',
                'url' => ['controller' => 'Restrito', 'action' => 'vitrinesLista'],
                'class' => 'btn-dark',
                'icon' => 'fas fa-images',
            ],
            [
                'titulo' => 'Manuais',
                'descricao' => 'Manutenção de manuais (incluir, editar e excluir)',
                'url' => ['controller' => 'Restrito', 'action' => 'manuaisLista'],
                'class' => 'btn-secondary',
                'icon' => 'fas fa-book',
            ],
            [
                'titulo' => 'Mensagens',
                'descricao' => 'Cadastro de mensagens com popup para home e área interna',
                'url' => ['controller' => 'Restrito', 'action' => 'mensagensLista'],
                'class' => 'btn-info',
                'icon' => 'fas fa-comment-dots',
            ],
            [
                'titulo' => 'Calendários',
                'descricao' => 'Gerenciar feriados, ausências e indisponibilidades',
                'url' => ['controller' => 'Restrito', 'action' => 'calendariosLista'],
                'class' => 'btn-warning',
                'icon' => 'fas fa-calendar-alt',
            ],
            [
                'titulo' => 'Extrair Avaliadores',
                'descricao' => 'Baixar a tabela de avaliadores em formato Excel',
                'url' => ['controller' => 'Restrito', 'action' => 'exportarAvaliadors'],
                'class' => 'btn-success',
                'icon' => 'fas fa-file-excel',
            ],
            [
                'titulo' => 'Carga RAIC Vigentes',
                'descricao' => 'Previa e cria RAIC para bolsistas vigentes filtrando por unidade e edital ativo',
                'url' => ['controller' => 'Restrito', 'action' => 'cargaRaicsVigentes'],
                'class' => 'btn-primary',
                'icon' => 'fas fa-copy',
            ],
        ];

        $this->set(compact('atalhos'));
    }

    public function cargaRaicsVigentes()
    {
        $this->request->allowMethod(['get', 'post']);

        $filtros = [
            'unidade_id' => (int)($this->request->getData('unidade_id', $this->request->getQuery('unidade_id', 0))),
            'editai_id' => (int)($this->request->getData('editai_id', $this->request->getQuery('editai_id', 0))),
            'raic_editai_id' => (int)($this->request->getData('raic_editai_id', $this->request->getQuery('raic_editai_id', 0))),
            'status_lista' => (string)($this->request->getData('status_lista', $this->request->getQuery('status_lista', 'elegiveis'))),
        ];
        $buscaSolicitada = $this->request->is('post') || $this->request->getQuery('filtrar') !== null;

        $editaisAtivos = $this->fetchTable('Editais')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where([
                'Editais.origem IN' => ['R', 'N'],
                'Editais.programa_id <>' => 1,
                'Editais.inicio_vigencia <' => date('Y-m-d H:i:s'),
                'Editais.fim_vigencia >' => date('Y-m-d H:i:s'),
            ])
            ->orderBy(['Editais.nome' => 'ASC'])
            ->toArray();

        $editaisRaicAbertos = $this->fetchTable('Editais')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where([
                'Editais.origem' => 'V',
                'Editais.inicio_inscricao < NOW()',
                'Editais.fim_inscricao > NOW()',
            ])
            ->orderBy(['Editais.nome' => 'ASC'])
            ->toArray();

        $unidades = $this->fetchTable('Unidades')->find('list', [
            'keyField' => 'id',
            'valueField' => 'sigla',
        ])
            ->orderBy(['Unidades.sigla' => 'ASC'])
            ->toArray();

        $totalCandidatos = 0;
        $totalElegiveis = 0;
        $totalInelegiveis = 0;
        $candidatos = [];
        if ($buscaSolicitada && empty($filtros['raic_editai_id'])) {
            $this->Flash->error('Selecione a RAIC para realizar a busca.');
            $buscaSolicitada = false;
        }

        if ($buscaSolicitada) {
            $queryElegiveis = $this->montarQueryCargaRaicsVigentes($filtros, false);
            $queryInelegiveis = $this->montarQueryCargaRaicsVigentes($filtros, true);

            $totalElegiveis = $queryElegiveis->count();
            $totalInelegiveis = $queryInelegiveis->count();
            $totalCandidatos = $totalElegiveis + $totalInelegiveis;

            if ($filtros['status_lista'] === 'todos') {
                $candidatosElegiveis = (clone $queryElegiveis)->limit(150)->all();
                $candidatosInelegiveis = (clone $queryInelegiveis)->limit(150)->all();
                $candidatos = [];
                foreach ($candidatosElegiveis as $item) {
                    $item['situacao_carga'] = 'elegivel';
                    $candidatos[] = $item;
                }
                foreach ($candidatosInelegiveis as $item) {
                    $item['situacao_carga'] = 'inelegivel';
                    $candidatos[] = $item;
                }
            } else {
                $queryListagem = $filtros['status_lista'] === 'inelegiveis' ? $queryInelegiveis : $queryElegiveis;
                $candidatos = (clone $queryListagem)
                    ->limit(300)
                    ->all()
                    ->toArray();
                foreach ($candidatos as &$item) {
                    $item['situacao_carga'] = $filtros['status_lista'] === 'inelegiveis' ? 'inelegivel' : 'elegivel';
                }
                unset($item);
            }
        }

        if ($this->request->is('post') && (string)$this->request->getData('acao') === 'executar') {
            if (empty($filtros['raic_editai_id'])) {
                $this->Flash->error('Selecione o edital RAIC de destino para executar a carga.');
                $this->set(compact('filtros', 'buscaSolicitada', 'editaisAtivos', 'editaisRaicAbertos', 'unidades', 'candidatos', 'totalCandidatos', 'totalElegiveis', 'totalInelegiveis'));
                return;
            }

            if (!$buscaSolicitada) {
                $this->Flash->error('Execute a busca antes de realizar a carga.');
                $this->set(compact('filtros', 'buscaSolicitada', 'editaisAtivos', 'editaisRaicAbertos', 'unidades', 'candidatos', 'totalCandidatos', 'totalElegiveis', 'totalInelegiveis'));
                return;
            }

            if ($totalElegiveis === 0) {
                $this->Flash->info('Nao ha registros elegiveis para processar.');
                return $this->redirect(['action' => 'cargaRaicsVigentes', '?' => array_filter([
                    'unidade_id' => $filtros['unidade_id'],
                    'editai_id' => $filtros['editai_id'],
                    'raic_editai_id' => $filtros['raic_editai_id'],
                    'status_lista' => $filtros['status_lista'],
                ])]);
            }

            $tblRaics = $this->fetchTable('Raics');
            $tblRaicHistoricos = $this->fetchTable('RaicHistoricos');
            $raicEditalId = (int)$filtros['raic_editai_id'];
            $candidatosExecucao = $this->montarQueryCargaRaicsVigentes($filtros, false)->all();

            try {
                $tblRaics->getConnection()->transactional(function () use ($candidatosExecucao, $tblRaics, $tblRaicHistoricos, $raicEditalId): void {
                    foreach ($candidatosExecucao as $bolsista) {
                        $raic = $tblRaics->newEmptyEntity();
                        $raic->usuario_id = !empty($bolsista['bolsista']) ? (int)$bolsista['bolsista'] : null;
                        $raic->orientador = !empty($bolsista['orientador']) ? (int)$bolsista['orientador'] : null;
                        $raic->titulo = !empty($bolsista['sp_titulo']) ? (string)$bolsista['sp_titulo'] : null;
                        $raic->projeto_orientador = !empty($bolsista['projeto_id']) ? (int)$bolsista['projeto_id'] : null;
                        $raic->projeto_bolsista_id = (int)$bolsista['id'];
                        $raic->unidade_id = !empty($bolsista['orientador_unidade_id']) ? (int)$bolsista['orientador_unidade_id'] : null;
                        $raic->editai_id = $raicEditalId;
                        $raic->deleted = 0;
                        $raic->tipo_bolsa = strtoupper((string)($bolsista['tipo_bolsa'] ?? '')) === 'Z' ? 'Z' : 'Z';
                        $raic->usuario_cadastro = (int)$this->request->getAttribute('identity')->id;

                        if (!$tblRaics->save($raic)) {
                            throw new \RuntimeException('Falha ao criar RAIC para a inscricao #' . (int)$bolsista['id'] . '.');
                        }

                        $historico = $tblRaicHistoricos->newEmptyEntity();
                        $historico->raic_id = (int)$raic->id;
                        $historico->usuario_id = (int)$this->request->getAttribute('identity')->id;
                        $historico->alteracao = 'criação ou cadastro massivo';
                        $historico->justificativa = 'carga inicial dos bolsistas vigentes';

                        if (!$tblRaicHistoricos->save($historico)) {
                            throw new \RuntimeException('Falha ao gravar historico da RAIC #' . (int)$raic->id . '.');
                        }
                    }
                });

                $this->Flash->success('Carga concluida com sucesso. ' . $totalElegiveis . ' RAIC(s) criada(s).');
                return $this->redirect(['action' => 'cargaRaicsVigentes', '?' => array_filter([
                    'unidade_id' => $filtros['unidade_id'],
                    'editai_id' => $filtros['editai_id'],
                    'raic_editai_id' => $filtros['raic_editai_id'],
                    'status_lista' => $filtros['status_lista'],
                    'filtrar' => 1,
                ])]);
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - Carga de RAIC de bolsistas vigentes',
                    'Houve um erro ao executar a carga de RAIC. Tente novamente.'
                );

                return $this->redirect(['action' => 'cargaRaicsVigentes', '?' => array_filter([
                    'unidade_id' => $filtros['unidade_id'],
                    'editai_id' => $filtros['editai_id'],
                    'raic_editai_id' => $filtros['raic_editai_id'],
                    'status_lista' => $filtros['status_lista'],
                ])]);
            }
        }

        $this->set(compact(
            'filtros',
            'buscaSolicitada',
            'editaisAtivos',
            'editaisRaicAbertos',
            'unidades',
            'candidatos',
            'totalCandidatos',
            'totalElegiveis',
            'totalInelegiveis'
        ));
    }

    protected function montarQueryCargaRaicsVigentes(array $filtros, bool $inelegiveis)
    {
        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $tblRaics = $this->fetchTable('Raics');

        $query = $tblProjetoBolsistas->find()
            ->select([
                'id' => 'ProjetoBolsistas.id',
                'editai_id' => 'ProjetoBolsistas.editai_id',
                'projeto_id' => 'ProjetoBolsistas.projeto_id',
                'bolsista' => 'ProjetoBolsistas.bolsista',
                'orientador' => 'ProjetoBolsistas.orientador',
                'sp_titulo' => 'ProjetoBolsistas.sp_titulo',
                'tipo_bolsa' => 'ProjetoBolsistas.tipo_bolsa',
                'edital_nome' => 'Editais.nome',
                'bolsista_nome' => 'Bolsistas.nome',
                'orientador_nome' => 'Orientadores.nome',
                'orientador_unidade_id' => 'Orientadores.unidade_id',
                'unidade_sigla' => 'Unidades.sigla',
                'projeto_titulo' => 'Projetos.titulo',
            ])
            ->enableAutoFields(false)
            ->enableHydration(false)
            ->leftJoinWith('Bolsistas')
            ->leftJoinWith('Orientadores.Unidades')
            ->leftJoinWith('Projetos')
            ->innerJoinWith('Editais')
            ->where([
                'ProjetoBolsistas.vigente' => 1,
                'ProjetoBolsistas.deleted IS' => null,
                'Editais.programa_id <>' => 1,
            ])
            ->orderBy([
                'Editais.nome' => 'ASC',
                'Orientadores.nome' => 'ASC',
                'Bolsistas.nome' => 'ASC',
                'ProjetoBolsistas.id' => 'ASC',
            ]);

        if (!empty($filtros['unidade_id'])) {
            $query->where(['Orientadores.unidade_id' => (int)$filtros['unidade_id']]);
        }

        if (!empty($filtros['editai_id'])) {
            $query->where(['ProjetoBolsistas.editai_id' => (int)$filtros['editai_id']]);
        }

        $subqueryDuplicados = $tblRaics->find()
            ->select(['usuario_id'])
            ->where([
                'Raics.deleted' => 0,
                'Raics.editai_id' => (int)$filtros['raic_editai_id'],
                'Raics.usuario_id IS NOT' => null,
            ]);

        $query->where($inelegiveis
            ? ['ProjetoBolsistas.bolsista IN' => $subqueryDuplicados]
            : ['ProjetoBolsistas.bolsista NOT IN' => $subqueryDuplicados]);


        return $query;
    }

    public function exportarAvaliadors()
    {
        $query = $this->fetchTable('Avaliadors')->find();
        $query
            ->select([
                'id' => 'Avaliadors.id',
                'usuario_nome' => 'Usuarios.nome',
                'usuario_email' => 'Usuarios.email',
                'usuario_email_alternativo' => 'Usuarios.email_alternativo',
                'usuario_email_contato' => 'Usuarios.email_contato',
                'usuario_lattes' => 'Usuarios.lattes',
                'usuario_telefone' => 'Usuarios.telefone',
                'usuario_telefone_contato' => 'Usuarios.telefone_contato',
                'usuario_celular' => 'Usuarios.celular',
                'usuario_whatsapp' => 'Usuarios.whatsapp',
                'usuario_vinculo' => 'Vinculos.nome',
                'usuario_unidade' => 'UnidadeAvaliador.nome',
                'unidade_avaliacao' => 'UnidadeAvaliacao.nome',
                'edital_nome' => 'Editais.nome',
                'grande_area' => 'GrandesAreas.nome',
                'area' => 'Areas.nome',
                'ano_aceite' => 'Avaliadors.ano_aceite',
            ])
            ->leftJoin(['Usuarios' => 'usuarios'], ['Usuarios.id = Avaliadors.usuario_id'])
            ->leftJoin(['Vinculos' => 'vinculos'], ['Vinculos.id = Usuarios.vinculo_id'])
            ->leftJoin(['UnidadeAvaliador' => 'unidades'], ['UnidadeAvaliador.id = Usuarios.unidade_id'])
            ->leftJoin(['UnidadeAvaliacao' => 'unidades'], ['UnidadeAvaliacao.id = Avaliadors.unidade_id'])
            ->leftJoin(['Editais' => 'editais'], ['Editais.id = Avaliadors.editai_id'])
            ->leftJoin(['GrandesAreas' => 'grandes_areas'], ['GrandesAreas.id = Avaliadors.grandes_area_id'])
            ->leftJoin(['Areas' => 'areas'], ['Areas.id = Avaliadors.area_id'])
            ->where([
                'Avaliadors.deleted' => 0,
                'Avaliadors.ano_aceite IS NOT' => null,
                'Avaliadors.ano_aceite <>' => '',
            ])
            ->enableHydration(false)
            ->orderBy(['Avaliadors.id' => 'DESC']);

        $header = [
            'id',
            'nome_avaliador',
            'email',
            'email_alternativo',
            'email_contato',
            'lattes',
            'vinculo',
            'unidade_avaliador',
            'telefone',
            'telefone_contato',
            'celular',
            'whatsapp',
            'unidade_avaliacao',
            'edital',
            'grande_area',
            'area',
            'ano_aceite',
        ];

        $tmpFile = tempnam(sys_get_temp_dir(), 'avaliadors_csv_');
        if ($tmpFile === false) {
            throw new \RuntimeException('Não foi possível criar arquivo temporário para a exportação.');
        }

        $fh = fopen($tmpFile, 'w');
        if ($fh === false) {
            throw new \RuntimeException('Não foi possível abrir o arquivo temporário para a exportação.');
        }

        fwrite($fh, "\xEF\xBB\xBF");
        fputcsv($fh, $header, ';');

        $ouNaoInformado = static function ($valor): string {
            $texto = trim((string)($valor ?? ''));
            return $texto !== '' ? $texto : 'não informado';
        };

        foreach ($query as $avaliador) {
            fputcsv($fh, [
                $avaliador['id'] ?? '',
                $ouNaoInformado($avaliador['usuario_nome'] ?? null),
                $ouNaoInformado($avaliador['usuario_email'] ?? null),
                $ouNaoInformado($avaliador['usuario_email_alternativo'] ?? null),
                $ouNaoInformado($avaliador['usuario_email_contato'] ?? null),
                $ouNaoInformado($avaliador['usuario_lattes'] ?? null),
                $ouNaoInformado($avaliador['usuario_vinculo'] ?? null),
                $ouNaoInformado($avaliador['usuario_unidade'] ?? null),
                $ouNaoInformado($avaliador['usuario_telefone'] ?? null),
                $ouNaoInformado($avaliador['usuario_telefone_contato'] ?? null),
                $ouNaoInformado($avaliador['usuario_celular'] ?? null),
                $ouNaoInformado($avaliador['usuario_whatsapp'] ?? null),
                $ouNaoInformado($avaliador['unidade_avaliacao'] ?? null),
                $ouNaoInformado($avaliador['edital_nome'] ?? null),
                $ouNaoInformado($avaliador['grande_area'] ?? null),
                $ouNaoInformado($avaliador['area'] ?? null),
                $ouNaoInformado($avaliador['ano_aceite'] ?? null),
            ], ';');
        }

        fclose($fh);
        register_shutdown_function(static function ($path): void {
            if (is_string($path) && is_file($path)) {
                @unlink($path);
            }
        }, $tmpFile);

        return $this->response->withFile($tmpFile, [
            'download' => true,
            'name' => 'avaliadors_' . date('Ymd_His') . '.csv',
        ]);
    }

    public function vitrines($id = null)
    {
        $this->viewBuilder()->setLayout('admin');
        $tblVitrines = $this->fetchTable('Vitrines');
        $preselectedErrataVitrineId = (int)$this->request->getQuery('vitrine_id', 0);
        $isEnvio = $this->request->is(['post', 'put', 'patch']);
        $acaoSolicitada = $isEnvio ? trim((string)$this->request->getData('acao', 'salvar')) : 'salvar';

        $isEdicao = (bool)$id;
        if ($isEdicao) {
            if ($isEnvio && $acaoSolicitada === 'reativar') {
                $vitrine = $tblVitrines->newEmptyEntity();
            } else {
                $vitrine = $tblVitrines->find()
                    ->where([
                        'Vitrines.id' => (int)$id,
                        'Vitrines.deleted IS' => null,
                    ])
                    ->first();
                if (!$vitrine) {
                    $this->Flash->error('Vitrine não localizada para edição.');
                    return $this->redirect(['action' => 'vitrines']);
                }
            }
        } else {
            $vitrine = $tblVitrines->newEmptyEntity();
        }

        if ($isEnvio) {
            $dados = $this->request->getData();
            $acao = trim((string)($dados['acao'] ?? 'salvar'));

            if ($acao === 'deletar') {
                $idDelete = (int)($dados['id'] ?? $id ?? 0);
                if ($idDelete <= 0) {
                    $this->Flash->error('Registro inválido para exclusão.');
                    return $this->redirect(['action' => 'vitrines']);
                }
                $registro = $tblVitrines->find()
                    ->where([
                        'Vitrines.id' => $idDelete,
                        'Vitrines.deleted IS' => null,
                    ])
                    ->first();
                if (!$registro) {
                    $this->Flash->error('Vitrine não encontrada para exclusão.');
                    return $this->redirect(['action' => 'vitrines']);
                }
                $registro->deleted = date('Y-m-d H:i:s');
                if ($tblVitrines->save($registro)) {
                    $this->Flash->success('Vitrine excluída com sucesso.');
                } else {
                    $this->Flash->error('Não foi possível excluir a vitrine.');
                }
                return $this->redirect(['action' => 'vitrines']);
            }

            if ($acao === 'reativar') {
                $idReativar = (int)($dados['id'] ?? $id ?? 0);
                if ($idReativar <= 0) {
                    $this->Flash->error('Registro inválido para reativação.');
                    return $this->redirect(['action' => 'vitrines']);
                }
                $registro = $tblVitrines->find()
                    ->where(['Vitrines.id' => $idReativar])
                    ->first();
                if (!$registro) {
                    $this->Flash->error('Vitrine não encontrada para reativação.');
                    return $this->redirect(['action' => 'vitrines']);
                }
                if ($registro->deleted === null) {
                    $this->Flash->info('A vitrine selecionada já está ativa.');
                    return $this->redirect(['action' => 'vitrines']);
                }
                $registro->deleted = null;
                if ($tblVitrines->save($registro)) {
                    $this->Flash->success('Vitrine reativada com sucesso.');
                } else {
                    $this->Flash->error('Não foi possível reativar a vitrine.');
                }
                return $this->redirect(['action' => 'vitrines']);
            }

            if (array_key_exists('divulgacao', $dados) && trim((string)$dados['divulgacao']) === '') {
                $dados['divulgacao'] = null;
            }
            if (array_key_exists('inicio', $dados) && trim((string)$dados['inicio']) === '') {
                $dados['inicio'] = null;
            }
            if (array_key_exists('fim', $dados) && trim((string)$dados['fim']) === '') {
                $dados['fim'] = null;
            }
            $dados = $this->handleUpload($dados, 'anexo_edital', 'editais');
            $dados = $this->handleUpload($dados, 'anexo_resultado', 'editais');
            $dados = $this->handleUpload($dados, 'anexo_resultado_recurso', 'editais');
            $dados = $this->handleUpload($dados, 'anexo_modelo_relatorio', 'editais');
            $dados = $this->handleUpload($dados, 'anexo_modelo_consentimento', 'editais');
            if (!$isEdicao) {
                $dados['deleted'] = null;
            }
            $vitrine = $tblVitrines->patchEntity($vitrine, $dados);
            if ($tblVitrines->save($vitrine)) {
                $this->Flash->success($isEdicao ? 'Vitrine atualizada com sucesso.' : 'Vitrine cadastrada com sucesso.');
                return $this->redirect(['action' => 'vitrines']);
            }
            $this->Flash->error('Não foi possível salvar a vitrine.');
        }

        $vitrines = $tblVitrines->find()
            ->orderBy(['Vitrines.id' => 'DESC'])
            ->all();

        $vitrinesAtivas = $tblVitrines->find('list', [
            'keyField' => 'id',
            'valueField' => function ($row) {
                return '#' . $row->id . ' - ' . ($row->nome ?: 'Sem nome');
            },
        ])
            ->where(['Vitrines.deleted IS' => null])
            ->orderBy(['Vitrines.id' => 'DESC'])
            ->toArray();

        $erratasRecentes = $this->fetchTable('Erratas')->find()
            ->contain(['Vitrines'])
            ->where(['Erratas.vitrine_id IS NOT' => null])
            ->orderBy(['Erratas.id' => 'DESC'])
            ->limit(50)
            ->all();

        $this->set(compact('vitrine', 'vitrines', 'isEdicao', 'vitrinesAtivas', 'erratasRecentes', 'preselectedErrataVitrineId'));
    }

    public function vitrinesLista()
    {
        $this->viewBuilder()->setLayout('admin');
        $tblVitrines = $this->fetchTable('Vitrines');

        $query = $tblVitrines->find()
            ->orderBy(['Vitrines.id' => 'DESC']);

        $vitrines = $this->paginate($query, [
            'limit' => 15,
        ]);

        $this->set(compact('vitrines'));
    }

    public function erratasVitrine()
    {
        $this->viewBuilder()->setLayout('admin');
        $tblVitrines = $this->fetchTable('Vitrines');
        $tblErratas = $this->fetchTable('Erratas');
        $preselectedErrataVitrineId = (int)$this->request->getQuery('vitrine_id', 0);

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();
            $acao = trim((string)($dados['acao'] ?? ''));

            if ($acao === 'cadastrar_erratas_vitrine') {
                $vitrineId = (int)($dados['vitrine_id'] ?? 0);
                if ($vitrineId <= 0) {
                    $this->Flash->error('Selecione uma vitrine para cadastrar as erratas.');
                    return $this->redirect(['action' => 'erratasVitrine']);
                }

                $vitrineRef = $tblVitrines->find()
                    ->where([
                        'Vitrines.id' => $vitrineId,
                        'Vitrines.deleted IS' => null,
                    ])
                    ->first();
                if (!$vitrineRef) {
                    $this->Flash->error('Vitrine inválida para cadastro de erratas.');
                    return $this->redirect(['action' => 'erratasVitrine']);
                }

                $arquivos = (array)($dados['erratas_arquivos'] ?? []);
                $arquivosValidos = [];
                foreach ($arquivos as $arquivo) {
                    if (is_object($arquivo) && $arquivo->getClientFileName() !== '') {
                        $arquivosValidos[] = $arquivo;
                    }
                }
                if (empty($arquivosValidos)) {
                    $this->Flash->error('Selecione ao menos um PDF para cadastrar.');
                    return $this->redirect(['action' => 'erratasVitrine', '?' => ['vitrine_id' => $vitrineId]]);
                }

                $salvos = 0;
                foreach ($arquivosValidos as $arquivo) {
                    $nomeOriginal = (string)$arquivo->getClientFileName();
                    $extensao = strtolower((string)pathinfo($nomeOriginal, PATHINFO_EXTENSION));
                    if ($extensao !== 'pdf') {
                        $this->Flash->error('Arquivo inválido: ' . $nomeOriginal . '. Envie apenas PDF.');
                        continue;
                    }
                    if ((int)$arquivo->getSize() > 10485760) {
                        $this->Flash->error('Arquivo acima de 10MB: ' . $nomeOriginal . '.');
                        continue;
                    }

                    $nomeArquivo = date('ymdHis') . '_' . uniqid('e', false) . '.pdf';
                    $caminho = WWW_ROOT . 'uploads' . DS . 'editais' . DS . $nomeArquivo;

                    try {
                        $arquivo->moveTo($caminho);
                    } catch (\Throwable $e) {
                        $this->Flash->error('Falha no upload de: ' . $nomeOriginal . '.');
                        continue;
                    }

                    $novaErrata = $tblErratas->newEntity([
                        'vitrine_id' => $vitrineId,
                        'editai_id' => null,
                        'arquivo' => $nomeArquivo,
                    ]);

                    if ($tblErratas->save($novaErrata)) {
                        $salvos++;
                    } else {
                        $this->Flash->error('Não foi possível gravar a errata: ' . $nomeOriginal . '.');
                    }
                }

                if ($salvos > 0) {
                    $this->Flash->success($salvos . ' errata(s) cadastrada(s) com sucesso.');
                }

                return $this->redirect(['action' => 'erratasVitrine', '?' => ['vitrine_id' => $vitrineId]]);
            }
        }

        $vitrinesAtivas = $tblVitrines->find('list', [
            'keyField' => 'id',
            'valueField' => function ($row) {
                return '#' . $row->id . ' - ' . ($row->nome ?: 'Sem nome');
            },
        ])
            ->where(['Vitrines.deleted IS' => null])
            ->orderBy(['Vitrines.id' => 'DESC'])
            ->toArray();

        $erratasRecentes = $tblErratas->find()
            ->contain(['Vitrines'])
            ->where(['Erratas.vitrine_id IS NOT' => null])
            ->orderBy(['Erratas.id' => 'DESC'])
            ->limit(100)
            ->all();

        $this->set(compact('vitrinesAtivas', 'erratasRecentes', 'preselectedErrataVitrineId'));
    }

    public function manuais($id = null)
    {
        $this->viewBuilder()->setLayout('admin');
        $tblManuais = $this->fetchTable('Manuais');
        $isEdicao = (bool)$id;

        if ($isEdicao) {
            $manual = $tblManuais->find()
                ->where([
                    'Manuais.id' => (int)$id,
                    'Manuais.deleted IS' => null,
                ])
                ->first();
            if (!$manual) {
                $this->Flash->error('Manual não localizado para edição.');
                return $this->redirect(['action' => 'manuaisLista']);
            }
        } else {
            $manual = $tblManuais->newEmptyEntity();
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();
            $dados = $this->handleUpload($dados, 'arquivo', 'editais');
            $identity = $this->request->getAttribute('identity');
            if (!empty($identity->id)) {
                $dados['usuario_id'] = (int)$identity->id;
            }
            if (!$isEdicao) {
                $dados['deleted'] = null;
            }

            $manual = $tblManuais->patchEntity($manual, $dados);
            if ($tblManuais->save($manual)) {
                $this->Flash->success($isEdicao ? 'Manual atualizado com sucesso.' : 'Manual cadastrado com sucesso.');
                return $this->redirect(['action' => 'manuaisLista']);
            }
            $this->Flash->error('Não foi possível salvar o manual.');
        }

        $this->set(compact('manual', 'isEdicao'));
    }

    public function manuaisLista($limpar = false)
    {
        $this->viewBuilder()->setLayout('admin');
        $tblManuais = $this->fetchTable('Manuais');
        $session = $this->request->getSession();
        $filtros = [];

        if ($limpar) {
            $session->delete('restrito_manuais_filtros');
        }

        $filtrosSalvos = (array)$session->read('restrito_manuais_filtros', []);
        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();
            $acao = trim((string)($dados['acao'] ?? 'filtrar'));

            if ($acao === 'deletar') {
                $idDelete = (int)($dados['id'] ?? 0);
                if ($idDelete <= 0) {
                    $this->Flash->error('Registro inválido para exclusão.');
                    return $this->redirect(['action' => 'manuaisLista']);
                }

                $registro = $tblManuais->find()
                    ->where([
                        'Manuais.id' => $idDelete,
                        'Manuais.deleted IS' => null,
                    ])
                    ->first();
                if (!$registro) {
                    $this->Flash->error('Manual não encontrado para exclusão.');
                    return $this->redirect(['action' => 'manuaisLista']);
                }
                $registro->deleted = date('Y-m-d H:i:s');
                if ($tblManuais->save($registro)) {
                    $this->Flash->success('Manual excluído com sucesso.');
                } else {
                    $this->Flash->error('Não foi possível excluir o manual.');
                }
                return $this->redirect(['action' => 'manuaisLista']);
            }

            $filtros = [
                'nome' => trim((string)($dados['nome'] ?? '')),
                'restrito' => (string)($dados['restrito'] ?? ''),
                'status' => (string)($dados['status'] ?? 'A'),
            ];
            $session->write('restrito_manuais_filtros', $filtros);
        } else {
            $filtros = $filtrosSalvos;
        }

        $where = [];
        if (!empty($filtros['nome'])) {
            $where['Manuais.nome LIKE'] = '%' . $filtros['nome'] . '%';
        }
        if (($filtros['restrito'] ?? '') !== '') {
            $where['Manuais.restrito'] = (int)$filtros['restrito'];
        }
        $status = (string)($filtros['status'] ?? 'A');
        if ($status === 'A') {
            $where['Manuais.deleted IS'] = null;
        } elseif ($status === 'I') {
            $where['Manuais.deleted IS NOT'] = null;
        }

        $query = $tblManuais->find()
            ->contain(['Usuarios'])
            ->where($where)
            ->orderBy(['Manuais.id' => 'DESC']);

        $manuais = $this->paginate($query, ['limit' => 15]);
        $this->set(compact('manuais', 'filtros'));
    }

    public function mensagens($id = null)
    {
        $this->viewBuilder()->setLayout('admin');
        $tblMensagens = $this->fetchTable('Mensagens');
        $isEdicao = (bool)$id;

        if ($isEdicao) {
            $mensagem = $tblMensagens->find()
                ->where([
                    'Mensagens.id' => (int)$id,
                    'Mensagens.deleted IS' => null,
                ])
                ->first();
            if (!$mensagem) {
                $this->Flash->error('Mensagem não localizada para edição.');
                return $this->redirect(['action' => 'mensagensLista']);
            }
        } else {
            $mensagem = $tblMensagens->newEmptyEntity();
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();
            $arquivoImagem = $dados['imagem'] ?? null;
            unset($dados['imagem']);

            if (array_key_exists('inicio', $dados) && trim((string)$dados['inicio']) === '') {
                $dados['inicio'] = null;
            }
            if (array_key_exists('fim', $dados) && trim((string)$dados['fim']) === '') {
                $dados['fim'] = null;
            }

            $uploadImagem = $this->uploadImagemMensagem($arquivoImagem);
            if ($uploadImagem['erro']) {
                $this->Flash->error($uploadImagem['mensagem']);
            } elseif (!empty($uploadImagem['arquivo'])) {
                if (!empty($mensagem->imagem)) {
                    $this->removerImagemMensagemArquivo((string)$mensagem->imagem);
                }
                $dados['imagem'] = $uploadImagem['arquivo'];
            } else {
                $removerImagem = (string)($dados['remover_imagem'] ?? '0') === '1';
                if ($removerImagem) {
                    if (!empty($mensagem->imagem)) {
                        $this->removerImagemMensagemArquivo((string)$mensagem->imagem);
                    }
                    $dados['imagem'] = null;
                } else {
                    unset($dados['imagem']);
                }
            }

            unset($dados['remover_imagem']);

            if (!$isEdicao) {
                $dados['deleted'] = null;
            }

            $mensagem = $tblMensagens->patchEntity($mensagem, $dados);
            if (!$mensagem->getErrors() && $tblMensagens->save($mensagem)) {
                $this->Flash->success($isEdicao ? 'Mensagem atualizada com sucesso.' : 'Mensagem cadastrada com sucesso.');
                return $this->redirect(['action' => 'mensagensLista']);
            }

            if (!$uploadImagem['erro']) {
                $this->Flash->error('Não foi possível salvar a mensagem.');
            }
        }

        $tipos = [
            'E' => 'Externa',
            'I' => 'Interna',
        ];

        $this->set(compact('mensagem', 'isEdicao', 'tipos'));
    }

    public function mensagensLista($limpar = false)
    {
        $this->viewBuilder()->setLayout('admin');
        $tblMensagens = $this->fetchTable('Mensagens');
        $session = $this->request->getSession();
        $filtros = [];

        if ($limpar) {
            $session->delete('restrito_mensagens_filtros');
        }

        $filtrosSalvos = (array)$session->read('restrito_mensagens_filtros', []);
        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();
            $acao = trim((string)($dados['acao'] ?? 'filtrar'));

            if ($acao === 'deletar') {
                $idDelete = (int)($dados['id'] ?? 0);
                if ($idDelete <= 0) {
                    $this->Flash->error('Registro inválido para exclusão.');
                    return $this->redirect(['action' => 'mensagensLista']);
                }

                $registro = $tblMensagens->find()
                    ->where([
                        'Mensagens.id' => $idDelete,
                        'Mensagens.deleted IS' => null,
                    ])
                    ->first();
                if (!$registro) {
                    $this->Flash->error('Mensagem não encontrada para exclusão.');
                    return $this->redirect(['action' => 'mensagensLista']);
                }

                $registro->deleted = date('Y-m-d H:i:s');
                if ($tblMensagens->save($registro)) {
                    $this->Flash->success('Mensagem excluída com sucesso.');
                } else {
                    $this->Flash->error('Não foi possível excluir a mensagem.');
                }
                return $this->redirect(['action' => 'mensagensLista']);
            }

            $filtros = [
                'titulo' => trim((string)($dados['titulo'] ?? '')),
                'tipo' => (string)($dados['tipo'] ?? ''),
                'status' => (string)($dados['status'] ?? 'A'),
            ];
            $session->write('restrito_mensagens_filtros', $filtros);
        } else {
            $filtros = $filtrosSalvos;
        }

        $where = [];
        if (!empty($filtros['titulo'])) {
            $where['Mensagens.titulo LIKE'] = '%' . $filtros['titulo'] . '%';
        }
        if (($filtros['tipo'] ?? '') !== '') {
            $where['Mensagens.tipo'] = $filtros['tipo'];
        }

        $status = (string)($filtros['status'] ?? 'A');
        if ($status === 'A') {
            $where['Mensagens.deleted IS'] = null;
        } elseif ($status === 'I') {
            $where['Mensagens.deleted IS NOT'] = null;
        }

        $query = $tblMensagens->find()
            ->where($where)
            ->orderBy(['Mensagens.id' => 'DESC']);

        $mensagens = $this->paginate($query, ['limit' => 15]);
        $tipos = [
            'E' => 'Externa',
            'I' => 'Interna',
        ];

        $this->set(compact('mensagens', 'filtros', 'tipos'));
    }

    public function calendarios($id = null)
    {
        $this->viewBuilder()->setLayout('admin');
        $tblCalendarios = $this->fetchTable('Calendarios');
        $isEdicao = (bool)$id;

        if ($isEdicao) {
            $calendario = $tblCalendarios->find()
                ->where([
                    'Calendarios.id' => (int)$id,
                    'Calendarios.deleted IS' => null,
                ])
                ->first();
            if (!$calendario) {
                $this->Flash->error('Registro não localizado para edição.');
                return $this->redirect(['action' => 'calendariosLista']);
            }
        } else {
            $calendario = $tblCalendarios->newEmptyEntity();
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();
            if ($isEdicao) {
                if (array_key_exists('dia', $dados) && trim((string)$dados['dia']) === '') {
                    $dados['dia'] = null;
                }

                $calendario = $tblCalendarios->patchEntity($calendario, $dados);
                if ($tblCalendarios->save($calendario)) {
                    $this->Flash->success('Registro atualizado com sucesso.');
                    return $this->redirect(['action' => 'calendariosLista']);
                }
            } else {
                $linhas = array_values((array)($dados['calendarios'] ?? []));
                $linhasPreparadas = [];
                foreach ($linhas as $linha) {
                    $dia = trim((string)($linha['dia'] ?? ''));
                    $tipo = trim((string)($linha['tipo'] ?? ''));
                    $descricao = trim((string)($linha['descricao'] ?? ''));
                    if ($dia === '' && $tipo === '' && $descricao === '') {
                        continue;
                    }
                    $linhasPreparadas[] = [
                        'dia' => $dia !== '' ? $dia : null,
                        'tipo' => $tipo !== '' ? $tipo : null,
                        'descricao' => $descricao !== '' ? $descricao : null,
                        'deleted' => null,
                    ];
                }

                if (empty($linhasPreparadas)) {
                    $this->Flash->error('Informe ao menos uma linha para cadastro.');
                } else {
                    $entidades = $tblCalendarios->newEntities($linhasPreparadas);
                    $temErro = false;
                    foreach ($entidades as $entidade) {
                        if ($entidade->getErrors()) {
                            $temErro = true;
                            break;
                        }
                    }

                    if (!$temErro && $tblCalendarios->saveMany($entidades)) {
                        $this->Flash->success(count($entidades) . ' registro(s) cadastrados com sucesso.');
                        return $this->redirect(['action' => 'calendariosLista']);
                    }

                    $this->Flash->error('Não foi possível salvar os registros informados.');
                    $calendario = $tblCalendarios->newEntity($linhasPreparadas[0] ?? []);
                }
            }

            if ($isEdicao) {
                $this->Flash->error('Não foi possível salvar o registro.');
            }
        }

        $tipos = $this->getCalendarioTipos();
        $this->set(compact('calendario', 'isEdicao', 'tipos'));
    }

    public function calendariosLista($limpar = false)
    {
        $this->viewBuilder()->setLayout('admin');
        $tblCalendarios = $this->fetchTable('Calendarios');
        $session = $this->request->getSession();
        $filtros = [];

        if ($limpar) {
            $session->delete('restrito_calendarios_filtros');
        }

        $filtrosSalvos = (array)$session->read('restrito_calendarios_filtros', []);
        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();
            $acao = trim((string)($dados['acao'] ?? 'filtrar'));

            if ($acao === 'deletar') {
                $idDelete = (int)($dados['id'] ?? 0);
                if ($idDelete <= 0) {
                    $this->Flash->error('Registro inválido para exclusão.');
                    return $this->redirect(['action' => 'calendariosLista']);
                }

                $registro = $tblCalendarios->find()
                    ->where([
                        'Calendarios.id' => $idDelete,
                        'Calendarios.deleted IS' => null,
                    ])
                    ->first();
                if (!$registro) {
                    $this->Flash->error('Registro não encontrado para exclusão.');
                    return $this->redirect(['action' => 'calendariosLista']);
                }

                $registro->deleted = date('Y-m-d H:i:s');
                if ($tblCalendarios->save($registro)) {
                    $this->Flash->success('Registro excluído com sucesso.');
                } else {
                    $this->Flash->error('Não foi possível excluir o registro.');
                }
                return $this->redirect(['action' => 'calendariosLista']);
            }

            $filtros = [
                'dia' => trim((string)($dados['dia'] ?? '')),
                'tipo' => trim((string)($dados['tipo'] ?? '')),
                'descricao' => trim((string)($dados['descricao'] ?? '')),
            ];
            $session->write('restrito_calendarios_filtros', $filtros);
        } else {
            $filtros = $filtrosSalvos;
        }

        $where = [];
        if (!empty($filtros['dia'])) {
            $where['Calendarios.dia'] = $filtros['dia'];
        }
        if (!empty($filtros['tipo'])) {
            $where['Calendarios.tipo'] = $filtros['tipo'];
        }
        if (!empty($filtros['descricao'])) {
            $where['Calendarios.descricao LIKE'] = '%' . $filtros['descricao'] . '%';
        }
        $where['Calendarios.deleted IS'] = null;

        $query = $tblCalendarios->find()
            ->where($where)
            ->orderBy([
                'Calendarios.dia' => 'DESC',
                'Calendarios.id' => 'DESC',
            ]);

        $calendarios = $this->paginate($query, ['limit' => 15]);
        $tipos = $this->getCalendarioTipos();
        $this->set(compact('calendarios', 'filtros', 'tipos'));
    }

    public function replicarAnexos()
    {
        $this->request->allowMethod(['post']);

        $tblBolsistas = TableRegistry::getTableLocator()->get('ProjetoBolsistas');
        $tblAnexos = TableRegistry::getTableLocator()->get('Anexos');

        $falta = $tblBolsistas->find()
            ->select(['id', 'origem', 'bolsista_anterior'])
            ->where([
                'origem IN' => ['S', 'A'],
                'vigente' => 1,
                'id NOT IN' => $tblAnexos->find()
                    ->select('projeto_bolsista_id')
                    ->where(['deleted IS' => null, 'anexos_tipo_id' => 20]),
            ])
            ->all();

        if ($falta->isEmpty()) {
            $this->Flash->info('Não há registros para processar.');
            return $this->redirect($this->referer());
        }

        try {
            $connection = $tblBolsistas->getConnection();
            $connection->transactional(function () use ($falta, $tblAnexos) {
                foreach ($falta as $registro) {
                    $anexoAnterior = $tblAnexos->find()
                        ->where([
                            'projeto_bolsista_id' => $registro->bolsista_anterior,
                            'anexos_tipo_id' => 20,
                            'deleted IS' => null,
                        ])
                        ->first();

                    if ($anexoAnterior) {
                        $novoAnexo = $tblAnexos->newEntity($anexoAnterior->toArray());
                        $novoAnexo->projeto_bolsista_id = $registro->id;
                        $novoAnexo->projeto_id = $anexoAnterior->projeto_id;
                        $novoAnexo->anexos_tipo_id = $anexoAnterior->anexos_tipo_id;
                        $novoAnexo->anexo = $anexoAnterior->anexo;
                        $novoAnexo->created = $anexoAnterior->created;
                        $novoAnexo->modified = $anexoAnterior->modified;
                        $novoAnexo->deleted = $anexoAnterior->deleted;
                        $novoAnexo->usuario_id = $anexoAnterior->usuario_id;
                        $novoAnexo->raic_id = $anexoAnterior->raic_id;

                        if (!$tblAnexos->save($novoAnexo)) {
                            throw new \Exception("Erro ao replicar anexo para o projeto bolsista ID {$registro->id}");
                        }
                    }
                }
            });

            $this->Flash->success('Anexos replicados com sucesso!');
        } catch (\Exception $e) {
            $this->Flash->error('Erro na replicação dos anexos: ' . $e->getMessage());
        }

        return $this->redirect($this->referer());
    }

    public function replicarAnexosRenova()
    {
        $this->request->allowMethod(['post']);

        $tblBolsistas = TableRegistry::getTableLocator()->get('ProjetoBolsistas');
        $tblAnexos = TableRegistry::getTableLocator()->get('Anexos');

        $falta = $tblBolsistas->find()
            ->select(['id', 'referencia_inscricao_anterior'])
            ->where([
                'situacao' => 'F',
                'editai_id IN' => [29, 30],
                'deleted IS' => null,
                'id NOT IN' => $tblAnexos->find()
                    ->select('projeto_bolsista_id')
                    ->where(['deleted IS' => null, 'anexos_tipo_id' => 20]),
            ])
            ->all();

        if ($falta->isEmpty()) {
            $this->Flash->info('Não há registros para processar.');
            return $this->redirect($this->referer());
        }

        try {
            $connection = $tblBolsistas->getConnection();
            $connection->transactional(function () use ($falta, $tblAnexos) {
                foreach ($falta as $registro) {
                    $anexoAnterior = $tblAnexos->find()
                        ->where([
                            'projeto_bolsista_id' => $registro->referencia_inscricao_anterior,
                            'anexos_tipo_id' => 20,
                            'deleted IS' => null,
                        ])
                        ->first();

                    if ($anexoAnterior) {
                        $novoAnexo = $tblAnexos->newEntity($anexoAnterior->toArray());
                        $novoAnexo->projeto_bolsista_id = $registro->id;
                        $novoAnexo->projeto_id = $anexoAnterior->projeto_id;
                        $novoAnexo->anexos_tipo_id = $anexoAnterior->anexos_tipo_id;
                        $novoAnexo->anexo = $anexoAnterior->anexo;
                        $novoAnexo->created = $anexoAnterior->created;
                        $novoAnexo->modified = $anexoAnterior->modified;
                        $novoAnexo->deleted = $anexoAnterior->deleted;
                        $novoAnexo->usuario_id = $anexoAnterior->usuario_id;
                        $novoAnexo->raic_id = $anexoAnterior->raic_id;

                        if (!$tblAnexos->save($novoAnexo)) {
                            throw new \Exception("Erro ao replicar anexo para o projeto bolsista ID {$registro->id}");
                        }
                    }
                }
            });

            $this->Flash->success('Anexos replicados com sucesso!');
        } catch (\Exception $e) {
            $this->Flash->error('Erro na replicação dos anexos: ' . $e->getMessage());
        }

        return $this->redirect($this->referer());
    }

    public function migrarPdjParaProjetoBolsistas()
    {
        $this->request->allowMethod(['post']);

        $tblBolsistas = TableRegistry::getTableLocator()->get('ProjetoBolsistas');
        $connection = $tblBolsistas->getConnection();

        $sql = "
            INSERT INTO projeto_bolsistas (
                editai_id, projeto_id, bolsista, data_primeira, data_inicio, data_fim,
                orientador, coorientador, estagio_previo, provoc, pibi_antes, matricula,
                historico, cr_acumulado, sp_titulo, sp_resumo, palavras_chaves, sp_objetivos,
                sp_projeto, anexo, codigo_declaracao, relatorio, egresso, relatorio_entregue,
                situacao, bolsista_anterior, data_substituicao, substituicao_confirmador,
                data_sub_confirmacao, nota_final, pontos_orientador, justificativa,
                segunda_cota, motivo_cancelamento_id, justificativa_cancelamento,
                data_cancelamento, data_cancela_confirmacao, cancelamento_confirmador,
                premiado, tipo_bolsa, origem, programa, modified, vigente, resultado,
                cota, atestado, created, deleted, projetos_dado_id, relatorio_final,
                revista_id, autorizacao, autorizacao_anexo, data_resposta_bolsista,
                data_inclusao_bolsista, resposta_bolsista, justificativa_recusa_bolsista,
                data_resposta_coorientador, resposta_coorientador,
                justificativa_recusa_coorientador, revista_orientador, revista_bolsista,
                anexo_rg, filhos_menor, referencia_raic, anexo_rg_responsavel,
                data_fim_cancelamento, apresentar_raic, referencia_inscricao_anterior,
                subprojeto_renovacao, justificativa_alteracao, ordem, prorrogacao,
                resumo_relatorio, fase_id, programa_id, primeiro_periodo, troca_projeto,
                heranca, pontos_bolsista, area_pdj, matriz, pdj_inscricoe_id
            )
            SELECT
                pdj.editai_id,
                pdj.projeto_id,
                pdj.bolsista,
                NULL,
                pdj.data_inicio,
                pdj.data_fim,
                pdj.orientador,
                pdj.coorientador,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                pdj.situacao,
                pdj.bolsista_anterior,
                NULL,
                NULL,
                NULL,
                NULL,
                pdj.pontos_orientador,
                NULL,
                NULL,
                pdj.motivo_cancelamento_id,
                pdj.justificativa_cancelamento,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                pdj.origem,
                NULL,
                pdj.modified,
                COALESCE(pdj.vigente, 0),
                pdj.resultado,
                pdj.cota,
                NULL,
                pdj.created,
                pdj.deleted,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                NULL,
                pdj.referencia_inscricao_anterior,
                NULL,
                NULL,
                pdj.ordem,
                pdj.prorrogacao,
                NULL,
                pdj.fase_id,
                pdj.programa_id,
                NULL,
                NULL,
                NULL,
                pdj.pontos_bolsista,
                pdj.area_id,
                NULL,
                pdj.id
            FROM pdj_inscricoes pdj
            WHERE NOT EXISTS (
                SELECT 1
                FROM projeto_bolsistas pb
                WHERE pb.editai_id = pdj.editai_id
                  AND pb.projeto_id = pdj.projeto_id
                  AND pb.bolsista = pdj.bolsista
                  AND pb.orientador = pdj.orientador
                  AND (pb.coorientador <=> pdj.coorientador)
                  AND (pb.fase_id <=> pdj.fase_id)
                  AND (pb.origem <=> pdj.origem)
                  AND (pb.programa_id <=> pdj.programa_id)
            )
        ";

        try {
            $result = $connection->execute($sql);
            $this->Flash->success('Migração concluída. Linhas inseridas: ' . $result->rowCount());
        } catch (\Throwable $e) {
            $this->Flash->error('Erro na migração PDJ → ProjetoBolsistas: ' . $e->getMessage());
        }

        return $this->redirect($this->referer());
    }

    public function reestabelecerAnexosPdjMigrados()
    {
        $this->request->allowMethod(['post']);

        $tblAnexos = TableRegistry::getTableLocator()->get('Anexos');
        $tblProjetoBolsistas = TableRegistry::getTableLocator()->get('ProjetoBolsistas');
        $connection = $tblAnexos->getConnection();
        $schemaProjetoBolsistas = $tblProjetoBolsistas->getSchema();
        $schemaAnexos = $tblAnexos->getSchema();

        if (
            !$schemaProjetoBolsistas->hasColumn('pdj_inscricoe_id') ||
            !$schemaAnexos->hasColumn('pdj_inscricoe_id')
        ) {
            $this->Flash->error('As colunas pdj_inscricoe_id não estão disponíveis em anexos/projeto_bolsistas.');
            return $this->redirect($this->referer(['action' => 'index']));
        }

        $whereAlvo = "a.projeto_bolsista_id < 645 AND YEAR(a.created) > 2020";
        $sqlMapaUnico = "
            SELECT pb.pdj_inscricoe_id AS pdj_legacy_id, MIN(pb.id) AS novo_projeto_bolsista_id
            FROM projeto_bolsistas pb
            WHERE pb.pdj_inscricoe_id IS NOT NULL
            GROUP BY pb.pdj_inscricoe_id
            HAVING COUNT(*) = 1
        ";
        $sqlMapaDuplicado = "
            SELECT pb.pdj_inscricoe_id AS pdj_legacy_id
            FROM projeto_bolsistas pb
            WHERE pb.pdj_inscricoe_id IS NOT NULL
            GROUP BY pb.pdj_inscricoe_id
            HAVING COUNT(*) > 1
        ";

        try {
            $resultado = $connection->transactional(function ($conn) use ($whereAlvo, $sqlMapaUnico, $sqlMapaDuplicado) {
                $totalAlvo = (int)$conn->execute("
                    SELECT COUNT(*) AS total
                    FROM anexos a
                    WHERE {$whereAlvo}
                ")->fetch('assoc')['total'];

                $mapeaveis = (int)$conn->execute("
                    SELECT COUNT(*) AS total
                    FROM anexos a
                    INNER JOIN ({$sqlMapaUnico}) mapa ON mapa.pdj_legacy_id = a.projeto_bolsista_id
                    WHERE {$whereAlvo}
                ")->fetch('assoc')['total'];

                $ambiguos = (int)$conn->execute("
                    SELECT COUNT(*) AS total
                    FROM anexos a
                    INNER JOIN ({$sqlMapaDuplicado}) dup ON dup.pdj_legacy_id = a.projeto_bolsista_id
                    WHERE {$whereAlvo}
                ")->fetch('assoc')['total'];

                $conn->execute("
                    UPDATE anexos a
                    SET a.pdj_inscricoe_id = a.projeto_bolsista_id
                    WHERE {$whereAlvo}
                ");
                $pdjReplica = (int)$conn->execute('SELECT ROW_COUNT() AS total')->fetch('assoc')['total'];

                $conn->execute("
                    UPDATE anexos a
                    INNER JOIN ({$sqlMapaUnico}) mapa ON mapa.pdj_legacy_id = a.projeto_bolsista_id
                    SET a.projeto_bolsista_id = mapa.novo_projeto_bolsista_id
                    WHERE {$whereAlvo}
                ");
                $atualizados = (int)$conn->execute('SELECT ROW_COUNT() AS total')->fetch('assoc')['total'];

                return compact('totalAlvo', 'mapeaveis', 'ambiguos', 'pdjReplica', 'atualizados');
            });

            $semCorrespondencia = max(
                0,
                ((int)$resultado['totalAlvo']) - ((int)$resultado['mapeaveis']) - ((int)$resultado['ambiguos'])
            );
            $this->Flash->success(
                'Reestabelecimento PDJ concluído. ' .
                'Alvos=' . (int)$resultado['totalAlvo'] .
                ', Mapeáveis=' . (int)$resultado['mapeaveis'] .
                ', Ambíguos=' . (int)$resultado['ambiguos'] .
                ', Sem correspondência=' . $semCorrespondencia .
                ', PDJ replicado em anexos.pdj_inscricoe_id=' . (int)$resultado['pdjReplica'] .
                ', Atualizados=' . (int)$resultado['atualizados'] . '.'
            );
        } catch (\Throwable $e) {
            $this->Flash->error('Erro no reestabelecimento de anexos PDJ: ' . $e->getMessage());
        }

        return $this->redirect($this->referer(['action' => 'index']));
    }

    public function migrarHistoricosPdjParaSituacaoHistoricos()
    {
        $this->request->allowMethod(['post']);

        $tblSituacaoHistoricos = TableRegistry::getTableLocator()->get('SituacaoHistoricos');
        $tblFases = TableRegistry::getTableLocator()->get('Fases');
        $connection = $tblSituacaoHistoricos->getConnection();

        $schemaFases = $tblFases->getSchema();
        if (!$schemaFases->hasColumn('letra')) {
            $this->Flash->error('Tabela fases sem a coluna "letra" necessária para mapear situação.');
            return $this->redirect($this->referer(['action' => 'index']));
        }

        $sqlInsert = "
            INSERT INTO situacao_historicos (
                projeto_bolsista_id,
                usuario_id,
                situacao_original,
                situacao_atual,
                created,
                modified,
                justificativa,
                fase_original,
                fase_atual,
                editai_id
            )
            SELECT
                pb.id AS projeto_bolsista_id,
                ph.usuario_id,
                ph.situacao_original,
                ph.situacao_atual,
                ph.created,
                ph.modified,
                ph.justificativa,
                fo.id AS fase_original,
                fa.id AS fase_atual,
                pb.editai_id
            FROM pdj_historicos ph
            INNER JOIN projeto_bolsistas pb
                ON pb.pdj_inscricoe_id = ph.pdj_inscricoe_id
            LEFT JOIN fases fo ON fo.letra = ph.situacao_original
            LEFT JOIN fases fa ON fa.letra = ph.situacao_atual
            WHERE NOT EXISTS (
                SELECT 1
                FROM situacao_historicos sh
                WHERE sh.projeto_bolsista_id = pb.id
                  AND (sh.usuario_id <=> ph.usuario_id)
                  AND (sh.situacao_original <=> ph.situacao_original)
                  AND (sh.situacao_atual <=> ph.situacao_atual)
                  AND (sh.created <=> ph.created)
                  AND (sh.justificativa <=> ph.justificativa)
            )
        ";

        $sqlFasesNulas = "
            SELECT COUNT(*) AS total
            FROM situacao_historicos sh
            WHERE sh.fase_original IS NULL OR sh.fase_atual IS NULL
        ";

        try {
            $resultado = $connection->transactional(function ($conn) use ($sqlInsert, $sqlFasesNulas) {
                $insertResult = $conn->execute($sqlInsert);
                $inseridos = (int)$insertResult->rowCount();
                $fasesNulas = (int)$conn->execute($sqlFasesNulas)->fetch('assoc')['total'];

                return compact('inseridos', 'fasesNulas');
            });

            $this->Flash->success(
                'Migração de históricos PDJ concluída. Inseridos=' . (int)$resultado['inseridos'] .
                ', registros com fase_original/fase_atual nulas=' . (int)$resultado['fasesNulas'] . '.'
            );
        } catch (\Throwable $e) {
            $this->Flash->error('Erro na migração de históricos PDJ: ' . $e->getMessage());
        }

        return $this->redirect($this->referer(['action' => 'index']));
    }

    public function atualizarDeletedTimestamp()
    {
        $this->request->allowMethod(['post']);

        $tblBolsistas = TableRegistry::getTableLocator()->get('ProjetoBolsistas');
        $connection = $tblBolsistas->getConnection();

        $sql = "
            UPDATE projeto_bolsistas
            SET deleted = CASE
                WHEN created IS NULL AND modified IS NULL AND data_cancelamento IS NULL THEN NULL
                ELSE GREATEST(
                    COALESCE(created, '1970-01-01 00:00:00'),
                    COALESCE(modified, '1970-01-01 00:00:00'),
                    COALESCE(data_cancelamento, '1970-01-01 00:00:00')
                )
            END
            WHERE deleted IS NULL
              AND data_cancelamento IS NOT NULL
        ";

        try {
            $result = $connection->execute($sql);
            $this->Flash->success('Atualização concluída. Linhas afetadas: ' . $result->rowCount());
        } catch (\Throwable $e) {
            $this->Flash->error('Erro ao atualizar deleted (timestamp): ' . $e->getMessage());
        }

        return $this->redirect($this->referer());
    }

    public function erros()
    {
        $this->TiExceptions = \Cake\ORM\TableRegistry::getTableLocator()->get('TiExceptions');

        $status = $this->request->getQuery('status');
        $repeticao = $this->request->getQuery('repeticao');
        $mes = $this->request->getQuery('mes');
        $ano = $this->request->getQuery('ano');
        $usuarioId = $this->request->getQuery('usuario_id');
        $acao = $this->request->getQuery('acao');

        $query = $this->TiExceptions->find()
            ->contain(['TiTipos'])
            ->orderBy(['TiExceptions.id' => 'DESC']);

        if (!empty($status)) {
            if ($status === 'N') {
                $query->where([
                    'OR' => [
                        'status' => 'N',
                        'status IS' => null,
                    ],
                ]);
            } else {
                $query->where(['status' => $status]);
            }
        }

        if ($repeticao !== null && $repeticao !== '') {
            $query->where(['repeticao' => (int)$repeticao]);
        }

        if (!empty($usuarioId)) {
            $query->where(['usuario_id' => (int)$usuarioId]);
        }

        if (!empty($mes) && !empty($ano)) {
            $mesInt = (int)$mes;
            $anoInt = (int)$ano;
            if ($mesInt >= 1 && $mesInt <= 12 && $anoInt > 1900) {
                $start = new \DateTimeImmutable(sprintf('%04d-%02d-01 00:00:00', $anoInt, $mesInt));
                $end = $start->modify('last day of this month')->setTime(23, 59, 59);
                $query->where([
                    'created >=' => $start->format('Y-m-d H:i:s'),
                    'created <=' => $end->format('Y-m-d H:i:s'),
                ]);
            }
        }

        if ($acao === 'excel') {
            $rows = $query->all();
            $header = [
                'id',
                'created',
                'status',
                'classificacao_nome',
                'classificacao_tipo',
                'usuario_id',
                'usuario_nome',
                'usuario_email',
                'usuario_email_alternativo',
                'usuario_email_contato',
                'url',
                'host',
                'mensagem',
                'repeticoes',
                'repeticao',
                'repeticao_de_id',
                'tempo_resposta_min',
                'ultima_ocorrencia',
                'resposta',
                'respondido_por',
                'respondido_em',
            ];

            $exportRows = [];
            foreach ($rows as $erro) {
                $tipo = $erro->ti_tipo;
                $statusLabel = ($erro->status ?? 'N') === 'R' ? 'Respondido' : 'Nova';
                $email = !empty($erro->usuario_email) ? $erro->usuario_email : 'não informado';
                $emailAlt = !empty($erro->usuario_email_alternativo) ? $erro->usuario_email_alternativo : 'não informado';
                $emailContato = !empty($erro->usuario_email_contato) ? $erro->usuario_email_contato : 'não informado';
                $tipoLabel = '';
                if (!empty($tipo->tipo)) {
                    $tipoMap = [
                        'U' => 'Usuario',
                        'S' => 'Sistema',
                        'I' => 'Infra',
                        'O' => 'Outros',
                    ];
                    $tipoLabel = $tipoMap[$tipo->tipo] ?? $tipo->tipo;
                }

                $tempoResposta = '';
                if ($erro->created && $erro->respondido_em) {
                    $diffSec = $erro->respondido_em->getTimestamp() - $erro->created->getTimestamp();
                    if ($diffSec < 0) {
                        $tempoResposta = 'Tratado anteriormente';
                    } elseif ($diffSec < 60) {
                        $tempoResposta = 'Imediato';
                    } else {
                        $tempoResposta = (string)floor($diffSec / 60);
                    }
                }

                $exportRows[] = [
                    $erro->id,
                    $erro->created ? $erro->created->format('d/m/Y H:i:s') : '',
                    $statusLabel,
                    $tipo->nome ?? '',
                    $tipoLabel,
                    $erro->usuario_id ?? '',
                    $erro->usuario_nome ?? '',
                    $email,
                    $emailAlt,
                    $emailContato,
                    $erro->url ?? '',
                    $erro->host ?? '',
                    $erro->mensagem ?? '',
                    $erro->repeticoes ?? '',
                    !empty($erro->repeticao) ? 'Sim' : 'Nao',
                    $erro->repeticao_de_id ?? '',
                    $tempoResposta,
                    $erro->ultima_ocorrencia ? $erro->ultima_ocorrencia->format('d/m/Y H:i:s') : '',
                    $erro->resposta ?? '',
                    $erro->respondido_por ?? '',
                    $erro->respondido_em ? $erro->respondido_em->format('d/m/Y H:i:s') : '',
                ];
            }

            return $this->downloadCsvResponse(
                'ti_exceptions_' . date('Ymd_His') . '.csv',
                $header,
                $exportRows
            );
        }

        $erros = $this->paginate($query, ['limit' => 15]);
        $this->set(compact('erros', 'status', 'repeticao', 'mes', 'ano', 'usuarioId'));
    }

    public function respondererro($id)
    {
        $this->TiExceptions = \Cake\ORM\TableRegistry::getTableLocator()->get('TiExceptions');
        $erro = $this->TiExceptions->get($id);

        if (!empty($erro->repeticao)) {
            $this->Flash->error('não e permitido responder uma repeticao. Responda a ocorrencia principal.');
            return $this->redirect(['action' => 'erros']);
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $dados = $this->request->getData();
            $tipo = trim((string)($dados['tipo'] ?? ''));
            $classificacaoId = trim((string)($dados['classificacao_id'] ?? ''));
            $respostaTexto = trim((string)($dados['resposta'] ?? ''));

            if ($tipo === '' || $classificacaoId === '' || $respostaTexto === '') {
                $this->Flash->error('Tipo, classificacao e mensagem sao obrigatorios.');
                return $this->redirect(['action' => 'respondererro', $id]);
            }

            $tiTiposTable = \Cake\ORM\TableRegistry::getTableLocator()->get('TiTipos');
            $classificacaoNome = $tiTiposTable->find()
                ->select(['nome'])
                ->where(['id' => $classificacaoId])
                ->first()
                ?->nome;
            $classificacaoNome = $classificacaoNome ?: 'não informado';

            $nomeUsuario = trim((string)($erro->usuario_nome ?? ''));
            if ($nomeUsuario === '') {
                $nomeUsuario = 'Usuario';
            }

            $dataCriacao = $erro->created ? $erro->created->format('d/m/Y H:i:s') : 'não informada';
            $mensagemCompleta = implode("\n", [
                "Prezado(a) sr(a) {$nomeUsuario}",
                "Recebemos a notificacao de um erro na acao {$erro->url} em {$dataCriacao}.",
                "Classificacao do erro: {$classificacaoNome}",
                $respostaTexto,
                "Att,",
                "Antonietta Versiani",
                "Desenvolvedora Plataforma fomento",
                "maria.britto@fiocruz.br.",
            ]);

            $emailCopiaRaw = trim((string)($dados['email_copia'] ?? ''));
            $destinatarios = array_values(array_filter([
                $erro->usuario_email ?? null,
                $erro->usuario_email_alternativo ?? null,
                $erro->usuario_email_contato ?? null,
            ], static function ($email): bool {
                return is_string($email) && trim($email) !== '';
            }));

            $emailsCopia = [];
            if ($emailCopiaRaw !== '') {
                $emailsCopia = preg_split('/[\s,;]+/', $emailCopiaRaw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            }

            $ccPadrao = array_merge([
                'mariaversiani@yahoo.com.br',
                'maria.britto@fiocruz.br',
            ], $emailsCopia);

            $host = $this->request->host();
            $enviarEmail = ($host !== 'fomento2026.local') || \Cake\Core\Configure::read('debug') === false;
            $prefixo = '';
            if ($host === 'fomento2026.local' || $host === 'homolog.pibic.fiocruz.br') {
                $prefixo = 'TESTE EM AMBIENTE DE DESENVOLVIMENTO/HOMOLOGAÇÃO (' . $host . '). FAVOR DESCONSIDERAR ESTE EMAIL.<br><br>';
            }

            $emailEnviado = false;
            if ($enviarEmail && (!empty($destinatarios) || !empty($ccPadrao))) {
                try {
                    $mailer = new \Cake\Mailer\Mailer('default');
                    $mailer->setEmailFormat('html')
                        ->setSubject('Resposta ao erro #' . $erro->id);

                    $toList = array_values(array_unique($destinatarios));
                    $ccList = array_values(array_unique($ccPadrao));

                    if (empty($toList)) {
                        $toList = $ccList;
                        $ccList = [];
                    } else {
                        $ccList = array_values(array_diff($ccList, $toList));
                    }

                    $mailer->setTo($toList);
                    if (!empty($ccList)) {
                        $mailer->setCc($ccList);
                    }

                    $respostaHtml = nl2br(htmlspecialchars($mensagemCompleta, ENT_QUOTES, 'UTF-8'));
                    $mailer->deliver($prefixo . '<p>' . $respostaHtml . '</p>');
                    $emailEnviado = true;
                } catch (\Throwable $mailError) {
                    $this->Flash->error('não foi possivel enviar o email. A resposta não foi registrada.');
                    $emailEnviado = false;
                }
            } else {
                $this->Flash->error('Email não enviado. A resposta não foi registrada.');
            }

            if ($emailEnviado) {
                $erro = $this->TiExceptions->patchEntity($erro, [
                    'classificacao_id' => $classificacaoId !== '' ? $classificacaoId : null,
                    'resposta' => $mensagemCompleta !== '' ? $mensagemCompleta : null,
                    'status' => 'R',
                    'respondido_por' => $this->Authentication->getIdentity()->id ?? null,
                    'respondido_em' => \Cake\I18n\FrozenTime::now(),
                ]);

                if ($this->TiExceptions->save($erro)) {
                    $this->TiExceptions->updateAll([
                        'classificacao_id' => $erro->classificacao_id,
                        'resposta' => $erro->resposta,
                        'status' => 'R',
                        'respondido_por' => $erro->respondido_por,
                        'respondido_em' => $erro->respondido_em,
                    ], [
                        'repeticao_de_id' => $erro->id,
                    ]);

                    $this->Flash->success('Resposta registrada com sucesso.');
                    return $this->redirect(['action' => 'erros']);
                }

                $this->Flash->error('não foi possivel salvar a resposta.');
            }

            return $this->redirect(['action' => 'respondererro', $id]);
        }

        $tiTiposTable = \Cake\ORM\TableRegistry::getTableLocator()->get('TiTipos');
        $tiposRows = $tiTiposTable->find()
            ->select(['id', 'tipo', 'nome'])
            ->where(['deleted' => 0])
            ->orderBy(['nome' => 'ASC'])
            ->all();

        $tiposMap = [
            'U' => 'Usuario',
            'S' => 'Sistema',
            'I' => 'Infra',
            'O' => 'Outros',
        ];

        $tiposDisponiveis = [];
        $classificacoesOptions = [];
        $classificacoesAttrs = [];
        foreach ($tiposRows as $row) {
            $tiposDisponiveis[$row->tipo] = $tiposMap[$row->tipo] ?? $row->tipo;
            $classificacoesOptions[] = [
                'value' => $row->id,
                'text' => $row->nome,
                'data-tipo' => $row->tipo,
            ];
        }

        $tipoSelecionado = $this->request->getData('tipo');
        if ($tipoSelecionado === null && !empty($erro->classificacao_id)) {
            $tipoSelecionado = $tiTiposTable->find()
                ->select(['tipo'])
                ->where(['id' => $erro->classificacao_id])
                ->first()
                ?->tipo;
        }

        $this->set(compact('erro', 'classificacoesOptions', 'tiposDisponiveis', 'tipoSelecionado'));
    }

    public function uploadArquivos()
    {
        $pastasUpload = [
            'documentos' => 'Documentos',
            'parecer' => 'Parecer',
            'autorizacao' => 'Autorizacao',
            'anexos' => 'Anexos',
            'editais' => 'Editais',
            'aulas' => 'Aulas',
            'relatorios' => 'Relatorios',
            'imagens' => 'Imagens internas',
        ];

        if ($this->request->is(['post', 'put', 'patch'])) {
            $arquivo = $this->request->getData('arquivo');
            $pasta = (string)$this->request->getData('pasta');
            $nomeArquivo = trim((string)$this->request->getData('nome_arquivo'));

            if (!isset($pastasUpload[$pasta])) {
                $this->Flash->error('Pasta de destino invalida.');
                return $this->redirect(['action' => 'uploadArquivos']);
            }

            if (!is_object($arquivo) || !$arquivo->getClientFilename()) {
                $this->Flash->error('Selecione um arquivo para upload.');
                return $this->redirect(['action' => 'uploadArquivos']);
            }

            if ($nomeArquivo !== '' && strpos($nomeArquivo, '.') === false) {
                $extensaoOriginal = pathinfo((string)$arquivo->getClientFilename(), PATHINFO_EXTENSION);
                if ($extensaoOriginal !== '') {
                    $nomeArquivo .= '.' . $extensaoOriginal;
                }
            }

            $upload = parent::uploadArquivo($arquivo, $pasta, $nomeArquivo !== '' ? $nomeArquivo : null);
            if (!empty($upload['status'])) {
                $this->Flash->success('Upload realizado com sucesso. Arquivo: ' . (string)($upload['arquivo'] ?? ''));
                return $this->redirect(['action' => 'uploadArquivos']);
            }

            $this->Flash->error((string)($upload['mensagem'] ?? 'não foi possivel enviar o arquivo.'));
        }

        $this->set(compact('pastasUpload'));
    }

    public function migrarHomologacaoHistoricaProjetoBolsistas()
    {
        $this->request->allowMethod(['post']);

        $tblProjetoBolsistas = TableRegistry::getTableLocator()->get('ProjetoBolsistas');
        $connection = $tblProjetoBolsistas->getConnection();

        try {
            $resultado = $connection->transactional(function ($conn) {
                $totalFase6 = (int)$conn->execute("
                    SELECT COUNT(*) AS total
                    FROM projeto_bolsistas pb
                    WHERE pb.fase_id = 6
                ")->fetch('assoc')['total'];

                $totalFase7 = (int)$conn->execute("
                    SELECT COUNT(*) AS total
                    FROM projeto_bolsistas pb
                    WHERE pb.fase_id = 7
                ")->fetch('assoc')['total'];

                $mapeaveisFase6 = (int)$conn->execute("
                    SELECT COUNT(*) AS total
                    FROM projeto_bolsistas pb
                    INNER JOIN (
                        SELECT sh.projeto_bolsista_id, MAX(sh.id) AS historico_id
                        FROM situacao_historicos sh
                        WHERE sh.fase_atual = 6
                        GROUP BY sh.projeto_bolsista_id
                    ) ult ON ult.projeto_bolsista_id = pb.id
                    WHERE pb.fase_id = 6
                ")->fetch('assoc')['total'];

                $mapeaveisFase7 = (int)$conn->execute("
                    SELECT COUNT(*) AS total
                    FROM projeto_bolsistas pb
                    INNER JOIN (
                        SELECT sh.projeto_bolsista_id, MAX(sh.id) AS historico_id
                        FROM situacao_historicos sh
                        WHERE sh.fase_atual = 7
                        GROUP BY sh.projeto_bolsista_id
                    ) ult ON ult.projeto_bolsista_id = pb.id
                    WHERE pb.fase_id = 7
                ")->fetch('assoc')['total'];

                $conn->execute("
                    UPDATE projeto_bolsistas pb
                    INNER JOIN (
                        SELECT sh.projeto_bolsista_id, MAX(sh.id) AS historico_id
                        FROM situacao_historicos sh
                        WHERE sh.fase_atual = 6
                        GROUP BY sh.projeto_bolsista_id
                    ) ult ON ult.projeto_bolsista_id = pb.id
                    INNER JOIN situacao_historicos sh ON sh.id = ult.historico_id
                    SET
                        pb.homologado = 'S',
                        pb.homologado_data = COALESCE(sh.modified, sh.created),
                        pb.homologado_por = sh.usuario_id,
                        pb.homologado_justificativa = NULL
                    WHERE pb.fase_id = 6
                ");
                $atualizadosFase6 = (int)$conn->execute('SELECT ROW_COUNT() AS total')->fetch('assoc')['total'];

                $conn->execute("
                    UPDATE projeto_bolsistas pb
                    INNER JOIN (
                        SELECT sh.projeto_bolsista_id, MAX(sh.id) AS historico_id
                        FROM situacao_historicos sh
                        WHERE sh.fase_atual = 7
                        GROUP BY sh.projeto_bolsista_id
                    ) ult ON ult.projeto_bolsista_id = pb.id
                    INNER JOIN situacao_historicos sh ON sh.id = ult.historico_id
                    SET
                        pb.homologado = 'N',
                        pb.homologado_data = COALESCE(sh.modified, sh.created),
                        pb.homologado_por = sh.usuario_id,
                        pb.homologado_justificativa = sh.justificativa
                    WHERE pb.fase_id = 7
                ");
                $atualizadosFase7 = (int)$conn->execute('SELECT ROW_COUNT() AS total')->fetch('assoc')['total'];

                return compact(
                    'totalFase6',
                    'totalFase7',
                    'mapeaveisFase6',
                    'mapeaveisFase7',
                    'atualizadosFase6',
                    'atualizadosFase7'
                );
            });

            $semHistoricoFase6 = max(0, (int)$resultado['totalFase6'] - (int)$resultado['mapeaveisFase6']);
            $semHistoricoFase7 = max(0, (int)$resultado['totalFase7'] - (int)$resultado['mapeaveisFase7']);

            $this->Flash->success(
                'Migração de homologação concluída. ' .
                'Fase 6: alvos=' . (int)$resultado['totalFase6'] .
                ', mapeáveis=' . (int)$resultado['mapeaveisFase6'] .
                ', atualizados=' . (int)$resultado['atualizadosFase6'] .
                ', sem histórico=' . $semHistoricoFase6 . '. ' .
                'Fase 7: alvos=' . (int)$resultado['totalFase7'] .
                ', mapeáveis=' . (int)$resultado['mapeaveisFase7'] .
                ', atualizados=' . (int)$resultado['atualizadosFase7'] .
                ', sem histórico=' . $semHistoricoFase7 . '.'
            );
        } catch (\Throwable $e) {
            $this->Flash->error('Erro na migração de homologação histórica: ' . $e->getMessage());
        }

        return $this->redirect($this->referer(['action' => 'index']));
    }

    protected function uploadImagemMensagem($file): array
    {
        if (!is_object($file) || !$file->getClientFilename()) {
            return ['erro' => false, 'arquivo' => null, 'mensagem' => null];
        }

        if (method_exists($file, 'getError') && (int)$file->getError() !== UPLOAD_ERR_OK) {
            return ['erro' => true, 'arquivo' => null, 'mensagem' => 'Falha no upload da imagem.'];
        }

        $extensao = strtolower((string)pathinfo((string)$file->getClientFilename(), PATHINFO_EXTENSION));
        $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        if (!in_array($extensao, $extensoesPermitidas, true)) {
            return ['erro' => true, 'arquivo' => null, 'mensagem' => 'Formato de imagem inválido. Envie JPG, PNG, GIF, WEBP ou SVG.'];
        }

        if ((int)$file->getSize() > 2097152) {
            return ['erro' => true, 'arquivo' => null, 'mensagem' => 'A imagem deve ter no máximo 2MB.'];
        }

        $diretorio = WWW_ROOT . 'uploads' . DS . 'editais' . DS;
        if (!is_dir($diretorio) && !mkdir($diretorio, 0775, true) && !is_dir($diretorio)) {
            return ['erro' => true, 'arquivo' => null, 'mensagem' => 'Diretório webroot/uploads/editais não está disponível para gravação.'];
        }

        if (!is_writable($diretorio)) {
            return ['erro' => true, 'arquivo' => null, 'mensagem' => 'Diretório webroot/uploads/editais sem permissão de escrita.'];
        }

        $nomeArquivo = 'mensagem_' . date('Ymd_His') . '_' . uniqid() . '.' . $extensao;
        $caminho = $diretorio . $nomeArquivo;

        try {
            $file->moveTo($caminho);
        } catch (\Throwable $e) {
            return ['erro' => true, 'arquivo' => null, 'mensagem' => 'Não foi possível gravar a imagem em webroot/uploads/editais. ' . $e->getMessage()];
        }

        return ['erro' => false, 'arquivo' => $nomeArquivo, 'mensagem' => null];
    }

    protected function removerImagemMensagemArquivo(string $arquivo): void
    {
        $arquivo = trim($arquivo);
        if ($arquivo === '') {
            return;
        }

        $caminho = WWW_ROOT . 'uploads' . DS . 'editais' . DS . $arquivo;
        if (is_file($caminho)) {
            @unlink($caminho);
        }
    }

    protected function getCalendarioTipos(): array
    {
        return [
            'F' => 'Feriado',
            'A' => 'Ausência',
            'P' => 'Ponto facultativo',
            'O' => 'Indisponibilidade técnica',
        ];
    }
}
