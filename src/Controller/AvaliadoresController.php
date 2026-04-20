<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;

class AvaliadoresController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setLayout('admin');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $identity = $this->getIdentityAtual();
        $jedi = '';
        if (is_array($identity)) {
            $jedi = (string)($identity['jedi'] ?? '');
        } elseif ($identity !== null) {
            $jedi = (string)($identity->jedi ?? '');
        }
        if ($jedi === '') {
            $jedi = (string)($this->request->getAttribute('identity')['jedi'] ?? '');
        }

        if (!$this->ehYoda() && $jedi === '') {
            $this->Flash->error('Área restrita à Coordenação da Unidade e à Gestão de Fomento.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }

        return null;
    }

    public function cadastroRaic()
    {
        $this->request->allowMethod(['get', 'post']);

        $editais = $this->fetchTable('Editais')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where([
                'Editais.origem' => 'V',
                'Editais.deleted' => 0,
                'Editais.inicio_avaliar < NOW()',
                'Editais.fim_avaliar > NOW()',
            ])
            ->orderBy(['Editais.nome' => 'ASC'])
            ->toArray();

        $unidades = $this->obterUnidadesDisponiveis();

        $grandesAreas = $this->fetchTable('GrandesAreas')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where(['GrandesAreas.id <' => 10])
            ->orderBy(['GrandesAreas.nome' => 'ASC'])
            ->toArray();

        $dados = [
            'editai_id' => (int)$this->request->getData('editai_id', 0),
            'unidade_id' => (int)$this->request->getData('unidade_id', 0),
            'grandes_area_id' => (int)$this->request->getData('grandes_area_id', 0),
            'area_id' => (int)$this->request->getData('area_id', 0),
            'cpfs' => (string)$this->request->getData('cpfs', ''),
        ];

        $areas = [];
        if (!empty($dados['grandes_area_id'])) {
            $areas = $this->fetchTable('Areas')->find('list', [
                'keyField' => 'id',
                'valueField' => 'nome',
            ])
                ->where(['Areas.grandes_area_id' => $dados['grandes_area_id']])
                ->orderBy(['Areas.nome' => 'ASC'])
                ->toArray();
        }

        $resultado = [
            'processado' => false,
            'confirmado' => false,
            'elegiveis' => [],
            'inelegiveis' => [],
            'totalInformados' => 0,
            'totalElegiveis' => 0,
            'totalInelegiveis' => 0,
        ];

        if ($this->request->is('post')) {
            $acao = (string)$this->request->getData('acao', 'analisar');
            $errosFormulario = $this->validarFormularioCadastroRaic($dados, $editais, $unidades, $areas);

            if ($errosFormulario !== []) {
                $this->Flash->error(implode('<br>', $errosFormulario), ['escape' => false]);
            } else {
                try {
                    $resultado = $this->analisarCadastroRaic($dados);

                    if ($acao === 'confirmar') {
                        if ($resultado['totalElegiveis'] === 0) {
                            $this->Flash->error('Não há avaliadores elegíveis para confirmar o cadastro.');
                        } else {
                            $totalGravados = $this->confirmarCadastroRaic($dados, $resultado['elegiveis']);
                            $resultado['confirmado'] = true;
                            $this->Flash->success($totalGravados . ' avaliador(es) RAIC cadastrado(s) com sucesso.');
                        }
                    } else {
                        if ($resultado['totalElegiveis'] > 0) {
                            $this->Flash->info('Revise a prévia abaixo antes de confirmar o cadastro massivo.');
                        }
                    }

                    if ($resultado['totalInelegiveis'] > 0) {
                        $this->Flash->error(
                            'Foram encontradas inelegibilidades em ' . $resultado['totalInelegiveis'] . ' CPF(s). Revise a listagem abaixo.',
                            ['escape' => false]
                        );
                    }

                    if ($resultado['totalElegiveis'] === 0 && $resultado['totalInelegiveis'] === 0) {
                        $this->Flash->info('Nenhum CPF válido foi informado para processamento.');
                    }
                } catch (\Throwable $e) {
                    $resultado['processado'] = true;
                    $this->Flash->error('Não foi possível concluir o cadastro massivo de avaliadores RAIC.');
                }
            }
        }

        $this->set(compact('editais', 'unidades', 'grandesAreas', 'areas', 'dados', 'resultado'));
    }

    public function cadastroNova()
    {
        $this->request->allowMethod(['get', 'post']);

        if (!$this->ehYoda()) {
            $this->Flash->error('Área restrita à Gestão de Fomento.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }

        $editais = $this->fetchTable('Editais')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where([
                'Editais.origem IN' => ['N', 'R', 'J', 'W'],
                'Editais.deleted' => 0,
                'Editais.inicio_avaliar < NOW()',
                'Editais.fim_avaliar > NOW()',
            ])
            ->orderBy(['Editais.nome' => 'ASC'])
            ->toArray();

        $grandesAreas = $this->fetchTable('GrandesAreas')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where(['GrandesAreas.id <' => 10])
            ->orderBy(['GrandesAreas.nome' => 'ASC'])
            ->toArray();

        $dados = [
            'editais' => $this->normalizarEditaisSelecionados((array)$this->request->getData('editais', [])),
            'grandes_area_id' => (int)$this->request->getData('grandes_area_id', 0),
            'area_id' => (int)$this->request->getData('area_id', 0),
            'cpfs' => (string)$this->request->getData('cpfs', ''),
        ];

        $areas = [];
        if (!empty($dados['grandes_area_id'])) {
            $areas = $this->fetchTable('Areas')->find('list', [
                'keyField' => 'id',
                'valueField' => 'nome',
            ])
                ->where(['Areas.grandes_area_id' => $dados['grandes_area_id']])
                ->orderBy(['Areas.nome' => 'ASC'])
                ->toArray();
        }

        $resultado = [
            'processado' => false,
            'confirmado' => false,
            'elegiveis' => [],
            'inelegiveis' => [],
            'totalInformados' => 0,
            'totalElegiveis' => 0,
            'totalInelegiveis' => 0,
        ];

        if ($this->request->is('post')) {
            $acao = (string)$this->request->getData('acao', 'analisar');
            $errosFormulario = $this->validarFormularioCadastroNova($dados, $editais, $areas);

            if ($errosFormulario !== []) {
                $this->Flash->error(implode('<br>', $errosFormulario), ['escape' => false]);
            } else {
                try {
                    $resultado = $this->analisarCadastroNova($dados);

                    if ($acao === 'confirmar') {
                        if ($resultado['totalElegiveis'] === 0) {
                            $this->Flash->error('Não há avaliadores elegíveis para confirmar o cadastro.');
                        } else {
                            $totalGravados = $this->confirmarCadastroNova($dados, $resultado['elegiveis']);
                            $resultado['confirmado'] = true;
                            $this->Flash->success($totalGravados . ' vínculo(s) de avaliador(es) gravado(s) com sucesso.');
                        }
                    } else {
                        if ($resultado['totalElegiveis'] > 0) {
                            $this->Flash->info('Revise a prévia abaixo antes de confirmar o cadastro massivo.');
                        }
                    }

                    if ($resultado['totalInelegiveis'] > 0) {
                        $this->Flash->error(
                            'Foram encontradas inelegibilidades em ' . $resultado['totalInelegiveis'] . ' combinação(ões) CPF/edital. Revise a listagem abaixo.',
                            ['escape' => false]
                        );
                    }

                    if ($resultado['totalElegiveis'] === 0 && $resultado['totalInelegiveis'] === 0) {
                        $this->Flash->info('Nenhum CPF válido foi informado para processamento.');
                    }
                } catch (\Throwable $e) {
                    $resultado['processado'] = true;
                    $this->Flash->error('Não foi possível concluir o cadastro massivo de avaliadores para editais.');
                }
            }
        }

        $this->set(compact('editais', 'grandesAreas', 'areas', 'dados', 'resultado'));
    }

    public function buscaAreas()
    {
        $this->request->allowMethod(['post', 'ajax']);
        $this->autoRender = false;

        $grandeAreaId = (int)$this->request->getData('id');

        $areas = $this->fetchTable('Areas')->find()
            ->select(['id', 'nome'])
            ->where(['Areas.grandes_area_id' => $grandeAreaId])
            ->orderBy(['Areas.nome' => 'ASC'])
            ->all()
            ->map(function ($area): array {
                return [
                    'id' => (int)$area->id,
                    'nome' => (string)$area->nome,
                ];
            })
            ->toList();

        return $this->response
            ->withType('application/json')
            ->withStringBody((string)json_encode($areas, JSON_UNESCAPED_UNICODE));
    }

    public function buscaAvaliadoresInscricao($id = null)
    {
        $this->request->allowMethod(['post', 'ajax']);
        $this->autoRender = false;

        if (!$this->ehYoda()) {
            return $this->response
                ->withStatus(403)
                ->withType('application/json')
                ->withStringBody((string)json_encode([
                    'erro' => 'Acesso negado.',
                ], JSON_UNESCAPED_UNICODE));
        }

        $inscricao = $this->fetchTable('ProjetoBolsistas')->find()
            ->select(['id', 'editai_id', 'orientador', 'coorientador', 'bolsista'])
            ->where(['ProjetoBolsistas.id' => (int)$id])
            ->first();

        if (!$inscricao) {
            return $this->response
                ->withStatus(404)
                ->withType('application/json')
                ->withStringBody((string)json_encode([
                    'erro' => 'Inscrição não localizada.',
                ], JSON_UNESCAPED_UNICODE));
        }

        $grandeAreaId = (int)$this->request->getData('grandes_area_id', 0);
        $areaId = (int)$this->request->getData('area_id', 0);

        $avaliadores = $this->obterOpcoesAvaliadoresInscricao(
            (int)($inscricao->editai_id ?? 0),
            $this->obterUsuariosExcluidosInscricao($inscricao),
            $grandeAreaId,
            $areaId
        );

        $payload = [];
        foreach ($avaliadores as $avaliadorId => $descricao) {
            $payload[] = [
                'id' => (int)$avaliadorId,
                'nome' => (string)$descricao,
            ];
        }

        return $this->response
            ->withType('application/json')
            ->withStringBody((string)json_encode($payload, JSON_UNESCAPED_UNICODE));
    }

    public function listaRaic()
    {
        $this->request->allowMethod(['get']);

        $unidades = $this->obterUnidadesDisponiveis();
        $filtros = $this->obterFiltrosListaRaic($unidades);
        $anosOptions = $this->obterAnosListaRaic();
        if (!isset($anosOptions[$filtros['ano']])) {
            $filtros['ano'] = (int)array_key_first($anosOptions);
        }

        $query = $this->montarQueryListaRaic($filtros);

        if ((int)$this->request->getQuery('exportar', 0) === 1) {
            return $this->exportarListaRaicCsv($query, $filtros);
        }

        $avaliadores = $this->paginate($query, ['limit' => 20]);

        $this->set(compact('avaliadores', 'anosOptions', 'unidades', 'filtros'));
    }

    public function listaNova()
    {
        $this->request->allowMethod(['get']);

        if (!$this->ehYoda()) {
            $this->Flash->error('Área restrita à Gestão de Fomento.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }

        $filtros = $this->obterFiltrosListaNova();
        $anosOptions = $this->obterAnosListaPorTipo('N');
        if (!isset($anosOptions[$filtros['ano']])) {
            $filtros['ano'] = (int)array_key_first($anosOptions);
        }

        $editais = $this->obterEditaisListaNova();
        if ($filtros['editai_id'] !== 0 && !isset($editais[$filtros['editai_id']])) {
            $filtros['editai_id'] = 0;
        }

        $grandesAreas = $this->obterGrandesAreasRestritas();
        if ($filtros['grandes_area_id'] !== 0 && !isset($grandesAreas[$filtros['grandes_area_id']])) {
            $filtros['grandes_area_id'] = 0;
        }

        $areas = $this->obterAreasPorGrandeArea((int)$filtros['grandes_area_id']);
        if ($filtros['area_id'] !== 0 && !isset($areas[$filtros['area_id']])) {
            $filtros['area_id'] = 0;
        }

        $query = $this->montarQueryListaNova($filtros);

        if ((int)$this->request->getQuery('exportar', 0) === 1) {
            return $this->exportarListaNovaCsv($query);
        }

        $avaliadores = $this->paginate($query, ['limit' => 20]);

        $this->set(compact('avaliadores', 'anosOptions', 'editais', 'grandesAreas', 'areas', 'filtros'));
    }

    public function listaInscricoes()
    {
        $this->request->allowMethod(['get']);

        if (!$this->ehYoda()) {
            $this->Flash->error('Área restrita à Gestão de Fomento.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }

        $filtros = $this->obterFiltrosListaInscricoes();
        $editais = $this->obterEditaisAbertosInscricoes();
        if ($filtros['editai_id'] !== 0 && !isset($editais[$filtros['editai_id']])) {
            $filtros['editai_id'] = 0;
        }

        $grandesAreas = $this->obterGrandesAreasRestritas();
        if ($filtros['grandes_area_id'] !== 0 && !isset($grandesAreas[$filtros['grandes_area_id']])) {
            $filtros['grandes_area_id'] = 0;
        }

        $areas = $this->obterAreasPorGrandeArea((int)$filtros['grandes_area_id']);
        if ($filtros['area_id'] !== 0 && !isset($areas[$filtros['area_id']])) {
            $filtros['area_id'] = 0;
        }

        $statusOptions = [
            'vinculado' => 'Vinculado (2 avaliadores)',
            'nao_vinculado' => 'Não vinculado (0 avaliadores)',
        ];
        if ($filtros['status_vinculo'] !== '' && !isset($statusOptions[$filtros['status_vinculo']])) {
            $filtros['status_vinculo'] = '';
        }

        $query = $this->montarQueryListaInscricoes($filtros);
        $inscricoes = $this->paginate($query, ['limit' => 12]);

        $this->set(compact('inscricoes', 'editais', 'grandesAreas', 'areas', 'filtros', 'statusOptions'));
    }

    public function vincularInscricao($id = null)
    {
        $this->request->allowMethod(['get', 'post', 'put', 'patch']);

        if (!$this->ehYoda()) {
            $this->Flash->error('Área restrita à Gestão de Fomento.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }

        $projetoBolsistasTable = $this->fetchTable('ProjetoBolsistas');
        $avaliadorBolsistasTable = $this->fetchTable('AvaliadorBolsistas');
        $avaliadorsTable = $this->fetchTable('Avaliadors');

        $inscricao = $projetoBolsistasTable->get((int)$id, [
            'contain' => [
                'Editais',
                'Bolsistas' => ['Unidades'],
                'Orientadores' => ['Unidades', 'Vinculos'],
                'Coorientadores' => ['Unidades', 'Vinculos'],
                'Projetos' => ['Areas' => ['GrandesAreas']],
            ],
        ]);

        $editalAberto = !empty($inscricao->editai)
            && empty($inscricao->editai->deleted)
            && in_array((string)($inscricao->editai->origem ?? ''), ['N', 'R'], true)
            && !empty($inscricao->editai->inicio_avaliar)
            && !empty($inscricao->editai->fim_avaliar)
            && $inscricao->editai->inicio_avaliar <= new \Cake\I18n\FrozenTime()
            && $inscricao->editai->fim_avaliar >= new \Cake\I18n\FrozenTime();

        if (!$editalAberto) {
            $this->Flash->error('O edital desta inscrição não está com avaliação aberta para vinculação de avaliadores.');
            return $this->redirect(['action' => 'listaInscricoes']);
        }

        $vinculosAtivos = $avaliadorBolsistasTable->find()
            ->contain([
                'Avaliadors' => ['Usuarios', 'GrandesAreas', 'Areas'],
            ])
            ->where([
                'AvaliadorBolsistas.deleted' => 0,
                'AvaliadorBolsistas.tipo' => 'N',
            ])
            ->andWhere(function ($exp) use ($id) {
                return $exp->or([
                    'AvaliadorBolsistas.projeto_bolsista_id' => (int)$id,
                    'AvaliadorBolsistas.bolsista' => (int)$id,
                ]);
            })
            ->orderBy(['AvaliadorBolsistas.ordem' => 'ASC'])
            ->all()
            ->toList();

        $avaliador1Atual = null;
        $avaliador2Atual = null;
        foreach ($vinculosAtivos as $vinculoAtivo) {
            if ((int)($vinculoAtivo->ordem ?? 0) === 1) {
                $avaliador1Atual = (int)($vinculoAtivo->avaliador_id ?? 0);
            }
            if ((int)($vinculoAtivo->ordem ?? 0) === 2) {
                $avaliador2Atual = (int)($vinculoAtivo->avaliador_id ?? 0);
            }
        }

        $fora = $this->obterUsuariosExcluidosInscricao($inscricao);

        $projetoAreaId = (int)($inscricao->projeto->area_id ?? 0);
        $projetoGrandeAreaId = (int)($inscricao->projeto->area->grandes_area_id ?? 0);
        $grandesAreas = $this->obterGrandesAreasRestritas();

        $filtrosAvaliador1 = [
            'grandes_area_id' => $projetoGrandeAreaId,
            'area_id' => $projetoAreaId,
        ];
        $filtrosAvaliador2 = [
            'grandes_area_id' => $projetoGrandeAreaId,
            'area_id' => $projetoAreaId,
        ];

        foreach ($vinculosAtivos as $vinculoAtivo) {
            $ordem = (int)($vinculoAtivo->ordem ?? 0);
            if (!in_array($ordem, [1, 2], true)) {
                continue;
            }

            $filtrosAtual = [
                'grandes_area_id' => (int)($vinculoAtivo->avaliador->grandes_area_id ?? $projetoGrandeAreaId),
                'area_id' => (int)($vinculoAtivo->avaliador->area_id ?? $projetoAreaId),
            ];

            if ($ordem === 1) {
                $filtrosAvaliador1 = $filtrosAtual;
                continue;
            }

            $filtrosAvaliador2 = $filtrosAtual;
        }

        if (!isset($grandesAreas[$filtrosAvaliador1['grandes_area_id']])) {
            $filtrosAvaliador1['grandes_area_id'] = 0;
            $filtrosAvaliador1['area_id'] = 0;
        }
        if (!isset($grandesAreas[$filtrosAvaliador2['grandes_area_id']])) {
            $filtrosAvaliador2['grandes_area_id'] = 0;
            $filtrosAvaliador2['area_id'] = 0;
        }

        $areasAvaliador1 = $this->obterAreasPorGrandeArea((int)$filtrosAvaliador1['grandes_area_id']);
        $areasAvaliador2 = $this->obterAreasPorGrandeArea((int)$filtrosAvaliador2['grandes_area_id']);

        if ($filtrosAvaliador1['area_id'] !== 0 && !isset($areasAvaliador1[$filtrosAvaliador1['area_id']])) {
            $filtrosAvaliador1['area_id'] = 0;
        }
        if ($filtrosAvaliador2['area_id'] !== 0 && !isset($areasAvaliador2[$filtrosAvaliador2['area_id']])) {
            $filtrosAvaliador2['area_id'] = 0;
        }

        $avaliadoresAvaliador1 = $this->obterOpcoesAvaliadoresInscricao(
            (int)($inscricao->editai_id ?? 0),
            $fora,
            (int)$filtrosAvaliador1['grandes_area_id'],
            (int)$filtrosAvaliador1['area_id']
        );
        $avaliadoresAvaliador2 = $this->obterOpcoesAvaliadoresInscricao(
            (int)($inscricao->editai_id ?? 0),
            $fora,
            (int)$filtrosAvaliador2['grandes_area_id'],
            (int)$filtrosAvaliador2['area_id']
        );

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();
            $avaliador1 = (int)($dados['avaliador_1'] ?? 0);
            $avaliador2 = (int)($dados['avaliador_2'] ?? 0);

            if ($avaliador1 <= 0 || $avaliador2 <= 0) {
                $this->Flash->error('Selecione os dois avaliadores.');
                return $this->redirect(['action' => 'vincularInscricao', $inscricao->id]);
            }

            if ($avaliador1 === $avaliador2) {
                $this->Flash->error('Não pode haver repetição entre os avaliadores.');
                return $this->redirect(['action' => 'vincularInscricao', $inscricao->id]);
            }

            if (in_array((int)($inscricao->orientador ?? 0), [$avaliador1, $avaliador2], true)) {
                $this->Flash->error('O orientador não pode ser vinculado como avaliador.');
                return $this->redirect(['action' => 'vincularInscricao', $inscricao->id]);
            }

            if (!empty($inscricao->coorientador) && in_array((int)$inscricao->coorientador, [$avaliador1, $avaliador2], true)) {
                $this->Flash->error('O coorientador não pode ser vinculado como avaliador.');
                return $this->redirect(['action' => 'vincularInscricao', $inscricao->id]);
            }

            if (!empty($inscricao->bolsista) && in_array((int)$inscricao->bolsista, [$avaliador1, $avaliador2], true)) {
                $this->Flash->error('O bolsista não pode ser vinculado como avaliador.');
                return $this->redirect(['action' => 'vincularInscricao', $inscricao->id]);
            }

            $avaliadoresSelecionados = $avaliadorsTable->find()
                ->select(['Avaliadors.id', 'Avaliadors.usuario_id', 'Usuarios.nome'])
                ->leftJoinWith('Usuarios')
                ->where([
                    'Avaliadors.id IN' => [$avaliador1, $avaliador2],
                    'Avaliadors.deleted' => 0,
                ])
                ->enableHydration(false)
                ->all()
                ->indexBy('id')
                ->toArray();

            if (count($avaliadoresSelecionados) !== 2) {
                $this->Flash->error('Um ou mais avaliadores selecionados não estão mais disponíveis.');
                return $this->redirect(['action' => 'vincularInscricao', $inscricao->id]);
            }

            $normalizarNome = static function (?string $nome): string {
                $nome = trim((string)$nome);
                if ($nome === '') {
                    return '';
                }

                $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nome);
                if ($ascii !== false) {
                    $nome = $ascii;
                }

                $nome = preg_replace('/[^a-zA-Z0-9\s]/', '', $nome) ?? $nome;
                $nome = preg_replace('/\s+/', ' ', $nome) ?? $nome;

                return mb_strtolower(trim($nome));
            };

            $nomesBloqueados = array_values(array_filter(array_unique([
                $normalizarNome((string)($inscricao->orientadore->nome ?? '')),
                $normalizarNome((string)($inscricao->coorientadore->nome ?? '')),
                $normalizarNome((string)($inscricao->bolsista_usuario->nome ?? '')),
            ])));

            foreach ([$avaliador1, $avaliador2] as $avaliadorIdSelecionado) {
                $nomeAvaliador = $normalizarNome((string)($avaliadoresSelecionados[$avaliadorIdSelecionado]['Usuarios__nome'] ?? ''));
                if ($nomeAvaliador !== '' && in_array($nomeAvaliador, $nomesBloqueados, true)) {
                    $this->Flash->error('Não é permitido vincular avaliador com o mesmo nome do orientador, do coorientador ou do bolsista.');
                    return $this->redirect(['action' => 'vincularInscricao', $inscricao->id]);
                }
            }

            try {
                $avaliadorBolsistasTable->getConnection()->transactional(function () use (
                    $avaliadorBolsistasTable,
                    $inscricao,
                    $avaliador1,
                    $avaliador2,
                    $avaliadoresSelecionados,
                    $id
                ): void {
                    $avaliadorBolsistasTable->updateAll(
                        ['deleted' => 1],
                        [
                            'tipo' => 'N',
                            'deleted' => 0,
                            'OR' => [
                                ['projeto_bolsista_id' => (int)$id],
                                ['bolsista' => (int)$id],
                            ],
                        ]
                    );

                    $dadosVinculo = [
                        ['avaliador_id' => $avaliador1, 'ordem' => 1],
                        ['avaliador_id' => $avaliador2, 'ordem' => 2],
                    ];

                    foreach ($dadosVinculo as $item) {
                        $novo = $avaliadorBolsistasTable->newEmptyEntity();
                        $novo->bolsista = (int)$inscricao->id;
                        $novo->projeto_bolsista_id = (int)$inscricao->id;
                        $novo->tipo = 'N';
                        $novo->situacao = 'E';
                        $novo->editai_id = (int)$inscricao->editai_id;
                        $novo->ano = (string)date('Y');
                        $novo->coordenador = 0;
                        $novo->avaliador_id = (int)$item['avaliador_id'];
                        $novo->usuario_id = (int)($avaliadoresSelecionados[(int)$item['avaliador_id']]['usuario_id'] ?? 0);
                        $novo->ordem = (int)$item['ordem'];
                        $novo->deleted = 0;

                        if (!$avaliadorBolsistasTable->save($novo)) {
                            throw new \RuntimeException('Erro ao gravar o vínculo do avaliador.');
                        }
                    }
                });

                $this->Flash->success(
                    empty($vinculosAtivos)
                        ? 'Avaliadores vinculados com sucesso.'
                        : 'Avaliadores substituídos com sucesso.'
                );
                return $this->redirect(['action' => 'listaInscricoes']);
            } catch (\Throwable $e) {
                $this->Flash->error('Não foi possível salvar a vinculação dos avaliadores.');
                return $this->redirect(['action' => 'vincularInscricao', $inscricao->id]);
            }
        }

        $this->set(compact(
            'inscricao',
            'grandesAreas',
            'areasAvaliador1',
            'areasAvaliador2',
            'avaliadoresAvaliador1',
            'avaliadoresAvaliador2',
            'avaliador1Atual',
            'avaliador2Atual',
            'vinculosAtivos',
            'filtrosAvaliador1',
            'filtrosAvaliador2'
        ));
    }

    protected function montarQueryListaRaic(array $filtros)
    {
        $query = $this->fetchTable('Avaliadors')->find()
            ->contain([
                'Usuarios' => ['Unidades', 'Vinculos'],
                'GrandesAreas',
                'Areas',
                'Editais',
                'Unidades',
            ])
            ->leftJoinWith('Usuarios')
            ->leftJoinWith('Editais')
            ->leftJoinWith('Unidades')
            ->where([
                'Avaliadors.deleted' => 0,
                'Avaliadors.tipo_avaliador' => 'R',
                'Avaliadors.ano_convite' => (string)$filtros['ano'],
            ])
            ->orderBy([
                'Usuarios.nome' => 'ASC',
                'Editais.nome' => 'ASC',
                'Unidades.sigla' => 'ASC',
            ]);

        if ((int)$filtros['unidade_id'] > 0) {
            $query->where(['Avaliadors.unidade_id' => (int)$filtros['unidade_id']]);
        } elseif (!$this->ehYoda()) {
            $idsJedi = $this->obterIdsJedi();
            if ($idsJedi === []) {
                $query->where(['1 = 0']);
            } else {
                $query->where(['Avaliadors.unidade_id IN' => $idsJedi]);
            }
        }

        if ((string)$filtros['nome'] !== '') {
            $query->where([
                'Usuarios.nome LIKE' => '%' . (string)$filtros['nome'] . '%',
            ]);
        }

        return $query;
    }

    protected function montarQueryListaNova(array $filtros)
    {
        $query = $this->fetchTable('Avaliadors')->find()
            ->contain([
                'Usuarios' => ['Unidades', 'Vinculos'],
                'GrandesAreas',
                'Areas',
                'Editais',
            ])
            ->leftJoinWith('Usuarios')
            ->leftJoinWith('Editais')
            ->leftJoinWith('GrandesAreas')
            ->leftJoinWith('Areas')
            ->where([
                'Avaliadors.deleted' => 0,
                'Avaliadors.tipo_avaliador' => 'N',
                'Avaliadors.ano_convite' => (string)$filtros['ano'],
            ])
            ->orderBy([
                'Usuarios.nome' => 'ASC',
                'Editais.nome' => 'ASC',
            ]);

        if ((string)$filtros['nome'] !== '') {
            $query->where([
                'Usuarios.nome LIKE' => '%' . (string)$filtros['nome'] . '%',
            ]);
        }

        if ((int)$filtros['editai_id'] > 0) {
            $query->where(['Avaliadors.editai_id' => (int)$filtros['editai_id']]);
        }

        if ((int)$filtros['grandes_area_id'] > 0) {
            $query->where(['Avaliadors.grandes_area_id' => (int)$filtros['grandes_area_id']]);
        }

        if ((int)$filtros['area_id'] > 0) {
            $query->where(['Avaliadors.area_id' => (int)$filtros['area_id']]);
        }

        return $query;
    }

    protected function obterAnosListaRaic(): array
    {
        return $this->obterAnosListaPorTipo('R');
    }

    protected function obterFiltrosListaRaic(array $unidades): array
    {
        $anoAtual = (int)date('Y');
        $filtros = [
            'ano' => (int)$this->request->getQuery('ano', $anoAtual),
            'unidade_id' => (int)$this->request->getQuery('unidade_id', 0),
            'nome' => trim((string)$this->request->getQuery('nome', '')),
        ];

        if ($filtros['unidade_id'] !== 0 && !isset($unidades[$filtros['unidade_id']])) {
            $filtros['unidade_id'] = 0;
        }

        return $filtros;
    }

    protected function exportarListaRaicCsv($query, array $filtros)
    {
        $avaliadores = $query->all();
        $header = [
            'nome_avaliador',
            'cpf',
            'grande_area',
            'area',
            'ano',
            'edital',
            'unidade_avaliacao',
            'vinculo_usuario',
            'unidade_cadastro_usuario',
            'telefone',
            'telefone_contato',
            'celular',
            'whatsapp',
            'email',
            'email_alternativo',
            'email_contato',
        ];

        $fh = fopen('php://temp', 'r+');
        fwrite($fh, "\xEF\xBB\xBF");
        fputcsv($fh, $header, ';');

        $rows = [];
        foreach ($avaliadores as $avaliador) {
            $cpf = (string)($avaliador->usuario->cpf ?? '');
            if ($cpf !== '') {
                $cpf = "\t" . $cpf;
            }

            $row = [
                $avaliador->usuario->nome ?? '',
                $cpf,
                $avaliador->grandes_area->nome ?? '',
                $avaliador->area->nome ?? '',
                $avaliador->ano_convite ?? '',
                $avaliador->editai->nome ?? '',
                $avaliador->unidade->sigla ?? '',
                $avaliador->usuario->vinculo->nome ?? '',
                $avaliador->usuario->unidade->sigla ?? '',
                $avaliador->usuario->telefone ?? '',
                $avaliador->usuario->telefone_contato ?? '',
                $avaliador->usuario->celular ?? '',
                $avaliador->usuario->whatsapp ?? '',
                $avaliador->usuario->email ?? '',
                $avaliador->usuario->email_alternativo ?? '',
                $avaliador->usuario->email_contato ?? '',
            ];
            $row = array_map(function ($value): string {
                $text = (string)$value;
                $text = str_replace(["\r\n", "\n", "\r", "\0"], ' ', $text);
                return $text;
            }, $row);
            fputcsv($fh, $row, ';');
        }

        $nomeArquivo = sprintf(
            'avaliadores_raic_%s.csv',
            date('Ymd_His')
        );

        rewind($fh);
        $csv = (string)stream_get_contents($fh);
        fclose($fh);

        return $this->response
            ->withType('csv')
            ->withCharset('UTF-8')
            ->withStringBody($csv)
            ->withDownload($nomeArquivo);
    }

    protected function obterAnosListaPorTipo(string $tipoAvaliador): array
    {
        $anoAtual = (int)date('Y');
        $anos = $this->fetchTable('Avaliadors')->find()
            ->select(['ano_convite'])
            ->where([
                'Avaliadors.deleted' => 0,
                'Avaliadors.tipo_avaliador' => $tipoAvaliador,
                'Avaliadors.ano_convite IS NOT' => null,
                'Avaliadors.ano_convite <>' => '',
            ])
            ->distinct(['ano_convite'])
            ->orderBy(['Avaliadors.ano_convite' => 'DESC'])
            ->enableHydration(false)
            ->all()
            ->extract('ano_convite')
            ->toList();

        $anosOptions = [];
        foreach ($anos as $ano) {
            $anoInt = (int)$ano;
            if ($anoInt > 0) {
                $anosOptions[$anoInt] = (string)$anoInt;
            }
        }
        if ($anosOptions === []) {
            $anosOptions[$anoAtual] = (string)$anoAtual;
        }

        return $anosOptions;
    }

    protected function obterFiltrosListaNova(): array
    {
        $anoAtual = (int)date('Y');

        return [
            'ano' => (int)$this->request->getQuery('ano', $anoAtual),
            'nome' => trim((string)$this->request->getQuery('nome', '')),
            'editai_id' => (int)$this->request->getQuery('editai_id', 0),
            'grandes_area_id' => (int)$this->request->getQuery('grandes_area_id', 0),
            'area_id' => (int)$this->request->getQuery('area_id', 0),
        ];
    }

    protected function obterFiltrosListaInscricoes(): array
    {
        return [
            'editai_id' => (int)$this->request->getQuery('editai_id', 0),
            'status_vinculo' => trim((string)$this->request->getQuery('status_vinculo', '')),
            'grandes_area_id' => (int)$this->request->getQuery('grandes_area_id', 0),
            'area_id' => (int)$this->request->getQuery('area_id', 0),
        ];
    }

    protected function obterEditaisAbertosInscricoes(): array
    {
        return $this->fetchTable('Editais')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where([
                'Editais.deleted' => 0,
                'Editais.origem IN' => ['N', 'R'],
                'Editais.inicio_avaliar < NOW()',
                'Editais.fim_avaliar > NOW()',
            ])
            ->orderBy(['Editais.nome' => 'ASC'])
            ->toArray();
    }

    protected function montarQueryListaInscricoes(array $filtros)
    {
        $projetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $query = $projetoBolsistas->find()
            ->select([
                'ProjetoBolsistas.id',
                'ProjetoBolsistas.editai_id',
                'ProjetoBolsistas.projeto_id',
                'ProjetoBolsistas.bolsista',
                'ProjetoBolsistas.orientador',
                'ProjetoBolsistas.origem',
                'ProjetoBolsistas.sp_titulo',
                'ProjetoBolsistas.created',
                'projeto_titulo' => 'Projetos.titulo',
                'area_nome' => 'Areas.nome',
                'grande_area_nome' => 'GrandesAreas.nome',
                'total_avaliadores' => $projetoBolsistas->find()->newExpr('COALESCE(av_vinculos.total_avaliadores, 0)'),
                'avaliadores_nomes' => $projetoBolsistas->find()->newExpr('av_vinculos.avaliadores_nomes'),
            ])
            ->contain([
                'Editais' => function ($q) {
                    return $q->select(['Editais.id', 'Editais.nome', 'Editais.origem']);
                },
                'Bolsistas' => function ($q) {
                    return $q->select(['Bolsistas.id', 'Bolsistas.nome', 'Bolsistas.cpf', 'Bolsistas.unidade_id', 'Bolsistas.vinculo_id'])
                        ->contain([
                            'Unidades' => function ($qu) {
                                return $qu->select(['Unidades.id', 'Unidades.sigla']);
                            },
                            'Vinculos' => function ($qu) {
                                return $qu->select(['Vinculos.id', 'Vinculos.nome']);
                            },
                        ]);
                },
                'Orientadores' => function ($q) {
                    return $q->select(['Orientadores.id', 'Orientadores.nome', 'Orientadores.cpf', 'Orientadores.unidade_id', 'Orientadores.vinculo_id'])
                        ->contain([
                            'Unidades' => function ($qu) {
                                return $qu->select(['Unidades.id', 'Unidades.sigla']);
                            },
                            'Vinculos' => function ($qu) {
                                return $qu->select(['Vinculos.id', 'Vinculos.nome']);
                            },
                        ]);
                },
                'Projetos' => function ($q) {
                    return $q->select(['Projetos.id', 'Projetos.titulo', 'Projetos.area_id'])
                        ->contain([
                            'Areas' => function ($qu) {
                                return $qu->select(['Areas.id', 'Areas.nome', 'Areas.grandes_area_id'])
                                    ->contain([
                                        'GrandesAreas' => function ($qg) {
                                            return $qg->select(['GrandesAreas.id', 'GrandesAreas.nome']);
                                        },
                                    ]);
                            },
                        ]);
                },
            ])
            ->innerJoinWith('Editais')
            ->leftJoinWith('Bolsistas')
            ->leftJoinWith('Orientadores')
            ->leftJoinWith('Projetos.Areas.GrandesAreas')
            ->leftJoin(
                [
                    'av_vinculos' => '(
                        SELECT
                            ab.projeto_bolsista_id,
                            COUNT(*) AS total_avaliadores,
                            GROUP_CONCAT(DISTINCT u.nome ORDER BY u.nome SEPARATOR " | ") AS avaliadores_nomes
                        FROM avaliador_bolsistas ab
                        LEFT JOIN usuarios u ON u.id = ab.usuario_id
                        WHERE ab.deleted = 0
                          AND ab.projeto_bolsista_id IS NOT NULL
                        GROUP BY ab.projeto_bolsista_id
                    )',
                ],
                'av_vinculos.projeto_bolsista_id = ProjetoBolsistas.id'
            )
            ->where([
                'ProjetoBolsistas.deleted IS' => null,
                'Editais.deleted' => 0,
                'Editais.origem IN' => ['N', 'R'],
                'Editais.inicio_avaliar < NOW()',
                'Editais.fim_avaliar > NOW()',
            ])
            ->orderBy([
                'Editais.nome' => 'ASC',
                'Bolsistas.nome' => 'ASC',
                'ProjetoBolsistas.id' => 'DESC',
            ]);

        if ((int)$filtros['editai_id'] > 0) {
            $query->where(['ProjetoBolsistas.editai_id' => (int)$filtros['editai_id']]);
        }

        if ((int)$filtros['grandes_area_id'] > 0) {
            $query->where(['Areas.grandes_area_id' => (int)$filtros['grandes_area_id']]);
        }

        if ((int)$filtros['area_id'] > 0) {
            $query->where(['Projetos.area_id' => (int)$filtros['area_id']]);
        }

        if ($filtros['status_vinculo'] === 'vinculado') {
            $query->where(['COALESCE(av_vinculos.total_avaliadores, 0) = 2']);
        } elseif ($filtros['status_vinculo'] === 'nao_vinculado') {
            $query->where(['COALESCE(av_vinculos.total_avaliadores, 0) = 0']);
        }

        return $query;
    }

    protected function obterOpcoesAvaliadoresInscricao(
        int $editaiId,
        array $usuariosExcluidos,
        int $grandeAreaId,
        int $areaId
    ): array {
        $avaliadorsTable = $this->fetchTable('Avaliadors');
        $query = $avaliadorsTable->find('list', [
            'keyField' => 'id',
            'valueField' => function ($row): string {
                $nome = (string)($row->usuario->nome ?? 'Não informado');
                $sigla = (string)($row->usuario->unidade->sigla ?? 'Unidade não informada');
                $grandeArea = (string)($row->grandes_area->nome ?? 'Não informada');
                $area = (string)($row->area->nome ?? 'Não informada');
                return $nome . ' (' . $sigla . ') (' . $grandeArea . ' - ' . $area . ')';
            },
        ])
            ->contain([
                'GrandesAreas',
                'Areas',
                'Usuarios' => ['Unidades'],
            ])
            ->where([
                'Avaliadors.editai_id' => $editaiId,
                'Avaliadors.tipo_avaliador IN' => ['N', 'A'],
                'Avaliadors.deleted' => 0,
            ]);

        if ($usuariosExcluidos !== []) {
            $query->where(['Avaliadors.usuario_id NOT IN' => $usuariosExcluidos]);
        }

        if ($grandeAreaId > 0) {
            $query->where(['Avaliadors.grandes_area_id' => $grandeAreaId]);
        }

        if ($areaId > 0) {
            $query->where(['Avaliadors.area_id' => $areaId]);
        }

        $rankingSql = sprintf(
            'CASE
                WHEN Avaliadors.grandes_area_id = %d AND Avaliadors.area_id = %d THEN 0
                WHEN Avaliadors.grandes_area_id = %d THEN 1
                ELSE 2
            END',
            $grandeAreaId,
            $areaId,
            $grandeAreaId
        );

        $query
            ->orderByAsc($query->newExpr($rankingSql))
            ->orderBy(['Usuarios.nome' => 'ASC']);

        return $query->toArray();
    }

    protected function obterUsuariosExcluidosInscricao($inscricao): array
    {
        return array_values(array_filter(array_unique([
            (int)($inscricao->orientador ?? 0),
            (int)($inscricao->coorientador ?? 0),
            (int)($inscricao->bolsista ?? 0),
        ])));
    }

    protected function obterEditaisListaNova(): array
    {
        return $this->fetchTable('Editais')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where([
                'Editais.deleted' => 0,
                'Editais.origem IN' => ['N', 'R', 'J', 'W'],
            ])
            ->orderBy(['Editais.nome' => 'ASC'])
            ->toArray();
    }

    protected function obterGrandesAreasRestritas(): array
    {
        return $this->fetchTable('GrandesAreas')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where(['GrandesAreas.id <' => 10])
            ->orderBy(['GrandesAreas.nome' => 'ASC'])
            ->toArray();
    }

    protected function obterAreasPorGrandeArea(int $grandeAreaId): array
    {
        if ($grandeAreaId <= 0) {
            return [];
        }

        return $this->fetchTable('Areas')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where(['Areas.grandes_area_id' => $grandeAreaId])
            ->orderBy(['Areas.nome' => 'ASC'])
            ->toArray();
    }

    protected function exportarListaNovaCsv($query)
    {
        $avaliadores = $query->all();
        $header = [
            'nome_avaliador',
            'cpf',
            'grande_area',
            'area',
            'ano',
            'edital',
            'vinculo_usuario',
            'unidade_cadastro_usuario',
            'telefone',
            'telefone_contato',
            'celular',
            'whatsapp',
            'email',
            'email_alternativo',
            'email_contato',
        ];

        $fh = fopen('php://temp', 'r+');
        fwrite($fh, "\xEF\xBB\xBF");
        fputcsv($fh, $header, ';');

        foreach ($avaliadores as $avaliador) {
            $cpf = (string)($avaliador->usuario->cpf ?? '');
            if ($cpf !== '') {
                $cpf = "\t" . $cpf;
            }

            $row = [
                $avaliador->usuario->nome ?? '',
                $cpf,
                $avaliador->grandes_area->nome ?? '',
                $avaliador->area->nome ?? '',
                $avaliador->ano_convite ?? '',
                $avaliador->editai->nome ?? '',
                $avaliador->usuario->vinculo->nome ?? '',
                $avaliador->usuario->unidade->sigla ?? '',
                $avaliador->usuario->telefone ?? '',
                $avaliador->usuario->telefone_contato ?? '',
                $avaliador->usuario->celular ?? '',
                $avaliador->usuario->whatsapp ?? '',
                $avaliador->usuario->email ?? '',
                $avaliador->usuario->email_alternativo ?? '',
                $avaliador->usuario->email_contato ?? '',
            ];
            $row = array_map(function ($value): string {
                $text = (string)$value;
                $text = str_replace(["\r\n", "\n", "\r", "\0"], ' ', $text);
                return $text;
            }, $row);
            fputcsv($fh, $row, ';');
        }

        $nomeArquivo = sprintf(
            'avaliadores_editais_%s.csv',
            date('Ymd_His')
        );

        rewind($fh);
        $csv = (string)stream_get_contents($fh);
        fclose($fh);

        return $this->response
            ->withType('csv')
            ->withCharset('UTF-8')
            ->withStringBody($csv)
            ->withDownload($nomeArquivo);
    }

    protected function obterUnidadesDisponiveis(): array
    {
        $query = $this->fetchTable('Unidades')->find('list', [
            'keyField' => 'id',
            'valueField' => 'sigla',
        ])
            ->where(['Unidades.deleted' => 0])
            ->orderBy(['Unidades.sigla' => 'ASC']);

        if ($this->ehYoda()) {
            return $query->toArray();
        }

        $idsJedi = $this->obterIdsJedi();
        if ($idsJedi === []) {
            return [];
        }

        return $query
            ->where(['Unidades.id IN' => $idsJedi])
            ->toArray();
    }

    protected function obterIdsJedi(): array
    {
        $identity = $this->getIdentityAtual();
        $jediRaw = '';

        if (is_array($identity)) {
            $jediRaw = (string)($identity['jedi'] ?? '');
        } elseif ($identity !== null) {
            $jediRaw = (string)($identity->jedi ?? '');
        }

        if ($jediRaw === '') {
            $jediRaw = (string)($this->request->getAttribute('identity')['jedi'] ?? '');
        }

        $partes = array_map('trim', explode(',', $jediRaw));
        $ids = [];
        foreach ($partes as $parte) {
            if ($parte !== '' && ctype_digit($parte)) {
                $ids[] = (int)$parte;
            }
        }

        return array_values(array_unique($ids));
    }

    protected function validarFormularioCadastroRaic(array $dados, array $editais, array $unidades, array $areas): array
    {
        $erros = [];

        if (empty($dados['editai_id']) || !isset($editais[$dados['editai_id']])) {
            $erros[] = 'Selecione um edital RAIC com avaliações abertas.';
        }

        if (empty($dados['unidade_id']) || !isset($unidades[$dados['unidade_id']])) {
            $erros[] = 'Selecione uma unidade válida e ativa.';
        }

        if (empty($dados['grandes_area_id'])) {
            $erros[] = 'Selecione a grande área.';
        }

        if (empty($dados['area_id']) || !isset($areas[$dados['area_id']])) {
            $erros[] = 'Selecione a área correspondente à grande área informada.';
        }

        if (trim((string)$dados['cpfs']) === '') {
            $erros[] = 'Informe ao menos um CPF.';
        }

        return $erros;
    }

    protected function validarFormularioCadastroNova(array $dados, array $editais, array $areas): array
    {
        $erros = [];

        if (empty($dados['editais'])) {
            $erros[] = 'Selecione ao menos um edital.';
        } else {
            foreach ($dados['editais'] as $editaiId) {
                if (!isset($editais[$editaiId])) {
                    $erros[] = 'Há edital inválido entre os selecionados.';
                    break;
                }
            }
        }

        if (empty($dados['grandes_area_id'])) {
            $erros[] = 'Selecione a grande área.';
        }

        if (empty($dados['area_id']) || !isset($areas[$dados['area_id']])) {
            $erros[] = 'Selecione a área correspondente à grande área informada.';
        }

        if (trim((string)$dados['cpfs']) === '') {
            $erros[] = 'Informe ao menos um CPF.';
        }

        return $erros;
    }

    protected function analisarCadastroRaic(array $dados): array
    {
        $usuariosTable = $this->fetchTable('Usuarios');
        $avaliadorsTable = $this->fetchTable('Avaliadors');

        $cpfs = $this->normalizarCpfs((string)$dados['cpfs']);
        $resultado = [
            'processado' => true,
            'confirmado' => false,
            'elegiveis' => [],
            'inelegiveis' => [],
            'totalInformados' => count($cpfs),
            'totalElegiveis' => 0,
            'totalInelegiveis' => 0,
        ];

        if ($cpfs === []) {
            return $resultado;
        }

        $cpfJaProcessado = [];
        foreach ($cpfs as $cpfInformado) {
            $cpfOriginal = $cpfInformado['original'];
            $cpfNumerico = $cpfInformado['numerico'];

            if (isset($cpfJaProcessado[$cpfNumerico])) {
                $resultado['inelegiveis'][] = [
                    'cpf' => $cpfOriginal,
                    'motivo' => 'CPF repetido na própria listagem enviada.',
                ];
                continue;
            }
            $cpfJaProcessado[$cpfNumerico] = true;

            if (!$this->validaCPF($cpfNumerico)) {
                $resultado['inelegiveis'][] = [
                    'cpf' => $cpfOriginal,
                    'motivo' => 'CPF inválido.',
                ];
                continue;
            }

            $usuario = $usuariosTable->find()
                ->select(['id', 'nome', 'cpf'])
                ->where(['Usuarios.cpf' => $cpfNumerico])
                ->first();

            if (!$usuario) {
                $resultado['inelegiveis'][] = [
                    'cpf' => $cpfOriginal,
                    'motivo' => 'CPF não localizado na base de usuários.',
                ];
                continue;
            }

            $jaCadastrado = $this->existeCadastroRaic(
                (int)$usuario->id,
                (int)$dados['editai_id'],
                (int)$dados['unidade_id']
            );

            if ($jaCadastrado) {
                $resultado['inelegiveis'][] = [
                    'cpf' => $cpfOriginal,
                    'nome' => (string)$usuario->nome,
                    'motivo' => 'Avaliador já cadastrado nesta unidade para o edital RAIC selecionado.',
                ];
                continue;
            }

            $resultado['elegiveis'][] = [
                'usuario_id' => (int)$usuario->id,
                'cpf' => $cpfOriginal,
                'nome' => (string)$usuario->nome,
            ];
        }

        $resultado['totalElegiveis'] = count($resultado['elegiveis']);
        $resultado['totalInelegiveis'] = count($resultado['inelegiveis']);

        return $resultado;
    }

    protected function confirmarCadastroRaic(array $dados, array $elegiveis): int
    {
        $avaliadorsTable = $this->fetchTable('Avaliadors');
        $anoAtual = date('Y');
        $entidadesSalvar = [];
        $totalSalvar = 0;

        foreach ($elegiveis as $item) {
            if ($this->existeCadastroRaic(
                (int)$item['usuario_id'],
                (int)$dados['editai_id'],
                (int)$dados['unidade_id']
            )) {
                continue;
            }

            $entidadesSalvar[] = $avaliadorsTable->newEntity([
                'usuario_id' => (int)$item['usuario_id'],
                'grandes_area_id' => (int)$dados['grandes_area_id'],
                'area_id' => (int)$dados['area_id'],
                'ano_convite' => $anoAtual,
                'ano_aceite' => $anoAtual,
                'tipo_avaliador' => 'R',
                'deleted' => 0,
                'editai_id' => (int)$dados['editai_id'],
                'unidade_id' => (int)$dados['unidade_id'],
                'aceite' => 1,
            ]);
            $totalSalvar++;
        }

        $avaliadorsTable->getConnection()->transactional(function () use ($avaliadorsTable, $entidadesSalvar): void {
            foreach ($entidadesSalvar as $entidade) {
                $avaliadorsTable->saveOrFail($entidade);
            }
        });

        return $totalSalvar;
    }

    protected function analisarCadastroNova(array $dados): array
    {
        $usuariosTable = $this->fetchTable('Usuarios');
        $cpfs = $this->normalizarCpfs((string)$dados['cpfs']);
        $editaisSelecionados = $this->normalizarEditaisSelecionados((array)$dados['editais']);

        $resultado = [
            'processado' => true,
            'confirmado' => false,
            'elegiveis' => [],
            'inelegiveis' => [],
            'totalInformados' => count($cpfs),
            'totalElegiveis' => 0,
            'totalInelegiveis' => 0,
        ];

        if ($cpfs === [] || $editaisSelecionados === []) {
            return $resultado;
        }

        $cpfJaProcessado = [];
        foreach ($cpfs as $cpfInformado) {
            $cpfOriginal = $cpfInformado['original'];
            $cpfNumerico = $cpfInformado['numerico'];

            if (isset($cpfJaProcessado[$cpfNumerico])) {
                $resultado['inelegiveis'][] = [
                    'cpf' => $cpfOriginal,
                    'motivo' => 'CPF repetido na própria listagem enviada.',
                ];
                continue;
            }
            $cpfJaProcessado[$cpfNumerico] = true;

            if (!$this->validaCPF($cpfNumerico)) {
                $resultado['inelegiveis'][] = [
                    'cpf' => $cpfOriginal,
                    'motivo' => 'CPF inválido.',
                ];
                continue;
            }

            $usuario = $usuariosTable->find()
                ->select(['id', 'nome', 'cpf'])
                ->where(['Usuarios.cpf' => $cpfNumerico])
                ->first();

            if (!$usuario) {
                $resultado['inelegiveis'][] = [
                    'cpf' => $cpfOriginal,
                    'motivo' => 'CPF não localizado na base de usuários.',
                ];
                continue;
            }

            foreach ($editaisSelecionados as $editaiId) {
                if ($this->existeCadastroNova((int)$usuario->id, $editaiId)) {
                    $resultado['inelegiveis'][] = [
                        'cpf' => $cpfOriginal,
                        'nome' => (string)$usuario->nome,
                        'editai_id' => $editaiId,
                        'motivo' => 'Avaliador já cadastrado para este edital.',
                    ];
                    continue;
                }

                $resultado['elegiveis'][] = [
                    'usuario_id' => (int)$usuario->id,
                    'cpf' => $cpfOriginal,
                    'nome' => (string)$usuario->nome,
                    'editai_id' => $editaiId,
                ];
            }
        }

        $resultado['totalElegiveis'] = count($resultado['elegiveis']);
        $resultado['totalInelegiveis'] = count($resultado['inelegiveis']);

        return $resultado;
    }

    protected function confirmarCadastroNova(array $dados, array $elegiveis): int
    {
        $avaliadorsTable = $this->fetchTable('Avaliadors');
        $anoAtual = date('Y');
        $entidadesSalvar = [];
        $totalSalvar = 0;

        foreach ($elegiveis as $item) {
            if ($this->existeCadastroNova((int)$item['usuario_id'], (int)$item['editai_id'])) {
                continue;
            }

            $entidadesSalvar[] = $avaliadorsTable->newEntity([
                'usuario_id' => (int)$item['usuario_id'],
                'grandes_area_id' => (int)$dados['grandes_area_id'],
                'area_id' => (int)$dados['area_id'],
                'ano_convite' => $anoAtual,
                'ano_aceite' => $anoAtual,
                'tipo_avaliador' => 'N',
                'deleted' => 0,
                'editai_id' => (int)$item['editai_id'],
                'unidade_id' => null,
                'aceite' => 1,
            ]);
            $totalSalvar++;
        }

        $avaliadorsTable->getConnection()->transactional(function () use ($avaliadorsTable, $entidadesSalvar): void {
            foreach ($entidadesSalvar as $entidade) {
                $avaliadorsTable->saveOrFail($entidade);
            }
        });

        return $totalSalvar;
    }

    protected function existeCadastroRaic(int $usuarioId, int $editaiId, int $unidadeId): bool
    {
        return $this->fetchTable('Avaliadors')->find()
            ->where([
                'Avaliadors.usuario_id' => $usuarioId,
                'Avaliadors.editai_id' => $editaiId,
                'Avaliadors.unidade_id' => $unidadeId,
                'Avaliadors.deleted' => 0,
            ])
            ->count() > 0;
    }

    protected function existeCadastroNova(int $usuarioId, int $editaiId): bool
    {
        return $this->fetchTable('Avaliadors')->find()
            ->where([
                'Avaliadors.usuario_id' => $usuarioId,
                'Avaliadors.editai_id' => $editaiId,
                'Avaliadors.deleted' => 0,
            ])
            ->count() > 0;
    }

    protected function normalizarEditaisSelecionados(array $editais): array
    {
        $selecionados = [];
        foreach ($editais as $valor) {
            $id = (int)$valor;
            if ($id > 0) {
                $selecionados[] = $id;
            }
        }

        return array_values(array_unique($selecionados));
    }

    protected function normalizarCpfs(string $texto): array
    {
        $partes = preg_split('/[\s,;]+/', $texto) ?: [];
        $cpfs = [];

        foreach ($partes as $parte) {
            $parte = trim($parte);
            if ($parte === '') {
                continue;
            }

            $cpfNumerico = preg_replace('/\D+/', '', $parte) ?? '';
            if ($cpfNumerico === '') {
                continue;
            }

            $cpfs[] = [
                'original' => $parte,
                'numerico' => $cpfNumerico,
            ];
        }

        return $cpfs;
    }
}
