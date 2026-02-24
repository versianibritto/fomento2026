<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\EventInterface;

class PadraoController extends AppController
{
    protected $identityLogado = null;

    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setLayout('admin');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->identityLogado = $this->Authentication->getIdentity();
        return null;
    }

    public function visualizar($inscricaoId = null)
    {
        $identity = $this->identityLogado;
        if (empty($inscricaoId)) {
            $this->Flash->error('Parametros invalidos para visualizacao.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'T']);
        }

        $ehYoda = $this->ehYoda();
        $conditions = [
            'ProjetoBolsistas.id' => (int)$inscricaoId,
        ];
        if (!$ehYoda) {
            $conditions['OR'] = [
                ['ProjetoBolsistas.orientador' => (int)$identity->id],
                ['ProjetoBolsistas.coorientador' => (int)$identity->id],
            ];
        }

        $inscricao = $this->fetchTable('ProjetoBolsistas')->find()
            ->contain([
                'Editais',
                'Bolsistas',
                'Orientadores',
                'Coorientadores' => ['Escolaridades', 'Vinculos'],
                'Projetos' => ['Areas' => ['GrandesAreas'], 'Linhas' => ['AreasFiocruz']],
                'Fases',
                'Anexos' => ['conditions' => 'Anexos.deleted IS NULL', 'AnexosTipos'],
            ])
            ->where($conditions)
            ->first();

        if (!$inscricao) {
            $this->Flash->error('Inscricao nao localizada para visualizacao.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'T']);
        }

        $edital = $inscricao->editai ?? null;
        $editaiId = (int)($inscricao->editai_id ?? 0);
        if ($editaiId <= 0) {
            $editaiId = (int)($inscricao->edital_id ?? 0);
        }
        if (!$edital && $editaiId > 0) {
            $edital = $this->fetchTable('Editais')->find()
                ->where(['Editais.id' => (int)$editaiId])
                ->first();
        }
        if (!$edital) {
            $this->Flash->error('Edital nao localizado para visualizacao.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $tiposAnexos = $this->fetchTable('AnexosTipos')->find()
            ->select(['id', 'nome', 'bloco'])
            ->where(['AnexosTipos.deleted' => 0])
            ->all();
        $tiposMap = [];
        foreach ($tiposAnexos as $tipo) {
            $tiposMap[(int)$tipo->id] = [
                'nome' => (string)$tipo->nome,
                'bloco' => (string)$tipo->bloco,
            ];
        }

        $anexosPorBloco = [
            'B' => [],
            'C' => [],
            'P' => [],
            'S' => [],
            'OUTROS' => [],
        ];
        foreach ((array)$inscricao->anexos as $anexo) {
            $tipoId = (int)($anexo->anexos_tipo_id ?? 0);
            $meta = $tiposMap[$tipoId] ?? ['nome' => 'Anexo #' . $tipoId, 'bloco' => 'OUTROS'];
            $bloco = strtoupper((string)$meta['bloco']);
            if (in_array($tipoId, [13, 20], true)) {
                $bloco = 'S';
            } elseif (!array_key_exists($bloco, $anexosPorBloco)) {
                $bloco = 'OUTROS';
            }
            $anexosPorBloco[$bloco][] = [
                'tipo_id' => $tipoId,
                'tipo_nome' => (string)$meta['nome'],
                'arquivo' => (string)($anexo->anexo ?? ''),
                'created' => $anexo->created ?? null,
            ];
        }

        $historicos = $this->fetchTable('SituacaoHistoricos')->find()
            ->contain(['Usuarios', 'FaseOriginal', 'FaseAtual'])
            ->where(['SituacaoHistoricos.projeto_bolsista_id' => (int)$inscricao->id])
            ->orderBy(['SituacaoHistoricos.id' => 'DESC'])
            ->all();

        $avaliacoes = $this->fetchTable('AvaliadorBolsistas')->find()
            ->contain(['Avaliadors' => ['Usuarios']])
            ->where([
                'AvaliadorBolsistas.bolsista' => (int)$inscricao->id,
                'AvaliadorBolsistas.deleted' => 0,
            ])
            ->orderBy([
                'AvaliadorBolsistas.ordem' => 'ASC',
                'AvaliadorBolsistas.id' => 'ASC',
            ])
            ->all();

        $sumulasEdital = [];
        $quantidadesSumula = [];
        if (strtoupper((string)($inscricao->origem ?? '')) === 'N') {
            $sumulasEdital = $this->fetchTable('EditaisSumulas')->find()
                ->where([
                    'EditaisSumulas.editai_id' => (int)$edital->id,
                    'EditaisSumulas.deleted IS' => null,
                ])
                ->orderBy(['EditaisSumulas.id' => 'ASC'])
                ->all();

            if ($sumulasEdital->count() > 0) {
                $idsSumula = [];
                foreach ($sumulasEdital as $sumula) {
                    $idsSumula[] = (int)$sumula->id;
                }

                $sumulasSalvas = $this->fetchTable('InscricaoSumulas')->find()
                    ->where([
                        'InscricaoSumulas.projeto_bolsista_id' => (int)$inscricao->id,
                        'InscricaoSumulas.pdj_inscricoe_id IS' => null,
                        'InscricaoSumulas.editais_sumula_id IN' => $idsSumula,
                    ])
                    ->all();

                foreach ($sumulasSalvas as $sumulaSalva) {
                    $quantidadesSumula[(int)$sumulaSalva->editais_sumula_id] = $sumulaSalva->quantidade;
                }
            }
        }

        $origens = $this->origem;
        $cotas = $this->cota;
        $fontes = $this->fonte;
        $resultadoMap = $this->resultado;
        $statusAvaliacaoMap = [
            'F' => 'Finalizada',
            'E' => 'Excluida',
        ];

        $origemAtual = strtoupper((string)($inscricao->origem ?? ''));
        $controllerFluxo = $origemAtual === 'R' ? 'Renovacoes' : 'Inscricoes';

        $this->set(compact(
            'inscricao',
            'edital',
            'anexosPorBloco',
            'historicos',
            'avaliacoes',
            'sumulasEdital',
            'quantidadesSumula',
            'origens',
            'cotas',
            'fontes',
            'resultadoMap',
            'statusAvaliacaoMap',
            'controllerFluxo',
            'origemAtual'
        ));
    }
    public function cancelar($inscricaoId = null)
    {
        $identity = $this->identityLogado;
        if (empty($inscricaoId)) {
            $this->Flash->error('Parametros invalidos para cancelamento.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'V']);
        }

        $ehYoda = $this->ehYoda();
        $ehTiId = in_array((int)($identity->id ?? 0), [1, 8088], true);
        $conditions = [
            'ProjetoBolsistas.id' => (int)$inscricaoId,
        ];
        if (!$ehYoda) {
            $conditions['ProjetoBolsistas.orientador'] = (int)$identity->id;
        }

        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $inscricao = $tblProjetoBolsistas->find()
            ->contain(['Editais', 'Bolsistas', 'Orientadores'])
            ->where($conditions)
            ->first();
        if (!$inscricao) {
            $this->Flash->error('Inscricao nao localizada ou não possui acesso para cancelamento.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'V']);
        }

        if ((int)$inscricao->deleted === 1) {
            $this->Flash->error('Registro inativado. Nao e possivel cancelar.');
            return $this->redirect(['action' => 'visualizar', (int)$inscricao->id]);
        }
        $faseAtual = (int)$inscricao->fase_id;
        if ($ehTiId) {
            if (!in_array($faseAtual, [11, 18, 19], true)) {
                $this->Flash->error('Para TI, o cancelamento e permitido apenas nas fases Ativo, Finalizando Bolsa ou Renovação solucutada.');
                return $this->redirect(['action' => 'visualizar', (int)$inscricao->id]);
            }
        } else {
            if (!in_array($faseAtual, [11, 18], true)) {
                $this->Flash->error('O cancelamento e permitido apenas para os status Ativo ou Finalizando Bolsa');
                return $this->redirect(['action' => 'visualizar', (int)$inscricao->id]);
            }
        }

       

       
        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();
            $erros = [];

            $motivoCancelamentoId = (int)($dados['motivo_cancelamento_id'] ?? 0);
            if ($motivoCancelamentoId <= 0) {
                $erros[] = 'Informe o motivo do cancelamento.';
            }

            $justificativa = trim((string)($dados['justificativa_cancelamento'] ?? ''));
            if ($justificativa === '') {
                $erros[] = 'Informe a justificativa do cancelamento.';
            } elseif ((function_exists('mb_strlen') ? mb_strlen($justificativa) : strlen($justificativa)) < 20) {
                $erros[] = 'A justificativa do cancelamento deve ter pelo menos 20 caracteres.';
            }

            $naoEnviar = (int)($dados['nao_enviar'] ?? 0) === 1;
            $arquivoRelatorio = $dados['anexos'][14] ?? null;
            $enviouRelatorio = is_object($arquivoRelatorio) && $arquivoRelatorio->getClientFilename() !== '';
            if (!$naoEnviar && !$enviouRelatorio) {
                $erros[] = 'Anexe o relatorio final ou marque que nao enviara neste momento.';
            }

            if (!empty($erros)) {
                $this->Flash->error(implode('<br>', $erros), ['escape' => false]);
                return $this->redirect(['action' => 'cancelar', (int)$inscricao->id]);
            }

            try {
                $tblProjetoBolsistas->getConnection()->transactional(function () use ($tblProjetoBolsistas, $inscricao, $motivoCancelamentoId, $justificativa, $naoEnviar, $arquivoRelatorio, $faseAtual) {
                    if (!$naoEnviar) {
                        $okAnexo = $this->anexarInscricao(
                            [14 => $arquivoRelatorio],
                            !empty($inscricao->projeto_id) ? (int)$inscricao->projeto_id : null,
                            (int)$inscricao->id,
                            null,
                            true
                        );
                        if (!$okAnexo) {
                            throw new \RuntimeException('Falha ao anexar relatorio final do cancelamento.');
                        }
                    }

                    if ((int)$faseAtual === 19) {
                        $renovacaoEmAndamento = $tblProjetoBolsistas->find()
                            ->where([
                                'ProjetoBolsistas.referencia_inscricao_anterior' => (int)$inscricao->id,
                                'ProjetoBolsistas.deleted' => 0,
                            ])
                            ->orderBy(['ProjetoBolsistas.id' => 'DESC'])
                            ->first();
                        if ($renovacaoEmAndamento) {
                            $faseRenovacao = (int)$renovacaoEmAndamento->fase_id;
                            $renovacaoPatch = $tblProjetoBolsistas->patchEntity($renovacaoEmAndamento, [
                                'deleted' => 1,
                                'justificativa_cancelamento' => $justificativa,
                                'data_cancelamento' => date('Y-m-d H:i:s'),
                            ]);
                            $tblProjetoBolsistas->saveOrFail($renovacaoPatch);
                            $this->historico(
                                (int)$renovacaoEmAndamento->id,
                                $faseRenovacao,
                                $faseRenovacao,
                                'Inativacao automatica de renovacao vinculada por cancelamento da referencia: ' . $justificativa,
                                true
                            );
                        }
                    }

                    $faseOriginal = (int)$inscricao->fase_id;
                    $inscricaoPatch = $tblProjetoBolsistas->patchEntity($inscricao, [
                        'fase_id' => 12,
                        'motivo_cancelamento_id' => $motivoCancelamentoId,
                        'justificativa_cancelamento' => $justificativa,
                        'data_cancelamento' => date('Y-m-d H:i:s'),
                    ]);
                    $tblProjetoBolsistas->saveOrFail($inscricaoPatch);
                    $this->historico((int)$inscricao->id, $faseOriginal, 12, 'Solicitacao de cancelamento: ' . $justificativa, true);
                });
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - solicitar cancelamento da inscricao',
                    'Nao foi possivel solicitar o cancelamento.'
                );
                return $this->redirect(['action' => 'cancelar', (int)$inscricao->id]);
            }

            $this->Flash->success('Cancelamento solicitado com sucesso.');
            return $this->redirect(['action' => 'visualizar', (int)$inscricao->id]);
        }

         $motivos = $this->fetchTable('MotivoCancelamentos')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])->toArray();


        $origemAtual = strtoupper(trim((string)($inscricao->origem ?? '')));
        $this->set(compact('inscricao', 'motivos', 'origemAtual'));
    }

    public function substituir($inscricaoId = null)
    {
        $identity = $this->identityLogado;
        if (empty($inscricaoId)) {
            $this->Flash->error('Inscricao nao informada para substituicao.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'V']);
        }

        $conditions = [
            'ProjetoBolsistas.id' => (int)$inscricaoId,
            'ProjetoBolsistas.deleted' => 0,
        ];
        if (!$this->ehTi()) {
            $conditions['ProjetoBolsistas.orientador'] = (int)$identity->id;
        }

        $inscricao = $this->fetchTable('ProjetoBolsistas')->find()
            ->select(['id', 'editai_id'])
            ->where($conditions)
            ->first();
        if (!$inscricao) {
            $this->Flash->error('Inscricao nao localizada oi deletada para substituicao.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'V']);
        }

        if (!$this->ehTi()) {
            $edital = $this->fetchTable('Editais')->find()
                ->select(['id', 'inicio_inscricao', 'fim_inscricao', 'inicio_avaliar', 'fim_avaliar'])
                ->where(['Editais.id' => (int)$inscricao->editai_id])
                ->first();
            if (!$edital) {
                $this->Flash->error('Edital nao localizado para substituicao.');
                return $this->redirect(['action' => 'visualizar', (int)$inscricao->id]);
            }
            $wkSubstituicaoId = 4;
            $wkSubstituicao = $this->fetchTable('EditaisWks')->find()
                ->select(['int'])
                ->where(['EditaisWks.nome LIKE' => 'Substitui%'])
                ->first();
            if ($wkSubstituicao && !empty($wkSubstituicao->int)) {
                $wkSubstituicaoId = (int)$wkSubstituicao->int;
            }

            if (!$this->loadPeriodo($edital, $identity, $wkSubstituicaoId, [], [(int)$inscricao->id])) {
                $this->Flash->error('Substituicao fora do periodo permitido para este edital.');
                return $this->redirect(['action' => 'visualizar', (int)$inscricao->id]);
            }
        }

        $this->Flash->info('Fluxo de substituicao ainda nao foi disponibilizado.');
        return $this->redirect(['action' => 'visualizar', (int)$inscricao->id]);
    }

    public function deletar($inscricaoId = null)
    {
        $identity = $this->identityLogado;
        if (!$this->ehTi()) {
            $this->Flash->error('Somente TI pode deletar.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        if (empty($inscricaoId)) {
            $this->Flash->error('Inscricao nao informada para exclusao.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $inscricao = $tblProjetoBolsistas->find()
            ->contain(['Bolsistas', 'Orientadores', 'Editais'])
            ->where(['ProjetoBolsistas.id' => (int)$inscricaoId])
            ->first();

        if (!$inscricao) {
            $this->Flash->error('Inscricao nao localizada para exclusao.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $erros = [];
        if ((int)$inscricao->deleted === 1) {
            $erros[] = 'Este registro ja foi inativado anteriormente.';
        }
        if ((int)$inscricao->vigente === 1) {
            $erros[] = 'O registro esta vigente. Para inativar, utilize a funcao de cancelamento.';
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            if (!empty($erros)) {
                $this->Flash->error(implode('<br>', $erros), ['escape' => false]);
                return $this->redirect(['action' => 'deletar', (int)$inscricao->id]);
            }

            $dados = $this->request->getData();
            $motivoCancelamentoId = (int)($dados['motivo_cancelamento_id'] ?? 0);
            $justificativa = trim((string)($dados['justificativa_cancelamento'] ?? ''));
            $errosValidacao = [];
            if ($motivoCancelamentoId <= 0) {
                $errosValidacao[] = 'Informe o motivo do cancelamento.';
            }
            if ($justificativa === '') {
                $errosValidacao[] = 'Informe a justificativa da exclusao.';
            }
            if (!empty($errosValidacao)) {
                $this->Flash->error(implode('<br>', $errosValidacao), ['escape' => false]);
                return $this->redirect(['action' => 'deletar', (int)$inscricao->id]);
            }

            try {
                $tblProjetoBolsistas->getConnection()->transactional(function () use ($tblProjetoBolsistas, $inscricao, $motivoCancelamentoId, $justificativa, $identity) {
                    $faseOriginal = (int)$inscricao->fase_id;
                    $inscricaoPatch = $tblProjetoBolsistas->patchEntity($inscricao, [
                        'deleted' => 1,
                        'vigente' => 0,
                        'motivo_cancelamento_id' => $motivoCancelamentoId,
                        'justificativa_cancelamento' => $justificativa,
                    ]);
                    $tblProjetoBolsistas->saveOrFail($inscricaoPatch);
                    $this->historico(
                        (int)$inscricao->id,
                        $faseOriginal,
                        $faseOriginal,
                        'Registro excluido: ' . $justificativa,
                        true
                    );

                    // Se for renovacao excluida, reativa a referencia para fase ativa.
                    if (strtoupper((string)($inscricao->origem ?? '')) === 'R' && !empty($inscricao->referencia_inscricao_anterior)) {
                        $referencia = $tblProjetoBolsistas->find()
                            ->where([
                                'ProjetoBolsistas.id' => (int)$inscricao->referencia_inscricao_anterior,
                                'ProjetoBolsistas.deleted' => 0,
                            ])
                            ->first();
                        if ($referencia && (int)$referencia->fase_id === 19) {
                            $faseOriginalReferencia = (int)$referencia->fase_id;
                            $referenciaPatch = $tblProjetoBolsistas->patchEntity($referencia, ['fase_id' => 11]);
                            $tblProjetoBolsistas->saveOrFail($referenciaPatch);
                            $this->historico(
                                (int)$referencia->id,
                                $faseOriginalReferencia,
                                11,
                                'Status alterado para Ativo pois a renovacao #' . (int)$inscricao->id . ' foi excluida.',
                                true
                            );
                        }
                    }

                    // Se estiver em processo de substituicao (fase 15), reativa a inscricao anterior.
                    if ($faseOriginal === 15 && !empty($inscricao->bolsista_anterior)) {
                        $inscricaoAnterior = $tblProjetoBolsistas->find()
                            ->where([
                                'ProjetoBolsistas.id' => (int)$inscricao->bolsista_anterior,
                                'ProjetoBolsistas.deleted' => 0,
                            ])
                            ->first();
                        if ($inscricaoAnterior) {
                            $faseAnteriorOriginal = (int)$inscricaoAnterior->fase_id;
                            $anteriorPatch = $tblProjetoBolsistas->patchEntity($inscricaoAnterior, [
                                'fase_id' => 11,
                                'vigente' => 1,
                                'data_fim' => null,
                            ]);
                            $tblProjetoBolsistas->saveOrFail($anteriorPatch);
                            $this->historico(
                                (int)$inscricaoAnterior->id,
                                $faseAnteriorOriginal,
                                11,
                                'Inscricao reativada apos exclusao da substituicao #' . (int)$inscricao->id . '.',
                                true
                            );
                        }
                    }

                    $this->fetchTable('Raics')->updateAll(
                        ['deleted' => 1],
                        ['projeto_bolsista_id' => (int)$inscricao->id, 'deleted' => 0]
                    );
                });
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - exclusao de inscricao no PadraoController',
                    'Nao foi possivel concluir a exclusao.'
                );
                return $this->redirect(['action' => 'deletar', (int)$inscricao->id]);
            }

            $this->Flash->success('Exclusao realizada com sucesso.');
            return $this->redirect(['action' => 'visualizar', (int)$inscricao->id]);
        }

        $motivos = $this->fetchTable('MotivoCancelamentos')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])->toArray();

        $this->set(compact('inscricao', 'motivos', 'erros'));
    }

    public function reativar($inscricaoId = null)
    {
        if (!$this->ehTi()) {
            $this->Flash->error('Somente TI pode desfazer a substituicao.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        if (empty($inscricaoId)) {
            $this->Flash->error('Inscricao nao informada.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $inscricao = $tblProjetoBolsistas->find()
            ->where(['ProjetoBolsistas.id' => (int)$inscricaoId])
            ->first();
        if (!$inscricao) {
            $this->Flash->error('Inscricao nao localizada.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $faseOriginal = (int)$inscricao->fase_id;
        $erros = [];
        if ((int)$inscricao->deleted === 1) {
            $erros[] = 'O registro esta inativado. Nao pode ser alterado.';
        }
        if ($faseOriginal === 14) {
            $contaSubstituto = $tblProjetoBolsistas->find()
                ->where([
                    'ProjetoBolsistas.bolsista_anterior' => (int)$inscricao->id,
                    'ProjetoBolsistas.deleted' => 0,
                ])
                ->count();
            if ($contaSubstituto > 0) {
                $erros[] = 'E necessario primeiro inativar/cancelar a inscricao indicada para substituir esta.';
            }
        }

        $textoDataFim = !empty($inscricao->data_fim)
            ? 'A data de finalizacao da bolsa cadastrada (' . $inscricao->data_fim->i18nFormat('yyyy-MM-dd') . ') foi removida. O bolsista esta vigente novamente.'
            : 'Nao havia data de finalizacao registrada. O bolsista esta vigente novamente.';

        if ($this->request->is(['post', 'put', 'patch'])) {
            if (!empty($erros)) {
                $this->Flash->error(implode('<br>', $erros), ['escape' => false]);
                return $this->redirect(['action' => 'reativar', (int)$inscricao->id]);
            }

            $dados = $this->request->getData();
            $motivoCancelamentoId = (int)($dados['motivo_cancelamento_id'] ?? 0);
            $justificativa = trim((string)($dados['justificativa_cancelamento'] ?? ''));
            if ($motivoCancelamentoId <= 0 || $justificativa === '') {
                $this->Flash->error('Informe motivo e justificativa da alteracao.');
                return $this->redirect(['action' => 'reativar', (int)$inscricao->id]);
            }

            try {
                $tblProjetoBolsistas->getConnection()->transactional(function () use ($tblProjetoBolsistas, $inscricao, $motivoCancelamentoId, $justificativa, $textoDataFim, $faseOriginal) {
                    $inscricaoPatch = $tblProjetoBolsistas->patchEntity($inscricao, [
                        'fase_id' => 11,
                        'vigente' => 1,
                        'data_fim' => null,
                        'motivo_cancelamento_id' => $motivoCancelamentoId,
                        'justificativa_cancelamento' => $justificativa,
                    ]);
                    $tblProjetoBolsistas->saveOrFail($inscricaoPatch);
                    $this->historico(
                        (int)$inscricao->id,
                        $faseOriginal,
                        11,
                        $justificativa . ' ' . $textoDataFim,
                        true
                    );
                });
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - reativar inscricao no PadraoController',
                    'Nao foi possivel gravar a reativacao.'
                );
                return $this->redirect(['action' => 'reativar', (int)$inscricao->id]);
            }

            $this->Flash->success('Alteracao gravada com sucesso.');
            return $this->redirect(['action' => 'visualizar', (int)$inscricao->id]);
        }

        $motivos = $this->fetchTable('MotivoCancelamentos')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])->toArray();

        $this->set(compact('inscricao', 'motivos', 'erros'));
    }

    public function trocaorientador($inscricaoId = null)
    {
        if (!$this->ehTi()) {
            $this->Flash->error('Restrito.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        if (empty($inscricaoId)) {
            $this->Flash->error('Inscricao nao informada.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $inscricao = $tblProjetoBolsistas->find()
            ->contain(['Projetos'])
            ->where(['ProjetoBolsistas.id' => (int)$inscricaoId])
            ->first();
        if (!$inscricao) {
            $this->Flash->error('Inscricao nao localizada.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();
            $novoOrientador = !empty($dados['coorientador']) ? (int)$dados['coorientador'] : null;
            $justificativa = trim((string)($dados['justificativa_cancelamento'] ?? ''));

            if ($justificativa === '') {
                $this->Flash->error('Informe a justificativa da alteracao.');
                return $this->redirect(['action' => 'trocaorientador', (int)$inscricao->id]);
            }

            try {
                $tblProjetoBolsistas->getConnection()->transactional(function () use ($tblProjetoBolsistas, $inscricao, $novoOrientador, $justificativa) {
                    $orientadorDestino = $novoOrientador ?: (int)($inscricao->coorientador ?? 0);
                    if ($orientadorDestino <= 0) {
                        throw new \RuntimeException('Informe o ID do novo orientador ou tenha coorientador definido na inscricao.');
                    }

                    $colunas = $tblProjetoBolsistas->getSchema()->columns();
                    $nova = $tblProjetoBolsistas->newEmptyEntity();
                    foreach ($colunas as $coluna) {
                        if (in_array($coluna, ['id', 'created', 'modified'], true)) {
                            continue;
                        }
                        $nova->set($coluna, $inscricao->get($coluna));
                    }

                    $origemAntiga = strtoupper((string)($inscricao->origem ?? ''));
                    $nova->orientador = $orientadorDestino;
                    $nova->coorientador = null;
                    $nova->origem = in_array($origemAntiga, ['A', 'S'], true) ? $origemAntiga : 'T';
                    $nova->resultado = null;
                    $nova->revista_id = null;
                    $nova->autorizacao = null;
                    $nova->data_resposta_coorientador = null;
                    $nova->resposta_coorientador = null;
                    $nova->justificativa_recusa_coorientador = null;
                    $nova->revista_orientador = null;
                    $nova->revista_bolsista = null;
                    $nova->filhos_menor = null;
                    $nova->referencia_inscricao_anterior = (int)$inscricao->id;

                    $novaSalva = $tblProjetoBolsistas->save($nova);
                    if (!$novaSalva) {
                        throw new \RuntimeException('Erro ao salvar a nova inscricao para troca de orientador.');
                    }

                    $faseNova = (int)($nova->fase_id ?? 0);
                    $this->historico(
                        (int)$nova->id,
                        $faseNova,
                        $faseNova,
                        'Troca de orientador - Inscricao original #' . (int)$inscricao->id . '. Justificativa: ' . $justificativa,
                        true
                    );

                    $faseAntiga = (int)($inscricao->fase_id ?? 0);
                    $inscricaoPatch = $tblProjetoBolsistas->patchEntity($inscricao, [
                        'origem' => 'T',
                        'vigente' => 0,
                    ]);
                    $salvouAntiga = $tblProjetoBolsistas->save($inscricaoPatch);
                    if (!$salvouAntiga) {
                        throw new \RuntimeException('Erro ao atualizar a inscricao original.');
                    }
                    $this->historico(
                        (int)$inscricao->id,
                        $faseAntiga,
                        $faseAntiga,
                        'Troca de orientador - Inscricao alterada #' . (int)$nova->id . '. Justificativa: ' . $justificativa,
                        true
                    );

                    $tblAnexos = $this->fetchTable('Anexos');
                    $anexosOriginais = $tblAnexos->find()
                        ->where([
                            'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                            'OR' => [
                                'Anexos.deleted IS' => null,
                                'Anexos.deleted' => 0,
                            ],
                        ])
                        ->all();

                    foreach ($anexosOriginais as $anexo) {
                        $novoAnexo = $tblAnexos->newEmptyEntity();
                        $novoAnexo->projeto_id = $anexo->projeto_id;
                        $novoAnexo->projeto_bolsista_id = (int)$nova->id;
                        $novoAnexo->anexos_tipo_id = $anexo->anexos_tipo_id;
                        $novoAnexo->anexo = $anexo->anexo;
                        $novoAnexo->created = $anexo->created;
                        $novoAnexo->modified = $anexo->modified;
                        $novoAnexo->deleted = $anexo->deleted;
                        $novoAnexo->usuario_id = $anexo->usuario_id;
                        if (!$tblAnexos->save($novoAnexo)) {
                            throw new \RuntimeException('Erro ao copiar anexos da inscricao original.');
                        }
                    }

                    if (!empty($inscricao->projeto_id)) {
                        $tblProjetos = $this->fetchTable('Projetos');
                        $projeto = $tblProjetos->find()
                            ->where(['Projetos.id' => (int)$inscricao->projeto_id])
                            ->first();
                        if ($projeto) {
                            $projeto->usuario_id = $orientadorDestino;
                            if (!$tblProjetos->save($projeto)) {
                                throw new \RuntimeException('Erro ao atualizar orientador do projeto.');
                            }
                        }
                    }
                });
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - troca de orientador no PadraoController',
                    'Houve um erro na gravacao. Tente novamente.'
                );
                return $this->redirect(['controller' => 'Index', 'action' => 'index']);
            }

            $this->Flash->success('Troca de orientador gravada com sucesso.');
            return $this->redirect(['action' => 'visualizar', (int)$inscricao->id]);
        }

        $this->set('id', (int)$inscricaoId);
    }
}
