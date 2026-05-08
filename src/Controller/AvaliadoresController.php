<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\AvaliadorBolsista;
use Cake\Event\EventInterface;

class AvaliadoresController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setLayout('admin');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

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

        $action = (string)$this->request->getParam('action');
        if (!$this->ehYoda() && $jedi === '' && !in_array($action, ['avaliacoes', 'avaliar', 'verNotas'], true)) {
            $this->Flash->error('Área restrita à Coordenação da Unidade e à Gestão de Fomento.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }

        return null;
    }

    public function avaliacoes()
    {
        $this->request->allowMethod(['get']);

        $identity = $this->getIdentityAtual();
        $usuarioId = is_array($identity) ? (int)($identity['id'] ?? 0) : (int)($identity->id ?? 0);
        if ($usuarioId <= 0) {
            $this->Flash->error('Não foi possível identificar o usuário logado.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }

        $anoCorrente = (int)date('Y');
        $filtros = [
            'ano' => (string)$this->request->getQuery('ano', ''),
            'tipo' => (string)$this->request->getQuery('tipo', ''),
            'situacao' => (string)$this->request->getQuery('situacao', ''),
        ];

        $avaliadorBolsistasTable = $this->fetchTable('AvaliadorBolsistas');
        $contain = [
            'Avaliadors',
            'Editais',
            'ProjetoBolsistas' => [
                'Editais',
                'Bolsistas',
                'Orientadores' => ['Unidades'],
            ],
            'Raics' => [
                'Editais',
                'Unidades',
                'Usuarios',
                'Orientadores' => ['Unidades'],
            ],
            'PdjInscricoes' => [
                'Editais',
                'Bolsistas',
                'Orientadores' => ['Unidades'],
            ],
            'Workshops' => [
                'Editais',
                'Unidades',
                'Usuarios',
                'Orientadores' => ['Unidades'],
                'PdjInscricoes' => [
                    'Bolsistas',
                    'Orientadores' => ['Unidades'],
                ],
            ],
        ];

        $baseQuery = function () use ($avaliadorBolsistasTable, $contain, $usuarioId) {
            return $avaliadorBolsistasTable->find()
                ->contain($contain)
                ->matching('Avaliadors', function ($q) use ($usuarioId) {
                    return $q->where(['Avaliadors.usuario_id' => $usuarioId]);
                })
                ->where(['AvaliadorBolsistas.deleted' => 0]);
        };

        $avaliacoesAnoCorrente = $baseQuery()
            ->where(['AvaliadorBolsistas.ano' => (string)$anoCorrente])
            ->orderBy([
                'AvaliadorBolsistas.situacao' => 'ASC',
                'AvaliadorBolsistas.created' => 'DESC',
                'AvaliadorBolsistas.id' => 'DESC',
            ])
            ->all();

        $query = $baseQuery()
            ->where([
                'OR' => [
                    'AvaliadorBolsistas.ano IS' => null,
                    'AvaliadorBolsistas.ano !=' => (string)$anoCorrente,
                ],
            ]);

        if ($filtros['ano'] !== '') {
            $query->where(['AvaliadorBolsistas.ano' => $filtros['ano']]);
        }
        if ($filtros['tipo'] !== '') {
            $query->where(['AvaliadorBolsistas.tipo' => $filtros['tipo']]);
        }
        if ($filtros['situacao'] === 'F') {
            $query->where(['AvaliadorBolsistas.situacao' => 'F']);
        } elseif ($filtros['situacao'] === 'P') {
            $query->where([
                'OR' => [
                    'AvaliadorBolsistas.situacao IS' => null,
                    'AvaliadorBolsistas.situacao !=' => 'F',
                ],
            ]);
        }

        $anosOptions = $avaliadorBolsistasTable->find()
            ->select(['AvaliadorBolsistas.ano'])
            ->matching('Avaliadors', function ($q) use ($usuarioId) {
                return $q->where(['Avaliadors.usuario_id' => $usuarioId]);
            })
            ->where(['AvaliadorBolsistas.deleted' => 0])
            ->where([
                'AvaliadorBolsistas.ano IS NOT' => null,
                'AvaliadorBolsistas.ano !=' => (string)$anoCorrente,
            ])
            ->distinct(['AvaliadorBolsistas.ano'])
            ->orderBy(['AvaliadorBolsistas.ano' => 'DESC'])
            ->all()
            ->combine('ano', 'ano')
            ->toArray();

        $query->orderBy([
            'AvaliadorBolsistas.ano' => 'DESC',
            'AvaliadorBolsistas.created' => 'DESC',
            'AvaliadorBolsistas.id' => 'DESC',
        ]);

        $avaliacoes = $this->paginate($query, ['limit' => 20]);
        $tipoMap = [
            'N' => 'Inscrições',
            'V' => 'RAIC (aluno de renovação)',
            'Z' => 'RAIC de outras agências',
            'J' => 'PDJ Nova',
            'W' => 'Workshop',
        ];
        $situacaoMap = [
            'P' => 'Pendente',
            'F' => 'Finalizada',
        ];

        $this->set(compact(
            'avaliacoes',
            'avaliacoesAnoCorrente',
            'anoCorrente',
            'anosOptions',
            'filtros',
            'tipoMap',
            'situacaoMap'
        ));
    }

    public function avaliar(int $id)
    {
        $this->request->allowMethod(['get', 'post']);

        $identity = $this->getIdentityAtual();
        $usuarioId = is_array($identity) ? (int)($identity['id'] ?? 0) : (int)($identity->id ?? 0);
        if ($usuarioId <= 0) {
            $this->Flash->error('Não foi possível identificar o usuário logado.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }

        $avaliadorBolsistasTable = $this->fetchTable('AvaliadorBolsistas');
        $avaliacao = $avaliadorBolsistasTable->find()
            ->contain([
                'Avaliadors' => ['Usuarios'],
                'Editais',
                'ProjetoBolsistas' => [
                    'Editais',
                    'Bolsistas',
                    'Orientadores' => ['Unidades'],
                    'Projetos',
                ],
                'Raics' => [
                    'Editais',
                    'Unidades',
                    'Usuarios',
                    'Orientadores',
                    'ProjetoBolsistas' => ['Projetos'],
                ],
                'PdjInscricoes' => [
                    'Editais',
                    'Bolsistas',
                    'Orientadores' => ['Unidades'],
                    'Projetos',
                ],
                'Workshops' => [
                    'Editais',
                    'Unidades',
                    'Usuarios',
                    'Orientadores',
                    'PdjInscricoes' => [
                        'Bolsistas',
                        'Orientadores' => ['Unidades'],
                        'Projetos',
                    ],
                ],
            ])
            ->where(['AvaliadorBolsistas.id' => $id])
            ->first();

        if (!$avaliacao) {
            $this->Flash->error('Avaliação não encontrada.');
            return $this->redirect(['action' => 'avaliacoes']);
        }
        $this->carregarReferenciaAvaliacao($avaliacao);

        $ti = in_array($usuarioId, [1, 8088], true);
        $avaliadorUsuarioId = (int)($avaliacao->avaliador->usuario_id ?? 0);

        if (!$ti && $avaliadorUsuarioId !== $usuarioId) {
            $this->Flash->error('Você não tem permissão para acessar esta avaliação.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }

        if ((int)($avaliacao->deleted ?? 0) !== 0 || (int)($avaliacao->avaliador->deleted ?? 0) !== 0) {
            $this->Flash->error('Registro deletado.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $situacao = (string)($avaliacao->situacao ?? '');
        if ($situacao === 'F') {
            if ($ti) {
                return $this->redirect(['action' => 'verNotas', $avaliacao->id]);
            }

            $this->Flash->error('Você já avaliou esse registro.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        if ($situacao === 'E') {
            if (!$ti) {
                $identityPeriodo = is_array($identity) ? (object)$identity : $identity;
                if (
                    empty($avaliacao->editai)
                    || !$this->loadPeriodo(
                        $avaliacao->editai,
                        $identityPeriodo,
                        8,
                        [$usuarioId],
                        [(int)($avaliacao->bolsista ?? 0)]
                    )
                ) {
                    $this->Flash->error('Fora do período de avaliação.');
                    return $this->redirect(['controller' => 'Index', 'action' => 'index']);
                }
                if (in_array((string)$avaliacao->tipo, ['V', 'Z'], true)) {
                    $dataAgendada = $avaliacao->raic->data_apresentacao ?? null;
                    $dataAgendadaDia = null;
                    if ($dataAgendada instanceof \DateTimeInterface) {
                        $dataAgendadaDia = \Cake\I18n\FrozenDate::parse($dataAgendada->format('Y-m-d'));
                    } elseif (!empty($dataAgendada)) {
                        $dataAgendadaDia = \Cake\I18n\FrozenDate::parse((string)$dataAgendada);
                    }

                    if (
                        empty($avaliacao->raic)
                        || empty($dataAgendadaDia)
                        || $dataAgendadaDia->greaterThan(\Cake\I18n\FrozenDate::today())
                    ) {
                        $raicId = (int)($avaliacao->raic->id ?? $avaliacao->bolsista ?? 0);
                        $dataAgendadaTexto = $dataAgendadaDia
                            ? $dataAgendadaDia->i18nFormat('dd/MM/yyyy')
                            : 'não informada';
                        $this->Flash->error(
                            sprintf(
                                'A avaliação da RAIC #%d estará disponível a partir da data agendada: %s.',
                                $raicId,
                                $dataAgendadaTexto
                            )
                        );
                        return $this->redirect(['controller' => 'Index', 'action' => 'index']);
                    }
                }
            }
        } else {
            $this->Flash->error('Esta avaliação não está liberada para lançamento.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $questoes = $this->fetchTable('Questions')->find()
            ->where([
                'Questions.editai_id' => (int)$avaliacao->editai_id,
                //'Questions.tipo' => (string)$avaliacao->tipo,
                'OR' => [
                    'Questions.deleted IS' => null,
                    'Questions.deleted' => 0,
                ],
            ])
            ->orderBy(['Questions.id' => 'ASC'])
            ->all();
        $questoesLista = $questoes->toList();

        if (count($questoesLista) === 0) {
            $this->Flash->error('Não há quesitos cadastrados para o edital desta avaliação.');
            return $this->redirect(['action' => 'avaliacoes']);
        }

        $avaliarSumulas = $this->deveAvaliarSumulas($avaliacao);
        $sumulasAvaliacao = $avaliarSumulas ? $this->carregarSumulasAvaliacao($avaliacao) : [];
        if ($this->request->is('post') && (string)$this->request->getData('_voltar_lancamento', '') !== '1') {
            $erros = $this->validarLancamentoAvaliacao($questoesLista, (array)$this->request->getData('q', []));
            if ($avaliarSumulas) {
                $erros = array_merge(
                    $erros,
                    $this->validarLancamentoSumulas($sumulasAvaliacao, (array)$this->request->getData('sumula', []))
                );
            }
            if (trim((string)$this->request->getData('observacao_avaliador', '')) === '') {
                $erros[] = 'Informe as observações dos quesitos.';
            }
            if ($avaliarSumulas && $sumulasAvaliacao !== [] && trim((string)$this->request->getData('observacao_sumulas', '')) === '') {
                $erros[] = 'Informe as observações da súmula.';
            }
            if ((string)$this->request->getData('parecer', '') === '') {
                $erros[] = 'Informe a situação do parecer do Comitê de Ética.';
            }
            if (in_array((string)($avaliacao->editai->origem ?? ''), ['R', 'V'], true)) {
                if ((string)$this->request->getData('destaque', '') === '') {
                    $erros[] = 'Informe se o aluno se destacou.';
                }
                if ((string)$this->request->getData('indicado_premio_capes', '') === '') {
                    $erros[] = 'Informe se há indicação ao Prêmio Destaque CNPq.';
                }
            }

            if ($erros !== []) {
                $this->Flash->error(implode('<br>', $erros), ['escape' => false]);
            } elseif ((string)$this->request->getData('confirmar_lancamento', '') !== '1') {
                $dadosLancamento = $this->request->getData();
                $tipoMap = [
                    'N' => 'Inscrições',
                    'V' => 'RAIC (aluno de renovação)',
                    'Z' => 'RAIC de outras agências',
                    'J' => 'PDJ Nova',
                    'W' => 'Workshop',
                ];

                $this->set(compact('avaliacao', 'questoesLista', 'tipoMap', 'avaliarSumulas', 'sumulasAvaliacao', 'dadosLancamento'));
                return $this->render('confirmar_avaliacao');
            } else {
                try {
                    $this->salvarLancamentoAvaliacao($avaliacao, $questoesLista, $sumulasAvaliacao);
                    $this->Flash->success('Avaliação finalizada com sucesso.');
                    return $this->redirect(['action' => 'avaliacoes']);
                } catch (\Throwable $e) {
                    $this->flashFriendlyException(
                        $e,
                        'Erro no Sistema - confirmar lançamento de avaliação',
                        'Não foi possível gravar a avaliação. Os dados não foram finalizados; revise as informações e tente novamente.'
                    );
                }
            }
        }

        $tipoMap = [
            'N' => 'Inscrições',
            'V' => 'RAIC (aluno de renovação)',
            'Z' => 'RAIC de outras agências',
            'J' => 'PDJ Nova',
            'W' => 'Workshop',
        ];
        $questoes = $questoesLista;

        $this->set(compact('avaliacao', 'questoes', 'tipoMap', 'avaliarSumulas', 'sumulasAvaliacao'));
    }

    public function verNotas(int $id)
    {
        $this->request->allowMethod(['get']);

        $avaliacao = $this->fetchTable('AvaliadorBolsistas')->find()
            ->contain([
                'Avaliadors' => ['Usuarios'],
                'Editais',
                'Avaliations' => [
                    'Questions',
                    'sort' => ['Avaliations.id' => 'ASC'],
                ],
                'ProjetoBolsistas' => [
                    'Editais',
                    'Bolsistas',
                    'Orientadores' => ['Unidades'],
                    'Coorientadores',
                ],
                'Raics' => [
                    'Editais',
                    'Unidades',
                    'Usuarios',
                    'Orientadores',
                ],
                'PdjInscricoes' => [
                    'Editais',
                    'Bolsistas',
                    'Orientadores' => ['Unidades'],
                ],
                'Workshops' => [
                    'Editais',
                    'Unidades',
                    'Usuarios',
                    'Orientadores',
                    'PdjInscricoes' => [
                        'Bolsistas',
                        'Orientadores' => ['Unidades'],
                    ],
                ],
            ])
            ->where(['AvaliadorBolsistas.id' => $id])
            ->first();

        if (!$avaliacao) {
            $this->Flash->error('Avaliação não encontrada.');
            return $this->redirect(['action' => 'avaliacoes']);
        }
        $this->carregarReferenciaAvaliacao($avaliacao);

        if (!$this->podeVerNotasAvaliacao($avaliacao)) {
            $this->Flash->error('Sem acesso às notas desta avaliação.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $notas = $this->fetchTable('Avaliations')->find()
            ->contain(['Questions'])
            ->where([
                'Avaliations.avaliador_bolsista_id' => (int)$avaliacao->id,
                'Avaliations.deleted' => 0,
            ])
            ->orderBy(['Avaliations.id' => 'ASC'])
            ->all();

        $notasSumulas = $this->fetchTable('AvaliationsSumulas')->find()
            ->contain([
                'EditaisSumulas' => ['EditaisSumulasBlocos'],
                'EditaisSumulasBlocos',
                'InscricaoSumulas',
            ])
            ->where([
                'AvaliationsSumulas.avaliador_bolsista_id' => (int)$avaliacao->id,
                'AvaliationsSumulas.deleted' => 0,
            ])
            ->orderBy([
                'AvaliationsSumulas.editais_sumula_bloco_id' => 'ASC',
                'AvaliationsSumulas.id' => 'ASC',
            ])
            ->all();

        $tipoMap = [
            'N' => 'Inscrições',
            'V' => 'RAIC (aluno de renovação)',
            'Z' => 'RAIC de outras agências',
            'J' => 'PDJ Nova',
            'W' => 'Workshop',
        ];

        $this->set(compact('avaliacao', 'notas', 'notasSumulas', 'tipoMap'));
    }

    public function deletarNotas(int $id)
    {
        $this->request->allowMethod(['post']);

        if (!$this->ehTi()) {
            $this->Flash->error('Sem permissão para excluir notas.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $chamado = trim((string)$this->request->getData('chamado', ''));
        if ($chamado === '') {
            $this->Flash->error('Informe o número do chamado para registrar a exclusão das notas.');
            return $this->redirect(['action' => 'verNotas', $id]);
        }

        $avaliadorBolsistasTable = $this->fetchTable('AvaliadorBolsistas');
        $avaliationsTable = $this->fetchTable('Avaliations');
        $avaliationsSumulasTable = $this->fetchTable('AvaliationsSumulas');

        $avaliacao = $avaliadorBolsistasTable->find()
            ->contain([
                'Avaliadors' => ['Usuarios'],
                'ProjetoBolsistas',
                'Raics',
                'Workshops',
            ])
            ->where(['AvaliadorBolsistas.id' => $id])
            ->first();

        if (!$avaliacao) {
            $this->Flash->error('Avaliação não encontrada.');
            return $this->redirect(['action' => 'avaliacoes']);
        }

        try {
            $avaliadorBolsistasTable->getConnection()->transactional(function () use (
                $avaliacao,
                $avaliadorBolsistasTable,
                $avaliationsTable,
                $avaliationsSumulasTable,
                $chamado
            ): void {
                $avaliationsTable->updateAll(
                    ['deleted' => 1],
                    [
                        'avaliador_bolsista_id' => (int)$avaliacao->id,
                        'OR' => [
                            'deleted IS' => null,
                            'deleted' => 0,
                        ],
                    ]
                );

                $avaliationsSumulasTable->updateAll(
                    ['deleted' => 1],
                    [
                        'avaliador_bolsista_id' => (int)$avaliacao->id,
                        'OR' => [
                            'deleted IS' => null,
                            'deleted' => 0,
                        ],
                    ]
                );

                $avaliacao->situacao = 'E';
                $avaliacao->nota = null;
                $avaliacao->nota_sumula = null;
                $avaliadorBolsistasTable->saveOrFail($avaliacao);

                $this->registrarHistoricoExclusaoNotas($avaliacao, $chamado);
            });

            $this->Flash->success('Notas excluídas com sucesso. A avaliação voltou para aguardando notas.');
            return $this->redirect($this->urlDetalhesReferenciaAvaliacao($avaliacao));
        } catch (\Throwable $e) {
            $this->flashFriendlyException(
                $e,
                'Erro no Sistema - excluir notas de avaliação',
                'Não foi possível excluir as notas da avaliação.'
            );
            return $this->redirect(['action' => 'verNotas', $id]);
        }
    }

    protected function urlDetalhesReferenciaAvaliacao(AvaliadorBolsista $avaliacao): array
    {
        $tipo = (string)($avaliacao->tipo ?? '');

        if ($tipo === 'N') {
            $inscricaoId = (int)($avaliacao->projeto_bolsista_id ?? $avaliacao->bolsista ?? 0);
            if ($inscricaoId > 0) {
                return ['controller' => 'Padrao', 'action' => 'visualizar', $inscricaoId];
            }
        }

        if (in_array($tipo, ['V', 'Z'], true)) {
            $raicId = (int)($avaliacao->raic_id ?? $avaliacao->bolsista ?? 0);
            if ($raicId > 0) {
                return ['controller' => 'RaicNew', 'action' => 'ver', $raicId];
            }
        }

        return ['controller' => 'Avaliadores', 'action' => 'avaliacoes'];
    }

    protected function registrarHistoricoExclusaoNotas(AvaliadorBolsista $avaliacao, string $chamado): void
    {
        $tipo = (string)($avaliacao->tipo ?? '');
        $avaliadorNome = trim((string)($avaliacao->avaliador->usuario->nome ?? 'Não informado'));
        $identity = $this->getIdentityAtual();
        $usuarioId = is_array($identity) ? (int)($identity['id'] ?? 0) : (int)($identity->id ?? 0);
        $alteracao = 'Exclusão de notas de avaliação';
        $justificativa = sprintf(
            'Solicitada exclusão das notas do avaliador %s (avaliador_bolsista #%d). Chamado: %s',
            $avaliadorNome,
            (int)$avaliacao->id,
            $chamado
        );

        if ($tipo === 'N') {
            $inscricaoId = (int)($avaliacao->projeto_bolsista_id ?? $avaliacao->bolsista ?? 0);
            if ($inscricaoId <= 0) {
                return;
            }

            $inscricao = $this->fetchTable('ProjetoBolsistas')->get($inscricaoId);
            $faseAtual = (int)($inscricao->fase_id ?? 0);
            $historicoSituacao = function_exists('mb_substr')
                ? mb_substr($justificativa, 0, 255)
                : substr($justificativa, 0, 255);
            $this->historico($inscricaoId, $faseAtual, $faseAtual, $historicoSituacao, true);

            return;
        }

        if (in_array($tipo, ['V', 'Z'], true)) {
            $raicId = (int)($avaliacao->raic_id ?? $avaliacao->bolsista ?? 0);
            if ($raicId <= 0) {
                return;
            }

            $raicHistoricos = $this->fetchTable('RaicHistoricos');
            $historico = $raicHistoricos->newEntity([
                'raic_id' => $raicId,
                'usuario_id' => $usuarioId,
                'alteracao' => $alteracao,
                'justificativa' => $justificativa,
            ]);
            $raicHistoricos->saveOrFail($historico);

            return;
        }

        if ($tipo === 'W') {
            $workshopId = (int)($avaliacao->workshop_id ?? $avaliacao->bolsista ?? 0);
            if ($workshopId <= 0) {
                return;
            }

            $workshopHistoricos = $this->fetchTable('WorkshopHistoricos');
            $historico = $workshopHistoricos->newEntity([
                'workshop_id' => $workshopId,
                'usuario_id' => $usuarioId,
                'alteracao' => $alteracao,
                'justificativa' => $justificativa,
            ]);
            $workshopHistoricos->saveOrFail($historico);
        }
    }

    protected function podeVerNotasAvaliacao(AvaliadorBolsista $avaliacao): bool
    {
        if ($this->ehYoda() || $this->ehTi()) {
            return true;
        }

        $identity = $this->getIdentityAtual();
        $usuarioId = is_array($identity) ? (int)($identity['id'] ?? 0) : (int)($identity->id ?? 0);
        if ($usuarioId <= 0) {
            return false;
        }

        $tipo = (string)($avaliacao->tipo ?? '');
        if ($tipo === 'N' && !empty($avaliacao->projeto_bolsista)) {
            $inscricao = $avaliacao->projeto_bolsista;
            $jedi = is_array($identity) ? (string)($identity['jedi'] ?? '') : (string)($identity->jedi ?? '');
            $padauan = is_array($identity) ? (string)($identity['padauan'] ?? '') : (string)($identity->padauan ?? '');
            $jediPermitidas = array_values(array_filter(array_map('trim', explode(',', $jedi))));
            $unidadeOrientador = (string)($inscricao->orientadore->unidade_id ?? $inscricao->orientadore->unidade?->id ?? '');
            $ehJediPermitido = !empty($jediPermitidas)
                && $unidadeOrientador !== ''
                && in_array($unidadeOrientador, $jediPermitidas, true);

            $padauanPermitidos = array_values(array_filter(array_map('trim', explode(',', $padauan))));
            $programaInscricao = (string)((int)($inscricao->programa_id ?? $inscricao->editai->programa_id ?? 0));
            $ehPadauanPermitido = !empty($padauanPermitidos)
                && $programaInscricao !== '0'
                && in_array($programaInscricao, $padauanPermitidos, true);

            return in_array($usuarioId, [
                (int)($inscricao->orientador ?? 0),
                (int)($inscricao->coorientador ?? 0),
                (int)($inscricao->bolsista ?? 0),
            ], true) || $ehJediPermitido || $ehPadauanPermitido;
        }
        if (in_array($tipo, ['V', 'Z'], true) && !empty($avaliacao->raic)) {
            return in_array($usuarioId, [
                (int)($avaliacao->raic->orientador ?? 0),
                (int)($avaliacao->raic->usuario_id ?? 0),
            ], true);
        }
        if ($tipo === 'J' && !empty($avaliacao->pdj_inscrico)) {
            return in_array($usuarioId, [
                (int)($avaliacao->pdj_inscrico->usuario_id ?? 0),
                (int)($avaliacao->pdj_inscrico->bolsista ?? 0),
            ], true);
        }
        if ($tipo === 'W' && !empty($avaliacao->workshop)) {
            return in_array($usuarioId, [
                (int)($avaliacao->workshop->orientador ?? 0),
                (int)($avaliacao->workshop->usuario_id ?? 0),
            ], true);
        }

        return false;
    }

    protected function validarLancamentoAvaliacao(array $questoes, array $notas): array
    {
        $erros = [];
        foreach ($questoes as $questao) {
            $questaoId = (int)$questao->id;
            if (!array_key_exists((string)$questaoId, $notas) && !array_key_exists($questaoId, $notas)) {
                $erros[] = 'Informe a nota de todos os quesitos.';
                break;
            }

            $valor = $notas[$questaoId] ?? $notas[(string)$questaoId] ?? null;
            $valor = str_replace(',', '.', trim((string)$valor));
            if ($valor === '' || !is_numeric($valor)) {
                $erros[] = 'Há nota inválida nos quesitos.';
                break;
            }

            $nota = (float)$valor;
            $minimo = (float)($questao->limite_min ?? 0);
            $maximo = (float)($questao->limite_max ?? 0);
            if ($nota < $minimo || $nota > $maximo) {
                $erros[] = sprintf(
                    'A nota do quesito "%s" deve estar entre %s e %s.',
                    (string)$questao->questao,
                    (string)$questao->limite_min,
                    (string)$questao->limite_max
                );
            }
        }

        return $erros;
    }

    protected function validarLancamentoSumulas(array $sumulasAvaliacao, array $dadosSumulas): array
    {
        $erros = [];
        foreach ($sumulasAvaliacao as $linha) {
            $sumula = $linha['sumula'] ?? null;
            $sumulaId = (int)($sumula->id ?? 0);
            if ($sumulaId <= 0) {
                continue;
            }
            if (!array_key_exists((string)$sumulaId, $dadosSumulas) && !array_key_exists($sumulaId, $dadosSumulas)) {
                $erros[] = 'Informe todas as quantidades avaliadas da súmula. Use 0 quando não houver produção.';
                break;
            }

            $valor = $dadosSumulas[$sumulaId] ?? $dadosSumulas[(string)$sumulaId] ?? null;
            $valor = trim((string)$valor);
            if ($valor === '' || !ctype_digit($valor)) {
                $erros[] = 'As quantidades avaliadas da súmula devem ser números inteiros. Use 0 quando não houver produção.';
                break;
            }

            $quantidade = (int)$valor;
            if ($quantidade < 0 || $quantidade > 50) {
                $erros[] = 'As quantidades avaliadas da súmula devem estar entre 0 e 50.';
                break;
            }
        }

        return $erros;
    }

    protected function valorSumulaLancamento(array $dadosSumulas, int $sumulaId, int $padrao = 0): int
    {
        if (array_key_exists($sumulaId, $dadosSumulas)) {
            return (int)$dadosSumulas[$sumulaId];
        }
        if (array_key_exists((string)$sumulaId, $dadosSumulas)) {
            return (int)$dadosSumulas[(string)$sumulaId];
        }

        return $padrao;
    }

    protected function salvarLancamentoAvaliacao(AvaliadorBolsista $avaliacao, array $questoes, array $sumulasAvaliacao = []): void
    {
        $avaliadorBolsistasTable = $this->fetchTable('AvaliadorBolsistas');
        $avaliationsTable = $this->fetchTable('Avaliations');
        $avaliationsSumulasTable = $this->fetchTable('AvaliationsSumulas');
        $notas = (array)$this->request->getData('q', []);
        $dadosSumulas = (array)$this->request->getData('sumula', []);
        $observacaoQuesitos = trim((string)$this->request->getData('observacao_avaliador', ''));
        $observacaoSumulas = trim((string)$this->request->getData('observacao_sumulas', ''));
        $parecer = (string)$this->request->getData('parecer', '');
        $destaque = $this->request->getData('destaque');
        $indicadoPremioCapes = $this->request->getData('indicado_premio_capes');

        $avaliadorBolsistasTable->getConnection()->transactional(function () use (
            $avaliacao,
            $questoes,
            $notas,
            $observacaoQuesitos,
            $observacaoSumulas,
            $parecer,
            $destaque,
            $indicadoPremioCapes,
            $avaliadorBolsistasTable,
            $avaliationsTable,
            $avaliationsSumulasTable,
            $sumulasAvaliacao,
            $dadosSumulas
        ): void {
            $avaliationsTable->updateAll(
                ['deleted' => 1],
                [
                    'avaliador_bolsista_id' => (int)$avaliacao->id,
                    'OR' => [
                        'deleted IS' => null,
                        'deleted' => 0,
                    ],
                ]
            );

            $total = 0.0;
            foreach ($questoes as $questao) {
                $questaoId = (int)$questao->id;
                $nota = (float)str_replace(',', '.', (string)($notas[$questaoId] ?? $notas[(string)$questaoId]));
                $total += $nota;

                $avaliationsTable->saveOrFail($avaliationsTable->newEntity([
                    'avaliador_bolsista_id' => (int)$avaliacao->id,
                    'question_id' => $questaoId,
                    'nota' => $nota,
                    'observacao_avaliador' => $observacaoQuesitos,
                    'parecer' => $parecer,
                    'deleted' => 0,
                ]));
            }

            $notaSumula = null;
            if ($sumulasAvaliacao !== []) {
                $avaliationsSumulasTable->updateAll(
                    ['deleted' => 1],
                    [
                        'avaliador_bolsista_id' => (int)$avaliacao->id,
                        'OR' => [
                            'deleted IS' => null,
                            'deleted' => 0,
                        ],
                    ]
                );

                $totaisPorBloco = [];
                $maxPorBloco = [];
                foreach ($sumulasAvaliacao as $linha) {
                    $sumula = $linha['sumula'];
                    $inscricaoSumula = $linha['inscricao_sumula'] ?? null;
                    $sumulaId = (int)$sumula->id;
                    $blocoId = (int)($sumula->editais_sumula_bloco_id ?? 0);
                    $quantidadeOriginal = (int)($linha['quantidade_original'] ?? 0);
                    $quantidadeAvaliada = $this->valorSumulaLancamento($dadosSumulas, $sumulaId);
                    $fator = (float)($sumula->fator ?? 0);
                    $notaItem = round($quantidadeAvaliada * $fator, 2);
                    $maxSumula = $sumula->max ?? null;
                    if ($maxSumula !== null && $maxSumula !== '') {
                        $notaItem = min($notaItem, (float)$maxSumula);
                    }

                    $totaisPorBloco[$blocoId] = ($totaisPorBloco[$blocoId] ?? 0) + $notaItem;
                    $maxBloco = $sumula->editais_sumulas_bloco->max ?? null;
                    if ($maxBloco !== null && $maxBloco !== '') {
                        $maxPorBloco[$blocoId] = (float)$maxBloco;
                    }

                    $avaliationsSumulasTable->saveOrFail($avaliationsSumulasTable->newEntity([
                        'avaliador_bolsista_id' => (int)$avaliacao->id,
                        'editais_sumula_id' => $sumulaId,
                        'editais_sumula_bloco_id' => $blocoId > 0 ? $blocoId : null,
                        'inscricao_sumula_id' => !empty($inscricaoSumula) ? (int)$inscricaoSumula->id : null,
                        'nota' => $notaItem,
                        'observacao_avaliador' => $observacaoSumulas,
                        'deleted' => 0,
                        'quantidade_original' => $quantidadeOriginal,
                        'quantidade_avaliada' => $quantidadeAvaliada,
                    ]));
                }

                $notaSumula = 0.0;
                foreach ($totaisPorBloco as $blocoId => $totalBloco) {
                    $maxBloco = $maxPorBloco[$blocoId] ?? null;
                    if ($maxBloco !== null) {
                        $totalBloco = min((float)$totalBloco, (float)$maxBloco);
                    }
                    $notaSumula += (float)$totalBloco;
                }
                $notaSumula = round($notaSumula, 2);
            }

            $avaliacao->observacao = $observacaoQuesitos;
            $avaliacao->situacao = 'F';
            $avaliacao->nota = $total;
            $avaliacao->nota_sumula = $notaSumula;
            $avaliacao->parecer = $parecer;
            if ($destaque !== null && $destaque !== '') {
                $avaliacao->destaque = (int)$destaque;
            }
            if ($indicadoPremioCapes !== null && $indicadoPremioCapes !== '') {
                $avaliacao->indicado_premio_capes = (int)$indicadoPremioCapes;
            }
            $avaliadorBolsistasTable->saveOrFail($avaliacao);

            if (in_array((string)$avaliacao->tipo, ['V', 'Z'], true) && (!empty($avaliacao->raic_id) || !empty($avaliacao->bolsista))) {
                $raicsTable = $this->fetchTable('Raics');
                $raic = $raicsTable->get((int)($avaliacao->raic_id ?: $avaliacao->bolsista));
                $raic->observacao_avaliador = $observacaoQuesitos;
                $raic->nota_final = $total;
                if ($destaque !== null && $destaque !== '') {
                    $raic->destaque = (int)$destaque;
                }
                if ($indicadoPremioCapes !== null && $indicadoPremioCapes !== '') {
                    $raic->indicado_premio_capes = (int)$indicadoPremioCapes;
                }
                $raicsTable->saveOrFail($raic);
            } elseif ((string)$avaliacao->tipo === 'W' && (!empty($avaliacao->workshop_id) || !empty($avaliacao->bolsista))) {
                $workshopsTable = $this->fetchTable('Workshops');
                $workshop = $workshopsTable->get((int)($avaliacao->workshop_id ?: $avaliacao->bolsista));
                $workshop->observacao_avaliador = $observacaoQuesitos;
                $workshop->nota_final = $total;
                if ($destaque !== null && $destaque !== '') {
                    $workshop->destaque = (int)$destaque;
                }
                if ($indicadoPremioCapes !== null && $indicadoPremioCapes !== '') {
                    $workshop->indicado_premio_capes = (int)$indicadoPremioCapes;
                }
                $workshopsTable->saveOrFail($workshop);
            }
        });
    }

    protected function deveAvaliarSumulas(AvaliadorBolsista $avaliacao): bool
    {
        return (string)($avaliacao->tipo ?? '') === 'N'
            && (string)($avaliacao->editai->origem ?? '') === 'N'
            && !empty($avaliacao->projeto_bolsista);
    }

    protected function carregarSumulasAvaliacao(AvaliadorBolsista $avaliacao): array
    {
        $inscricaoId = (int)($avaliacao->projeto_bolsista->id ?? $avaliacao->projeto_bolsista_id ?? $avaliacao->bolsista ?? 0);
        $editalId = (int)($avaliacao->editai_id ?? $avaliacao->editai->id ?? 0);
        if ($inscricaoId <= 0 || $editalId <= 0) {
            return [];
        }

        $sumulas = $this->fetchTable('EditaisSumulas')->find()
            ->contain(['EditaisSumulasBlocos'])
            ->where([
                'EditaisSumulas.editai_id' => $editalId,
                'EditaisSumulas.deleted IS' => null,
            ])
            ->orderBy([
                'EditaisSumulas.editais_sumula_bloco_id' => 'ASC',
                'EditaisSumulas.id' => 'ASC',
            ])
            ->all()
            ->toList();

        if ($sumulas === []) {
            return [];
        }

        $sumulasIds = array_map(fn($sumula): int => (int)$sumula->id, $sumulas);
        $inscricaoSumulas = $this->fetchTable('InscricaoSumulas')->find()
            ->where([
                'InscricaoSumulas.projeto_bolsista_id' => $inscricaoId,
                //'InscricaoSumulas.pdj_inscricoe_id IS' => null,
                'InscricaoSumulas.editais_sumula_id IN' => $sumulasIds,
            ])
            ->all();

        $mapInscricaoSumulas = [];
        foreach ($inscricaoSumulas as $inscricaoSumula) {
            $mapInscricaoSumulas[(int)$inscricaoSumula->editais_sumula_id] = $inscricaoSumula;
        }

        $linhas = [];
        foreach ($sumulas as $sumula) {
            $inscricaoSumula = $mapInscricaoSumulas[(int)$sumula->id] ?? null;
            $linhas[] = [
                'sumula' => $sumula,
                'inscricao_sumula' => $inscricaoSumula,
                'quantidade' => (int)($inscricaoSumula->quantidade ?? 0),
            ];
        }

        return $linhas;
    }

    protected function carregarReferenciaAvaliacao($avaliacao): void
    {
        $tipo = (string)($avaliacao->tipo ?? '');
        $referenciaId = (int)($avaliacao->bolsista ?? 0);

        if ($tipo === 'N' && empty($avaliacao->projeto_bolsista) && $referenciaId > 0) {
            $referencia = $this->fetchTable('ProjetoBolsistas')->find()
                ->contain([
                    'Editais',
                    'Bolsistas',
                    'Orientadores' => ['Unidades'],
                    'Projetos',
                ])
                ->where(['ProjetoBolsistas.id' => $referenciaId])
                ->first();
            $avaliacao->set('projeto_bolsista', $referencia);
        } elseif (in_array($tipo, ['V', 'Z'], true) && empty($avaliacao->raic) && $referenciaId > 0) {
            $referencia = $this->fetchTable('Raics')->find()
                ->contain([
                    'Editais',
                    'Unidades',
                    'Usuarios',
                    'Orientadores',
                    'ProjetoBolsistas' => ['Projetos'],
                ])
                ->where(['Raics.id' => $referenciaId])
                ->first();
            $avaliacao->set('raic', $referencia);
        } elseif ($tipo === 'W' && empty($avaliacao->workshop) && $referenciaId > 0) {
            $referencia = $this->fetchTable('Workshops')->find()
                ->contain([
                    'Editais',
                    'Unidades',
                    'Usuarios',
                    'Orientadores',
                    'PdjInscricoes' => [
                        'Bolsistas',
                        'Orientadores' => ['Unidades'],
                        'Projetos',
                    ],
                ])
                ->where(['Workshops.id' => $referenciaId])
                ->first();
            $avaliacao->set('workshop', $referencia);
        }
    }

    public function cadastroRaic()
    {
        $this->request->allowMethod(['get', 'post']);

        $editais = $this->fetchTable('Editais')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where([
                'Editais.origem' => 'V',
                'Editais.deleted' => 0,
                'Editais.inicio_avaliar < NOW()',
                'Editais.fim_avaliar > NOW()',
            ])
            ->orderBy(['Editais.nome' => 'ASC'])
            ->toArray();

        $unidades = $this->obterUnidadesDisponiveis();

        $dados = [
            'editai_id' => (int)$this->request->getData('editai_id', 0),
            'unidade_id' => (int)$this->request->getData('unidade_id', 0),
            'cpfs' => (string)$this->request->getData('cpfs', ''),
        ];

        $resultado = [
            'processado' => false,
            'confirmado' => false,
            'elegiveis' => [],
            'inelegiveis' => [],
            'totalInformados' => 0,
            'totalElegiveis' => 0,
            'totalInelegiveis' => 0,
        ];

        if ($this->request->is('post')) {
            $acao = (string)$this->request->getData('acao', 'analisar');
            $errosFormulario = $this->validarFormularioCadastroRaic($dados, $editais, $unidades);

            if ($errosFormulario !== []) {
                $this->Flash->error(implode('<br>', $errosFormulario), ['escape' => false]);
            } else {
                try {
                    $resultado = $this->analisarCadastroRaic($dados);

                    if ($acao === 'confirmar') {
                        if ($resultado['totalElegiveis'] === 0) {
                            $this->Flash->error('Não há avaliadores elegíveis para confirmar o cadastro.');
                        } else {
                            $totalGravados = $this->confirmarCadastroRaic($dados, $resultado['elegiveis']);
                            $resultado['confirmado'] = true;
                            $this->Flash->success($totalGravados . ' avaliador(es) RAIC cadastrado(s) com sucesso.');
                        }
                    } else {
                        if ($resultado['totalElegiveis'] > 0) {
                            $this->Flash->info('Revise a prévia abaixo antes de confirmar o cadastro massivo.');
                        }
                    }

                    if ($resultado['totalInelegiveis'] > 0) {
                        $this->Flash->error(
                            'Foram encontradas inelegibilidades em ' . $resultado['totalInelegiveis'] . ' CPF(s). Revise a listagem abaixo.',
                            ['escape' => false]
                        );
                    }

                    if ($resultado['totalElegiveis'] === 0 && $resultado['totalInelegiveis'] === 0) {
                        $this->Flash->info('Nenhum CPF válido foi informado para processamento.');
                    }
                } catch (\Throwable $e) {
                    $resultado['processado'] = true;
                    $this->flashFriendlyException(
                        $e,
                        'Erro no Sistema - cadastro massivo de avaliadores RAIC',
                        'Não foi possível concluir o cadastro massivo de avaliadores RAIC.'
                    );
                }
            }
        }

        $this->set(compact('editais', 'unidades', 'dados', 'resultado'));
    }

    public function cadastroNova()
    {
        $this->request->allowMethod(['get', 'post']);

        if (!$this->ehYoda()) {
            $this->Flash->error('Área restrita à Gestão de Fomento.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }

        $editais = $this->fetchTable('Editais')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where([
                'Editais.origem IN' => ['N', 'R', 'J', 'W'],
                'Editais.deleted' => 0,
                'Editais.inicio_avaliar < NOW()',
                'Editais.fim_avaliar > NOW()',
            ])
            ->orderBy(['Editais.nome' => 'ASC'])
            ->toArray();

        $grandesAreas = $this->fetchTable('GrandesAreas')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where(['GrandesAreas.id <' => 10])
            ->orderBy(['GrandesAreas.nome' => 'ASC'])
            ->toArray();

        $dados = [
            'editais' => $this->normalizarEditaisSelecionados((array)$this->request->getData('editais', [])),
            'grandes_area_id' => (int)$this->request->getData('grandes_area_id', 0),
            'area_id' => (int)$this->request->getData('area_id', 0),
            'cpfs' => (string)$this->request->getData('cpfs', ''),
        ];

        $areas = [];
        if (!empty($dados['grandes_area_id'])) {
            $areas = $this->fetchTable('Areas')->find('list', [
                'keyField' => 'id',
                'valueField' => 'nome',
            ])
                ->where(['Areas.grandes_area_id' => $dados['grandes_area_id']])
                ->orderBy(['Areas.nome' => 'ASC'])
                ->toArray();
        }

        $resultado = [
            'processado' => false,
            'confirmado' => false,
            'elegiveis' => [],
            'inelegiveis' => [],
            'totalInformados' => 0,
            'totalElegiveis' => 0,
            'totalInelegiveis' => 0,
        ];

        if ($this->request->is('post')) {
            $acao = (string)$this->request->getData('acao', 'analisar');
            $errosFormulario = $this->validarFormularioCadastroNova($dados, $editais, $grandesAreas, $areas);

            if ($errosFormulario !== []) {
                $this->Flash->error(implode('<br>', $errosFormulario), ['escape' => false]);
            } else {
                try {
                    $resultado = $this->analisarCadastroNova($dados);

                    if ($acao === 'confirmar') {
                        if ($resultado['totalElegiveis'] === 0) {
                            $this->Flash->error('Não há avaliadores elegíveis para confirmar o cadastro.');
                        } else {
                            $totalGravados = $this->confirmarCadastroNova($dados, $resultado['elegiveis']);
                            $resultado['confirmado'] = true;
                            $this->Flash->success($totalGravados . ' vínculo(s) de avaliador(es) gravado(s) com sucesso.');
                        }
                    } else {
                        if ($resultado['totalElegiveis'] > 0) {
                            $this->Flash->info('Revise a prévia abaixo antes de confirmar o cadastro massivo.');
                        }
                    }

                    if ($resultado['totalInelegiveis'] > 0) {
                        $this->Flash->error(
                            'Foram encontradas inelegibilidades em ' . $resultado['totalInelegiveis'] . ' combinação(ões) CPF/edital. Revise a listagem abaixo.',
                            ['escape' => false]
                        );
                    }

                    if ($resultado['totalElegiveis'] === 0 && $resultado['totalInelegiveis'] === 0) {
                        $this->Flash->info('Nenhum CPF válido foi informado para processamento.');
                    }
                } catch (\Throwable $e) {
                    $resultado['processado'] = true;
                    $this->flashFriendlyException(
                        $e,
                        'Erro no Sistema - cadastro massivo de avaliadores para editais',
                        'Não foi possível concluir o cadastro massivo de avaliadores para editais.'
                    );
                }
            }
        }

        $this->set(compact('editais', 'grandesAreas', 'areas', 'dados', 'resultado'));
    }

    public function cadastroConvites()
    {
        $this->request->allowMethod(['get', 'post']);

        if (!$this->ehYoda()) {
            $this->Flash->error('Área restrita à Gestão de Fomento.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }

        $editais = $this->fetchTable('Editais')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where([
                'Editais.origem IN' => ['N', 'R', 'J', 'W'],
                'Editais.deleted' => 0,
                'Editais.inicio_avaliar < NOW()',
                'Editais.fim_avaliar > NOW()',
            ])
            ->orderBy(['Editais.nome' => 'ASC'])
            ->toArray();

        $dados = [
            'editais' => $this->normalizarEditaisSelecionados((array)$this->request->getData('editais', [])),
            'cpfs' => (string)$this->request->getData('cpfs', ''),
        ];
        sort($dados['editais'], SORT_NUMERIC);

        $resultado = [
            'processado' => false,
            'confirmado' => false,
            'elegiveis' => [],
            'inelegiveis' => [],
            'totalInformados' => 0,
            'totalElegiveis' => 0,
            'totalInelegiveis' => 0,
        ];

        if ($this->request->is('post')) {
            $acao = (string)$this->request->getData('acao', 'analisar');
            $errosFormulario = $this->validarFormularioCadastroConvites($dados, $editais);

            if ($errosFormulario !== []) {
                $this->Flash->error(implode('<br>', $errosFormulario), ['escape' => false]);
            } else {
                try {
                    $resultado = $this->analisarCadastroConvites($dados);

                    if ($acao === 'confirmar') {
                        if ($resultado['totalElegiveis'] === 0) {
                            $this->Flash->error('Não há convites elegíveis para confirmar o cadastro.');
                        } else {
                            $totalGravados = $this->confirmarCadastroConvites($dados, $resultado['elegiveis']);
                            $resultado['confirmado'] = true;
                            $this->Flash->success($totalGravados . ' convite(s) gravado(s) com sucesso.');
                        }
                    } elseif ($resultado['totalElegiveis'] > 0) {
                        $this->Flash->info('Revise a prévia abaixo antes de confirmar o cadastro massivo de convites.');
                    }

                    if ($resultado['totalInelegiveis'] > 0) {
                        $this->Flash->error(
                            'Foram encontradas inelegibilidades em ' . $resultado['totalInelegiveis'] . ' CPF(s). Revise a listagem abaixo.',
                            ['escape' => false]
                        );
                    }

                    if ($resultado['totalElegiveis'] === 0 && $resultado['totalInelegiveis'] === 0) {
                        $this->Flash->info('Nenhum CPF válido foi informado para processamento.');
                    }
                } catch (\Throwable $e) {
                    $resultado['processado'] = true;
                    $this->flashFriendlyException(
                        $e,
                        'Erro no Sistema - cadastro massivo de convites de avaliadores',
                        'Não foi possível concluir o cadastro massivo de convites.'
                    );
                }
            }
        }

        $this->set(compact('editais', 'dados', 'resultado'));
    }

    public function listaConvites()
    {
        $this->request->allowMethod(['get']);

        if (!$this->ehYoda()) {
            $this->Flash->error('Área restrita à Gestão de Fomento.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }

        $convitesTable = $this->fetchTable('Convites');
        $anoAtual = (int)date('Y');
        $anos = $convitesTable->find('list', [
            'keyField' => 'ano',
            'valueField' => 'ano',
        ])
            ->select(['ano'])
            ->where(['Convites.ano IS NOT' => null])
            ->distinct(['Convites.ano'])
            ->orderBy(['Convites.ano' => 'DESC'])
            ->toArray();
        if (!isset($anos[$anoAtual])) {
            $anos = [$anoAtual => $anoAtual] + $anos;
        }

        $filtros = [
            'ano' => (int)$this->request->getQuery('ano', $anoAtual),
            'aceite' => trim((string)$this->request->getQuery('aceite', '')),
        ];
        if ($filtros['ano'] > 0 && !isset($anos[$filtros['ano']])) {
            $filtros['ano'] = $anoAtual;
        }

        $aceiteOptions = [
            'pendente' => 'Não respondido',
            '1' => 'Aceito',
            '0' => 'Recusado',
        ];
        if ($filtros['aceite'] !== '' && !isset($aceiteOptions[$filtros['aceite']])) {
            $filtros['aceite'] = '';
        }

        $query = $convitesTable->find()
            ->contain([
                'Usuarios',
                'Cadastradores',
            ])
            ->where(['Convites.deleted IS' => null])
            ->orderBy([
                'Convites.created' => 'DESC',
                'Convites.id' => 'DESC',
            ]);

        if ($filtros['ano'] > 0) {
            $query->where(['Convites.ano' => $filtros['ano']]);
        }

        if ($filtros['aceite'] === 'pendente') {
            $query->where(['Convites.aceite IS' => null]);
        } elseif ($filtros['aceite'] !== '') {
            $query->where(['Convites.aceite' => (int)$filtros['aceite']]);
        }

        $convites = $this->paginate($query, ['limit' => 20]);
        $editais = $this->fetchTable('Editais')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])->toArray();

        $this->set(compact('convites', 'anos', 'aceiteOptions', 'filtros', 'editais'));
    }

    public function cadastroMultiareasDemo()
    {
        $this->request->allowMethod(['get']);

        $grandesAreas = $this->fetchTable('GrandesAreas')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where(['GrandesAreas.id <' => 10])
            ->orderBy(['GrandesAreas.nome' => 'ASC'])
            ->toArray();

        $areasPorGrandeArea = [];
        $areas = $this->fetchTable('Areas')->find()
            ->select(['id', 'nome', 'grandes_area_id'])
            ->where(['Areas.grandes_area_id IN' => array_keys($grandesAreas)])
            ->orderBy([
                'Areas.grandes_area_id' => 'ASC',
                'Areas.nome' => 'ASC',
            ])
            ->all();

        foreach ($areas as $area) {
            $grandeAreaId = (int)$area->grandes_area_id;
            if (!isset($areasPorGrandeArea[$grandeAreaId])) {
                $areasPorGrandeArea[$grandeAreaId] = [];
            }
            $areasPorGrandeArea[$grandeAreaId][(int)$area->id] = (string)$area->nome;
        }

        $this->set(compact('grandesAreas', 'areasPorGrandeArea'));
    }

    public function buscaAreas()
    {
        $this->request->allowMethod(['post', 'ajax']);
        $this->autoRender = false;

        $grandeAreaId = (int)$this->request->getData('id');

        $areas = $this->fetchTable('Areas')->find()
            ->select(['id', 'nome'])
            ->where(['Areas.grandes_area_id' => $grandeAreaId])
            ->orderBy(['Areas.nome' => 'ASC'])
            ->all()
            ->map(function ($area): array {
                return [
                    'id' => (int)$area->id,
                    'nome' => (string)$area->nome,
                ];
            })
            ->toList();

        return $this->response
            ->withType('application/json')
            ->withStringBody((string)json_encode($areas, JSON_UNESCAPED_UNICODE));
    }

    public function buscaAvaliadoresInscricao($id = null)
    {
        $this->request->allowMethod(['post', 'ajax']);
        $this->autoRender = false;

        if (!$this->ehYoda()) {
            return $this->response
                ->withStatus(403)
                ->withType('application/json')
                ->withStringBody((string)json_encode([
                    'erro' => 'Acesso negado.',
                ], JSON_UNESCAPED_UNICODE));
        }

        $inscricao = $this->fetchTable('ProjetoBolsistas')->find()
            ->select(['id', 'editai_id', 'orientador', 'coorientador', 'bolsista'])
            ->where(['ProjetoBolsistas.id' => (int)$id])
            ->first();

        if (!$inscricao) {
            return $this->response
                ->withStatus(404)
                ->withType('application/json')
                ->withStringBody((string)json_encode([
                    'erro' => 'Inscrição não localizada.',
                ], JSON_UNESCAPED_UNICODE));
        }

        $grandeAreaId = (int)$this->request->getData('grandes_area_id', 0);
        $areaId = (int)$this->request->getData('area_id', 0);

        $avaliadores = $this->obterOpcoesAvaliadoresInscricao(
            (int)($inscricao->editai_id ?? 0),
            $this->obterUsuariosExcluidosInscricao($inscricao),
            $grandeAreaId,
            $areaId
        );

        $payload = [];
        foreach ($avaliadores as $avaliadorId => $descricao) {
            $payload[] = [
                'id' => (int)$avaliadorId,
                'nome' => (string)$descricao,
            ];
        }

        return $this->response
            ->withType('application/json')
            ->withStringBody((string)json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    public function listaRaic()
    {
        return $this->redirect([
            'controller' => 'Listas',
            'action' => 'listaAvaliadoresRaic',
            '?' => $this->request->getQueryParams(),
        ]);
    }

    public function listaNova()
    {
        return $this->redirect([
            'controller' => 'Listas',
            'action' => 'listaAvaliadoresNova',
            '?' => $this->request->getQueryParams(),
        ]);
    }

    public function listaInscricoes()
    {
        return $this->redirect([
            'controller' => 'Listas',
            'action' => 'listaInscricoesAvaliadores',
            '?' => $this->request->getQueryParams(),
        ]);
    }

    public function editarAreaAvaliadorNova(int $id)
    {
        $this->request->allowMethod(['get', 'post', 'put', 'patch']);

        if (!$this->ehYoda()) {
            $this->Flash->error('Área restrita à Gestão de Fomento.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }

        $avaliadorsTable = $this->fetchTable('Avaliadors');
        $avaliador = $avaliadorsTable->get($id, [
            'contain' => ['Usuarios', 'Editais', 'GrandesAreas', 'Areas'],
        ]);

        if ((string)($avaliador->tipo_avaliador ?? '') !== 'N' || (int)($avaliador->deleted ?? 0) !== 0) {
            $this->Flash->error('Avaliador não localizado na lista de avaliadores nova.');
            return $this->redirect(['controller' => 'Listas', 'action' => 'listaAvaliadoresNova']);
        }

        $grandesAreas = $this->obterGrandesAreasRestritasAvaliadores();
        $grandeAreaSelecionada = (int)$this->request->getData('grandes_area_id', (int)($avaliador->grandes_area_id ?? 0));
        $areas = $this->obterAreasPorGrandeAreaAvaliadores($grandeAreaSelecionada);
        $retorno = (string)$this->request->getData(
            'retorno',
            (string)$this->request->getQuery('retorno', $this->request->referer(true))
        );

        if ($this->request->is(['post', 'put', 'patch'])) {
            $grandeAreaId = (int)$this->request->getData('grandes_area_id', 0);
            $areaId = (int)$this->request->getData('area_id', 0);
            $grandeAreaSalvar = $grandeAreaId > 0 ? $grandeAreaId : null;
            $areaSalvar = $areaId > 0 ? $areaId : null;

            $erros = [];
            if ($grandeAreaSalvar !== null && !isset($grandesAreas[$grandeAreaSalvar])) {
                $erros[] = 'Selecione uma grande área válida.';
            }

            $areasSelecionadas = $this->obterAreasPorGrandeAreaAvaliadores($grandeAreaSalvar ?? 0);
            if ($areaSalvar !== null) {
                if ($grandeAreaSalvar === null) {
                    $erros[] = 'Selecione a grande área antes de selecionar a área.';
                } elseif (!isset($areasSelecionadas[$areaSalvar])) {
                    $erros[] = 'Selecione uma área correspondente à grande área informada.';
                }
            }

            if ($erros === [] && $this->existeOutroAvaliadorNovaMesmaCompetencia(
                (int)$avaliador->id,
                (int)$avaliador->usuario_id,
                (int)$avaliador->editai_id,
                $grandeAreaSalvar,
                $areaSalvar
            )) {
                $erros[] = 'Já existe cadastro deste avaliador para o mesmo edital com esta competência.';
            }

            if ($erros !== []) {
                $this->Flash->error(implode('<br>', $erros), ['escape' => false]);
                $areas = $areasSelecionadas;
            } else {
                $avaliador = $avaliadorsTable->patchEntity($avaliador, [
                    'grandes_area_id' => $grandeAreaSalvar,
                    'area_id' => $areaSalvar,
                ]);

                try {
                    $avaliadorsTable->saveOrFail($avaliador);
                    $this->Flash->success('Grande área e área atualizadas com sucesso.');
                    return $this->redirect($retorno !== '' ? $retorno : ['controller' => 'Listas', 'action' => 'listaAvaliadoresNova']);
                } catch (\Throwable $e) {
                    $this->flashFriendlyException(
                        $e,
                        'Erro no Sistema - alterar area de avaliador nova',
                        'Não foi possível atualizar a grande área e a área do avaliador.'
                    );
                }
            }
        }

        $this->set(compact('avaliador', 'grandesAreas', 'areas', 'retorno'));
    }

    public function vincularInscricao($id = null)
    {
        $this->request->allowMethod(['get', 'post', 'put', 'patch']);

        if (!$this->ehYoda()) {
            $this->Flash->error('Área restrita à Gestão de Fomento.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }

        $projetoBolsistasTable = $this->fetchTable('ProjetoBolsistas');
        $avaliadorBolsistasTable = $this->fetchTable('AvaliadorBolsistas');
        $avaliadorsTable = $this->fetchTable('Avaliadors');

        $inscricao = $projetoBolsistasTable->get((int)$id, [
            'contain' => [
                'Editais',
                'Bolsistas' => ['Unidades'],
                'Orientadores' => ['Unidades', 'Vinculos'],
                'Coorientadores' => ['Unidades', 'Vinculos'],
                'Projetos' => ['Areas' => ['GrandesAreas']],
            ],
        ]);

        if ($inscricao->deleted !== null) {
            $this->Flash->error('Não é permitido vincular avaliadores a uma inscrição deletada.');
            return $this->redirect(['controller' => 'Listas', 'action' => 'listaInscricoesAvaliadores']);
        }

        if ($inscricao->homologado === null) {
            $this->Flash->error('Não é permitido vincular avaliadores a uma inscrição sem homologação definida.');
            return $this->redirect(['controller' => 'Listas', 'action' => 'listaInscricoesAvaliadores']);
        }

        $editalAberto = !empty($inscricao->editai)
            && empty($inscricao->editai->deleted)
            && in_array((string)($inscricao->editai->origem ?? ''), ['N', 'R'], true)
            && !empty($inscricao->editai->inicio_avaliar)
            && !empty($inscricao->editai->fim_avaliar)
            && $inscricao->editai->inicio_avaliar <= new \Cake\I18n\FrozenTime()
            && $inscricao->editai->fim_avaliar >= new \Cake\I18n\FrozenTime();

        if (!$editalAberto) {
            $this->Flash->error('O edital desta inscrição não está com avaliação aberta para vinculação de avaliadores.');
            return $this->redirect(['controller' => 'Listas', 'action' => 'listaInscricoesAvaliadores']);
        }

        $vinculosAtivos = $avaliadorBolsistasTable->find()
            ->contain([
                'Avaliadors' => ['Usuarios', 'GrandesAreas', 'Areas'],
                'Criadores',
            ])
            ->where([
                'AvaliadorBolsistas.deleted' => 0,
                'AvaliadorBolsistas.tipo' => 'N',
            ])
            ->andWhere(function ($exp) use ($id) {
                return $exp->or([
                    'AvaliadorBolsistas.projeto_bolsista_id' => (int)$id,
                    'AvaliadorBolsistas.bolsista' => (int)$id,
                ]);
            })
            ->orderBy(['AvaliadorBolsistas.ordem' => 'ASC'])
            ->all()
            ->toList();

        $avaliador1Atual = null;
        $avaliador2Atual = null;
        foreach ($vinculosAtivos as $vinculoAtivo) {
            if ((int)($vinculoAtivo->ordem ?? 0) === 1) {
                $avaliador1Atual = (int)($vinculoAtivo->avaliador_id ?? 0);
            }
            if ((int)($vinculoAtivo->ordem ?? 0) === 2) {
                $avaliador2Atual = (int)($vinculoAtivo->avaliador_id ?? 0);
            }
        }

        $fora = $this->obterUsuariosExcluidosInscricao($inscricao);

        $projetoAreaId = (int)($inscricao->projeto->area_id ?? 0);
        $projetoGrandeAreaId = (int)($inscricao->projeto->area->grandes_area_id ?? 0);
        $grandesAreas = $this->obterGrandesAreasRestritas();

        $filtrosAvaliador1 = [
            'grandes_area_id' => $projetoGrandeAreaId,
            'area_id' => $projetoAreaId,
        ];
        $filtrosAvaliador2 = [
            'grandes_area_id' => $projetoGrandeAreaId,
            'area_id' => $projetoAreaId,
        ];

        foreach ($vinculosAtivos as $vinculoAtivo) {
            $ordem = (int)($vinculoAtivo->ordem ?? 0);
            if (!in_array($ordem, [1, 2], true)) {
                continue;
            }

            $filtrosAtual = [
                'grandes_area_id' => (int)($vinculoAtivo->avaliador->grandes_area_id ?? $projetoGrandeAreaId),
                'area_id' => (int)($vinculoAtivo->avaliador->area_id ?? $projetoAreaId),
            ];

            if ($ordem === 1) {
                $filtrosAvaliador1 = $filtrosAtual;
                continue;
            }

            $filtrosAvaliador2 = $filtrosAtual;
        }

        if (!isset($grandesAreas[$filtrosAvaliador1['grandes_area_id']])) {
            $filtrosAvaliador1['grandes_area_id'] = 0;
            $filtrosAvaliador1['area_id'] = 0;
        }
        if (!isset($grandesAreas[$filtrosAvaliador2['grandes_area_id']])) {
            $filtrosAvaliador2['grandes_area_id'] = 0;
            $filtrosAvaliador2['area_id'] = 0;
        }

        $areasAvaliador1 = $this->obterAreasPorGrandeArea((int)$filtrosAvaliador1['grandes_area_id']);
        $areasAvaliador2 = $this->obterAreasPorGrandeArea((int)$filtrosAvaliador2['grandes_area_id']);

        if ($filtrosAvaliador1['area_id'] !== 0 && !isset($areasAvaliador1[$filtrosAvaliador1['area_id']])) {
            $filtrosAvaliador1['area_id'] = 0;
        }
        if ($filtrosAvaliador2['area_id'] !== 0 && !isset($areasAvaliador2[$filtrosAvaliador2['area_id']])) {
            $filtrosAvaliador2['area_id'] = 0;
        }

        $avaliadoresAvaliador1 = $this->obterOpcoesAvaliadoresInscricao(
            (int)($inscricao->editai_id ?? 0),
            $fora,
            (int)$filtrosAvaliador1['grandes_area_id'],
            (int)$filtrosAvaliador1['area_id']
        );
        $avaliadoresAvaliador2 = $this->obterOpcoesAvaliadoresInscricao(
            (int)($inscricao->editai_id ?? 0),
            $fora,
            (int)$filtrosAvaliador2['grandes_area_id'],
            (int)$filtrosAvaliador2['area_id']
        );

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();
            $avaliador1 = (int)($dados['avaliador_1'] ?? 0);
            $avaliador2 = (int)($dados['avaliador_2'] ?? 0);
            $vinculoAtualPorOrdem = [];
            foreach ($vinculosAtivos as $vinculoAtivo) {
                $ordemAtual = (int)($vinculoAtivo->ordem ?? 0);
                if (in_array($ordemAtual, [1, 2], true)) {
                    $vinculoAtualPorOrdem[$ordemAtual] = $vinculoAtivo;
                }
            }

            $avaliadoresInformados = array_filter([$avaliador1, $avaliador2], static function (int $avaliadorId): bool {
                return $avaliadorId > 0;
            });

            if ($avaliadoresInformados === [] && empty($vinculosAtivos)) {
                $this->Flash->error('Selecione pelo menos um avaliador.');
                return $this->redirect(['action' => 'vincularInscricao', $inscricao->id]);
            }

            if ($avaliador1 > 0 && $avaliador2 > 0 && $avaliador1 === $avaliador2) {
                $this->Flash->error('Não pode haver repetição entre os avaliadores.');
                return $this->redirect(['action' => 'vincularInscricao', $inscricao->id]);
            }

            if (in_array((int)($inscricao->orientador ?? 0), $avaliadoresInformados, true)) {
                $this->Flash->error('O orientador não pode ser vinculado como avaliador.');
                return $this->redirect(['action' => 'vincularInscricao', $inscricao->id]);
            }

            if (!empty($inscricao->coorientador) && in_array((int)$inscricao->coorientador, $avaliadoresInformados, true)) {
                $this->Flash->error('O coorientador não pode ser vinculado como avaliador.');
                return $this->redirect(['action' => 'vincularInscricao', $inscricao->id]);
            }

            if (!empty($inscricao->bolsista) && in_array((int)$inscricao->bolsista, $avaliadoresInformados, true)) {
                $this->Flash->error('O bolsista não pode ser vinculado como avaliador.');
                return $this->redirect(['action' => 'vincularInscricao', $inscricao->id]);
            }

            if ($avaliador1Atual !== null && $avaliador2Atual !== null) {
                if ((int)$avaliador1Atual === $avaliador1 && (int)$avaliador2Atual === $avaliador2) {
                    $this->Flash->error('Nenhuma alteração foi realizada nos avaliadores vinculados.');
                    return $this->redirect(['action' => 'vincularInscricao', $inscricao->id]);
                }

                if ((int)$avaliador1Atual === $avaliador2 && (int)$avaliador2Atual === $avaliador1) {
                    $this->Flash->error('Não é permitido apenas inverter a ordem dos mesmos avaliadores.');
                    return $this->redirect(['action' => 'vincularInscricao', $inscricao->id]);
                }
            }

            foreach ([1 => $avaliador1, 2 => $avaliador2] as $ordem => $avaliadorSelecionado) {
                $vinculoAtual = $vinculoAtualPorOrdem[$ordem] ?? null;
                if (
                    $vinculoAtual !== null
                    && (string)($vinculoAtual->situacao ?? '') === 'F'
                    && (int)($vinculoAtual->avaliador_id ?? 0) !== (int)$avaliadorSelecionado
                ) {
                    $this->Flash->error('Não é permitido substituir avaliador que já lançou nota.');
                    return $this->redirect(['action' => 'vincularInscricao', $inscricao->id]);
                }
            }

            $avaliadoresSelecionados = [];
            if ($avaliadoresInformados !== []) {
                $avaliadoresSelecionados = $avaliadorsTable->find()
                    ->select(['Avaliadors.id', 'Avaliadors.usuario_id', 'Usuarios.nome'])
                    ->leftJoinWith('Usuarios')
                    ->where([
                        'Avaliadors.id IN' => $avaliadoresInformados,
                        'Avaliadors.deleted' => 0,
                    ])
                    ->enableHydration(false)
                    ->all()
                    ->indexBy('id')
                    ->toArray();
            }

            if (count($avaliadoresSelecionados) !== count($avaliadoresInformados)) {
                $this->Flash->error('Um ou mais avaliadores selecionados não estão mais disponíveis.');
                return $this->redirect(['action' => 'vincularInscricao', $inscricao->id]);
            }

            $usuariosSelecionados = array_map(static function (array $avaliadorSelecionado): int {
                return (int)($avaliadorSelecionado['usuario_id'] ?? 0);
            }, $avaliadoresSelecionados);
            $usuariosSelecionados = array_filter($usuariosSelecionados, static function (int $usuarioId): bool {
                return $usuarioId > 0;
            });

            if (
                count($usuariosSelecionados) !== count($avaliadoresInformados)
                || count($usuariosSelecionados) !== count(array_unique($usuariosSelecionados))
            ) {
                $this->Flash->error('Não pode haver repetição entre os avaliadores.');
                return $this->redirect(['action' => 'vincularInscricao', $inscricao->id]);
            }

            $normalizarNome = static function (?string $nome): string {
                $nome = trim((string)$nome);
                if ($nome === '') {
                    return '';
                }

                $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nome);
                if ($ascii !== false) {
                    $nome = $ascii;
                }

                $nome = preg_replace('/[^a-zA-Z0-9\s]/', '', $nome) ?? $nome;
                $nome = preg_replace('/\s+/', ' ', $nome) ?? $nome;

                return mb_strtolower(trim($nome));
            };

            $nomesBloqueados = array_values(array_filter(array_unique([
                $normalizarNome((string)($inscricao->orientadore->nome ?? '')),
                $normalizarNome((string)($inscricao->coorientadore->nome ?? '')),
                $normalizarNome((string)($inscricao->bolsista_usuario->nome ?? '')),
            ])));

            foreach ($avaliadoresInformados as $avaliadorIdSelecionado) {
                $nomeAvaliador = $normalizarNome((string)($avaliadoresSelecionados[$avaliadorIdSelecionado]['Usuarios__nome'] ?? ''));
                if ($nomeAvaliador !== '' && in_array($nomeAvaliador, $nomesBloqueados, true)) {
                    $this->Flash->error('Não é permitido vincular avaliador com o mesmo nome do orientador, do coorientador ou do bolsista.');
                    return $this->redirect(['action' => 'vincularInscricao', $inscricao->id]);
                }
            }

            $identityAtual = $this->getIdentityAtual();
            $usuarioHistoricoId = is_array($identityAtual)
                ? (int)($identityAtual['id'] ?? 0)
                : (int)($identityAtual->id ?? 0);

            try {
                $avaliadorBolsistasTable->getConnection()->transactional(function () use (
                    $avaliadorBolsistasTable,
                    $inscricao,
                    $avaliador1,
                    $avaliador2,
                    $avaliadoresSelecionados,
                    $vinculoAtualPorOrdem,
                    $usuarioHistoricoId
                ): void {
                    $dadosVinculo = [
                        ['avaliador_id' => $avaliador1, 'ordem' => 1],
                        ['avaliador_id' => $avaliador2, 'ordem' => 2],
                    ];

                    foreach ($dadosVinculo as $item) {
                        $ordem = (int)$item['ordem'];
                        $avaliadorId = (int)$item['avaliador_id'];
                        $vinculoAtual = $vinculoAtualPorOrdem[$ordem] ?? null;

                        if ($vinculoAtual !== null && (int)($vinculoAtual->avaliador_id ?? 0) === $avaliadorId) {
                            continue;
                        }

                        if ($vinculoAtual !== null) {
                            $vinculoAtual->deleted = 1;
                            $vinculoAtual->deletado_por = $usuarioHistoricoId > 0 ? $usuarioHistoricoId : null;
                            $vinculoAtual->deletado_em = \Cake\I18n\DateTime::now();
                            if (!$avaliadorBolsistasTable->save($vinculoAtual)) {
                                throw new \RuntimeException('Erro ao inativar o vínculo anterior do avaliador.');
                            }
                        }

                        if ($avaliadorId <= 0) {
                            continue;
                        }

                        $novo = $avaliadorBolsistasTable->newEmptyEntity();
                        $novo->bolsista = (int)$inscricao->id;
                        $novo->projeto_bolsista_id = (int)$inscricao->id;
                        $novo->tipo = 'N';
                        $novo->situacao = 'E';
                        $novo->editai_id = (int)$inscricao->editai_id;
                        $novo->ano = (string)date('Y');
                        $novo->coordenador = 0;
                        $novo->avaliador_id = $avaliadorId;
                        $novo->usuario_id = (int)($avaliadoresSelecionados[$avaliadorId]['usuario_id'] ?? 0);
                        $novo->ordem = $ordem;
                        $novo->deleted = 0;
                        $novo->criado_por = $usuarioHistoricoId > 0 ? $usuarioHistoricoId : null;
                        $novo->deletado_por = null;
                        $novo->deletado_em = null;

                        if (!$avaliadorBolsistasTable->save($novo)) {
                            throw new \RuntimeException('Erro ao gravar o vínculo do avaliador.');
                        }
                    }
                });

                $this->Flash->success(
                    empty($vinculosAtivos)
                        ? 'Avaliadores vinculados com sucesso.'
                        : 'Avaliadores substituídos com sucesso.'
                );
                return $this->redirect(['controller' => 'Listas', 'action' => 'listaInscricoesAvaliadores']);
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - vincular avaliadores a inscricao',
                    'Não foi possível salvar a vinculação dos avaliadores.'
                );
                return $this->redirect(['action' => 'vincularInscricao', $inscricao->id]);
            }
        }

        $this->set(compact(
            'inscricao',
            'grandesAreas',
            'areasAvaliador1',
            'areasAvaliador2',
            'avaliadoresAvaliador1',
            'avaliadoresAvaliador2',
            'avaliador1Atual',
            'avaliador2Atual',
            'vinculosAtivos',
            'filtrosAvaliador1',
            'filtrosAvaliador2'
        ));
    }

    protected function montarQueryListaRaic(array $filtros)
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
            $idsJedi = $this->obterIdsJedi();
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

    protected function montarQueryListaNova(array $filtros)
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

    protected function existeOutroAvaliadorNovaMesmaCompetencia(
        int $avaliadorId,
        int $usuarioId,
        int $editaiId,
        ?int $grandeAreaId,
        ?int $areaId
    ): bool {
        $condicoes = [
            'Avaliadors.id !=' => $avaliadorId,
            'Avaliadors.usuario_id' => $usuarioId,
            'Avaliadors.editai_id' => $editaiId,
            'Avaliadors.tipo_avaliador' => 'N',
            'Avaliadors.deleted' => 0,
        ];

        if ($grandeAreaId === null) {
            $condicoes['Avaliadors.grandes_area_id IS'] = null;
        } else {
            $condicoes['Avaliadors.grandes_area_id'] = $grandeAreaId;
        }

        if ($areaId === null) {
            $condicoes['Avaliadors.area_id IS'] = null;
        } else {
            $condicoes['Avaliadors.area_id'] = $areaId;
        }

        return $this->fetchTable('Avaliadors')->find()
            ->where($condicoes)
            ->count() > 0;
    }

    protected function obterAnosListaRaic(): array
    {
        return $this->obterAnosListaPorTipo('R');
    }

    protected function obterFiltrosListaRaic(array $unidades): array
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

    protected function exportarListaRaicCsv($query, array $filtros)
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

        $rows = [];
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

        $nomeArquivo = sprintf(
            'avaliadores_raic_%s.csv',
            date('Ymd_His')
        );

        rewind($fh);
        $csv = (string)stream_get_contents($fh);
        fclose($fh);

        return $this->response
            ->withType('csv')
            ->withCharset('UTF-8')
            ->withStringBody($csv)
            ->withDownload($nomeArquivo);
    }

    protected function obterAnosListaPorTipo(string $tipoAvaliador): array
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

    protected function obterFiltrosListaNova(): array
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

    protected function obterFiltrosListaInscricoes(): array
    {
        return [
            'editai_id' => (int)$this->request->getQuery('editai_id', 0),
            'status_vinculo' => trim((string)$this->request->getQuery('status_vinculo', '')),
            'grandes_area_id' => (int)$this->request->getQuery('grandes_area_id', 0),
            'area_id' => (int)$this->request->getQuery('area_id', 0),
        ];
    }

    protected function obterEditaisAbertosInscricoes(): array
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

    protected function montarQueryListaInscricoes(array $filtros)
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
                'ProjetoBolsistas.created',
                'projeto_titulo' => 'Projetos.titulo',
                'area_nome' => 'Areas.nome',
                'grande_area_nome' => 'GrandesAreas.nome',
                'total_avaliadores' => $projetoBolsistas->find()->newExpr('COALESCE(av_vinculos.total_avaliadores, 0)'),
                'avaliadores_nomes' => $projetoBolsistas->find()->newExpr('av_vinculos.avaliadores_nomes'),
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
                            GROUP_CONCAT(DISTINCT u.nome ORDER BY u.nome SEPARATOR " | ") AS avaliadores_nomes
                        FROM avaliador_bolsistas ab
                        LEFT JOIN usuarios u ON u.id = ab.usuario_id
                        WHERE ab.deleted = 0
                          AND ab.projeto_bolsista_id IS NOT NULL
                        GROUP BY ab.projeto_bolsista_id
                    )',
                ],
                'av_vinculos.projeto_bolsista_id = ProjetoBolsistas.id'
            )
            ->where([
                'ProjetoBolsistas.deleted IS' => null,
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

    protected function obterOpcoesAvaliadoresInscricao(
        int $editaiId,
        array $usuariosExcluidos,
        int $grandeAreaId,
        int $areaId
    ): array {
        $avaliadorsTable = $this->fetchTable('Avaliadors');
        $query = $avaliadorsTable->find('list', [
            'keyField' => 'id',
            'valueField' => function ($row): string {
                $nome = (string)($row->usuario->nome ?? 'Não informado');
                $sigla = (string)($row->usuario->unidade->sigla ?? 'Unidade não informada');
                $grandeArea = (string)($row->grandes_area->nome ?? 'Não informada');
                $area = (string)($row->area->nome ?? 'Não informada');
                return $nome . ' (' . $sigla . ') (' . $grandeArea . ' - ' . $area . ')';
            },
        ])
            ->contain([
                'GrandesAreas',
                'Areas',
                'Usuarios' => ['Unidades'],
            ])
            ->where([
                'Avaliadors.editai_id' => $editaiId,
                'Avaliadors.tipo_avaliador IN' => ['N', 'A'],
                'Avaliadors.deleted' => 0,
            ]);

        if ($usuariosExcluidos !== []) {
            $query->where(['Avaliadors.usuario_id NOT IN' => $usuariosExcluidos]);
        }

        if ($grandeAreaId > 0) {
            $query->where(['Avaliadors.grandes_area_id' => $grandeAreaId]);
        }

        if ($areaId > 0) {
            $query->where(['Avaliadors.area_id' => $areaId]);
        }

        $rankingSql = sprintf(
            'CASE
                WHEN Avaliadors.grandes_area_id = %d AND Avaliadors.area_id = %d THEN 0
                WHEN Avaliadors.grandes_area_id = %d THEN 1
                ELSE 2
            END',
            $grandeAreaId,
            $areaId,
            $grandeAreaId
        );

        $query
            ->orderByAsc($query->newExpr($rankingSql))
            ->orderBy(['Usuarios.nome' => 'ASC']);

        return $query->toArray();
    }

    protected function obterUsuariosExcluidosInscricao($inscricao): array
    {
        return array_values(array_filter(array_unique([
            (int)($inscricao->orientador ?? 0),
            (int)($inscricao->coorientador ?? 0),
            (int)($inscricao->bolsista ?? 0),
        ])));
    }

    protected function obterEditaisListaNova(): array
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

    protected function obterGrandesAreasRestritas(): array
    {
        return $this->fetchTable('GrandesAreas')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where(['GrandesAreas.id <' => 10])
            ->orderBy(['GrandesAreas.nome' => 'ASC'])
            ->toArray();
    }

    protected function obterAreasPorGrandeArea(int $grandeAreaId): array
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

    protected function exportarListaNovaCsv($query)
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

        $nomeArquivo = sprintf(
            'avaliadores_editais_%s.csv',
            date('Ymd_His')
        );

        rewind($fh);
        $csv = (string)stream_get_contents($fh);
        fclose($fh);

        return $this->response
            ->withType('csv')
            ->withCharset('UTF-8')
            ->withStringBody($csv)
            ->withDownload($nomeArquivo);
    }

    protected function obterUnidadesDisponiveis(): array
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

        $idsJedi = $this->obterIdsJedi();
        if ($idsJedi === []) {
            return [];
        }

        return $query
            ->where(['Unidades.id IN' => $idsJedi])
            ->toArray();
    }

    protected function obterIdsJedi(): array
    {
        $identity = $this->getIdentityAtual();
        $jediRaw = '';

        if (is_array($identity)) {
            $jediRaw = (string)($identity['jedi'] ?? '');
        } elseif ($identity !== null) {
            $jediRaw = (string)($identity->jedi ?? '');
        }

        if ($jediRaw === '') {
            $jediRaw = (string)($this->request->getAttribute('identity')['jedi'] ?? '');
        }

        $partes = array_map('trim', explode(',', $jediRaw));
        $ids = [];
        foreach ($partes as $parte) {
            if ($parte !== '' && ctype_digit($parte)) {
                $ids[] = (int)$parte;
            }
        }

        return array_values(array_unique($ids));
    }

    protected function validarFormularioCadastroRaic(array $dados, array $editais, array $unidades): array
    {
        $erros = [];

        if (empty($dados['editai_id']) || !isset($editais[$dados['editai_id']])) {
            $erros[] = 'Selecione um edital RAIC com avaliações abertas.';
        }

        if (empty($dados['unidade_id']) || !isset($unidades[$dados['unidade_id']])) {
            $erros[] = 'Selecione uma unidade válida e ativa.';
        }

        if (trim((string)$dados['cpfs']) === '') {
            $erros[] = 'Informe ao menos um CPF.';
        }

        return $erros;
    }

    protected function validarFormularioCadastroNova(array $dados, array $editais, array $grandesAreas, array $areas): array
    {
        $erros = [];

        if (empty($dados['editais'])) {
            $erros[] = 'Selecione ao menos um edital.';
        } else {
            foreach ($dados['editais'] as $editaiId) {
                if (!isset($editais[$editaiId])) {
                    $erros[] = 'Há edital inválido entre os selecionados.';
                    break;
                }
            }
        }

        if (!empty($dados['grandes_area_id']) && !isset($grandesAreas[$dados['grandes_area_id']])) {
            $erros[] = 'Selecione uma grande área válida.';
        }

        if (!empty($dados['area_id'])) {
            if (empty($dados['grandes_area_id'])) {
                $erros[] = 'Selecione a grande área antes de selecionar a área.';
            } elseif (!isset($areas[$dados['area_id']])) {
                $erros[] = 'Selecione uma área correspondente à grande área informada.';
            }
        }

        if (trim((string)$dados['cpfs']) === '') {
            $erros[] = 'Informe ao menos um CPF.';
        }

        return $erros;
    }

    protected function validarFormularioCadastroConvites(array $dados, array $editais): array
    {
        $erros = [];

        if (empty($dados['editais'])) {
            $erros[] = 'Selecione ao menos um edital.';
        } else {
            foreach ($dados['editais'] as $editaiId) {
                if (!isset($editais[$editaiId])) {
                    $erros[] = 'Há edital inválido entre os selecionados.';
                    break;
                }
            }
            $editaisSelecionados = $this->normalizarEditaisSelecionados((array)$dados['editais']);
            sort($editaisSelecionados, SORT_NUMERIC);
            if (strlen(implode(',', $editaisSelecionados)) > 45) {
                $erros[] = 'A lista de editais selecionados excede o tamanho máximo permitido para gravação.';
            }
        }

        if (trim((string)$dados['cpfs']) === '') {
            $erros[] = 'Informe ao menos um CPF.';
        }

        return $erros;
    }

    protected function analisarCadastroRaic(array $dados): array
    {
        $usuariosTable = $this->fetchTable('Usuarios');
        $avaliadorsTable = $this->fetchTable('Avaliadors');

        $cpfs = $this->normalizarCpfs((string)$dados['cpfs']);
        $resultado = [
            'processado' => true,
            'confirmado' => false,
            'elegiveis' => [],
            'inelegiveis' => [],
            'totalInformados' => count($cpfs),
            'totalElegiveis' => 0,
            'totalInelegiveis' => 0,
        ];

        if ($cpfs === []) {
            return $resultado;
        }

        $cpfJaProcessado = [];
        foreach ($cpfs as $cpfInformado) {
            $cpfOriginal = $cpfInformado['original'];
            $cpfNumerico = $cpfInformado['numerico'];

            if (isset($cpfJaProcessado[$cpfNumerico])) {
                $resultado['inelegiveis'][] = [
                    'cpf' => $cpfOriginal,
                    'motivo' => 'CPF repetido na própria listagem enviada.',
                ];
                continue;
            }
            $cpfJaProcessado[$cpfNumerico] = true;

            if (!$this->validaCPF($cpfNumerico)) {
                $resultado['inelegiveis'][] = [
                    'cpf' => $cpfOriginal,
                    'motivo' => 'CPF inválido.',
                ];
                continue;
            }

            $usuario = $usuariosTable->find()
                ->select(['id', 'nome', 'cpf'])
                ->where(['Usuarios.cpf' => $cpfNumerico])
                ->first();

            if (!$usuario) {
                $resultado['inelegiveis'][] = [
                    'cpf' => $cpfOriginal,
                    'motivo' => 'CPF não localizado na base de usuários.',
                ];
                continue;
            }

            $jaCadastrado = $this->existeCadastroRaic(
                (int)$usuario->id,
                (int)$dados['editai_id'],
                (int)$dados['unidade_id']
            );

            if ($jaCadastrado) {
                $resultado['inelegiveis'][] = [
                    'cpf' => $cpfOriginal,
                    'nome' => (string)$usuario->nome,
                    'motivo' => 'Avaliador já cadastrado nesta unidade para o edital RAIC selecionado.',
                ];
                continue;
            }

            $resultado['elegiveis'][] = [
                'usuario_id' => (int)$usuario->id,
                'cpf' => $cpfOriginal,
                'nome' => (string)$usuario->nome,
            ];
        }

        $resultado['totalElegiveis'] = count($resultado['elegiveis']);
        $resultado['totalInelegiveis'] = count($resultado['inelegiveis']);

        return $resultado;
    }

    protected function confirmarCadastroRaic(array $dados, array $elegiveis): int
    {
        $avaliadorsTable = $this->fetchTable('Avaliadors');
        $anoAtual = date('Y');
        $entidadesSalvar = [];
        $totalSalvar = 0;

        foreach ($elegiveis as $item) {
            if ($this->existeCadastroRaic(
                (int)$item['usuario_id'],
                (int)$dados['editai_id'],
                (int)$dados['unidade_id']
            )) {
                continue;
            }

            $entidadesSalvar[] = $avaliadorsTable->newEntity([
                'usuario_id' => (int)$item['usuario_id'],
                'grandes_area_id' => null,
                'area_id' => null,
                'ano_convite' => $anoAtual,
                'ano_aceite' => $anoAtual,
                'tipo_avaliador' => 'R',
                'deleted' => 0,
                'editai_id' => (int)$dados['editai_id'],
                'unidade_id' => (int)$dados['unidade_id'],
                'aceite' => 1,
            ]);
            $totalSalvar++;
        }

        $avaliadorsTable->getConnection()->transactional(function () use ($avaliadorsTable, $entidadesSalvar): void {
            foreach ($entidadesSalvar as $entidade) {
                $avaliadorsTable->saveOrFail($entidade);
            }
        });

        return $totalSalvar;
    }

    protected function analisarCadastroNova(array $dados): array
    {
        $usuariosTable = $this->fetchTable('Usuarios');
        $cpfs = $this->normalizarCpfs((string)$dados['cpfs']);
        $editaisSelecionados = $this->normalizarEditaisSelecionados((array)$dados['editais']);

        $resultado = [
            'processado' => true,
            'confirmado' => false,
            'elegiveis' => [],
            'inelegiveis' => [],
            'totalInformados' => count($cpfs),
            'totalElegiveis' => 0,
            'totalInelegiveis' => 0,
        ];

        if ($cpfs === [] || $editaisSelecionados === []) {
            return $resultado;
        }

        $cpfJaProcessado = [];
        foreach ($cpfs as $cpfInformado) {
            $cpfOriginal = $cpfInformado['original'];
            $cpfNumerico = $cpfInformado['numerico'];

            if (isset($cpfJaProcessado[$cpfNumerico])) {
                $resultado['inelegiveis'][] = [
                    'cpf' => $cpfOriginal,
                    'motivo' => 'CPF repetido na própria listagem enviada.',
                ];
                continue;
            }
            $cpfJaProcessado[$cpfNumerico] = true;

            if (!$this->validaCPF($cpfNumerico)) {
                $resultado['inelegiveis'][] = [
                    'cpf' => $cpfOriginal,
                    'motivo' => 'CPF inválido.',
                ];
                continue;
            }

            $usuario = $usuariosTable->find()
                ->select(['id', 'nome', 'cpf'])
                ->where(['Usuarios.cpf' => $cpfNumerico])
                ->first();

            if (!$usuario) {
                $resultado['inelegiveis'][] = [
                    'cpf' => $cpfOriginal,
                    'motivo' => 'CPF não localizado na base de usuários.',
                ];
                continue;
            }

            foreach ($editaisSelecionados as $editaiId) {
                $grandeAreaCadastro = !empty($dados['grandes_area_id']) ? (int)$dados['grandes_area_id'] : null;
                $areaCadastro = !empty($dados['area_id']) ? (int)$dados['area_id'] : null;

                if ($this->existeCadastroNova(
                    (int)$usuario->id,
                    $editaiId,
                    $grandeAreaCadastro,
                    $areaCadastro
                )) {
                    $motivoDuplicidade = $grandeAreaCadastro === null && $areaCadastro === null
                        ? 'Avaliador já possui cadastro para este edital; cadastro sem competência só é permitido quando não há nenhuma referência anterior.'
                        : 'Avaliador já cadastrado para este edital com a mesma competência.';
                    $resultado['inelegiveis'][] = [
                        'cpf' => $cpfOriginal,
                        'nome' => (string)$usuario->nome,
                        'editai_id' => $editaiId,
                        'motivo' => $motivoDuplicidade,
                    ];
                    continue;
                }

                $resultado['elegiveis'][] = [
                    'usuario_id' => (int)$usuario->id,
                    'cpf' => $cpfOriginal,
                    'nome' => (string)$usuario->nome,
                    'editai_id' => $editaiId,
                ];
            }
        }

        $resultado['totalElegiveis'] = count($resultado['elegiveis']);
        $resultado['totalInelegiveis'] = count($resultado['inelegiveis']);

        return $resultado;
    }

    protected function confirmarCadastroNova(array $dados, array $elegiveis): int
    {
        $avaliadorsTable = $this->fetchTable('Avaliadors');
        $anoAtual = date('Y');
        $entidadesSalvar = [];
        $totalSalvar = 0;

        foreach ($elegiveis as $item) {
            if ($this->existeCadastroNova(
                (int)$item['usuario_id'],
                (int)$item['editai_id'],
                !empty($dados['grandes_area_id']) ? (int)$dados['grandes_area_id'] : null,
                !empty($dados['area_id']) ? (int)$dados['area_id'] : null
            )) {
                continue;
            }

            $entidadesSalvar[] = $avaliadorsTable->newEntity([
                'usuario_id' => (int)$item['usuario_id'],
                'grandes_area_id' => !empty($dados['grandes_area_id']) ? (int)$dados['grandes_area_id'] : null,
                'area_id' => !empty($dados['area_id']) ? (int)$dados['area_id'] : null,
                'ano_convite' => $anoAtual,
                'ano_aceite' => $anoAtual,
                'tipo_avaliador' => 'N',
                'deleted' => 0,
                'editai_id' => (int)$item['editai_id'],
                'unidade_id' => null,
                'aceite' => 1,
            ]);
            $totalSalvar++;
        }

        $avaliadorsTable->getConnection()->transactional(function () use ($avaliadorsTable, $entidadesSalvar): void {
            foreach ($entidadesSalvar as $entidade) {
                $avaliadorsTable->saveOrFail($entidade);
            }
        });

        return $totalSalvar;
    }

    protected function analisarCadastroConvites(array $dados): array
    {
        $usuariosTable = $this->fetchTable('Usuarios');
        $cpfs = $this->normalizarCpfs((string)$dados['cpfs']);
        $editaisSelecionados = $this->normalizarEditaisSelecionados((array)$dados['editais']);
        sort($editaisSelecionados, SORT_NUMERIC);
        $editaisTexto = implode(',', $editaisSelecionados);
        $anoAtual = (int)date('Y');

        $resultado = [
            'processado' => true,
            'confirmado' => false,
            'elegiveis' => [],
            'inelegiveis' => [],
            'totalInformados' => count($cpfs),
            'totalElegiveis' => 0,
            'totalInelegiveis' => 0,
        ];

        if ($cpfs === [] || $editaisSelecionados === []) {
            return $resultado;
        }

        $cpfJaProcessado = [];
        foreach ($cpfs as $cpfInformado) {
            $cpfOriginal = $cpfInformado['original'];
            $cpfNumerico = $cpfInformado['numerico'];

            if (isset($cpfJaProcessado[$cpfNumerico])) {
                $resultado['inelegiveis'][] = [
                    'cpf' => $cpfOriginal,
                    'editais' => $editaisTexto,
                    'ano' => $anoAtual,
                    'motivo' => 'CPF repetido na própria listagem enviada.',
                ];
                continue;
            }
            $cpfJaProcessado[$cpfNumerico] = true;

            if (!$this->validaCPF($cpfNumerico)) {
                $resultado['inelegiveis'][] = [
                    'cpf' => $cpfOriginal,
                    'editais' => $editaisTexto,
                    'ano' => $anoAtual,
                    'motivo' => 'CPF inválido.',
                ];
                continue;
            }

            $usuario = $usuariosTable->find()
                ->select(['id', 'nome', 'cpf'])
                ->where(['Usuarios.cpf' => $cpfNumerico])
                ->first();

            if (!$usuario) {
                $resultado['inelegiveis'][] = [
                    'cpf' => $cpfOriginal,
                    'editais' => $editaisTexto,
                    'ano' => $anoAtual,
                    'motivo' => 'CPF não localizado na base de usuários.',
                ];
                continue;
            }

            if ($this->existeConviteAtivo((int)$usuario->id, $anoAtual, $editaisTexto)) {
                $resultado['inelegiveis'][] = [
                    'cpf' => $cpfOriginal,
                    'nome' => (string)$usuario->nome,
                    'editais' => $editaisTexto,
                    'ano' => $anoAtual,
                    'motivo' => 'Usuário já possui convite ativo neste ano para o mesmo conjunto de editais.',
                ];
                continue;
            }

            $resultado['elegiveis'][] = [
                'usuario_id' => (int)$usuario->id,
                'cpf' => $cpfOriginal,
                'nome' => (string)$usuario->nome,
                'editais' => $editaisTexto,
                'ano' => $anoAtual,
            ];
        }

        $resultado['totalElegiveis'] = count($resultado['elegiveis']);
        $resultado['totalInelegiveis'] = count($resultado['inelegiveis']);

        return $resultado;
    }

    protected function confirmarCadastroConvites(array $dados, array $elegiveis): int
    {
        $convitesTable = $this->fetchTable('Convites');
        $editaisSelecionados = $this->normalizarEditaisSelecionados((array)$dados['editais']);
        sort($editaisSelecionados, SORT_NUMERIC);
        $editaisTexto = implode(',', $editaisSelecionados);
        $anoAtual = (int)date('Y');
        $identity = $this->getIdentityAtual();
        $cadastradoPor = is_array($identity) ? (int)($identity['id'] ?? 0) : (int)($identity->id ?? 0);
        $entidadesSalvar = [];
        $totalSalvar = 0;

        foreach ($elegiveis as $item) {
            if ($this->existeConviteAtivo((int)$item['usuario_id'], $anoAtual, $editaisTexto)) {
                continue;
            }

            $entidadesSalvar[] = $convitesTable->newEntity([
                'usuario_id' => (int)$item['usuario_id'],
                'ano' => $anoAtual,
                'aceite' => null,
                'editais' => $editaisTexto,
                'cadastrado_por' => $cadastradoPor > 0 ? $cadastradoPor : null,
                'deletado_por' => null,
                'deleted' => null,
            ]);
            $totalSalvar++;
        }

        $convitesTable->getConnection()->transactional(function () use ($convitesTable, $entidadesSalvar): void {
            foreach ($entidadesSalvar as $entidade) {
                $convitesTable->saveOrFail($entidade);
            }
        });

        return $totalSalvar;
    }

    protected function existeCadastroRaic(int $usuarioId, int $editaiId, int $unidadeId): bool
    {
        return $this->fetchTable('Avaliadors')->find()
            ->where([
                'Avaliadors.usuario_id' => $usuarioId,
                'Avaliadors.editai_id' => $editaiId,
                'Avaliadors.unidade_id' => $unidadeId,
                'Avaliadors.deleted' => 0,
            ])
            ->count() > 0;
    }

    protected function existeCadastroNova(int $usuarioId, int $editaiId, ?int $grandeAreaId, ?int $areaId): bool
    {
        $condicoes = [
            'Avaliadors.usuario_id' => $usuarioId,
            'Avaliadors.editai_id' => $editaiId,
            'Avaliadors.deleted' => 0,
        ];

        if ($grandeAreaId === null && $areaId === null) {
            return $this->fetchTable('Avaliadors')->find()
                ->where($condicoes)
                ->count() > 0;
        }

        if ($grandeAreaId === null) {
            $condicoes['Avaliadors.grandes_area_id IS'] = null;
        } else {
            $condicoes['Avaliadors.grandes_area_id'] = $grandeAreaId;
        }

        if ($areaId === null) {
            $condicoes['Avaliadors.area_id IS'] = null;
        } else {
            $condicoes['Avaliadors.area_id'] = $areaId;
        }

        return $this->fetchTable('Avaliadors')->find()
            ->where($condicoes)
            ->count() > 0;
    }

    protected function existeConviteAtivo(int $usuarioId, int $ano, string $editais): bool
    {
        return $this->fetchTable('Convites')->find()
            ->where([
                'Convites.usuario_id' => $usuarioId,
                'Convites.ano' => $ano,
                'Convites.editais' => $editais,
                'Convites.deleted IS' => null,
            ])
            ->count() > 0;
    }

    protected function normalizarEditaisSelecionados(array $editais): array
    {
        $selecionados = [];
        foreach ($editais as $valor) {
            $id = (int)$valor;
            if ($id > 0) {
                $selecionados[] = $id;
            }
        }

        return array_values(array_unique($selecionados));
    }

    protected function normalizarCpfs(string $texto): array
    {
        $partes = preg_split('/[\s,;]+/', $texto) ?: [];
        $cpfs = [];

        foreach ($partes as $parte) {
            $parte = trim($parte);
            if ($parte === '') {
                continue;
            }

            $cpfNumerico = preg_replace('/\D+/', '', $parte) ?? '';
            if ($cpfNumerico === '') {
                continue;
            }

            $cpfs[] = [
                'original' => $parte,
                'numerico' => $cpfNumerico,
            ];
        }

        return $cpfs;
    }
}
