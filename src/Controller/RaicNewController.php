<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;

class RaicNewController extends AppController
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
        $minha = $this->fetchTable('Raics')->find()
            ->contain([
                'Usuarios',
                'Orientadores',
                'Unidades',
                'ProjetoBolsistas',
                'Editais',
            ])
            ->where(['Raics.deleted' => 0])
            ->andWhere(function ($exp) use ($usuarioId) {
                return $exp->or([
                    'Raics.usuario_id' => $usuarioId,
                    'Raics.orientador' => $usuarioId,
                ]);
            })
            ->orderBy(['Raics.id' => 'DESC'])
            ->all();

        $this->set(compact('minha'));
    }

    public function ver($id = null)
    {
        $raic = $this->fetchTable('Raics')->get($id, [
            'contain' => [
                'Usuarios',
                'ProjetoBolsistas',
                'Projetos' => ['Areas', 'Linhas'],
                'Editais',
                'Orientadores',
                'Coorientadores',
                'Cadastro',
                'Libera',
                'Unidades',
                'RaicHistoricos' => ['Usuarios'],
            ],
        ]);

        if (!$this->request->getAttribute('identity')['yoda']) {
            if ($this->request->getAttribute('identity')['jedi']) {
                $unidadesPermitidas = array_filter(array_map('trim', explode(',', (string)$this->request->getAttribute('identity')['jedi'])));
                if (!in_array((string)$raic->unidade_id, $unidadesPermitidas, true)) {
                    $this->Flash->error('Esta RAIC é da gestão de outra unidade.');
                    return $this->redirect(['controller' => 'RaicNew', 'action' => 'painel']);
                }
            } else {
                if (!in_array($this->request->getAttribute('identity')['id'], [$raic->orientador, $raic->usuario_id])) {
                    $this->Flash->error('Somente a gestão, orientador e bolsista tem acesso a este módulo');
                    return $this->redirect(['controller' => 'Index', 'action' => 'index']);
                }
            }
        }

        $tipoAvaliacaoLista = strtoupper((string)($raic->tipo_bolsa ?? '')) === 'Z' ? 'Z' : 'V';

        $lista = $this->fetchTable('AvaliadorBolsistas')->find('all')
            ->contain([
                'Avaliadors' => 'Usuarios',
            ])
            ->where([
                'AvaliadorBolsistas.bolsista' => (int)$id,
                'AvaliadorBolsistas.tipo' => $tipoAvaliacaoLista,
            ])
            ->orderBy(['AvaliadorBolsistas.ordem' => 'ASC']);

        $anexoRelatorio = $this->fetchTable('Anexos')->find()
            ->contain(['AnexosTipos', 'Usuarios'])
            ->where([
                'Anexos.raic_id' => (int)$raic->id,
                'Anexos.anexos_tipo_id' => 13,
                'Anexos.deleted IS' => null,
            ])
            ->orderBy(['Anexos.id' => 'DESC'])
            ->first();

        $historicos = $this->fetchTable('RaicHistoricos')->find()
            ->contain(['Usuarios'])
            ->where(['RaicHistoricos.raic_id' => (int)$raic->id])
            ->orderBy(['RaicHistoricos.id' => 'DESC'])
            ->all();

        $editalReferencia = $raic->editai ?? null;
        $editalEvento = null;
        if (strtoupper((string)($raic->tipo_bolsa ?? '')) === 'R' && !empty($editalReferencia?->evento)) {
            $editalEvento = $this->fetchTable('Editais')->find()
                ->where(['Editais.id' => (int)$editalReferencia->evento])
                ->first();
        }

        $this->set(compact('raic', 'lista', 'anexoRelatorio', 'historicos', 'editalReferencia', 'editalEvento'));
    }

    public function agendar($id = null)
    {
        $raicsTable = $this->fetchTable('Raics');
        $avaliadorBolsistasTable = $this->fetchTable('AvaliadorBolsistas');
        $avaliadorsTable = $this->fetchTable('Avaliadors');

        $raic = $raicsTable->get((int)$id, [
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
        $ehJediDaUnidade = in_array((string)$raic->unidade_id, $jediArray, true);

        if (!$ehYoda && !$ehJediDaUnidade) {
            $this->Flash->error('Somente a Coordenação da Unidade e a Gestão de Fomento pode agendar a RAIC.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        if (!empty($raic->deleted)) {
            $this->Flash->error('A RAIC está deletada.');
            return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
        }

        $anexoRelatorio = $this->fetchTable('Anexos')->find()
            ->where([
                'Anexos.raic_id' => (int)$raic->id,
                'Anexos.anexos_tipo_id' => 13,
                'Anexos.deleted IS' => null,
            ])
            ->orderBy(['Anexos.id' => 'DESC'])
            ->first();

        if (trim((string)($raic->titulo ?? '')) === '' || empty($anexoRelatorio)) {
            $this->Flash->error('A RAIC precisa ter título e relatório anexado antes do agendamento. Edite a RAIC, atualize os dados e depois agende.');
            return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
        }

        $agora = FrozenTime::now();

        if (strtoupper((string)$raic->tipo_bolsa) === 'R' && !empty($raic->projeto_bolsista)) {
            if (!empty($raic->projeto_bolsista->deleted)) {
                $this->Flash->error('Houve desistência na solicitação de renovação.');
                return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
            }
            if ((string)($raic->projeto_bolsista->situacao ?? '') === 'O') {
                $this->Flash->error('A inscrição de renovação não foi finalizada.');
                return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
            }
        }

        $jaAgendada = !empty($raic->data_apresentacao);
        if ($jaAgendada && $raic->data_apresentacao instanceof \Cake\I18n\FrozenDate) {
            $dataJaAgendada = $raic->data_apresentacao->format('Y-m-d');
            if ($dataJaAgendada <= $agora->format('Y-m-d') && !$ehTi) {
                $this->Flash->error('A apresentação já passou ou ocorre hoje. Entre Em contato com a Gestão.');
                return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
            }
        }

        $tipoAvaliacao = strtoupper((string)($raic->tipo_bolsa ?? '')) === 'Z' ? 'Z' : 'V';
        $bancaAtiva = $avaliadorBolsistasTable->find()
            ->where([
                'AvaliadorBolsistas.bolsista' => (int)$raic->id,
                'AvaliadorBolsistas.tipo' => $tipoAvaliacao,
                'AvaliadorBolsistas.deleted' => 0,
            ])
            ->orderBy(['AvaliadorBolsistas.ordem' => 'ASC'])
            ->all();

        $avaliador1Atual = null;
        $avaliador2Atual = null;
        $ordemAvaliador = 1;
        foreach ($bancaAtiva as $itemBanca) {
            if ((int)($itemBanca->coordenador ?? 0) === 1) {
                continue;
            }
            if ($ordemAvaliador === 1) {
                $avaliador1Atual = (int)$itemBanca->avaliador_id;
                $ordemAvaliador++;
            } elseif ($ordemAvaliador === 2) {
                $avaliador2Atual = (int)$itemBanca->avaliador_id;
                $ordemAvaliador++;
            }
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();

            if (empty($dados['data_apresentacao'])) {
                $this->Flash->error('Informe a data de apresentação.');
                return $this->redirect(['action' => 'agendar', $raic->id]);
            }
            if ((new FrozenTime((string)$dados['data_apresentacao']))->year !== (int)date('Y')) {
                $this->Flash->error('A data de apresentação deve estar no ano atual.');
                return $this->redirect(['action' => 'agendar', $raic->id]);
            }

            $dados['data_apresentacao'] = parent::acertaData((string)$dados['data_apresentacao']);
            $dados['tipo_apresentacao'] = strtoupper(trim((string)($dados['tipo_apresentacao'] ?? '')));
            $dados['local_apresentacao'] = trim((string)($dados['local_apresentacao'] ?? ''));

            if (!in_array($dados['tipo_apresentacao'], ['O', 'P'], true)) {
                $this->Flash->error('Selecione o tipo de apresentação.');
                return $this->redirect(['action' => 'agendar', $raic->id]);
            }

            if ($dados['local_apresentacao'] === '') {
                $this->Flash->error('Informe o local de apresentação.');
                return $this->redirect(['action' => 'agendar', $raic->id]);
            }

            $avaliador1 = (int)($dados['avaliador_1'] ?? 0);
            $avaliador2 = (int)($dados['avaliador_2'] ?? 0);
            if (!$jaAgendada) {
                if ($avaliador1 <= 0 || $avaliador2 <= 0) {
                    $this->Flash->error('Informe os dois avaliadores da banca.');
                    return $this->redirect(['action' => 'agendar', $raic->id]);
                }

                if (
                    $avaliador1 === (int)$raic->orientador ||
                    $avaliador2 === (int)$raic->orientador
                ) {
                    $this->Flash->error('O orientador não pode fazer parte da banca.');
                    return $this->redirect(['action' => 'agendar', $raic->id]);
                }

                $bolsistaId = (int)($raic->usuario_id ?? 0);
                if ($bolsistaId > 0 && in_array($bolsistaId, [$avaliador1, $avaliador2], true)) {
                    $this->Flash->error('O bolsista não pode fazer parte da banca.');
                    return $this->redirect(['action' => 'agendar', $raic->id]);
                }

                if ($avaliador1 === $avaliador2) {
                    $this->Flash->error('Não pode haver repetição na banca.');
                    return $this->redirect(['action' => 'agendar', $raic->id]);
                }
            }

            $historicoAlteracao = $jaAgendada ? 'Raic reagendada.' : 'Raic agendada.';
            $historicoJustificativa = $jaAgendada
                ? 'Reagendamento com atualização da apresentação e vinculação da banca'
                : 'Agendamento padrão e vinculação de banca';

            try {
                $raicsTable->getConnection()->transactional(function () use (
                    $raicsTable,
                    $avaliadorBolsistasTable,
                    $raic,
                    $dados,
                    $tipoAvaliacao,
                    $avaliador1,
                    $avaliador2,
                    $jaAgendada,
                    $historicoAlteracao,
                    $historicoJustificativa
                ): void {
                    $raic->data_apresentacao = $dados['data_apresentacao'];
                    $raic->tipo_apresentacao = $dados['tipo_apresentacao'];
                    $raic->local_apresentacao = $dados['local_apresentacao'];

                    if (!$raicsTable->save($raic)) {
                        throw new \RuntimeException('Erro ao salvar o agendamento da RAIC.');
                    }

                    if (!$jaAgendada) {
                        $avaliadorBolsistasTable->updateAll(
                            ['deleted' => 1],
                            [
                                'bolsista' => (int)$raic->id,
                                'tipo' => $tipoAvaliacao,
                                'deleted' => 0,
                            ]
                        );

                        $dadosBanca = [
                            ['avaliador_id' => $avaliador1, 'ordem' => 1],
                            ['avaliador_id' => $avaliador2, 'ordem' => 2],
                        ];
                        foreach ($dadosBanca as $itemBanca) {
                            $novo = $avaliadorBolsistasTable->newEmptyEntity();
                            $novo->bolsista = (int)$raic->id;
                            $novo->raic_id = (int)$raic->id;
                            $novo->tipo = $tipoAvaliacao;
                            $novo->situacao = 'E';
                            $novo->editai_id = (int)$raic->editai_id;
                            $novo->ano = (int)date('Y');
                            $novo->coordenador = 0;
                            $novo->avaliador_id = (int)$itemBanca['avaliador_id'];
                            $novo->ordem = (int)$itemBanca['ordem'];
                            $novo->deleted = 0;

                            if (!$avaliadorBolsistasTable->save($novo)) {
                                throw new \RuntimeException('Erro ao salvar a banca da RAIC.');
                            }
                        }
                    }

                    if (!$this->historico((int)$raic->id, $historicoAlteracao, $historicoAlteracao, $historicoJustificativa)) {
                        throw new \RuntimeException('Erro ao gravar o histórico do agendamento.');
                    }
                });

                $this->Flash->success($jaAgendada ? 'A RAIC foi reagendada com sucesso.' : 'A RAIC foi agendada com sucesso.');
                return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - Agendar RAIC',
                    'Houve um erro no agendamento da RAIC. Tente novamente.'
                );
                return $this->redirect(['action' => 'agendar', $raic->id]);
            }
        }

        $usuariosBloqueados = array_values(array_filter(array_unique([
            (int)$raic->orientador,
            (int)($raic->usuario_id ?? 0),
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
                'Avaliadors.editai_id' => (int)$raic->editai_id,
                'Avaliadors.deleted' => 0,
                'Avaliadors.unidade_id' => (int)$raic->unidade_id,
            ])
            ->where(empty($usuariosBloqueados) ? [] : ['Avaliadors.usuario_id NOT IN' => $usuariosBloqueados])
            ->orderBy(['Usuarios.nome' => 'ASC'])
            ->all();

        $this->set(compact(
            'raic',
            'avaliadores',
            'avaliador1Atual',
            'avaliador2Atual',
            'jaAgendada'
        ));
    }

    public function editar($id = null)
    {
        $raicsTable = $this->fetchTable('Raics');
        $raic = $raicsTable->get((int)$id, [
            'contain' => [
                'Usuarios',
                'Orientadores',
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

        if (!$ehYoda) {
            $this->Flash->error('Somente a Gestão de Fomento pode editar a RAIC.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        if (!empty($raic->deleted)) {
            $this->Flash->error('A RAIC está deletada.');
            return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
        }

        if (!empty($raic->data_apresentacao)) {
            $hoje = FrozenTime::now()->format('Y-m-d');
            $dataApresentacao = $raic->data_apresentacao->format('Y-m-d');
            if ($dataApresentacao <= $hoje) {
                $dataApresentacaoTela = $raic->data_apresentacao->i18nFormat('dd/MM/YYYY');
                $dataLimiteEdicao = $raic->data_apresentacao->modify('-1 day')->i18nFormat('dd/MM/YYYY');
                $this->Flash->error(
                    'A edição desta RAIC não é mais permitida. A apresentação está marcada para ' . $dataApresentacaoTela . ' e a edição só era permitida até ' . $dataLimiteEdicao . '.'
                );
                return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
            }
        }

        $anexoRelatorio = $this->fetchTable('Anexos')->find()
            ->contain(['Usuarios'])
            ->where([
                'Anexos.raic_id' => (int)$raic->id,
                'Anexos.anexos_tipo_id' => 13,
                'Anexos.deleted IS' => null,
            ])
            ->orderBy(['Anexos.id' => 'DESC'])
            ->first();

        $unidades = [];

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();

            $titulo = trim((string)($dados['titulo'] ?? ''));
            if ($titulo === '') {
                $this->Flash->error('Informe o título do subprojeto.');
                return $this->redirect(['action' => 'editar', $raic->id]);
            }

            $alteracoes = [];
            if ($titulo !== (string)($raic->titulo ?? '')) {
                $alteracoes[] = 'título';
            }
            $raic->titulo = $titulo;

            $arquivoRelatorio = $dados['anexos'][13] ?? null;
            $temNovoRelatorio = is_object($arquivoRelatorio) && $arquivoRelatorio->getClientFilename() !== '';
            if ($temNovoRelatorio) {
                $alteracoes[] = 'relatório';
            }

            if (empty($alteracoes)) {
                $this->Flash->info('Nenhuma alteração foi informada.');
                return $this->redirect(['action' => 'editar', $raic->id]);
            }

            try {
                $raicsTable->getConnection()->transactional(function () use ($raicsTable, $raic, $temNovoRelatorio, $arquivoRelatorio, $alteracoes): void {
                    if (!$raicsTable->save($raic)) {
                        throw new \RuntimeException('Erro ao salvar a RAIC.');
                    }

                    if ($temNovoRelatorio) {
                        if (!$this->anexarInscricao([13 => $arquivoRelatorio], null, null, (int)$raic->id, true)) {
                            throw new \RuntimeException('Erro ao atualizar o relatório da RAIC.');
                        }
                    }

                    $justificativa = 'Atualização manual da RAIC: ' . implode(', ', $alteracoes);

                    if (!$this->historico((int)$raic->id, 'edição', 'edição', $justificativa)) {
                        throw new \RuntimeException('Erro ao gravar o histórico da edição da RAIC.');
                    }
                });

                $this->Flash->success('A RAIC foi atualizada com sucesso.');
                return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - Edição de RAIC',
                    'Houve um erro na edição da RAIC. Tente novamente.'
                );
                return $this->redirect(['action' => 'editar', $raic->id]);
            }
        }

        $this->set(compact('raic', 'anexoRelatorio', 'unidades'));
    }

    public function liberacertificado($id = null)
    {
        $raicsTable = $this->fetchTable('Raics');
        $raic = $raicsTable->get($id);

        if (!$this->request->getAttribute('identity')['yoda']) {
            if ($this->request->getAttribute('identity')['jedi']) {
                $unidadesPermitidas = array_filter(array_map('trim', explode(',', (string)$this->request->getAttribute('identity')['jedi'])));
                if (!in_array((string)$raic->unidade_id, $unidadesPermitidas, true)) {
                    $this->Flash->error('Esta RAIC é da gestão de outra unidade.');
                    return $this->redirect(['controller' => 'Index', 'action' => 'index']);
                }
            } else {
                $this->Flash->error('Somente a Coordenação da Unidade e a Gestão de Fomento pode liberar certificado da RAIC.');
                return $this->redirect(['controller' => 'Index', 'action' => 'index']);
            }
        }

        if ((int)$raic->deleted === 1) {
            $this->Flash->error('A RAIC foi deletada.');
            return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
        }

        if ($raic->data_apresentacao == null) {
            $this->Flash->error('A RAIC ainda não foi agendada.');
            return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
        }

        if (date('Ymd') <= $raic->data_apresentacao->i18nFormat('YMMdd')) {
            $this->Flash->error('A RAIC ainda não ocorreu. A presença só poderá ser registrada após o evento.');
            return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
        }

        if ($raic->presenca === 'S') {
            $this->Flash->error('O certificado já estava liberado.');
            return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
        }

        $this->request->allowMethod(['post', 'put']);

        $raic->presenca = 'S';
        $raic->usuario_libera = $this->request->getAttribute('identity')['id'];
        $raic->data_liberacao = date('Y-m-d H:i:s');

        if ($raicsTable->save($raic)) {
            $this->historico($raic->id, 'Certificado Liberado', 'Certificado Liberado', 'Usuário responsável registrou a presença do aluno');
            $this->Flash->success('Certificado liberado.');
            return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
        }

        $this->Flash->error('Houve um erro durante a liberação. Tente novamente.');
        return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
    }

    public function deletar($id = null)
    {
        if (!$this->ehTi()) {
            $this->Flash->error('A exclusão da RAIC é restrita à TI.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $raicsTable = $this->fetchTable('Raics');
        $raic = $raicsTable->get((int)$id, [
            'contain' => ['Usuarios', 'Orientadores', 'Unidades', 'Editais'],
        ]);

        if ((int)($raic->deleted ?? 0) === 1) {
            $this->Flash->info('A RAIC já estava deletada.');
            return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $justificativa = trim((string)($this->request->getData('justificativa_cancelamento') ?? ''));
            if ($justificativa === '') {
                $this->Flash->error('Informe a justificativa da exclusão.');
                return $this->redirect(['action' => 'deletar', $raic->id]);
            }

            try {
                $raicsTable->getConnection()->transactional(function () use ($raicsTable, $raic, $justificativa): void {
                    $raic->deleted = 1;
                    if (!$raicsTable->save($raic)) {
                        throw new \RuntimeException('Erro ao deletar a RAIC.');
                    }

                    if (!$this->historico((int)$raic->id, 'deletada', 'deletada', 'Exclusão da RAIC pela TI: ' . $justificativa)) {
                        throw new \RuntimeException('Erro ao gravar o histórico da exclusão da RAIC.');
                    }
                });

                $this->Flash->success('A RAIC foi deletada com sucesso.');
                return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - Exclusão de RAIC',
                    'Houve um erro ao deletar a RAIC. Tente novamente.'
                );
                return $this->redirect(['action' => 'deletar', $raic->id]);
            }
        }

        $this->set(compact('raic'));
    }

    public function historico($id, $original, $atual, $just, bool $throw = true): bool
    {
        $raicHistoricos = $this->fetchTable('RaicHistoricos');
        $novo = $raicHistoricos->newEmptyEntity();
        $novo->raic_id = $id;
        $novo->usuario_id = $this->request->getAttribute('identity')->id;
        $novo->alteracao = $original;
        $novo->justificativa = $just;

        $nv = $raicHistoricos->save($novo);
        if (!$nv) {
            $this->Flash->error('Houve um erro na gravação. Tente novamente');
            return false;
        }

        return true;
    }


    public function voluntarias($ed = null)
    {   
        $identity = $this->request->getAttribute('identity');
        $ehYoda = !empty($identity['yoda']);
        $jediIds = array_values(array_filter(array_map(
            'intval',
            explode(',', (string)($identity['jedi'] ?? ''))
        )));

        if((!$ehYoda) && ($identity['jedi']==null)){
            $this->Flash->error('Restrito a administradores');
            return $this->redirect(['controller'=>'Index', 'action'=>'index']);
        }

        if(($identity['jedi']===0)){
            $this->Flash->error('Restrito a administradores');
            return $this->redirect(['controller'=>'Index', 'action'=>'index']);
        }

        $editaisAbertos = TableRegistry::getTableLocator()->get('Editais')->find()
            ->select(['id', 'nome', 'unidades_permitidas'])
            ->where([
                'inicio_inscricao < NOW()',
                'fim_inscricao > NOW()',
                'origem' => 'V',
            ])
            ->orderBy(['nome' => 'ASC'])
            ->all();

        if (count($editaisAbertos->toArray()) === 0) {
            $this->Flash->error('Não existe Raic em período de inscrição atualmente');
            return $this->redirect(['controller'=>'Index', 'action'=>'index']);
        }

        if($ehYoda){
            $unidadesBase = TableRegistry::getTableLocator()->get('Unidades')->find('list', [
                'keyField' => 'id',
                'valueField' => 'sigla',
                'limit' => 220,
            ])
                ->where(['Unidades.deleted' => 0])
                ->orderBy(['Unidades.sigla'=>'ASC'])
                ->toArray();
        } else {
            $unidadesBase = TableRegistry::getTableLocator()->get('Unidades')
                ->find('list', [
                    'keyField' => 'id',
                    'valueField' => 'sigla',
                    'limit' => 220,
                ])
                ->where([
                    'Unidades.id IN' => $jediIds,
                    'Unidades.deleted' => 0,
                ])
                ->orderBy(['Unidades.sigla'=>'ASC'])
                ->toArray();
        }

        $editais = [];
        $mapaUnidadesPorEdital = [];
        foreach ($editaisAbertos as $editalAberto) {
            $permitidas = array_values(array_filter(array_map(
                'intval',
                explode(',', (string)($editalAberto->unidades_permitidas ?? ''))
            )));

            $unidadesPermitidasEdital = $unidadesBase;
            if (!empty($permitidas)) {
                $unidadesPermitidasEdital = array_intersect_key($unidadesBase, array_flip($permitidas));

                if (!$ehYoda && empty($unidadesPermitidasEdital)) {
                    continue;
                }
            }

            if (empty($unidadesPermitidasEdital)) {
                continue;
            }

            $editais[(int)$editalAberto->id] = (string)$editalAberto->nome;
            $mapaUnidadesPorEdital[(int)$editalAberto->id] = $unidadesPermitidasEdital;
        }

        if (count($editais) === 0) {
            $this->Flash->error('Não existe edital RAIC disponível para o seu perfil e unidades habilitadas.');
            return $this->redirect(['controller'=>'Index', 'action'=>'index']);
        }
        
        $raicTabela = TableRegistry::getTableLocator()->get('Raics');
        $raic = $raicTabela->newEmptyEntity();


        if($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();

            try {

                $editalSelecionadoId = (int)($dados['editai_id'] ?? 0);
                if ($editalSelecionadoId <= 0 || !isset($editais[$editalSelecionadoId])) {
                    $this->Flash->error('Selecione um edital RAIC válido.');
                    return $this->redirect(['action' => 'voluntarias']);
                }

                $unidadesPermitidasEdital = $mapaUnidadesPorEdital[$editalSelecionadoId] ?? [];
                $unidadeSelecionadaId = (int)($dados['unidade_id'] ?? 0);
                if ($unidadeSelecionadaId <= 0 || !isset($unidadesPermitidasEdital[$unidadeSelecionadaId])) {
                    $this->Flash->error('Selecione uma unidade válida para o edital informado.');
                    return $this->redirect(['action' => 'voluntarias']);
                }

                $connection = $raicTabela->getConnection();
                $connection->transactional(function () use ($dados, $editalSelecionadoId) {

                    // Verifica se o CPF bolsista é válido
                        $cpf = preg_replace('/[^0-9]/', '', $dados['cpf']);
                        if(parent::validaCPF($cpf)) {
                            // Verifica se o usuário existe no Banco
                                $usuario = TableRegistry::getTableLocator()->get('Usuarios')
                                ->find()->where(['cpf' => $dados['cpf']])->first();

                                if($usuario) {
                                    // Verifica se o bolsista tem raic nesse ano
                                    //$ano=date("Y", strtotime($dados['data_apresentacao']));
                                    $raic = TableRegistry::getTableLocator()->get('Raics')
                                    ->find()->where([
                                        'usuario_id' => $usuario->id,
                                        'editai_id' => $editalSelecionadoId,
                                        'deleted'=>0
                                        ])->first();

                                    if($raic !=null){
                                        $erro = true;
                                        $this->Flash->error('RAIC NÃO CADASTRADA: O aluno '.$usuario->nome.' já possui Raic cadastrada para este evento, sob numero #'.$raic->id);
                                        return $this->redirect(['action' => 'voluntarias']);

                                    }
                                }else{
                                    //se nao existe grava
                                        $tblUsuario = TableRegistry::getTableLocator()->get("Usuarios");
                                        $usuario = $tblUsuario->newEmptyEntity();
                                        $usuario->cpf = $cpf;
                                        $usuario->nome = $dados['nome'];
                                        $usuario->email = $dados['email'];

                                        $nb = $tblUsuario->save($usuario);
                                        if(!$nb) {
                                            throw new \Exception('Erro ao salvar os dados do bolsista.');
                                            return $this->redirect(['action' => 'voluntarias']);
                                        }
                                    //
                                }
                            //
                        }else{
                            $this->Flash->error('O formato do CPF do bolsista é inválido');
                            return $this->redirect(['action' => 'voluntarias']);
                        }
                    //

                    //VERIFICA ORIENTADOR
                        if($dados['cpf_orientador']!=null){
                            // Verifica se o CPF coorientador é válido
                                $cpfor = preg_replace('/[^0-9]/', '', $dados['cpf_orientador']);
                                if(parent::validaCPF($cpfor)) {
                                    // Verifica se o usuário existe no Banco
                                        $orientador = TableRegistry::getTableLocator()->get('Usuarios')
                                        ->find()->where(['cpf' => $dados['cpf_orientador']])->first();

                                        if($orientador) {
                                            if($orientador->cpf == $usuario->cpf){
                                                $this->Flash->error('RAIC NÃO CADASTRADA: O orientador nao pode ser o mesmo que o bolsista.');
                                                return $this->redirect(['action' => 'voluntarias']);
                                            }
                                        }else{
                                            //se nao existe grava
                                            $tblUsuario = TableRegistry::getTableLocator()->get("Usuarios");
                                            $orientador = $tblUsuario->newEmptyEntity();
                                            $orientador->cpf = $cpfor;
                                            $orientador->nome = $dados['nome2'];
                                            $orientador->email = $dados['email2'];

                                            $nb2 = $tblUsuario->save($orientador);
                                            if(!$nb2) {
                                                $erro = true;
                                                throw new \Exception('Erro ao salvar os dados do orientador.');
                                            }
                                        }
                                    //
                                }else{
                                    $this->Flash->error('O formato do CPF do orientador é inválido');
                                    return $this->redirect($this->request->referer());
                                }
                            //
                        }
                    //

                    //GRAVAÇÃO  DA RAIC
                        $raicTabela = TableRegistry::getTableLocator()->get('Raics');
                        $raic = $raicTabela->newEmptyEntity();

                        $raic->usuario_id=$usuario->id;
                        $raic->orientador=$orientador->id;
                        $raic->deleted=0;
                        $raic->unidade_id=$dados['unidade_id'];	
                        $raic->titulo=$dados['titulo'];	
                        //$raic->data_apresentacao=$dados['data_apresentacao'];	
                        $raic->editai_id=$editalSelecionadoId;

                        // colocar o antigo como V se for o caso$raic->tipo_bolsa='V'; //v=voluntario
                        $raic->tipo_bolsa='Z';
                        $raic->usuario_cadastro =  $this->Authentication->getIdentity()['id'];

                        if($raicTabela->save($raic)){
                            $anexosRelatorio = [];
                            if (!empty($dados['anexos'][13])) {
                                $anexosRelatorio[13] = $dados['anexos'][13];
                            }
                            if (!$this->anexarInscricao($anexosRelatorio, null, null, $raic->id, true)) {
                                throw new \Exception('Erro ao fazer o upload do relatório. A RAIC não foi gravada');
                            }
                            if (!$this->historico($raic->id, 'criação', 'criação', 'cadastro manual da Raic')) {
                                throw new \Exception('Erro ao gravar o histórico da RAIC manual.');
                            }
                        }else{
                            throw new \Exception('Erro ao salvar a Raic. A RAIC não foi gravada');
                            return $this->redirect(['action' => 'voluntarias']);
                        }

                        

                    // FIM GRAVAÇÃO  DA RAIC
            
                    $this->Flash->success('RAIC CADASTRADA COM SUCESSO PARA O ALUNO '.$usuario->nome.', sob numero #'.$raic->id);
                    return $this->redirect(['action' => 'voluntarias']);
                });
            
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - Gravação de RAIC voluntária',
                    'Houve um erro na gravação. Tente novamente.'
                );
                return $this->redirect(['action' => 'voluntarias']);
            }   

        }

        $unidades = $unidadesBase;

        $this->set(compact('unidades', 'raic', 'editais', 'mapaUnidadesPorEdital'));
    }
}
