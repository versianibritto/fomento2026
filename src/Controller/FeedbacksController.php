<?php
declare(strict_types=1);

namespace App\Controller;

class FeedbacksController extends AppController
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
        $yodaIds = $this->fetchTable('Usuarios')->find()
            ->select(['id'])
            ->where(['yoda' => 1])
            ->enableHydration(false)
            ->all()
            ->extract('id')
            ->map(fn($id) => (int)$id)
            ->toList();
        if (empty($yodaIds)) {
            $yodaIds = [0];
        }

        $feedbacksTable = $this->fetchTable('Feedbacks');
        $query = $feedbacksTable->find()
            ->contain(['Usuarios'])
            ->where(['Feedbacks.parent_id IS' => null])
            ->orderBy(['Feedbacks.created' => 'DESC']);

        if ($isYoda) {
            if ($this->request->is(['post', 'put', 'patch'])) {
                $dados = $this->request->getData();
                if (($dados['tipo'] ?? '') !== '') {
                    $query->where(['Feedbacks.tipo' => (string)$dados['tipo']]);
                }
                if (($dados['destinatario'] ?? '') !== '') {
                    $query->where(['Feedbacks.destinatario' => (string)$dados['destinatario']]);
                }
                if (($dados['situacao'] ?? '') !== '') {
                    $query->where(['Feedbacks.situacao' => (string)$dados['situacao']]);
                }
            }
        } else {
            $userId = (int)($identity['id'] ?? 0);
            $feedbacksDoUsuario = $feedbacksTable->find()
                ->select(['id'])
                ->where(['Feedbacks.usuario_id' => $userId]);
            $query->where(['Feedbacks.usuario_id' => $userId]);
        }

        $feedbacks = $this->paginate($query);

        $novasRespostasPorRamo = [];
        $ramosDaPagina = [];
        foreach ($feedbacks as $item) {
            $ramoItem = (int)($item->ramo ?? $item->id ?? 0);
            if ($ramoItem > 0) {
                $ramosDaPagina[] = $ramoItem;
            }
        }
        $ramosDaPagina = array_values(array_unique($ramosDaPagina));
        if (!empty($ramosDaPagina)) {
            $conditionsNovas = [
                'Feedbacks.parent_id IS NOT' => null,
                'Feedbacks.situacao' => 'N',
                'Feedbacks.ramo IN' => $ramosDaPagina,
            ];
            if ($isYoda) {
                $conditionsNovas['Feedbacks.usuario_id NOT IN'] = $yodaIds;
            } else {
                $conditionsNovas['Feedbacks.usuario_id IN'] = $yodaIds;
            }
            $novasRespostas = $feedbacksTable->find()
                ->select(['ramo'])
                ->where($conditionsNovas)
                ->enableHydration(false)
                ->all();
            foreach ($novasRespostas as $item) {
                $ramo = (int)($item['ramo'] ?? 0);
                if ($ramo <= 0) {
                    continue;
                }
                if (!isset($novasRespostasPorRamo[$ramo])) {
                    $novasRespostasPorRamo[$ramo] = 0;
                }
                $novasRespostasPorRamo[$ramo]++;
            }
        }

        $this->set(compact('feedbacks', 'novasRespostasPorRamo'));
    }

    public function view($id = null)
    {
        $feedbacksTable = $this->fetchTable('Feedbacks');
        $ramoParam = (int)($this->request->getQuery('ramo') ?? 0);
        $rootId = $ramoParam > 0 ? $ramoParam : (int)$id;

        $feedback = $feedbacksTable->find()
            ->contain(['Usuarios'])
            ->where(['Feedbacks.id' => $rootId])
            ->first();

        if (!$feedback) {
            $this->Flash->error('Feedback não localizado.');
            return $this->redirect(['action' => 'index']);
        }

        $ramo = (int)($feedback->ramo ?? $feedback->id);
        $allReplies = $feedbacksTable->find()
            ->contain(['Usuarios'])
            ->where([
                'Feedbacks.ramo' => $ramo,
                'Feedbacks.id !=' => (int)$feedback->id,
            ])
            ->orderBy(['Feedbacks.created' => 'ASC'])
            ->all();

        $identity = $this->request->getAttribute('identity');
        $isYoda = (bool)($identity['yoda'] ?? false);
        $yodaIds = $this->fetchTable('Usuarios')->find()
            ->select(['id'])
            ->where(['yoda' => 1])
            ->enableHydration(false)
            ->all()
            ->extract('id')
            ->map(fn($id) => (int)$id)
            ->toList();
        if (empty($yodaIds)) {
            $yodaIds = [0];
        }
        if ($isYoda || (int)($feedback->usuario_id ?? 0) === (int)($identity['id'] ?? 0)) {
            if ($isYoda) {
                $feedbacksTable->updateAll(
                    ['situacao' => 'R'],
                    [
                        'Feedbacks.ramo' => $ramo,
                        'Feedbacks.origem' => 'R',
                        'Feedbacks.situacao' => 'N',
                        'Feedbacks.usuario_id NOT IN' => $yodaIds,
                    ]
                );
                $feedbacksTable->updateAll(
                    ['situacao' => 'R'],
                    [
                        'Feedbacks.id' => (int)$feedback->id,
                        'Feedbacks.origem' => 'P',
                        'Feedbacks.situacao' => 'N',
                        'Feedbacks.usuario_id NOT IN' => $yodaIds,
                    ]
                );
            } else {
                $feedbacksTable->updateAll(
                    ['situacao' => 'R'],
                    [
                        'Feedbacks.ramo' => $ramo,
                        'Feedbacks.origem' => 'R',
                        'Feedbacks.situacao' => 'N',
                        'Feedbacks.usuario_id IN' => $yodaIds,
                    ]
                );
            }
        }

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

        $this->set(compact('feedback', 'repliesByParent', 'ramo'));
    }

    public function add()
    {
        $identity = $this->request->getAttribute('identity');
        $isYoda = (bool)($identity['yoda'] ?? false);
        if ($isYoda) {
            $this->Flash->error('Apenas usuários podem registrar feedback.');
            return $this->redirect(['action' => 'index']);
        }

        $feedbacksTable = $this->fetchTable('Feedbacks');
        $feedback = $feedbacksTable->newEmptyEntity();

        if ($this->request->is('post')) {
            $dados = $this->request->getData();
            $dados['usuario_id'] = (int)($identity['id'] ?? 0);
            $dados['destinatario'] = 'A';
            $dados['situacao'] = 'N';
            $dados['origem'] = 'P';
            $dados['ativo'] = 1;
            $feedback = $feedbacksTable->patchEntity($feedback, $dados);
            try {
                if ($feedbacksTable->save($feedback)) {
                    $feedbacksTable->updateAll(
                        ['ramo' => (int)$feedback->id],
                        ['id' => (int)$feedback->id]
                    );
                    $this->Flash->success('Feedback registrado com sucesso.');
                    return $this->redirect(['action' => 'index']);
                }
                $this->Flash->error('Não foi possível registrar o feedback. Verifique os campos e tente novamente.');
            } catch (\Throwable $e) {
                $this->flashFriendlyException($e, 'Erro no Sistema - registrar feedback');
            }
        }

        $this->set(compact('feedback'));
    }

    public function responder($id = null)
    {
        $identity = $this->request->getAttribute('identity');

        $feedbacksTable = $this->fetchTable('Feedbacks');
        $feedbackPai = $feedbacksTable->find()
            ->where(['Feedbacks.id' => (int)$id])
            ->first();

        if (!$feedbackPai) {
            $this->Flash->error('Feedback não localizado.');
            return $this->redirect(['action' => 'index']);
        }

        $feedback = $feedbacksTable->newEmptyEntity();
        if ($this->request->is('post')) {
            $dados = $this->request->getData();
            $dados['usuario_id'] = (int)($identity['id'] ?? 0);
            $dados['parent_id'] = (int)$feedbackPai->id;
            $dados['destinatario'] = (string)($feedbackPai->destinatario ?? '');
            $dados['tipo'] = 'P';
            $dados['situacao'] = 'N';
            $dados['origem'] = 'R';
            $dados['ativo'] = 1;
            $dados['ramo'] = $feedbackPai->ramo ?? $feedbackPai->id;

            $feedback = $feedbacksTable->patchEntity($feedback, $dados);
            try {
                if ($feedbacksTable->save($feedback)) {
                    $feedbacksTable->updateAll(
                        ['situacao' => 'R'],
                        ['id' => (int)$feedbackPai->id]
                    );
                    $this->Flash->success('Resposta registrada com sucesso.');
                    $ramoDestino = $feedbackPai->ramo ?? $feedbackPai->id;
                    return $this->redirect([
                        'action' => 'view',
                        $feedbackPai->id,
                        '?' => ['ramo' => (int)$ramoDestino],
                    ]);
                }
                $this->Flash->error('Não foi possível registrar a resposta. Verifique os campos e tente novamente.');
            } catch (\Throwable $e) {
                $this->flashFriendlyException($e, 'Erro no Sistema - registrar resposta de feedback');
            }
        }

        $this->set(compact('feedback'));
    }
}
