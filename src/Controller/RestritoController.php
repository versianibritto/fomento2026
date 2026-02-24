<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\EventInterface;

class RestritoController extends AppController
{
    protected $TiExceptions;
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setLayout('admin');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        if (!$this->ehTi()) {
            $this->Flash->error('Acesso restrito a TI.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }
    }

    public function index()
    {
        $atalhos = [
            [
                'titulo' => 'Usuarios',
                'descricao' => 'Gestao e manutencao de usuarios',
                'url' => ['controller' => 'Users', 'action' => 'index'],
                'class' => 'btn-primary',
                'icon' => 'fas fa-users',
            ],
            [
                'titulo' => 'Cadastrar Usuario',
                'descricao' => 'Cadastro manual de usuario (TI)',
                'url' => ['controller' => 'Users', 'action' => 'cadastrarUsuarioCpf'],
                'class' => 'btn-warning',
                'icon' => 'fas fa-user-plus',
            ],
            [
                'titulo' => 'Erros do Sistema',
                'descricao' => 'Excecoes registradas e suporte',
                'url' => ['controller' => 'Restrito', 'action' => 'erros'],
                'class' => 'btn-danger',
                'icon' => 'fas fa-bug',
            ],
            [
                'titulo' => 'Editais',
                'descricao' => 'Cadastro e gestao de editais',
                'url' => ['controller' => 'Editais', 'action' => 'lista'],
                'class' => 'btn-success',
                'icon' => 'fas fa-bullhorn',
            ],
            [
                'titulo' => 'Modelos em Lote',
                'descricao' => 'Aplicar um mesmo modelo em varios editais',
                'url' => ['controller' => 'Editais', 'action' => 'lista', '#' => 'carga-modelo-lote'],
                'class' => 'btn-primary',
                'icon' => 'fas fa-upload',
            ],
            [
                'titulo' => 'Blocos de Sumula',
                'descricao' => 'Gerencie blocos vinculados aos editais',
                'url' => ['controller' => 'Editais', 'action' => 'sumulasblocos'],
                'class' => 'btn-info',
                'icon' => 'fas fa-layer-group',
            ],
        ];

        $this->set(compact('atalhos'));
    }

    public function erros()
    {
        $this->TiExceptions = \Cake\ORM\TableRegistry::getTableLocator()->get('TiExceptions');

        $status = $this->request->getQuery('status');
        $repeticao = $this->request->getQuery('repeticao');
        $mes = $this->request->getQuery('mes');
        $ano = $this->request->getQuery('ano');
        $usuarioId = $this->request->getQuery('usuario_id');
        $acao = $this->request->getQuery('acao');

        $query = $this->TiExceptions->find()
            ->contain(['TiTipos'])
            ->orderBy(['TiExceptions.id' => 'DESC']);

        if (!empty($status)) {
            if ($status === 'N') {
                $query->where([
                    'OR' => [
                        'status' => 'N',
                        'status IS' => null,
                    ],
                ]);
            } else {
                $query->where(['status' => $status]);
            }
        }

        if ($repeticao !== null && $repeticao !== '') {
            $query->where(['repeticao' => (int)$repeticao]);
        }

        if (!empty($usuarioId)) {
            $query->where(['usuario_id' => (int)$usuarioId]);
        }

        if (!empty($mes) && !empty($ano)) {
            $mesInt = (int)$mes;
            $anoInt = (int)$ano;
            if ($mesInt >= 1 && $mesInt <= 12 && $anoInt > 1900) {
                $start = new \DateTimeImmutable(sprintf('%04d-%02d-01 00:00:00', $anoInt, $mesInt));
                $end = $start->modify('last day of this month')->setTime(23, 59, 59);
                $query->where([
                    'created >=' => $start->format('Y-m-d H:i:s'),
                    'created <=' => $end->format('Y-m-d H:i:s'),
                ]);
            }
        }

        if ($acao === 'excel') {
            $rows = $query->all();
            $header = [
                'id',
                'created',
                'status',
                'classificacao_nome',
                'classificacao_tipo',
                'usuario_id',
                'usuario_nome',
                'usuario_email',
                'usuario_email_alternativo',
                'usuario_email_contato',
                'url',
                'host',
                'mensagem',
                'repeticoes',
                'repeticao',
                'repeticao_de_id',
                'tempo_resposta_min',
                'ultima_ocorrencia',
                'resposta',
                'respondido_por',
                'respondido_em',
            ];

            $fh = fopen('php://temp', 'r+');
            fputcsv($fh, $header, ';');

            foreach ($rows as $erro) {
                $tipo = $erro->ti_tipo;
                $statusLabel = ($erro->status ?? 'N') === 'R' ? 'Respondido' : 'Nova';
                $email = !empty($erro->usuario_email) ? $erro->usuario_email : 'Nao informado';
                $emailAlt = !empty($erro->usuario_email_alternativo) ? $erro->usuario_email_alternativo : 'Nao informado';
                $emailContato = !empty($erro->usuario_email_contato) ? $erro->usuario_email_contato : 'Nao informado';
                $tipoLabel = '';
                if (!empty($tipo->tipo)) {
                    $tipoMap = [
                        'U' => 'Usuario',
                        'S' => 'Sistema',
                        'I' => 'Infra',
                        'O' => 'Outros',
                    ];
                    $tipoLabel = $tipoMap[$tipo->tipo] ?? $tipo->tipo;
                }

                $tempoResposta = '';
                if ($erro->created && $erro->respondido_em) {
                    $diffSec = $erro->respondido_em->getTimestamp() - $erro->created->getTimestamp();
                    if ($diffSec < 0) {
                        $tempoResposta = 'Tratado anteriormente';
                    } elseif ($diffSec < 60) {
                        $tempoResposta = 'Imediato';
                    } else {
                        $tempoResposta = (string)floor($diffSec / 60);
                    }
                }

                $row = [
                    $erro->id,
                    $erro->created ? $erro->created->format('d/m/Y H:i:s') : '',
                    $statusLabel,
                    $tipo->nome ?? '',
                    $tipoLabel,
                    $erro->usuario_id ?? '',
                    $erro->usuario_nome ?? '',
                    $email,
                    $emailAlt,
                    $emailContato,
                    $erro->url ?? '',
                    $erro->host ?? '',
                    $erro->mensagem ?? '',
                    $erro->repeticoes ?? '',
                    !empty($erro->repeticao) ? 'Sim' : 'Nao',
                    $erro->repeticao_de_id ?? '',
                    $tempoResposta,
                    $erro->ultima_ocorrencia ? $erro->ultima_ocorrencia->format('d/m/Y H:i:s') : '',
                    $erro->resposta ?? '',
                    $erro->respondido_por ?? '',
                    $erro->respondido_em ? $erro->respondido_em->format('d/m/Y H:i:s') : '',
                ];
                fputcsv($fh, $row, ';');
            }

            rewind($fh);
            $csv = stream_get_contents($fh);
            fclose($fh);

            $filename = 'ti_exceptions_' . date('Ymd_His') . '.csv';
            $this->response = $this->response
                ->withType('csv')
                ->withDownload($filename);
            $this->response->getBody()->write($csv);
            return $this->response;
        }

        $erros = $this->paginate($query, ['limit' => 15]);
        $this->set(compact('erros', 'status', 'repeticao', 'mes', 'ano', 'usuarioId'));
    }

    public function respondererro($id)
    {
        $this->TiExceptions = \Cake\ORM\TableRegistry::getTableLocator()->get('TiExceptions');
        $erro = $this->TiExceptions->get($id);

        if (!empty($erro->repeticao)) {
            $this->Flash->error('Nao e permitido responder uma repeticao. Responda a ocorrencia principal.');
            return $this->redirect(['action' => 'erros']);
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $dados = $this->request->getData();
            $tipo = trim((string)($dados['tipo'] ?? ''));
            $classificacaoId = trim((string)($dados['classificacao_id'] ?? ''));
            $respostaTexto = trim((string)($dados['resposta'] ?? ''));

            if ($tipo === '' || $classificacaoId === '' || $respostaTexto === '') {
                $this->Flash->error('Tipo, classificacao e mensagem sao obrigatorios.');
                return $this->redirect(['action' => 'respondererro', $id]);
            }

            $tiTiposTable = \Cake\ORM\TableRegistry::getTableLocator()->get('TiTipos');
            $classificacaoNome = $tiTiposTable->find()
                ->select(['nome'])
                ->where(['id' => $classificacaoId])
                ->first()
                ?->nome;
            $classificacaoNome = $classificacaoNome ?: 'Nao informado';

            $nomeUsuario = trim((string)($erro->usuario_nome ?? ''));
            if ($nomeUsuario === '') {
                $nomeUsuario = 'Usuario';
            }

            $dataCriacao = $erro->created ? $erro->created->format('d/m/Y H:i:s') : 'Nao informada';
            $mensagemCompleta = implode("\n", [
                "Prezado(a) sr(a) {$nomeUsuario}",
                "Recebemos a notificacao de um erro na acao {$erro->url} em {$dataCriacao}.",
                "Classificacao do erro: {$classificacaoNome}",
                $respostaTexto,
                "Att,",
                "Antonietta Versiani",
                "Desenvolvedora Plataforma fomento",
                "maria.britto@fiocruz.br.",
            ]);

            $emailCopiaRaw = trim((string)($dados['email_copia'] ?? ''));
            $destinatarios = array_values(array_filter([
                $erro->usuario_email ?? null,
                $erro->usuario_email_alternativo ?? null,
                $erro->usuario_email_contato ?? null,
            ], static function ($email): bool {
                return is_string($email) && trim($email) !== '';
            }));

            $emailsCopia = [];
            if ($emailCopiaRaw !== '') {
                $emailsCopia = preg_split('/[\s,;]+/', $emailCopiaRaw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            }

            $ccPadrao = array_merge([
                'mariaversiani@yahoo.com.br',
                'maria.britto@fiocruz.br',
            ], $emailsCopia);

            $host = $this->request->host();
            $enviarEmail = ($host !== 'fomento2026.local') || \Cake\Core\Configure::read('debug') === false;
            $prefixo = '';
            if ($host === 'fomento2026.local' || $host === 'homolog.pibic.fiocruz.br') {
                $prefixo = 'TESTE EM AMBIENTE DE DESENVOLVIMENTO/HOMOLOGAÇÃO (' . $host . '). FAVOR DESCONSIDERAR ESTE EMAIL.<br><br>';
            }

            $emailEnviado = false;
            if ($enviarEmail && (!empty($destinatarios) || !empty($ccPadrao))) {
                try {
                    $mailer = new \Cake\Mailer\Mailer('default');
                    $mailer->setEmailFormat('html')
                        ->setSubject('Resposta ao erro #' . $erro->id);

                    $toList = array_values(array_unique($destinatarios));
                    $ccList = array_values(array_unique($ccPadrao));

                    if (empty($toList)) {
                        $toList = $ccList;
                        $ccList = [];
                    } else {
                        $ccList = array_values(array_diff($ccList, $toList));
                    }

                    $mailer->setTo($toList);
                    if (!empty($ccList)) {
                        $mailer->setCc($ccList);
                    }

                    $respostaHtml = nl2br(htmlspecialchars($mensagemCompleta, ENT_QUOTES, 'UTF-8'));
                    $mailer->deliver($prefixo . '<p>' . $respostaHtml . '</p>');
                    $emailEnviado = true;
                } catch (\Throwable $mailError) {
                    $this->Flash->error('Nao foi possivel enviar o email. A resposta nao foi registrada.');
                    $emailEnviado = false;
                }
            } else {
                $this->Flash->error('Email nao enviado. A resposta nao foi registrada.');
            }

            if ($emailEnviado) {
                $erro = $this->TiExceptions->patchEntity($erro, [
                    'classificacao_id' => $classificacaoId !== '' ? $classificacaoId : null,
                    'resposta' => $mensagemCompleta !== '' ? $mensagemCompleta : null,
                    'status' => 'R',
                    'respondido_por' => $this->Authentication->getIdentity()->id ?? null,
                    'respondido_em' => \Cake\I18n\FrozenTime::now(),
                ]);

                if ($this->TiExceptions->save($erro)) {
                    $this->TiExceptions->updateAll([
                        'classificacao_id' => $erro->classificacao_id,
                        'resposta' => $erro->resposta,
                        'status' => 'R',
                        'respondido_por' => $erro->respondido_por,
                        'respondido_em' => $erro->respondido_em,
                    ], [
                        'repeticao_de_id' => $erro->id,
                    ]);

                    $this->Flash->success('Resposta registrada com sucesso.');
                    return $this->redirect(['action' => 'erros']);
                }

                $this->Flash->error('Nao foi possivel salvar a resposta.');
            }

            return $this->redirect(['action' => 'respondererro', $id]);
        }

        $tiTiposTable = \Cake\ORM\TableRegistry::getTableLocator()->get('TiTipos');
        $tiposRows = $tiTiposTable->find()
            ->select(['id', 'tipo', 'nome'])
            ->where(['deleted' => 0])
            ->orderBy(['nome' => 'ASC'])
            ->all();

        $tiposMap = [
            'U' => 'Usuario',
            'S' => 'Sistema',
            'I' => 'Infra',
            'O' => 'Outros',
        ];

        $tiposDisponiveis = [];
        $classificacoesOptions = [];
        $classificacoesAttrs = [];
        foreach ($tiposRows as $row) {
            $tiposDisponiveis[$row->tipo] = $tiposMap[$row->tipo] ?? $row->tipo;
            $classificacoesOptions[] = [
                'value' => $row->id,
                'text' => $row->nome,
                'data-tipo' => $row->tipo,
            ];
        }

        $tipoSelecionado = $this->request->getData('tipo');
        if ($tipoSelecionado === null && !empty($erro->classificacao_id)) {
            $tipoSelecionado = $tiTiposTable->find()
                ->select(['tipo'])
                ->where(['id' => $erro->classificacao_id])
                ->first()
                ?->tipo;
        }

        $this->set(compact('erro', 'classificacoesOptions', 'tiposDisponiveis', 'tipoSelecionado'));
    }
}
