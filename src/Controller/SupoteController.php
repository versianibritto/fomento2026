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
            ->contain(['Usuarios', 'SuporteCategorias', 'SuporteStatus'])
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
            $header = [
                'id',
                'data_criacao',
                'usuario',
                'categoria',
                'texto',
                'status',
                'reaberto',
                'data_finalizacao',
                'tempo_solucao',
            ];

            $fh = fopen('php://temp', 'r+');
            fputcsv($fh, $header, ';');

            foreach ($rows as $item) {
                $criadoEm = $item->created;
                $finalizadoEm = $item->finalizado ?? null;
                $tempoSolucao = '';
                if ($criadoEm && $finalizadoEm) {
                    $diffSec = $finalizadoEm->getTimestamp() - $criadoEm->getTimestamp();
                    if ($diffSec >= 0) {
                        $dias = (int)floor($diffSec / 86400);
                        $horas = (int)floor(($diffSec % 86400) / 3600);
                        $minutos = (int)floor(($diffSec % 3600) / 60);
                        $tempoSolucao = sprintf('%dd %02dh %02dm', $dias, $horas, $minutos);
                    }
                }

                $row = [
                    $item->id,
                    $criadoEm ? $criadoEm->format('d/m/Y H:i:s') : '',
                    $item->usuario->nome ?? '',
                    $item->suporte_categoria->nome ?? '',
                    $this->normalizeCsvValue($item->texto ?? ''),
                    $item->suporte_status->nome ?? '',
                    (int)($item->reaberto ?? 0) === 1 ? 'Sim' : 'Nao',
                    $finalizadoEm ? $finalizadoEm->format('d/m/Y H:i:s') : '',
                    $tempoSolucao,
                ];
                fputcsv($fh, $row, ';');
            }

            rewind($fh);
            $csv = stream_get_contents($fh);
            fclose($fh);

            $filename = 'suporte_chamados_' . date('Ymd_His') . '.csv';
            $this->response = $this->response
                ->withType('csv')
                ->withDownload($filename);
            $this->response->getBody()->write($csv);
            return $this->response;
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
        $isYoda = (bool)($identity['yoda'] ?? false);

        $chamadosTable = $this->fetchTable('SuporteChamados');
        $chamado = $chamadosTable->newEmptyEntity();

        $categorias = $this->fetchTable('SuporteCategorias')->find('list')
            ->where(['ativo' => 1])
            ->orderBy(['nome' => 'ASC']);

        $usuarios = $this->fetchTable('Usuarios')->find('list')
            ->orderBy(['nome' => 'ASC']);

        if ($this->request->is('post')) {
            $dados = $this->request->getData();
            $statusId = $this->getStatusId('N');

            $dados['usuario_id'] = (int)($identity['id'] ?? 0);
            $dados['status_id'] = $statusId;
            $dados['origem'] = 'P';
            $dados['reaberto'] = 0;
            $dados['finalizado'] = null;
            $dados['para_outro'] = 0;
            $dados['destinatario_id'] = null;

            if ($isYoda) {
                $dados['categoria_id'] = null;
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

        $this->set(compact('chamado', 'categorias', 'usuarios', 'isYoda'));
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
            $dados['destinatario_id'] = null;
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
                            'destinatario_id' => null,
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
            'destinatario_id' => null,
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

}
