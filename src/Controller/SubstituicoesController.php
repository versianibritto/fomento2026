<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\I18n\FrozenTime;

class SubstituicoesController extends AppController
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

    public function iniciar($inscricaoId = null)
    {
        $this->Flash->error('Fluxo de substituição temporariamente indisponível.');
        return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'V']);

        $identity = $this->identityLogado;
        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');

        if (empty($inscricaoId)) {
            $this->Flash->error('Inscricao não informada para substituicao.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'V']);
        }

        $conditions = [
            'ProjetoBolsistas.id' => (int)$inscricaoId,
            'ProjetoBolsistas.deleted IS' => null,
        ];
        if (!$this->ehTi()) {
            $conditions['ProjetoBolsistas.orientador'] = (int)$identity->id;
        }

        $inscricao = $tblProjetoBolsistas->find()
            ->contain(['Bolsistas', 'Orientadores', 'Editais' => ['Programas']])
            ->select([
                'id',
                'editai_id',
                'fase_id',
                'origem',
                'programa',
                'programa_id',
                'data_inicio',
                'orientador',
                'bolsista',
                'coorientador',
                'projeto_id',
                'sp_titulo',
                'sp_resumo',
                'tipo_bolsa',
                'cota',
                'vigente',
            ])
            ->where($conditions)
            ->first();
        if (!$inscricao) {
            $this->Flash->error('Inscricao não localizada: está deletada ou o usuario logado não tem acesso para substituicao (somente o orientador poderá realizar a substituição).');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'V']);
        }

        if (!in_array((int)$inscricao->fase_id, [11, 16], true)) {
            $this->Flash->error('Somente inscrições Ativas podem ser substituidas.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'V']);
        }

        if ((string)$inscricao->origem === 'S') {
            $this->Flash->error('Bolsa ativada por substituição não poderá passar por nova substituição.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'V']);
        }

        $editalId = (int)($inscricao->editai_id ?? 0);
        $edital = null;
        if ($editalId > 0) {
            $edital = $this->fetchTable('Editais')->find()
                ->select(['id', 'nome', 'programa_id', 'inicio_inscricao', 'fim_inscricao', 'inicio_avaliar', 'fim_avaliar'])
                ->where(['Editais.id' => $editalId])
                ->first();
        }

        if (!$edital) {
            $this->Flash->error('Edital não localizado para substituicao.');
            return $this->redirect(['controller' => 'Padrao', 'action' => 'visualizar', (int)$inscricao->id]);
        }

        $bolsistaNome = trim((string)($inscricao->bolsista_usuario->nome ?? ''));
        if ($bolsistaNome === '' && !empty($inscricao->bolsista)) {
            $bolsistaNome = (string)($this->fetchTable('Usuarios')->find()
                ->select(['nome'])
                ->where(['Usuarios.id' => (int)$inscricao->bolsista])
                ->first()
                ?->nome ?? '');
        }

        $orientadorNome = trim((string)($inscricao->orientadore->nome ?? ''));
        if ($orientadorNome === '' && !empty($inscricao->orientador)) {
            $orientadorNome = (string)($this->fetchTable('Usuarios')->find()
                ->select(['nome'])
                ->where(['Usuarios.id' => (int)$inscricao->orientador])
                ->first()
                ?->nome ?? '');
        }

        $programaNome = '';
        if (!empty($inscricao->programa_id)) {
            $programaNome = (string)($this->fetchTable('Programas')->find()
                ->select(['sigla'])
                ->where(['Programas.id' => (int)$inscricao->programa_id])
                ->first()
                ?->sigla ?? '');
        }
        if ($programaNome === '') {
            $programaNome = trim((string)($inscricao->editai->programa->sigla ?? ''));
        }
        if ($programaNome === '' && !empty($edital->programa_id)) {
            $programaNome = (string)($this->fetchTable('Programas')->find()
                ->select(['sigla'])
                ->where(['Programas.id' => (int)$edital->programa_id])
                ->first()
                ?->sigla ?? '');
        }
        if ($programaNome === '') {
            $programaNome = trim((string)($inscricao->programa ?? ''));
        }

        if ((int)$inscricao->programa_id === 1 && !in_array((int)$identity->id, [1, 8088], true)) {
            if (empty($inscricao->data_inicio)) {
                $this->Flash->error('A inscrição não possui data de início para validar a substituição.');
                return $this->redirect(['controller' => 'Padrao', 'action' => 'visualizar', (int)$inscricao->id]);
            }

            $dataInicio = FrozenTime::instance($inscricao->data_inicio);
            $primeiroDia = $dataInicio->addMonths(2)->startOfMonth()->startOfDay();
            $ultimoDia = $dataInicio->addMonths(6)->endOfMonth()->endOfDay();
            $hoje = FrozenTime::now();
            $dataInicioFormatada = $dataInicio->i18nFormat('dd/MM/yyyy');

            if ($hoje < $primeiroDia) {
                $this->Flash->error(
                    'Fora do período mínimo de substituição. Tente novamente a partir de ' .
                    $primeiroDia->i18nFormat('dd/MM/yyyy') .
                    '. (Data de início do bolsista: ' . $dataInicioFormatada . ').'
                );
                return $this->redirect(['controller' => 'Padrao', 'action' => 'visualizar', (int)$inscricao->id]);
            }

            if ($hoje > $ultimoDia) {
                $this->Flash->error(
                    'Fora do período máximo de substituição. O prazo para esta substituição terminou em ' .
                    $ultimoDia->i18nFormat('dd/MM/yyyy') .
                    '. (Data de início do bolsista: ' . $dataInicioFormatada . ').'
                );
                return $this->redirect(['controller' => 'Padrao', 'action' => 'visualizar', (int)$inscricao->id]);
            }
        }

        if ((int)$inscricao->programa_id > 1 && !$this->ehTi()) {
            $liberadoPeloInicio = false;
            if (!empty($inscricao->data_inicio)) {
                $dataInicio = FrozenTime::instance($inscricao->data_inicio);
                $limiteInicio = $dataInicio->startOfMonth()->addDays(4)->endOfDay();
                $liberadoPeloInicio = FrozenTime::now() <= $limiteInicio;
            }

            if (!$liberadoPeloInicio && !$this->loadPeriodo($edital, $identity, 4, [], [(int)$inscricao->id])) {
                $this->Flash->error('Substituicao fora do periodo permitido para este edital.');
                return $this->redirect(['controller' => 'Padrao', 'action' => 'visualizar', (int)$inscricao->id]);
            }
        }

        $substituicao = $this->buscarSubstituicaoPendente((int)$inscricao->id);

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();
            $motivoCancelamentoId = (int)($dados['motivo_cancelamento_id'] ?? 0);
            $justificativa = trim((string)($dados['justificativa_substituicao'] ?? ''));
            $erros = [];

            if ($motivoCancelamentoId <= 0) {
                $erros[] = 'Informe o motivo da substituição.';
            }
            if ($justificativa === '') {
                $erros[] = 'Informe a justificativa da substituição.';
            } elseif ((function_exists('mb_strlen') ? mb_strlen($justificativa) : strlen($justificativa)) < 20) {
                $erros[] = 'A justificativa da substituição deve ter pelo menos 20 caracteres.';
            }

            if (!empty($erros)) {
                $this->Flash->error(implode('<br>', $erros), ['escape' => false]);
            } else {
                $substituicao = $this->criarOuCarregarSubstituicao($inscricao, $edital);
                if (!$substituicao) {
                    $this->Flash->error('Não foi possível iniciar a substituição.');
                    return $this->redirect(['controller' => 'Padrao', 'action' => 'visualizar', (int)$inscricao->id]);
                }

                try {
                    $tblProjetoBolsistas->getConnection()->transactional(function () use ($tblProjetoBolsistas, $inscricao, $substituicao, $motivoCancelamentoId, $justificativa) {
                        $faseSubstituicao = (int)$substituicao->fase_id;
                        $substituicaoPatch = $tblProjetoBolsistas->patchEntity($substituicao, [
                            'justificativa' => $justificativa,
                            'motivo_cancelamento_id' => $motivoCancelamentoId,
                        ]);
                        $tblProjetoBolsistas->saveOrFail($substituicaoPatch);
                        $this->historico(
                            (int)$substituicao->id,
                            $faseSubstituicao,
                            $faseSubstituicao,
                            'Dados iniciais da substituição informados. Justificativa: ' . $justificativa,
                            true
                        );

                        $faseAnterior = (int)$inscricao->fase_id;
                        $inscricaoPatch = $tblProjetoBolsistas->patchEntity($inscricao, [
                            'fase_id' => 14,
                            'motivo_cancelamento_id' => $motivoCancelamentoId,
                            'justificativa_cancelamento' => $justificativa,
                        ]);
                        $tblProjetoBolsistas->saveOrFail($inscricaoPatch);
                        $this->historico(
                            (int)$inscricao->id,
                            $faseAnterior,
                            14,
                            $justificativa,
                            true
                        );
                    });

                    $this->Flash->success('Etapa inicial da substituição gravada com sucesso.');
                    return $this->redirect(['action' => 'dadosBolsista', (int)$substituicao->id]);
                } catch (\Throwable $e) {
                    $this->flashFriendlyException(
                        $e,
                        'Erro no Sistema - iniciar substituicao',
                        'Não foi possível gravar a etapa inicial da substituição.'
                    );
                }
            }
        }

        $motivos = $this->fetchTable('MotivoCancelamentos')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])->toArray();

        $this->set(compact('inscricao', 'edital', 'motivos', 'substituicao', 'bolsistaNome', 'orientadorNome', 'programaNome'));
        return;
    }

    public function dadosBolsista($inscricaoId = null)
    {
        $context = $this->carregarContextoSubstituicao($inscricaoId);
        if (isset($context['redirect'])) {
            return $context['redirect'];
        }

        $edital = $context['edital'];
        $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $inscricao = $projetoBolsistas->find()
            ->contain(['Bolsistas'])
            ->where([
                'ProjetoBolsistas.id' => (int)$context['inscricao']->id,
                'ProjetoBolsistas.editai_id' => (int)$edital->id,
                'ProjetoBolsistas.deleted IS' => null,
            ])
            ->first();
        if (!$inscricao) {
            $this->Flash->error('Inscrição de substituição não localizada.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'V']);
        }

        $anexosTiposBase = $this->fetchTable('AnexosTipos')->find()
            ->where([
                'AnexosTipos.bloco' => 'B',
                'AnexosTipos.deleted' => 0,
            ])
            ->orderBy(['AnexosTipos.id' => 'ASC'])
            ->all();

        $programaEditalId = (int)($edital->programa_id ?? 0);
        $cotaAtual = (string)($inscricao->cota ?? '');
        $primeiroPeriodoAtual = $inscricao->primeiro_periodo !== null ? (string)(int)$inscricao->primeiro_periodo : '';

        $bolsistaMenorIdade = false;
        if (!empty($inscricao->bolsista_usuario) && !empty($inscricao->bolsista_usuario->data_nascimento)) {
            $dataNascimento = $inscricao->bolsista_usuario->data_nascimento;
            if (is_object($dataNascimento) && method_exists($dataNascimento, 'i18nFormat')) {
                $dataNascimento = $dataNascimento->i18nFormat('yyyy-MM-dd');
            }
            $idadeBolsista = (int)$this->idade((string)$dataNascimento, false);
            $bolsistaMenorIdade = $idadeBolsista < 18;
        }

        $anexosTiposDefault = [];
        $anexosTiposPrograma = [];
        $anexosTiposCota = [];
        $anexosTiposPrimeiroPeriodo = [];
        $anexosTiposCondicional = [];
        foreach ($anexosTiposBase as $tipoAnexo) {
            $tipoId = (int)$tipoAnexo->id;
            $condicional = (int)($tipoAnexo->condicional ?? 0);
            $programaRegra = trim((string)($tipoAnexo->programa ?? ''));
            $cotaRegra = strtoupper(trim((string)($tipoAnexo->cota ?? '')));

            if ($tipoId === 16) {
                $anexosTiposPrimeiroPeriodo[] = $tipoAnexo;
                continue;
            }

            if ($condicional === 0) {
                if ($programaRegra === '' && $cotaRegra === '') {
                    $anexosTiposDefault[] = $tipoAnexo;
                    continue;
                }
                if ($cotaRegra === '' && $programaRegra !== '') {
                    $programas = array_filter(array_map('trim', explode(',', $programaRegra)));
                    if (in_array((string)$programaEditalId, $programas, true)) {
                        $anexosTiposPrograma[] = $tipoAnexo;
                    }
                    continue;
                }
                if ($programaRegra === '' && $cotaRegra !== '') {
                    $anexosTiposCota[] = $tipoAnexo;
                }
                continue;
            }

            if ($programaRegra !== '' || $cotaRegra !== '') {
                continue;
            }
            if (in_array($tipoId, [18, 19], true) && $bolsistaMenorIdade) {
                $anexosTiposCondicional[] = $tipoAnexo;
            }
        }

        $anexos = [];
        $tiposBaseIds = array_values(array_unique(array_map(static fn($tipoBase) => (int)$tipoBase->id, iterator_to_array($anexosTiposBase))));
        if (!empty($tiposBaseIds)) {
            $anexosBol = $this->fetchTable('Anexos')->find()
                ->select(['id', 'anexos_tipo_id', 'anexo'])
                ->where([
                    'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                    'Anexos.anexos_tipo_id IN' => $tiposBaseIds,
                    'Anexos.deleted IS' => null,
                ])
                ->orderBy(['Anexos.id' => 'DESC'])
                ->all();
            foreach ($anexosBol as $an) {
                $tipoId = (int)$an->anexos_tipo_id;
                if (!isset($anexos[$tipoId])) {
                    $anexos[$tipoId] = (string)$an->anexo;
                }
            }
        }

        $cotas = [
            'G' => 'Geral',
            'I' => 'Pessoas Indígenas',
            'N' => 'Pessoas Negras (Pretos/Pardos)',
            'T' => 'Pessoas Trans',
            'D' => 'Pessoas com deficiência',
        ];
        $cotaTravada = $cotaAtual !== '';
        if ($cotaTravada) {
            $cotasDisponiveis = [$cotaAtual => ($cotas[$cotaAtual] ?? $cotaAtual)];
        } else {
            $cotasDisponiveis = $cotas;
        }

        $cpfVincular = (string)$this->request->getQuery('cpf_vincular');
        if ($cpfVincular !== '' && !$this->validaCPF($cpfVincular)) {
            $this->Flash->error('CPF inválido para vinculação.');
            return $this->redirect(['action' => 'dadosBolsista', $inscricao->id]);
        }

        if ($cpfVincular !== '') {
            $cpfVincularNumerico = preg_replace('/\D/', '', $cpfVincular);
            $bolsistaVincular = $this->fetchTable('Usuarios')->find()
                ->select(['id'])
                ->where(['Usuarios.cpf' => $cpfVincularNumerico])
                ->first();

            if (!$bolsistaVincular) {
                return $this->redirect([
                    'controller' => 'Users',
                    'action' => 'cadastrarUsuario',
                    $cpfVincularNumerico,
                    'B',
                    (int)$inscricao->id,
                    (int)$edital->id,
                    'S',
                ]);
            }

            try {
                $this->checkBolsistaSubstituicao((int)$bolsistaVincular->id);
            } catch (\Exception $e) {
                $this->Flash->error($e->getMessage());
                return $this->redirect(['action' => 'dadosBolsista', $inscricao->id]);
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - validar bolsista na substituição',
                    'Não foi possível validar o usuário informado.'
                );
                return $this->redirect(['action' => 'dadosBolsista', $inscricao->id]);
            }

            try {
                $faseOriginal = (int)$inscricao->fase_id;
                $inscricao = $projetoBolsistas->patchEntity($inscricao, [
                    'bolsista' => (int)$bolsistaVincular->id,
                ]);
                $projetoBolsistas->saveOrFail($inscricao);
                $this->limparAnexosPorBloco((int)$inscricao->id, 'B');
                $this->historico((int)$inscricao->id, $faseOriginal, $faseOriginal, 'Vinculação de usuário cadastrada no Dados Bolsista', true);
                $this->Flash->success('Usuário vinculado à substituição com sucesso.');
                return $this->redirect(['action' => 'dadosBolsista', $inscricao->id]);
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - vincular bolsista na substituição',
                    'Não foi possível vincular o usuário informado.'
                );
                return $this->redirect(['action' => 'dadosBolsista', $inscricao->id]);
            }
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();
            $primeiroPeriodoInformado = array_key_exists('primeiro_periodo', $dados)
                ? (string)$dados['primeiro_periodo']
                : $primeiroPeriodoAtual;
            if ($primeiroPeriodoInformado !== '' && !in_array($primeiroPeriodoInformado, ['0', '1'], true)) {
                $this->Flash->error('Informe corretamente se o bolsista está no primeiro período.');
                return $this->redirect(['action' => 'dadosBolsista', $inscricao->id]);
            }

            $tiposAnexosPermitidos = [];
            foreach ($anexosTiposDefault as $tipoAnexo) {
                $tiposAnexosPermitidos[] = (int)$tipoAnexo->id;
            }
            foreach ($anexosTiposPrograma as $tipoAnexo) {
                $tiposAnexosPermitidos[] = (int)$tipoAnexo->id;
            }
            $cotaProcessada = strtoupper(trim((string)$cotaAtual));
            foreach ($anexosTiposCota as $tipoAnexo) {
                $cotaRegra = strtoupper(trim((string)($tipoAnexo->cota ?? '')));
                if ($cotaRegra === '') {
                    continue;
                }
                $cotasRegra = array_filter(array_map('trim', explode(',', $cotaRegra)));
                if (in_array($cotaProcessada, $cotasRegra, true)) {
                    $tiposAnexosPermitidos[] = (int)$tipoAnexo->id;
                }
            }
            if ($bolsistaMenorIdade) {
                $tiposAnexosPermitidos[] = 18;
                $tiposAnexosPermitidos[] = 19;
            }
            if ($primeiroPeriodoInformado === '0') {
                $tiposAnexosPermitidos[] = 16;
            }
            $tiposAnexosPermitidos = array_values(array_unique($tiposAnexosPermitidos));
            $acaoRapidaAnexo = $this->processarAcaoRapidaAnexoInscricao(
                $dados,
                $inscricao->projeto_id !== null ? (int)$inscricao->projeto_id : null,
                (int)$inscricao->id,
                $tiposAnexosPermitidos
            );
            if ($acaoRapidaAnexo !== null) {
                return $this->redirect(['action' => 'dadosBolsista', $inscricao->id]);
            }

            $acao = (string)($dados['acao'] ?? 'salvar_anexos');
            if (in_array($acao, ['vincular_bolsista', 'incluir_bolsista'], true)) {
                $cpfInformado = (string)($dados['cpf_bolsista'] ?? '');
                if (!$this->validaCPF($cpfInformado)) {
                    $this->Flash->error('Informe um CPF válido para o bolsista.');
                    return $this->redirect(['action' => 'dadosBolsista', $inscricao->id]);
                }
                $cpf = preg_replace('/\D/', '', $cpfInformado);

                $bolsista = $this->fetchTable('Usuarios')->find()
                    ->select(['id'])
                    ->where(['Usuarios.cpf' => $cpf])
                    ->first();
                if (!$bolsista) {
                    return $this->redirect([
                        'controller' => 'Users',
                        'action' => 'cadastrarUsuario',
                        $cpf,
                        'B',
                        (int)$inscricao->id,
                        (int)$edital->id,
                        'S',
                    ]);
                }

                try {
                    $this->checkBolsistaSubstituicao((int)$bolsista->id);
                } catch (\Exception $e) {
                    $this->Flash->error($e->getMessage());
                    return $this->redirect(['action' => 'dadosBolsista', $inscricao->id]);
                } catch (\Throwable $e) {
                    $this->flashFriendlyException(
                        $e,
                        'Erro no Sistema - validar bolsista na substituição',
                        'Não foi possível validar o usuário informado.'
                    );
                    return $this->redirect(['action' => 'dadosBolsista', $inscricao->id]);
                }

                try {
                    $faseOriginal = (int)$inscricao->fase_id;
                    $inscricao = $projetoBolsistas->patchEntity($inscricao, [
                        'bolsista' => (int)$bolsista->id,
                    ]);
                    $projetoBolsistas->saveOrFail($inscricao);
                    $this->limparAnexosPorBloco((int)$inscricao->id, 'B');
                    $this->historico((int)$inscricao->id, $faseOriginal, $faseOriginal, 'Vinculação de usuário cadastrada no Dados Bolsista', true);
                    $this->Flash->success('Usuário vinculado à substituição com sucesso.');
                } catch (\Throwable $e) {
                    $this->flashFriendlyException(
                        $e,
                        'Erro no Sistema - vincular bolsista na substituição',
                        'Não foi possível vincular o usuário informado.'
                    );
                }
                return $this->redirect(['action' => 'dadosBolsista', $inscricao->id]);
            }

            if ($acao === 'excluir_bolsista') {
                try {
                    $faseOriginal = (int)$inscricao->fase_id;
                    $inscricao = $projetoBolsistas->patchEntity($inscricao, ['bolsista' => null]);
                    $projetoBolsistas->saveOrFail($inscricao);
                    $this->limparAnexosPorBloco((int)$inscricao->id, 'B');
                    $this->historico((int)$inscricao->id, $faseOriginal, $faseOriginal, 'Exclusão de bolsista no Dados Bolsista', true);
                    $this->Flash->success('Bolsista removido da substituição.');
                } catch (\Throwable $e) {
                    $this->flashFriendlyException(
                        $e,
                        'Erro no Sistema - excluir bolsista na substituição',
                        'Não foi possível remover o bolsista da substituição.'
                    );
                }
                return $this->redirect(['action' => 'dadosBolsista', $inscricao->id]);
            }

            $anexosUpload = $dados['anexos'] ?? [];
            if (!is_array($anexosUpload)) {
                $anexosUpload = [];
            }
            if (empty($inscricao->bolsista)) {
                $this->Flash->error('Vincule um bolsista antes de anexar os documentos.');
                return $this->redirect(['action' => 'dadosBolsista', $inscricao->id]);
            }

            $cotaInformada = $cotaAtual;
            if ($cotaInformada !== '' && !array_key_exists($cotaInformada, $cotas)) {
                $this->Flash->error('Cota inválida para este edital.');
                return $this->redirect(['action' => 'dadosBolsista', $inscricao->id]);
            }

            try {
                $inscricao = $projetoBolsistas->patchEntity($inscricao, [
                    'cota' => $cotaInformada !== '' ? $cotaInformada : null,
                    'primeiro_periodo' => $primeiroPeriodoInformado !== '' ? (int)$primeiroPeriodoInformado : null,
                ]);
                $projetoBolsistas->saveOrFail($inscricao);
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - salvar cota na substituição',
                    'Não foi possível salvar a cota do bolsista.'
                );
                return $this->redirect(['action' => 'dadosBolsista', $inscricao->id]);
            }

            if ($this->anexarInscricao($anexosUpload, $inscricao->projeto_id, $inscricao->id, null, true)) {
                $faseAtual = (int)$inscricao->fase_id;
                $this->historico((int)$inscricao->id, $faseAtual, $faseAtual, 'Atualização de dados do bolsista', true);
                $this->Flash->success('Dados do bolsista salvos com sucesso.');
                return $this->redirect(['action' => 'coorientador', $inscricao->id]);
            }

            $this->Flash->error('Não foi possível salvar os anexos. Tente novamente.');
            return $this->redirect(['action' => 'dadosBolsista', $inscricao->id]);
        }

        $this->set(compact(
            'anexos',
            'inscricao',
            'edital',
            'anexosTiposDefault',
            'anexosTiposPrograma',
            'anexosTiposCota',
            'anexosTiposPrimeiroPeriodo',
            'anexosTiposCondicional',
            'cotas',
            'cotasDisponiveis',
            'cotaTravada',
            'bolsistaMenorIdade'
        ));
        $this->render('dados_bolsista');
    }

    public function coorientador($inscricaoId = null)
    {
        $context = $this->carregarContextoSubstituicao($inscricaoId);
        if (isset($context['redirect'])) {
            return $context['redirect'];
        }

        $edital = $context['edital'];
        $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $inscricao = $projetoBolsistas->find()
            ->contain([
                'Coorientadores',
                'Anexos' => ['conditions' => 'Anexos.deleted IS NULL', 'AnexosTipos'],
            ])
            ->where([
                'ProjetoBolsistas.id' => (int)$context['inscricao']->id,
                'ProjetoBolsistas.editai_id' => (int)$edital->id,
                'ProjetoBolsistas.deleted IS' => null,
            ])
            ->first();

        if (!$inscricao) {
            $this->Flash->error('Inscrição de substituição não localizada. Reinicie o processo.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'V']);
        }

        $anexosTiposC = $this->fetchTable('AnexosTipos')->find()
            ->where([
                'AnexosTipos.bloco' => 'C',
                'AnexosTipos.deleted' => 0,
            ])
            ->orderBy(['AnexosTipos.id' => 'ASC'])
            ->all()
            ->toList();

        $tiposAnexosPermitidos = [];
        foreach ($anexosTiposC as $tipoAnexo) {
            $tiposAnexosPermitidos[] = (int)$tipoAnexo->id;
        }

        $anexos = [];
        foreach ($inscricao->anexos as $anexoAtual) {
            $tipoId = (int)$anexoAtual->anexos_tipo_id;
            if (!in_array($tipoId, $tiposAnexosPermitidos, true)) {
                continue;
            }
            $anexos[$tipoId] = (string)$anexoAtual->anexo;
        }

        $coorientadorUsuario = null;
        if (!empty($inscricao->coorientador)) {
            $coorientadorUsuario = $this->fetchTable('Usuarios')->find()
                ->where(['Usuarios.id' => (int)$inscricao->coorientador])
                ->first();
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();

            $acaoRapidaAnexo = $this->processarAcaoRapidaAnexoInscricao(
                $dados,
                $inscricao->projeto_id !== null ? (int)$inscricao->projeto_id : null,
                (int)$inscricao->id,
                $tiposAnexosPermitidos
            );
            if ($acaoRapidaAnexo !== null) {
                return $this->redirect(['action' => 'coorientador', $inscricao->id]);
            }

            $acao = (string)($dados['acao'] ?? 'salvar_anexos');
            if (in_array($acao, ['vincular_coorientador', 'incluir_coorientador'], true)) {
                $cpfInformado = (string)($dados['cpf_coorientador'] ?? '');
                if (!$this->validaCPF($cpfInformado)) {
                    $this->Flash->error('Informe um CPF válido para o coorientador.');
                    return $this->redirect(['action' => 'coorientador', $inscricao->id]);
                }
                $cpf = preg_replace('/\D/', '', $cpfInformado);

                $coorientador = $this->fetchTable('Usuarios')->find()
                    ->select(['id'])
                    ->where(['Usuarios.cpf' => $cpf])
                    ->first();

                if (!$coorientador) {
                    return $this->redirect([
                        'controller' => 'Users',
                        'action' => 'cadastrarUsuario',
                        $cpf,
                        'C',
                        (int)$inscricao->id,
                        (int)$edital->id,
                        'S',
                    ]);
                }

                try {
                    $programaAtual = (int)($inscricao->programa_id ?? $edital->programa_id ?? 0);
                    $mensagemElegibilidade = $this->checkCoorientador(
                        (int)$coorientador->id,
                        (int)$inscricao->orientador,
                        $programaAtual,
                        (int)($inscricao->editai_id ?? $edital->id ?? 0),
                        (int)$inscricao->id
                    );
                    if ($mensagemElegibilidade !== null) {
                        $this->Flash->error($mensagemElegibilidade);
                        return $this->redirect(['action' => 'coorientador', $inscricao->id]);
                    }

                    $faseOriginal = (int)$inscricao->fase_id;
                    $inscricao = $projetoBolsistas->patchEntity($inscricao, [
                        'coorientador' => (int)$coorientador->id,
                    ]);
                    $projetoBolsistas->saveOrFail($inscricao);
                    $this->limparAnexosPorBloco((int)$inscricao->id, 'C');
                    $this->historico((int)$inscricao->id, $faseOriginal, $faseOriginal, 'Vinculação de usuário cadastrada no Coorientador', true);
                    $this->Flash->success('Coorientador vinculado à substituição com sucesso.');
                } catch (\Throwable $e) {
                    $this->flashFriendlyException(
                        $e,
                        'Erro no sistema ao vincular o coorientador na substituição.',
                        'Não foi possível vincular o coorientador na substituição.'
                    );
                }

                return $this->redirect(['action' => 'coorientador', $inscricao->id]);
            }

            if ($acao === 'excluir_coorientador') {
                try {
                    $faseOriginal = (int)$inscricao->fase_id;
                    $inscricao = $projetoBolsistas->patchEntity($inscricao, ['coorientador' => null]);
                    $projetoBolsistas->saveOrFail($inscricao);
                    $this->limparAnexosPorBloco((int)$inscricao->id, 'C');
                    $this->historico((int)$inscricao->id, $faseOriginal, $faseOriginal, 'Exclusão de coorientador na substituição', true);
                    $this->Flash->success('Coorientador removido da substituição.');
                } catch (\Throwable $e) {
                    $this->flashFriendlyException(
                        $e,
                        'Erro no sistema ao excluir o coorientador da substituição.',
                        'Não foi possível remover o coorientador da substituição.'
                    );
                }

                return $this->redirect(['action' => 'coorientador', $inscricao->id]);
            }

            $anexosUpload = $dados['anexos'] ?? [];
            if (!is_array($anexosUpload)) {
                $anexosUpload = [];
            }
            if (empty($inscricao->coorientador)) {
                $this->Flash->error('Vincule um coorientador antes de anexar os documentos.');
                return $this->redirect(['action' => 'coorientador', $inscricao->id]);
            }

            if ($this->anexarInscricao($anexosUpload, $inscricao->projeto_id, $inscricao->id, null, true)) {
                $faseAtual = (int)$inscricao->fase_id;
                $this->historico((int)$inscricao->id, $faseAtual, $faseAtual, 'Atualização de dados do coorientador', true);
                $this->Flash->success('Dados do coorientador salvos com sucesso.');
                return $this->redirect(['action' => 'gerarTermo', $inscricao->id]);
            }

            $this->Flash->error('Não foi possível salvar os anexos. Tente novamente.');
            return $this->redirect(['action' => 'coorientador', $inscricao->id]);
        }

        $this->set(compact('inscricao', 'edital', 'anexosTiposC', 'anexos', 'coorientadorUsuario'));
        $this->set($context);
        $this->render('coorientador');
    }

    public function gerarTermo($inscricaoId = null)
    {
        $context = $this->carregarContextoSubstituicao($inscricaoId);
        if (isset($context['redirect'])) {
            return $context['redirect'];
        }

        $this->set($context);
        $this->render('gerar_termo');
    }

    public function finalizar($inscricaoId = null)
    {
        $context = $this->carregarContextoSubstituicao($inscricaoId);
        if (isset($context['redirect'])) {
            return $context['redirect'];
        }

        $this->set($context);
        $this->render('finalizar');
    }

    private function criarOuCarregarSubstituicao($bolsista, $edital)
    {
        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $tblAnexos = $this->fetchTable('Anexos');

        $substituicao = $this->buscarSubstituicaoPendente((int)$bolsista->id);
        if ($substituicao) {
            return $substituicao;
        }

        $dados = [
            'editai_id' => $bolsista->editai_id,
            'projeto_id' => $bolsista->projeto_id,
            'orientador' => $bolsista->orientador,
            'sp_titulo' => $bolsista->sp_titulo,
            'sp_resumo' => $bolsista->sp_resumo,
            'fase_id' => 15,
            'bolsista_anterior' => (int)$bolsista->id,
            'tipo_bolsa' => $bolsista->tipo_bolsa,
            'deleted' => null,
            'programa_id' => $bolsista->programa_id ?? $edital->programa_id ?? null,
            'cota' => $bolsista->cota,
            'coorientador' => $bolsista->coorientador,
            'origem' => ((int)$bolsista->fase_id === 16) ? 'A' : 'S',
            'vigente' => 0,
            'autorizacao' => 0,
            'data_substituicao' => date('Y-m-d H:i:s'),
            'bolsista' => null,
            'justificativa' => null,
            'data_inicio' => null,
            'data_fim' => null,
            'data_primeira' => null,
            'substituicao_confirmador' => null,
            'data_sub_confirmacao' => null,
            'pdj_inscricoe_id' => $bolsista->pdj_inscricoe_id,
            'heranca' => $bolsista->heranca,


        ];

        $substituicao = $tblProjetoBolsistas->newEmptyEntity();
        $substituicao = $tblProjetoBolsistas->patchEntity($substituicao, $dados);
        try {
            $tblProjetoBolsistas->getConnection()->transactional(function () use ($tblProjetoBolsistas, $tblAnexos, &$substituicao, $bolsista) {
                $substituicao = $tblProjetoBolsistas->saveOrFail($substituicao);
                $this->copiarAnexosBaseSubstituicao(
                    $tblAnexos,
                    (int)$bolsista->id,
                    (int)$substituicao->id
                );
                $this->historico(
                    (int)$substituicao->id,
                    15,
                    15,
                    'Criação da inscrição de substituição a partir da inscrição #' . (int)$bolsista->id,
                    true
                );
            });
        } catch (\Throwable $e) {
            $this->flashFriendlyException(
                $e,
                'Erro no Sistema - criar inscrição de substituição',
                'Não foi possível preparar a inscrição de substituição.'
            );
            return null;
        }

        return $substituicao;
    }

    private function buscarSubstituicaoPendente(int $inscricaoOrigemId)
    {
        return $this->fetchTable('ProjetoBolsistas')->find()
            ->where([
                'ProjetoBolsistas.bolsista_anterior' => $inscricaoOrigemId,
                'ProjetoBolsistas.deleted IS' => null,
                'ProjetoBolsistas.fase_id' => 15,
            ])
            ->orderBy(['ProjetoBolsistas.id' => 'DESC'])
            ->first();
    }

    private function carregarContextoSubstituicao($inscricaoId): array
    {
        $identity = $this->identityLogado;
        if (empty($inscricaoId)) {
            $this->Flash->error('Inscricao de substituição não informada.');
            return ['redirect' => $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'V'])];
        }

        $conditions = [
            'ProjetoBolsistas.id' => (int)$inscricaoId,
            'ProjetoBolsistas.deleted IS' => null,
            'ProjetoBolsistas.fase_id' => 15,
        ];
        if (!$this->ehTi()) {
            $conditions['ProjetoBolsistas.orientador'] = (int)$identity->id;
        }

        $inscricao = $this->fetchTable('ProjetoBolsistas')->find()
            ->contain([
                'Bolsistas',
                'Orientadores',
                'Coorientadores',
                'Editais',
                'Substitutos' => ['Bolsistas'],
            ])
            ->where($conditions)
            ->first();
        if (!$inscricao || empty($inscricao->bolsista_anterior)) {
            $this->Flash->error('Inscrição de substituição não localizada.');
            return ['redirect' => $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'V'])];
        }

        $edital = $inscricao->editai ?? null;
        if (!$edital) {
            $this->Flash->error('Edital não localizado para a substituição.');
            return ['redirect' => $this->redirect(['controller' => 'Padrao', 'action' => 'visualizar', (int)$inscricao->bolsista_anterior])];
        }

        return compact('inscricao', 'edital');
    }

    private function checkBolsistaSubstituicao(int $usuarioId): void
    {
        $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $temVigente = $projetoBolsistas->find()
            ->where([
                'ProjetoBolsistas.bolsista' => $usuarioId,
                'ProjetoBolsistas.deleted IS' => null,
                'ProjetoBolsistas.vigente' => 1,
                //'ProjetoBolsistas.fase_id' => 11,
            ])
            ->count();

        $temAndamento = $projetoBolsistas->find()
            ->where([
                'ProjetoBolsistas.bolsista' => $usuarioId,
                'ProjetoBolsistas.deleted IS' => null,
                'ProjetoBolsistas.fase_id <' => 10,
            ])
            ->count();

        if ($temVigente > 0 || $temAndamento > 0) {
            throw new \Exception('O bolsista informado possui bolsa ativa ou inscrição em andamento.');
        }
    }

    private function copiarAnexosBaseSubstituicao($tblAnexos, int $inscricaoOrigemId, int $inscricaoDestinoId): void
    {
        $anexos = $tblAnexos->find()
            ->contain(['AnexosTipos'])
            ->where([
                'Anexos.projeto_bolsista_id' => $inscricaoOrigemId,
                'Anexos.deleted IS' => null,
                'OR' => [
                    [
                        'AnexosTipos.bloco' => 'S',
                    ],
                    [
                        'AnexosTipos.bloco' => 'O',
                    ],
                    [
                        'AnexosTipos.bloco' => 'P',
                        'Anexos.anexos_tipo_id !=' => 5,
                    ],
                    [
                        'Anexos.anexos_tipo_id' => 9,
                    ],
                ],
            ])
            ->all();

        foreach ($anexos as $anexo) {
            $novoAnexo = $tblAnexos->newEmptyEntity();
            $novoAnexo->projeto_id = $anexo->projeto_id;
            $novoAnexo->projeto_bolsista_id = $inscricaoDestinoId;
            $novoAnexo->anexos_tipo_id = $anexo->anexos_tipo_id;
            $novoAnexo->anexo = $anexo->anexo;
            $novoAnexo->usuario_id = $anexo->usuario_id;
            $novoAnexo->raic_id = $anexo->raic_id;
            $novoAnexo->pdj_inscricoe_id = $anexo->pdj_inscricoe_id;
            $novoAnexo->bloco = $anexo->bloco;
            $tblAnexos->saveOrFail($novoAnexo);
        }
    }
}
