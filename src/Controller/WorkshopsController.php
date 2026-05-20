<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;

class WorkshopsController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->viewBuilder()->setLayout('admin');
    }

    public function painel()
    {
        $identity = $this->request->getAttribute('identity');
        $usuarioId = (int)($identity['id'] ?? 0);
        $minha = $this->fetchTable('Workshops')->find()
            ->contain([
                'Usuarios',
                'Orientadores',
                'Unidades',
                'ProjetoBolsistas',
                'Editais',
            ])
            ->where(['Workshops.deleted' => 0])
            ->andWhere(function ($exp) use ($usuarioId) {
                return $exp->or([
                    'Workshops.bolsista' => $usuarioId,
                    'Workshops.orientador' => $usuarioId,
                ]);
            })
            ->orderBy(['Workshops.id' => 'DESC'])
            ->all();

        $this->set(compact('minha'));
    }

    public function ver($id = null)
    {
        $workshop = $this->fetchTable('Workshops')->get($id, [
            'contain' => [
                'Usuarios',
                'ProjetoBolsistas' => [
                    'Programas',
                    'Editais' => ['Programas'],
                ],
                'Projetos' => ['Areas', 'Linhas'],
                'Editais' => ['Programas'],
                'Orientadores',
                'Cadastro',
                'Libera',
                'Unidades',
                'WorkshopHistoricos' => ['Usuarios'],
            ],
        ]);

        $ehAvaliadorPendente = $this->avaliadorTemAcessoAvaliacaoReferencia(['W'], (int)$workshop->id);

        if (!$this->request->getAttribute('identity')['yoda'] && !$ehAvaliadorPendente) {
            if ($this->request->getAttribute('identity')['jedi']) {
                $unidadesPermitidas = array_filter(array_map('trim', explode(',', (string)$this->request->getAttribute('identity')['jedi'])));
                if (!in_array((string)$workshop->unidade_id, $unidadesPermitidas, true)) {
                    $this->Flash->error('Esta Workshop é da gestão de outra unidade.');
                    return $this->redirect(['controller' => 'Workshops', 'action' => 'painel']);
                }
            } else {
                if (!in_array($this->request->getAttribute('identity')['id'], [$workshop->orientador, $workshop->bolsista])) {
                    $this->Flash->error('Somente a gestão, orientador e bolsista tem acesso a este módulo');
                    return $this->redirect(['controller' => 'Index', 'action' => 'index']);
                }
            }
        }

        $tipoAvaliacaoLista = 'W';

        $lista = $this->fetchTable('AvaliadorBolsistas')->find('all')
            ->contain([
                'Avaliadors' => 'Usuarios',
                'Criadores',
                'Deletadores',
            ])
            ->where([
                'AvaliadorBolsistas.bolsista' => (int)$id,
                'AvaliadorBolsistas.tipo' => $tipoAvaliacaoLista,
            ])
            ->orderBy([
                'AvaliadorBolsistas.deleted' => 'ASC',
                'AvaliadorBolsistas.ordem' => 'ASC',
            ]);

        $anexoRelatorio = null;

        $historicos = $this->fetchTable('WorkshopHistoricos')->find()
            ->contain(['Usuarios'])
            ->where(['WorkshopHistoricos.workshop_id' => (int)$workshop->id])
            ->orderBy(['WorkshopHistoricos.id' => 'DESC'])
            ->all();

        $editalReferencia = $workshop->editai ?? null;
        $editalEvento = null;
        if (strtoupper((string)($workshop->tipo_bolsa ?? '')) === 'R' && !empty($editalReferencia?->evento)) {
            $editalEvento = $this->fetchTable('Editais')->find()
                ->where(['Editais.id' => (int)$editalReferencia->evento])
                ->first();
        }

        $this->set(compact('workshop', 'lista', 'anexoRelatorio', 'historicos', 'editalReferencia', 'editalEvento'));
    }

    public function agendar($id = null)
    {
        $workshopsTable = $this->fetchTable('Workshops');
        $avaliadorBolsistasTable = $this->fetchTable('AvaliadorBolsistas');
        $avaliadorsTable = $this->fetchTable('Avaliadors');

        $workshop = $workshopsTable->get((int)$id, [
            'contain' => [
                'ProjetoBolsistas' => ['Orientadores', 'Usuarios'],
                'Orientadores',
                'Usuarios',
                'Unidades',
                'Editais',
            ],
        ]);

        $identity = $this->request->getAttribute('identity');
        $jediArray = [];
        if (!empty($identity['jedi'])) {
            $jediArray = array_filter(array_map('trim', explode(',', (string)$identity['jedi'])));
        }
        $ehYoda = !empty($identity['yoda']);
        $ehTi = in_array((int)($identity['id'] ?? 0), [1, 8088], true);
        $usuarioLogId = (int)($identity['id'] ?? 0);
        $ehJediDaUnidade = in_array((string)$workshop->unidade_id, $jediArray, true);

        if (!$ehYoda && !$ehJediDaUnidade && !$ehTi) {
            $this->Flash->error('Somente a Coordenação da Unidade e a Gestão de Fomento pode agendar a Workshop.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        if (!empty($workshop->deleted)) {
            $this->Flash->error('A Workshop está deletada.');
            return $this->redirect(['controller' => 'Workshops', 'action' => 'ver', $workshop->id]);
        }

        $agora = FrozenTime::now();

        if (strtoupper((string)$workshop->tipo_bolsa) === 'R' && !empty($workshop->projeto_bolsista)) {
            if (!empty($workshop->projeto_bolsista->deleted)) {
                $this->Flash->error('Houve desistência na solicitação de renovação.');
                return $this->redirect(['controller' => 'Workshops', 'action' => 'ver', $workshop->id]);
            }
            if ((string)($workshop->projeto_bolsista->situacao ?? '') === 'O') {
                $this->Flash->error('A inscrição de renovação não foi finalizada.');
                return $this->redirect(['controller' => 'Workshops', 'action' => 'ver', $workshop->id]);
            }
        }

        $jaAgendada = !empty($workshop->data_apresentacao);
        if ($jaAgendada && $workshop->data_apresentacao instanceof \Cake\I18n\FrozenDate) {
            $dataJaAgendada = $workshop->data_apresentacao->format('Y-m-d');
            if ($dataJaAgendada <= $agora->format('Y-m-d') && !$ehTi) {
                $this->Flash->error('A apresentação já passou ou ocorre hoje. Entre Em contato com a Gestão.');
                return $this->redirect(['controller' => 'Workshops', 'action' => 'ver', $workshop->id]);
            }
        }

        $tipoAvaliacao = 'W';
        $bancaAtiva = $avaliadorBolsistasTable->find()
            ->contain(['Avaliadors' => ['Usuarios']])
            ->where([
                'AvaliadorBolsistas.bolsista' => (int)$workshop->id,
                'AvaliadorBolsistas.tipo' => $tipoAvaliacao,
                'AvaliadorBolsistas.deleted' => 0,
            ])
            ->orderBy(['AvaliadorBolsistas.ordem' => 'ASC'])
            ->all()
            ->toList();

        $avaliador1Atual = null;
        $avaliador2Atual = null;
        $avaliador1Bloqueado = false;
        $avaliador2Bloqueado = false;
        $vinculoAtualPorOrdem = [];
        foreach ($bancaAtiva as $itemBanca) {
            if ((int)($itemBanca->coordenador ?? 0) === 1) {
                continue;
            }
            $ordemAtual = (int)($itemBanca->ordem ?? 0);
            if (!in_array($ordemAtual, [1, 2], true)) {
                continue;
            }
            $vinculoAtualPorOrdem[$ordemAtual] = $itemBanca;
            if ($ordemAtual === 1) {
                $avaliador1Atual = (int)$itemBanca->avaliador_id;
                $avaliador1Bloqueado = (string)($itemBanca->situacao ?? '') === 'F';
            } elseif ($ordemAtual === 2) {
                $avaliador2Atual = (int)$itemBanca->avaliador_id;
                $avaliador2Bloqueado = (string)($itemBanca->situacao ?? '') === 'F';
            }
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();

            if (empty($dados['data_apresentacao'])) {
                $this->Flash->error('Informe a data de apresentação.');
                return $this->redirect(['action' => 'agendar', $workshop->id]);
            }
            if ((new FrozenTime((string)$dados['data_apresentacao']))->year !== (int)date('Y')) {
                $this->Flash->error('A data de apresentação deve estar no ano atual.');
                return $this->redirect(['action' => 'agendar', $workshop->id]);
            }

            $dados['data_apresentacao'] = parent::acertaData((string)$dados['data_apresentacao']);
            $dados['tipo_apresentacao'] = strtoupper(trim((string)($dados['tipo_apresentacao'] ?? '')));
            $dados['local_apresentacao'] = trim((string)($dados['local_apresentacao'] ?? ''));

            if (!in_array($dados['tipo_apresentacao'], ['O', 'P'], true)) {
                $this->Flash->error('Selecione o tipo de apresentação.');
                return $this->redirect(['action' => 'agendar', $workshop->id]);
            }

            if ($dados['local_apresentacao'] === '') {
                $this->Flash->error('Informe o local de apresentação.');
                return $this->redirect(['action' => 'agendar', $workshop->id]);
            }

            $avaliador1 = (int)($dados['avaliador_1'] ?? 0);
            $avaliador2 = (int)($dados['avaliador_2'] ?? 0);
            if ($avaliador1 <= 0 || $avaliador2 <= 0) {
                $this->Flash->error('Informe os dois avaliadores da banca.');
                return $this->redirect(['action' => 'agendar', $workshop->id]);
            }

            if ($avaliador1 === $avaliador2) {
                $this->Flash->error('Não pode haver repetição na banca.');
                return $this->redirect(['action' => 'agendar', $workshop->id]);
            }

            foreach ([1 => $avaliador1, 2 => $avaliador2] as $ordem => $avaliadorSelecionado) {
                $vinculoAtual = $vinculoAtualPorOrdem[$ordem] ?? null;
                if (
                    $vinculoAtual !== null
                    && (string)($vinculoAtual->situacao ?? '') === 'F'
                    && (int)($vinculoAtual->avaliador_id ?? 0) !== $avaliadorSelecionado
                ) {
                    $this->Flash->error('Não é permitido substituir avaliador que já lançou nota.');
                    return $this->redirect(['action' => 'agendar', $workshop->id]);
                }
            }

            $avaliadoresSelecionados = $avaliadorsTable->find()
                ->select(['Avaliadors.id', 'Avaliadors.usuario_id', 'Usuarios.nome'])
                ->leftJoinWith('Usuarios')
                ->where([
                    'Avaliadors.id IN' => [$avaliador1, $avaliador2],
                    'Avaliadors.editai_id' => (int)$workshop->editai_id,
                    'Avaliadors.deleted' => 0,
                ])
                ->enableHydration(false)
                ->all()
                ->indexBy('id')
                ->toArray();

            if (count($avaliadoresSelecionados) !== 2) {
                $this->Flash->error('Um ou mais avaliadores selecionados não estão mais disponíveis.');
                return $this->redirect(['action' => 'agendar', $workshop->id]);
            }

            foreach ($avaliadoresSelecionados as $avaliadorSelecionado) {
                $usuarioAvaliadorId = (int)($avaliadorSelecionado['usuario_id'] ?? 0);
                if ($usuarioAvaliadorId === (int)$workshop->orientador) {
                    $this->Flash->error('O orientador não pode fazer parte da banca.');
                    return $this->redirect(['action' => 'agendar', $workshop->id]);
                }
                if ($usuarioAvaliadorId > 0 && $usuarioAvaliadorId === (int)($workshop->bolsista ?? 0)) {
                    $this->Flash->error('O bolsista não pode fazer parte da banca.');
                    return $this->redirect(['action' => 'agendar', $workshop->id]);
                }
            }

            $historicoAlteracao = $jaAgendada ? 'Workshop reagendada.' : 'Workshop agendada.';
            $historicoJustificativa = $jaAgendada
                ? 'Reagendamento com atualização da apresentação e vinculação da banca'
                : 'Agendamento padrão e vinculação de banca';

            try {
                $workshopsTable->getConnection()->transactional(function () use (
                    $workshopsTable,
                    $avaliadorBolsistasTable,
                    $workshop,
                    $dados,
                    $tipoAvaliacao,
                    $avaliador1,
                    $avaliador2,
                    $avaliadoresSelecionados,
                    $vinculoAtualPorOrdem,
                    $historicoAlteracao,
                    $historicoJustificativa,
                    $usuarioLogId
                ): void {
                    $workshop->data_apresentacao = $dados['data_apresentacao'];
                    $workshop->tipo_apresentacao = $dados['tipo_apresentacao'];
                    $workshop->local_apresentacao = $dados['local_apresentacao'];

                    if (!$workshopsTable->save($workshop)) {
                        throw new \RuntimeException('Erro ao salvar o agendamento da Workshop.');
                    }

                    $dadosBanca = [
                        ['avaliador_id' => $avaliador1, 'ordem' => 1],
                        ['avaliador_id' => $avaliador2, 'ordem' => 2],
                    ];
                    foreach ($dadosBanca as $itemBanca) {
                        $ordem = (int)$itemBanca['ordem'];
                        $avaliadorId = (int)$itemBanca['avaliador_id'];
                        $vinculoAtual = $vinculoAtualPorOrdem[$ordem] ?? null;

                        if ($vinculoAtual !== null && (int)($vinculoAtual->avaliador_id ?? 0) === $avaliadorId) {
                            continue;
                        }

                        if ($vinculoAtual !== null) {
                            $vinculoAtual->deleted = 1;
                            $vinculoAtual->deletado_por = $usuarioLogId > 0 ? $usuarioLogId : null;
                            $vinculoAtual->deletado_em = \Cake\I18n\DateTime::now();
                            if (!$avaliadorBolsistasTable->save($vinculoAtual)) {
                                throw new \RuntimeException('Erro ao inativar o vínculo anterior da banca da Workshop.');
                            }
                        }

                        $novo = $avaliadorBolsistasTable->newEmptyEntity();
                        $novo->bolsista = (int)$workshop->id;
                        $novo->workshop_id = (int)$workshop->id;
                        $novo->tipo = $tipoAvaliacao;
                        $novo->situacao = 'E';
                        $novo->editai_id = (int)$workshop->editai_id;
                        $novo->ano = (int)date('Y');
                        $novo->coordenador = 0;
                        $novo->avaliador_id = $avaliadorId;
                        $novo->usuario_id = (int)($avaliadoresSelecionados[$avaliadorId]['usuario_id'] ?? 0);
                        $novo->ordem = $ordem;
                        $novo->deleted = 0;
                        $novo->criado_por = $usuarioLogId > 0 ? $usuarioLogId : null;
                        $novo->deletado_por = null;
                        $novo->deletado_em = null;

                        if (!$avaliadorBolsistasTable->save($novo)) {
                            throw new \RuntimeException('Erro ao salvar a banca da Workshop.');
                        }
                    }

                    if (!$this->historico((int)$workshop->id, $historicoAlteracao, $historicoAlteracao, $historicoJustificativa)) {
                        throw new \RuntimeException('Erro ao gravar o histórico do agendamento.');
                    }
                });

                $this->Flash->success($jaAgendada ? 'A Workshop foi reagendada com sucesso.' : 'A Workshop foi agendada com sucesso.');
                return $this->redirect(['controller' => 'Workshops', 'action' => 'ver', $workshop->id]);
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - Agendar Workshop',
                    'Houve um erro no agendamento da Workshop. Tente novamente.'
                );
                return $this->redirect(['action' => 'agendar', $workshop->id]);
            }
        }

        $usuariosBloqueados = array_values(array_filter(array_unique([
            (int)$workshop->orientador,
            (int)($workshop->bolsista ?? 0),
        ])));

        $avaliadores = $avaliadorsTable->find('list', [
            'keyField' => 'id',
            'valueField' => function ($row) {
                $nome = (string)($row->usuario->nome ?? 'Não informado');
                $sigla = (string)($row->usuario->unidade->sigla ?? 'Unidade não informada');
                $grandeArea = (string)($row->grandes_area->nome ?? 'Não informada');
                $area = (string)($row->area->nome ?? 'Não informada');
                return $nome . ' (' . $sigla . ') - ' . $grandeArea . ' - ' . $area;
            },
        ])
            ->contain(['GrandesAreas', 'Areas', 'Usuarios' => ['Unidades']])
            ->where([
                'Avaliadors.editai_id' => (int)$workshop->editai_id,
                'Avaliadors.deleted' => 0,
                'Avaliadors.unidade_id' => (int)$workshop->unidade_id,
            ])
            ->where(empty($usuariosBloqueados) ? [] : ['Avaliadors.usuario_id NOT IN' => $usuariosBloqueados])
            ->orderBy(['Usuarios.nome' => 'ASC'])
            ->all();

        $this->set(compact(
            'workshop',
            'avaliadores',
            'avaliador1Atual',
            'avaliador2Atual',
            'jaAgendada',
            'avaliador1Bloqueado',
            'avaliador2Bloqueado',
            'bancaAtiva'
        ));
    }

    public function alterarAvaliador($id = null)
    {
        $vinculo = $this->fetchTable('AvaliadorBolsistas')->find()
            ->where([
                'AvaliadorBolsistas.id' => (int)$id,
                'AvaliadorBolsistas.tipo' => 'W',
            ])
            ->first();

        if (!$vinculo) {
            $this->Flash->error('Vínculo de avaliador não encontrado.');
            return $this->redirect(['action' => 'painel']);
        }

        $workshopId = (int)($vinculo->workshop_id ?? $vinculo->bolsista ?? 0);
        if ((int)($vinculo->deleted ?? 0) === 1) {
            $this->Flash->error('Este avaliador já está desvinculado.');
            return $this->redirect(['controller' => 'Workshops', 'action' => 'ver', $workshopId]);
        }
        if ((string)($vinculo->situacao ?? '') === 'F') {
            $this->Flash->error('Não é permitido alterar avaliador que já lançou nota.');
            return $this->redirect(['controller' => 'Workshops', 'action' => 'ver', $workshopId]);
        }

        return $this->redirect(['controller' => 'Workshops', 'action' => 'agendar', $workshopId]);
    }

    public function editar($id = null)
    {
        $this->Flash->error('Função não permitida.');
        return $this->redirect(['controller' => 'Index', 'action' => 'index']);

        $workshopsTable = $this->fetchTable('Workshops');
        $workshop = $workshopsTable->get((int)$id, [
            'contain' => [
                'Usuarios',
                'Orientadores',
                'Unidades',
                'Editais',
            ],
        ]);

        $identity = $this->request->getAttribute('identity');
        $ehYoda = !empty($identity['yoda']);

        if (!$ehYoda) {
            $this->Flash->error('Somente a Gestão de Fomento pode editar a Workshop.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        if (!empty($workshop->deleted)) {
            $this->Flash->error('A Workshop está deletada.');
            return $this->redirect(['controller' => 'Workshops', 'action' => 'ver', $workshop->id]);
        }

        if (!empty($workshop->data_apresentacao)) {
            $hoje = FrozenTime::now()->format('Y-m-d');
            $dataApresentacao = $workshop->data_apresentacao->format('Y-m-d');
            if ($dataApresentacao <= $hoje) {
                $dataApresentacaoTela = $workshop->data_apresentacao->i18nFormat('dd/MM/YYYY');
                $dataLimiteEdicao = $workshop->data_apresentacao->modify('-1 day')->i18nFormat('dd/MM/YYYY');
                $this->Flash->error(
                    'A edição desta Workshop não é mais permitida. A apresentação está marcada para ' . $dataApresentacaoTela . ' e a edição só era permitida até ' . $dataLimiteEdicao . '.'
                );
                return $this->redirect(['controller' => 'Workshops', 'action' => 'ver', $workshop->id]);
            }
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $this->Flash->info('Nenhuma alteração foi informada.');
            return $this->redirect(['controller' => 'Workshops', 'action' => 'ver', $workshop->id]);
        }

        $this->set(compact('workshop'));
    }

    public function liberacertificado($id = null)
    {
        $workshopsTable = $this->fetchTable('Workshops');
        $workshop = $workshopsTable->get($id);

        if (!$this->request->getAttribute('identity')['yoda']) {
            if ($this->request->getAttribute('identity')['jedi']) {
                $unidadesPermitidas = array_filter(array_map('trim', explode(',', (string)$this->request->getAttribute('identity')['jedi'])));
                if (!in_array((string)$workshop->unidade_id, $unidadesPermitidas, true)) {
                    $this->Flash->error('Esta Workshop é da gestão de outra unidade.');
                    return $this->redirect(['controller' => 'Index', 'action' => 'index']);
                }
            } else {
                $this->Flash->error('Somente a Coordenação da Unidade e a Gestão de Fomento pode liberar certificado da Workshop.');
                return $this->redirect(['controller' => 'Index', 'action' => 'index']);
            }
        }

        if ((int)$workshop->deleted === 1) {
            $this->Flash->error('A Workshop foi deletada.');
            return $this->redirect(['controller' => 'Workshops', 'action' => 'ver', $workshop->id]);
        }

        if ($workshop->data_apresentacao == null) {
            $this->Flash->error('A Workshop ainda não foi agendada.');
            return $this->redirect(['controller' => 'Workshops', 'action' => 'ver', $workshop->id]);
        }

        if (date('Ymd') <= $workshop->data_apresentacao->i18nFormat('YMMdd')) {
            $this->Flash->error('A Workshop ainda não ocorreu. A presença só poderá ser registrada após o evento.');
            return $this->redirect(['controller' => 'Workshops', 'action' => 'ver', $workshop->id]);
        }

        if ($workshop->presenca === 'S') {
            $this->Flash->error('O certificado já estava liberado.');
            return $this->redirect(['controller' => 'Workshops', 'action' => 'ver', $workshop->id]);
        }

        $this->request->allowMethod(['post', 'put']);

        $workshop->presenca = 'S';
        $workshop->usuario_libera = $this->request->getAttribute('identity')['id'];
        $workshop->data_liberacao = date('Y-m-d H:i:s');

        if ($workshopsTable->save($workshop)) {
            $this->historico($workshop->id, 'Certificado Liberado', 'Certificado Liberado', 'Usuário responsável registrou a presença do aluno');
            $this->Flash->success('Certificado liberado.');
            return $this->redirect(['controller' => 'Workshops', 'action' => 'ver', $workshop->id]);
        }

        $this->Flash->error('Houve um erro durante a liberação. Tente novamente.');
        return $this->redirect(['controller' => 'Workshops', 'action' => 'ver', $workshop->id]);
    }

    public function deletar($id = null)
    {
        if (!$this->ehTi()) {
            $this->Flash->error('A exclusão da Workshop é restrita à TI.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $workshopsTable = $this->fetchTable('Workshops');
        $workshop = $workshopsTable->get((int)$id, [
            'contain' => ['Usuarios', 'Orientadores', 'Unidades', 'Editais'],
        ]);

        if ((int)($workshop->deleted ?? 0) === 1) {
            $this->Flash->info('A Workshop já estava deletada.');
            return $this->redirect(['controller' => 'Workshops', 'action' => 'ver', $workshop->id]);
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $justificativa = trim((string)($this->request->getData('justificativa_cancelamento') ?? ''));
            if ($justificativa === '') {
                $this->Flash->error('Informe a justificativa da exclusão.');
                return $this->redirect(['action' => 'deletar', $workshop->id]);
            }

            try {
                $workshopsTable->getConnection()->transactional(function () use ($workshopsTable, $workshop, $justificativa): void {
                    $workshop->deleted = 1;
                    if (!$workshopsTable->save($workshop)) {
                        throw new \RuntimeException('Erro ao deletar a Workshop.');
                    }

                    if (!$this->historico((int)$workshop->id, 'deletada', 'deletada', 'Exclusão da Workshop pela TI: ' . $justificativa)) {
                        throw new \RuntimeException('Erro ao gravar o histórico da exclusão da Workshop.');
                    }
                });

                $this->Flash->success('A Workshop foi deletada com sucesso.');
                return $this->redirect(['controller' => 'Workshops', 'action' => 'ver', $workshop->id]);
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - Exclusão de Workshop',
                    'Houve um erro ao deletar a Workshop. Tente novamente.'
                );
                return $this->redirect(['action' => 'deletar', $workshop->id]);
            }
        }

        $this->set(compact('workshop'));
    }

    public function historico($id, $original, $atual, $just, bool $throw = true): bool
    {
        $workshopHistoricos = $this->fetchTable('WorkshopHistoricos');
        $novo = $workshopHistoricos->newEmptyEntity();
        $novo->workshop_id = $id;
        $novo->usuario_id = $this->request->getAttribute('identity')->id;
        $novo->alteracao = $original;
        $novo->justificativa = $just;

        $nv = $workshopHistoricos->save($novo);
        if (!$nv) {
            $this->Flash->error('Houve um erro na gravação. Tente novamente');
            return false;
        }

        return true;
    }


    public function voluntarias($ed = null)
    {
        $this->Flash->error('Função não permitida.');
        return $this->redirect(['controller' => 'Index', 'action' => 'index']);

        $perfil = $this->perfilVoluntarias();
        if (!$perfil['permitido']) {
            $this->Flash->error('As inscrições não são permitidas para seu perfil ou alguma condição não foi atendida');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $editaisAbertos = $this->editaisAbertosVoluntarias();
        if (empty($editaisAbertos)) {
            $this->Flash->error('Não existe Workshop em período de inscrição atualmente');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $unidadesBase = $this->unidadesBaseVoluntarias($perfil);
        if (empty($unidadesBase)) {
            $this->Flash->error('Unidades restritas para o seu perfil');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        [$editais, $mapaUnidadesPorEdital] = $this->opcoesEditaisVoluntarias($editaisAbertos, $unidadesBase);
        if (empty($editais)) {
            $this->Flash->error('Não existe edital Workshop disponível para o seu perfil e unidades habilitadas.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $workshopTabela = $this->fetchTable('Workshops');
        $workshop = $workshopTabela->newEmptyEntity();

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();

            try {
                $editalSelecionadoId = $this->validarEditalVoluntarias($dados, $editais);
                $unidadesPermitidasEdital = $mapaUnidadesPorEdital[$editalSelecionadoId] ?? [];
                [$orientadorPreSelecionado, $unidadeSelecionadaId] = $this->resolverUnidadeVoluntarias($dados, $perfil, $unidadesPermitidasEdital);

                $workshopCriado = null;
                $bolsistaNome = '';
                $workshopTabela->getConnection()->transactional(function () use ($dados, $perfil, $editalSelecionadoId, $unidadeSelecionadaId, $orientadorPreSelecionado, $workshopTabela, &$workshopCriado, &$bolsistaNome): void {
                    $bolsista = $this->resolverBolsistaVoluntarias($dados, $perfil, $editalSelecionadoId);
                    $orientador = $this->resolverOrientadorVoluntarias($dados, $perfil, $bolsista, $orientadorPreSelecionado);
                    $bolsistaNome = (string)($bolsista->nome ?? '');

                    $workshop = $workshopTabela->newEmptyEntity();
                    $workshop->bolsista = (int)$bolsista->id;
                    $workshop->orientador = (int)$orientador->id;
                    $workshop->deleted = 0;
                    $workshop->unidade_id = $unidadeSelecionadaId;
                    $workshop->editai_id = $editalSelecionadoId;
                    $workshop->tipo_bolsa = 'Z';
                    $workshop->usuario_cadastro = (int)$perfil['usuario_id'];

                    if (!$workshopTabela->save($workshop)) {
                        throw new \RuntimeException('Erro ao salvar a Workshop. A Workshop não foi gravada');
                    }

                    if (!$this->historico((int)$workshop->id, 'criação', 'criação', 'cadastro manual da Workshop')) {
                        throw new \RuntimeException('Erro ao gravar o histórico da Workshop manual.');
                    }

                    $workshopCriado = $workshop;
                });

                if ($workshopCriado === null || empty($workshopCriado->id)) {
                    throw new \RuntimeException('Erro ao recuperar a Workshop cadastrada.');
                }

                $this->Flash->success('Workshop CADASTRADA COM SUCESSO PARA O ALUNO ' . $bolsistaNome . ', sob numero #' . $workshopCriado->id);
                return $this->redirect(['action' => 'voluntarias']);
            } catch (\DomainException $e) {
                $this->Flash->error($e->getMessage());
                return $this->redirect(['action' => 'voluntarias']);
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - Gravação de Workshop voluntária',
                    'Houve um erro na gravação. Tente novamente.'
                );
                return $this->redirect(['action' => 'voluntarias']);
            }
        }

        $unidades = $unidadesBase;
        $ehOrientadorSemCoordenacao = $perfil['orientador_sem_coordenacao'];
        $ehAlunoSemCoordenacao = $perfil['aluno_sem_coordenacao'];
        $mostrarBlocoBolsista = !$ehAlunoSemCoordenacao;
        $mostrarBlocoOrientador = !$ehOrientadorSemCoordenacao;
        $mostrarCampoUnidade = !$ehAlunoSemCoordenacao;

        $this->set(compact(
            'unidades',
            'workshop',
            'editais',
            'mapaUnidadesPorEdital',
            'ehOrientadorSemCoordenacao',
            'ehAlunoSemCoordenacao',
            'mostrarBlocoBolsista',
            'mostrarBlocoOrientador',
            'mostrarCampoUnidade'
        ));
    }

    public function bancas()
    {
        if (!$this->ehYoda()) {
            $this->Flash->error('Área restrita à Gestão de Fomento.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $grandesAreas = $this->fetchTable('GrandesAreas')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where(['GrandesAreas.id >' => 9])
            ->orderBy(['GrandesAreas.nome' => 'ASC'])
            ->toArray();

        $periodos = [
            'M' => 'Manhã',
            'T' => 'Tarde',
            'A' => 'Ambos',
        ];

        $filtros = [
            'grandes_areas_id' => (int)$this->request->getQuery('grandes_areas_id', 0),
            'data' => (string)$this->request->getQuery('data', ''),
            'periodo' => strtoupper((string)$this->request->getQuery('periodo', '')),
        ];

        if (!isset($grandesAreas[$filtros['grandes_areas_id']])) {
            $filtros['grandes_areas_id'] = 0;
        }
        if (!isset($periodos[$filtros['periodo']])) {
            $filtros['periodo'] = '';
        }

        $query = $this->fetchTable('Bancas')->find()
            ->contain([
                'GrandesAreas',
                'Editais',
                'BancaUsuarios' => [
                    'Avaliadors' => ['Usuarios'],
                ],
            ])
            ->where(['Bancas.deleted IS' => null])
            ->orderBy(['Bancas.data' => 'DESC', 'Bancas.id' => 'DESC']);

        if ($filtros['grandes_areas_id'] > 0) {
            $query->where(['Bancas.grandes_areas_id' => $filtros['grandes_areas_id']]);
        }
        if ($filtros['data'] !== '') {
            $query->where(['Bancas.data' => $filtros['data']]);
        }
        if ($filtros['periodo'] !== '') {
            $query->where(['Bancas.periodo' => $filtros['periodo']]);
        }

        $bancas = $query->all();

        $this->set(compact('bancas', 'grandesAreas', 'periodos', 'filtros'));
    }

    public function adicionarBanca()
    {
        if (!$this->ehYoda()) {
            $this->Flash->error('Área restrita à Gestão de Fomento.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $bancasTable = $this->fetchTable('Bancas');
        $banca = $bancasTable->newEmptyEntity();
        $opcoes = $this->opcoesFormularioBancaWorkshop();
        $avaliadoresPost = [
            $this->request->getData('avaliador_1'),
            $this->request->getData('avaliador_2'),
            $this->request->getData('avaliador_3'),
        ];

        $dados = [
            'grandes_areas_id' => (int)$this->request->getData('grandes_areas_id', 0),
            'nome' => trim((string)$this->request->getData('nome', '')),
            'data' => (string)$this->request->getData('data', ''),
            'periodo' => strtoupper((string)$this->request->getData('periodo', '')),
            'editai_id' => (int)$this->request->getData('editai_id', 0),
            'evento' => (int)$this->request->getData('evento', 0),
            'avaliadores' => array_values(array_filter(array_map('intval', (array)$avaliadoresPost))),
        ];
        $avaliadoresDisponiveis = [];

        if (!empty($dados['evento'])) {
            $avaliadoresDisponiveis = $this->avaliadoresDisponiveisBancaWorkshop($dados['evento']);
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $erros = $this->validarDadosBancaWorkshop($dados, $opcoes, $avaliadoresDisponiveis);

            if ($erros !== []) {
                $this->Flash->error(implode('<br>', $erros), ['escape' => false]);
            } else {
                try {
                    $bancasTable->getConnection()->transactional(function () use ($bancasTable, $banca, $dados): void {
                        $bancaUsuariosTable = $this->fetchTable('BancaUsuarios');
                        $banca = $bancasTable->patchEntity($banca, [
                            'grandes_areas_id' => $dados['grandes_areas_id'],
                            'nome' => $dados['nome'],
                            'data' => $dados['data'],
                            'periodo' => $dados['periodo'],
                            'editai_id' => $dados['editai_id'],
                            'evento' => $dados['evento'],
                        ]);
                        $bancasTable->saveOrFail($banca);

                        foreach ($dados['avaliadores'] as $avaliadorId) {
                            $vinculo = $bancaUsuariosTable->newEntity([
                                'banca_id' => (int)$banca->id,
                                'avaliador_id' => (int)$avaliadorId,
                            ]);
                            $bancaUsuariosTable->saveOrFail($vinculo);
                        }
                    });

                    $this->Flash->success('Banca Workshop cadastrada com sucesso.');
                    return $this->redirect(['action' => 'bancas']);
                } catch (\Throwable $e) {
                    $this->flashFriendlyException(
                        $e,
                        'Erro no Sistema - Banca Workshop',
                        'Não foi possível cadastrar a banca Workshop.'
                    );
                }
            }
        }

        $this->set(compact('banca', 'dados', 'opcoes', 'avaliadoresDisponiveis'));
    }

    protected function opcoesFormularioBancaWorkshop(): array
    {
        $grandesAreas = $this->fetchTable('GrandesAreas')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where(['GrandesAreas.id >' => 9])
            ->orderBy(['GrandesAreas.nome' => 'ASC'])
            ->toArray();

        $eventos = $this->fetchTable('Editais')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where([
                'Editais.programa_id' => 8,
                'Editais.deleted' => 0,
                'Editais.inicio_avaliar < NOW()',
                'Editais.fim_avaliar > NOW()',
            ])
            ->orderBy(['Editais.nome' => 'ASC'])
            ->toArray();

        $avaliadoresPorEvento = [];
        foreach (array_keys($eventos) as $eventoId) {
            $avaliadoresPorEvento[(int)$eventoId] = $this->avaliadoresDisponiveisBancaWorkshop((int)$eventoId);
        }

        $editaisWorkshop = $this->fetchTable('Editais')->find()
            ->select(['id', 'nome', 'evento'])
            ->where([
                'Editais.deleted' => 0,
                'Editais.evento IN' => array_keys($eventos ?: [0 => 0]),
            ])
            ->orderBy(['Editais.nome' => 'ASC'])
            ->all();
        $editais = [];
        $eventosPorEdital = [];
        foreach ($editaisWorkshop as $editalWorkshop) {
            $editais[(int)$editalWorkshop->id] = (string)$editalWorkshop->nome;
            $eventosPorEdital[(int)$editalWorkshop->id] = (int)$editalWorkshop->evento;
        }

        return [
            'grandesAreas' => $grandesAreas,
            'editais' => $editais,
            'eventosPorEdital' => $eventosPorEdital,
            'eventos' => $eventos,
            'avaliadoresPorEvento' => $avaliadoresPorEvento,
            'periodos' => [
                'M' => 'Manhã',
                'T' => 'Tarde',
                'A' => 'Ambos',
            ],
        ];
    }

    protected function validarDadosBancaWorkshop(array $dados, array $opcoes, array $avaliadoresDisponiveis): array
    {
        $erros = [];
        if (empty($dados['grandes_areas_id']) || !isset($opcoes['grandesAreas'][$dados['grandes_areas_id']])) {
            $erros[] = 'Selecione uma grande área válida para Workshop.';
        }
        if ($dados['nome'] === '') {
            $erros[] = 'Informe o nome de identificação da banca.';
        }
        if ($dados['data'] === '') {
            $erros[] = 'Informe a data da banca.';
        }
        if (empty($dados['periodo']) || !isset($opcoes['periodos'][$dados['periodo']])) {
            $erros[] = 'Selecione o período da banca.';
        }
        if (empty($dados['editai_id']) || !isset($opcoes['editais'][$dados['editai_id']])) {
            $erros[] = 'Selecione o edital de origem.';
        }
        if (empty($dados['evento']) || !isset($opcoes['eventos'][$dados['evento']])) {
            $erros[] = 'Selecione o edital do evento Workshop com avaliação aberta.';
        }
        if (
            !empty($dados['editai_id'])
            && !empty($dados['evento'])
            && (int)($opcoes['eventosPorEdital'][$dados['editai_id']] ?? 0) !== (int)$dados['evento']
        ) {
            $erros[] = 'O edital selecionado não está vinculado ao evento Workshop informado.';
        }

        $avaliadores = array_values(array_unique(array_map('intval', $dados['avaliadores'])));
        if (count($avaliadores) !== count($dados['avaliadores'])) {
            $erros[] = 'Não pode haver repetição de avaliador na banca.';
        }
        if (count($avaliadores) === 0) {
            $erros[] = 'Selecione ao menos um avaliador para a banca.';
        }
        if (count($avaliadores) > 3) {
            $erros[] = 'Selecione no máximo três avaliadores para a banca.';
        }

        $idsDisponiveis = array_map('intval', array_keys($avaliadoresDisponiveis));
        foreach ($avaliadores as $avaliadorId) {
            if (!in_array($avaliadorId, $idsDisponiveis, true)) {
                $erros[] = 'Há avaliador selecionado que não está disponível para o Workshop.';
                break;
            }
        }

        return $erros;
    }

    protected function avaliadoresDisponiveisBancaWorkshop(int $eventoId): array
    {
        $avaliadores = $this->fetchTable('Avaliadors')->find()
            ->contain(['Usuarios'])
            ->where([
                'Avaliadors.editai_id' => $eventoId,
                'Avaliadors.tipo_avaliador' => 'R',
                'Avaliadors.deleted' => 0,
                'Avaliadors.aceite' => 1,
            ])
            ->orderBy(['Usuarios.nome' => 'ASC'])
            ->all();

        $lista = [];
        foreach ($avaliadores as $avaliador) {
            $nome = trim((string)($avaliador->usuario->nome ?? ''));
            $lista[(int)$avaliador->id] = $nome !== '' ? $nome : ('Avaliador #' . (int)$avaliador->id);
        }

        return $lista;
    }

    protected function perfilVoluntarias(): array
    {
        $identity = $this->request->getAttribute('identity');
        $valor = static function (string $campo) use ($identity) {
            if (is_array($identity) || $identity instanceof \ArrayAccess) {
                return $identity[$campo] ?? null;
            }

            return $identity->{$campo} ?? null;
        };

        $usuarioId = (int)($valor('id') ?? 0);
        $jediRaw = trim((string)($valor('jedi') ?? ''));
        $padauanRaw = trim((string)($valor('padauan') ?? ''));
        $ehYoda = !empty($valor('yoda'));
        $ehTi = in_array($usuarioId, [1, 8088], true);
        $ehJedi = !$ehYoda && !$ehTi && !in_array($jediRaw, ['', '0', '-0'], true);
        $padauanVazio = in_array($padauanRaw, ['', '0', '-0'], true);
        $escolaridadeId = (int)($valor('escolaridade_id') ?? 0);
        $vinculoId = (int)($valor('vinculo_id') ?? 0);
        $unidadeUsuarioId = (int)($valor('unidade_id') ?? 0);

        $orientadorSemCoordenacao = !$ehYoda
            && !$ehTi
            && !$ehJedi
            && $padauanVazio
            && $escolaridadeId === 10
            && $vinculoId !== 7;
        $alunoSemCoordenacao = !$ehYoda
            && !$ehTi
            && !$ehJedi
            && $padauanVazio
            && $escolaridadeId === 7;
        $grupo = null;
        if ($ehYoda) {
            $grupo = 'yoda';
        } elseif ($ehTi) {
            $grupo = 'ti';
        } elseif ($ehJedi) {
            $grupo = 'jedi';
        } elseif ($orientadorSemCoordenacao) {
            $grupo = 'orientador';
        } elseif ($alunoSemCoordenacao) {
            $grupo = 'bolsista';
        }
        $permitido = $grupo !== null;

        return [
            'bolsista' => $usuarioId,
            'grupo' => $grupo,
            'yoda' => $ehYoda,
            'ti' => $ehTi,
            'jedi' => $ehJedi,
            'coordenacao' => $ehJedi,
            'orientador_sem_coordenacao' => $orientadorSemCoordenacao,
            'aluno_sem_coordenacao' => $alunoSemCoordenacao,
            'permitido' => $permitido,
            'jedi_ids' => array_values(array_filter(array_map('intval', explode(',', $jediRaw)))),
            'unidade_id' => $unidadeUsuarioId,
        ];
    }

    protected function editaisAbertosVoluntarias()
    {
        return $this->fetchTable('Editais')->find()
            ->select(['id', 'nome', 'unidades_permitidas'])
            ->where([
                'inicio_inscricao < NOW()',
                'fim_inscricao > NOW()',
                'origem' => 'V',
            ])
            ->orderBy(['nome' => 'ASC'])
            ->all()
            ->toArray();
    }

    protected function unidadesBaseVoluntarias(array $perfil): array
    {
        $query = $this->fetchTable('Unidades')->find('list', [
            'keyField' => 'id',
            'valueField' => 'sigla',
            'limit' => 220,
        ])
            ->where(['Unidades.deleted' => 0])
            ->orderBy(['Unidades.sigla' => 'ASC']);

        if ($perfil['orientador_sem_coordenacao']) {
            if ((int)$perfil['unidade_id'] <= 0) {
                return [];
            }
            $query->where(['Unidades.id' => (int)$perfil['unidade_id']]);
        } elseif ($perfil['coordenacao']) {
            if (empty($perfil['jedi_ids'])) {
                return [];
            }
            $query->where(['Unidades.id IN' => $perfil['jedi_ids']]);
        }

        return $query->toArray();
    }

    protected function opcoesEditaisVoluntarias($editaisAbertos, array $unidadesBase): array
    {
        $editais = [];
        $mapaUnidadesPorEdital = [];

        foreach ($editaisAbertos as $editalAberto) {
            $permitidas = array_values(array_filter(array_map(
                'intval',
                explode(',', (string)($editalAberto->unidades_permitidas ?? ''))
            )));
            $unidadesPermitidasEdital = !empty($permitidas)
                ? array_intersect_key($unidadesBase, array_flip($permitidas))
                : $unidadesBase;

            if (empty($unidadesPermitidasEdital)) {
                continue;
            }

            $editais[(int)$editalAberto->id] = (string)$editalAberto->nome;
            $mapaUnidadesPorEdital[(int)$editalAberto->id] = $unidadesPermitidasEdital;
        }

        return [$editais, $mapaUnidadesPorEdital];
    }

    protected function validarEditalVoluntarias(array $dados, array $editais): int
    {
        $editalId = (int)($dados['editai_id'] ?? 0);
        if ($editalId <= 0 || !isset($editais[$editalId])) {
            throw new \DomainException('Selecione um edital Workshop válido.');
        }

        return $editalId;
    }

    protected function resolverUnidadeVoluntarias(array $dados, array $perfil, array $unidadesPermitidasEdital): array
    {
        if ($perfil['aluno_sem_coordenacao']) {
            $orientador = $this->buscarOrientadorAlunoVoluntarias($dados);
            $unidadeId = (int)($orientador->unidade_id ?? 0);
            if ($unidadeId <= 0 || !isset($unidadesPermitidasEdital[$unidadeId])) {
                throw new \DomainException('Workshop NÃO CADASTRADA: A unidade do orientador não está habilitada para este edital Workshop.');
            }

            return [$orientador, $unidadeId];
        }

        $unidadeId = (int)($dados['unidade_id'] ?? 0);
        if ($unidadeId <= 0 || !isset($unidadesPermitidasEdital[$unidadeId])) {
            throw new \DomainException('Selecione uma unidade válida para o edital informado.');
        }

        return [null, $unidadeId];
    }

    protected function buscarOrientadorAlunoVoluntarias(array $dados)
    {
        $cpf = preg_replace('/[^0-9]/', '', (string)($dados['cpf_orientador'] ?? ''));
        if (!parent::validaCPF($cpf)) {
            throw new \DomainException('O formato do CPF do orientador é inválido');
        }

        $orientador = $this->fetchTable('Usuarios')->find()->where(['cpf' => $cpf])->first();
        if (!$orientador) {
            throw new \DomainException('Workshop NÃO CADASTRADA: O CPF do orientador não foi localizado.');
        }

        return $orientador;
    }

    protected function resolverBolsistaVoluntarias(array $dados, array $perfil, int $editalId)
    {
        if ($perfil['aluno_sem_coordenacao']) {
            $usuario = $this->fetchTable('Usuarios')->get((int)$perfil['usuario_id']);
            $this->garantirSemWorkshopVoluntarias((int)$usuario->id, $editalId, 'você já possui Workshop cadastrada para este evento');

            return $usuario;
        }

        $cpf = preg_replace('/[^0-9]/', '', (string)($dados['cpf'] ?? ''));
        if (!parent::validaCPF($cpf)) {
            throw new \DomainException('O formato do CPF do bolsista é inválido');
        }

        $usuario = $this->fetchTable('Usuarios')->find()->where(['cpf' => $cpf])->first();
        if ($usuario) {
            $this->garantirSemWorkshopVoluntarias((int)$usuario->id, $editalId, 'O aluno ' . $usuario->nome . ' já possui Workshop cadastrada para este evento');
            return $usuario;
        }

        $usuarios = $this->fetchTable('Usuarios');
        $usuario = $usuarios->newEmptyEntity();
        $usuario->cpf = $cpf;
        $usuario->nome = $dados['nome'];
        $usuario->email = $dados['email'];

        if (!$usuarios->save($usuario)) {
            throw new \RuntimeException('Erro ao salvar os dados do bolsista.');
        }

        return $usuario;
    }

    protected function resolverOrientadorVoluntarias(array $dados, array $perfil, $bolsista, $orientadorPreSelecionado = null)
    {
        if ($perfil['aluno_sem_coordenacao']) {
            $orientador = $orientadorPreSelecionado;
        } elseif ($perfil['orientador_sem_coordenacao']) {
            $orientador = $this->fetchTable('Usuarios')->get((int)$perfil['usuario_id']);
        } else {
            $orientador = $this->buscarOuCriarOrientadorVoluntarias($dados);
        }

        if (!$orientador || (int)$orientador->id === (int)$bolsista->id) {
            throw new \DomainException('Workshop NÃO CADASTRADA: O orientador nao pode ser o mesmo que o bolsista.');
        }

        return $orientador;
    }

    protected function buscarOuCriarOrientadorVoluntarias(array $dados)
    {
        $cpf = preg_replace('/[^0-9]/', '', (string)($dados['cpf_orientador'] ?? ''));
        if (!parent::validaCPF($cpf)) {
            throw new \DomainException('O formato do CPF do orientador é inválido');
        }

        $usuarios = $this->fetchTable('Usuarios');
        $orientador = $usuarios->find()->where(['cpf' => $cpf])->first();
        if ($orientador) {
            return $orientador;
        }

        $orientador = $usuarios->newEmptyEntity();
        $orientador->cpf = $cpf;
        $orientador->nome = $dados['nome2'];
        $orientador->email = $dados['email2'];

        if (!$usuarios->save($orientador)) {
            throw new \RuntimeException('Erro ao salvar os dados do orientador.');
        }

        return $orientador;
    }

    protected function garantirSemWorkshopVoluntarias(int $usuarioId, int $editalId, string $mensagemBase): void
    {
        $workshop = $this->fetchTable('Workshops')->find()
            ->where([
                'bolsista' => $usuarioId,
                'editai_id' => $editalId,
                'deleted' => 0,
            ])
            ->first();

        if ($workshop) {
            throw new \DomainException('Workshop NÃO CADASTRADA: ' . $mensagemBase . ', sob numero #' . $workshop->id);
        }
    }
}
