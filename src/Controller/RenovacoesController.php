<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Event\EventInterface;

class RenovacoesController extends AppController
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

        switch ($tipo) {
            case 'E':
                return $this->redirect(['controller' => 'Renovacoes', 'action' => 'dadosBolsistaRenovacao', (int)$editalId, (int)$inscricaoId]);
            case 'V':
                $programaId = (int)($this->fetchTable('Editais')->find()
                    ->select(['programa_id'])
                    ->where(['Editais.id' => (int)$editalId])
                    ->first()
                    ->programa_id ?? 0);
                return $this->redirect(['controller' => 'Padrao', 'action' => 'visualizar', (int)$inscricaoId]);
            case 'T':
                return $this->redirect(['controller' => 'Renovacoes', 'action' => 'gerarTermoRenovacao', (int)$editalId, (int)$inscricaoId]);
            case 'F':
                return $this->redirect(['controller' => 'Renovacoes', 'action' => 'finalizarRenovacao', (int)$editalId, (int)$inscricaoId]);
            case 'C':
                return $this->redirect(['controller' => 'Padrao', 'action' => 'cancelar', (int)$inscricaoId]);
            case 'S':
                return $this->redirect(['controller' => 'Padrao', 'action' => 'substituir', (int)$inscricaoId]);
            case 'D':
                return $this->redirect(['controller' => 'Padrao', 'action' => 'deletar', (int)$inscricaoId]);
            default:
                $this->Flash->error('Ação de direcionamento inválida.');
                return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'A']);
        }
    }
    public function deletar($editalId = null, $inscricaoId = null)
    {
        $this->request->allowMethod(['post']);
        $context = $this->loadContext($editalId);
        if (isset($context['redirect'])) {
            return $context['redirect'];
        }
        $edital = $context['edital'];
        $identity = $context['identity'];

        if (empty($inscricaoId)) {
            $this->Flash->error('Inscrição não informada para exclusão.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'A']);
        }

        $ehTI = $this->ehTi();
        $conditions = [
            'ProjetoBolsistas.id' => (int)$inscricaoId,
            'ProjetoBolsistas.editai_id' => (int)$edital->id,
            'ProjetoBolsistas.deleted IS' => null,
        ];
        if (!$ehTI) {
            $conditions['ProjetoBolsistas.orientador'] = (int)$identity->id;
        }

        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $inscricao = $tblProjetoBolsistas->find()
            ->where($conditions)
            ->first();
        if (!$inscricao) {
            $this->Flash->error('Inscrição não localizada para exclusão.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'A']);
        }

        if (!in_array((int)$inscricao->fase_id, [1, 3], true)) {
            $this->Flash->error('A inscrição só pode ser excluída nas fases 1 ou 3.');
            return $this->redirect(['controller' => 'Padrao', 'action' => 'visualizar', (int)$inscricao->id]);
        }

        try {
            $faseAtual = (int)$inscricao->fase_id;
            $inscricaoPatch = $tblProjetoBolsistas->patchEntity($inscricao, ['deleted' => date('Y-m-d H:i:s')]);
            $tblProjetoBolsistas->saveOrFail($inscricaoPatch);
            $this->historico((int)$inscricao->id, $faseAtual, $faseAtual, 'Exclusao da inscricao pelo orientador', true);
        } catch (\Throwable $e) {
            $this->flashFriendlyException(
                $e,
                'Erro no Sistema - exclusao de inscricao',
                'Não foi possível excluir a inscrição.'
            );
            return $this->redirect(['controller' => 'Padrao', 'action' => 'visualizar', (int)$inscricao->id]);
        }

        $this->Flash->success('Inscrição excluída com sucesso.');
        return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'A']);
    }
    // valida se tem alguma inscrição e se tem alguma renovação para decidir
    public function fluxoRenovacao($editalId = null)
    {
        //valida regras
        $context = $this->loadContext($editalId);
        if (isset($context['redirect'])) {
            return $context['redirect'];
        }

        $edital = $context['edital'];
        $identity = $context['identity'];
        $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        
        //valida se tem inscrições NOVAS!! em andamento
        // isso garante que se o orientador tiver mais de um aluno p renovar ele consegue, mas se ja fez uma nova nao
        $inscricoesEmAndamento = $projetoBolsistas->find()
            ->where([
                'ProjetoBolsistas.orientador' => (int)$identity->id,
                'ProjetoBolsistas.programa_id' => (int)$edital->programa_id,
                'ProjetoBolsistas.deleted IS' => null,
                'ProjetoBolsistas.fase_id <' => 10,
                'ProjetoBolsistas.origem' => 'N',

            ])
            ->count();
        if ($inscricoesEmAndamento > 0) {
            $this->Flash->error('O(a) sr(a) já possui uma inscrição de bolsa Nova em andamento neste programa e não poderá renovar.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $vigentes = $this->fetchTable('ProjetoBolsistas')->find()
            ->contain(['Bolsistas'])
            ->where([
                'ProjetoBolsistas.orientador' => (int)$identity->id,
                'ProjetoBolsistas.programa_id' => (int)$edital->programa_id,
                'ProjetoBolsistas.deleted IS' => null,
                'ProjetoBolsistas.fase_id' => 11,
                'ProjetoBolsistas.vigente' => 1,
            ])
            ->orderBy(['ProjetoBolsistas.id' => 'DESC']);
        $qtdVigentes = $vigentes->count();

        if ($qtdVigentes === 0) {
            $this->Flash->error('Você não tem inscrição apta para renovar. Caso já tenha solicitado alguma renovação, acesse a renovação na tela de inscrições em andamento para edição.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'A']);
        
        }
        

        if ($qtdVigentes === 1) {
            $referencia = $vigentes->first();
            return $this->criarOuCarregarRenovacao($edital, $identity, (int)$referencia->id);
        }

        $renovacoes = $vigentes;
        $this->set(compact('renovacoes'));
        $this->set($context);
        return $this->render('selecionar_renovacao');
    }

    private function criarOuCarregarRenovacao($edital, $identity, int $referenciaId)
    {
        $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        if ($referenciaId <= 0) {
            $this->Flash->error('Referência de inscrição não informada.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $referencia = $projetoBolsistas->find()
            ->where([
                'ProjetoBolsistas.id' => $referenciaId,
                'ProjetoBolsistas.orientador' => (int)$identity->id,
                'ProjetoBolsistas.programa_id' => (int)$edital->programa_id,
                'ProjetoBolsistas.deleted IS' => null,
                'ProjetoBolsistas.vigente' => 1,
                'ProjetoBolsistas.fase_id' => 11,
            ])
            ->first();
        if (!$referencia) {
            $this->Flash->error('Inscrição não localizada. Ou está deletada ou inelegível.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }
        $referenciaValidaId = (int)$referencia->id;

        $inscricao = $projetoBolsistas->find()
            ->where([
                'ProjetoBolsistas.referencia_inscricao_anterior' => $referenciaValidaId,
                'ProjetoBolsistas.deleted IS' => null,
            ])
            ->orderBy(['ProjetoBolsistas.id' => 'DESC'])
            ->first();
        if ($inscricao) {
            try {
                $inscricaoFase = (int)$inscricao->fase_id;
                if (in_array($inscricaoFase, [1, 3], true) && (int)$referencia->fase_id !== 19) {
                    $faseOriginalReferencia = (int)$referencia->fase_id;
                    $referenciaAtualizada = $projetoBolsistas->patchEntity($referencia, [
                        'fase_id' => 19,
                    ]);
                    $projetoBolsistas->saveOrFail($referenciaAtualizada);
                    $this->historico(
                        $referenciaValidaId,
                        $faseOriginalReferencia,
                        19,
                        'Inscrição de referência marcada como utilizada para renovação existente #' . (int)$inscricao->id,
                        true
                    );
                }
                return $this->redirect(['action' => 'dadosBolsistaRenovacao', $edital->id, $inscricao->id]);
            } catch (\Throwable $e) {
                $this->flashFriendlyException($e, 'Erro no Sistema - atualizar inscricao de referencia');
                return $this->redirect(['controller' => 'Index', 'action' => 'index']);
            }
        }

        $inscricao = $projetoBolsistas->newEmptyEntity();
        $inscricao = $projetoBolsistas->patchEntity($inscricao, [
            'editai_id' => (int)$edital->id,
            'orientador' => (int)$identity->id,
            'programa_id' => (int)$edital->programa_id,
            'origem' => 'R',
            'fase_id' => 3,
            'vigente' => 0,
            'deleted' => null,
            'autorizacao' => 0,
            'prorrogacao' => 0,
            'referencia_inscricao_anterior' => $referenciaValidaId,
            'bolsista' => $referencia->bolsista,
            'projeto_id' => $referencia->projeto_id,
            'cota' => $referencia->cota,
            'matriz' => $referencia->matriz,
        ]);

        try {
            $connection = $projetoBolsistas->getConnection();
            $connection->transactional(function () use ($projetoBolsistas, &$inscricao, $referencia, $referenciaValidaId) {
                $projetoBolsistas->saveOrFail($inscricao);
                $this->replicarAnexosProjetoBlocoPRenovacao((int)$referencia->projeto_id, (int)$inscricao->id);
                $this->historico(
                    (int)$inscricao->id,
                    1,
                    1,
                    'Criação de renovação a partir da inscrição de referência #' . $referenciaValidaId,
                    true
                );

                $faseOriginalReferencia = (int)$referencia->fase_id;
                $referenciaAtualizada = $projetoBolsistas->patchEntity($referencia, [
                    'fase_id' => 19,
                ]);
                $projetoBolsistas->saveOrFail($referenciaAtualizada);
                $this->historico(
                    $referenciaValidaId,
                    $faseOriginalReferencia,
                    19,
                    'Inscrição de referência marcada como utilizada para renovação #' . (int)$inscricao->id,
                    true
                );
            });
            return $this->redirect(['action' => 'dadosBolsistaRenovacao', $edital->id, $inscricao->id]);
        } catch (\Throwable $e) {
            $this->flashFriendlyException($e, 'Erro no Sistema - criação de renovação');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }
    }

    private function replicarAnexosProjetoBlocoPRenovacao(int $projetoId, int $novaInscricaoId): void
    {
        if ($projetoId <= 0 || $novaInscricaoId <= 0) {
            return;
        }

        $tblAnexos = $this->fetchTable('Anexos');
        $originais = $tblAnexos->find()
            ->contain([
                'AnexosTipos' => function ($q) {
                    return $q->where([
                        'AnexosTipos.bloco' => 'P',
                        'AnexosTipos.deleted' => 0,
                    ]);
                },
            ])
            ->where([
                'Anexos.projeto_id' => $projetoId,
                'Anexos.deleted IS' => null,
            ])
            ->orderBy(['Anexos.id' => 'DESC'])
            ->all();

        $maisRecentesPorTipo = [];
        foreach ($originais as $anexoOriginal) {
            if (empty($anexoOriginal->anexos_tipo)) {
                continue;
            }
            $tipoId = (int)$anexoOriginal->anexos_tipo_id;
            if (!isset($maisRecentesPorTipo[$tipoId])) {
                $maisRecentesPorTipo[$tipoId] = $anexoOriginal;
            }
        }

        if (empty($maisRecentesPorTipo)) {
            return;
        }

        foreach ($maisRecentesPorTipo as $anexoOriginal) {
            $copia = $tblAnexos->newEmptyEntity();
            $copia = $tblAnexos->patchEntity($copia, [
                'projeto_id' => $anexoOriginal->projeto_id,
                'projeto_bolsista_id' => $novaInscricaoId,
                'anexos_tipo_id' => $anexoOriginal->anexos_tipo_id,
                'anexo' => $anexoOriginal->anexo,
                'usuario_id' => $anexoOriginal->usuario_id,
                'raic_id' => $anexoOriginal->raic_id,
                'pdj_inscricoe_id' => $anexoOriginal->pdj_inscricoe_id,
                'bloco' => $anexoOriginal->bloco,
                'created' => $anexoOriginal->created,
                'modified' => $anexoOriginal->modified,
                'deleted' => null,
            ], [
                'accessibleFields' => [
                    'created' => true,
                    'modified' => true,
                ],
            ]);

            $tblAnexos->saveOrFail($copia, [
                'checkRules' => true,
            ]);
        }
    }

    public function dadosBolsistaRenovacao($editalId = null, $inscricaoId = null)
    {
        $identity = $this->identityLogado;

        if ($editalId === null) {
            $this->Flash->error('Edital não informado.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $edital = $this->fetchTable('Editais')->find()
            ->where(['Editais.id' => (int)$editalId])
            ->first();

        if (!$edital) {
            $this->Flash->error('Edital não localizado.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        if (!empty($inscricaoId)) {
            $inscricaoBase = $this->fetchTable('ProjetoBolsistas')->find()
                ->select(['id', 'fase_id', 'origem'])
                ->where([
                    'ProjetoBolsistas.id' => (int)$inscricaoId,
                    'ProjetoBolsistas.editai_id' => (int)$edital->id,
                    'ProjetoBolsistas.deleted IS' => null,
                ])
                ->first();

            if ($inscricaoBase && (int)$inscricaoBase->fase_id === 11 && strtoupper((string)$inscricaoBase->origem) !== 'R') {
                return $this->criarOuCarregarRenovacao($edital, $identity, (int)$inscricaoBase->id);
            }
        }

        $inscricaoContext = $this->loadInscricaoEditavel([
            'edital' => $edital,
            'identity' => $identity,
        ], $inscricaoId);
        if (isset($inscricaoContext['redirect'])) {
            return $inscricaoContext['redirect'];
        }

        $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $inscricao = null;

         $inscricao = $projetoBolsistas->find()
            ->contain([
                'Bolsistas'
            ])
            ->where([
                'ProjetoBolsistas.id' => (int)$inscricaoContext['inscricao']->id,
                'ProjetoBolsistas.editai_id' => (int)$edital->id,
                'ProjetoBolsistas.deleted IS' => null,

            ])
            ->first();
        if (!$inscricao) {
            $this->Flash->error('Inscrição não localizada ou deletada.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
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

            // Condicional=1 com programa/cota nulos
            if ($programaRegra !== '' || $cotaRegra !== '') {
                continue;
            }
            if ($tipoId === 18) {
                if ($bolsistaMenorIdade) {
                    $anexosTiposCondicional[] = $tipoAnexo;
                }
                continue;
            }
            if ($tipoId === 19) {
                if ($bolsistaMenorIdade) {
                    $anexosTiposCondicional[] = $tipoAnexo;
                }
                continue;
            }
        }

        $anexos = [];
        $anexosAtivos = $this->fetchTable('Anexos')->find()
            ->where([
                'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
            ])
            ->orderBy(['Anexos.id' => 'DESC'])
            ->all();
        foreach ($anexosAtivos as $anexoAtual) {
            $tipoId = (int)$anexoAtual->anexos_tipo_id;
            $deletedAtual = $anexoAtual->deleted ?? null;
            $deletedVazio = $deletedAtual === null
                || $deletedAtual === ''
                || $deletedAtual === 0
                || $deletedAtual === '0'
                || $deletedAtual === '0000-00-00 00:00:00'
                || $deletedAtual === '1970-01-01 00:00:00';
            if (!$deletedVazio) {
                continue;
            }
            if (!isset($anexos[$tipoId])) {
                $anexos[$tipoId] = (string)$anexoAtual->anexo;
            }
        }
        $referenciaDetalhe = null;
        $referenciaId = (int)($inscricao->referencia_inscricao_anterior ?? 0);
        if ($referenciaId > 0) {
            $referenciaDetalhe = $projetoBolsistas->find()
                ->select([
                    'id' => 'ProjetoBolsistas.id',
                    'data_inicio' => 'ProjetoBolsistas.data_inicio',
                    'nome_edital' => 'Editais.nome',
                ])
                ->leftJoinWith('Editais')
                ->where(['ProjetoBolsistas.id' => $referenciaId])
                ->enableHydration(false)
                ->first();
        }

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
            $acao = (string)($dados['acao'] ?? 'salvar_anexos');
            if (in_array($acao, ['vincular_bolsista', 'incluir_bolsista', 'excluir_bolsista'], true)) {
                $this->Flash->error('Não é permitido incluir, alterar ou excluir bolsista nesta etapa da renovação.');
                return $this->redirect(['action' => 'dadosBolsistaRenovacao', $edital->id, $inscricao->id]);
            }
            $primeiroPeriodoInformado = array_key_exists('primeiro_periodo', $dados)
                ? (string)$dados['primeiro_periodo']
                : $primeiroPeriodoAtual;
            if ($primeiroPeriodoInformado !== '' && !in_array($primeiroPeriodoInformado, ['0', '1'], true)) {
                $this->Flash->error('Informe corretamente se o bolsista está no primeiro período.');
                return $this->redirect(['action' => 'dadosBolsistaRenovacao', $edital->id, $inscricao->id]);
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
            foreach ($anexosTiposCondicional as $tipoAnexo) {
                $tiposAnexosPermitidos[] = (int)$tipoAnexo->id;
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
                return $this->redirect(['action' => 'dadosBolsistaRenovacao', $edital->id, $inscricao->id]);
            }

            $anexosUpload = $dados['anexos'] ?? [];
            if (!is_array($anexosUpload)) {
                $anexosUpload = [];
            }

            if ($cotaAtual !== '' && !array_key_exists($cotaAtual, $cotas)) {
                $this->Flash->error('Cota inválida para este edital.');
                return $this->redirect(['action' => 'dadosBolsistaRenovacao', $edital->id, $inscricao->id]);
            }

            try {
                $inscricao = $projetoBolsistas->patchEntity($inscricao, [
                    'cota' => $cotaAtual !== '' ? $cotaAtual : null,
                    'primeiro_periodo' => $primeiroPeriodoInformado !== '' ? (int)$primeiroPeriodoInformado : null,
                ]);
                $projetoBolsistas->saveOrFail($inscricao);
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - salvar dados no dados bolsista',
                    'Não foi possível salvar os dados do bolsista.'
                );
                return $this->redirect(['action' => 'dadosBolsistaRenovacao', $edital->id, $inscricao->id]);
            }

            if ($this->anexarInscricao($anexosUpload, $inscricao->projeto_id, $inscricao->id, null, true)) {
                $faseAtual = (int)$inscricao->fase_id;
                $this->historico((int)$inscricao->id, $faseAtual, $faseAtual, 'Atualização de dados do bolsista na renovação', true);
                $this->Flash->success('Dados do bolsista salvos com sucesso.');
                $proximaAcao = (($edital->origem ?? null) === 'N') ? 'sumulaRenovacao' : 'projetoRenovacao';
                return $this->redirect(['action' => $proximaAcao, $edital->id, $inscricao->id]);
            }

            $this->Flash->error('Não foi possível salvar os anexos. Tente novamente.');
            return $this->redirect(['action' => 'dadosBolsistaRenovacao', $edital->id, $inscricao->id]);
        }


        $this->set(compact(
            'anexos',
            'inscricao',
            'anexosTiposDefault',
            'anexosTiposPrograma',
            'anexosTiposCota',
            'anexosTiposPrimeiroPeriodo',
            'anexosTiposCondicional',
            'cotas',
            'bolsistaMenorIdade',
            'referenciaDetalhe'
        ));
        $this->set(compact('edital', 'identity'));
    }

    private function checkBolsista(int $usuarioId): void
    {
        $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $temVigente = $projetoBolsistas->find()
            ->where([
                'ProjetoBolsistas.bolsista' => $usuarioId,
                'ProjetoBolsistas.deleted IS' => null,
                'ProjetoBolsistas.vigente' => 1,
                'ProjetoBolsistas.fase_id' => 11,
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
            throw new \Exception('O bolsista informado possui bolsa ativa ou inscricao em andamento.');
        }
    }

    public function selecionarRenovacaoInscricao($editalId = null)
    {
        $context = $this->loadContext($editalId);
        if (isset($context['redirect'])) {
            return $context['redirect'];
        }

        $edital = $context['edital'];
        $identity = $context['identity'];

        if (($edital->origem ?? null) !== 'R') {
            return $this->redirect(['action' => 'dadosBolsistaRenovacao', $edital->id]);
        }

        $referenciaId = (int)$this->request->getQuery('referencia_id');
        if ($referenciaId > 0) {
            return $this->criarOuCarregarRenovacao($edital, $identity, $referenciaId);
        }

        $renovacoes = $this->fetchTable('ProjetoBolsistas')->find()
            ->contain(['Bolsistas'])
            ->where([
                'ProjetoBolsistas.orientador' => (int)$identity->id,
                'ProjetoBolsistas.programa_id' => (int)$edital->programa_id,
                'ProjetoBolsistas.deleted IS' => null,
                'ProjetoBolsistas.fase_id' => 11,
                'ProjetoBolsistas.vigente' => 1,
            ])
            ->orderBy(['ProjetoBolsistas.id' => 'DESC']);

        $qtdRenovacoes = $renovacoes->count();
        if ($qtdRenovacoes === 1) {
            $registro = $renovacoes->first();
            return $this->criarOuCarregarRenovacao($edital, $identity, (int)$registro->id);
        }

        if ($qtdRenovacoes === 0) {
            $this->Flash->error('Você não tem inscrição apta para renovar. Caso já tenha solicitado alguma renovação, acesse a renovação na tela de inscrições em andamento para edição.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'A']);
        }

        $this->set(compact('renovacoes'));
        $this->set($context);
    }

    public function sumulaRenovacao($editalId = null, $inscricaoId = null)
    {
        $context = $this->loadContext($editalId);
        if (isset($context['redirect'])) {
            return $context['redirect'];
        }

        $inscricaoContext = $this->loadInscricaoEditavel($context, $inscricaoId);
        if (isset($inscricaoContext['redirect'])) {
            return $inscricaoContext['redirect'];
        }
        $inscricao = $inscricaoContext['inscricao'];

        if (($context['edital']->origem ?? null) !== 'N') {
            return $this->redirect(['action' => 'projetoRenovacao', $context['edital']->id, $inscricaoId]);
        }

        $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $sumulasEdital = $this->fetchTable('EditaisSumulas')->find()
            ->contain(['EditaisSumulasBlocos'])
            ->where([
                'EditaisSumulas.editai_id' => (int)$context['edital']->id,
                'EditaisSumulas.deleted IS' => null,
            ])
            ->orderBy([
                'EditaisSumulas.editais_sumula_bloco_id' => 'ASC',
                'EditaisSumulas.id' => 'ASC',
            ])
            ->all();

        $sumulasPermitidas = [];
        foreach ($sumulasEdital as $sumulaItem) {
            $sumulasPermitidas[(int)$sumulaItem->id] = $sumulaItem;
        }

        $inscricaoSumulasTable = $this->fetchTable('InscricaoSumulas');
        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();
            $filhosMenor = array_key_exists('filhos_menor', $dados) ? (string)$dados['filhos_menor'] : '';
            if ($filhosMenor !== '' && !in_array($filhosMenor, ['0', '1', '2'], true)) {
                $this->Flash->error('Informe corretamente a opção de filhos menores.');
                return $this->redirect(['action' => 'sumulaRenovacao', $context['edital']->id, $inscricao->id]);
            }

            $linhasSumula = $dados['sumula'] ?? [];
            if (!is_array($linhasSumula)) {
                $linhasSumula = [];
            }

            $quantidadesValidas = [];
            foreach ($linhasSumula as $linha) {
                $sumulaId = (int)($linha['editais_sumula_id'] ?? 0);
                if ($sumulaId <= 0 || !isset($sumulasPermitidas[$sumulaId])) {
                    continue;
                }
                $qtdRaw = trim((string)($linha['quantidade'] ?? ''));
                if ($qtdRaw === '') {
                    $quantidadesValidas[$sumulaId] = null;
                    continue;
                }
                if (!ctype_digit($qtdRaw)) {
                    $this->Flash->error('Quantidade inválida na súmula.');
                    return $this->redirect(['action' => 'sumulaRenovacao', $context['edital']->id, $inscricao->id]);
                }
                $quantidade = (int)$qtdRaw;
                if ($quantidade < 0 || $quantidade > 50) {
                    $this->Flash->error('A quantidade deve estar entre 0 e 50.');
                    return $this->redirect(['action' => 'sumulaRenovacao', $context['edital']->id, $inscricao->id]);
                }
                $quantidadesValidas[$sumulaId] = $quantidade;
            }
            foreach ($sumulasPermitidas as $sumulaId => $sumulaItem) {
                if (!array_key_exists((int)$sumulaId, $quantidadesValidas)) {
                    $quantidadesValidas[(int)$sumulaId] = null;
                }
            }

            try {
                $connection = $inscricaoSumulasTable->getConnection();
                $connection->transactional(function () use (
                    $projetoBolsistas,
                    $inscricao,
                    $filhosMenor,
                    $inscricaoSumulasTable,
                    $quantidadesValidas,
                    $sumulasPermitidas,
                    $context
                ) {
                    $inscricaoAtualizada = $projetoBolsistas->patchEntity($inscricao, [
                        'filhos_menor' => $filhosMenor !== '' ? $filhosMenor : null,
                    ]);
                    $projetoBolsistas->saveOrFail($inscricaoAtualizada);

                    $registrosExistentes = $inscricaoSumulasTable->find()
                        ->where([
                            'projeto_bolsista_id' => (int)$inscricao->id,
                            'pdj_inscricoe_id IS' => null,
                        ])
                        ->all();
                    $mapExistentes = [];
                    foreach ($registrosExistentes as $registroExistente) {
                        $mapExistentes[(int)$registroExistente->editais_sumula_id] = $registroExistente;
                    }

                    foreach ($quantidadesValidas as $sumulaId => $quantidade) {
                        $sumulaItem = $sumulasPermitidas[(int)$sumulaId];
                        $inscricaoSumula = $mapExistentes[(int)$sumulaId] ?? $inscricaoSumulasTable->newEmptyEntity();
                        $inscricaoSumula = $inscricaoSumulasTable->patchEntity($inscricaoSumula, [
                            'editais_sumula_id' => (int)$sumulaItem->id,
                            'editai_id' => (int)$context['edital']->id,
                            'editais_sumula_bloco_id' => (int)$sumulaItem->editais_sumula_bloco_id,
                            'projeto_bolsista_id' => (int)$inscricao->id,
                            'pdj_inscricoe_id' => null,
                            'quantidade' => $quantidade,
                        ]);
                        $inscricaoSumulasTable->saveOrFail($inscricaoSumula);
                    }

                    $sumulasAtivasIds = array_map('intval', array_keys($sumulasPermitidas));
                    $inscricaoSumulasTable->deleteAll([
                        'projeto_bolsista_id' => (int)$inscricao->id,
                        'pdj_inscricoe_id IS' => null,
                        'editais_sumula_id NOT IN' => $sumulasAtivasIds,
                    ]);
                });
            } catch (\Throwable $e) {
                $this->flashFriendlyException($e, 'Erro no Sistema - salvar súmula da renovação');
                return $this->redirect(['action' => 'sumulaRenovacao', $context['edital']->id, $inscricao->id]);
            }

            $faseAtual = (int)$inscricao->fase_id;
            $this->historico((int)$inscricao->id, $faseAtual, $faseAtual, 'Atualização da súmula da renovação', true);
            $this->Flash->success('Súmula salva com sucesso.');
            return $this->redirect(['action' => 'projetoRenovacao', $context['edital']->id, $inscricao->id]);
        }

        $inscricaoSumulasSalvas = $inscricaoSumulasTable->find()
            ->where([
                'InscricaoSumulas.projeto_bolsista_id' => (int)$inscricao->id,
                'InscricaoSumulas.pdj_inscricoe_id IS' => null,
            ])
            ->all();
        $quantidadesSalvas = [];
        foreach ($inscricaoSumulasSalvas as $itemSalvo) {
            $quantidadesSalvas[(int)$itemSalvo->editais_sumula_id] = $itemSalvo->quantidade;
        }

        $this->set(compact('sumulasEdital', 'quantidadesSalvas'));
        $this->set(compact('inscricao'));
        $this->set($context);
    }

    public function projetoRenovacao($editalId = null, $inscricaoId = null)
    {
        $context = $this->loadContext($editalId);
        if (isset($context['redirect'])) {
            return $context['redirect'];
        }

        $inscricaoContext = $this->loadInscricaoEditavel($context, $inscricaoId);
        if (isset($inscricaoContext['redirect'])) {
            return $inscricaoContext['redirect'];
        }
        $inscricao = $inscricaoContext['inscricao'];

        $projetoSelecionado = null;
        if (!empty($inscricao->projeto_id)) {
            $projetoSelecionado = $this->fetchTable('Projetos')->find()
                ->contain(['Areas', 'Linhas'])
                ->where([
                    'Projetos.id' => (int)$inscricao->projeto_id,
                    'Projetos.usuario_id' => (int)$context['identity']->id,
                    'Projetos.deleted IS' => null,
                ])
                ->first();
        }

        $anexosTiposProjeto = $this->fetchTable('AnexosTipos')->find()
            ->where([
                'AnexosTipos.bloco' => 'P',
                'AnexosTipos.deleted' => 0,
            ])
            ->orderBy(['AnexosTipos.id' => 'ASC'])
            ->all();
        $anexosTiposProjetoOrdenados = [];
        $anexosTiposProjetoRestantes = [];
        foreach ($anexosTiposProjeto as $tipoProjetoItem) {
            if ((int)$tipoProjetoItem->id === 5) {
                $anexosTiposProjetoOrdenados[] = $tipoProjetoItem;
                continue;
            }
            $anexosTiposProjetoRestantes[] = $tipoProjetoItem;
        }
        $anexosTiposProjeto = array_merge($anexosTiposProjetoOrdenados, $anexosTiposProjetoRestantes);
        $tiposAnexosProjetoPermitidos = [];
        foreach ($anexosTiposProjeto as $tipoProjeto) {
            $tiposAnexosProjetoPermitidos[] = (int)$tipoProjeto->id;
        }

        $anexosProjeto = $this->fetchTable('Anexos')->find()
            ->where([
                'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                'Anexos.deleted IS' => null,
            ])
            ->orderBy(['Anexos.id' => 'DESC'])
            ->all();
        $anexos = [];
        foreach ($anexosProjeto as $anexoProjeto) {
            $tipoId = (int)$anexoProjeto->anexos_tipo_id;
            if (!isset($anexos[$tipoId])) {
                $anexos[$tipoId] = (string)$anexoProjeto->anexo;
            }
        }
        // O tipo 5 pode estar vinculado somente ao projeto (sem projeto_bolsista_id).
        // Nesse caso, traz o anexo ativo mais recente para exibir no fluxo de renovacao.
        if (!isset($anexos[5]) && !empty($inscricao->projeto_id)) {
            $anexoProjetoTipo5 = $this->fetchTable('Anexos')->find()
                ->where([
                    'Anexos.projeto_id' => (int)$inscricao->projeto_id,
                    'Anexos.anexos_tipo_id' => 5,
                    'Anexos.deleted IS' => null,
                ])
                ->orderBy(['Anexos.id' => 'DESC'])
                ->first();
            if ($anexoProjetoTipo5) {
                $anexos[5] = (string)$anexoProjetoTipo5->anexo;
            }
        }

        $grandesAreas = $this->fetchTable('GrandesAreas')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])->orderBy(['GrandesAreas.nome' => 'ASC'])->toArray();

        $areasRows = $this->fetchTable('Areas')->find()
            ->select(['id', 'nome', 'grandes_area_id'])
            ->orderBy(['Areas.nome' => 'ASC'])
            ->all();

        $areas = [];
        $areasPorGrandeArea = [];
        foreach ($areasRows as $areaRow) {
            $areas[(int)$areaRow->id] = (string)$areaRow->nome;
            $grandeAreaId = (int)($areaRow->grandes_area_id ?? 0);
            if (!isset($areasPorGrandeArea[$grandeAreaId])) {
                $areasPorGrandeArea[$grandeAreaId] = [];
            }
            $areasPorGrandeArea[$grandeAreaId][] = [
                'id' => (int)$areaRow->id,
                'nome' => (string)$areaRow->nome,
            ];
        }

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
            if (!isset($linhasPorAreaFiocruz[$areaFiocruzId])) {
                $linhasPorAreaFiocruz[$areaFiocruzId] = [];
            }
            $linhasPorAreaFiocruz[$areaFiocruzId][] = [
                'id' => (int)$linhaRow->id,
                'nome' => (string)$linhaRow->nome,
            ];
        }

        $areaFiocruzSelecionada = null;
        if ($projetoSelecionado && !empty($projetoSelecionado->linha) && $projetoSelecionado->linha->areas_fiocruz_id !== null) {
            $areaFiocruzSelecionada = (int)$projetoSelecionado->linha->areas_fiocruz_id;
        }
        $grandeAreaSelecionada = null;
        if ($projetoSelecionado && !empty($projetoSelecionado->area) && $projetoSelecionado->area->grandes_area_id !== null) {
            $grandeAreaSelecionada = (int)$projetoSelecionado->area->grandes_area_id;
        }
        $podePreencherAreaCnpq = $projetoSelecionado !== null && empty($projetoSelecionado->area_id);
        $podePreencherLinhaFiocruz = $projetoSelecionado !== null && empty($projetoSelecionado->linha_id);
        $podePreencherResumoProjeto = $projetoSelecionado !== null && trim((string)($projetoSelecionado->resumo ?? '')) === '';

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();
            $projetoId = !empty($inscricao->projeto_id) ? (int)$inscricao->projeto_id : null;
            $houveAtualizacaoProjeto = false;

            $acaoRapidaAnexo = $this->processarAcaoRapidaAnexoInscricao(
                $dados,
                $projetoId,
                (int)$inscricao->id,
                $tiposAnexosProjetoPermitidos
            );
            if ($acaoRapidaAnexo !== null) {
                return $this->redirect(['action' => 'projetoRenovacao', $context['edital']->id, $inscricao->id]);
            }

            $dadosProjeto = [];
            if ($podePreencherAreaCnpq) {
                $areaId = (int)($dados['area_id'] ?? 0);
                if ($areaId > 0) {
                    if (!isset($areas[$areaId])) {
                        $this->Flash->error('Área CNPQ informada é inválida.');
                        return $this->redirect(['action' => 'projetoRenovacao', $context['edital']->id, $inscricao->id]);
                    }
                    $dadosProjeto['area_id'] = $areaId;
                }
            }
            if ($podePreencherLinhaFiocruz) {
                $linhaId = (int)($dados['linha_id'] ?? 0);
                if ($linhaId > 0) {
                    if (!isset($linhas[$linhaId])) {
                        $this->Flash->error('Linha Fiocruz informada é inválida.');
                        return $this->redirect(['action' => 'projetoRenovacao', $context['edital']->id, $inscricao->id]);
                    }
                    $dadosProjeto['linha_id'] = $linhaId;
                }
            }
            if ($podePreencherResumoProjeto) {
                $resumo = trim((string)($dados['resumo'] ?? ''));
                if ($resumo !== '') {
                    $erroResumoProjeto = $this->validarTextoComLimites($resumo, 'Resumo do projeto', 20, 4000);
                    if ($erroResumoProjeto !== null) {
                        $this->Flash->error($erroResumoProjeto);
                        return $this->redirect(['action' => 'projetoRenovacao', $context['edital']->id, $inscricao->id]);
                    }
                    $dadosProjeto['resumo'] = $resumo;
                }
            }
            if (!empty($dadosProjeto) && $projetoSelecionado) {
                $tblProjetos = $this->fetchTable('Projetos');
                $projetoAtualizado = $tblProjetos->patchEntity($projetoSelecionado, $dadosProjeto);
                try {
                    $tblProjetos->saveOrFail($projetoAtualizado);
                    $houveAtualizacaoProjeto = true;
                } catch (\Throwable $e) {
                    $this->flashFriendlyException(
                        $e,
                        'Erro no Sistema - atualizar dados do projeto na renovação',
                    'Não foi possível salvar os dados do projeto.'
                    );
                    return $this->redirect(['action' => 'projetoRenovacao', $context['edital']->id, $inscricao->id]);
                }
            }

            $anexosUpload = $dados['anexos'] ?? [];
            if (!is_array($anexosUpload)) {
                $anexosUpload = [];
            }
            $enviouTipo5 = isset($anexosUpload[5]) && is_object($anexosUpload[5]) && $anexosUpload[5]->getClientFilename() !== '';
            if (!empty($anexos[5]) && $enviouTipo5) {
                unset($anexosUpload[5]);
                $this->Flash->error('O anexo do tipo 5 já existe e não pode ser alterado.');
            }

            $temUpload = false;
            foreach ($anexosUpload as $arquivoUpload) {
                if (is_object($arquivoUpload) && $arquivoUpload->getClientFilename() !== '') {
                    $temUpload = true;
                    break;
                }
            }

            if (!$temUpload) {
                $faseAtual = (int)$inscricao->fase_id;
                if ($houveAtualizacaoProjeto) {
                    $this->historico((int)$inscricao->id, $faseAtual, $faseAtual, 'Atualização do projeto da renovação', true);
                    $this->Flash->success('Dados do projeto atualizados com sucesso.');
                } else {
                    $this->historico((int)$inscricao->id, $faseAtual, $faseAtual, 'Submissão de projeto da renovação sem alterações', true);
                    $this->Flash->success('Dados do projeto mantidos sem alterações.');
                }
                return $this->redirect(['action' => 'subprojetoRenovacao', $context['edital']->id, $inscricao->id]);
            }

            try {
                if (!$this->anexarInscricao($anexosUpload, $projetoId, (int)$inscricao->id, null, true)) {
                    throw new \RuntimeException('Falha ao salvar anexos do projeto.');
                }
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - salvar anexos do projeto da inscricao',
                    'Não foi possível processar os anexos do projeto.'
                );
                return $this->redirect(['action' => 'projetoRenovacao', $context['edital']->id, $inscricao->id]);
            }

            $faseAtual = (int)$inscricao->fase_id;
            $this->historico((int)$inscricao->id, $faseAtual, $faseAtual, 'Atualização de anexos do projeto na renovação', true);
            $this->Flash->success('Anexos do projeto salvos com sucesso.');
            return $this->redirect(['action' => 'subprojetoRenovacao', $context['edital']->id, $inscricao->id]);
        }

        $this->set(compact(
            'inscricao',
            'projetoSelecionado',
            'anexosTiposProjeto',
            'anexos',
            'grandesAreas',
            'areas',
            'areasFiocruz',
            'areasPorGrandeArea',
            'linhas',
            'linhasPorAreaFiocruz',
            'grandeAreaSelecionada',
            'areaFiocruzSelecionada',
            'podePreencherAreaCnpq',
            'podePreencherLinhaFiocruz',
            'podePreencherResumoProjeto'
        ));
        $this->set($context);
    }

    public function subprojetoRenovacao($editalId = null, $inscricaoId = null)
    {
        $context = $this->loadContext($editalId);
        if (isset($context['redirect'])) {
            return $context['redirect'];
        }

        $inscricaoContext = $this->loadInscricaoEditavel($context, $inscricaoId);
        if (isset($inscricaoContext['redirect'])) {
            return $inscricaoContext['redirect'];
        }
        $inscricao = $inscricaoContext['inscricao'];

        $anexoTipoRelatorioParcial = 13;
        $anexoTipoSubprojeto = 20;
        $anexos = [];
        $anexoAtualRelatorioParcial = $this->fetchTable('Anexos')->find()
            ->where([
                'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                'Anexos.anexos_tipo_id' => $anexoTipoRelatorioParcial,
                'Anexos.deleted IS' => null,
            ])
            ->orderBy(['Anexos.id' => 'DESC'])
            ->first();
        if ($anexoAtualRelatorioParcial) {
            $anexos[$anexoTipoRelatorioParcial] = (string)$anexoAtualRelatorioParcial->anexo;
        }
        $anexoAtualSubprojeto = $this->fetchTable('Anexos')->find()
            ->where([
                'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                'Anexos.anexos_tipo_id' => $anexoTipoSubprojeto,
                'Anexos.deleted IS' => null,
            ])
            ->orderBy(['Anexos.id' => 'DESC'])
            ->first();
        if ($anexoAtualSubprojeto) {
            $anexos[$anexoTipoSubprojeto] = (string)$anexoAtualSubprojeto->anexo;
        }

        $referenciaSubprojeto = null;
        $referenciaId = (int)($inscricao->referencia_inscricao_anterior ?? 0);
        if ($referenciaId > 0) {
            $referenciaSubprojeto = $this->fetchTable('ProjetoBolsistas')->find()
                ->select(['id', 'sp_titulo', 'sp_resumo'])
                ->where([
                    'ProjetoBolsistas.id' => $referenciaId,
                    'ProjetoBolsistas.deleted IS' => null,
                ])
                ->first();

            if ($referenciaSubprojeto) {
                $anexoReferenciaSubprojeto = $this->fetchTable('Anexos')->find()
                    ->where([
                        'Anexos.projeto_bolsista_id' => (int)$referenciaSubprojeto->id,
                        'Anexos.anexos_tipo_id' => $anexoTipoSubprojeto,
                        'Anexos.deleted IS' => null,
                    ])
                    ->orderBy(['Anexos.id' => 'DESC'])
                    ->first();
                if ($anexoReferenciaSubprojeto) {
                    $referenciaSubprojeto->anexo_subprojeto = (string)$anexoReferenciaSubprojeto->anexo;
                }
            }
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();
            $modoSubprojeto = strtoupper(trim((string)($dados['modo_subprojeto'] ?? '')));
            if (!in_array($modoSubprojeto, ['I', 'D'], true)) {
                $this->Flash->error('Informe se deseja manter o subprojeto anterior ou cadastrar um novo.');
                return $this->redirect(['action' => 'subprojetoRenovacao', $context['edital']->id, $inscricao->id]);
            }
            $autorizacaoInformada = isset($dados['autorizacao']) ? (string)$dados['autorizacao'] : '';
            $autorizacaoSalvar = in_array($autorizacaoInformada, ['0', '1'], true)
                ? (int)$autorizacaoInformada
                : $inscricao->autorizacao;
            $resumoRelatorio = trim((string)($dados['resumo_relatorio'] ?? ''));
            $tituloSubprojetoNovo = trim((string)($dados['sp_titulo'] ?? ''));
            $resumoSubprojetoNovo = trim((string)($dados['sp_resumo'] ?? ''));
            $justificativaAlteracao = trim((string)($dados['justificativa_alteracao'] ?? ''));
            $errosTextoSubprojeto = [];
            $erroResumoRelatorio = $this->validarTextoComLimites($resumoRelatorio, 'Resumo do relatorio', 20, 4000);
            if ($erroResumoRelatorio !== null) {
                $errosTextoSubprojeto[] = $erroResumoRelatorio;
            }
            if ($modoSubprojeto === 'D') {
                $erroTituloSubprojeto = $this->validarTextoComLimites($tituloSubprojetoNovo, 'Titulo do subprojeto', 20, 255);
                if ($erroTituloSubprojeto !== null) {
                    $errosTextoSubprojeto[] = $erroTituloSubprojeto;
                }
                $erroResumoSubprojeto = $this->validarTextoComLimites($resumoSubprojetoNovo, 'Resumo do subprojeto', 20, 4000);
                if ($erroResumoSubprojeto !== null) {
                    $errosTextoSubprojeto[] = $erroResumoSubprojeto;
                }
                $erroJustificativa = $this->validarTextoComLimites($justificativaAlteracao, 'Justificativa da alteracao', 20, 4000);
                if ($erroJustificativa !== null) {
                    $errosTextoSubprojeto[] = $erroJustificativa;
                }
            }
            if (!empty($errosTextoSubprojeto)) {
                foreach ($errosTextoSubprojeto as $erroTextoSubprojeto) {
                    $this->Flash->error($erroTextoSubprojeto);
                }
                return $this->redirect(['action' => 'subprojetoRenovacao', $context['edital']->id, $inscricao->id]);
            }
            if ($modoSubprojeto === 'I' && !$referenciaSubprojeto) {
                $this->Flash->error('Subprojeto de referência não localizado para replicação.');
                return $this->redirect(['action' => 'subprojetoRenovacao', $context['edital']->id, $inscricao->id]);
            }

            try {
                $tiposAcaoRapida = [$anexoTipoRelatorioParcial];
                if ($modoSubprojeto === 'D') {
                    $tiposAcaoRapida[] = $anexoTipoSubprojeto;
                }

                $acaoRapidaAnexo = $this->processarAcaoRapidaAnexoInscricao(
                    $dados,
                    !empty($inscricao->projeto_id) ? (int)$inscricao->projeto_id : null,
                    (int)$inscricao->id,
                    $tiposAcaoRapida
                );
                if ($acaoRapidaAnexo !== null) {
                    return $this->redirect(['action' => 'subprojetoRenovacao', $context['edital']->id, $inscricao->id]);
                }
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - ação rápida de anexo no subprojeto',
                    'Não foi possível processar a ação do anexo.'
                );
                return $this->redirect(['action' => 'subprojetoRenovacao', $context['edital']->id, $inscricao->id]);
            }

            try {
                $anexosUpload = $dados['anexos'] ?? [];
                if (!is_array($anexosUpload)) {
                    $anexosUpload = [];
                }
                $anexosUploadRelatorio = [];
                if (isset($anexosUpload[$anexoTipoRelatorioParcial])) {
                    $anexosUploadRelatorio[$anexoTipoRelatorioParcial] = $anexosUpload[$anexoTipoRelatorioParcial];
                }
                $anexosUploadSubprojeto = [];
                if (isset($anexosUpload[$anexoTipoSubprojeto])) {
                    $anexosUploadSubprojeto[$anexoTipoSubprojeto] = $anexosUpload[$anexoTipoSubprojeto];
                }

                $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
                $tblProjetoBolsistas->getConnection()->transactional(function () use (
                    $tblProjetoBolsistas,
                    &$inscricao,
                    $dados,
                    $modoSubprojeto,
                    $autorizacaoSalvar,
                    $referenciaSubprojeto,
                    $anexoTipoSubprojeto,
                    $anexosUploadRelatorio,
                    $anexosUploadSubprojeto,
                    $anexoTipoRelatorioParcial
                ) {
                    if ($modoSubprojeto === 'I') {
                        $inscricaoPatch = $tblProjetoBolsistas->patchEntity($inscricao, [
                            'sp_titulo' => trim((string)($referenciaSubprojeto->sp_titulo ?? '')),
                            'sp_resumo' => trim((string)($referenciaSubprojeto->sp_resumo ?? '')),
                            'resumo_relatorio' => trim((string)($dados['resumo_relatorio'] ?? '')),
                            'autorizacao' => $autorizacaoSalvar,
                            'subprojeto_renovacao' => 'I',
                        ]);
                        $tblProjetoBolsistas->saveOrFail($inscricaoPatch);
                        $this->replicarAnexoSubprojetoDaReferencia((int)$inscricao->id, (int)$anexoTipoSubprojeto, (int)($referenciaSubprojeto->id ?? 0));
                        if (!$this->anexarInscricao($anexosUploadRelatorio, $inscricao->projeto_id, (int)$inscricao->id, null, true)) {
                            throw new \RuntimeException('Falha ao salvar anexo do relatorio parcial.');
                        }
                    } else {
                        $inscricaoPatch = $tblProjetoBolsistas->patchEntity($inscricao, [
                            'sp_titulo' => trim((string)($dados['sp_titulo'] ?? '')),
                            'sp_resumo' => trim((string)($dados['sp_resumo'] ?? '')),
                            'resumo_relatorio' => trim((string)($dados['resumo_relatorio'] ?? '')),
                            'justificativa_alteracao' => trim((string)($dados['justificativa_alteracao'] ?? '')),
                            'autorizacao' => $autorizacaoSalvar,
                            'subprojeto_renovacao' => 'D',
                        ]);
                        $tblProjetoBolsistas->saveOrFail($inscricaoPatch);

                        if (!$this->anexarInscricao($anexosUploadSubprojeto, $inscricao->projeto_id, (int)$inscricao->id, null, true)) {
                            throw new \RuntimeException('Falha ao salvar anexo do subprojeto.');
                        }
                        if (!$this->anexarInscricao($anexosUploadRelatorio, $inscricao->projeto_id, (int)$inscricao->id, null, true)) {
                            throw new \RuntimeException('Falha ao salvar anexo do relatorio parcial.');
                        }
                    }
                });
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - salvar subprojeto da inscricao',
                    'Não foi possível processar os dados do subprojeto.'
                );
                return $this->redirect(['action' => 'subprojetoRenovacao', $context['edital']->id, $inscricao->id]);
            }

            $faseAtual = (int)$inscricao->fase_id;
            $this->historico((int)$inscricao->id, $faseAtual, $faseAtual, 'Atualização do subprojeto na renovação', true);
            $this->Flash->success('Subprojeto salvo com sucesso.');
            return $this->redirect(['action' => 'coorientadorRenovacao', $context['edital']->id, $inscricao->id]);
        }

        $this->set(compact('inscricao', 'anexos', 'referenciaSubprojeto'));
        $this->set($context);
    }

    private function replicarAnexoSubprojetoDaReferencia(int $inscricaoDestinoId, int $anexoTipoId, int $referenciaId): void
    {
        if ($inscricaoDestinoId <= 0 || $referenciaId <= 0) {
            return;
        }

        $tblAnexos = $this->fetchTable('Anexos');
        $agora = date('Y-m-d H:i:s');
        $tblAnexos->updateAll(
            ['deleted' => $agora],
            [
                'Anexos.projeto_bolsista_id' => $inscricaoDestinoId,
                'Anexos.anexos_tipo_id' => $anexoTipoId,
                'Anexos.deleted IS' => null,
            ]
        );

        $anexoReferencia = $tblAnexos->find()
            ->where([
                'Anexos.projeto_bolsista_id' => $referenciaId,
                'Anexos.anexos_tipo_id' => $anexoTipoId,
                'Anexos.deleted IS' => null,
            ])
            ->orderBy(['Anexos.id' => 'DESC'])
            ->first();

        if (!$anexoReferencia) {
            return;
        }

        $copia = $tblAnexos->newEmptyEntity();
        $copia = $tblAnexos->patchEntity($copia, [
            'projeto_id' => $anexoReferencia->projeto_id,
            'projeto_bolsista_id' => $inscricaoDestinoId,
            'anexos_tipo_id' => $anexoReferencia->anexos_tipo_id,
            'anexo' => $anexoReferencia->anexo,
            'usuario_id' => $anexoReferencia->usuario_id,
            'raic_id' => $anexoReferencia->raic_id,
            'pdj_inscricoe_id' => $anexoReferencia->pdj_inscricoe_id,
            'bloco' => $anexoReferencia->bloco,
            'created' => $anexoReferencia->created,
            'modified' => $anexoReferencia->modified,
            'deleted' => null,
        ], [
            'accessibleFields' => [
                'created' => true,
                'modified' => true,
            ],
        ]);
        $tblAnexos->saveOrFail($copia);
    }

    public function coorientadorRenovacao($editalId = null, $inscricaoId = null)
    {
        $context = $this->loadContext($editalId);
        if (isset($context['redirect'])) {
            return $context['redirect'];
        }

        $inscricaoContext = $this->loadInscricaoEditavel($context, $inscricaoId);
        if (isset($inscricaoContext['redirect'])) {
            return $inscricaoContext['redirect'];
        }
        $edital = $context['edital'];
        $identity = $context['identity'];
        $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $inscricao = $projetoBolsistas->find()
            ->contain([
                'Coorientadores',
                'Anexos' => ['conditions' => 'Anexos.deleted IS NULL', 'AnexosTipos'],
            ])
            ->where([
                'ProjetoBolsistas.id' => (int)$inscricaoContext['inscricao']->id,
                'ProjetoBolsistas.editai_id' => (int)$edital->id,
                'ProjetoBolsistas.orientador' => (int)$identity->id,
                'ProjetoBolsistas.deleted IS' => null,
            ])
            ->first();

        if (!$inscricao) {
            $this->Flash->error('Inscrição não localizada. Reinicie o processo.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
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
                return $this->redirect(['action' => 'coorientadorRenovacao', $edital->id, $inscricao->id]);
            }

            $acao = (string)($dados['acao'] ?? 'salvar_anexos');
            if (in_array($acao, ['vincular_coorientador', 'incluir_coorientador'], true)) {
                $cpfInformado = (string)($dados['cpf_coorientador'] ?? '');
                if (!$this->validaCPF($cpfInformado)) {
                    $this->Flash->error('Informe um CPF válido para o coorientador.');
                    return $this->redirect(['action' => 'coorientadorRenovacao', $edital->id, $inscricao->id]);
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
                        return $this->redirect(['action' => 'coorientadorRenovacao', $edital->id, $inscricao->id]);
                    }

                    $faseOriginal = (int)$inscricao->fase_id;
                    $inscricao = $projetoBolsistas->patchEntity($inscricao, [
                        'coorientador' => (int)$coorientador->id,
                    ]);
                    $projetoBolsistas->saveOrFail($inscricao);
                    $this->limparAnexosPorBloco((int)$inscricao->id, 'C');
                    $this->historico((int)$inscricao->id, $faseOriginal, $faseOriginal, 'Vinculação de usuário cadastrada no Coorientador', true);
                    $this->Flash->success('Coorientador vinculado à inscrição com sucesso.');
                } catch (\Throwable $e) {
                    $this->flashFriendlyException(
                        $e,
                        'Erro no Sistema - vincular coorientador na inscricao',
                        'Não foi possível vincular o coorientador na inscrição.'
                    );
                }

                return $this->redirect(['action' => 'coorientadorRenovacao', $edital->id, $inscricao->id]);
            }

            if ($acao === 'excluir_coorientador') {
                try {
                    $faseOriginal = (int)$inscricao->fase_id;
                    $inscricao = $projetoBolsistas->patchEntity($inscricao, ['coorientador' => null]);
                    $projetoBolsistas->saveOrFail($inscricao);
                    $this->limparAnexosPorBloco((int)$inscricao->id, 'C');
                    $this->historico((int)$inscricao->id, $faseOriginal, $faseOriginal, 'Exclusão de coorientador na inscrição', true);
                    $this->Flash->success('Coorientador removido da inscrição.');
                } catch (\Throwable $e) {
                    $this->flashFriendlyException(
                        $e,
                        'Erro no Sistema - excluir coorientador na inscricao',
                        'Não foi possível remover o coorientador da inscrição.'
                    );
                }

                return $this->redirect(['action' => 'coorientadorRenovacao', $edital->id, $inscricao->id]);
            }

            $anexosUpload = $dados['anexos'] ?? [];
            if (!is_array($anexosUpload)) {
                $anexosUpload = [];
            }
            if (empty($inscricao->coorientador)) {
                $this->Flash->error('Vincule um coorientador antes de anexar os documentos.');
                return $this->redirect(['action' => 'coorientadorRenovacao', $edital->id, $inscricao->id]);
            }

            if ($this->anexarInscricao($anexosUpload, $inscricao->projeto_id, $inscricao->id, null, true)) {
                $faseAtual = (int)$inscricao->fase_id;
                $this->historico((int)$inscricao->id, $faseAtual, $faseAtual, 'Atualização de dados do coorientador na renovação', true);
                $this->Flash->success('Dados do coorientador salvos com sucesso.');
                return $this->redirect(['action' => 'gerarTermoRenovacao', $edital->id, $inscricao->id]);
            }

            $this->Flash->error('Não foi possível salvar os anexos. Tente novamente.');
            return $this->redirect(['action' => 'coorientadorRenovacao', $edital->id, $inscricao->id]);
        }

        $this->set(compact('inscricao', 'anexosTiposC', 'anexos', 'coorientadorUsuario'));
        $this->set($context);
    }


    public function gerarTermoRenovacao($editalId = null, $inscricaoId = null)
    {
        $this->request->allowMethod(['get', 'post']);

        $context = $this->loadContext($editalId);
        if (isset($context['redirect'])) {
            return $context['redirect'];
        }
        $edital = $context['edital'];
        $identity = $context['identity'];
        if (empty($inscricaoId)) {
            $this->Flash->error('Inscrição não informada.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $inscricao = $this->fetchTable('ProjetoBolsistas')->find()
            ->contain([
                'Bolsistas',
                'Orientadores' => ['Vinculos'],
                'Coorientadores',
                'Projetos',
                'Anexos' => ['conditions' => 'Anexos.deleted IS NULL', 'AnexosTipos'],
            ])
            ->where([
                'ProjetoBolsistas.id' => (int)$inscricaoId,
                'ProjetoBolsistas.editai_id' => (int)$edital->id,
                'ProjetoBolsistas.deleted IS' => null,

            ])
            ->first();
        if (!$inscricao) {
            $this->Flash->error('Inscrição não localizada ou deletada. Reinicie o processo.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $ehTI = $this->ehTi();
        if ((int)$inscricao->orientador !== (int)$identity->id && !$ehTI) {
            $this->Flash->error('Acesso negado. Somente o Orientador pode realizar esta ação.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }
        if (!in_array((int)$inscricao->fase_id, [1, 3], true)) {
            $this->Flash->error('Inscrição indisponível para geração de termo nesta fase. Permitido apenas nas fases 1 e 3.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        if (!$this->request->is('post')) {
            $this->set(compact('inscricao'));
            $this->set($context);
            return;
        }

        $anexosPorTipo = [];
        foreach ((array)$inscricao->anexos as $anexo) {
            $tipoId = (int)($anexo->anexos_tipo_id ?? 0);
            if ($tipoId > 0) {
                $anexosPorTipo[$tipoId] = true;
            }
        }
        $falhas = [];

        $errosBolsista = [];
        if (empty($inscricao->bolsista)) {
            $errosBolsista[] = 'Informe o bolsista.';
        }else{
            // validar os dados do bolsista
            
            if ((string)$inscricao->bolsista_usuario->curso === '') {
                $errosBolsista[] = 'Dados do bolsista pendentes: CURSO';
            }
            
            if ((string)$inscricao->bolsista_usuario->ano_conclusao === '') {
                $errosBolsista[] = 'Dados do bolsista pendentes: ANO DE CONCLUSAO DO CURSO';
            }

            if (!empty($inscricao->bolsista_usuario->ano_conclusao) && (int)$inscricao->bolsista_usuario->ano_conclusao < (int)date('Y')) {
                $errosBolsista[] = 'O ano de conclusão é menor que o ano atual, e o programa é para alunos não graduados';
            }

            if ((string)$inscricao->bolsista_usuario->documento_numero === '') {
                $errosBolsista[] = 'Dados do bolsista pendentes: NUMERO DO DOCUMENTO DE IDENTIFICACAO';
            }
                
        }
        if (($inscricao->cota == null)) {
            $errosBolsista[] = 'Informe a cota afirmativa.';
        }
        if (($inscricao->primeiro_periodo ===null))  {
            $errosBolsista[] = 'Informe se o aluno é do primeiro período.';
        }



        
        $programaId = (string)((int)($edital->programa_id ?? 0));
        $cotaAtual = strtoupper(trim((string)($inscricao->cota ?? '')));
        $primeiroPeriodo = $inscricao->primeiro_periodo !== null ? (string)(int)$inscricao->primeiro_periodo : '';
        $bolsistaMenorIdade = false;
        if (!empty($inscricao->bolsista) && !empty($inscricao->bolsista_usuario) && !empty($inscricao->bolsista_usuario->data_nascimento)) {
            $dataNascimento = $inscricao->bolsista_usuario->data_nascimento;
            if (is_object($dataNascimento) && method_exists($dataNascimento, 'i18nFormat')) {
                $dataNascimento = $dataNascimento->i18nFormat('yyyy-MM-dd');
            }
            $idade = (int)$this->idade((string)$dataNascimento, false);
            $bolsistaMenorIdade = $idade < 18;
        }
        $tiposB = $this->fetchTable('AnexosTipos')->find()
            ->where([
                'AnexosTipos.bloco' => 'B',
                'AnexosTipos.deleted' => 0,
            ])
            ->orderBy(['AnexosTipos.id' => 'ASC'])
            ->all();
        $tiposObrigatoriosB = [];
        $nomesTiposB = [];
        foreach ($tiposB as $tipo) {
            $tipoId = (int)$tipo->id;
            $nomesTiposB[$tipoId] = (string)$tipo->nome;
            $condicional = (int)($tipo->condicional ?? 0);
            $programaRegra = trim((string)($tipo->programa ?? ''));
            $cotaRegra = strtoupper(trim((string)($tipo->cota ?? '')));

            if ($tipoId === 16) {
                if ($primeiroPeriodo === '0') {
                    $tiposObrigatoriosB[] = $tipoId;
                }
                continue;
            }

            if ($condicional === 1 && $programaRegra === '' && $cotaRegra === '') {
                if (in_array($tipoId, [18, 19], true) && $bolsistaMenorIdade) {
                    $tiposObrigatoriosB[] = $tipoId;
                }
                continue;
            }
            if ($condicional !== 0) {
                continue;
            }
            if ($programaRegra === '' && $cotaRegra === '') {
                $tiposObrigatoriosB[] = $tipoId;
                continue;
            }
            if ($cotaRegra === '' && $programaRegra !== '') {
                $programas = array_filter(array_map('trim', explode(',', $programaRegra)));
                if (in_array($programaId, $programas, true)) {
                    $tiposObrigatoriosB[] = $tipoId;
                }
                continue;
            }
            if ($programaRegra === '' && $cotaRegra !== '') {
                $cotasRegra = array_filter(array_map('trim', explode(',', $cotaRegra)));
                if ($cotaAtual !== '' && in_array($cotaAtual, $cotasRegra, true)) {
                    $tiposObrigatoriosB[] = $tipoId;
                }
            }
        }
        $tiposObrigatoriosB = array_values(array_unique($tiposObrigatoriosB));
        if (!empty($errosBolsista)) {
            $falhas[] = [
                'nome' => 'Bolsista',
                'url' => ['action' => 'dadosBolsistaRenovacao', $edital->id, $inscricao->id],
                'erros' => $errosBolsista,
            ];
        }

        $errosSumula = [];
        if (($edital->origem ?? null) === 'N') {
            $sumulasAtivas = $this->fetchTable('EditaisSumulas')->find()
                ->where([
                    'EditaisSumulas.editai_id' => (int)$edital->id,
                    'EditaisSumulas.deleted IS' => null,
                ])
                ->all();
            $sumulasIds = [];
            foreach ($sumulasAtivas as $sumula) {
                $sumulasIds[] = (int)$sumula->id;
            }
            if (!empty($sumulasIds)) {
                $sumulasSalvas = $this->fetchTable('InscricaoSumulas')->find()
                    ->where([
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
                    if (!isset($mapSumulasSalvas[(int)$sumulaId])) {
                        $errosSumula[] = 'Preencha a súmula #' . (int)$sumulaId . '.';
                        continue;
                    }
                    $qtd = $mapSumulasSalvas[(int)$sumulaId]->quantidade;
                    if ($qtd === null || $qtd === '') {
                        $errosSumula[] = 'Informe quantidade na súmula #' . (int)$sumulaId . ' (use 0 quando não possuir).';
                    }
                }
            }
        }
        if (!empty($errosSumula)) {
            $falhas[] = [
                'nome' => 'Súmula',
                'url' => ['action' => 'sumulaRenovacao', $edital->id, $inscricao->id],
                'erros' => $errosSumula,
            ];
        }

        $errosCoorientador = [];
        $orientadorServidor = (int)($inscricao->orientadore->vinculo->servidor ?? 0);
        if ($orientadorServidor !== 1 && empty($inscricao->coorientador)) {
            $errosCoorientador[] = 'Informe o coorientador.';
        }
        if (!empty($errosCoorientador)) {
            $falhas[] = [
                'nome' => 'Coorientador',
                'url' => ['action' => 'coorientadorRenovacao', $edital->id, $inscricao->id],
                'erros' => $errosCoorientador,
            ];
        }

        $errosProjeto = [];
        $projeto = $inscricao->projeto ?? null;
        if (empty($inscricao->projeto_id) || !$projeto) {
            $errosProjeto[] = 'Cadastre o projeto.';
        } else {
            $erroTituloProjeto = $this->validarTextoComLimites((string)($projeto->titulo ?? ''), 'Titulo do projeto', 20, 255, true);
            if ($erroTituloProjeto !== null) {
                $errosProjeto[] = $erroTituloProjeto;
            }
            $erroResumoProjeto = $this->validarTextoComLimites((string)($projeto->resumo ?? ''), 'Resumo do projeto', 20, 4000, true);
            if ($erroResumoProjeto !== null) {
                $errosProjeto[] = $erroResumoProjeto;
            }
            $erroFinanciadores = $this->validarTextoComLimites((string)($projeto->financiamento ?? ''), 'Instituicoes financiadoras', 20, 255);
            if ($erroFinanciadores !== null) {
                $errosProjeto[] = $erroFinanciadores;
            }
            $erroPalavras = $this->validarTextoComLimites((string)($projeto->palavras_chaves ?? ''), 'Palavras-chave', 20, 255);
            if ($erroPalavras !== null) {
                $errosProjeto[] = $erroPalavras;
            }
            if (empty($projeto->area_id)) {
                $errosProjeto[] = 'Informe a Area CNPQ do projeto.';
            }
            if (empty($projeto->linha_id)) {
                $errosProjeto[] = 'Informe a Linha Fiocruz do projeto.';
            }
        }
        if (!empty($errosProjeto)) {
            $falhas[] = [
                'nome' => 'Projeto',
                'url' => ['action' => 'projetoRenovacao', $edital->id, $inscricao->id],
                'erros' => $errosProjeto,
            ];
        }

        $errosSubprojeto = [];
        $erroTituloSubprojeto = $this->validarTextoComLimites((string)($inscricao->sp_titulo ?? ''), 'Titulo do subprojeto', 20, 255, true);
        if ($erroTituloSubprojeto !== null) {
            $errosSubprojeto[] = $erroTituloSubprojeto;
        }
        $erroResumoSubprojeto = $this->validarTextoComLimites((string)($inscricao->sp_resumo ?? ''), 'Resumo do subprojeto', 20, 4000, true);
        if ($erroResumoSubprojeto !== null) {
            $errosSubprojeto[] = $erroResumoSubprojeto;
        }
        $erroResumoRelatorio = $this->validarTextoComLimites((string)($inscricao->resumo_relatorio ?? ''), 'Resumo do relatorio', 20, 4000, true);
        if ($erroResumoRelatorio !== null) {
            $errosSubprojeto[] = $erroResumoRelatorio;
        }
        if (!in_array((string)($inscricao->autorizacao ?? ''), ['0', '1'], true)) {
            $errosSubprojeto[] = 'Informe se autoriza publicacao em revista.';
        }
        if (strtoupper(trim((string)($inscricao->subprojeto_renovacao ?? ''))) === 'D') {
            $erroJustificativa = $this->validarTextoComLimites((string)($inscricao->justificativa_alteracao ?? ''), 'Justificativa da alteracao', 20, 4000, true);
            if ($erroJustificativa !== null) {
                $errosSubprojeto[] = $erroJustificativa;
            }
        }
        if (!empty($errosSubprojeto)) {
            $falhas[] = [
                'nome' => 'Subprojeto',
                'url' => ['action' => 'subprojetoRenovacao', $edital->id, $inscricao->id],
                'erros' => $errosSubprojeto,
            ];
        }

        //==== bloco anexos (mesma logica dos forms)
        $errosAnexosBolsista = [];
        foreach ($tiposObrigatoriosB as $tipoId) {
            if (empty($anexosPorTipo[(int)$tipoId])) {
                $errosAnexosBolsista[] = 'Anexo pendente: ' . ($nomesTiposB[(int)$tipoId] ?? ('anexo #' . (int)$tipoId)) . '.';
            }
        }
        if (!empty($errosAnexosBolsista)) {
            $falhas[] = [
                'nome' => 'Anexos (Bolsista)',
                'url' => ['action' => 'dadosBolsistaRenovacao', $edital->id, $inscricao->id],
                'erros' => $errosAnexosBolsista,
            ];
        }

        if (!empty($inscricao->coorientador)) {
            $tiposC = $this->fetchTable('AnexosTipos')->find()
                ->select(['id', 'nome'])
                ->where([
                    'AnexosTipos.bloco' => 'C',
                    'AnexosTipos.deleted' => 0,
                ])
                ->orderBy(['AnexosTipos.id' => 'ASC'])
                ->all();
            $errosAnexosCoorientador = [];
            foreach ($tiposC as $tipoC) {
                $tipoId = (int)$tipoC->id;
                if (empty($anexosPorTipo[$tipoId])) {
                    $errosAnexosCoorientador[] = 'Anexo pendente: ' . (string)$tipoC->nome . '.';
                }
            }
            if (!empty($errosAnexosCoorientador)) {
                $falhas[] = [
                    'nome' => 'Anexos (Coorientador)',
                    'url' => ['action' => 'coorientadorRenovacao', $edital->id, $inscricao->id],
                    'erros' => $errosAnexosCoorientador,
                ];
            }
        }

        $errosAnexosProjeto = [];
        if (empty($anexosPorTipo[5])) {
            $nomeTipo5 = $this->fetchTable('AnexosTipos')->find()
                ->select(['nome'])
                ->where(['AnexosTipos.id' => 5])
                ->first();
            $errosAnexosProjeto[] = 'Anexo pendente: ' . ($nomeTipo5 ? (string)$nomeTipo5->nome : 'anexo #5') . '.';
        }
        if (!empty($errosAnexosProjeto)) {
            $falhas[] = [
                'nome' => 'Anexos (Projeto)',
                'url' => ['action' => 'projetoRenovacao', $edital->id, $inscricao->id],
                'erros' => $errosAnexosProjeto,
            ];
        }

        $errosAnexosSubprojeto = [];
        if (empty($anexosPorTipo[20])) {
            $errosAnexosSubprojeto[] = 'Anexo pendente: arquivo do subprojeto (tipo #20).';
        }
        if (empty($anexosPorTipo[13])) {
            $errosAnexosSubprojeto[] = 'Anexo pendente: arquivo do relatorio parcial (tipo #13).';
        }
        if (!empty($errosAnexosSubprojeto)) {
            $falhas[] = [
                'nome' => 'Anexos (Subprojeto)',
                'url' => ['action' => 'subprojetoRenovacao', $edital->id, $inscricao->id],
                'erros' => $errosAnexosSubprojeto,
            ];
        }

        if (empty($falhas)) {
            try {
                $projetoBolsistasTable = $this->fetchTable('ProjetoBolsistas');
                $projetoBolsistasTable->getConnection()->transactional(function () use ($projetoBolsistasTable, $inscricao) {
                    $faseOriginal = (int)$inscricao->fase_id;
                    $inscricaoPatch = $projetoBolsistasTable->patchEntity($inscricao, ['fase_id' => 5]);
                    $projetoBolsistasTable->saveOrFail($inscricaoPatch);
                    $this->historico((int)$inscricao->id, $faseOriginal, 5, 'Geracao de termo da inscricao', true);
                });
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - gerar termo da inscricao',
                    'Não foi possível concluir a geração do termo.'
                );
                return $this->redirect(['action' => 'gerarTermoRenovacao', $edital->id, $inscricao->id]);
            }
            return $this->redirect(['action' => 'finalizarRenovacao', $edital->id, $inscricao->id]);
        }

        $this->request->getSession()->write(
            'Inscricoes.gerar_termo_falhas.' . (int)$inscricao->id,
            $falhas
        );
        return $this->redirect(['action' => 'errosGeracaoTermoRenovacao', $edital->id, $inscricao->id]);
    }

    public function validarGeracaoTermoRenovacao($editalId = null, $inscricaoId = null)
    {
        $this->request->allowMethod(['post']);
        return $this->gerarTermoRenovacao($editalId, $inscricaoId);
    }

    public function errosGeracaoTermoRenovacao($editalId = null, $inscricaoId = null)
    {
        $context = $this->loadContext($editalId);
        if (isset($context['redirect'])) {
            return $context['redirect'];
        }
        $edital = $context['edital'];
        $identity = $context['identity'];
        if (empty($inscricaoId)) {
            $this->Flash->error('Inscrição não informada.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $inscricao = $this->fetchTable('ProjetoBolsistas')->find()
            ->where([
                'ProjetoBolsistas.id' => (int)$inscricaoId,
                'ProjetoBolsistas.editai_id' => (int)$edital->id,
            ])
            ->first();
        if (!$inscricao) {
            $this->Flash->error('Inscrição não localizada. Reinicie o processo.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }
        $ehTI = $this->ehTi();
        if ((int)$inscricao->orientador !== (int)$identity->id && !$ehTI) {
            $this->Flash->error('Acesso negado.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }

        $falhas = (array)$this->request->getSession()->read(
            'Inscricoes.gerar_termo_falhas.' . (int)$inscricao->id,
            []
        );
        if (empty($falhas)) {
            $this->Flash->info('Nenhuma falha pendente para esta inscrição.');
            return $this->redirect(['action' => 'gerarTermoRenovacao', $edital->id, $inscricao->id]);
        }

        $linksPorBloco = [
            'Bolsista' => ['controller' => 'Renovacoes', 'action' => 'dadosBolsistaRenovacao', $edital->id, $inscricao->id],
            'Anexos (Bolsista)' => ['controller' => 'Renovacoes', 'action' => 'dadosBolsistaRenovacao', $edital->id, $inscricao->id],
            'Coorientador' => ['controller' => 'Renovacoes', 'action' => 'coorientadorRenovacao', $edital->id, $inscricao->id],
            'Anexos (Coorientador)' => ['controller' => 'Renovacoes', 'action' => 'coorientadorRenovacao', $edital->id, $inscricao->id],
            'Súmula' => ['controller' => 'Renovacoes', 'action' => 'sumulaRenovacao', $edital->id, $inscricao->id],
            'Projeto' => ['controller' => 'Renovacoes', 'action' => 'projetoRenovacao', $edital->id, $inscricao->id],
            'Anexos (Projeto)' => ['controller' => 'Renovacoes', 'action' => 'projetoRenovacao', $edital->id, $inscricao->id],
            'Subprojeto' => ['controller' => 'Renovacoes', 'action' => 'subprojetoRenovacao', $edital->id, $inscricao->id],
            'Anexos (Subprojeto)' => ['controller' => 'Renovacoes', 'action' => 'subprojetoRenovacao', $edital->id, $inscricao->id],
        ];
        foreach ($falhas as &$falha) {
            $nomeBloco = (string)($falha['nome'] ?? '');
            if (isset($linksPorBloco[$nomeBloco])) {
                $falha['url'] = $linksPorBloco[$nomeBloco];
            } else {
                $falha['url'] = ['controller' => 'Renovacoes', 'action' => 'gerarTermoRenovacao', $edital->id, $inscricao->id];
            }
        }
        unset($falha);

        $this->set(compact('inscricao', 'falhas'));
        $this->set($context);
    }

    public function baixarTermoRenovacao($editalId = null, $inscricaoId = null)
    {
        $context = $this->loadContext($editalId);
        if (isset($context['redirect'])) {
            return $context['redirect'];
        }
        $edital = $context['edital'];
        $identity = $context['identity'];
        if (empty($inscricaoId)) {
            $this->Flash->error('Inscrição não informada.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $inscricao = $this->fetchTable('ProjetoBolsistas')->find()
            ->contain(['Bolsistas', 'Orientadores', 'Projetos'])
            ->where([
                'ProjetoBolsistas.id' => (int)$inscricaoId,
                'ProjetoBolsistas.editai_id' => (int)$edital->id,
                'ProjetoBolsistas.deleted IS' => null,
            ])
            ->first();
        if (!$inscricao) {
            $this->Flash->error('Inscrição não localizada.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $ehTI = $this->ehTi();
        if ((int)$inscricao->orientador !== (int)$identity->id && !$ehTI) {
            $this->Flash->error('Acesso negado.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }
        if ((int)$inscricao->fase_id !== 5) {
            $this->Flash->error('O termo só pode ser baixado quando a inscrição estiver na fase 5.');
            return $this->redirect(['action' => 'gerarTermoRenovacao', $edital->id, $inscricao->id]);
        }

        $geracaoTopo = 'Termo gerado em ' . date('d/m/Y H:i:s', strtotime('-3 hours')) . ' por ' . (string)($identity->nome ?? 'Usuario');
        $htmlPdf = $this->montarHtmlTermoInscricao($edital, $inscricao, false, '', $geracaoTopo);
        $htmlExport = $this->montarHtmlTermoInscricao($edital, $inscricao, false, '', $geracaoTopo);

        if (!class_exists(\Mpdf\Mpdf::class)) {
            $this->Flash->error('Biblioteca de PDF não instalada. Baixando termo em HTML.');
            return $this->response
                ->withType('text/html; charset=UTF-8')
                ->withStringBody($htmlExport)
                ->withDownload('termo_inscricao_' . (int)$inscricao->id . '.html');
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
            ]);
            $mpdf->WriteHTML($htmlPdf);
            $pdfBin = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);

            return $this->response
                ->withType('application/pdf')
                ->withStringBody($pdfBin)
                ->withDownload('termo_inscricao_' . (int)$inscricao->id . '.pdf');
        } catch (\Throwable $e) {
            $this->log(
                'Falha gerar PDF mPDF termo inscricao #' . (int)$inscricao->id . ' | erro=' . $e->getMessage(),
                'error'
            );
            $this->Flash->error('Não foi possível gerar o PDF no momento. Baixando termo em HTML.');
            return $this->response
                ->withType('text/html; charset=UTF-8')
                ->withStringBody($htmlExport)
                ->withDownload('termo_inscricao_' . (int)$inscricao->id . '.html');
        }
    }

    public function finalizarRenovacao($editalId = null, $inscricaoId = null)
    {
        $context = $this->loadContext($editalId);
        if (isset($context['redirect'])) {
            return $context['redirect'];
        }
        $edital = $context['edital'];
        $identity = $context['identity'];
        if (empty($inscricaoId)) {
            $this->Flash->error('Inscrição não informada.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $inscricao = $this->fetchTable('ProjetoBolsistas')->find()
            ->contain([
                'Bolsistas',
                'Orientadores',
                'Coorientadores',
                'Projetos',
                'Anexos' => ['conditions' => 'Anexos.deleted IS NULL', 'AnexosTipos'],
            ])
            ->where([
                'ProjetoBolsistas.id' => (int)$inscricaoId,
                'ProjetoBolsistas.editai_id' => (int)$edital->id,
                'ProjetoBolsistas.deleted IS' => null,
            ])
            ->first();
        if (!$inscricao) {
            $this->Flash->error('Inscrição não localizada. Reinicie o processo.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }
        $ehTI = $this->ehTi();
        if ((int)$inscricao->orientador !== (int)$identity->id && !$ehTI) {
            $this->Flash->error('Acesso negado.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }
        if ((int)$inscricao->fase_id !== 5) {
            $this->Flash->error('Inscrição indisponível para finalização nesta fase. Gere o termo antes de finalizar.');
            return $this->redirect(['controller' => 'Renovacoes', 'action' => 'gerarTermoRenovacao', $editalId, $inscricaoId]);
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $arquivoTermo = $this->request->getData('termo_assinado');
            if (!is_object($arquivoTermo) || $arquivoTermo->getClientFilename() === '') {
                $this->Flash->error('Anexe o termo assinado (PDF) para finalizar a inscrição.');
                return $this->redirect(['action' => 'finalizarRenovacao', $edital->id, $inscricao->id]);
            }

            $projetoBolsistasTable = $this->fetchTable('ProjetoBolsistas');
            try {
                $projetoBolsistasTable->getConnection()->transactional(function () use ($projetoBolsistasTable, $inscricao, $arquivoTermo) {
                    $okAnexo = $this->anexarInscricao(
                        [24 => $arquivoTermo],
                        !empty($inscricao->projeto_id) ? (int)$inscricao->projeto_id : null,
                        (int)$inscricao->id,
                        null,
                        true
                    );
                    if (!$okAnexo) {
                        throw new \RuntimeException('Falha ao anexar termo assinado da inscricao.');
                    }

                    $faseOriginal = (int)$inscricao->fase_id;
                    $inscricaoPatch = $projetoBolsistasTable->patchEntity($inscricao, [
                        'fase_id' => 4,
                    ]);
                    $projetoBolsistasTable->saveOrFail($inscricaoPatch);
                    $this->historico((int)$inscricao->id, $faseOriginal, 4, 'Finalizacao da inscricao com termo assinado', true);
                });
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - finalizar inscricao com termo assinado',
                    'Não foi possível finalizar a inscrição.'
                );
                return $this->redirect(['action' => 'finalizarRenovacao', $edital->id, $inscricao->id]);
            }

            $this->Flash->success(
                '<strong>Parabens!</strong> Sua inscricao foi finalizada com sucesso.<br>Boa sorte no processo seletivo!',
                ['escape' => false]
            );
            return $this->redirect(['controller' => 'Padrao', 'action' => 'visualizar', (int)$inscricao->id]);
        }

        $this->set(compact('inscricao'));
        $this->set($context);
    }

    private function tamanhoTexto(string $valor): int
    {
        return function_exists('mb_strlen') ? mb_strlen($valor) : strlen($valor);
    }

    private function validarTextoComLimites(
        ?string $valor,
        string $rotulo,
        int $minimo,
        int $maximo,
        bool $obrigatorio = false
    ): ?string {
        $texto = trim((string)$valor);
        if ($texto === '') {
            return $obrigatorio ? 'Informe ' . strtolower($rotulo) . '.' : null;
        }
        $tamanho = $this->tamanhoTexto($texto);
        if ($tamanho > $maximo) {
            return $rotulo . ' deve ter no maximo ' . $maximo . ' caracteres.';
        }
        return null;
    }

    private function montarHtmlTermoInscricao(
        $edital,
        $inscricao,
        bool $incluirRodapeHtml = false,
        string $textoRodapeUrl = '',
        string $textoRodapeGeracao = ''
    ): string
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
        $instituicaoBolsista = $fmt($bolsista->instituicao_curso ?? '');
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
            $rotuloOrigem = 'Renovacao';
        } elseif ($origemInscricao === 'S') {
            $rotuloOrigem = 'Subst';
        } elseif ($origemInscricao === 'A') {
            $rotuloOrigem = 'Subst na Implantacao';
        }
        $rotuloOrigemFmt = $fmt($rotuloOrigem);
        $nomeEditalCabecalho = $esc($edital->nome ?? 'PIBIC');
        $textoGeracaoCabecalho = trim($textoRodapeGeracao) !== ''
            ? $esc($textoRodapeGeracao)
            : $esc('Termo gerado em ' . date('d/m/Y H:i:s', strtotime('-3 hours')) . ' por Usuario');

        $logoHtml = '<img src="/img/logoNovo.svg" alt="Logo Fomento" style="width:15%;">';

        return '<!doctype html><html><head><meta charset="utf-8"><style>
            @page { size: auto; margin: 50px 0; }
            body{font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.5;color:#111}
            p{margin:0 0 10px 0;text-align:justify}
            .wrap{padding:0 70px 50px}
            .topo-termo{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;margin-bottom:22px}
            .topo-termo .geracao{font-size:11px;text-align:right;max-width:60%;line-height:1.35;color:#222}
            .titulo{text-align:center;margin:14px 0 18px}
            .titulo h1{margin:0 0 8px;font-size:19px;line-height:1.2;font-weight:700}
            .titulo h2{margin:0;font-size:14px;line-height:1.3;font-weight:600}
            .caixa-inscricao{background:#eee;padding:6px 10px;text-align:right;margin-top:12px}
            .assinatura{margin-top:28px}
            ul{margin:6px 0 12px 20px}
            </style></head><body>
            <div class="wrap">
                <div class="topo-termo"><div>' . $logoHtml . '</div><div class="geracao">' . $textoGeracaoCabecalho . '</div></div>
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
                <p>7. O beneficiário e o orientador manifestam sua integral e incondicional concordância com os termos da concessão, comprometendo-se a cumprir fielmente as condições expressas neste instrumento e as normas que lhe são aplicáveis: Resolução Normativa 017/2006 do Programa Institucional de Bolsas de Iniciação Científica.</p>

                <br><br>
                <p>Data: ________________________________________</p>
                <br><br>
                <p class="assinatura">______________________________________________________________________<br>' . $nomeBolsista . ' - CPF ' . $cpfBolsista . '<br>Bolsista</p>
                <br><br>
                <p class="assinatura">________________________________________________________________________<br>' . $nomeOrientador . ' - CPF ' . $cpfOrientador . '<br>Orientador</p>
            </div>
            </body></html>';
    }

}
