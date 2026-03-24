<?php
namespace App\Controller;

use App\Controller\AppController;
use App\Service\DashboardUserService;
use Cake\Event\EventInterface;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;

class GestaoController extends AppController
{
    protected $identityLogado = null;
    protected $FonteHistoricos;

    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setLayout('admin');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->identityLogado = $this->Authentication->getIdentity();

        $acao = strtolower((string)$this->request->getParam('action'));
        $ehTiEstrito = in_array((int)($this->identityLogado->id ?? 0), [1, 8088], true);

        if ($acao === 'limparinscricoesexpiradas') {
            if (!$ehTiEstrito) {
                $this->Flash->error('Acesso restrito à TI.');
                return $this->redirect(['controller' => 'Index', 'action' => 'index']);
            }
            return null;
        }

        if (!$this->ehYoda()) {
            $this->Flash->error('Acesso restrito à gestão.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        return null;
    }

    public function suspender($pbId)
    {
        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $bolsista = $tblProjetoBolsistas->get((int)$pbId);
        $original = $bolsista->fase_id;
        $erros = [];

        if (!$this->ehYoda()) {
            $erros[] = 'Somente a gestão pode suspender';
        }

        if (!in_array((int)$bolsista->fase_id, [11, 18, 19], true)) {
            $erros[] = 'Somente uma bolsa ativa pode ser suspensa';
        }

        if ($bolsista->deleted !== null) {
            $erros[] = 'O registro esta inativado. Não podde ser suspensa';
        }

        if (!empty($erros) && !$this->request->is(['post', 'put', 'patch'])) {
            $this->Flash->error(implode('<br>', $erros), ['escape' => false]);
            return $this->redirect(['controller' => 'Padrao', 'action' => 'visualizar', $bolsista->id]);
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            if (!empty($erros)) {
                $this->Flash->error(implode('<br>', $erros), ['escape' => false]);
                return $this->redirect(['controller' => 'Gestao', 'action' => 'suspender', $bolsista->id]);
            }
            $dados = $this->request->getData();
            $erro = '';

            $tamanho = mb_strlen(trim((string)($dados['justificativa_cancelamento'] ?? '')));
            if ($tamanho < 20) {
                $erro .= '<li>O campo Justificatifa tem menos que 20 caracteres</li>';
            }

            $frase = '';
            if (($dados['licenca'] ?? '') === 'B') {
                $dados['prorrogacao'] = 1;
                $frase = 'Licença Materinadade';
            } else {
                $dados['prorrogacao'] = 0;
                $frase = 'Licença médica';
            }

            if ($erro !== '') {
                $erro .= '<br><big><strong>A SUSPENSÃO NÃO FOI CONCLUÍDA! <br>PREENCHA CORRETAMENTE O FORMULÁRIO</big></strong>';
                $this->Flash->error($erro, ['escape' => false]);
                return $this->redirect(['controller' => 'Gestao', 'action' => 'suspender', $bolsista->id]);
            }

            $dados['fase_id'] = 23;
            $bolsista->fase_id = $dados['fase_id'];

            try {
                $this->historico($bolsista->id, $original, $dados['fase_id'], ($frase . ' - ' . $dados['justificativa_cancelamento']));
                $bolsista = $tblProjetoBolsistas->patchEntity($bolsista, $dados);
                $tblProjetoBolsistas->saveOrFail($bolsista);
                $this->Flash->success('Bolsa Suspensa com sucesso');
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - suspender bolsa',
                    'Houve um erro solicitando a suspensão, por favor, tente novamente'
                );
                return $this->redirect(['controller' => 'Gestao', 'action' => 'suspender', $bolsista->id]);
            }

            return $this->redirect(['controller' => 'Padrao', 'action' => 'visualizar', $bolsista->id]);
        }

        $motivos = [
            'B' => 'Licença Maternidade',
            'M' => 'Licença Médica',
        ];
        $this->set(compact('motivos', 'bolsista', 'erros'));
    }

    public function fonte($pbId)
    {
        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $bolsista = $tblProjetoBolsistas->get((int)$pbId);
        $original = $bolsista->tipo_bolsa;
        $erros = [];

        if (!$this->ehYoda()) {
            $erros[] = 'Somente a gestão pode alterar a fonte pagadora';
        }

        if (!empty($erros) && !$this->request->is(['post', 'put', 'patch'])) {
            $this->Flash->error(implode('<br>', $erros), ['escape' => false]);
            return $this->redirect(['controller' => 'Padrao', 'action' => 'visualizar', $bolsista->id]);
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            if (!empty($erros)) {
                $this->Flash->error(implode('<br>', $erros), ['escape' => false]);
                return $this->redirect(['controller' => 'Gestao', 'action' => 'fonte', $bolsista->id]);
            }

            $dados = $this->request->getData();

            $this->FonteHistoricos = TableRegistry::getTableLocator()->get('FonteHistoricos');
            $novo = $this->FonteHistoricos->newEmptyEntity();
            $novo->projeto_bolsista_id = $bolsista->id;
            $novo->usuario_id = $this->request->getAttribute('identity')->id;
            $novo->fonte_original = $original;
            $novo->fonte_atual = $dados['tipo_bolsa'] ?? null;

            if (!$this->FonteHistoricos->save($novo)) {
                $this->Flash->error('Houve um erro na gravação. Tente novamente');
                return $this->redirect($this->referer());
            }

            $bolsista->tipo_bolsa = $dados['tipo_bolsa'] ?? null;
            $bolsista = $tblProjetoBolsistas->patchEntity($bolsista, $dados);
            if ($tblProjetoBolsistas->save($bolsista)) {
                $this->Flash->success('Alteração realizada com sucesso');
            } else {
                $this->Flash->error('Houve um erro, por favor, tente novamente');
            }

            return $this->redirect(['controller' => 'Padrao', 'action' => 'visualizar', $bolsista->id]);
        }

        $fontes = $this->fonte;
        $this->set(compact('bolsista', 'erros', 'fontes'));
    }

    public function addarquivo($pbId)
    {
        $inscricaoId = (int)$pbId;
        $inscricao = $this->fetchTable('ProjetoBolsistas')->find()
            ->contain([
                'Projetos',
                'Editais' => ['Programas'],
                'Orientadores',
                'Bolsistas',
                'Fases',
            ])
            ->where(['ProjetoBolsistas.id' => $inscricaoId])
            ->first();

        if (!$inscricao) {
            $this->Flash->error('Inscrição não localizada.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }

        if ($inscricao->deleted !== null) {
            $this->Flash->error('O registro foi deletado. Não é possível inserir/alterar anexos.');
            return $this->redirect(['controller' => 'Padrao', 'action' => 'visualizar', $inscricaoId]);
        }

        $tiposAnexo = $this->fetchTable('AnexosTipos')->find()
            ->select(['id', 'nome', 'bloco'])
            ->where(['AnexosTipos.deleted' => 0])
            ->orderBy(['AnexosTipos.bloco' => 'ASC', 'AnexosTipos.id' => 'ASC'])
            ->all()
            ->toList();

        $tiposPermitidos = array_map(
            static fn($tipo) => (int)$tipo->id,
            $tiposAnexo
        );

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = (array)$this->request->getData();

            $acaoRapida = $this->processarAcaoRapidaAnexoInscricao(
                $dados,
                (int)($inscricao->projeto_id ?? 0),
                $inscricaoId,
                $tiposPermitidos
            );
            if ($acaoRapida !== null) {
                return $this->redirect(['controller' => 'Gestao', 'action' => 'addarquivo', $inscricaoId]);
            }

            $anexosUpload = $dados['anexos'] ?? [];
            if (!is_array($anexosUpload)) {
                $anexosUpload = [];
            }

            $temArquivo = false;
            foreach ($anexosUpload as $arquivoUpload) {
                if (is_object($arquivoUpload) && method_exists($arquivoUpload, 'getClientFilename') && $arquivoUpload->getClientFilename() !== '') {
                    $temArquivo = true;
                    break;
                }
            }
            if (!$temArquivo) {
                $this->Flash->error('Selecione ao menos um arquivo para enviar.');
                return $this->redirect(['controller' => 'Gestao', 'action' => 'addarquivo', $inscricaoId]);
            }

            try {
                $ok = $this->anexarInscricao(
                    $anexosUpload,
                    (int)($inscricao->projeto_id ?? 0),
                    $inscricaoId,
                    null,
                    true
                );
                if ($ok) {
                    $this->historico(
                        $inscricaoId,
                        (int)$inscricao->fase_id,
                        (int)$inscricao->fase_id,
                        'Alteração/Inserção de anexos pela gestão'
                    );
                    $this->Flash->success('Anexos atualizados com sucesso.');
                } else {
                    $this->Flash->error('Não foi possível atualizar os anexos. Tente novamente.');
                }
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - gestão addarquivo',
                    'Não foi possível atualizar os anexos.'
                );
            }

            return $this->redirect(['controller' => 'Gestao', 'action' => 'addarquivo', $inscricaoId]);
        }

        $anexosMap = [];
        if (!empty($tiposPermitidos)) {
            $anexosAtivos = $this->fetchTable('Anexos')->find()
                ->select(['id', 'anexos_tipo_id', 'anexo', 'usuario_id', 'created'])
                ->contain([
                    'Usuarios' => function ($q) {
                        return $q->select(['id', 'nome']);
                    },
                ])
                ->where([
                    'Anexos.projeto_bolsista_id' => $inscricaoId,
                    'Anexos.anexos_tipo_id IN' => $tiposPermitidos,
                    'OR' => [
                        'Anexos.deleted IS' => null,
                        'Anexos.deleted' => 0,
                    ],
                ])
                ->orderBy(['Anexos.id' => 'DESC'])
                ->all();
            foreach ($anexosAtivos as $anexo) {
                $tipoId = (int)($anexo->anexos_tipo_id ?? 0);
                if ($tipoId <= 0 || isset($anexosMap[$tipoId])) {
                    continue;
                }
                $anexosMap[$tipoId] = [
                    'arquivo' => (string)($anexo->anexo ?? ''),
                    'usuario_nome' => trim((string)($anexo->usuario->nome ?? '')),
                    'data_inclusao' => $anexo->created ?? null,
                ];
            }
        }

        $anexosLista = [];
        foreach ($tiposAnexo as $tipo) {
            $tipoId = (int)$tipo->id;
            $anexosLista[] = [
                'tipo_id' => $tipoId,
                'tipo_nome' => (string)$tipo->nome,
                'bloco' => (string)$tipo->bloco,
                'arquivo' => (string)($anexosMap[$tipoId]['arquivo'] ?? ''),
                'usuario_nome' => (string)($anexosMap[$tipoId]['usuario_nome'] ?? ''),
                'data_inclusao' => $anexosMap[$tipoId]['data_inclusao'] ?? null,
            ];
        }

        $this->set(compact('inscricao', 'anexosLista'));
    }

    public function lancarresultados()
    {
        $resultadoMap = [
            'A' => 9,
            'R' => 10,
            'B' => 8,
        ];
        $resultadoLabels = $this->resultado ?? [];
        $origemLabels = $this->origem ?? [];
        $faseLabels = $this->fetchTable('Fases')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])->toArray();
        $resultadoSelecionado = strtoupper(trim((string)$this->request->getData('resultado')));
        $idsRaw = (string)$this->request->getData('inscricoes');
        $modo = strtolower(trim((string)$this->request->getData('modo')));
        $preview = null;
        $faseLabels = $this->fetchTable('Fases')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])->toArray();

        if ($this->request->is(['post', 'put', 'patch'])) {
            if (!isset($resultadoMap[$resultadoSelecionado])) {
                $this->Flash->error('Selecione um resultado válido.');
            } else {
                $ids = $this->parseInscricaoIdsLote($idsRaw);
                if (empty($ids)) {
                    $this->Flash->error('Informe ao menos uma inscrição válida.');
                } else {
                    $preview = $this->avaliarLoteResultados($ids, $resultadoSelecionado, $resultadoMap);

                    if ($modo === 'confirmar') {
                        if (empty($preview['aptas'])) {
                            $this->Flash->error('Nenhuma inscrição apta para atualização.');
                        } else {
                            $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
                            $faseDestino = $resultadoMap[$resultadoSelecionado];
                            $atualizadas = 0;

                            try {
                                $resultadoHistorico = $resultadoLabels[$resultadoSelecionado] ?? $resultadoSelecionado;
                                $tblProjetoBolsistas->getConnection()->transactional(function () use (
                                    $tblProjetoBolsistas,
                                    $preview,
                                    $resultadoSelecionado,
                                    $resultadoHistorico,
                                    $faseDestino,
                                    &$atualizadas
                                ) {
                                    foreach ($preview['aptas'] as $item) {
                                        $inscricao = $tblProjetoBolsistas->get((int)$item['id']);
                                        $faseOriginal = (int)$inscricao->fase_id;
                                        $inscricao->resultado = $resultadoSelecionado;
                                        $inscricao->fase_id = $faseDestino;
                                        $tblProjetoBolsistas->saveOrFail($inscricao);
                                        $this->historico(
                                            (int)$inscricao->id,
                                            $faseOriginal,
                                            $faseDestino,
                                            'Lançamento massivo de resultado: ' . $resultadoHistorico,
                                            true
                                        );
                                        $atualizadas++;
                                    }
                                });

                                $this->Flash->success($atualizadas . ' inscrição(ões) atualizada(s) com sucesso.');
                                return $this->redirect(['controller' => 'Gestao', 'action' => 'lancarresultados']);
                            } catch (\Throwable $e) {
                                $this->flashFriendlyException(
                                    $e,
                                    'Erro no Sistema - lançamento massivo de resultados',
                                    'Não foi possível concluir o lançamento massivo de resultados.'
                                );
                            }
                        }
                    }
                }
            }
        }

        $this->set(compact('resultadoMap', 'resultadoLabels', 'origemLabels', 'faseLabels', 'resultadoSelecionado', 'idsRaw', 'preview'));
    }

    public function vigencias($modo = 'A')
    {
        $modo = strtoupper(trim((string)$modo));
        if (!in_array($modo, ['A', 'E'], true)) {
            $this->Flash->error('Modo inválido.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashyoda']);
        }

        $agora = FrozenTime::now();
        $editalId = (int)$this->request->getData('edital_id');
        $etapa = strtolower(trim((string)$this->request->getData('etapa')));
        $preview = null;
        $faseLabels = $this->fetchTable('Fases')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])->toArray();

        $condicoesEditais = [
            'Editais.deleted' => 0,
            'Editais.visualizar' => 'E',
        ];
        if ($modo === 'A') {
            $condicoesEditais['Editais.inicio_vigencia >'] = $agora;
        } else {
            $condicoesEditais['Editais.inicio_vigencia <='] = $agora;
            $condicoesEditais['Editais.fim_vigencia >='] = $agora;
        }

        $editais = $this->fetchTable('Editais')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where($condicoesEditais)
            ->orderBy(['Editais.nome' => 'ASC'])
            ->toArray();

        if ($this->request->is(['post', 'put', 'patch'])) {
            if ($editalId <= 0 || !isset($editais[$editalId])) {
                $this->Flash->error('Selecione um edital válido.');
            } else {
                $preview = $this->avaliarLoteVigencias($editalId, $modo);

                if ($etapa === 'confirmar') {
                    if (empty($preview['aptas'])) {
                        $this->Flash->error('Nenhum registro apto para processamento.');
                    } else {
                        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
                        $edital = $preview['edital'];
                        $processadas = 0;
                        $justificativaHistorico = $modo === 'A'
                            ? 'Ativação massiva de bolsas pelo edital'
                            : 'Encerramento massivo de bolsas pelo edital';

                        try {
                            $tblProjetoBolsistas->getConnection()->transactional(function () use (
                                $tblProjetoBolsistas,
                                $preview,
                                $modo,
                                $edital,
                                $justificativaHistorico,
                                &$processadas
                            ) {
                                foreach ($preview['aptas'] as $item) {
                                    $inscricao = $tblProjetoBolsistas->get((int)$item['id']);
                                    $faseOriginal = (int)$inscricao->fase_id;

                                    if ($modo === 'A') {
                                        $inscricao->data_inicio = $edital->inicio_vigencia;
                                        $inscricao->vigente = 1;
                                        $inscricao->fase_id = 11;
                                    } else {
                                        $inscricao->data_fim = $edital->fim_vigencia;
                                        $inscricao->vigente = 0;
                                        $inscricao->fase_id = 17;
                                    }

                                    $tblProjetoBolsistas->saveOrFail($inscricao);
                                    $this->historico(
                                        (int)$inscricao->id,
                                        $faseOriginal,
                                        (int)$inscricao->fase_id,
                                        $justificativaHistorico . ' - edital #' . (int)$edital->id,
                                        true
                                    );
                                    $processadas++;
                                }
                            });

                            $mensagem = $modo === 'A'
                                ? 'Ativação massiva concluída com sucesso.'
                                : 'Encerramento massivo concluído com sucesso.';
                            $this->Flash->success($mensagem . ' Total: ' . $processadas . '.');
                            return $this->redirect(['controller' => 'Gestao', 'action' => 'vigencias', $modo]);
                        } catch (\Throwable $e) {
                            $this->flashFriendlyException(
                                $e,
                                'Erro no Sistema - processamento massivo de vigências',
                                'Não foi possível concluir o processamento massivo.'
                            );
                        }
                    }
                }
            }
        }

        $titulo = $modo === 'A' ? 'Ativar Aprovadas' : 'Encerrar Vigentes';
        $this->set(compact('modo', 'titulo', 'editais', 'editalId', 'preview', 'faseLabels'));
    }

    public function limparinscricoesexpiradas()
    {
        $agora = FrozenTime::now();
        $fraseHistorico = 'deletado sistemicamente pois a inscrição nao foi finalizada em tempo de inscrição do edital';
        $faseLabels = $this->fetchTable('Fases')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])->toArray();

        $query = $this->fetchTable('ProjetoBolsistas')->find()
            ->contain(['Editais'])
            ->where([
                'ProjetoBolsistas.deleted IS' => null,
                'ProjetoBolsistas.fase_id IN' => [1, 2, 3, 5],
                'Editais.fim_inscricao <' => $agora,
            ])
            ->orderBy(['ProjetoBolsistas.id' => 'ASC']);

        $inscricoes = $query->all()->toList();
        $total = count($inscricoes);

        if ($this->request->is(['post', 'put', 'patch'])) {
            if ($total === 0) {
                $this->Flash->error('Nenhuma inscrição apta para deleção sistêmica.');
                return $this->redirect(['controller' => 'Gestao', 'action' => 'limparinscricoesexpiradas']);
            }

            $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
            $processadas = 0;

            try {
                $tblProjetoBolsistas->getConnection()->transactional(function () use (
                    $tblProjetoBolsistas,
                    $inscricoes,
                    $agora,
                    $fraseHistorico,
                    &$processadas
                ) {
                    foreach ($inscricoes as $item) {
                        $inscricao = $tblProjetoBolsistas->get((int)$item->id);
                        $faseOriginal = (int)$inscricao->fase_id;
                        $inscricao->deleted = $agora;
                        $inscricao->vigente = 0;
                        $tblProjetoBolsistas->saveOrFail($inscricao);
                        $this->historico(
                            (int)$inscricao->id,
                            $faseOriginal,
                            $faseOriginal,
                            $fraseHistorico,
                            true
                        );
                        $processadas++;
                    }
                });

                $this->Flash->success($processadas . ' inscrição(ões) deletada(s) sistemicamente com sucesso.');
                return $this->redirect(['controller' => 'Gestao', 'action' => 'limparinscricoesexpiradas']);
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - deleção sistêmica de inscrições expiradas',
                    'Não foi possível concluir a deleção sistêmica das inscrições expiradas.'
                );
            }
        }

        $this->set(compact('inscricoes', 'total', 'fraseHistorico', 'faseLabels'));
    }

    public function listarconfirmacoes(string $tipo)
    {
        $tipo = strtoupper(trim($tipo));
        if (!in_array($tipo, ['C', 'S'], true)) {
            $this->Flash->error('Tipo de confirmação inválido.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $fases = $tipo === 'C' ? [12, 21] : [15];

        $formatarNome = function (?string $nome): string {
            $nome = trim((string)$nome);
            if ($nome === '') {
                return '-';
            }
            $partes = preg_split('/\s+/', $nome);
            if (!$partes || count($partes) === 1) {
                return $nome;
            }
            return $partes[0] . ' ' . end($partes);
        };

        $registros = [];

        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $icQuery = $tblProjetoBolsistas->find()
            ->contain([
                'Bolsistas',
                'Orientadores' => ['Unidades'],
                'Substitutos' => ['Bolsistas'],
                'Editais',
            ])
            ->where(['ProjetoBolsistas.fase_id IN' => $fases])
            ->orderBy(['ProjetoBolsistas.id' => 'DESC']);
        if ($tipo !== 'C') {
            $icQuery->where(['ProjetoBolsistas.deleted IS' => null]);
        }

        foreach ($icQuery as $item) {
            $dataSolicitacao = $item->data_cancelamento ?? $item->created ?? $item->modified ?? null;
            $registros[] = [
                'id' => (int)$item->id,
                'fonte' => 'IC',
                'programa_id' => (int)($item->programa_id ?? $item->programa ?? 0),
                'bolsista_entrando' => $formatarNome($item->bolsista_usuario->nome ?? null),
                'bolsista_saindo' => $formatarNome($item->substituto->bolsista_usuario->nome ?? null),
                'orientador' => $formatarNome($item->orientadore->nome ?? null),
                'unidade' => (string)($item->orientadore->unidade->sigla ?? '-'),
                'data_solicitacao' => $dataSolicitacao,
                'cota' => (string)($item->cota ?? ''),
                'programa' => (string)($item->programa ?? ''),
                'edital' => (string)($item->editai->nome ?? ''),
                'deleted' => $item->deleted ?? null,
            ];
        }

        $tblPdj = $this->fetchTable('PdjInscricoes');
        $pdjQuery = $tblPdj->find()
            ->contain([
                'Bolsistas',
                'Orientadores' => ['Unidades'],
                'Substitutospdj' => ['Bolsistas'],
                'Editais',
            ])
            ->where(['PdjInscricoes.fase_id IN' => $fases])
            ->orderBy(['PdjInscricoes.id' => 'DESC']);
        if ($tipo !== 'C') {
            $pdjQuery->where(['PdjInscricoes.deleted IS' => null]);
        }

        $pdjRows = $pdjQuery->all();
        $mapPdjToPb = [];
        if (!$pdjRows->isEmpty()) {
            $pdjIds = [];
            foreach ($pdjRows as $row) {
                $pdjIds[] = (int)($row->id ?? 0);
            }
            $pdjIds = array_values(array_filter($pdjIds));
            if (!empty($pdjIds)) {
                $mapPdjToPb = $tblProjetoBolsistas->find()
                    ->select(['id', 'pdj_inscricoe_id'])
                    ->where(['pdj_inscricoe_id IN' => $pdjIds])
                    ->enableHydration(false)
                    ->all()
                    ->combine('pdj_inscricoe_id', 'id')
                    ->toArray();
            }
        }

        foreach ($pdjRows as $item) {
            $dataSolicitacao = $item->data_cancelamento ?? $item->created ?? $item->modified ?? null;
            $itemId = (int)($item->id ?? 0);
            $idReferencia = $mapPdjToPb[$itemId] ?? $itemId;
            $registros[] = [
                'id' => (int)$idReferencia,
                'fonte' => 'PDJ',
                'programa_id' => (int)($item->programa_id ?? $item->programa ?? 0),
                'bolsista_entrando' => $formatarNome($item->bolsista_usuario->nome ?? null),
                'bolsista_saindo' => $formatarNome($item->substitutospdj->bolsista_usuario->nome ?? null),
                'orientador' => $formatarNome($item->orientadore->nome ?? null),
                'unidade' => (string)($item->orientadore->unidade->sigla ?? '-'),
                'data_solicitacao' => $dataSolicitacao,
                'cota' => (string)($item->cota ?? ''),
                'programa' => (string)($item->programa ?? ''),
                'edital' => (string)($item->editai->nome ?? ''),
                'deleted' => $item->deleted ?? null,
            ];
        }

        usort($registros, fn($a, $b) => ($b['id'] <=> $a['id']));

        $cotas = $this->cota;
        $this->set(compact('tipo', 'registros', 'cotas'));
    }

    public function listahomologacao()
    {
        $programaId = (int)($this->request->getQuery('programa_id') ?? 0);
        $faseIdParam = $this->request->getQuery('fase_id');
        $faseId = $faseIdParam === null ? 4 : (int)$faseIdParam;
        $agora = FrozenTime::now();

        $fasesFiltro = [4, 6, 7];
        $conditions = [
            'ProjetoBolsistas.deleted IS' => null,
            'Editais.inicio_vigencia >' => $agora,
        ];

        if ($faseId > 0) {
            $conditions['ProjetoBolsistas.fase_id'] = $faseId;
        } else {
            $conditions['ProjetoBolsistas.fase_id IN'] = $fasesFiltro;
        }

        if ($programaId > 0) {
            $conditions['Editais.programa_id'] = $programaId;
        }

        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $query = $tblProjetoBolsistas->find()
            ->contain([
                'Editais' => ['Programas'],
                'Bolsistas',
                'Orientadores' => ['Unidades'],
                'Coorientadores',
                'Fases',
            ])
            ->where($conditions)
            ->orderBy(['ProjetoBolsistas.id' => 'DESC']);

        $programas = $this->fetchTable('Programas')->find('list', ['limit' => 200])->toArray();
        $fases = $this->fetchTable('Fases')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])->where(['Fases.id IN' => $fasesFiltro])->toArray();

        if ($this->request->getQuery('acao') === 'excel') {
            $origemExportacao = (string)$this->request->getQuery('origem');
            if ($origemExportacao === 'dashyoda') {
                $excelConditions = [
                    'ProjetoBolsistas.deleted IS' => null,
                    'Editais.inicio_vigencia >' => $agora,
                    'ProjetoBolsistas.fase_id IN' => [4, 6, 7],
                ];
            } else {
                $excelConditions = $conditions;
            }

            $excelQuery = $tblProjetoBolsistas->find()
                ->contain([
                    'Editais' => ['Programas'],
                    'Bolsistas',
                    'Orientadores' => ['Unidades'],
                    'Coorientadores',
                    'Fases',
                ])
                ->where($excelConditions)
                ->orderBy(['ProjetoBolsistas.id' => 'DESC']);

            $rows = $excelQuery->all();
            $idsInscricao = [];
            foreach ($rows as $item) {
                $idInscricao = (int)($item->id ?? 0);
                if ($idInscricao > 0) {
                    $idsInscricao[] = $idInscricao;
                }
            }
            $idsInscricao = array_values(array_unique($idsInscricao));

            $justificativaNaoHomologacaoMap = [];
            if (!empty($idsInscricao)) {
                $historicos = $this->fetchTable('SituacaoHistoricos')->find()
                    ->select(['id', 'projeto_bolsista_id', 'justificativa'])
                    ->where([
                        'SituacaoHistoricos.projeto_bolsista_id IN' => $idsInscricao,
                        'SituacaoHistoricos.fase_atual' => 7,
                    ])
                    ->orderBy([
                        'SituacaoHistoricos.projeto_bolsista_id' => 'ASC',
                        'SituacaoHistoricos.id' => 'DESC',
                    ])
                    ->enableHydration(false)
                    ->all();

                foreach ($historicos as $historico) {
                    $idInscricao = (int)($historico['projeto_bolsista_id'] ?? 0);
                    if ($idInscricao <= 0 || isset($justificativaNaoHomologacaoMap[$idInscricao])) {
                        continue;
                    }
                    $justificativaNaoHomologacaoMap[$idInscricao] = (string)($historico['justificativa'] ?? '');
                }
            }

            $header = [
                'id',
                'edital',
                'programa',
                'fase',
                'bolsista',
                'orientador',
                'unidade',
                'data_inscricao',
                'telefone',
                'telefone_contato',
                'celular',
                'whatsapp',
                'email',
                'email_alternativo',
                'email_contato',
                'justificativa_ultima_nao_homologacao',
            ];

            $exportRows = [];
            foreach ($rows as $item) {
                $faseAtualId = (int)($item->fase_id ?? 0);
                $exportRows[] = [
                    $item->id ?? '',
                    $item->editai->nome ?? '',
                    $item->editai->programa->sigla ?? ($item->editai->programa_id ?? ''),
                    $item->fase->nome ?? '',
                    $item->bolsista_usuario->nome ?? '',
                    $item->orientadore->nome ?? '',
                    $item->orientadore->unidade->sigla ?? '',
                    $item->created ? $item->created->format('d/m/Y H:i:s') : '',
                    $item->orientadore->telefone ?? '',
                    $item->orientadore->telefone_contato ?? '',
                    $item->orientadore->celular ?? '',
                    $item->orientadore->whatsapp ?? '',
                    $item->orientadore->email ?? '',
                    $item->orientadore->email_alternativo ?? '',
                    $item->orientadore->email_contato ?? '',
                    $faseAtualId === 7
                        ? ($justificativaNaoHomologacaoMap[(int)($item->id ?? 0)] ?? '')
                        : '',
                ];
            }

            return $this->downloadCsvResponse(
                'lista_homologacao_' . date('Ymd_His') . '.csv',
                $header,
                $exportRows
            );
        }

        $inscricoes = $this->paginate($query, ['limit' => 20]);
        $this->set(compact('inscricoes', 'programas', 'fases', 'programaId', 'faseId'));
    }

    public function telahomologacao($id)
    {
        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $inscricao = $tblProjetoBolsistas->find()
            ->contain([
                'Editais' => ['Programas'],
                'Bolsistas',
                'Projetos',
                'Orientadores' => ['Unidades', 'Vinculos'],
                'Coorientadores' => ['Vinculos'],
                'Fases',
            ])
            ->where(['ProjetoBolsistas.id' => (int)$id])
            ->first();

        if (!$inscricao) {
            $this->Flash->error('Inscrição não localizada para homologação.');
            return $this->redirect(['controller' => 'Gestao', 'action' => 'listahomologacao']);
        }
        $faseOriginal = (int)$inscricao->fase_id;
        $fasesPermitidasHomologacao = [4, 6, 7];
        $homologacaoPermitida = in_array($faseOriginal, $fasesPermitidasHomologacao, true);
        $exigeConfirmacaoReavaliacao = in_array($faseOriginal, [6, 7], true);
        $motivoNaoHomologacao = trim((string)($this->request->getData('motivo_nao_homologacao') ?? ''));

        if ($this->request->is(['post', 'put', 'patch'])) {
            $acaoRapidaAnexo = (string)($this->request->getData('anexo_acao') ?? '');
            $tipoRapido = (int)($this->request->getData('anexo_tipo') ?? 0);
            $tipoRapidoAlternativo = (int)($this->request->getData('alterar_anexo_tipo') ?? 0);
            if ($acaoRapidaAnexo === 'alterar' || $tipoRapido > 0 || $tipoRapidoAlternativo > 0) {
                $tiposPermitidosAnexo = $this->fetchTable('AnexosTipos')->find()
                    ->select(['id'])
                    ->where([
                        'AnexosTipos.deleted' => 0,
                        'AnexosTipos.bloco IN' => ['B', 'C', 'I'],
                    ])
                    ->enableHydration(false)
                    ->all()
                    ->extract('id')
                    ->map(fn($idTipo) => (int)$idTipo)
                    ->toList();

                $this->processarAcaoRapidaAnexoInscricao(
                    (array)$this->request->getData(),
                    (int)($inscricao->projeto_id ?? 0),
                    (int)$inscricao->id,
                    $tiposPermitidosAnexo
                );

                return $this->redirect(['controller' => 'Gestao', 'action' => 'telahomologacao', (int)$inscricao->id]);
            }
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            if (!$this->ehYoda()) {
                $this->Flash->error('Somente perfil yoda pode homologar ou não homologar inscrições.');
                return $this->redirect(['controller' => 'Gestao', 'action' => 'listahomologacao']);
            }
            if (!$homologacaoPermitida) {
                $this->Flash->error('Somente inscrições nas fases Finalizada, Homologada ou Não homologada podem ser homologadas ou não homologadas.');
                return $this->redirect(['controller' => 'Gestao', 'action' => 'telahomologacao', (int)$inscricao->id]);
            }
            $acaoHomologacao = strtolower(trim((string)($this->request->getData('acao_homologacao') ?? '')));
            $confirmouReavaliacao = (int)$this->request->getData('confirmou_reavaliacao') === 1;
            if ($exigeConfirmacaoReavaliacao && !$confirmouReavaliacao) {
                $this->Flash->error('Esta inscrição já havia sido verificada. Confirme que deseja alterar a avaliação.');
                return $this->redirect(['controller' => 'Gestao', 'action' => 'telahomologacao', (int)$inscricao->id]);
            }
            if ($acaoHomologacao === 'homologar') {
                try {
                    $tblProjetoBolsistas->getConnection()->transactional(function () use ($tblProjetoBolsistas, $inscricao, $faseOriginal) {
                        $inscricao->fase_id = 6;
                        $tblProjetoBolsistas->saveOrFail($inscricao);
                        $this->historico(
                            (int)$inscricao->id,
                            $faseOriginal,
                            6,
                            'Inscrição homologada após verificação dos anexos e dados'
                        );
                    });
                    $this->Flash->success('Inscrição homologada com sucesso.');
                    return $this->redirect(['controller' => 'Gestao', 'action' => 'listahomologacao']);
                } catch (\Throwable $e) {
                    $this->flashFriendlyException(
                        $e,
                        'Erro no Sistema - homologar inscrição',
                        'Não foi possível homologar a inscrição.'
                    );
                }
            } elseif ($acaoHomologacao === 'nao_homologar') {
                if (mb_strlen($motivoNaoHomologacao) < 20) {
                    $this->Flash->error('Informe o motivo da não homologação com pelo menos 20 caracteres.');
                } else {
                    try {
                        $tblProjetoBolsistas->getConnection()->transactional(function () use ($tblProjetoBolsistas, $inscricao, $faseOriginal, $motivoNaoHomologacao) {
                            $inscricao->fase_id = 7;
                            $tblProjetoBolsistas->saveOrFail($inscricao);
                            $this->historico(
                                (int)$inscricao->id,
                                $faseOriginal,
                                7,
                                'Inscrição não homologada devido a: ' . $motivoNaoHomologacao
                            );
                        });
                        $this->Flash->success('Inscrição marcada como não homologada com sucesso.');
                        return $this->redirect(['controller' => 'Gestao', 'action' => 'listahomologacao']);
                    } catch (\Throwable $e) {
                        $this->flashFriendlyException(
                            $e,
                            'Erro no Sistema - não homologar inscrição',
                            'Não foi possível registrar a não homologação da inscrição.'
                        );
                    }
                }
            } else {
                $this->Flash->error('Ação de homologação inválida.');
            }
        }

        $tiposB = $this->fetchTable('AnexosTipos')->find()
            ->select(['id', 'nome', 'condicional', 'programa', 'cota'])
            ->where([
                'AnexosTipos.deleted' => 0,
                'AnexosTipos.bloco' => 'B',
            ])
            ->orderBy(['AnexosTipos.id' => 'ASC'])
            ->all();
        $programasMap = $this->fetchTable('Programas')->find()
            ->select(['id', 'sigla'])
            ->orderBy(['Programas.id' => 'ASC'])
            ->all()
            ->combine('id', 'sigla')
            ->toArray();

        $tiposBIds = [];
        foreach ($tiposB as $tipoB) {
            $tiposBIds[] = (int)$tipoB->id;
        }
        $anexosBMap = [];
        $anexosBAtivos = $this->fetchTable('Anexos')->find()
            ->select(['anexos_tipo_id', 'anexo', 'usuario_id', 'created'])
            ->contain([
                'Usuarios' => function ($q) {
                    return $q->select(['id', 'nome']);
                },
            ])
            ->where([
                'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                'Anexos.anexos_tipo_id IN' => $tiposBIds,
                'OR' => [
                    'Anexos.deleted IS' => null,
                    'Anexos.deleted' => 0,
                ],
            ])
            ->orderBy(['Anexos.id' => 'DESC'])
            ->all();
        foreach ($anexosBAtivos as $anexo) {
            $tipoId = (int)($anexo->anexos_tipo_id ?? 0);
            if ($tipoId <= 0 || (string)($anexo->anexo ?? '') === '') {
                continue;
            }
            if (!isset($anexosBMap[$tipoId])) {
                $anexosBMap[$tipoId] = [
                    'arquivo' => (string)$anexo->anexo,
                    'usuario_nome' => trim((string)($anexo->usuario->nome ?? '')),
                    'data_inclusao' => $anexo->created ?? null,
                ];
            }
        }

        $anexosB = [];
        $programaEdital = (string)($inscricao->editai->programa_id ?? '');
        $cotaAtual = strtoupper(trim((string)($inscricao->cota ?? '')));
        $primeiroPeriodo = $inscricao->primeiro_periodo !== null ? (string)(int)$inscricao->primeiro_periodo : '';
        $bolsistaMenorIdade = false;
        $dataNascimentoBolsista = $inscricao->bolsista_usuario->data_nascimento
            ?? $inscricao->bolsista->data_nascimento
            ?? null;
        if (!empty($dataNascimentoBolsista)) {
            if (is_object($dataNascimentoBolsista) && method_exists($dataNascimentoBolsista, 'i18nFormat')) {
                $dataNascimentoBolsista = $dataNascimentoBolsista->i18nFormat('yyyy-MM-dd');
            }
            $idade = (int)$this->idade((string)$dataNascimentoBolsista, false);
            $bolsistaMenorIdade = $idade < 18;
        }
        foreach ($tiposB as $tipo) {
            $tipoId = (int)$tipo->id;
            $condicional = (int)($tipo->condicional ?? 0);
            $programaRegra = trim((string)($tipo->programa ?? ''));
            $cotaRegra = strtoupper(trim((string)($tipo->cota ?? '')));
            $regras = [];
            $statusRegra = 'Obrigatório';
            $mostrarTipo = false;

            if ($condicional === 0) {
                if ($programaRegra === '' && $cotaRegra === '') {
                    $regras[] = 'Obrigatório';
                    $mostrarTipo = true;
                }

                if ($programaRegra !== '' && $cotaRegra === '') {
                    $programas = array_values(array_filter(array_map('trim', explode(',', $programaRegra))));
                    $siglasProgramas = [];
                    foreach ($programas as $pid) {
                        if (isset($programasMap[$pid])) {
                            $siglasProgramas[] = $programasMap[$pid];
                        }
                    }
                    $listaProgramas = !empty($siglasProgramas) ? implode(', ', $siglasProgramas) : implode(', ', $programas);
                    if ($listaProgramas !== '') {
                        $regras[] = 'Obrigatório nos programas: ' . $listaProgramas . '.';
                    }

                    if ($programaEdital !== '' && in_array($programaEdital, $programas, true)) {
                        $regras[] = 'Obrigatório neste edital.';
                        $mostrarTipo = true;
                    }
                }

                if ($programaRegra === '' && $cotaRegra !== '') {
                    $cotasRegra = array_values(array_filter(array_map('trim', explode(',', $cotaRegra))));
                    $listaCotas = implode(', ', $cotasRegra);
                    if ($listaCotas !== '') {
                        $regras[] = 'Obrigatório nas cotas: ' . $listaCotas . '.';
                    }

                    if ($cotaAtual !== '' && in_array($cotaAtual, $cotasRegra, true)) {
                        $regras[] = 'Obrigatório nesta cota.';
                        $mostrarTipo = true;
                    }
                }
            } else {
                if ($programaRegra === '' && $cotaRegra === '') {
                    if ($tipoId === 16 && $primeiroPeriodo === '0') {
                        $regras[] = 'Condicional: obrigatório para bolsista fora do primeiro período.';
                        $mostrarTipo = true;
                    }
                    if (in_array($tipoId, [18, 19], true) && $bolsistaMenorIdade) {
                        $regras[] = 'Condicional: obrigatório para bolsista menor de idade.';
                        $mostrarTipo = true;
                    }
                }
            }

            if (!$mostrarTipo) {
                continue;
            }

            $anexosB[] = [
                'tipo_id' => $tipoId,
                'tipo_nome' => (string)$tipo->nome,
                'arquivo' => (string)($anexosBMap[$tipoId]['arquivo'] ?? ''),
                'usuario_nome' => (string)($anexosBMap[$tipoId]['usuario_nome'] ?? ''),
                'data_inclusao' => $anexosBMap[$tipoId]['data_inclusao'] ?? null,
                'regras' => $regras,
                'status_regra' => $statusRegra,
            ];
        }
        $tiposI = $this->fetchTable('AnexosTipos')->find()
            ->select(['id', 'nome', 'condicional', 'programa', 'cota'])
            ->where([
                'AnexosTipos.deleted' => 0,
                'AnexosTipos.bloco' => 'I',
            ])
            ->orderBy(['AnexosTipos.id' => 'ASC'])
            ->all();
        $tiposIIds = [];
        foreach ($tiposI as $tipoI) {
            $tiposIIds[] = (int)$tipoI->id;
        }
        $anexosIMap = [];
        if (!empty($tiposIIds)) {
            $anexosIAtivos = $this->fetchTable('Anexos')->find()
                ->select(['anexos_tipo_id', 'anexo', 'usuario_id', 'created'])
                ->contain([
                    'Usuarios' => function ($q) {
                        return $q->select(['id', 'nome']);
                    },
                ])
                ->where([
                    'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                    'Anexos.anexos_tipo_id IN' => $tiposIIds,
                    'OR' => [
                        'Anexos.deleted IS' => null,
                        'Anexos.deleted' => 0,
                    ],
                ])
                ->orderBy(['Anexos.id' => 'DESC'])
                ->all();
            foreach ($anexosIAtivos as $anexoI) {
                $tipoId = (int)($anexoI->anexos_tipo_id ?? 0);
                if ($tipoId <= 0 || (string)($anexoI->anexo ?? '') === '') {
                    continue;
                }
                if (!isset($anexosIMap[$tipoId])) {
                    $anexosIMap[$tipoId] = [
                        'arquivo' => (string)$anexoI->anexo,
                        'usuario_nome' => trim((string)($anexoI->usuario->nome ?? '')),
                        'data_inclusao' => $anexoI->created ?? null,
                    ];
                }
            }
        }
        $anexosI = [];
        $origemEdital = strtoupper(trim((string)($inscricao->editai->origem ?? '')));
        foreach ($tiposI as $tipoI) {
            $tipoId = (int)$tipoI->id;
            $condicional = (int)($tipoI->condicional ?? 0);
            $programaRegra = trim((string)($tipoI->programa ?? ''));
            $cotaRegra = strtoupper(trim((string)($tipoI->cota ?? '')));
            if (in_array($tipoId, [12, 13], true) && $origemEdital !== 'R') {
                continue;
            }
            if ($condicional !== 0) {
                continue;
            }

            $regras = [];
            $mostrarTipo = false;
            if ($programaRegra === '' && $cotaRegra === '') {
                $regras[] = 'Obrigatório';
                $mostrarTipo = true;
            } elseif ($programaRegra !== '') {
                $programas = array_values(array_filter(array_map('trim', explode(',', $programaRegra))));
                $siglasProgramas = [];
                foreach ($programas as $pid) {
                    if (isset($programasMap[$pid])) {
                        $siglasProgramas[] = $programasMap[$pid];
                    }
                }
                $listaProgramas = !empty($siglasProgramas) ? implode(', ', $siglasProgramas) : implode(', ', $programas);
                if ($listaProgramas !== '') {
                    $regras[] = 'Obrigatório nos programas: ' . $listaProgramas . '.';
                }
                if ($programaEdital !== '' && in_array($programaEdital, $programas, true)) {
                    $regras[] = 'Obrigatório neste edital.';
                    $mostrarTipo = true;
                }
            }

            if (!$mostrarTipo) {
                continue;
            }
            $anexosI[] = [
                'tipo_id' => $tipoId,
                'tipo_nome' => (string)$tipoI->nome,
                'arquivo' => (string)($anexosIMap[$tipoId]['arquivo'] ?? ''),
                'usuario_nome' => (string)($anexosIMap[$tipoId]['usuario_nome'] ?? ''),
                'data_inclusao' => $anexosIMap[$tipoId]['data_inclusao'] ?? null,
                'regras' => $regras,
                'status_regra' => 'Obrigatório',
            ];
        }

        $orientadoraSexo = strtoupper(trim((string)($inscricao->orientadore->sexo ?? '')));
        $filhosMenorOrientadora = (int)($inscricao->filhos_menor ?? 0);
        if ($orientadoraSexo === 'F' && $filhosMenorOrientadora > 0) {
            $tipoCertidaoId = 27;
            $certidaoJaListada = false;
            foreach ($anexosI as $item) {
                if ((int)($item['tipo_id'] ?? 0) === $tipoCertidaoId) {
                    $certidaoJaListada = true;
                    break;
                }
            }
            if (!$certidaoJaListada) {
                $tipoCertidao = $this->fetchTable('AnexosTipos')->find()
                    ->select(['id', 'nome'])
                    ->where(['AnexosTipos.id' => $tipoCertidaoId])
                    ->first();
                $anexoCertidao = $this->fetchTable('Anexos')->find()
                    ->select(['anexo', 'usuario_id', 'created'])
                    ->contain([
                        'Usuarios' => function ($q) {
                            return $q->select(['id', 'nome']);
                        },
                    ])
                    ->where([
                        'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                        'Anexos.anexos_tipo_id' => $tipoCertidaoId,
                        'OR' => [
                            'Anexos.deleted IS' => null,
                            'Anexos.deleted' => 0,
                        ],
                    ])
                    ->orderBy(['Anexos.id' => 'DESC'])
                    ->first();
                $anexosI[] = [
                    'tipo_id' => $tipoCertidaoId,
                    'tipo_nome' => (string)($tipoCertidao->nome ?? 'Certidão de nascimento (filhos orientadora)'),
                    'arquivo' => (string)($anexoCertidao->anexo ?? ''),
                    'usuario_nome' => trim((string)($anexoCertidao->usuario->nome ?? '')),
                    'data_inclusao' => $anexoCertidao->created ?? null,
                    'regras' => ['Obrigatório para orientadora com filhos menores de 4 anos.'],
                    'status_regra' => 'Obrigatório',
                ];
            }
        }
        if ((int)($inscricao->recem_servidor ?? 0) === 1) {
            $tipoServidorId = 29;
            $servidorJaListado = false;
            foreach ($anexosI as $item) {
                if ((int)($item['tipo_id'] ?? 0) === $tipoServidorId) {
                    $servidorJaListado = true;
                    break;
                }
            }
            if (!$servidorJaListado) {
                $tipoServidor = $this->fetchTable('AnexosTipos')->find()
                    ->select(['id', 'nome'])
                    ->where(['AnexosTipos.id' => $tipoServidorId])
                    ->first();
                $anexoServidor = $this->fetchTable('Anexos')->find()
                    ->select(['anexo', 'usuario_id', 'created'])
                    ->contain([
                        'Usuarios' => function ($q) {
                            return $q->select(['id', 'nome']);
                        },
                    ])
                    ->where([
                        'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                        'Anexos.anexos_tipo_id' => $tipoServidorId,
                        'OR' => [
                            'Anexos.deleted IS' => null,
                            'Anexos.deleted' => 0,
                        ],
                    ])
                    ->orderBy(['Anexos.id' => 'DESC'])
                    ->first();
                $anexosI[] = [
                    'tipo_id' => $tipoServidorId,
                    'tipo_nome' => (string)($tipoServidor->nome ?? 'Anexo de ingresso na Fiocruz'),
                    'arquivo' => (string)($anexoServidor->anexo ?? ''),
                    'usuario_nome' => trim((string)($anexoServidor->usuario->nome ?? '')),
                    'data_inclusao' => $anexoServidor->created ?? null,
                    'regras' => ['Obrigatório para ingressos nos concursos de 2016 e 2024.'],
                    'status_regra' => 'Obrigatório',
                ];
            }
        }

        $projetoAtual = $inscricao->projeto ?? null;
        $tiposP = $this->fetchTable('AnexosTipos')->find()
            ->select(['id', 'nome'])
            ->where([
                'AnexosTipos.deleted' => 0,
                'AnexosTipos.bloco' => 'P',
                'AnexosTipos.id' => 5,
            ])
            ->orderBy(['AnexosTipos.id' => 'ASC'])
            ->all();
        $tiposPIds = [];
        foreach ($tiposP as $tipoP) {
            $tiposPIds[] = (int)$tipoP->id;
        }
        $anexosPMap = [];
        if (!empty($inscricao->projeto_id) && !empty($tiposPIds)) {
            $anexosPAtivos = $this->fetchTable('Anexos')->find()
                ->select(['anexos_tipo_id', 'anexo', 'usuario_id', 'created'])
                ->contain([
                    'Usuarios' => function ($q) {
                        return $q->select(['id', 'nome']);
                    },
                ])
                ->where([
                    'Anexos.projeto_id' => (int)$inscricao->projeto_id,
                    'Anexos.anexos_tipo_id IN' => $tiposPIds,
                    'OR' => [
                        'Anexos.deleted IS' => null,
                        'Anexos.deleted' => 0,
                    ],
                ])
                ->orderBy(['Anexos.id' => 'DESC'])
                ->all();
            foreach ($anexosPAtivos as $anexoProjeto) {
                $tipoId = (int)($anexoProjeto->anexos_tipo_id ?? 0);
                if ($tipoId <= 0 || (string)($anexoProjeto->anexo ?? '') === '') {
                    continue;
                }
                if (!isset($anexosPMap[$tipoId])) {
                    $anexosPMap[$tipoId] = [
                        'arquivo' => (string)$anexoProjeto->anexo,
                        'usuario_nome' => trim((string)($anexoProjeto->usuario->nome ?? '')),
                        'data_inclusao' => $anexoProjeto->created ?? null,
                    ];
                }
            }
        }
        $anexosP = [];
        foreach ($tiposP as $tipoP) {
            $tipoId = (int)$tipoP->id;
            $anexosP[] = [
                'tipo_id' => $tipoId,
                'tipo_nome' => (string)$tipoP->nome,
                'arquivo' => (string)($anexosPMap[$tipoId]['arquivo'] ?? ''),
                'usuario_nome' => (string)($anexosPMap[$tipoId]['usuario_nome'] ?? ''),
                'data_inclusao' => $anexosPMap[$tipoId]['data_inclusao'] ?? null,
                'status_regra' => 'Obrigatório',
            ];
        }
        $subprojetoTitulo = trim((string)($inscricao->sp_titulo ?? ''));
        $tiposS = $this->fetchTable('AnexosTipos')->find()
            ->select(['id', 'nome'])
            ->where([
                'AnexosTipos.deleted' => 0,
                'AnexosTipos.bloco' => 'S',
                'AnexosTipos.id' => 20,
            ])
            ->orderBy(['AnexosTipos.id' => 'ASC'])
            ->all();
        $tiposSIds = [];
        foreach ($tiposS as $tipoS) {
            $tiposSIds[] = (int)$tipoS->id;
        }
        $anexosSMap = [];
        if (!empty($tiposSIds)) {
            $anexosSAtivos = $this->fetchTable('Anexos')->find()
                ->select(['anexos_tipo_id', 'anexo', 'usuario_id', 'created'])
                ->contain([
                    'Usuarios' => function ($q) {
                        return $q->select(['id', 'nome']);
                    },
                ])
                ->where([
                    'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                    'Anexos.anexos_tipo_id IN' => $tiposSIds,
                    'OR' => [
                        'Anexos.deleted IS' => null,
                        'Anexos.deleted' => 0,
                    ],
                ])
                ->orderBy(['Anexos.id' => 'DESC'])
                ->all();
            foreach ($anexosSAtivos as $anexoSubprojeto) {
                $tipoId = (int)($anexoSubprojeto->anexos_tipo_id ?? 0);
                if ($tipoId <= 0 || (string)($anexoSubprojeto->anexo ?? '') === '') {
                    continue;
                }
                if (!isset($anexosSMap[$tipoId])) {
                    $anexosSMap[$tipoId] = [
                        'arquivo' => (string)$anexoSubprojeto->anexo,
                        'usuario_nome' => trim((string)($anexoSubprojeto->usuario->nome ?? '')),
                        'data_inclusao' => $anexoSubprojeto->created ?? null,
                    ];
                }
            }
        }
        $anexosS = [];
        foreach ($tiposS as $tipoS) {
            $tipoId = (int)$tipoS->id;
            $anexosS[] = [
                'tipo_id' => $tipoId,
                'tipo_nome' => (string)$tipoS->nome,
                'arquivo' => (string)($anexosSMap[$tipoId]['arquivo'] ?? ''),
                'usuario_nome' => (string)($anexosSMap[$tipoId]['usuario_nome'] ?? ''),
                'data_inclusao' => $anexosSMap[$tipoId]['data_inclusao'] ?? null,
                'status_regra' => 'Obrigatório',
            ];
        }
        $orientadorNome = trim((string)($inscricao->orientadore->nome ?? ''));
        $orientadorVinculoNome = trim((string)($inscricao->orientadore->vinculo->nome ?? ''));
        $orientadorServidor = (int)($inscricao->orientadore->vinculo->servidor ?? 0);
        $coorientadorNome = trim((string)($inscricao->coorientadore->nome ?? ''));
        $coorientadorVinculoNome = trim((string)($inscricao->coorientadore->vinculo->nome ?? ''));
        $coorientadorServidor = (int)($inscricao->coorientadore->vinculo->servidor ?? 0);
        $coorientadorInformado = !empty($inscricao->coorientadore->id);
        $coorientadorObrigatorioServidor = ($orientadorServidor === 0);
        $errosCoorientador = [];
        if ($coorientadorObrigatorioServidor) {
            if (!$coorientadorInformado) {
                $errosCoorientador[] = 'Obrigatório coorientador servidor.';
            } elseif ($coorientadorServidor !== 1) {
                $errosCoorientador[] = 'Coorientador informado com vínculo não servidor.';
            }
        }
        $tiposC = $this->fetchTable('AnexosTipos')->find()
            ->select(['id', 'nome'])
            ->where([
                'AnexosTipos.deleted' => 0,
                'AnexosTipos.bloco' => 'C',
            ])
            ->orderBy(['AnexosTipos.id' => 'ASC'])
            ->all();
        $tiposCIds = [];
        foreach ($tiposC as $tipoC) {
            $tiposCIds[] = (int)$tipoC->id;
        }
        $anexosC = [];
        if ($coorientadorInformado && !empty($tiposCIds)) {
            $anexosCMap = [];
            $anexosCAtivos = $this->fetchTable('Anexos')->find()
                ->select(['anexos_tipo_id', 'anexo', 'usuario_id', 'created'])
                ->contain([
                    'Usuarios' => function ($q) {
                        return $q->select(['id', 'nome']);
                    },
                ])
                ->where([
                    'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                    'Anexos.anexos_tipo_id IN' => $tiposCIds,
                    'OR' => [
                        'Anexos.deleted IS' => null,
                        'Anexos.deleted' => 0,
                    ],
                ])
                ->orderBy(['Anexos.id' => 'DESC'])
                ->all();
            foreach ($anexosCAtivos as $anexoC) {
                $tipoId = (int)($anexoC->anexos_tipo_id ?? 0);
                if ($tipoId <= 0 || (string)($anexoC->anexo ?? '') === '') {
                    continue;
                }
                if (!isset($anexosCMap[$tipoId])) {
                    $anexosCMap[$tipoId] = [
                        'arquivo' => (string)$anexoC->anexo,
                        'usuario_nome' => trim((string)($anexoC->usuario->nome ?? '')),
                        'data_inclusao' => $anexoC->created ?? null,
                    ];
                }
            }
            foreach ($tiposC as $tipoC) {
                $tipoId = (int)$tipoC->id;
                $anexosC[] = [
                    'tipo_id' => $tipoId,
                    'tipo_nome' => (string)$tipoC->nome,
                    'arquivo' => (string)($anexosCMap[$tipoId]['arquivo'] ?? ''),
                    'usuario_nome' => (string)($anexosCMap[$tipoId]['usuario_nome'] ?? ''),
                    'data_inclusao' => $anexosCMap[$tipoId]['data_inclusao'] ?? null,
                    'status_regra' => 'Obrigatório',
                ];
                if ((string)($anexosCMap[$tipoId]['arquivo'] ?? '') === '') {
                    $errosCoorientador[] = 'Anexo obrigatório do coorientador pendente: ' . (string)$tipoC->nome . '.';
                }
            }
        }

        $primeiroPeriodoTexto = $inscricao->primeiro_periodo === null
            ? 'Não informado'
            : ((int)$inscricao->primeiro_periodo === 1 ? 'Sim' : 'Não');
        $cotas = $this->cota;

        $this->set(compact(
            'inscricao',
            'anexosB',
            'anexosI',
            'anexosP',
            'anexosS',
            'anexosC',
            'projetoAtual',
            'subprojetoTitulo',
            'primeiroPeriodoTexto',
            'cotas',
            'orientadorNome',
            'orientadorVinculoNome',
            'orientadorServidor',
            'coorientadorNome',
            'coorientadorVinculoNome',
            'coorientadorServidor',
            'coorientadorInformado',
            'coorientadorObrigatorioServidor',
            'errosCoorientador',
            'motivoNaoHomologacao',
            'homologacaoPermitida',
            'exigeConfirmacaoReavaliacao'
        ));
    }

    public function confirmacao($id)
    {
        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $bolsista = $tblProjetoBolsistas->find()
            ->contain([
                'Bolsistas',
                'Orientadores' => ['Unidades'],
                'Substitutos' => ['Bolsistas'],
            ])
            ->where(['ProjetoBolsistas.id' => (int)$id])
            ->first();

        if (!$bolsista) {
            $this->Flash->error('Inscricao não localizada para confirmação.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $faseAtual = (int)$bolsista->fase_id;
        if ($faseAtual === 15) {
            $modo = 'S';
        } elseif (in_array($faseAtual, [12, 21], true)) {
            $modo = 'C';
        } else {
            $this->Flash->error('Fase invalida para confirmação.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $original = $faseAtual;

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();

            if ($modo === 'S') {
                $bolsistaSaindo = null;
                $faseSaindoOriginal = null;
                if (!empty($bolsista->bolsista_anterior)) {
                    $bolsistaSaindo = $tblProjetoBolsistas->get((int)$bolsista->bolsista_anterior);
                    $faseSaindoOriginal = (int)$bolsistaSaindo->fase_id;
                }

                $dataInicio = trim((string)($dados['data_inicio'] ?? ''));
                if ($dataInicio !== '') {
                    $bolsista->data_inicio = $this->acertaData($dataInicio);
                } else {
                    $bolsista->data_inicio = null;
                }

                $bolsista->fase_id = 11;
                $bolsista->vigente = 1;
                $bolsista->data_primeira = $bolsista->data_inicio;

                if (!empty($dados['tipo_bolsa'])) {
                    $bolsista->tipo_bolsa = $dados['tipo_bolsa'];
                }

                if ($bolsistaSaindo) {
                    if (empty($bolsistaSaindo->data_fim)) {
                        if ((string)($bolsista->origem ?? '') === 'A') {
                            $bolsistaSaindo->data_fim = $bolsistaSaindo->data_inicio;
                        } elseif (!empty($bolsista->data_inicio)) {
                            $bolsistaSaindo->data_fim = date('Y-m-d', strtotime($bolsista->data_inicio . ' -1 day'));
                        } else {
                            $bolsistaSaindo->data_fim = date('Y-m-d');
                        }
                    }

                    $bolsistaSaindo->vigente = 0;
                    $bolsistaSaindo->fase_id = 14;
                    $bolsistaSaindo->substituicao_confirmador = (int)$this->request->getAttribute('identity')['id'];
                    $bolsistaSaindo->data_sub_confirmacao = date('Y-m-d H:i:s');
                }

                try {
                    $tblProjetoBolsistas->getConnection()->transactional(function () use ($tblProjetoBolsistas, $bolsista, $bolsistaSaindo, $original, $faseSaindoOriginal) {
                        $tblProjetoBolsistas->saveOrFail($bolsista);
                        $this->historico($bolsista->id, $original, 11, 'Confirmação da Substituição', true);

                        if ($bolsistaSaindo) {
                            $tblProjetoBolsistas->saveOrFail($bolsistaSaindo);
                            $this->historico($bolsistaSaindo->id, $faseSaindoOriginal, 14, 'Confirmação da Substituição. Bolsa desativada', true);
                        }
                    });

                    $this->Flash->success('Substituição confirmada com sucesso!');
                } catch (\Throwable $e) {
                    $this->flashFriendlyException(
                        $e,
                        'Erro no Sistema - confirmacao de substituicao',
                        'Não foi possível realizar a confirmação, por favor tente novamente!'
                    );
                    return $this->redirect(['controller' => 'Gestao', 'action' => 'confirmacao', $bolsista->id]);
                }

                return $this->redirect(['controller' => 'Gestao', 'action' => 'listarconfirmacoes', 'S']);
            }

            if ($modo === 'C') {
                $novaFase = $faseAtual === 12 ? 13 : 22;
                $dataFim = trim((string)($dados['data_fim'] ?? ''));
                if ($dataFim !== '') {
                    $bolsista->data_fim = $this->acertaData($dataFim);
                } else {
                    $bolsista->data_fim = date('Y-m-d H:i:s');
                }

                $bolsista->fase_id = $novaFase;
                $bolsista->vigente = 0;
                $bolsista->data_cancela_confirmacao = date('Y-m-d H:i:s');
                $bolsista->cancelamento_confirmador = (int)$this->request->getAttribute('identity')['id'];

                if ($tblProjetoBolsistas->save($bolsista)) {
                    $this->Flash->success('Cancelamento confirmado com sucesso');
                    $this->historico($bolsista->id, $original, $novaFase, 'Processo administrativo', true);
                } else {
                    $this->Flash->error('Não foi possível confirmar o cancelamento');
                }

                return $this->redirect(['controller' => 'Gestao', 'action' => 'listarconfirmacoes', 'C']);
            }
        }

        $this->set(compact('bolsista', 'modo'));
    }

    private function parseInscricaoIdsLote(string $raw): array
    {
        $partes = preg_split('/[\s,;]+/', trim($raw)) ?: [];
        $ids = [];
        foreach ($partes as $parte) {
            $parte = trim($parte);
            if ($parte === '' || !ctype_digit($parte)) {
                continue;
            }
            $id = (int)$parte;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    private function avaliarLoteResultados(array $ids, string $resultado, array $resultadoMap): array
    {
        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $rows = $tblProjetoBolsistas->find()
            ->select(['id', 'fase_id', 'resultado', 'origem', 'deleted'])
            ->where(['ProjetoBolsistas.id IN' => $ids])
            ->all();

        $porId = [];
        foreach ($rows as $row) {
            $porId[(int)$row->id] = $row;
        }

        $aptas = [];
        $recusadas = [];
        foreach ($ids as $id) {
            $item = $porId[(int)$id] ?? null;
            if (!$item) {
                $recusadas[] = ['id' => $id, 'motivo' => 'Inscrição não localizada.'];
                continue;
            }

            if ($item->deleted !== null) {
                $recusadas[] = ['id' => $id, 'motivo' => 'Inscrição deletada/inativa.'];
                continue;
            }

            if ($item->resultado !== null && $item->resultado !== '') {
                $recusadas[] = ['id' => $id, 'motivo' => 'Inscrição já possui resultado lançado.'];
                continue;
            }

            if ((int)$item->fase_id !== 6) {
                $recusadas[] = ['id' => $id, 'motivo' => 'Inscrição não homologada.'];
                continue;
            }

            if (!in_array((string)$item->origem, ['N', 'R'], true)) {
                $recusadas[] = ['id' => $id, 'motivo' => 'Origem inválida para lançamento de resultado.'];
                continue;
            }

            $aptas[] = [
                'id' => (int)$item->id,
                'fase_atual' => (int)$item->fase_id,
                'fase_destino' => (int)$resultadoMap[$resultado],
                'origem' => (string)$item->origem,
            ];
        }

        return [
            'ids_informados' => $ids,
            'aptas' => $aptas,
            'recusadas' => $recusadas,
        ];
    }

    private function avaliarLoteVigencias(int $editalId, string $modo): array
    {
        $edital = $this->fetchTable('Editais')->find()
            ->select(['id', 'nome', 'inicio_vigencia', 'fim_vigencia'])
            ->where(['Editais.id' => $editalId])
            ->first();

        $aptas = [];
        $recusadas = [];
        if (!$edital) {
            return compact('edital', 'aptas', 'recusadas');
        }

        $rows = $this->fetchTable('ProjetoBolsistas')->find()
            ->select(['id', 'fase_id', 'resultado', 'vigente', 'deleted'])
            ->where([
                'ProjetoBolsistas.editai_id' => $editalId,
                'ProjetoBolsistas.deleted IS' => null,
            ])
            ->orderBy(['ProjetoBolsistas.id' => 'ASC'])
            ->all();

        foreach ($rows as $item) {
            $id = (int)$item->id;

            if ($modo === 'A') {
                if (!in_array((string)$item->resultado, ['A', 'T'], true)) {
                    $recusadas[] = ['id' => $id, 'motivo' => 'Resultado diferente de Aprovado / Aprovação Automática.'];
                    continue;
                }
                if ((int)$item->vigente === 1) {
                    $recusadas[] = ['id' => $id, 'motivo' => 'Bolsa já está vigente.'];
                    continue;
                }

                $aptas[] = [
                    'id' => $id,
                    'fase_atual' => (int)$item->fase_id,
                    'fase_destino' => 11,
                ];
                continue;
            }

            if (!in_array((int)$item->fase_id, [11, 18, 19], true)) {
                $recusadas[] = ['id' => $id, 'motivo' => 'Fase fora do escopo de encerramento.'];
                continue;
            }
            if ((int)$item->vigente !== 1) {
                $recusadas[] = ['id' => $id, 'motivo' => 'Bolsa não está vigente.'];
                continue;
            }

            $aptas[] = [
                'id' => $id,
                'fase_atual' => (int)$item->fase_id,
                'fase_destino' => 17,
            ];
        }

        return compact('edital', 'aptas', 'recusadas');
    }
}
