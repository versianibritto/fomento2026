<?php
namespace App\Controller;

use Cake\Event\EventInterface;

class PdjInscricoesController extends AppController
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

    public function direcionarAcao($editalId = null, $inscricaoId = null, $tipo = null)
    {
        $tipo = strtoupper(trim((string)$tipo));
        if (empty($editalId) || empty($inscricaoId) || $tipo === '') {
            $this->Flash->error('Parâmetros de direcionamento inválidos.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'A']);
        }

        if ($tipo === 'E') {
            return $this->redirect(['action' => 'dadosBolsista', (int)$editalId, (int)$inscricaoId]);
        }
        if ($tipo === 'T') {
            return $this->redirect(['action' => 'gerarTermo', (int)$editalId, (int)$inscricaoId]);
        }
        if ($tipo === 'F') {
            return $this->redirect(['action' => 'finalizar', (int)$editalId, (int)$inscricaoId]);
        }

        $this->Flash->error('Ação de direcionamento inválida.');
        return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'A']);
    }

    public function fluxoNova($editalId = null)
    {
        $context = $this->loadContext($editalId);
        if (isset($context['redirect'])) {
            return $context['redirect'];
        }

        $edital = $context['edital'];
        $identity = $context['identity'];
        $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');

        $inscricoesEmAndamento = $projetoBolsistas->find()
            ->where([
                'ProjetoBolsistas.orientador' => (int)$identity->id,
                'ProjetoBolsistas.programa_id' => (int)$edital->programa_id,
                'ProjetoBolsistas.deleted IS' => null,
                'ProjetoBolsistas.fase_id <' => 10,
            ])
            ->count();
        if ($inscricoesEmAndamento > 0) {
            $this->Flash->error('O(a) sr(a) já possui uma inscrição em andamento neste programa.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $vigentes = $projetoBolsistas->find()
            ->contain(['Bolsistas'])
            ->where([
                'ProjetoBolsistas.orientador' => (int)$identity->id,
                'ProjetoBolsistas.programa_id' => (int)$edital->programa_id,
                'ProjetoBolsistas.deleted IS' => null,
                'ProjetoBolsistas.fase_id' => 11,
            ])
            ->orderBy(['ProjetoBolsistas.id' => 'DESC']);

        if ($vigentes->count() > 0) {
            $this->set(compact('vigentes'));
            $this->set($context);
            return $this->render('confirmar_vigentes');
            /*
                Nessa tela, o usuário vê o aviso e escolhe:
                Cancelar: volta para o dashboard.
                Continuar: envia POST para:PdjInscricoesController::confirmarVigentes($editalId)
                confirmarVigentes() valida o POST e chama:$this->processaVigentes($edital, $identity)
            */

        }

        $inscricao = $this->criarOuCarregarInscricaoNova($edital, $identity);
        if (!$inscricao) {
            $this->Flash->error('Não foi possível iniciar a inscrição. Tente novamente.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        return $this->redirect([
            'action' => 'dadosBolsista',
            $edital->id,
            $inscricao->id,
        ]);
    }

    private function criarOuCarregarInscricaoNova($edital, $identity)
    {
        $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $inscricao = $projetoBolsistas->find()
            ->where([
                'ProjetoBolsistas.editai_id' => (int)$edital->id,
                'ProjetoBolsistas.orientador' => (int)$identity->id,
                'ProjetoBolsistas.origem' => 'N',
                'ProjetoBolsistas.fase_id' => 3,
                'ProjetoBolsistas.deleted IS' => null,
            ])
            ->orderBy(['ProjetoBolsistas.id' => 'DESC'])
            ->first();

        if ($inscricao) {
            return $inscricao;
        }

        $inscricao = $projetoBolsistas->newEmptyEntity();
        $inscricao = $projetoBolsistas->patchEntity($inscricao, [
            'editai_id' => (int)$edital->id,
            'orientador' => (int)$identity->id,
            'programa_id' => (int)$edital->programa_id,
            'origem' => 'N',
            'fase_id' => 3,
            'vigente' => 0,
            'deleted' => null,
            'autorizacao' => 0,
            'prorrogacao' => 0,
        ]);

        try {
            $projetoBolsistas->saveOrFail($inscricao, ['validate' => false, 'checkRules' => false]);
            $this->historico((int)$inscricao->id, 3, 3, 'Criacao de inscricao PDJ', true);
            return $inscricao;
        } catch (\Throwable $e) {
            $this->flashFriendlyException($e, 'Erro no Sistema - criacao de inscricao PDJ');
            return null;
        }
    }

    private function processaVigentes($edital, $identity): bool
    {
        $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');

        try {
            $connection = $projetoBolsistas->getConnection();
            $connection->transactional(function () use ($projetoBolsistas, $identity, $edital) {
                $vigentes = $projetoBolsistas->find()
                    ->where([
                        'ProjetoBolsistas.orientador' => (int)$identity->id,
                        'ProjetoBolsistas.programa_id' => (int)$edital->programa_id,
                        'ProjetoBolsistas.deleted IS' => null,
                        'ProjetoBolsistas.vigente' => 1,
                    ])
                    ->all();

                $justificativa = 'Você possui inscrições vigentes neste programa.
                        Ao solicitar uma inscrição neste edital,
                        não será possível realizar a renovação delas.
                        Ao confirmar esta ação não será possível futuramente renová-las.
                        Deseja continuar?
                        O(a) Orientador(a) confirmou o processo.';
                foreach ($vigentes as $registro) {
                    $faseOriginal = (int)$registro->fase_id;
                    $registro = $projetoBolsistas->patchEntity($registro, [
                        'fase_id' => 18,
                    ]);
                    $projetoBolsistas->saveOrFail($registro, ['validate' => false, 'checkRules' => false]);

                    $this->historico((int)$registro->id, $faseOriginal, 18, $justificativa, true);
                }
            });
        } catch (\Throwable $e) {
            $this->flashFriendlyException($e, 'Erro no Sistema - confirmacao de vigentes PDJ');
            return false;
        }

        return true;
    }

    public function confirmarVigentes($editalId = null)
    {
        $context = $this->loadContext($editalId);
        if (isset($context['redirect'])) {
            return $context['redirect'];
        }

        if (!$this->request->is('post')) {
            return $this->redirect(['action' => 'fluxoNova', $editalId]);
        }

        $edital = $context['edital'];
        if (($edital->origem ?? null) !== 'N') {
            $this->Flash->error('Origem do edital inválida para esta operação.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $acao = (string)($this->request->getData('acao') ?? '');
        if ($acao !== 'confirmar') {
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $identity = $context['identity'];
        if (!$this->processaVigentes($edital, $identity)) {
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        return $this->redirect(['action' => 'fluxoNova', $edital->id]);
    }

    public function dadosBolsista($editalId = null, $inscricaoId = null)
    {
        $loaded = $this->loadPdjEditavel($editalId, $inscricaoId, ['Bolsistas']);
        if (isset($loaded['redirect'])) {
            return $loaded['redirect'];
        }
        [$context, $inscricao] = [$loaded['context'], $loaded['inscricao']];
        $edital = $context['edital'];

        if (!empty($inscricao->bolsista)) {
            $erroEscolaridade = $this->validarEscolaridadeBolsistaPdj((int)$inscricao->bolsista, $edital);
            if ($erroEscolaridade !== null) {
                $this->desvincularBolsistaEscolaridadeInvalida($edital, $inscricao, $erroEscolaridade);
                return $this->redirect(['action' => 'dadosBolsista', (int)$edital->id, (int)$inscricao->id]);
            }
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
        $bolsistaSexo = strtoupper(trim((string)($inscricao->bolsista_usuario->sexo ?? '')));
        $bolsistaSexoFeminino = $bolsistaSexo === 'F';
        $filhosMenorBolsistaAtual = (string)($inscricao->filhos_menor_bolsista ?? '');

        $anexosTiposDefault = [];
        $anexosTiposPrograma = [];
        $anexosTiposCota = [];
        $anexoTipoFilhosMenorBolsista = null;
        foreach ($anexosTiposBase as $tipoAnexo) {
            $tipoId = (int)$tipoAnexo->id;
            $condicional = (int)($tipoAnexo->condicional ?? 0);
            $programaRegra = trim((string)($tipoAnexo->programa ?? ''));
            $cotaRegra = strtoupper(trim((string)($tipoAnexo->cota ?? '')));

            if ($tipoId === 11) {
                $anexoTipoFilhosMenorBolsista = $tipoAnexo;
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
        }

        $anexos = $this->buscarAnexosProjetoBolsista((int)$inscricao->id, $anexosTiposBase);
        $cotas = [
            'G' => 'Geral',
            'I' => 'Pessoas Indígenas',
            'N' => 'Pessoas Negras (Pretos/Pardos)',
            'T' => 'Pessoas Trans',
            'D' => 'Pessoas com deficiência',
        ];
        if ((int)$edital->programa_id === 9) {
            $cotas = [
                'I' => 'Pessoas Indígenas',
            ];
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();
            $filhosMenorBolsistaInformado = array_key_exists('filhos_menor_bolsista', $dados)
                ? (string)$dados['filhos_menor_bolsista']
                : $filhosMenorBolsistaAtual;
            if (!$bolsistaSexoFeminino) {
                $filhosMenorBolsistaInformado = '';
            }
            if ($filhosMenorBolsistaInformado !== '' && !in_array($filhosMenorBolsistaInformado, ['0', '1', '2'], true)) {
                $this->Flash->error('Informe corretamente a opção de filhos menores do bolsista.');
                return $this->redirect(['action' => 'dadosBolsista', (int)$edital->id, (int)$inscricao->id]);
            }

            $tiposAnexosPermitidos = [];
            foreach ($anexosTiposDefault as $tipoAnexo) {
                $tiposAnexosPermitidos[] = (int)$tipoAnexo->id;
            }
            foreach ($anexosTiposPrograma as $tipoAnexo) {
                $tiposAnexosPermitidos[] = (int)$tipoAnexo->id;
            }
            $cotaProcessada = strtoupper(trim((string)($dados['cota'] ?? $cotaAtual)));
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
            if ($bolsistaSexoFeminino && (int)$filhosMenorBolsistaInformado > 0 && $anexoTipoFilhosMenorBolsista !== null) {
                $tiposAnexosPermitidos[] = (int)$anexoTipoFilhosMenorBolsista->id;
            }
            $tiposAnexosPermitidos = array_values(array_unique($tiposAnexosPermitidos));
            $acaoRapidaAnexo = $this->processarAcaoRapidaAnexoInscricao(
                $dados,
                $inscricao->projeto_id !== null ? (int)$inscricao->projeto_id : null,
                (int)$inscricao->id,
                $tiposAnexosPermitidos
            );
            if ($acaoRapidaAnexo !== null) {
                return $this->redirect(['action' => 'dadosBolsista', (int)$edital->id, (int)$inscricao->id]);
            }

            $acao = (string)($dados['acao'] ?? 'salvar_anexos');

            if (in_array($acao, ['vincular_bolsista', 'incluir_bolsista'], true)) {
                return $this->vincularUsuarioPdj($edital, $inscricao, 'B', (string)($dados['cpf_bolsista'] ?? ''));
            }
            if ($acao === 'excluir_bolsista') {
                return $this->removerVinculoPdj($edital, $inscricao, 'bolsista', 'dadosBolsista');
            }

            $anexosUpload = is_array($dados['anexos'] ?? null) ? $dados['anexos'] : [];
            $cotaInformada = (string)($dados['cota'] ?? '');
            if ($cotaInformada !== '' && !array_key_exists($cotaInformada, $cotas)) {
                $this->Flash->error('Cota inválida para este edital.');
                return $this->redirect(['action' => 'dadosBolsista', (int)$edital->id, (int)$inscricao->id]);
            }
            if (empty($inscricao->bolsista)) {
                $this->Flash->error('Vincule um bolsista antes de anexar os documentos.');
                return $this->redirect(['action' => 'dadosBolsista', (int)$edital->id, (int)$inscricao->id]);
            }

            $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');
            try {
                $inscricaoPatch = $projetoBolsistas->patchEntity($inscricao, [
                    'cota' => $cotaInformada !== '' ? $cotaInformada : null,
                    'filhos_menor_bolsista' => $filhosMenorBolsistaInformado !== '' ? $filhosMenorBolsistaInformado : null,
                ]);
                $projetoBolsistas->saveOrFail($inscricaoPatch, ['validate' => false, 'checkRules' => false]);

                if (!$this->anexarInscricao($anexosUpload, $inscricao->projeto_id ?? null, (int)$inscricao->id, null, true)) {
                    throw new \RuntimeException('Falha ao salvar anexos dos dados do bolsista PDJ.');
                }

                $this->historico((int)$inscricao->id, (int)$inscricao->fase_id, (int)$inscricao->fase_id, 'Atualizacao de dados do bolsista PDJ', true);
                $this->Flash->success('Dados do bolsista salvos com sucesso.');
                return $this->redirect(['action' => 'sumulaBolsista', (int)$edital->id, (int)$inscricao->id]);
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - salvar dados do bolsista PDJ',
                    'Não foi possível salvar os dados do bolsista.'
                );
                return $this->redirect(['action' => 'dadosBolsista', (int)$edital->id, (int)$inscricao->id]);
            }
        }

        $this->set(compact(
            'anexos',
            'inscricao',
            'anexosTiposDefault',
            'anexosTiposPrograma',
            'anexosTiposCota',
            'anexoTipoFilhosMenorBolsista',
            'cotas',
            'bolsistaSexoFeminino'
        ));
        $this->set($context);
    }

    public function sumulaBolsista($editalId = null, $inscricaoId = null)
    {
        return $this->sumulaPdj($editalId, $inscricaoId, 'B', 'sumula_bolsista', 'sumulaBolsista', 'sumulaOrientador');
    }

    public function sumulaOrientador($editalId = null, $inscricaoId = null)
    {
        return $this->sumulaPdj($editalId, $inscricaoId, 'O', 'sumula_orientador', 'sumulaOrientador', 'projeto');
    }

    public function projeto($editalId = null, $inscricaoId = null)
    {
        $loaded = $this->loadPdjEditavel($editalId, $inscricaoId, ['Projetos' => ['Areas', 'Linhas']]);
        if (isset($loaded['redirect'])) {
            return $loaded['redirect'];
        }
        [$context, $inscricao] = [$loaded['context'], $loaded['inscricao']];
        $edital = $context['edital'];

        $projetoSelecionado = $inscricao->projeto ?? $this->fetchTable('Projetos')->newEmptyEntity();
        $anexosTiposProjeto = $this->fetchTable('AnexosTipos')->find()
            ->where(['AnexosTipos.bloco' => 'P', 'AnexosTipos.deleted' => 0])
            ->orderBy(['AnexosTipos.id' => 'ASC'])
            ->all()
            ->toList();
        $tiposAnexosProjetoPermitidos = array_map(static function ($tipo) {
            return (int)$tipo->id;
        }, $anexosTiposProjeto);
        $anexos = $this->buscarAnexosProjetoBolsista((int)$inscricao->id, $anexosTiposProjeto);

        $grandesAreas = $this->fetchTable('GrandesAreas')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])->where(['GrandesAreas.id <' => 10])->orderBy(['GrandesAreas.nome' => 'ASC'])->toArray();

        $areasRows = $this->fetchTable('Areas')->find()
            ->select(['id', 'nome', 'grandes_area_id'])
            ->orderBy(['Areas.nome' => 'ASC'])
            ->all();
        $areas = [];
        $areasPorGrandeArea = [];
        foreach ($areasRows as $areaRow) {
            $areas[(int)$areaRow->id] = (string)$areaRow->nome;
            $grandeAreaId = (int)($areaRow->grandes_area_id ?? 0);
            $areasPorGrandeArea[$grandeAreaId][] = ['id' => (int)$areaRow->id, 'nome' => (string)$areaRow->nome];
        }
        $areasInscricaoPdj = $this->fetchTable('GrandesAreas')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where(['GrandesAreas.id >' => 9])
            ->orderBy(['GrandesAreas.nome' => 'ASC'])
            ->toArray();

        $areasFiocruz = $this->fetchTable('AreasFiocruz')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])->orderBy(['AreasFiocruz.nome' => 'ASC'])->toArray();

        $linhasRows = $this->fetchTable('Linhas')->find()
            ->select(['id', 'nome', 'areas_fiocruz_id'])
            ->orderBy(['Linhas.nome' => 'ASC'])
            ->all();
        $linhas = [];
        $linhasPorAreaFiocruz = [];
        foreach ($linhasRows as $linhaRow) {
            $linhas[(int)$linhaRow->id] = (string)$linhaRow->nome;
            $areaFiocruzId = (int)($linhaRow->areas_fiocruz_id ?? 0);
            $linhasPorAreaFiocruz[$areaFiocruzId][] = ['id' => (int)$linhaRow->id, 'nome' => (string)$linhaRow->nome];
        }
        $grandeAreaSelecionada = !empty($projetoSelecionado->area?->grandes_area_id) ? (int)$projetoSelecionado->area->grandes_area_id : null;
        $areaFiocruzSelecionada = !empty($projetoSelecionado->linha?->areas_fiocruz_id) ? (int)$projetoSelecionado->linha->areas_fiocruz_id : null;

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();
            if (!empty($inscricao->projeto_id)) {
                $acaoRapidaAnexo = $this->processarAcaoRapidaAnexoInscricao(
                    $dados,
                    (int)$inscricao->projeto_id,
                    (int)$inscricao->id,
                    $tiposAnexosProjetoPermitidos
                );
                if ($acaoRapidaAnexo !== null) {
                    return $this->redirect(['action' => 'projeto', (int)$edital->id, (int)$inscricao->id]);
                }
            } else {
                $solicitouAcaoRapida = !empty($dados['anexo_acao']) || !empty($dados['alterar_anexo_tipo']) || !empty($dados['anexo_tipo']);
                if ($solicitouAcaoRapida) {
                    $this->Flash->error('Cadastre primeiro o projeto para depois alterar ou excluir anexos.');
                    return $this->redirect(['action' => 'projeto', (int)$edital->id, (int)$inscricao->id]);
                }
            }

            $titulo = trim((string)($dados['titulo'] ?? ''));
            $resumo = trim((string)($dados['resumo'] ?? ''));
            $areaPdjInformada = isset($dados['area_pdj']) && $dados['area_pdj'] !== '' ? (int)$dados['area_pdj'] : null;
            if ($areaPdjInformada !== null && !isset($areasInscricaoPdj[$areaPdjInformada])) {
                $this->Flash->error('Área de inscrição PDJ inválida.');
                return $this->redirect(['action' => 'projeto', (int)$edital->id, (int)$inscricao->id]);
            }
            $justificativaBolsa = trim((string)($dados['justificativa_bolsa'] ?? ''));
            $erroJustificativaBolsa = parent::padraoValidarTextoComLimites(
                $justificativaBolsa,
                'Justificativa da bolsa',
                0,
                4000,
                false,
                true
            );
            if ($erroJustificativaBolsa !== null) {
                $this->Flash->error($erroJustificativaBolsa);
                return $this->redirect(['action' => 'projeto', (int)$edital->id, (int)$inscricao->id]);
            }
            if ($titulo === '') {
                $this->Flash->error('Informe o título do projeto.');
                return $this->redirect(['action' => 'projeto', (int)$edital->id, (int)$inscricao->id]);
            }

            $projetos = $this->fetchTable('Projetos');
            $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');
            try {
                $projetos->getConnection()->transactional(function () use ($projetos, $projetoBolsistas, &$projetoSelecionado, $inscricao, $context, $dados, $titulo, $resumo, $justificativaBolsa, $areaPdjInformada) {
                    $projetoSelecionado = $projetos->patchEntity($projetoSelecionado, [
                        'usuario_id' => (int)$context['identity']->id,
                        'titulo' => $titulo,
                        'area_id' => isset($dados['area_id']) && $dados['area_id'] !== '' ? (int)$dados['area_id'] : null,
                        'linha_id' => isset($dados['linha_id']) && $dados['linha_id'] !== '' ? (int)$dados['linha_id'] : null,
                        'financiamento' => trim((string)($dados['financiadores'] ?? '')),
                        'palavras_chaves' => trim((string)($dados['palavras_chaves'] ?? '')),
                        'resumo' => $resumo,
                    ]);
                    $projetos->saveOrFail($projetoSelecionado);

                    $inscricaoPatch = $projetoBolsistas->patchEntity($inscricao, [
                        'projeto_id' => (int)$projetoSelecionado->id,
                        'palavras_chaves' => trim((string)($dados['palavras_chaves'] ?? '')),
                        'justificativa_bolsa' => $justificativaBolsa,
                        'area_pdj' => $areaPdjInformada,
                    ]);
                    $projetoBolsistas->saveOrFail($inscricaoPatch, ['validate' => false, 'checkRules' => false]);

                    $anexosUpload = is_array($dados['anexos'] ?? null) ? $dados['anexos'] : [];
                    if (!$this->anexarInscricao($anexosUpload, (int)$projetoSelecionado->id, (int)$inscricao->id, null, true)) {
                        throw new \RuntimeException('Falha ao salvar anexos do projeto PDJ.');
                    }
                });
            } catch (\Throwable $e) {
                $this->flashFriendlyException($e, 'Erro no Sistema - salvar projeto PDJ', 'Não foi possível salvar o projeto.');
                return $this->redirect(['action' => 'projeto', (int)$edital->id, (int)$inscricao->id]);
            }

            $this->Flash->success('Projeto salvo com sucesso.');
            return $this->redirect(['action' => 'gerarTermo', (int)$edital->id, (int)$inscricao->id]);
        }

        $this->set(compact(
            'inscricao',
            'projetoSelecionado',
            'anexosTiposProjeto',
            'anexos',
            'grandesAreas',
            'areas',
            'areasInscricaoPdj',
            'areasFiocruz',
            'areasPorGrandeArea',
            'linhas',
            'linhasPorAreaFiocruz',
            'grandeAreaSelecionada',
            'areaFiocruzSelecionada'
        ));
        $this->set($context);
    }

    public function gerarTermo($editalId = null, $inscricaoId = null)
    {
        $this->request->allowMethod(['get', 'post']);
        $loaded = $this->loadPdjEditavel($editalId, $inscricaoId, [
            'Bolsistas',
            'Orientadores' => ['Vinculos', 'Escolaridades'],
            'Projetos',
        ]);
        if (isset($loaded['redirect'])) {
            return $loaded['redirect'];
        }
        [$context, $inscricao] = [$loaded['context'], $loaded['inscricao']];
        $edital = $context['edital'];

        if ($this->request->is('post')) {
            $falhas = [];
            $errosProjeto = [];
            if (empty($inscricao->projeto_id)) {
                $errosProjeto[] = 'Informe o projeto.';
            } else {
                $projeto = $inscricao->projeto ?? null;
                if (!$projeto) {
                    $errosProjeto[] = 'Projeto vinculado não localizado.';
                } else {
                    if (trim((string)($projeto->titulo ?? '')) === '') {
                        $errosProjeto[] = 'Informe o título do projeto.';
                    }
                    if (empty($inscricao->area_pdj)) {
                        $errosProjeto[] = 'Informe a área de inscrição PDJ.';
                    }
                    if (empty($projeto->area_id)) {
                        $errosProjeto[] = 'Informe a área CNPQ.';
                    }
                    if (empty($projeto->linha_id)) {
                        $errosProjeto[] = 'Informe a linha de pesquisa Fiocruz.';
                    }
                    if (trim((string)($projeto->resumo ?? '')) === '') {
                        $errosProjeto[] = 'Informe o resumo do projeto.';
                    }
                    if (trim((string)($inscricao->justificativa_bolsa ?? '')) === '') {
                        $errosProjeto[] = 'Informe a justificativa da bolsa.';
                    }
                }

                $anexoProjetoObrigatorio = $this->fetchTable('Anexos')->find()
                    ->where([
                        'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                        'Anexos.pdj_inscricoe_id IS' => null,
                        'Anexos.anexos_tipo_id' => 5,
                        'Anexos.deleted IS' => null,
                    ])
                    ->count() > 0;
                if (!$anexoProjetoObrigatorio) {
                    $tipoAnexoProjeto = $this->fetchTable('AnexosTipos')->find()
                        ->select(['nome'])
                        ->where(['AnexosTipos.id' => 5])
                        ->first();
                    $errosProjeto[] = 'Anexo pendente: ' . ($tipoAnexoProjeto ? (string)$tipoAnexoProjeto->nome : 'anexo #5') . '.';
                }
            }
            if (!empty($errosProjeto)) {
                $falhas[] = [
                    'nome' => 'Projeto',
                    'url' => ['action' => 'projeto', (int)$edital->id, (int)$inscricao->id],
                    'erros' => $errosProjeto,
                ];
            }

            $errosBolsista = [];
            if (empty($inscricao->bolsista)) {
                $errosBolsista[] = 'Informe o bolsista.';
            } else {
                $erroEscolaridade = $this->validarEscolaridadeBolsistaPdj((int)$inscricao->bolsista, $edital);
                if ($erroEscolaridade !== null) {
                    $errosBolsista[] = $erroEscolaridade;
                }

                $cpfBolsista = (string)($inscricao->bolsista_usuario->cpf ?? '');
                if ($cpfBolsista === '') {
                    $usuarioBolsista = $this->fetchTable('Usuarios')->find()
                        ->select(['cpf'])
                        ->where(['Usuarios.id' => (int)$inscricao->bolsista])
                        ->first();
                    $cpfBolsista = (string)($usuarioBolsista->cpf ?? '');
                }
                if ($this->cpfInvalidoNoEdital($cpfBolsista, $edital)) {
                    $errosBolsista[] = 'O CPF informado não pode ser utilizado neste edital.';
                }

                $tiposBolsista = $this->fetchTable('AnexosTipos')->find()
                    ->where([
                        'AnexosTipos.bloco' => 'B',
                        'AnexosTipos.deleted' => 0,
                    ])
                    ->orderBy(['AnexosTipos.id' => 'ASC'])
                    ->all();
                $programaId = (string)((int)($edital->programa_id ?? 0));
                $cotaAtual = strtoupper(trim((string)($inscricao->cota ?? '')));
                if ($cotaAtual === '') {
                    $errosBolsista[] = 'Informe a cota do bolsista.';
                }
                $tiposObrigatoriosBolsista = [];
                $nomesTiposBolsista = [];
                foreach ($tiposBolsista as $tipo) {
                    $tipoId = (int)$tipo->id;
                    $nomesTiposBolsista[$tipoId] = (string)$tipo->nome;
                    if ($tipoId === 11) {
                        continue;
                    }
                    $condicional = (int)($tipo->condicional ?? 0);
                    $programaRegra = trim((string)($tipo->programa ?? ''));
                    $cotaRegra = strtoupper(trim((string)($tipo->cota ?? '')));

                    if ($condicional !== 0) {
                        if ($cotaAtual !== '' && $cotaRegra !== '') {
                            $cotasRegra = array_filter(array_map('trim', explode(',', $cotaRegra)));
                            $programas = array_filter(array_map('trim', explode(',', $programaRegra)));
                            $programaConfere = $programaRegra === '' || in_array($programaId, $programas, true);
                            if ($programaConfere && in_array($cotaAtual, $cotasRegra, true)) {
                                $tiposObrigatoriosBolsista[] = $tipoId;
                            }
                        }
                        continue;
                    }
                    if ($programaRegra === '' && $cotaRegra === '') {
                        $tiposObrigatoriosBolsista[] = $tipoId;
                        continue;
                    }
                    if ($cotaRegra === '' && $programaRegra !== '') {
                        $programas = array_filter(array_map('trim', explode(',', $programaRegra)));
                        if (in_array($programaId, $programas, true)) {
                            $tiposObrigatoriosBolsista[] = $tipoId;
                        }
                        continue;
                    }
                    if ($programaRegra === '' && $cotaRegra !== '') {
                        $cotasRegra = array_filter(array_map('trim', explode(',', $cotaRegra)));
                        if ($cotaAtual !== '' && in_array($cotaAtual, $cotasRegra, true)) {
                            $tiposObrigatoriosBolsista[] = $tipoId;
                        }
                    }
                }

                $tiposObrigatoriosBolsista = array_values(array_unique($tiposObrigatoriosBolsista));
                $anexosBolsistaEnviados = [];
                if ($tiposObrigatoriosBolsista) {
                    $anexosBolsista = $this->fetchTable('Anexos')->find()
                        ->select(['anexos_tipo_id'])
                        ->where([
                            'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                            'Anexos.pdj_inscricoe_id IS' => null,
                            'Anexos.anexos_tipo_id IN' => $tiposObrigatoriosBolsista,
                            'Anexos.deleted IS' => null,
                        ])
                        ->all();
                    foreach ($anexosBolsista as $anexoBolsista) {
                        $anexosBolsistaEnviados[(int)$anexoBolsista->anexos_tipo_id] = true;
                    }
                    foreach ($tiposObrigatoriosBolsista as $tipoId) {
                        if (empty($anexosBolsistaEnviados[(int)$tipoId])) {
                            $errosBolsista[] = 'Anexo pendente: ' . ($nomesTiposBolsista[(int)$tipoId] ?? ('anexo #' . (int)$tipoId)) . '.';
                        }
                    }
                }
                $sexoBolsista = strtoupper(trim((string)($inscricao->bolsista_usuario->sexo ?? '')));
                if ($sexoBolsista === 'F') {
                    $filhosMenorBolsista = (string)($inscricao->filhos_menor_bolsista ?? '');
                    if ($filhosMenorBolsista === '') {
                        $errosBolsista[] = 'Informe se a bolsista possui filhos menores de 8 anos.';
                    } elseif (!in_array($filhosMenorBolsista, ['0', '1', '2'], true)) {
                        $errosBolsista[] = 'Informe corretamente se a bolsista possui filhos menores de 8 anos.';
                    } elseif ((int)$filhosMenorBolsista > 0) {
                        if (empty($anexosBolsistaEnviados[11])) {
                            $temAnexo11 = $this->fetchTable('Anexos')->find()
                                ->where([
                                    'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                                    'Anexos.pdj_inscricoe_id IS' => null,
                                    'Anexos.anexos_tipo_id' => 11,
                                    'Anexos.deleted IS' => null,
                                ])
                                ->count() > 0;
                            if (!$temAnexo11) {
                                $nomeTipo11 = $nomesTiposBolsista[11] ?? 'anexo #11';
                                $errosBolsista[] = 'Anexo pendente: ' . $nomeTipo11 . '.';
                            }
                        }
                    }
                }
            }
            if (!empty($errosBolsista)) {
                $falhas[] = [
                    'nome' => 'Bolsista',
                    'url' => ['action' => 'dadosBolsista', (int)$edital->id, (int)$inscricao->id],
                    'erros' => $errosBolsista,
                ];
            }

            $errosOrientador = [];
            $orientador = $inscricao->orientadore ?? null;
            if (!$orientador) {
                $errosOrientador[] = 'Informe o orientador.';
            } else {
                if ((int)($orientador->vinculo_id ?? 0) !== 1) {
                    $errosOrientador[] = 'O orientador precisa ter vínculo igual a 1.';
                }
                if ((int)($orientador->escolaridade_id ?? 0) !== 10) {
                    $errosOrientador[] = 'O orientador precisa ter escolaridade igual a 10.';
                }
                $anoConclusaoOrientador = trim((string)($orientador->ano_conclusao ?? ''));
                if ($anoConclusaoOrientador === '') {
                    $errosOrientador[] = 'Dados do orientador pendentes: ANO DE CONCLUSÃO.';
                } elseif (!ctype_digit($anoConclusaoOrientador) || (int)$anoConclusaoOrientador >= (int)date('Y')) {
                    $errosOrientador[] = 'O ano de conclusão do orientador deve ser menor que o ano atual.';
                }
                if (trim((string)($orientador->matricula_siape ?? '')) === '') {
                    $errosOrientador[] = 'Dados do orientador pendentes: SIAPE.';
                }
                if (trim((string)($orientador->laboratorio ?? '')) === '') {
                    $errosOrientador[] = 'Dados do orientador pendentes: LABORATÓRIO.';
                }
                if (trim((string)($orientador->departamento ?? '')) === '') {
                    $errosOrientador[] = 'Dados do orientador pendentes: DEPARTAMENTO.';
                }
            }
            if (!empty($errosOrientador)) {
                $falhas[] = [
                    'nome' => 'Orientador',
                    'url' => !empty($inscricao->orientador)
                        ? ['controller' => 'Users', 'action' => 'editar', (int)$inscricao->orientador]
                        : ['action' => 'gerarTermo', (int)$edital->id, (int)$inscricao->id],
                    'erros' => $errosOrientador,
                ];
            }

            $validarSumulasPdj = function (string $tipo) use ($edital, $inscricao): array {
                $erros = [];
                $sumulas = $this->buscarSumulasPdj((int)$edital->id, $tipo);
                if (empty($sumulas)) {
                    return $erros;
                }

                $sumulasIds = [];
                $nomesSumulas = [];
                foreach ($sumulas as $sumula) {
                    $sumulaId = (int)$sumula->id;
                    $sumulasIds[] = $sumulaId;
                    $nomesSumulas[$sumulaId] = trim((string)($sumula->sumula ?? '')) ?: 'súmula #' . $sumulaId;
                }

                $condicaoBolsistaSumula = $tipo === 'B'
                    ? ['InscricaoSumulas.bolsista' => 1]
                    : ['InscricaoSumulas.bolsista IS' => null];
                $sumulasSalvas = $this->fetchTable('InscricaoSumulas')->find()
                    ->where($condicaoBolsistaSumula + [
                        'InscricaoSumulas.projeto_bolsista_id' => (int)$inscricao->id,
                        'InscricaoSumulas.pdj_inscricoe_id IS' => null,
                        'InscricaoSumulas.editais_sumula_id IN' => $sumulasIds,
                    ])
                    ->all();

                $mapSumulasSalvas = [];
                foreach ($sumulasSalvas as $item) {
                    $mapSumulasSalvas[(int)$item->editais_sumula_id] = $item;
                }

                foreach ($sumulasIds as $sumulaId) {
                    $nomeSumula = $nomesSumulas[(int)$sumulaId] ?? ('súmula #' . (int)$sumulaId);
                    if (!isset($mapSumulasSalvas[(int)$sumulaId])) {
                        $erros[] = 'Preencha a súmula "' . $nomeSumula . '".';
                        continue;
                    }
                    $qtd = $mapSumulasSalvas[(int)$sumulaId]->quantidade;
                    if ($qtd === null || $qtd === '') {
                        $erros[] = 'Informe quantidade na súmula "' . $nomeSumula . '" (use 0 quando não possuir).';
                    }
                }

                return $erros;
            };

            $temAnexoInscricao = function (int $tipoAnexoId) use ($inscricao): bool {
                return $this->fetchTable('Anexos')->find()
                    ->where([
                        'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                        'Anexos.pdj_inscricoe_id IS' => null,
                        'Anexos.anexos_tipo_id' => $tipoAnexoId,
                        'Anexos.deleted IS' => null,
                    ])
                    ->count() > 0;
            };
            $nomeAnexoTipo = function (int $tipoAnexoId): string {
                $tipoAnexo = $this->fetchTable('AnexosTipos')->find()
                    ->select(['nome'])
                    ->where(['AnexosTipos.id' => $tipoAnexoId])
                    ->first();

                return $tipoAnexo ? (string)$tipoAnexo->nome : 'anexo #' . $tipoAnexoId;
            };

            $errosSumulaBolsista = $validarSumulasPdj('B');
            if (!empty($errosSumulaBolsista)) {
                $falhas[] = [
                    'nome' => 'Súmula do bolsista',
                    'url' => ['action' => 'sumulaBolsista', (int)$edital->id, (int)$inscricao->id],
                    'erros' => $errosSumulaBolsista,
                ];
            }

            $errosSumulaOrientador = $validarSumulasPdj('O');
            if (!$temAnexoInscricao(9)) {
                $errosSumulaOrientador[] = 'Anexo pendente: ' . $nomeAnexoTipo(9) . '.';
            }
            $sexoOrientador = strtoupper(trim((string)($orientador->sexo ?? '')));
            if ($sexoOrientador === 'F') {
                $filhosMenor = (string)($inscricao->filhos_menor ?? '');
                if ($filhosMenor === '') {
                    $errosSumulaOrientador[] = 'Informe se a orientadora possui filhos menores de 8 anos.';
                } elseif (!in_array($filhosMenor, ['0', '1', '2'], true)) {
                    $errosSumulaOrientador[] = 'Informe corretamente se a orientadora possui filhos menores de 8 anos.';
                } elseif ((int)$filhosMenor > 0 && !$temAnexoInscricao(27)) {
                    $errosSumulaOrientador[] = 'Anexo pendente: ' . $nomeAnexoTipo(27) . '.';
                }
            }
            $anoDoutorado = trim((string)($inscricao->ano_doutorado ?? ''));
            if ($anoDoutorado === '') {
                $errosSumulaOrientador[] = 'Informe o ano de conclusão do doutorado.';
            } elseif (!ctype_digit($anoDoutorado) || (int)$anoDoutorado < 1900 || (int)$anoDoutorado > (int)date('Y')) {
                $errosSumulaOrientador[] = 'Informe um ano válido de conclusão do doutorado.';
            }
            $recemServidor = $inscricao->recem_servidor;
            if ($recemServidor === null || $recemServidor === '') {
                $errosSumulaOrientador[] = 'Informe se ingressou na Fiocruz por meio dos concursos de 2016 e 2024.';
            } elseif (!in_array((string)$recemServidor, ['0', '1'], true)) {
                $errosSumulaOrientador[] = 'Informe corretamente a opção de ingresso na Fiocruz.';
            } elseif ((int)$recemServidor === 1 && !$temAnexoInscricao(29)) {
                $errosSumulaOrientador[] = 'Anexo pendente: ' . $nomeAnexoTipo(29) . '.';
            }
            if (!empty($errosSumulaOrientador)) {
                $falhas[] = [
                    'nome' => 'Súmula do orientador',
                    'url' => ['action' => 'sumulaOrientador', (int)$edital->id, (int)$inscricao->id],
                    'erros' => $errosSumulaOrientador,
                ];
            }

            if ($falhas) {
                $this->set(compact('inscricao', 'falhas'));
                $this->set($context);
                return $this->render('erros_geracao_termo');
            }

            $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');
            try {
                $faseOriginal = (int)$inscricao->fase_id;
                $inscricaoPatch = $projetoBolsistas->patchEntity($inscricao, ['fase_id' => 5]);
                $projetoBolsistas->saveOrFail($inscricaoPatch, ['validate' => false, 'checkRules' => false]);
                $this->historico((int)$inscricao->id, $faseOriginal, 5, 'Geracao de termo PDJ', true);
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - gerar termo PDJ',
                    'Não foi possível liberar o termo para assinatura.'
                );
                return $this->redirect(['action' => 'gerarTermo', (int)$edital->id, (int)$inscricao->id]);
            }
            $this->Flash->success('Termo liberado para assinatura.');
            return $this->redirect(['action' => 'finalizar', (int)$edital->id, (int)$inscricao->id]);
        }

        $this->set(compact('inscricao'));
        $this->set($context);
    }

    public function finalizar($editalId = null, $inscricaoId = null)
    {
        $loaded = $this->loadPdjEditavel($editalId, $inscricaoId, ['Bolsistas', 'Orientadores', 'Projetos']);
        if (isset($loaded['redirect'])) {
            return $loaded['redirect'];
        }
        [$context, $inscricao] = [$loaded['context'], $loaded['inscricao']];
        $edital = $context['edital'];

        if ((int)$inscricao->fase_id !== 5) {
            $this->Flash->error('Inscrição indisponível para finalização nesta fase. Gere o termo antes de finalizar.');
            return $this->redirect(['action' => 'gerarTermo', (int)$edital->id, (int)$inscricao->id]);
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $arquivoTermo = $this->request->getData('termo_assinado');
            if (!is_object($arquivoTermo) || $arquivoTermo->getClientFilename() === '') {
                $this->Flash->error('Anexe o termo assinado para finalizar a inscrição.');
                return $this->redirect(['action' => 'finalizar', (int)$edital->id, (int)$inscricao->id]);
            }

            $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');
            try {
                $projetoBolsistas->getConnection()->transactional(function () use ($projetoBolsistas, $inscricao, $arquivoTermo) {
                    $ok = $this->anexarInscricao(
                        [24 => $arquivoTermo],
                        $inscricao->projeto_id ?? null,
                        (int)$inscricao->id,
                        null,
                        true
                    );
                    if (!$ok) {
                        throw new \RuntimeException('Falha ao anexar termo assinado PDJ.');
                    }
                    $faseOriginal = (int)$inscricao->fase_id;
                    $inscricaoPatch = $projetoBolsistas->patchEntity($inscricao, ['fase_id' => 4]);
                    $projetoBolsistas->saveOrFail($inscricaoPatch, ['validate' => false, 'checkRules' => false]);
                    $this->historico((int)$inscricao->id, $faseOriginal, 4, 'Finalizacao da inscricao PDJ com termo assinado', true);
                });
            } catch (\Throwable $e) {
                $this->flashFriendlyException($e, 'Erro no Sistema - finalizar inscrição PDJ', 'Não foi possível finalizar a inscrição.');
                return $this->redirect(['action' => 'finalizar', (int)$edital->id, (int)$inscricao->id]);
            }

            $this->Flash->success(
                '<strong>Parabéns!</strong> Sua inscrição foi finalizada com sucesso.<br>Boa sorte no processo seletivo!',
                ['escape' => false]
            );
            return $this->redirect(['controller' => 'Padrao', 'action' => 'visualizar', (int)$inscricao->id]);
        }

        $this->set(compact('inscricao'));
        $this->set($context);
    }

    public function baixarTermo($editalId = null, $inscricaoId = null)
    {
        $loaded = $this->loadPdjEditavel($editalId, $inscricaoId, ['Bolsistas', 'Orientadores', 'Projetos']);
        if (isset($loaded['redirect'])) {
            return $loaded['redirect'];
        }
        [$context, $inscricao] = [$loaded['context'], $loaded['inscricao']];
        $edital = $context['edital'];
        $identity = $context['identity'];

        if ((int)$inscricao->fase_id !== 5) {
            $this->Flash->error('O termo só pode ser baixado quando a inscrição estiver na fase 5.');
            return $this->redirect(['action' => 'gerarTermo', (int)$edital->id, (int)$inscricao->id]);
        }

        $geracaoTopo = 'Termo gerado em ' . date('d/m/Y H:i:s', strtotime('-3 hours')) . ' por ' . (string)($identity->nome ?? 'Usuário');
        $htmlPdf = $this->montarHtmlTermoPdj($edital, $inscricao, $geracaoTopo);
        $htmlExport = $this->montarHtmlTermoPdj($edital, $inscricao, $geracaoTopo);

        if (!class_exists(\Mpdf\Mpdf::class)) {
            $this->Flash->error('Biblioteca de PDF não instalada. Baixando termo em HTML.');
            return $this->response
                ->withType('text/html; charset=UTF-8')
                ->withStringBody($htmlExport)
                ->withDownload('termo_pdj_' . (int)$inscricao->id . '.html');
        }

        try {
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'tempDir' => TMP,
                'margin_left' => 12,
                'margin_right' => 12,
                'margin_top' => 20,
                'margin_bottom' => 18,
                'setAutoTopMargin' => 'stretch',
                'setAutoBottomMargin' => 'stretch',
            ]);
            $mpdf->WriteHTML($htmlPdf);
            $pdfBin = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);

            return $this->response
                ->withType('application/pdf')
                ->withStringBody($pdfBin)
                ->withDownload('termo_pdj_' . (int)$inscricao->id . '.pdf');
        } catch (\Throwable $e) {
            $this->log(
                'Falha ao gerar PDF mPDF do termo PDJ #' . (int)$inscricao->id . ' | erro=' . $e->getMessage(),
                'error'
            );
            $this->Flash->error('Não foi possível gerar o PDF no momento. Baixando termo em HTML.');
            return $this->response
                ->withType('text/html; charset=UTF-8')
                ->withStringBody($htmlExport)
                ->withDownload('termo_pdj_' . (int)$inscricao->id . '.html');
        }
    }

    private function loadPdjEditavel($editalId, $inscricaoId, array $contain = []): array
    {
        $context = $this->loadContext($editalId, $inscricaoId);
        if (isset($context['redirect'])) {
            return ['redirect' => $context['redirect']];
        }
        if (empty($inscricaoId)) {
            $this->Flash->error('Inscrição PDJ não informada.');
            return ['redirect' => $this->redirect(['controller' => 'Index', 'action' => 'index'])];
        }

        $identity = $context['identity'];
        $ehTI = $this->ehTi();
        $query = $this->fetchTable('ProjetoBolsistas')->find();
        if ($contain) {
            $query->contain($contain);
        }
        $inscricao = $query->where([
            'ProjetoBolsistas.id' => (int)$inscricaoId,
            'ProjetoBolsistas.editai_id' => (int)$context['edital']->id,
            'ProjetoBolsistas.deleted IS' => null,
        ])->first();

        if (!$inscricao) {
            $this->Flash->error('Inscrição PDJ não localizada.');
            return ['redirect' => $this->redirect(['controller' => 'Index', 'action' => 'index'])];
        }
        if ((int)$inscricao->orientador !== (int)$identity->id && !$ehTI) {
            $this->Flash->error('Acesso negado. Somente o orientador pode alterar a inscrição PDJ.');
            return ['redirect' => $this->redirect(['controller' => 'Index', 'action' => 'index'])];
        }
        if (!in_array((int)$inscricao->fase_id, [1, 3, 5], true)) {
            $this->Flash->error('Inscrição PDJ indisponível para edição nesta fase.');
            return ['redirect' => $this->redirect(['controller' => 'Index', 'action' => 'index'])];
        }

        return ['context' => $context, 'inscricao' => $inscricao];
    }

    private function vincularUsuarioPdj($edital, $inscricao, string $papel, string $cpfInformado)
    {
        if (!$this->validaCPF($cpfInformado)) {
            $this->Flash->error('Informe um CPF válido para o bolsista.');
            return $this->redirect(['action' => 'dadosBolsista', (int)$edital->id, (int)$inscricao->id]);
        }

        $cpf = preg_replace('/\D/', '', $cpfInformado);
        if ($this->cpfInvalidoNoEdital($cpf, $edital)) {
            $this->Flash->error('O CPF informado não pode ser utilizado neste edital.');
            return $this->redirect(['action' => 'dadosBolsista', (int)$edital->id, (int)$inscricao->id]);
        }

        $bolsista = $this->fetchTable('Usuarios')->find()
            ->select(['id'])
            ->where(['Usuarios.cpf' => $cpf])
            ->first();
        if (!$bolsista) {
            return $this->redirect([
                'controller' => 'Users',
                'action' => 'cadastrarUsuario',
                $cpf,
                $papel,
                (int)$inscricao->id,
                (int)$edital->id,
                'P',
            ]);
        }

        try {
            $this->checkBolsista((int)$bolsista->id, $edital);
        } catch (\Exception $e) {
            $this->Flash->error($e->getMessage());
            return $this->redirect(['action' => 'dadosBolsista', (int)$edital->id, (int)$inscricao->id]);
        } catch (\Throwable $e) {
            $this->flashFriendlyException(
                $e,
                'Erro no Sistema - validar bolsista na tela de dados do bolsista PDJ',
                'Não foi possível validar o usuário informado.'
            );
            return $this->redirect(['action' => 'dadosBolsista', (int)$edital->id, (int)$inscricao->id]);
        }

        $campo = $papel === 'C' ? 'coorientador' : 'bolsista';
        $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $vigentesAtualizadas = 0;
        try {
            $faseOriginal = (int)$inscricao->fase_id;
            $projetoBolsistas->getConnection()->transactional(function () use (
                $projetoBolsistas,
                $inscricao,
                $campo,
                $bolsista,
                $faseOriginal,
                &$vigentesAtualizadas
            ) {
                $patchData = [$campo => (int)$bolsista->id];
                if ($campo === 'bolsista') {
                    $patchData['filhos_menor'] = null;
                    $patchData['filhos_menor_bolsista'] = null;
                    $vigentesAtualizadas = $this->atualizarVigentesDoBolsistaPdj(
                        (int)$bolsista->id,
                        (int)$inscricao->id
                    );
                }
                $inscricaoPatch = $projetoBolsistas->patchEntity($inscricao, $patchData);
                $projetoBolsistas->saveOrFail($inscricaoPatch);
                $this->limparAnexosPorBloco((int)$inscricao->id, 'B');
                $this->historico((int)$inscricao->id, $faseOriginal, $faseOriginal, 'Vinculação de usuário cadastrada no Dados Bolsista PDJ', true);
            });
            $mensagemSucesso = $vigentesAtualizadas > 0
                ? 'Usuário vinculado à inscrição com sucesso. A bolsa vigente anterior foi atualizada.'
                : 'Usuário vinculado à inscrição com sucesso.';
            $this->Flash->success($mensagemSucesso);
        } catch (\Throwable $e) {
            $this->flashFriendlyException(
                $e,
                'Erro no Sistema - vincular bolsista na tela de dados do bolsista PDJ',
                'Não foi possível vincular o usuário na inscrição.'
            );
        }
        return $this->redirect(['action' => 'dadosBolsista', (int)$edital->id, (int)$inscricao->id]);
    }

    private function checkBolsista(int $usuarioId, $edital = null): void
    {
        $usuario = $this->fetchTable('Usuarios')->find()
            ->select(['id', 'cpf'])
            ->where(['Usuarios.id' => $usuarioId])
            ->first();

        if ($usuario && $this->cpfInvalidoNoEdital((string)$usuario->cpf, $edital)) {
            throw new \Exception('O CPF informado não pode ser utilizado neste edital.');
        }

        $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $temAndamento = $projetoBolsistas->find()
            ->where([
                'ProjetoBolsistas.bolsista' => $usuarioId,
                'ProjetoBolsistas.deleted IS' => null,
                'ProjetoBolsistas.fase_id <' => 10,
            ])
            ->count();

        if ($temAndamento > 0) {
            throw new \Exception('O bolsista informado possui inscrição em andamento.');
        }

        $erroEscolaridade = $this->validarEscolaridadeBolsistaPdj($usuarioId, $edital);
        if ($erroEscolaridade !== null) {
            throw new \Exception($erroEscolaridade);
        }
    }

    private function atualizarVigentesDoBolsistaPdj(int $usuarioId, int $novaInscricaoId): int
    {
        $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $vigentes = $projetoBolsistas->find()
            ->where([
                'ProjetoBolsistas.bolsista' => $usuarioId,
                'ProjetoBolsistas.id !=' => $novaInscricaoId,
                'ProjetoBolsistas.deleted IS' => null,
                'ProjetoBolsistas.vigente' => 1,
                'ProjetoBolsistas.fase_id' => 11,
            ])
            ->all();

        $atualizadas = 0;
        foreach ($vigentes as $vigente) {
            $faseOriginal = (int)$vigente->fase_id;
            $vigente = $projetoBolsistas->patchEntity($vigente, [
                'fase_id' => 18,
            ]);
            $projetoBolsistas->saveOrFail($vigente, ['validate' => false, 'checkRules' => false]);
            $this->historico(
                (int)$vigente->id,
                $faseOriginal,
                18,
                'Atualização automática de bolsa vigente para Dinalizando bolsa devido à vinculação do bolsista em nova inscrição #' . $novaInscricaoId . '.',
                true
            );
            $atualizadas++;
        }

        return $atualizadas;
    }

    private function cpfInvalidoNoEdital(string $cpf, $edital = null): bool
    {
        if (empty($edital->cpf_invalidos)) {
            return false;
        }

        $cpf = preg_replace('/\D+/', '', $cpf);
        if ($cpf === '') {
            return false;
        }

        $lista = preg_split('/[\s,;]+/', (string)$edital->cpf_invalidos, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($lista as $item) {
            if ($cpf === preg_replace('/\D+/', '', (string)$item)) {
                return true;
            }
        }

        return false;
    }

    private function validarEscolaridadeBolsistaPdj(int $usuarioId, $edital): ?string
    {
        $usuario = $this->fetchTable('Usuarios')->find()
            ->select(['id', 'escolaridade_id', 'ano_conclusao'])
            ->where(['Usuarios.id' => $usuarioId])
            ->first();

        if (!$usuario) {
            return 'Bolsista não localizado.';
        }

        $escolaridadesPermitidasRaw = $edital->escolaridades_permitidas ?? null;
        if (empty($escolaridadesPermitidasRaw)) {
            return null;
        }

        $escolaridadesPermitidas = is_array($escolaridadesPermitidasRaw)
            ? $escolaridadesPermitidasRaw
            : explode(',', (string)$escolaridadesPermitidasRaw);
        $escolaridadesPermitidas = array_values(array_unique(array_filter(array_map(
            'intval',
            array_map('trim', $escolaridadesPermitidas)
        ))));
        if (empty($escolaridadesPermitidas)) {
            return null;
        }

        $escolaridadeUsuario = (int)($usuario->escolaridade_id ?? 0);
        if ($escolaridadeUsuario <= 0 || !in_array($escolaridadeUsuario, $escolaridadesPermitidas, true)) {
            $nomesEscolaridades = $this->fetchTable('Escolaridades')->find('list', [
                'keyField' => 'id',
                'valueField' => 'nome',
            ])
                ->where(['Escolaridades.id IN' => $escolaridadesPermitidas])
                ->orderBy(['Escolaridades.nome' => 'ASC'])
                ->toArray();
            $permitidas = implode(', ', array_values($nomesEscolaridades));
            return 'A escolaridade do bolsista não é permitida para este edital.'
                . ($permitidas !== '' ? ' Escolaridades permitidas: ' . $permitidas . '.' : '')
            ;
        }

        $anoConclusao = trim((string)($usuario->ano_conclusao ?? ''));
        $anoAtual = (int)date('Y');
        if ($anoConclusao === '') {
            return 'Informe o ano de conclusão do doutorado do bolsista.';
        }
        if (!ctype_digit($anoConclusao) || (int)$anoConclusao < 1900 || (int)$anoConclusao > $anoAtual) {
            return 'O ano de conclusão do doutorado do bolsista deve estar entre 1900 e o ano atual.';
        }

        return null;
    }

    private function desvincularBolsistaEscolaridadeInvalida($edital, $inscricao, string $mensagem): void
    {
        $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        try {
            $faseOriginal = (int)$inscricao->fase_id;
            $inscricaoPatch = $projetoBolsistas->patchEntity($inscricao, [
                'bolsista' => null,
                'filhos_menor' => null,
                'filhos_menor_bolsista' => null,
            ]);
            $projetoBolsistas->saveOrFail($inscricaoPatch, ['validate' => false, 'checkRules' => false]);
            $this->limparAnexosPorBloco((int)$inscricao->id, 'B');
            $this->historico(
                (int)$inscricao->id,
                $faseOriginal,
                $faseOriginal,
                'Remocao de bolsista no fluxo PDJ por escolaridade incompatível com o edital',
                true
            );
            $this->Flash->error($mensagem);
        } catch (\Throwable $e) {
            $this->flashFriendlyException(
                $e,
                'Erro no Sistema - remover bolsista PDJ com escolaridade inválida',
                'O bolsista vinculado não atende à escolaridade do edital, mas não foi possível remover o vínculo automaticamente.'
            );
        }
    }

    private function removerVinculoPdj($edital, $inscricao, string $campo, string $action)
    {
        $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        try {
            $faseOriginal = (int)$inscricao->fase_id;
            $patchData = [$campo => null];
            if ($campo === 'bolsista') {
                $patchData['filhos_menor'] = null;
                $patchData['filhos_menor_bolsista'] = null;
            }
            $inscricaoPatch = $projetoBolsistas->patchEntity($inscricao, $patchData);
            $projetoBolsistas->saveOrFail($inscricaoPatch, ['validate' => false, 'checkRules' => false]);
            if ($campo === 'bolsista') {
                $this->limparAnexosPorBloco((int)$inscricao->id, 'B');
            }
            $this->historico((int)$inscricao->id, $faseOriginal, $faseOriginal, 'Remocao de vinculo no fluxo PDJ', true);
            $this->Flash->success('Vínculo removido da inscrição PDJ.');
        } catch (\Throwable $e) {
            $this->flashFriendlyException(
                $e,
                'Erro no Sistema - remover vinculo PDJ',
                'Não foi possível remover o vínculo da inscrição PDJ.'
            );
        }
        return $this->redirect(['action' => $action, (int)$edital->id, (int)$inscricao->id]);
    }

    private function buscarAnexosProjetoBolsista(int $inscricaoId, $tipos): array
    {
        $tiposIds = [];
        foreach ($tipos as $tipo) {
            $tiposIds[] = (int)$tipo->id;
        }
        if (!$tiposIds) {
            return [];
        }

        $anexosRows = $this->fetchTable('Anexos')->find()
            ->select(['id', 'anexos_tipo_id', 'anexo'])
            ->where([
                'Anexos.projeto_bolsista_id' => $inscricaoId,
                'Anexos.pdj_inscricoe_id IS' => null,
                'Anexos.anexos_tipo_id IN' => $tiposIds,
                'Anexos.deleted IS' => null,
            ])
            ->orderBy(['Anexos.id' => 'DESC'])
            ->all();
        $anexos = [];
        foreach ($anexosRows as $anexo) {
            $tipoId = (int)$anexo->anexos_tipo_id;
            if (!isset($anexos[$tipoId])) {
                $anexos[$tipoId] = (string)$anexo->anexo;
            }
        }

        return $anexos;
    }

    private function sumulaPdj($editalId, $inscricaoId, string $tipo, string $view, string $current, string $proximaAction)
    {
        $loaded = $this->loadPdjEditavel($editalId, $inscricaoId, $tipo === 'B' ? ['Bolsistas'] : []);
        if (isset($loaded['redirect'])) {
            return $loaded['redirect'];
        }
        [$context, $inscricao] = [$loaded['context'], $loaded['inscricao']];
        $edital = $context['edital'];

        $sumulasEdital = $this->buscarSumulasPdj((int)$edital->id, $tipo);
        $sumulasPermitidas = [];
        foreach ($sumulasEdital as $sumula) {
            $sumulasPermitidas[(int)$sumula->id] = $sumula;
        }
        $inscricaoSumulas = $this->fetchTable('InscricaoSumulas');
        $ehSumulaOrientador = $tipo === 'O';
        $ehSumulaBolsista = $tipo === 'B';

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();
            $tiposAnexosPermitidosSumula = [];
            if ($ehSumulaOrientador) {
                $tiposAnexosPermitidosSumula = [9, 27, 29];
            }
            if ($tiposAnexosPermitidosSumula) {
                $acaoRapidaAnexo = $this->processarAcaoRapidaAnexoInscricao(
                    $dados,
                    $inscricao->projeto_id !== null ? (int)$inscricao->projeto_id : null,
                    (int)$inscricao->id,
                    $tiposAnexosPermitidosSumula
                );
                if ($acaoRapidaAnexo !== null) {
                    return $this->redirect(['action' => $current, (int)$edital->id, (int)$inscricao->id]);
                }
            }

            $linhas = $this->request->getData('sumula') ?? [];
            if (!is_array($linhas)) {
                $linhas = [];
            }
            $filhosMenor = '';
            $anoDoutoradoRaw = '';
            $recemServidorRaw = '';
            if ($ehSumulaOrientador) {
                $filhosMenor = array_key_exists('filhos_menor', $dados) ? (string)$dados['filhos_menor'] : '';
                if (strtoupper(trim((string)($context['identity']->sexo ?? ''))) !== 'F') {
                    $filhosMenor = '';
                }
                if ($filhosMenor !== '' && !in_array($filhosMenor, ['0', '1', '2'], true)) {
                    $this->Flash->error('Informe corretamente a opção de filhos menores.');
                    return $this->redirect(['action' => $current, (int)$edital->id, (int)$inscricao->id]);
                }

                $anoDoutoradoRaw = trim((string)($dados['ano_doutorado'] ?? ''));
                if ($anoDoutoradoRaw !== '') {
                    if (!ctype_digit($anoDoutoradoRaw)) {
                        $this->Flash->error('Informe um ano válido de conclusão do doutorado.');
                        return $this->redirect(['action' => $current, (int)$edital->id, (int)$inscricao->id]);
                    }
                    $anoDoutoradoInt = (int)$anoDoutoradoRaw;
                    $anoAtual = (int)date('Y');
                    if ($anoDoutoradoInt < 1900 || $anoDoutoradoInt > $anoAtual) {
                        $this->Flash->error('O ano de conclusão do doutorado deve estar entre 1900 e o ano atual.');
                        return $this->redirect(['action' => $current, (int)$edital->id, (int)$inscricao->id]);
                    }
                }

                $recemServidorRaw = array_key_exists('recem_servidor', $dados) ? (string)$dados['recem_servidor'] : '';
                if ($recemServidorRaw !== '' && !in_array($recemServidorRaw, ['0', '1'], true)) {
                    $this->Flash->error('Informe corretamente a opção de ingresso na Fiocruz.');
                    return $this->redirect(['action' => $current, (int)$edital->id, (int)$inscricao->id]);
                }
            }

            $quantidades = [];
            foreach ($linhas as $linha) {
                $sumulaId = (int)($linha['editais_sumula_id'] ?? 0);
                if ($sumulaId <= 0 || !isset($sumulasPermitidas[$sumulaId])) {
                    continue;
                }
                $qtdRaw = trim((string)($linha['quantidade'] ?? ''));
                if ($qtdRaw === '') {
                    $quantidades[$sumulaId] = null;
                    continue;
                }
                if (!ctype_digit($qtdRaw)) {
                    $this->Flash->error('Quantidade inválida na súmula.');
                    return $this->redirect(['action' => $current, (int)$edital->id, (int)$inscricao->id]);
                }
                $quantidades[$sumulaId] = (int)$qtdRaw;
            }

            try {
                $inscricaoSumulas->getConnection()->transactional(function () use (
                    $inscricaoSumulas,
                    $sumulasPermitidas,
                    $quantidades,
                    $edital,
                    $inscricao,
                    $ehSumulaOrientador,
                    $ehSumulaBolsista,
                    $filhosMenor,
                    $anoDoutoradoRaw,
                    $recemServidorRaw
                ) {
                    $patchData = [];
                    if ($ehSumulaOrientador) {
                        $patchData = [
                            'filhos_menor' => $filhosMenor !== '' ? $filhosMenor : null,
                            'ano_doutorado' => $anoDoutoradoRaw !== '' ? (int)$anoDoutoradoRaw : null,
                            'recem_servidor' => $recemServidorRaw !== '' ? (int)$recemServidorRaw : null,
                        ];
                    }
                    if ($patchData) {
                        $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');
                        $inscricaoPatch = $projetoBolsistas->patchEntity($inscricao, $patchData);
                        $projetoBolsistas->saveOrFail($inscricaoPatch, ['validate' => false, 'checkRules' => false]);
                    }

                    $condicaoBolsistaSumula = $ehSumulaBolsista
                        ? ['InscricaoSumulas.bolsista' => 1]
                        : ['InscricaoSumulas.bolsista IS' => null];
                    foreach ($sumulasPermitidas as $sumulaId => $sumula) {
                        $registro = $inscricaoSumulas->find()
                            ->where($condicaoBolsistaSumula + [
                                'InscricaoSumulas.projeto_bolsista_id' => (int)$inscricao->id,
                                'InscricaoSumulas.pdj_inscricoe_id IS' => null,
                                'InscricaoSumulas.editais_sumula_id' => (int)$sumulaId,
                            ])
                            ->first() ?: $inscricaoSumulas->newEmptyEntity();

                        $registro = $inscricaoSumulas->patchEntity($registro, [
                            'editais_sumula_id' => (int)$sumulaId,
                            'editai_id' => (int)$edital->id,
                            'editais_sumula_bloco_id' => (int)$sumula->editais_sumula_bloco_id,
                            'projeto_bolsista_id' => (int)$inscricao->id,
                            'pdj_inscricoe_id' => null,
                            'quantidade' => $quantidades[(int)$sumulaId] ?? null,
                            'bolsista' => $ehSumulaBolsista ? 1 : null,
                        ]);
                        $inscricaoSumulas->saveOrFail($registro);
                    }
                });
            } catch (\Throwable $e) {
                $this->flashFriendlyException($e, 'Erro no Sistema - salvar súmula PDJ', 'Não foi possível salvar a súmula.');
                return $this->redirect(['action' => $current, (int)$edital->id, (int)$inscricao->id]);
            }

            if ($ehSumulaOrientador) {
                $anexosUpload = is_array($dados['anexos'] ?? null) ? $dados['anexos'] : [];
                $filhosInt = (int)$filhosMenor;
                $recemServidorInt = (int)$recemServidorRaw;
                if (isset($anexosUpload[9])) {
                    $this->anexarInscricao([9 => $anexosUpload[9]], $inscricao->projeto_id ?? null, (int)$inscricao->id, null, true);
                }
                if ($filhosInt > 0 && isset($anexosUpload[27])) {
                    $this->anexarInscricao([27 => $anexosUpload[27]], $inscricao->projeto_id ?? null, (int)$inscricao->id, null, true);
                }
                if ($recemServidorInt === 1 && isset($anexosUpload[29])) {
                    $this->anexarInscricao([29 => $anexosUpload[29]], $inscricao->projeto_id ?? null, (int)$inscricao->id, null, true);
                }
            }

            $this->Flash->success('Súmula salva com sucesso.');
            return $this->redirect(['action' => $proximaAction, (int)$edital->id, (int)$inscricao->id]);
        }

        $condicaoBolsistaSumula = $ehSumulaBolsista
            ? ['InscricaoSumulas.bolsista' => 1]
            : ['InscricaoSumulas.bolsista IS' => null];
        $salvas = $inscricaoSumulas->find()
            ->where($condicaoBolsistaSumula + [
                'InscricaoSumulas.projeto_bolsista_id' => (int)$inscricao->id,
                'InscricaoSumulas.pdj_inscricoe_id IS' => null,
            ])
            ->all();
        $quantidadesSalvas = [];
        foreach ($salvas as $salva) {
            $quantidadesSalvas[(int)$salva->editais_sumula_id] = $salva->quantidade;
        }

        if ($ehSumulaOrientador) {
            $anexoTipo9 = $this->fetchTable('AnexosTipos')->find()
                ->select(['id', 'nome'])
                ->where(['AnexosTipos.id' => 9])
                ->first();
            $anexoTipo9Nome = $anexoTipo9 ? (string)$anexoTipo9->nome : 'Anexo tipo 9';
            $anexoTipo27 = $this->fetchTable('AnexosTipos')->find()
                ->select(['id', 'nome'])
                ->where(['AnexosTipos.id' => 27])
                ->first();
            $anexoTipo27Nome = $anexoTipo27 ? (string)$anexoTipo27->nome : 'Anexo tipo 27';
            $anexoTipo29 = $this->fetchTable('AnexosTipos')->find()
                ->select(['id', 'nome'])
                ->where(['AnexosTipos.id' => 29])
                ->first();
            $anexoTipo29Nome = $anexoTipo29 ? (string)$anexoTipo29->nome : 'Anexo tipo 29';
            $anexoOrientadorAviso = $this->fetchTable('Anexos')->find()
                ->select(['anexo'])
                ->where([
                    'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                    'Anexos.anexos_tipo_id' => 9,
                    'Anexos.deleted IS' => null,
                ])
                ->orderBy(['Anexos.id' => 'DESC'])
                ->first();
            $anexoFilhosMenor = $this->fetchTable('Anexos')->find()
                ->select(['anexo'])
                ->where([
                    'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                    'Anexos.anexos_tipo_id' => 27,
                    'Anexos.deleted IS' => null,
                ])
                ->orderBy(['Anexos.id' => 'DESC'])
                ->first();
            $anexoRecemServidor = $this->fetchTable('Anexos')->find()
                ->select(['anexo'])
                ->where([
                    'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                    'Anexos.anexos_tipo_id' => 29,
                    'Anexos.deleted IS' => null,
                ])
                ->orderBy(['Anexos.id' => 'DESC'])
                ->first();
            $this->set(compact('anexoOrientadorAviso', 'anexoTipo9Nome', 'anexoFilhosMenor', 'anexoTipo27Nome', 'anexoRecemServidor', 'anexoTipo29Nome'));
        }

        $this->set(compact('inscricao', 'sumulasEdital', 'quantidadesSalvas', 'current', 'ehSumulaOrientador', 'ehSumulaBolsista'));
        $this->set($context);
        return $this->render($view);
    }

    private function buscarSumulasPdj(int $editalId, string $tipo)
    {
        $rows = $this->fetchTable('EditaisSumulas')->find()
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

        $filtradas = array_filter($rows, function ($sumula) use ($tipo) {
            $nomeBloco = strtoupper((string)($sumula->editais_sumulas_bloco->nome ?? ''));
            if ($tipo === 'B') {
                return strpos($nomeBloco, 'BOLSISTA') !== false;
            }
            return strpos($nomeBloco, 'ORIENTADOR') !== false;
        });

        return $filtradas ?: $rows;
    }

    private function resolverSiglaInstituicaoCurso($usuario): string
    {
        $valor = trim((string)($usuario->instituicao_curso ?? ''));
        if ($valor === '') {
            return '';
        }

        if (!empty($usuario->instituicao?->sigla)) {
            return (string)$usuario->instituicao->sigla;
        }

        if (!ctype_digit($valor)) {
            return $valor;
        }

        $instituicao = $this->fetchTable('Instituicaos')->find()
            ->select(['sigla'])
            ->where(['Instituicaos.id' => (int)$valor])
            ->first();

        return !empty($instituicao?->sigla) ? (string)$instituicao->sigla : $valor;
    }

    private function montarHtmlTermoPdj($edital, $inscricao, string $textoRodapeGeracao = ''): string
    {
        $esc = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
        $fmt = fn($v) => '<strong><em>' . $esc($v) . '</em></strong>';
        $orientador = $inscricao->orientadore;
        $bolsista = $inscricao->bolsista_usuario;
        $projeto = $inscricao->projeto;

        $numeroSistema = str_pad((string)(int)$inscricao->orientador, 5, '0', STR_PAD_LEFT);
        $inscricaoNumero = (int)$inscricao->id;
        $periodoInicio = '01/09/2026';
        $periodoFim = '31/08/2027';
        $anoAtual = date('Y');
        $nomeOrientador = $fmt($orientador->nome ?? '');
        $cpfOrientador = $fmt(preg_replace('/\D+/', '', (string)($orientador->cpf ?? '')));
        $nomeBolsista = $fmt($bolsista->nome ?? '');
        $cpfBolsista = $fmt(preg_replace('/\D+/', '', (string)($bolsista->cpf ?? '')));
        $cursoBolsista = $fmt($bolsista->curso ?? '');
        $instituicaoBolsista = $fmt($this->resolverSiglaInstituicaoCurso($bolsista));
        $tituloProjeto = $fmt($projeto->titulo ?? '');
        $numeroSistemaFmt = $fmt($numeroSistema);
        $inscricaoNumeroFmt = $fmt($inscricaoNumero);
        $periodoInicioFmt = $fmt($periodoInicio);
        $periodoFimFmt = $fmt($periodoFim);
        $anoAtualFmt = $fmt($anoAtual);
        $nomeVinculoOrientador = 'Pesquisador 40h';
        if (!empty($orientador) && !empty($orientador->vinculo_id)) {
            $vinculo = $this->fetchTable('Vinculos')->find()
                ->select(['nome'])
                ->where([
                    'Vinculos.id' => (int)$orientador->vinculo_id,
                    'Vinculos.deleted' => 0,
                ])
                ->first();
            if ($vinculo && !empty($vinculo->nome)) {
                $nomeVinculoOrientador = (string)$vinculo->nome;
            }
        }
        $nomeVinculoOrientadorFmt = $fmt($nomeVinculoOrientador);
        $nomeEscolaridadeOrientador = 'Doutorado';
        if (!empty($orientador) && !empty($orientador->escolaridade_id)) {
            $escolaridade = $this->fetchTable('Escolaridades')->find()
                ->select(['nome'])
                ->where([
                    'Escolaridades.id' => (int)$orientador->escolaridade_id,
                ])
                ->first();
            if ($escolaridade && !empty($escolaridade->nome)) {
                $nomeEscolaridadeOrientador = (string)$escolaridade->nome;
            }
        }
        $nomeEscolaridadeOrientadorFmt = $fmt($nomeEscolaridadeOrientador);
        $origemInscricao = strtoupper(trim((string)($inscricao->origem ?? '')));
        $rotuloOrigem = 'Bolsa Nova';
        if ($origemInscricao === 'R') {
            $rotuloOrigem = 'Renovação';
        } elseif ($origemInscricao === 'S') {
            $rotuloOrigem = 'Substituição';
        } elseif ($origemInscricao === 'A') {
            $rotuloOrigem = 'Subst na Implantacao';
        }
        $rotuloOrigemFmt = $fmt($rotuloOrigem);
        $nomeEditalCabecalho = $esc($edital->nome ?? 'PIBIC');
        $textoGeracaoCabecalho = trim((string)$textoRodapeGeracao) !== ''
            ? (string)$textoRodapeGeracao
            : ('Termo gerado em ' . date('d/m/Y H:i:s', strtotime('-3 hours')) . ' por Usuario');
        $textoGeracaoCabecalhoHtml = $esc($textoGeracaoCabecalho);
        if (preg_match('/^Termo gerado em (.+) por (.+)$/u', $textoGeracaoCabecalho, $partesGeracao) === 1) {
            $textoGeracaoCabecalhoHtml = 'Termo gerado em <strong>' . $esc($partesGeracao[1]) . '</strong> por <strong>' . $esc($partesGeracao[2]) . '</strong>';
        }

        $logoPath = WWW_ROOT . 'img' . DS . 'marcafiocruz_horizontal_POSITIVA_24052024.svg';
        $logoSrc = is_file($logoPath) ? str_replace(DS, '/', $logoPath) : '/img/marcafiocruz_horizontal_POSITIVA_24052024.svg';
        $logoHtml = '<img src="' . $esc($logoSrc) . '" alt="Marca Fiocruz" style="width:180px;max-width:100%;height:auto;display:block;">';

        return '<!doctype html><html><head><meta charset="utf-8"><style>
            @page { size: auto; margin: 78px 18px 60px; header: html_termoHeader; footer: html_termoFooter; }
            body{margin:0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.5;color:#111}
            .main-termo{display:block;padding-top:8px}
            .espaco-topo{height:16px}
            p{margin:0 0 10px 0;text-align:justify}
            .wrap{padding:0 52px 24px}
            .header-termo{border-bottom:1px solid #d7d7d7;padding:0 52px 8px}
            .header-termo .col-4{width:33.333%;text-align:left;vertical-align:middle}
            .header-termo .col-8{width:66.667%;text-align:right;vertical-align:middle}
            .header-termo .info{font-size:12px;line-height:1.35;color:#222;font-weight:600}
            .header-termo .geracao-linha{display:block;font-size:10px;font-style:italic;font-weight:400}
            .footer-termo{border-top:1px solid #9a9a9a;padding:6px 52px 0;font-size:10px;color:#444;text-align:center;line-height:1.35}
            .titulo{text-align:center;margin:14px 0 18px}
            .titulo h1{margin:0 0 8px;font-size:19px;line-height:1.2;font-weight:700}
            .titulo h2{margin:0;font-size:14px;line-height:1.3;font-weight:600}
            .caixa-inscricao{background:#eee;padding:6px 10px;text-align:right;margin-top:12px}
            .assinatura{margin-top:28px}
            ul{margin:6px 0 12px 20px}
            </style></head><body>
            <htmlpageheader name="termoHeader">
                <div class="header-termo"><table width="100%" cellpadding="0" cellspacing="0"><tr><td class="col-4">' . $logoHtml . '</td><td class="col-8"><div class="info">Termo de solicitação de bolsa ' . $nomeEditalCabecalho . '<br>Inscrição ' . $inscricaoNumeroFmt . '<br><span class="geracao-linha">' . $textoGeracaoCabecalhoHtml . '</span></div></td></tr></table></div>
            </htmlpageheader>
            <htmlpagefooter name="termoFooter">
                <div class="footer-termo">Fiocruz - Av. Brasil, 4365 • Campus Manguinhos •<br>Castelo Mourisco • sala 110 • Rio de Janeiro • RJ • Brasil • CEP 21040 900<br>Tel (21) 3865 1618 - 3865 1698 •<br>secretariavppcb@fiocruz.br • vppcb@fiocruz.br •<br>www.fiocruz.br</div>
            </htmlpagefooter>
            <main class="main-termo"><div class="wrap">
                <div class="espaco-topo"></div>
                <div class="titulo">
                    <h1>' . $nomeEditalCabecalho . ' - CNPq/FIOCRUZ</h1>
                    <h2>Termo de Solicitação de Bolsas para o Período ' . $periodoInicioFmt . ' a ' . $periodoFimFmt . '</h2>
                </div>
                <div class="caixa-inscricao">Inscrição número <b>' . $inscricaoNumeroFmt . '</b></div>

                <br><br>
                <p>Eu, ' . $nomeOrientador . ', portadora do CPF de número ' . $cpfOrientador . ', registrado no Sistema de Fomento à Pesquisa da VPPCB-FIOCRUZ sob o número ' . $numeroSistemaFmt . ', onde constam todos os meus dados pessoais e de contato, informados por mim.</p>
                <p>Declaro que meu vínculo com a FIOCRUZ é de ' . $nomeVinculoOrientadorFmt . ' e que detenho a graduação de ' . $nomeEscolaridadeOrientadorFmt . ', mínima exigida para a submissão de projetos conforme o Edital, que foi lido e estou de pleno acordo.</p>
                <p>O projeto (' . $tituloProjeto . ') apresentado solicita o/a bolsista descrito a seguir:</p>
                <ul>
                    <li>(' . $rotuloOrigemFmt . ' - ' . $inscricaoNumeroFmt . ' - ' . $anoAtualFmt . ') ' . $nomeBolsista . ', CPF ' . $cpfBolsista . ', estudante de ' . $cursoBolsista . ' em ' . $instituicaoBolsista . ', conforme Histórico Escolar e Comprovante de Matrícula anexo.</li>
                </ul>

                <br><br>
                <p><b>DECLARAÇÕES E TERMOS DE COMPROMISSO</b></p>
                <p>Eu, ' . $nomeBolsista . ', CPF nº ' . $cpfBolsista . ' declaro estar apto(a) a participar do Programa Institucional de Bolsas de Iniciação Científica Fiocruz/CNPq.</p>
                <p>Sendo assim me considero de acordo com o proposto pela GESTÃO DA PROPRIEDADE INTELECTUAL, considerando que a FIOCRUZ é uma instituição pública diretamente vinculada ao Ministério da Saúde, cuja missão é a geração, absorção e difusão de conhecimentos científicos e tecnológicos em saúde;</p>
                <p>Considerando que a FIOCRUZ, visando contribuir com a política nacional de saúde pública, possui como política institucional a busca da proteção legal dos resultados oriundos das suas atividades de pesquisas e desenvolvimento tecnológico;</p>
                <p>Considerando que a novidade é um dos requisitos necessários à proteção dos resultados de pesquisas pelos institutos de propriedade industrial, e, por consequência, a sua manutenção em sigilo até a adoção dos procedimentos legais pertinentes é indispensável para a obtenção da proteção almejada;</p>
                <p>Considerando, ainda, o disciplinado pelo ordenamento jurídico brasileiro, em especial pela Lei 9.279/96 (Lei de Propriedade Industrial), Lei 9.609/98 (Lei de Programa de Computador), Lei 9.610/98 (Lei de Direitos Autorais), Decreto 2.553/98 (que regulamenta sobre a premiação aos inventores de instituições públicas) e Lei 10.973/04 (Lei de regulamentada pelo Decreto nº 5.563, de 11 de outubro de 2005), pela Medida Provisória 2.186/2001 e demais atos normativos emanados do Conselho de Gestão do Patrimônio Genético do Ministério do Meio Ambiente;</p>

                <p>Pelo presente TERMO DE COMPROMISSO, o signatário abaixo qualificado:</p>
                <p>1º Obriga-se a manter em sigilo de todas as informações obtidas em função das atividades desempenhadas junto a FIOCRUZ, incluindo, mas não limitadas, às informações técnicas e científicas relativas a: projetos, resultados de pesquisas, operações, processos, produção, instalações, equipamentos, habilidades especializadas, métodos e metodologias, fluxogramas, componentes, fórmulas, produtos, amostras, diagramas, desenhos, desenho de esquema industrial, patentes, segredos de negócio. Estas informações serão consideradas INFORMAÇÕES CONFIDENCIAIS.</p>
                <p>A obrigação de sigilo assumida, por meio deste termo, não compreende informações que já sejam de conhecimento público ou se tornem publicamente disponíveis por outra maneira que não uma revelação não autorizada.</p>
                <p>O sigilo imposto veda quaisquer formas de divulgação das INFORMAÇÕES CONFIDENCIAIS, sejam através de artigos técnicos, relatórios, publicações, comunicações verbais entre outras, salvo prévia autorização por escrito da FIOCRUZ, em conformidade com o disposto no art. 12 da Lei 10.973/2004, que dispõe: “É vedado a dirigente, ao criador ou a qualquer servidor, militar, empregado ou prestador de serviços de ICT divulgar, noticiar ou publicar qualquer aspecto de criações de cujo desenvolvimento tenha participado diretamente ou tomado conhecimento por força de suas atividades, sem antes obter expressa autorização da ICT”.</p>
                <p>A vigência da obrigação de sigilo perdurará até que a informação tida como INFORMAÇÃO CONFIDENCIAL seja licitamente tornada de conhecimento público ou FIOCRUZ autorize por escrito a sua divulgação, devendo ser observado os procedimentos institucionais estabelecidos para tanto.</p>
                <p>2º Obriga-se a não usar as INFORMAÇÕES CONFIDENCIAIS de forma distinta dos propósitos das atividades a serem desempenhadas junto a FIOCRUZ.</p>
                <p>3º Obriga-se a não enviar amostras de material biológico e/ou genético, obtidas em função das atividades desempenhadas junto a FIOCRUZ, a terceiros sem a prévia autorização por escrito da FIOCRUZ, devendo ser observado os procedimentos institucionais estabelecidos para tanto.</p>
                <p>4º Reconhece que, respeitado o direito de nomeação a autoria (autor/inventor), os direitos de propriedade intelectual sobre os resultados porventura advindos da execução das atividades pelo signatário desempenhadas perante a FIOCRUZ pertencerão exclusivamente a FIOCRUZ, ficando esta desde já autorizada a requerer a proteção pelos institutos de propriedade intelectual que julgar pertinente. Para tanto, se compromete em assinar todos os documentos que forem necessários para regularizar a titularidade da FIOCRUZ perante os institutos de propriedade intelectual, no Brasil e exterior.</p>
                <p>5º Reconhece que a inobservância das disposições aqui contidas sujeitar-lhe-á à aplicação das sanções legais pertinentes, em especial às sanções administrativas, além de ensejar responsabilidade em eventuais perdas e danos ocasionados a FIOCRUZ.</p>
                <p>6º Declaro comprometimento da atualização da produção intelectual no Repositório Intelectual, ARCA.</p>
                <p>Submetido o projeto, aceitamos e concordamos com os seguintes <b>TERMOS DE COMPROMISSO</b>:</p>

                <br><br>
                <p><b>CONDIÇÕES GERAIS</b></p>
                <p>1. Ao aceitar a concessão, caso a bolsa seja aprovada, compromete-se o beneficiário a dedicar-se, com exclusividade, às atividades pertinentes à bolsa concedida.</p>
                <p>2. Confirma também ter sido informado pelo orientador sobre: (a) as normas de biossegurança da Instituição; (b) os aspectos éticos da pesquisa em desenvolvimento; (c) em caso de pesquisa com geração de produtos passíveis de registro de patente, estar a par e compromissado com os termos de sigilo.</p>
                <p>3. Compromete-se ainda o beneficiário a: a) estar regularmente matriculado em curso de graduação; b) apresentar excelente rendimento acadêmico e não ter reprovação em disciplinas afins com as atividades do projeto de pesquisa e nem ser do círculo familiar do orientador; c) dedicar-se integralmente às atividades acadêmicas e de pesquisa, em ritmo compatível com as atividades exigidas pelo curso durante o ano letivo, e de forma intensificada durante as férias letivas; d) não se afastar da instituição em que desenvolve seu projeto de pesquisa, exceto para a realização de pesquisa de campo, participação em evento científico ou estágio de pesquisa, por período limitado e com autorização do orientador; e) apresentar, após 6 (seis) meses de vigência do período da bolsa, relatório de pesquisa, contendo resultados parciais; f) apresentar os resultados parciais ou finais da pesquisa, sob a forma de exposições orais e painéis, acompanhado de um relatório de pesquisa com redação científica, que permita verificar o acesso a métodos e processos científicos; g) estar recebendo apenas esta modalidade de bolsa, sendo vedada a acumulação desta com a de outros programas do CNPq, de outra agência ou da própria instituição; h) devolver ao CNPq, em valores atualizados, a(s) mensalidade(s) recebidas indevidamente, caso os requisitos e compromissos estabelecidos acima não sejam cumpridos.</p>
                <p>4. Os trabalhos publicados em decorrência das atividades apoiadas pelo CNPq deverão, necessariamente, fazer referência ao apoio recebido, com as seguintes expressões: a) Se publicado individualmente: "O presente trabalho foi realizado com o apoio do Conselho Nacional de Desenvolvimento Científico e Tecnológico – CNPq – Brasil". b) Se publicado em co-autoria: "Bolsista do CNPq – Brasil".</p>
                <p>5. O CNPq poderá cancelar ou suspender a bolsa quando constatada infringência a quaisquer das condições constantes deste Termo das normas aplicáveis a esta concessão, sem prejuízo da aplicação dos dispositivos legais que disciplinam o ressarcimento dos recursos.</p>
                <p>6. A concessão objeto do presente instrumento não gera vínculo de qualquer natureza ou relação de trabalho, constituindo doação, com encargos, feita ao beneficiário.</p>
                <p>7. O beneficiário e o orientador manifestam sua integral e incondicional concordância com os termos da concessão, comprometendo-se a cumprir fielmente as condições expressas neste instrumento e as normas que lhe são aplicáveis: Portaria CNPq nº 2.539/2025 do Programa Institucional de Bolsas de Iniciação Científica.</p>

                <br><br>
                <p>Data: ________________________________________</p>
                <br><br>
                <p class="assinatura">______________________________________________________________________<br>' . $nomeBolsista . ' - CPF ' . $cpfBolsista . '<br>Bolsista</p>
                <br><br>
                <p class="assinatura">________________________________________________________________________<br>' . $nomeOrientador . ' - CPF ' . $cpfOrientador . '<br>Orientador</p>
            </div></main>
            </body></html>';
    }
}
