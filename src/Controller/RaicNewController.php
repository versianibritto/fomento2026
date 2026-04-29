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

        $usuarioId = (int)($identity['id'] ?? 0);
        $ehYoda = !empty($identity['yoda']);
        $ehTi = in_array($usuarioId, [1, 8088], true);
        $ehJediDaUnidade = in_array((string)$raic->unidade_id, $jediArray, true);
        $ehBolsista = $usuarioId > 0 && $usuarioId === (int)($raic->usuario_id ?? 0);
        $ehOrientador = $usuarioId > 0 && $usuarioId === (int)($raic->orientador ?? 0);

        /*
        if (!$ehYoda && !$ehTi && !$ehJediDaUnidade && !$ehBolsista && !$ehOrientador) {
            $this->Flash->error('Somente a Gestão de Fomento, TI, Coordenação da Unidade, orientador ou bolsista podem editar a RAIC.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }
            */

        if (!$ehYoda && !$ehTi && !$ehJediDaUnidade) {
            $this->Flash->error('Somente a Gestão de Fomento, Coordenação da Unidade podem editar a RAIC.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        if (!empty($raic->deleted)) {
            $this->Flash->error('A RAIC está deletada.');
            return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
        }

        if (!empty($raic->data_apresentacao)) {
            $hoje = FrozenTime::now()->format('Y-m-d');
            $dataApresentacao = $raic->data_apresentacao->format('Y-m-d');
            if ($dataApresentacao <= $hoje && !$ehTi) {
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
        if ($ehTi) {
            $unidades = $this->fetchTable('Unidades')->find('list', [
                'keyField' => 'id',
                'valueField' => 'sigla',
            ])
                ->where(['Unidades.deleted' => 0])
                ->orderBy(['Unidades.sigla' => 'ASC'])
                ->toArray();
        }

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

        $this->set(compact('raic', 'anexoRelatorio', 'unidades', 'ehTi'));
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
        $perfil = $this->perfilVoluntarias();
        if (!$perfil['permitido']) {
            $this->Flash->error('As inscrições não são permitidas para seu perfil ou alguma condição não foi atendida');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $editaisAbertos = $this->editaisAbertosVoluntarias();
        if (empty($editaisAbertos)) {
            $this->Flash->error('Não existe Raic em período de inscrição atualmente');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $unidadesBase = $this->unidadesBaseVoluntarias($perfil);
        if (empty($unidadesBase)) {
            $this->Flash->error('Unidades restritas para o seu perfil');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        [$editais, $mapaUnidadesPorEdital] = $this->opcoesEditaisVoluntarias($editaisAbertos, $unidadesBase);
        if (empty($editais)) {
            $this->Flash->error('Não existe edital RAIC disponível para o seu perfil e unidades habilitadas.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $raicTabela = $this->fetchTable('Raics');
        $raic = $raicTabela->newEmptyEntity();

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();

            try {
                $editalSelecionadoId = $this->validarEditalVoluntarias($dados, $editais);
                $unidadesPermitidasEdital = $mapaUnidadesPorEdital[$editalSelecionadoId] ?? [];
                [$orientadorPreSelecionado, $unidadeSelecionadaId] = $this->resolverUnidadeVoluntarias($dados, $perfil, $unidadesPermitidasEdital);

                $raicCriada = null;
                $bolsistaNome = '';
                $raicTabela->getConnection()->transactional(function () use ($dados, $perfil, $editalSelecionadoId, $unidadeSelecionadaId, $orientadorPreSelecionado, $raicTabela, &$raicCriada, &$bolsistaNome): void {
                    $bolsista = $this->resolverBolsistaVoluntarias($dados, $perfil, $editalSelecionadoId);
                    $orientador = $this->resolverOrientadorVoluntarias($dados, $perfil, $bolsista, $orientadorPreSelecionado);
                    $bolsistaNome = (string)($bolsista->nome ?? '');

                    $raic = $raicTabela->newEmptyEntity();
                    $raic->usuario_id = (int)$bolsista->id;
                    $raic->orientador = (int)$orientador->id;
                    $raic->deleted = 0;
                    $raic->unidade_id = $unidadeSelecionadaId;
                    $raic->titulo = $dados['titulo'];
                    $raic->editai_id = $editalSelecionadoId;
                    $raic->tipo_bolsa = 'Z';
                    $raic->usuario_cadastro = (int)$perfil['usuario_id'];

                    if (!$raicTabela->save($raic)) {
                        throw new \RuntimeException('Erro ao salvar a Raic. A RAIC não foi gravada');
                    }

                    $anexosRelatorio = [];
                    if (!empty($dados['anexos'][13])) {
                        $anexosRelatorio[13] = $dados['anexos'][13];
                    }
                    if (!$this->anexarInscricao($anexosRelatorio, null, null, (int)$raic->id, true)) {
                        throw new \RuntimeException('Erro ao fazer o upload do relatório. A RAIC não foi gravada');
                    }
                    if (!$this->historico((int)$raic->id, 'criação', 'criação', 'cadastro manual da Raic')) {
                        throw new \RuntimeException('Erro ao gravar o histórico da RAIC manual.');
                    }

                    $raicCriada = $raic;
                });

                $this->Flash->success('RAIC CADASTRADA COM SUCESSO PARA O ALUNO ' . $bolsistaNome . ', sob numero #' . $raicCriada->id);
                return $this->redirect(['action' => 'voluntarias']);
            } catch (\DomainException $e) {
                $this->Flash->error($e->getMessage());
                return $this->redirect(['action' => 'voluntarias']);
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
        $ehOrientadorSemCoordenacao = $perfil['orientador_sem_coordenacao'];
        $ehAlunoSemCoordenacao = $perfil['aluno_sem_coordenacao'];
        $mostrarBlocoBolsista = !$ehAlunoSemCoordenacao;
        $mostrarBlocoOrientador = !$ehOrientadorSemCoordenacao;
        $mostrarCampoUnidade = !$ehAlunoSemCoordenacao;

        $this->set(compact(
            'unidades',
            'raic',
            'editais',
            'mapaUnidadesPorEdital',
            'ehOrientadorSemCoordenacao',
            'ehAlunoSemCoordenacao',
            'mostrarBlocoBolsista',
            'mostrarBlocoOrientador',
            'mostrarCampoUnidade'
        ));
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
            'usuario_id' => $usuarioId,
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
            throw new \DomainException('Selecione um edital RAIC válido.');
        }

        return $editalId;
    }

    protected function resolverUnidadeVoluntarias(array $dados, array $perfil, array $unidadesPermitidasEdital): array
    {
        if ($perfil['aluno_sem_coordenacao']) {
            $orientador = $this->buscarOrientadorAlunoVoluntarias($dados);
            $unidadeId = (int)($orientador->unidade_id ?? 0);
            if ($unidadeId <= 0 || !isset($unidadesPermitidasEdital[$unidadeId])) {
                throw new \DomainException('RAIC NÃO CADASTRADA: A unidade do orientador não está habilitada para este edital RAIC.');
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
            throw new \DomainException('RAIC NÃO CADASTRADA: O CPF do orientador não foi localizado.');
        }

        return $orientador;
    }

    protected function resolverBolsistaVoluntarias(array $dados, array $perfil, int $editalId)
    {
        if ($perfil['aluno_sem_coordenacao']) {
            $usuario = $this->fetchTable('Usuarios')->get((int)$perfil['usuario_id']);
            $this->garantirSemRaicVoluntarias((int)$usuario->id, $editalId, 'você já possui Raic cadastrada para este evento');

            return $usuario;
        }

        $cpf = preg_replace('/[^0-9]/', '', (string)($dados['cpf'] ?? ''));
        if (!parent::validaCPF($cpf)) {
            throw new \DomainException('O formato do CPF do bolsista é inválido');
        }

        $usuario = $this->fetchTable('Usuarios')->find()->where(['cpf' => $cpf])->first();
        if ($usuario) {
            $this->garantirSemRaicVoluntarias((int)$usuario->id, $editalId, 'O aluno ' . $usuario->nome . ' já possui Raic cadastrada para este evento');
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
            throw new \DomainException('RAIC NÃO CADASTRADA: O orientador nao pode ser o mesmo que o bolsista.');
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

    protected function garantirSemRaicVoluntarias(int $usuarioId, int $editalId, string $mensagemBase): void
    {
        $raic = $this->fetchTable('Raics')->find()
            ->where([
                'usuario_id' => $usuarioId,
                'editai_id' => $editalId,
                'deleted' => 0,
            ])
            ->first();

        if ($raic) {
            throw new \DomainException('RAIC NÃO CADASTRADA: ' . $mensagemBase . ', sob numero #' . $raic->id);
        }
    }
}
