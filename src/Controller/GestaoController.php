<?php
namespace App\Controller;

use App\Controller\AppController;
use App\Service\DashboardUserService;
use Cake\Event\EventInterface;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;

class GestaoController extends AppController
{
    protected $identityLogado = null;
    protected $FonteHistoricos;

    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setLayout('admin');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->identityLogado = $this->Authentication->getIdentity();

        if (!$this->ehYoda()) {
            $this->Flash->error('Acesso restrito à gestão.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        return null;
    }

    public function suspender($pbId)
    {
        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $bolsista = $tblProjetoBolsistas->get((int)$pbId);
        $original = $bolsista->fase_id;
        $erros = [];

        if (!$this->ehYoda()) {
            $erros[] = 'Somente a gestão pode suspender';
        }

        if (!in_array((int)$bolsista->fase_id, [11, 18, 19], true)) {
            $erros[] = 'Somente uma bolsa ativa pode ser suspensa';
        }

        if ((int)$bolsista->deleted === 1) {
            $erros[] = 'O registro esta inativado. Não podde ser suspensa';
        }

        if (!empty($erros) && !$this->request->is(['post', 'put', 'patch'])) {
            $this->Flash->error(implode('<br>', $erros), ['escape' => false]);
            return $this->redirect(['controller' => 'Padrao', 'action' => 'visualizar', $bolsista->id]);
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            if (!empty($erros)) {
                $this->Flash->error(implode('<br>', $erros), ['escape' => false]);
                return $this->redirect(['controller' => 'Gestao', 'action' => 'suspender', $bolsista->id]);
            }
            $dados = $this->request->getData();
            $erro = '';

            $tamanho = mb_strlen(trim((string)($dados['justificativa_cancelamento'] ?? '')));
            if ($tamanho < 20) {
                $erro .= '<li>O campo Justificatifa tem menos que 20 caracteres</li>';
            }

            $frase = '';
            if (($dados['licenca'] ?? '') === 'B') {
                $dados['prorrogacao'] = 1;
                $frase = 'Licença Materinadade';
            } else {
                $dados['prorrogacao'] = 0;
                $frase = 'Licença médica';
            }

            if ($erro !== '') {
                $erro .= '<br><big><strong>A SUSPENSÃO NÃO FOI CONCLUÍDA! <br>PREENCHA CORRETAMENTE O FORMULÁRIO</big></strong>';
                $this->Flash->error($erro, ['escape' => false]);
                return $this->redirect(['controller' => 'Gestao', 'action' => 'suspender', $bolsista->id]);
            }

            $dados['fase_id'] = 23;
            $bolsista->fase_id = $dados['fase_id'];

            try {
                $this->historico($bolsista->id, $original, $dados['fase_id'], ($frase . ' - ' . $dados['justificativa_cancelamento']));
                $bolsista = $tblProjetoBolsistas->patchEntity($bolsista, $dados);
                $tblProjetoBolsistas->saveOrFail($bolsista);
                $this->Flash->success('Bolsa Suspensa com sucesso');
            } catch (\Throwable $e) {
                $this->flashFriendlyException(
                    $e,
                    'Erro no Sistema - suspender bolsa',
                    'Houve um erro solicitando a suspensão, por favor, tente novamente'
                );
                return $this->redirect(['controller' => 'Gestao', 'action' => 'suspender', $bolsista->id]);
            }

            return $this->redirect(['controller' => 'Padrao', 'action' => 'visualizar', $bolsista->id]);
        }

        $motivos = [
            'B' => 'Licença Maternidade',
            'M' => 'Licença Médica',
        ];
        $this->set(compact('motivos', 'bolsista', 'erros'));
    }

    public function fonte($pbId)
    {
        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $bolsista = $tblProjetoBolsistas->get((int)$pbId);
        $original = $bolsista->tipo_bolsa;
        $erros = [];

        if (!$this->ehYoda()) {
            $erros[] = 'Somente a gestão pode alterar a fonte pagadora';
        }

        if (!empty($erros) && !$this->request->is(['post', 'put', 'patch'])) {
            $this->Flash->error(implode('<br>', $erros), ['escape' => false]);
            return $this->redirect(['controller' => 'Padrao', 'action' => 'visualizar', $bolsista->id]);
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            if (!empty($erros)) {
                $this->Flash->error(implode('<br>', $erros), ['escape' => false]);
                return $this->redirect(['controller' => 'Gestao', 'action' => 'fonte', $bolsista->id]);
            }

            $dados = $this->request->getData();

            $this->FonteHistoricos = TableRegistry::getTableLocator()->get('FonteHistoricos');
            $novo = $this->FonteHistoricos->newEmptyEntity();
            $novo->projeto_bolsista_id = $bolsista->id;
            $novo->usuario_id = $this->request->getAttribute('identity')->id;
            $novo->fonte_original = $original;
            $novo->fonte_atual = $dados['tipo_bolsa'] ?? null;

            if (!$this->FonteHistoricos->save($novo)) {
                $this->Flash->error('Houve um erro na gravação. Tente novamente');
                return $this->redirect($this->referer());
            }

            $bolsista->tipo_bolsa = $dados['tipo_bolsa'] ?? null;
            $bolsista = $tblProjetoBolsistas->patchEntity($bolsista, $dados);
            if ($tblProjetoBolsistas->save($bolsista)) {
                $this->Flash->success('Alteração realizada com sucesso');
            } else {
                $this->Flash->error('Houve um erro, por favor, tente novamente');
            }

            return $this->redirect(['controller' => 'Padrao', 'action' => 'visualizar', $bolsista->id]);
        }

        $fontes = $this->fonte;
        $this->set(compact('bolsista', 'erros', 'fontes'));
    }

    public function listarconfirmacoes(string $tipo)
    {
        $tipo = strtoupper(trim($tipo));
        if (!in_array($tipo, ['C', 'S'], true)) {
            $this->Flash->error('Tipo de confirmação inválido.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $fases = $tipo === 'C' ? [12, 21] : [15];

        $formatarNome = function (?string $nome): string {
            $nome = trim((string)$nome);
            if ($nome === '') {
                return '-';
            }
            $partes = preg_split('/\s+/', $nome);
            if (!$partes || count($partes) === 1) {
                return $nome;
            }
            return $partes[0] . ' ' . end($partes);
        };

        $registros = [];

        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $icQuery = $tblProjetoBolsistas->find()
            ->contain([
                'Bolsistas',
                'Orientadores' => ['Unidades'],
                'Substitutos' => ['Bolsistas'],
            ])
            ->where([
                'ProjetoBolsistas.fase_id IN' => $fases,
                'ProjetoBolsistas.deleted' => 0,
            ])
            ->orderBy(['ProjetoBolsistas.id' => 'DESC']);

        foreach ($icQuery as $item) {
            $dataSolicitacao = $item->data_cancelamento ?? $item->created ?? $item->modified ?? null;
            $registros[] = [
                'id' => (int)$item->id,
                'fonte' => 'IC',
                'programa_id' => (int)($item->programa_id ?? $item->programa ?? 0),
                'bolsista_entrando' => $formatarNome($item->bolsista_usuario->nome ?? null),
                'bolsista_saindo' => $formatarNome($item->substituto->bolsista_usuario->nome ?? null),
                'orientador' => $formatarNome($item->orientadore->nome ?? null),
                'unidade' => (string)($item->orientadore->unidade->sigla ?? '-'),
                'data_solicitacao' => $dataSolicitacao,
                'cota' => (string)($item->cota ?? ''),
                'programa' => (string)($item->programa ?? ''),
            ];
        }

        $tblPdj = $this->fetchTable('PdjInscricoes');
        $pdjQuery = $tblPdj->find()
            ->contain([
                'Bolsistas',
                'Orientadores' => ['Unidades'],
                'Substitutospdj' => ['Bolsistas'],
            ])
            ->where([
                'PdjInscricoes.fase_id IN' => $fases,
                'PdjInscricoes.deleted IS' => null,
            ])
            ->orderBy(['PdjInscricoes.id' => 'DESC']);

        foreach ($pdjQuery as $item) {
            $dataSolicitacao = $item->data_cancelamento ?? $item->created ?? $item->modified ?? null;
            $registros[] = [
                'id' => (int)$item->id,
                'fonte' => 'PDJ',
                'programa_id' => (int)($item->programa_id ?? $item->programa ?? 0),
                'bolsista_entrando' => $formatarNome($item->bolsista_usuario->nome ?? null),
                'bolsista_saindo' => $formatarNome($item->substitutospdj->bolsista_usuario->nome ?? null),
                'orientador' => $formatarNome($item->orientadore->nome ?? null),
                'unidade' => (string)($item->orientadore->unidade->sigla ?? '-'),
                'data_solicitacao' => $dataSolicitacao,
                'cota' => (string)($item->cota ?? ''),
                'programa' => (string)($item->programa ?? ''),
            ];
        }

        usort($registros, fn($a, $b) => ($b['id'] <=> $a['id']));

        $cotas = $this->cota;
        $this->set(compact('tipo', 'registros', 'cotas'));
    }

    public function listahomologacao()
    {
        $programaId = (int)($this->request->getQuery('programa_id') ?? 0);
        $faseIdParam = $this->request->getQuery('fase_id');
        $faseId = $faseIdParam === null ? 4 : (int)$faseIdParam;
        $agora = FrozenTime::now();

        $fasesFiltro = [4, 6, 7];
        $conditions = [
            'ProjetoBolsistas.deleted' => 0,
            'Editais.inicio_vigencia >' => $agora,
        ];

        if ($faseId > 0) {
            $conditions['ProjetoBolsistas.fase_id'] = $faseId;
        } else {
            $conditions['ProjetoBolsistas.fase_id IN'] = $fasesFiltro;
        }

        if ($programaId > 0) {
            $conditions['Editais.programa_id'] = $programaId;
        }

        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $query = $tblProjetoBolsistas->find()
            ->contain([
                'Editais' => ['Programas'],
                'Bolsistas',
                'Orientadores' => ['Unidades'],
                'Coorientadores',
                'Fases',
            ])
            ->where($conditions)
            ->orderBy(['ProjetoBolsistas.id' => 'DESC']);

        $programas = $this->fetchTable('Programas')->find('list', ['limit' => 200])->toArray();
        $fases = $this->fetchTable('Fases')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])->where(['Fases.id IN' => $fasesFiltro])->toArray();

        if ($this->request->getQuery('acao') === 'excel') {
            $excelQuery = clone $query;
            $excelQuery->limit(null);
            $rows = $excelQuery->all();

            $header = [
                'id',
                'edital',
                'programa',
                'fase',
                'bolsista',
                'orientador',
                'unidade',
                'data_inscricao',
            ];

            $fh = fopen('php://temp', 'r+');
            fputcsv($fh, $header, ';');

            foreach ($rows as $item) {
                $row = [
                    $item->id ?? '',
                    $item->editai->nome ?? '',
                    $item->editai->programa->sigla ?? ($item->editai->programa_id ?? ''),
                    $item->fase->nome ?? '',
                    $item->bolsista_usuario->nome ?? '',
                    $item->orientadore->nome ?? '',
                    $item->orientadore->unidade->sigla ?? '',
                    $item->created ? $item->created->format('d/m/Y H:i:s') : '',
                ];
                fputcsv($fh, $row, ';');
            }

            rewind($fh);
            $csv = stream_get_contents($fh);
            fclose($fh);

            $filename = 'lista_homologacao_' . date('Ymd_His') . '.csv';
            $this->response = $this->response
                ->withType('csv')
                ->withDownload($filename);
            $this->response->getBody()->write($csv);
            return $this->response;
        }

        $inscricoes = $this->paginate($query, ['limit' => 20]);
        $this->set(compact('inscricoes', 'programas', 'fases', 'programaId', 'faseId'));
    }

    public function telahomologacao($id)
    {
        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $inscricao = $tblProjetoBolsistas->find()
            ->contain([
                'Editais' => ['Programas'],
                'Bolsistas',
                'Projetos',
                'Orientadores' => ['Unidades', 'Vinculos'],
                'Coorientadores' => ['Vinculos'],
                'Fases',
            ])
            ->where(['ProjetoBolsistas.id' => (int)$id])
            ->first();

        if (!$inscricao) {
            $this->Flash->error('Inscrição não localizada para homologação.');
            return $this->redirect(['controller' => 'Gestao', 'action' => 'listahomologacao']);
        }
        $faseOriginal = (int)$inscricao->fase_id;
        $fasesPermitidasHomologacao = [4, 6, 7];
        $homologacaoPermitida = in_array($faseOriginal, $fasesPermitidasHomologacao, true);
        $exigeConfirmacaoReavaliacao = in_array($faseOriginal, [6, 7], true);
        $motivoNaoHomologacao = trim((string)($this->request->getData('motivo_nao_homologacao') ?? ''));
        if ($this->request->is(['post', 'put', 'patch'])) {
            if (!$this->ehYoda()) {
                $this->Flash->error('Somente perfil yoda pode homologar ou não homologar inscrições.');
                return $this->redirect(['controller' => 'Gestao', 'action' => 'listahomologacao']);
            }
            if (!$homologacaoPermitida) {
                $this->Flash->error('Somente inscrições nas fases Finalizada, Homologada ou Não homologada podem ser homologadas ou não homologadas.');
                return $this->redirect(['controller' => 'Gestao', 'action' => 'telahomologacao', (int)$inscricao->id]);
            }
            $acaoHomologacao = strtolower(trim((string)($this->request->getData('acao_homologacao') ?? '')));
            $confirmouReavaliacao = (int)$this->request->getData('confirmou_reavaliacao') === 1;
            if ($exigeConfirmacaoReavaliacao && !$confirmouReavaliacao) {
                $this->Flash->error('Esta inscrição já havia sido verificada. Confirme que deseja alterar a avaliação.');
                return $this->redirect(['controller' => 'Gestao', 'action' => 'telahomologacao', (int)$inscricao->id]);
            }
            if ($acaoHomologacao === 'homologar') {
                try {
                    $tblProjetoBolsistas->getConnection()->transactional(function () use ($tblProjetoBolsistas, $inscricao, $faseOriginal) {
                        $inscricao->fase_id = 6;
                        $tblProjetoBolsistas->saveOrFail($inscricao);
                        $this->historico(
                            (int)$inscricao->id,
                            $faseOriginal,
                            6,
                            'Inscrição homologada após verificação dos anexos e dados'
                        );
                    });
                    $this->Flash->success('Inscrição homologada com sucesso.');
                    return $this->redirect(['controller' => 'Gestao', 'action' => 'listahomologacao']);
                } catch (\Throwable $e) {
                    $this->flashFriendlyException(
                        $e,
                        'Erro no Sistema - homologar inscrição',
                        'Não foi possível homologar a inscrição.'
                    );
                }
            } elseif ($acaoHomologacao === 'nao_homologar') {
                if (mb_strlen($motivoNaoHomologacao) < 20) {
                    $this->Flash->error('Informe o motivo da não homologação com pelo menos 20 caracteres.');
                } else {
                    try {
                        $tblProjetoBolsistas->getConnection()->transactional(function () use ($tblProjetoBolsistas, $inscricao, $faseOriginal, $motivoNaoHomologacao) {
                            $inscricao->fase_id = 7;
                            $tblProjetoBolsistas->saveOrFail($inscricao);
                            $this->historico(
                                (int)$inscricao->id,
                                $faseOriginal,
                                7,
                                'Inscrição não homologada devido a: ' . $motivoNaoHomologacao
                            );
                        });
                        $this->Flash->success('Inscrição marcada como não homologada com sucesso.');
                        return $this->redirect(['controller' => 'Gestao', 'action' => 'listahomologacao']);
                    } catch (\Throwable $e) {
                        $this->flashFriendlyException(
                            $e,
                            'Erro no Sistema - não homologar inscrição',
                            'Não foi possível registrar a não homologação da inscrição.'
                        );
                    }
                }
            } else {
                $this->Flash->error('Ação de homologação inválida.');
            }
        }

        $tiposB = $this->fetchTable('AnexosTipos')->find()
            ->select(['id', 'nome', 'condicional', 'programa', 'cota'])
            ->where([
                'AnexosTipos.deleted' => 0,
                'AnexosTipos.bloco' => 'B',
            ])
            ->orderBy(['AnexosTipos.id' => 'ASC'])
            ->all();
        $programasMap = $this->fetchTable('Programas')->find()
            ->select(['id', 'sigla'])
            ->orderBy(['Programas.id' => 'ASC'])
            ->all()
            ->combine('id', 'sigla')
            ->toArray();

        $tiposBIds = [];
        foreach ($tiposB as $tipoB) {
            $tiposBIds[] = (int)$tipoB->id;
        }
        $anexosBMap = [];
        $anexosBAtivos = $this->fetchTable('Anexos')->find()
            ->select(['anexos_tipo_id', 'anexo'])
            ->where([
                'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                'Anexos.anexos_tipo_id IN' => $tiposBIds,
                'OR' => [
                    'Anexos.deleted IS' => null,
                    'Anexos.deleted' => 0,
                ],
            ])
            ->orderBy(['Anexos.id' => 'DESC'])
            ->all();
        foreach ($anexosBAtivos as $anexo) {
            $tipoId = (int)($anexo->anexos_tipo_id ?? 0);
            if ($tipoId <= 0 || (string)($anexo->anexo ?? '') === '') {
                continue;
            }
            if (!isset($anexosBMap[$tipoId])) {
                $anexosBMap[$tipoId] = (string)$anexo->anexo;
            }
        }

        $anexosB = [];
        $programaEdital = (string)($inscricao->editai->programa_id ?? '');
        $cotaAtual = strtoupper(trim((string)($inscricao->cota ?? '')));
        $primeiroPeriodo = $inscricao->primeiro_periodo !== null ? (string)(int)$inscricao->primeiro_periodo : '';
        $bolsistaMenorIdade = false;
        $dataNascimentoBolsista = $inscricao->bolsista_usuario->data_nascimento
            ?? $inscricao->bolsista->data_nascimento
            ?? null;
        if (!empty($dataNascimentoBolsista)) {
            if (is_object($dataNascimentoBolsista) && method_exists($dataNascimentoBolsista, 'i18nFormat')) {
                $dataNascimentoBolsista = $dataNascimentoBolsista->i18nFormat('yyyy-MM-dd');
            }
            $idade = (int)$this->idade((string)$dataNascimentoBolsista, false);
            $bolsistaMenorIdade = $idade < 18;
        }
        foreach ($tiposB as $tipo) {
            $tipoId = (int)$tipo->id;
            $condicional = (int)($tipo->condicional ?? 0);
            $programaRegra = trim((string)($tipo->programa ?? ''));
            $cotaRegra = strtoupper(trim((string)($tipo->cota ?? '')));
            $regras = [];
            $statusRegra = 'Obrigatório';
            $mostrarTipo = false;

            if ($condicional === 0) {
                if ($programaRegra === '' && $cotaRegra === '') {
                    $regras[] = 'Obrigatório';
                    $mostrarTipo = true;
                }

                if ($programaRegra !== '' && $cotaRegra === '') {
                    $programas = array_values(array_filter(array_map('trim', explode(',', $programaRegra))));
                    $siglasProgramas = [];
                    foreach ($programas as $pid) {
                        if (isset($programasMap[$pid])) {
                            $siglasProgramas[] = $programasMap[$pid];
                        }
                    }
                    $listaProgramas = !empty($siglasProgramas) ? implode(', ', $siglasProgramas) : implode(', ', $programas);
                    if ($listaProgramas !== '') {
                        $regras[] = 'Obrigatório nos programas: ' . $listaProgramas . '.';
                    }

                    if ($programaEdital !== '' && in_array($programaEdital, $programas, true)) {
                        $regras[] = 'Obrigatório neste edital.';
                        $mostrarTipo = true;
                    }
                }

                if ($programaRegra === '' && $cotaRegra !== '') {
                    $cotasRegra = array_values(array_filter(array_map('trim', explode(',', $cotaRegra))));
                    $listaCotas = implode(', ', $cotasRegra);
                    if ($listaCotas !== '') {
                        $regras[] = 'Obrigatório nas cotas: ' . $listaCotas . '.';
                    }

                    if ($cotaAtual !== '' && in_array($cotaAtual, $cotasRegra, true)) {
                        $regras[] = 'Obrigatório nesta cota.';
                        $mostrarTipo = true;
                    }
                }
            } else {
                if ($programaRegra === '' && $cotaRegra === '') {
                    if ($tipoId === 16 && $primeiroPeriodo === '0') {
                        $regras[] = 'Condicional: obrigatório para bolsista fora do primeiro período.';
                        $mostrarTipo = true;
                    }
                    if (in_array($tipoId, [18, 19], true) && $bolsistaMenorIdade) {
                        $regras[] = 'Condicional: obrigatório para bolsista menor de idade.';
                        $mostrarTipo = true;
                    }
                }
            }

            if (!$mostrarTipo) {
                continue;
            }

            $anexosB[] = [
                'tipo_id' => $tipoId,
                'tipo_nome' => (string)$tipo->nome,
                'arquivo' => $anexosBMap[$tipoId] ?? '',
                'regras' => $regras,
                'status_regra' => $statusRegra,
            ];
        }
        $tiposI = $this->fetchTable('AnexosTipos')->find()
            ->select(['id', 'nome', 'condicional', 'programa', 'cota'])
            ->where([
                'AnexosTipos.deleted' => 0,
                'AnexosTipos.bloco' => 'I',
            ])
            ->orderBy(['AnexosTipos.id' => 'ASC'])
            ->all();
        $tiposIIds = [];
        foreach ($tiposI as $tipoI) {
            $tiposIIds[] = (int)$tipoI->id;
        }
        $anexosIMap = [];
        if (!empty($tiposIIds)) {
            $anexosIAtivos = $this->fetchTable('Anexos')->find()
                ->select(['anexos_tipo_id', 'anexo'])
                ->where([
                    'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                    'Anexos.anexos_tipo_id IN' => $tiposIIds,
                    'OR' => [
                        'Anexos.deleted IS' => null,
                        'Anexos.deleted' => 0,
                    ],
                ])
                ->orderBy(['Anexos.id' => 'DESC'])
                ->all();
            foreach ($anexosIAtivos as $anexoI) {
                $tipoId = (int)($anexoI->anexos_tipo_id ?? 0);
                if ($tipoId <= 0 || (string)($anexoI->anexo ?? '') === '') {
                    continue;
                }
                if (!isset($anexosIMap[$tipoId])) {
                    $anexosIMap[$tipoId] = (string)$anexoI->anexo;
                }
            }
        }
        $anexosI = [];
        $origemEdital = strtoupper(trim((string)($inscricao->editai->origem ?? '')));
        foreach ($tiposI as $tipoI) {
            $tipoId = (int)$tipoI->id;
            $condicional = (int)($tipoI->condicional ?? 0);
            $programaRegra = trim((string)($tipoI->programa ?? ''));
            $cotaRegra = strtoupper(trim((string)($tipoI->cota ?? '')));
            if (in_array($tipoId, [12, 13], true) && $origemEdital !== 'R') {
                continue;
            }
            if ($condicional !== 0) {
                continue;
            }

            $regras = [];
            $mostrarTipo = false;
            if ($programaRegra === '' && $cotaRegra === '') {
                $regras[] = 'Obrigatório';
                $mostrarTipo = true;
            } elseif ($programaRegra !== '') {
                $programas = array_values(array_filter(array_map('trim', explode(',', $programaRegra))));
                $siglasProgramas = [];
                foreach ($programas as $pid) {
                    if (isset($programasMap[$pid])) {
                        $siglasProgramas[] = $programasMap[$pid];
                    }
                }
                $listaProgramas = !empty($siglasProgramas) ? implode(', ', $siglasProgramas) : implode(', ', $programas);
                if ($listaProgramas !== '') {
                    $regras[] = 'Obrigatório nos programas: ' . $listaProgramas . '.';
                }
                if ($programaEdital !== '' && in_array($programaEdital, $programas, true)) {
                    $regras[] = 'Obrigatório neste edital.';
                    $mostrarTipo = true;
                }
            }

            if (!$mostrarTipo) {
                continue;
            }
            $anexosI[] = [
                'tipo_id' => $tipoId,
                'tipo_nome' => (string)$tipoI->nome,
                'arquivo' => $anexosIMap[$tipoId] ?? '',
                'regras' => $regras,
                'status_regra' => 'Obrigatório',
            ];
        }

        $projetoAtual = $inscricao->projeto ?? null;
        $tiposP = $this->fetchTable('AnexosTipos')->find()
            ->select(['id', 'nome'])
            ->where([
                'AnexosTipos.deleted' => 0,
                'AnexosTipos.bloco' => 'P',
                'AnexosTipos.id' => 5,
            ])
            ->orderBy(['AnexosTipos.id' => 'ASC'])
            ->all();
        $tiposPIds = [];
        foreach ($tiposP as $tipoP) {
            $tiposPIds[] = (int)$tipoP->id;
        }
        $anexosPMap = [];
        if (!empty($inscricao->projeto_id) && !empty($tiposPIds)) {
            $anexosPAtivos = $this->fetchTable('Anexos')->find()
                ->select(['anexos_tipo_id', 'anexo'])
                ->where([
                    'Anexos.projeto_id' => (int)$inscricao->projeto_id,
                    'Anexos.anexos_tipo_id IN' => $tiposPIds,
                    'OR' => [
                        'Anexos.deleted IS' => null,
                        'Anexos.deleted' => 0,
                    ],
                ])
                ->orderBy(['Anexos.id' => 'DESC'])
                ->all();
            foreach ($anexosPAtivos as $anexoProjeto) {
                $tipoId = (int)($anexoProjeto->anexos_tipo_id ?? 0);
                if ($tipoId <= 0 || (string)($anexoProjeto->anexo ?? '') === '') {
                    continue;
                }
                if (!isset($anexosPMap[$tipoId])) {
                    $anexosPMap[$tipoId] = (string)$anexoProjeto->anexo;
                }
            }
        }
        $anexosP = [];
        foreach ($tiposP as $tipoP) {
            $tipoId = (int)$tipoP->id;
            $anexosP[] = [
                'tipo_id' => $tipoId,
                'tipo_nome' => (string)$tipoP->nome,
                'arquivo' => $anexosPMap[$tipoId] ?? '',
                'status_regra' => 'Obrigatório',
            ];
        }
        $subprojetoTitulo = trim((string)($inscricao->sp_titulo ?? ''));
        $tiposS = $this->fetchTable('AnexosTipos')->find()
            ->select(['id', 'nome'])
            ->where([
                'AnexosTipos.deleted' => 0,
                'AnexosTipos.bloco' => 'S',
                'AnexosTipos.id' => 20,
            ])
            ->orderBy(['AnexosTipos.id' => 'ASC'])
            ->all();
        $tiposSIds = [];
        foreach ($tiposS as $tipoS) {
            $tiposSIds[] = (int)$tipoS->id;
        }
        $anexosSMap = [];
        if (!empty($tiposSIds)) {
            $anexosSAtivos = $this->fetchTable('Anexos')->find()
                ->select(['anexos_tipo_id', 'anexo'])
                ->where([
                    'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                    'Anexos.anexos_tipo_id IN' => $tiposSIds,
                    'OR' => [
                        'Anexos.deleted IS' => null,
                        'Anexos.deleted' => 0,
                    ],
                ])
                ->orderBy(['Anexos.id' => 'DESC'])
                ->all();
            foreach ($anexosSAtivos as $anexoSubprojeto) {
                $tipoId = (int)($anexoSubprojeto->anexos_tipo_id ?? 0);
                if ($tipoId <= 0 || (string)($anexoSubprojeto->anexo ?? '') === '') {
                    continue;
                }
                if (!isset($anexosSMap[$tipoId])) {
                    $anexosSMap[$tipoId] = (string)$anexoSubprojeto->anexo;
                }
            }
        }
        $anexosS = [];
        foreach ($tiposS as $tipoS) {
            $tipoId = (int)$tipoS->id;
            $anexosS[] = [
                'tipo_id' => $tipoId,
                'tipo_nome' => (string)$tipoS->nome,
                'arquivo' => $anexosSMap[$tipoId] ?? '',
                'status_regra' => 'Obrigatório',
            ];
        }
        $orientadorNome = trim((string)($inscricao->orientadore->nome ?? ''));
        $orientadorVinculoNome = trim((string)($inscricao->orientadore->vinculo->nome ?? ''));
        $orientadorServidor = (int)($inscricao->orientadore->vinculo->servidor ?? 0);
        $coorientadorNome = trim((string)($inscricao->coorientadore->nome ?? ''));
        $coorientadorVinculoNome = trim((string)($inscricao->coorientadore->vinculo->nome ?? ''));
        $coorientadorServidor = (int)($inscricao->coorientadore->vinculo->servidor ?? 0);
        $coorientadorInformado = !empty($inscricao->coorientadore->id);
        $coorientadorObrigatorioServidor = ($orientadorServidor === 0);
        $errosCoorientador = [];
        if ($coorientadorObrigatorioServidor) {
            if (!$coorientadorInformado) {
                $errosCoorientador[] = 'Obrigatório coorientador servidor.';
            } elseif ($coorientadorServidor !== 1) {
                $errosCoorientador[] = 'Coorientador informado com vínculo não servidor.';
            }
        }
        $tiposC = $this->fetchTable('AnexosTipos')->find()
            ->select(['id', 'nome'])
            ->where([
                'AnexosTipos.deleted' => 0,
                'AnexosTipos.bloco' => 'C',
            ])
            ->orderBy(['AnexosTipos.id' => 'ASC'])
            ->all();
        $tiposCIds = [];
        foreach ($tiposC as $tipoC) {
            $tiposCIds[] = (int)$tipoC->id;
        }
        $anexosC = [];
        if ($coorientadorInformado && !empty($tiposCIds)) {
            $anexosCMap = [];
            $anexosCAtivos = $this->fetchTable('Anexos')->find()
                ->select(['anexos_tipo_id', 'anexo'])
                ->where([
                    'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                    'Anexos.anexos_tipo_id IN' => $tiposCIds,
                    'OR' => [
                        'Anexos.deleted IS' => null,
                        'Anexos.deleted' => 0,
                    ],
                ])
                ->orderBy(['Anexos.id' => 'DESC'])
                ->all();
            foreach ($anexosCAtivos as $anexoC) {
                $tipoId = (int)($anexoC->anexos_tipo_id ?? 0);
                if ($tipoId <= 0 || (string)($anexoC->anexo ?? '') === '') {
                    continue;
                }
                if (!isset($anexosCMap[$tipoId])) {
                    $anexosCMap[$tipoId] = (string)$anexoC->anexo;
                }
            }
            foreach ($tiposC as $tipoC) {
                $tipoId = (int)$tipoC->id;
                $anexosC[] = [
                    'tipo_id' => $tipoId,
                    'tipo_nome' => (string)$tipoC->nome,
                    'arquivo' => $anexosCMap[$tipoId] ?? '',
                    'status_regra' => 'Obrigatório',
                ];
                if (($anexosCMap[$tipoId] ?? '') === '') {
                    $errosCoorientador[] = 'Anexo obrigatório do coorientador pendente: ' . (string)$tipoC->nome . '.';
                }
            }
        }

        $primeiroPeriodoTexto = $inscricao->primeiro_periodo === null
            ? 'Não informado'
            : ((int)$inscricao->primeiro_periodo === 1 ? 'Sim' : 'Não');
        $cotas = $this->cota;

        $this->set(compact(
            'inscricao',
            'anexosB',
            'anexosI',
            'anexosP',
            'anexosS',
            'anexosC',
            'projetoAtual',
            'subprojetoTitulo',
            'primeiroPeriodoTexto',
            'cotas',
            'orientadorNome',
            'orientadorVinculoNome',
            'orientadorServidor',
            'coorientadorNome',
            'coorientadorVinculoNome',
            'coorientadorServidor',
            'coorientadorInformado',
            'coorientadorObrigatorioServidor',
            'errosCoorientador',
            'motivoNaoHomologacao',
            'homologacaoPermitida',
            'exigeConfirmacaoReavaliacao'
        ));
    }

    public function confirmacao($id)
    {
        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $bolsista = $tblProjetoBolsistas->find()
            ->contain([
                'Bolsistas',
                'Orientadores' => ['Unidades'],
                'Substitutos' => ['Bolsistas'],
            ])
            ->where(['ProjetoBolsistas.id' => (int)$id])
            ->first();

        if (!$bolsista) {
            $this->Flash->error('Inscricao nao localizada para confirmação.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $faseAtual = (int)$bolsista->fase_id;
        if ($faseAtual === 15) {
            $modo = 'S';
        } elseif (in_array($faseAtual, [12, 21], true)) {
            $modo = 'C';
        } else {
            $this->Flash->error('Fase invalida para confirmação.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $original = $faseAtual;

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();

            if ($modo === 'S') {
                $bolsistaSaindo = null;
                $faseSaindoOriginal = null;
                if (!empty($bolsista->bolsista_anterior)) {
                    $bolsistaSaindo = $tblProjetoBolsistas->get((int)$bolsista->bolsista_anterior);
                    $faseSaindoOriginal = (int)$bolsistaSaindo->fase_id;
                }

                $dataInicio = trim((string)($dados['data_inicio'] ?? ''));
                if ($dataInicio !== '') {
                    $bolsista->data_inicio = $this->acertaData($dataInicio);
                } else {
                    $bolsista->data_inicio = null;
                }

                $bolsista->fase_id = 11;
                $bolsista->vigente = 1;
                $bolsista->data_primeira = $bolsista->data_inicio;

                if (!empty($dados['tipo_bolsa'])) {
                    $bolsista->tipo_bolsa = $dados['tipo_bolsa'];
                }

                if ($bolsistaSaindo) {
                    if (empty($bolsistaSaindo->data_fim)) {
                        if ((string)($bolsista->origem ?? '') === 'A') {
                            $bolsistaSaindo->data_fim = $bolsistaSaindo->data_inicio;
                        } elseif (!empty($bolsista->data_inicio)) {
                            $bolsistaSaindo->data_fim = date('Y-m-d', strtotime($bolsista->data_inicio . ' -1 day'));
                        } else {
                            $bolsistaSaindo->data_fim = date('Y-m-d');
                        }
                    }

                    $bolsistaSaindo->vigente = 0;
                    $bolsistaSaindo->fase_id = 14;
                    $bolsistaSaindo->substituicao_confirmador = (int)$this->request->getAttribute('identity')['id'];
                    $bolsistaSaindo->data_sub_confirmacao = date('Y-m-d H:i:s');
                }

                try {
                    $tblProjetoBolsistas->getConnection()->transactional(function () use ($tblProjetoBolsistas, $bolsista, $bolsistaSaindo, $original, $faseSaindoOriginal) {
                        $tblProjetoBolsistas->saveOrFail($bolsista);
                        $this->historico($bolsista->id, $original, 11, 'Confirmação da Substituição', true);

                        if ($bolsistaSaindo) {
                            $tblProjetoBolsistas->saveOrFail($bolsistaSaindo);
                            $this->historico($bolsistaSaindo->id, $faseSaindoOriginal, 14, 'Confirmação da Substituição. Bolsa desativada', true);
                        }
                    });

                    $this->Flash->success('Substituição confirmada com sucesso!');
                } catch (\Throwable $e) {
                    $this->flashFriendlyException(
                        $e,
                        'Erro no Sistema - confirmacao de substituicao',
                        'Não foi possível realizar a confirmação, por favor tente novamente!'
                    );
                    return $this->redirect(['controller' => 'Gestao', 'action' => 'confirmacao', $bolsista->id]);
                }

                return $this->redirect(['controller' => 'Gestao', 'action' => 'listarconfirmacoes', 'S']);
            }

            if ($modo === 'C') {
                $novaFase = $faseAtual === 12 ? 13 : 22;
                $dataFim = trim((string)($dados['data_fim'] ?? ''));
                if ($dataFim !== '') {
                    $bolsista->data_fim = $this->acertaData($dataFim);
                } else {
                    $bolsista->data_fim = date('Y-m-d H:i:s');
                }

                $bolsista->fase_id = $novaFase;
                $bolsista->vigente = 0;
                $bolsista->data_cancela_confirmacao = date('Y-m-d H:i:s');
                $bolsista->cancelamento_confirmador = (int)$this->request->getAttribute('identity')['id'];

                if ($tblProjetoBolsistas->save($bolsista)) {
                    $this->Flash->success('Cancelamento confirmado com sucesso');
                    $this->historico($bolsista->id, $original, $novaFase, 'Processo administrativo', true);
                } else {
                    $this->Flash->error('Não foi possível confirmar o cancelamento');
                }

                return $this->redirect(['controller' => 'Gestao', 'action' => 'listarconfirmacoes', 'C']);
            }
        }

        $this->set(compact('bolsista', 'modo'));
    }
}
