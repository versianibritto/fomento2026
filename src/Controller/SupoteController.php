<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\I18n\FrozenTime;

class SupoteController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setLayout('admin');
    }

    public function index()
    {
        $identity = $this->request->getAttribute('identity');
        $isYoda = (bool)($identity['yoda'] ?? false);
        $isTi = in_array((int)($identity['id'] ?? 0), [1, 8088], true);

        $chamadosTable = $this->fetchTable('SuporteChamados');
        $query = $chamadosTable->find()
            ->contain(['Usuarios', 'Beneficiados', 'Demandantes', 'SuporteCategorias', 'SuporteStatus'])
            ->where(['SuporteChamados.parent_id IS' => null])
            ->orderBy(['SuporteChamados.modified' => 'DESC']);

        $filtros = $this->request->getQueryParams();
        if (!empty($filtros['categoria_id'])) {
            $query->where(['SuporteChamados.categoria_id' => (int)$filtros['categoria_id']]);
        }
        if (!empty($filtros['status_id'])) {
            $query->where(['SuporteChamados.status_id' => (int)$filtros['status_id']]);
        }
        if ($isYoda && !empty($filtros['classificacao_final_id'])) {
            $query->where(['SuporteChamados.classificacao_final_id' => (int)$filtros['classificacao_final_id']]);
        }

        if (!$isYoda) {
            $userId = (int)($identity['id'] ?? 0);
            $query->where(['SuporteChamados.usuario_id' => $userId]);
        }

        if ($isTi && (string)($filtros['export'] ?? '') === '1') {
            $rows = $query->all();
            $calendarioMap = $this->obterCalendarioIndisponibilidades();
            $header = [
                'id',
                'data_criacao',
                'usuario',
                'demandante',
                'beneficiado',
                'demanda_interna',
                'categoria',
                'texto',
                'ultima_resposta',
                'status',
                'reaberto',
                'data_finalizacao',
                'tempo_total_decorrido',
                'detalhe_abertura',
                'aberto_no_expediente',
                'dias_ausencia_periodo',
                'tempo_execucao_util',
            ];

            $ultimasRespostas = $this->obterUltimasRespostasExportacao($rows);
            $exportRows = [];
            foreach ($rows as $item) {
                $criadoEm = $this->normalizarDataSuporteParaNegocio($item->created);
                $finalizadoEm = $this->normalizarDataSuporteParaNegocio($item->finalizado ?? null);
                $ramoId = (int)($item->ramo ?? $item->id);
                $tempoTotalDecorrido = '';
                $tempoExecucaoUtil = '';
                if ($criadoEm && $finalizadoEm) {
                    $diffSec = $finalizadoEm->getTimestamp() - $criadoEm->getTimestamp();
                    if ($diffSec >= 0) {
                        $dias = (int)floor($diffSec / 86400);
                        $horas = (int)floor(($diffSec % 86400) / 3600);
                        $minutos = (int)floor(($diffSec % 3600) / 60);
                        $tempoTotalDecorrido = sprintf('%dd %02dh %02dm', $dias, $horas, $minutos);
                    }
                    $tempoExecucaoUtil = $this->formatarDuracaoHorasUteis($criadoEm, $finalizadoEm, $calendarioMap);
                }

                $exportRows[] = [
                    $item->id,
                    $criadoEm ? (string)$criadoEm->i18nFormat('dd/MM/yyyy HH:mm:ss') : '',
                    $item->usuario->nome ?? '',
                    $item->usuario_demandante->nome ?? '',
                    $item->usuario_beneficiado->nome ?? '',
                    $this->chamadoEhDemandaInterna((int)$item->usuario_id) ? 'Sim' : 'Nao',
                    $item->suporte_categoria->nome ?? '',
                    $this->limparHtmlTextoExportacao($item->texto ?? ''),
                    $ultimasRespostas[$ramoId] ?? '',
                    $item->suporte_status->nome ?? '',
                    (int)($item->reaberto ?? 0) === 1 ? 'Sim' : 'Nao',
                    $finalizadoEm ? (string)$finalizadoEm->i18nFormat('dd/MM/yyyy HH:mm:ss') : '',
                    $tempoTotalDecorrido,
                    $this->getDetalheAbertura($criadoEm, $calendarioMap),
                    $this->abertoNoExpediente($criadoEm, $calendarioMap) ? 'Sim' : 'Nao',
                    $this->getDiasAusenciaPeriodo($criadoEm, $finalizadoEm, $calendarioMap),
                    $tempoExecucaoUtil,
                ];
            }

            return $this->downloadCsvResponse(
                'suporte_chamados_' . date('Ymd_His') . '.csv',
                $header,
                $exportRows
            );
        }

        $chamados = $this->paginate($query, ['limit' => 10]);

        $categorias = $this->fetchTable('SuporteCategorias')->find('list')
            ->where(['ativo' => 1])
            ->orderBy(['nome' => 'ASC']);
        $statusList = $this->fetchTable('SuporteStatus')->find('list')
            ->where(['ativo' => 1])
            ->orderBy(['id' => 'ASC']);
        $classificacoesFinais = $this->fetchTable('SuporteClassificacoesFinais')->find('list')
            ->where(['ativo' => 1])
            ->orderBy(['nome' => 'ASC']);

        $this->set(compact('chamados', 'isYoda', 'isTi', 'categorias', 'statusList', 'classificacoesFinais', 'filtros'));
    }

    public function add()
    {
        $identity = $this->request->getAttribute('identity');
        $isTi = in_array((int)($identity['id'] ?? 0), [1, 8088], true);

        $chamadosTable = $this->fetchTable('SuporteChamados');
        $chamado = $chamadosTable->newEmptyEntity();

        $categorias = $this->fetchTable('SuporteCategorias')->find('list')
            ->where(['ativo' => 1])
            ->orderBy(['nome' => 'ASC']);

        if ($this->request->is('post')) {
            $dados = $this->request->getData();
            $statusId = $this->getStatusId('N');

            $errosValidacao = [];
            if (empty($dados['categoria_id'])) {
                $errosValidacao[] = 'Informe a classificação.';
            }
            if (trim((string)($dados['texto'] ?? '')) === '') {
                $errosValidacao[] = 'Informe a descrição.';
            }

            $dados['usuario_id'] = (int)($identity['id'] ?? 0);
            $dados['status_id'] = $statusId;
            $dados['origem'] = 'P';
            $dados['reaberto'] = 0;
            $dados['finalizado'] = null;
            $dados['para_outro'] = 0;
            if ($isTi) {
                $dados['demandante'] = !empty($dados['demandante']) ? (int)$dados['demandante'] : null;
                $dados['beneficiado'] = !empty($dados['beneficiado']) ? (int)$dados['beneficiado'] : null;
                if ($dados['demandante'] === null) {
                    $errosValidacao[] = 'Informe o demandante.';
                }
                if ($dados['beneficiado'] === null) {
                    $errosValidacao[] = 'Informe o beneficiado.';
                }
            } else {
                $dados['demandante'] = null;
                $dados['beneficiado'] = null;
            }

            if (!empty($errosValidacao)) {
                foreach (array_unique($errosValidacao) as $erro) {
                    $this->Flash->error($erro);
                }
                $chamado = $chamadosTable->patchEntity($chamado, $dados);
                $this->set(compact('chamado', 'categorias', 'isTi'));
                return;
            }

            $dados = $this->handleUpload($dados, 'anexo_1', 'anexos');
            $dados = $this->handleUpload($dados, 'anexo_2', 'anexos');
            $dados = $this->handleUpload($dados, 'anexo_3', 'anexos');

            $chamado = $chamadosTable->patchEntity($chamado, $dados);

            try {
                if ($chamadosTable->save($chamado)) {
                    $chamadosTable->updateAll(
                        ['ramo' => (int)$chamado->id],
                        ['id' => (int)$chamado->id]
                    );
                    $this->registrarHistorico($chamado, null, $statusId);
                    $this->Flash->success('Chamado registrado com sucesso.');
                    return $this->redirect(['action' => 'index']);
                }
                $this->Flash->error('Não foi possível registrar o chamado.');
            } catch (\Throwable $e) {
                $this->flashFriendlyException($e, 'Erro no Sistema - registrar suporte');
            }
        }

        $this->set(compact('chamado', 'categorias', 'isTi'));
    }

    public function buscaUsuarios()
    {
        $this->request->allowMethod(['get']);

        $identity = $this->request->getAttribute('identity');
        $isTi = in_array((int)($identity['id'] ?? 0), [1, 8088], true);
        if (!$isTi) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([]));
        }

        $termo = trim((string)$this->request->getQuery('q', ''));
        if (mb_strlen($termo) < 2 && !ctype_digit($termo)) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([]));
        }

        $usuariosTable = $this->fetchTable('Usuarios');
        $cpf = preg_replace('/\D+/', '', $termo);

        $query = $usuariosTable->find()
            ->select(['id', 'nome', 'cpf'])
            ->limit(15)
            ->orderBy(['Usuarios.nome' => 'ASC']);

        $query->where(function ($exp) use ($termo, $cpf) {
            $or = $exp->or(['Usuarios.nome LIKE' => '%' . $termo . '%']);
            if ($cpf !== '') {
                $or->add(['Usuarios.cpf LIKE' => '%' . $cpf . '%']);
            }
            if (ctype_digit($termo)) {
                $or->add(['Usuarios.id' => (int)$termo]);
            }
            return $or;
        });

        $dados = [];
        foreach ($query->all() as $usuario) {
            $dados[] = [
                'id' => (int)$usuario->id,
                'nome' => (string)($usuario->nome ?? ''),
                'cpf' => (string)($usuario->cpf ?? ''),
                'label' => '#' . (int)$usuario->id . ' - ' . (string)($usuario->nome ?? '') . ' - CPF ' . (string)($usuario->cpf ?? ''),
            ];
        }

        return $this->response
            ->withType('application/json')
            ->withStringBody((string)json_encode($dados, JSON_UNESCAPED_UNICODE));
    }

    public function view($id = null)
    {
        $identity = $this->request->getAttribute('identity');
        $isYoda = (bool)($identity['yoda'] ?? false);
        $ramoParam = (int)($this->request->getQuery('ramo') ?? 0);
        $rootId = $ramoParam > 0 ? $ramoParam : (int)$id;

        $chamadosTable = $this->fetchTable('SuporteChamados');
        $chamado = $chamadosTable->find()
            ->contain(['Usuarios', 'SuporteCategorias', 'SuporteStatus'])
            ->where(['SuporteChamados.id' => $rootId])
            ->first();

        if (!$chamado) {
            $this->Flash->error('Chamado não localizado.');
            return $this->redirect(['action' => 'index']);
        }

        $ramo = (int)($chamado->ramo ?? $chamado->id);
        $allReplies = $chamadosTable->find()
            ->contain(['Usuarios'])
            ->where([
                'SuporteChamados.ramo' => $ramo,
                'SuporteChamados.id !=' => (int)$chamado->id,
            ])
            ->orderBy(['SuporteChamados.created' => 'ASC'])
            ->all();

        $repliesByParent = [];
        foreach ($allReplies as $reply) {
            $parentId = (int)($reply->parent_id ?? 0);
            if ($parentId <= 0) {
                continue;
            }
            if (!isset($repliesByParent[$parentId])) {
                $repliesByParent[$parentId] = [];
            }
            $repliesByParent[$parentId][] = $reply;
        }

        $statusList = $this->fetchTable('SuporteStatus')->find('list', ['keyField' => 'id', 'valueField' => 'nome'])
            ->where(['ativo' => 1])
            ->orderBy(['id' => 'ASC']);
        $classificacoesFinais = $this->fetchTable('SuporteClassificacoesFinais')->find('list')
            ->where(['ativo' => 1])
            ->orderBy(['nome' => 'ASC']);
        $categorias = $this->fetchTable('SuporteCategorias')->find('list')
            ->where(['ativo' => 1])
            ->orderBy(['nome' => 'ASC']);
        $usuarios = $this->fetchTable('Usuarios')->find('list')
            ->orderBy(['nome' => 'ASC']);

        $historico = $this->fetchTable('SuporteStatusHistorico')->find()
            ->contain(['Usuarios', 'StatusAnterior', 'StatusNovo'])
            ->where(['SuporteStatusHistorico.ramo' => $ramo])
            ->orderBy(['SuporteStatusHistorico.created' => 'DESC'])
            ->all();

        $this->set(compact('chamado', 'repliesByParent', 'ramo', 'isYoda', 'statusList', 'classificacoesFinais', 'categorias', 'usuarios', 'historico'));
    }

    public function responder($id = null)
    {
        $identity = $this->request->getAttribute('identity');
        $isTi = in_array((int)($identity['id'] ?? 0), [1, 8088], true);
        $chamadosTable = $this->fetchTable('SuporteChamados');
        $pai = $chamadosTable->find()
            ->where(['SuporteChamados.id' => (int)$id])
            ->first();

        if (!$pai) {
            $this->Flash->error('Chamado não localizado.');
            return $this->redirect(['action' => 'index']);
        }
        if (!$isTi && (int)$pai->status_id !== 3) {
            $this->Flash->error('Este chamado não está disponível para resposta no momento.');
            return $this->redirect(['action' => 'view', $pai->id, '?' => ['ramo' => (int)($pai->ramo ?? $pai->id)]]);
        }

        $reply = $chamadosTable->newEmptyEntity();
        if ($this->request->is('post')) {
            $dados = $this->request->getData();
            $dados['usuario_id'] = (int)($identity['id'] ?? 0);
            $dados['parent_id'] = (int)$pai->id;
            $dados['beneficiado'] = null;
            $dados['demandante'] = null;
            $dados['categoria_id'] = $pai->categoria_id;
            $dados['status_id'] = $pai->status_id;
            $novoStatus = $pai->status_id;
            if (!$isTi && (int)$pai->status_id === 3) {
                $novoStatus = $this->getStatusId('N');
                $dados['status_id'] = $novoStatus;
            }
            $dados['classificacao_final_id'] = $pai->classificacao_final_id;
            $dados['origem'] = 'R';
            $dados['ramo'] = $pai->ramo ?? $pai->id;
            $dados['para_outro'] = 0;
            $dados['reaberto'] = 0;

            $dados = $this->handleUpload($dados, 'anexo_1', 'anexos');
            $dados = $this->handleUpload($dados, 'anexo_2', 'anexos');
            $dados = $this->handleUpload($dados, 'anexo_3', 'anexos');

            $reply = $chamadosTable->patchEntity($reply, $dados);

            try {
                if ($chamadosTable->save($reply)) {
                    if (!$isTi && (int)$pai->status_id === 3 && $novoStatus !== (int)$pai->status_id) {
                        $chamadosTable->updateAll(
                            ['status_id' => $novoStatus],
                            ['id' => (int)$pai->id]
                        );
                        $this->registrarHistorico($pai, (int)$pai->status_id, $novoStatus);
                    }
                    $this->Flash->success('Resposta registrada com sucesso.');
                    return $this->redirect(['action' => 'view', $pai->id, '?' => ['ramo' => (int)($pai->ramo ?? $pai->id)]]);
                }
                $this->Flash->error('Não foi possível registrar a resposta.');
            } catch (\Throwable $e) {
                $this->flashFriendlyException($e, 'Erro no Sistema - responder suporte');
            }
        }

        $this->set(compact('reply', 'pai'));
    }

    public function alterarStatus($id = null)
    {
        $identity = $this->request->getAttribute('identity');
        $isTi = in_array((int)($identity['id'] ?? 0), [1, 8088], true);
        if (!$isTi) {
            $this->Flash->error('Restrito a administradores.');
            return $this->redirect(['action' => 'index']);
        }

        $chamadosTable = $this->fetchTable('SuporteChamados');
        $chamado = $chamadosTable->get((int)$id);
        if (!$chamado) {
            $this->Flash->error('Chamado não localizado.');
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();
            $statusAnterior = $chamado->status_id;
            $novoStatus = (int)($dados['status_id'] ?? 0);
            if ($novoStatus <= 0) {
                $novoStatus = (int)$statusAnterior;
            }
            $mensagem = trim((string)($dados['texto'] ?? ''));
            if (in_array($novoStatus, [3, 5], true) && $mensagem === '') {
                $this->Flash->error('Informe a mensagem para enviar ao cliente.');
                return $this->redirect(['action' => 'view', $chamado->id, '?' => ['ramo' => (int)($chamado->ramo ?? $chamado->id)]]);
            }

            $updateData = [
                'status_id' => $novoStatus,
            ];
            if ($novoStatus === 5) {
                $updateData['classificacao_final_id'] = $dados['classificacao_final_id'] ?? $chamado->classificacao_final_id;
                $updateData['finalizado'] = FrozenTime::now();
            } else {
                $updateData['finalizado'] = null;
            }

            $chamado = $chamadosTable->patchEntity($chamado, $updateData);
            try {
                if ($chamadosTable->save($chamado)) {
                    $this->registrarHistorico($chamado, $statusAnterior, (int)$chamado->status_id);
                    if (in_array($novoStatus, [3, 5], true)) {
                        $ramoId = (int)($chamado->ramo ?? $chamado->id);
                        $ultimo = $chamadosTable->find()
                            ->select(['id'])
                            ->where(['ramo' => $ramoId])
                            ->orderBy(['created' => 'DESC', 'id' => 'DESC'])
                            ->first();
                        $parentId = (int)($ultimo->id ?? $chamado->id);

                        $replyData = [
                            'usuario_id' => (int)($identity['id'] ?? 0),
                            'parent_id' => $parentId,
                            'beneficiado' => null,
                            'demandante' => null,
                            'categoria_id' => $chamado->categoria_id,
                            'status_id' => $novoStatus,
                            'classificacao_final_id' => $chamado->classificacao_final_id,
                            'origem' => 'R',
                            'ramo' => $ramoId,
                            'para_outro' => 0,
                            'reaberto' => 0,
                            'texto' => $mensagem,
                            'anexo_1' => $dados['anexo_1'] ?? null,
                            'anexo_2' => $dados['anexo_2'] ?? null,
                            'anexo_3' => $dados['anexo_3'] ?? null,
                        ];
                        $replyData = $this->handleUpload($replyData, 'anexo_1', 'anexos');
                        $replyData = $this->handleUpload($replyData, 'anexo_2', 'anexos');
                        $replyData = $this->handleUpload($replyData, 'anexo_3', 'anexos');
                        $reply = $chamadosTable->newEmptyEntity();
                        $reply = $chamadosTable->patchEntity($reply, $replyData);
                        $chamadosTable->save($reply);
                    }
                    $this->Flash->success('Status atualizado.');
                } else {
                    $this->Flash->error('Não foi possível atualizar o status.');
                }
            } catch (\Throwable $e) {
                $this->flashFriendlyException($e, 'Erro no Sistema - atualizar status suporte');
            }
        }

        return $this->redirect(['action' => 'view', $chamado->id, '?' => ['ramo' => (int)($chamado->ramo ?? $chamado->id)]]);
    }

    public function reabrir($id = null)
    {
        $identity = $this->request->getAttribute('identity');
        $chamadosTable = $this->fetchTable('SuporteChamados');
        $chamado = $chamadosTable->get((int)$id);
        if (!$chamado) {
            $this->Flash->error('Chamado não localizado.');
            return $this->redirect(['action' => 'index']);
        }

        if (!$this->request->is(['post', 'put', 'patch'])) {
            return $this->redirect(['action' => 'view', $chamado->id, '?' => ['ramo' => (int)($chamado->ramo ?? $chamado->id)]]);
        }
        if ($chamado->finalizado === null) {
            $this->Flash->error('Este chamado já está aberto.');
            return $this->redirect(['action' => 'view', $chamado->id, '?' => ['ramo' => (int)($chamado->ramo ?? $chamado->id)]]);
        }

        $dados = $this->request->getData();
        $statusEmAnalise = $this->getStatusId('N');
        $statusAnterior = $chamado->status_id;

        $chamado = $chamadosTable->patchEntity($chamado, [
            'status_id' => $statusEmAnalise,
            'reaberto' => 1,
            'finalizado' => null,
        ]);

        $ramoId = (int)($chamado->ramo ?? $chamado->id);
        $ultimo = $chamadosTable->find()
            ->select(['id'])
            ->where(['ramo' => $ramoId])
            ->orderBy(['created' => 'DESC', 'id' => 'DESC'])
            ->first();
        $parentId = (int)($ultimo->id ?? $chamado->id);

        $reply = $chamadosTable->newEmptyEntity();
        $replyData = [
            'usuario_id' => (int)($identity['id'] ?? 0),
            'parent_id' => $parentId,
            'beneficiado' => null,
            'demandante' => null,
            'categoria_id' => $chamado->categoria_id,
            'status_id' => $statusEmAnalise,
            'classificacao_final_id' => $chamado->classificacao_final_id,
            'origem' => 'R',
            'ramo' => $ramoId,
            'para_outro' => 0,
            'reaberto' => 1,
            'texto' => $dados['texto'] ?? '',
        ];
        $replyData = $this->handleUpload($replyData, 'anexo_1', 'anexos');
        $replyData = $this->handleUpload($replyData, 'anexo_2', 'anexos');
        $replyData = $this->handleUpload($replyData, 'anexo_3', 'anexos');
        $reply = $chamadosTable->patchEntity($reply, $replyData);

        try {
            if ($chamadosTable->save($chamado) && $chamadosTable->save($reply)) {
                $this->registrarHistorico($chamado, $statusAnterior, $statusEmAnalise);
                $this->Flash->success('Chamado reaberto.');
            } else {
                $this->Flash->error('Não foi possível reabrir o chamado.');
            }
        } catch (\Throwable $e) {
            $this->flashFriendlyException($e, 'Erro no Sistema - reabrir suporte');
        }

        return $this->redirect(['action' => 'view', $chamado->id, '?' => ['ramo' => (int)($chamado->ramo ?? $chamado->id)]]);
    }

    private function getStatusId(string $codigo): int
    {
        $status = $this->fetchTable('SuporteStatus')->find()
            ->select(['id'])
            ->where(['codigo' => $codigo])
            ->first();
        return (int)($status->id ?? 0);
    }

    private function registrarHistorico($chamado, ?int $statusAnterior, int $statusNovo): void
    {
        $identity = $this->request->getAttribute('identity');
        $historicoTable = $this->fetchTable('SuporteStatusHistorico');
        $registro = $historicoTable->newEmptyEntity();
        $registro = $historicoTable->patchEntity($registro, [
            'suporte_id' => (int)$chamado->id,
            'ramo' => (int)($chamado->ramo ?? $chamado->id),
            'usuario_id' => (int)($identity['id'] ?? 0),
            'status_anterior_id' => $statusAnterior,
            'status_novo_id' => $statusNovo,
        ]);
        $historicoTable->save($registro);
    }

    private function chamadoEhDemandaInterna(int $usuarioId): bool
    {
        return in_array($usuarioId, [1, 8088], true);
    }

    private function normalizarDataSuporteParaNegocio($value): ?FrozenTime
    {
        if (!$value) {
            return null;
        }

        $data = $value instanceof FrozenTime
            ? $value
            : ($value instanceof \DateTimeInterface
                ? FrozenTime::instance($value)
                : new FrozenTime((string)$value));

        return $data->addHours(-3);
    }

    private function limparHtmlTextoExportacao(?string $texto): string
    {
        $texto = (string)$texto;
        $texto = html_entity_decode($texto, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $texto = strip_tags($texto);
        $texto = preg_replace('/\s+/u', ' ', str_replace("\xc2\xa0", ' ', $texto));

        return trim((string)$texto);
    }

    private function obterUltimasRespostasExportacao($rows): array
    {
        $ramoIds = [];

        foreach ($rows as $item) {
            $ramoIds[] = (int)($item->ramo ?? $item->id);
        }

        $ramoIds = array_values(array_unique(array_filter($ramoIds)));
        if ($ramoIds === []) {
            return [];
        }

        $respostas = $this->fetchTable('SuporteChamados')->find()
            ->select(['id', 'ramo', 'texto', 'created'])
            ->where([
                'SuporteChamados.ramo IN' => $ramoIds,
                'SuporteChamados.parent_id IS NOT' => null,
            ])
            ->orderBy([
                'SuporteChamados.ramo' => 'ASC',
                'SuporteChamados.created' => 'DESC',
                'SuporteChamados.id' => 'DESC',
            ])
            ->all();

        $map = [];
        foreach ($respostas as $resposta) {
            $ramoId = (int)($resposta->ramo ?? 0);
            if ($ramoId <= 0 || isset($map[$ramoId])) {
                continue;
            }
            $map[$ramoId] = $this->limparHtmlTextoExportacao($resposta->texto ?? '');
        }

        return $map;
    }

    private function formatarDuracaoHorasUteis($inicio, $fim, array $calendarioMap = []): string
    {
        if (!$inicio || !$fim) {
            return '';
        }

        $inicioDt = $inicio instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($inicio) : new \DateTimeImmutable((string)$inicio);
        $fimDt = $fim instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($fim) : new \DateTimeImmutable((string)$fim);

        if ($fimDt <= $inicioDt) {
            return '0h 00m';
        }

        $segundos = $this->calcularSegundosUteis($inicioDt, $fimDt, $calendarioMap);
        $horas = (int)floor($segundos / 3600);
        $minutos = (int)floor(($segundos % 3600) / 60);

        return sprintf('%dh %02dm', $horas, $minutos);
    }

    private function calcularSegundosUteis(\DateTimeImmutable $inicio, \DateTimeImmutable $fim, array $calendarioMap = []): int
    {
        if ($fim <= $inicio) {
            return 0;
        }

        $total = 0;
        $diaAtual = $inicio->setTime(0, 0);
        $ultimoDia = $fim->setTime(0, 0);

        while ($diaAtual <= $ultimoDia) {
            $dataRef = $diaAtual->format('Y-m-d');
            if ($this->ehDiaUtil($diaAtual, $calendarioMap)) {
                $inicioExpediente = $diaAtual->setTime(8, 0, 0);
                $fimExpediente = $diaAtual->setTime(17, 0, 0);

                $inicioJanela = $inicio > $inicioExpediente ? $inicio : $inicioExpediente;
                $fimJanela = $fim < $fimExpediente ? $fim : $fimExpediente;

                if ($fimJanela > $inicioJanela) {
                    $total += $fimJanela->getTimestamp() - $inicioJanela->getTimestamp();
                }
            }

            $diaAtual = $diaAtual->modify('+1 day');
        }

        return $total;
    }

    private function obterCalendarioIndisponibilidades(): array
    {
        $map = [];
        $rows = $this->fetchTable('Calendarios')->find()
            ->where(['Calendarios.deleted IS' => null, 'Calendarios.dia IS NOT' => null])
            ->orderBy(['Calendarios.dia' => 'ASC', 'Calendarios.id' => 'ASC'])
            ->all();

        foreach ($rows as $row) {
            if (empty($row->dia)) {
                continue;
            }
            $dia = $row->dia->format('Y-m-d');
            $descricao = trim((string)($row->descricao ?? ''));
            $tipo = (string)($row->tipo ?? '');
            $rotuloTipo = $this->getCalendarioTipoRotulo($tipo);
            $rotulo = $descricao !== '' ? $descricao : $rotuloTipo;
            if (!isset($map[$dia])) {
                $map[$dia] = [];
            }
            $map[$dia][] = $rotulo;
        }

        return $map;
    }

    private function ehDiaUtil(\DateTimeImmutable $dia, array $calendarioMap = []): bool
    {
        $diaSemana = (int)$dia->format('N');
        if ($diaSemana >= 6) {
            return false;
        }

        return !isset($calendarioMap[$dia->format('Y-m-d')]);
    }

    private function getDetalheAbertura($abertura, array $calendarioMap = []): string
    {
        if (!$abertura) {
            return '';
        }

        $dt = $abertura instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($abertura) : new \DateTimeImmutable((string)$abertura);
        $dataRef = $dt->format('Y-m-d');
        $diaSemana = (int)$dt->format('N');

        if (isset($calendarioMap[$dataRef])) {
            return 'Feriado/Exceções';
        }
        if ($diaSemana === 6 || $diaSemana === 7) {
            return 'Fim de semana';
        }

        return 'Dia normal';
    }

    private function abertoNoExpediente($abertura, array $calendarioMap = []): bool
    {
        if (!$abertura) {
            return false;
        }

        $dt = $abertura instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($abertura) : new \DateTimeImmutable((string)$abertura);
        if (!$this->ehDiaUtil($dt->setTime(0, 0), $calendarioMap)) {
            return false;
        }

        $horaMinuto = $dt->format('H:i:s');
        return $horaMinuto >= '08:00:00' && $horaMinuto <= '17:00:00';
    }

    private function getDiasAusenciaPeriodo($inicio, $fim, array $calendarioMap = []): string
    {
        if (!$inicio || !$fim) {
            return '';
        }

        $inicioDt = $inicio instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($inicio) : new \DateTimeImmutable((string)$inicio);
        $fimDt = $fim instanceof \DateTimeInterface ? \DateTimeImmutable::createFromInterface($fim) : new \DateTimeImmutable((string)$fim);
        if ($fimDt <= $inicioDt) {
            return '';
        }

        $ausencias = [];
        $diaAtual = $inicioDt->setTime(0, 0)->modify('+1 day');
        $ultimoDia = $fimDt->setTime(0, 0);

        while ($diaAtual <= $ultimoDia) {
            $dataRef = $diaAtual->format('Y-m-d');
            $diaSemana = (int)$diaAtual->format('N');

            if (isset($calendarioMap[$dataRef])) {
                foreach ($calendarioMap[$dataRef] as $rotulo) {
                    $ausencias[] = $rotulo;
                }
            } elseif ($diaSemana === 6) {
                $ausencias[] = 'Sabado';
            } elseif ($diaSemana === 7) {
                $ausencias[] = 'Domingo';
            }

            $diaAtual = $diaAtual->modify('+1 day');
        }

        $ausencias = array_values(array_unique($ausencias));
        return implode(', ', $ausencias);
    }

    private function getCalendarioTipoRotulo(string $tipo): string
    {
        return match ($tipo) {
            'F' => 'Feriado',
            'A' => 'Ausência',
            'P' => 'Ponto facultativo',
            'O' => 'Indisponibilidade técnica',
            default => 'Exceção',
        };
    }

}
