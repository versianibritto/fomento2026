<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;


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


     public function imprimirSolicitacao($pb)
    {
        $this->viewBuilder()->setLayout('ajax');

        $identity = $this->identityLogado;
        if (empty($pb)) {
            $this->Flash->error('Parametros invalidos para o termo.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $bol = $this->fetchTable('ProjetoBolsistas')->find()
            ->contain([
                'Bolsistas'=>'Instituicaos', 'Editais'=>'Programas', 'Orientadores'=>['Vinculos', 'Escolaridades']
            ])
            ->where(['ProjetoBolsistas.id' => (int)$pb])
            ->first();

        if (!$pb) {
            $this->Flash->error('Inscricao não localizada para o termo.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $ehYoda = $this->ehYoda();
        $identityId = (int)($identity->id ?? 0);
        $ehOrientador = $identityId > 0 && $identityId === (int)($bol->orientador ?? 0);


        if (!$ehYoda && !$ehOrientador) {
            $this->Flash->error('Apenas o orientador pode gerar o documento');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }
            
        if((!in_array($bol->vigente, [1]))) {
            $this->Flash->error('Esta função abrange apenas bolsas vigentes.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);          
        }

        if((in_array($bol->editai->programa_id, [1]))) {
            $this->Flash->error('Esta função abrange apenas Ics.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);          
        }
        
                  
        if( ($bol->editai_id > 37)){
            $this->Flash->error('Esta função abrange apenas bolsas vigentes dos editais de 2025.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }


        //verifica aceite do bolsista
            if((!in_array($bol->resposta_bolsista, ['A']))) {
                $this->Flash->error('Não é possivel imprimir o termo sem o aceite do bolsista.');
                return $this->redirect(['controller' => 'Index', 'action' => 'index']);
            }
        //

            $projeto = TableRegistry::getTableLocator()->get('Projetos')->get($bol->projeto_id,['contain'=>[
                'Usuarios'=>[
                    'Escolaridades',
                    'Unidades',
                    'Vinculos'
                ],
                'Areas',
                'Linhas',
                'ProjetoBolsistas'=>[
                    'Bolsistas',
                    'Orientadores',
                    'Coorientadores'
                ]
            ]
        ]);  
        $teste_idade=parent::idade($bol->bolsista_usuario->data_nascimento->i18nFormat('YYYY-MM-dd'));
        

        $this->set(compact('projeto', 'bol', 'teste_idade'));
    }

    public function termopdj(int|string|null $id = null)
    {
        $this->viewBuilder()->setLayout('ajax');
        if (empty($id)) {
            $this->Flash->error('Inscrição não informada.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $tblInscricao = TableRegistry::getTableLocator()->get('ProjetoBolsistas');
        $inscricao = $tblInscricao->find()
            ->contain([
                'Orientadores' => [
                    'Unidades',
                    'Streets' => [
                        'Districts' => 'Cities',
                    ],
                    'Vinculos',
                ],
                'Bolsistas' => [
                    'Escolaridades',
                ],
                'Projetos',
                'Editais',
                'Areas',
            ])
            ->where(['ProjetoBolsistas.id' => (int)$id])
            ->first();

        if (!$inscricao) {
            $this->Flash->error('Inscrição não localizada.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        if ($inscricao->programa_id>1) {
            return $this->redirect(['action' => 'imprimirSolicitacao', $id]);
        }

        $inscricao->usuario = $inscricao->orientadore;
        $inscricao->candidato = $inscricao->bolsista_usuario;
        $inscricao->edital_id = $inscricao->editai_id;
        $inscricao->area_id = $inscricao->area_pdj;

        foreach (['usuario', 'candidato', 'edital_id', 'area_id'] as $campoTemporario) {
            $inscricao->setDirty($campoTemporario, false);
        }

        if (empty($inscricao->usuario) || empty($inscricao->candidato)) {
            $this->Flash->error('Inscrição sem orientador ou bolsista vinculado.');
            return $this->redirect(['action' => 'visualizar', $inscricao->id]);
        }

        $doc_bolsista = TableRegistry::getTableLocator()->get('Anexos')->find()->contain(['AnexosTipos', 'Usuarios'])->where([
            'projeto_bolsista_id' => $inscricao->id,
            'deleted IS' => null,
        ]);
        
        if(!in_array($this->request->getAttribute('identity')['id'], [1,8088, $inscricao->orientador])){
            $this->Flash->error('Somente o orientador pode realizar esta ação');
            return $this->redirect(['action' => 'visualizar',$inscricao->id ]);
        }

        if($inscricao->deleted!=null) {
            $this->Flash->error('A inscrição foi deletada');
            return $this->redirect(['action' => 'visualizar',$inscricao->id ]);
        }
        if($inscricao->usuario->unidade_id == null) {
            $this->Flash->error('O Orientador deve informar sua unidade');
            return $this->redirect(['action' => 'visualizar',$inscricao->id ]);
        }
        if($inscricao->usuario->matricula_siape == null) {
            $this->Flash->error('O Orientador deve informar sua matrícula Siape');
            return $this->redirect(['action' => 'visualizar',$inscricao->id ]);
        }
        if($inscricao->usuario->departamento == null) {
            $this->Flash->error('O Orientador deve informar seu departamento');
            return $this->redirect(['action' => 'visualizar',$inscricao->id ]);
        }

        /*
        if($inscricao->usuario->street_id == null) {
            $this->Flash->error('O Orientador deve informar seu endereço');
            return $this->redirect(['action' => 'detalhepdj',$inscricao->id ]);
        }
            */

        
        if($inscricao->candidato->escolaridade_id == null) {
            $this->Flash->error('O bolsista deve confirmar o doutourado em seu cadastro');
            return $this->redirect(['action' => 'visualizar',$inscricao->id ]);
        }

        if($inscricao->candidato->escolaridade_id !=10) {
            $this->Flash->error('O bolsista deve ser doutor');
            return $this->redirect(['action' => 'visualizar',$inscricao->id ]);
        }

        if($inscricao->candidato->ano_conclusao==null) {
            $this->Flash->error('O candidato deve informar o ano de conclusão do doutorado no seu cadastro');
            return $this->redirect(['action' => 'visualizar',$inscricao->id ]);
        }

        if($inscricao->candidato->ano_conclusao > 2026) {
            $this->Flash->error('O candidato deve ter concluído o doutorado até a implantação da bolsa');
            return $this->redirect(['action' => 'visualizar',$inscricao->id ]);
        }

        $pb =  $tblInscricao->find()->where([
            'bolsista' => $inscricao->bolsista, 
            'fase_id IN' => [4, 5],
            'editai_id'=>$inscricao->editai_id,
            'id <>'=>$inscricao->id,
            '(deleted IS NULL)'
        ])->count();

        if($pb>0) {
            $this->Flash->error('O bolsista informado possui outra inscrição em finalizada. Indique outro bolsista para continuar o processo!');
            return $this->redirect(['action' => 'visualizar',$inscricao->id ]);
        }

        $orientab =  $tblInscricao->find()->where([
            'orientador' => $inscricao->orientador, 
            'fase_id IN' => [4, 5],
            'editai_id'=>$inscricao->editai_id,
            'id <>'=>$inscricao->id,
            '(deleted IS NULL)'
        ])->count();

        if($orientab>0) {
            $this->Flash->error('O orientador tem outra inscrição finalizada.');
            return $this->redirect(['action' => 'visualizar',$inscricao->id ]);
        }
        
        $existe_anexo = TableRegistry::getTableLocator()->get('Anexos')->find()->where([
            'projeto_id' => $inscricao->projeto_id,
            'projeto_bolsista_id' => $inscricao->id,
            'anexos_tipo_id' => 10,
            'deleted IS' => null,
        ])->first();
        

        if(!$existe_anexo) {
            $this->Flash->error('O bolsista precisa anexar o comprovante de escolaridade antes de finalizar a inscrição!');
            return $this->redirect(['action' => 'visualizar',$inscricao->id ]);
        }

        if(in_array($inscricao->cota, ['N', 'I','D', 'T'])){
            $existe_cota = TableRegistry::getTableLocator()->get('Anexos')->find()->where([
                'projeto_id' => $inscricao->projeto_id,
                'projeto_bolsista_id' => $inscricao->id,
                'anexos_tipo_id' => 21,
                'deleted IS' => null,
            ])->first();
            
            if(!$existe_cota) {
                $this->Flash->error('O bolsista se increveu em cotas e precisa anexar a autodeclaração;');
                return $this->redirect(['action' => 'visualizar',$inscricao->id ]);
            }
        }
        //bloco colocado para abrir anexar fora do periodo de inscrição
            $ed = TableRegistry::getTableLocator()->get('Editais')->find()->where(['id' => $inscricao->editai_id])->first();
            
            // Vinculos       
            if($ed->vinculos_permitidos != null) {
            if(!in_array($this->Authentication->getIdentity()['vinculo_id'], explode(",", str_replace(" ", "", $ed->vinculos_permitidos)))) {
                    $vinculos = TableRegistry::getTableLocator()->get('Vinculos')->find()->where('id IN (' . $ed->vinculos_permitidos . ')');
                    $vinc = "";
                    $vp = "Não informado";
                    foreach($vinculos as $esc){
                        $vinc .= "<li>" . $esc->nome . "</li>";
                    }
                    $this->Flash->error("Este edital é restrito aos seguintes vínculos funcionais: <ul>" . $vinc . "</ul>" , ['escape' => false]);
                    return $this->redirect($this->referer());
                }
            }
            
            // Escolaridades
            if($ed->escolaridades_permitidas != null) {
                if(!in_array($this->Authentication->getIdentity()['escolaridade_id'], explode(",", str_replace(" ", "", $ed->escolaridades_permitidas)))) {
                    $escolaridades = TableRegistry::getTableLocator()->get('Escolaridades')->find()->where('id IN (' . $ed->escolaridades_permitidas . ')');
                    $escolas = "";
                    foreach($escolaridades as $esc){
                        $escolas .= "<li>" . $esc->nome . "</li>";
                    }
                    $this->Flash->error("Este edital é restrito às seguintes escolaridades: <ul>" . $escolas . "</ul>", ['escape' => false]);
                    return $this->redirect($this->referer());
                }
            }
            if (in_array((int)$inscricao->fase_id, [1, 3], true)) {
                $faseOriginal = (int)$inscricao->fase_id;
                $inscricaoPatch = $tblInscricao->patchEntity($inscricao, ['fase_id' => 5]);
                $tblInscricao->saveOrFail($inscricaoPatch);
                $inscricao->fase_id = 5;
                $this->historico((int)$inscricao->id, $faseOriginal, 5, 'Geração de termo PDJ', true);
            }

            //se for uma finalização de inscrição, deleta todas as indcrições anteriores desse orientador 
            // e qq outra inscrição que o bolsista esteja vinculado
            // sem comprometer as substituições
            if((int)$inscricao->fase_id === 5){
                $alt_bols = $tblInscricao->updateAll([
                    'deleted' => date('Y-m-d H:i:s'),
                ], ['bolsista' => $inscricao->bolsista,  'editai_id'=>$inscricao->editai_id, 'fase_id IN' => [1, 2, 3], '(deleted IS NULL)', 'id <>'=>$inscricao->id ]);
                $alt_orient = $tblInscricao->updateAll([
                    'deleted' => date('Y-m-d H:i:s'),
                ], ['orientador' => $inscricao->orientador,  'editai_id'=>$inscricao->editai_id, 'fase_id IN' => [1, 2, 3], '(deleted IS NULL)', 'id <>'=>$inscricao->id ]);
    
            }
            
        //

        $this->set(compact('inscricao', 'doc_bolsista'));
    }


    public function visualizar(int|string|null $inscricaoId = null)
    {
        $identity = $this->identityLogado;
        if (empty($inscricaoId)) {
            $this->Flash->error('Parametros invalidos para visualizacao.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'T']);
        }

        $inscricao = $this->fetchTable('ProjetoBolsistas')->find()
            ->contain([
                'Editais' => ['Programas'],
                'Bolsistas',
                'Orientadores',
                'Coorientadores' => ['Escolaridades', 'Vinculos'],
                'Projetos' => ['Areas' => ['GrandesAreas'], 'Linhas' => ['AreasFiocruz']],
                'Fases',
                'Anexos' => ['conditions' => 'Anexos.deleted IS NULL', 'AnexosTipos', 'Usuarios'],
            ])
            ->where(['ProjetoBolsistas.id' => (int)$inscricaoId])
            ->first();

        if (!$inscricao) {
            $this->Flash->error('Inscricao não localizada para visualizacao.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $instituicoesTable = $this->fetchTable('Instituicaos');
        if (!empty($inscricao->bolsista_usuario) && is_numeric((string)($inscricao->bolsista_usuario->instituicao_curso ?? ''))) {
            $inscricao->bolsista_usuario->instituicao = $instituicoesTable->find()
                ->where(['Instituicaos.id' => (int)$inscricao->bolsista_usuario->instituicao_curso])
                ->first();
        }
        if (!empty($inscricao->coorientadore) && is_numeric((string)($inscricao->coorientadore->instituicao_curso ?? ''))) {
            $inscricao->coorientadore->instituicao = $instituicoesTable->find()
                ->where(['Instituicaos.id' => (int)$inscricao->coorientadore->instituicao_curso])
                ->first();
        }

        $ehYoda = $this->ehYoda();
        $identityId = (int)($identity->id ?? 0);
        $ehOrientador = $identityId > 0 && $identityId === (int)($inscricao->orientador ?? 0);
        $ehCoorientador = $identityId > 0 && $identityId === (int)($inscricao->coorientador ?? 0);
        $ehBolsista = $identityId > 0 && $identityId === (int)($inscricao->bolsista ?? 0);

        $jediPermitidas = array_values(array_filter(array_map('trim', explode(',', (string)($identity->jedi ?? '')))));
        $unidadeOrientador = (string)($inscricao->orientadore->unidade_id ?? $inscricao->orientadore->unidade?->id ?? '');
        $ehJediPermitido = !$ehYoda
            && !empty($jediPermitidas)
            && $unidadeOrientador !== ''
            && in_array($unidadeOrientador, $jediPermitidas, true);

        $padauanPermitidos = array_values(array_filter(array_map('trim', explode(',', (string)($identity->padauan ?? '')))));
        $programaInscricao = (string)((int)($inscricao->programa_id ?? $inscricao->editai->programa_id ?? 0));
        $ehPadauanPermitido = !$ehYoda
            && !empty($padauanPermitidos)
            && $programaInscricao !== '0'
            && in_array($programaInscricao, $padauanPermitidos, true);

        if (!$ehYoda && !$ehOrientador && !$ehCoorientador && !$ehBolsista && !$ehJediPermitido && !$ehPadauanPermitido) {
            $this->Flash->error('Sem acesso a esta inscrição. Somente Gestão e coordenação da unidade da inscrição.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $edital = $inscricao->editai ?? null;
        $editaiId = (int)($inscricao->editai_id ?? 0);
        if ($editaiId <= 0) {
            $editaiId = (int)($inscricao->edital_id ?? 0);
        }
        if (!$edital && $editaiId > 0) {
            $edital = $this->fetchTable('Editais')->find()
                ->contain(['Programas'])
                ->where(['Editais.id' => (int)$editaiId])
                ->first();
        }
        if (!$edital) {
            $this->Flash->error('Edital não localizado para visualizacao.');
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
            'O' => [],
            'OUTROS' => [],
        ];
        $programaEdital = (int)($edital->programa_id ?? 0);
        $ehPdj = $programaEdital === 1;
        $anexosChaves = [];
        foreach ((array)$inscricao->anexos as $anexo) {
            $tipoId = (int)($anexo->anexos_tipo_id ?? 0);
            $meta = $tiposMap[$tipoId] ?? ['nome' => 'Anexo #' . $tipoId, 'bloco' => 'OUTROS'];
            $bloco = strtoupper((string)$meta['bloco']);
            if (in_array($tipoId, [13, 20], true) || ($ehPdj && $tipoId === 12)) {
                $bloco = 'S';
            } elseif (!array_key_exists($bloco, $anexosPorBloco)) {
                $bloco = 'OUTROS';
            }
            $anexosPorBloco[$bloco][] = [
                'tipo_id' => $tipoId,
                'tipo_nome' => (string)$meta['nome'],
                'arquivo' => (string)($anexo->anexo ?? ''),
                'created' => $anexo->created ?? null,
                'usuario_nome' => (string)($anexo->usuario->nome ?? ''),
            ];
            $anexosChaves[$tipoId . '|' . (string)($anexo->anexo ?? '')] = true;
        }

        // Regras da aba Projeto:
        // - tipos do bloco P diferentes de 5: listar anexos da inscrição atual (projeto_id + projeto_bolsista_id);
        // - tipo 5: trazer apenas o mais recente não deletado, baseado somente no projeto_id.
        $tiposProjetoIds = [];
        foreach ($tiposMap as $tipoId => $meta) {
            if (strtoupper((string)($meta['bloco'] ?? '')) === 'P') {
                $tiposProjetoIds[] = (int)$tipoId;
            }
        }
        if (!empty($inscricao->projeto_id) && !empty($tiposProjetoIds)) {
            $anexosPorBloco['P'] = [];
            $anexosProjetoPorTipo = [];

            $anexoTipo5 = $this->fetchTable('Anexos')->find()
                ->contain(['Usuarios'])
                ->where([
                    'Anexos.deleted IS' => null,
                    'Anexos.anexos_tipo_id' => 5,
                    'Anexos.projeto_id' => (int)$inscricao->projeto_id,
                ])
                ->orderBy(['Anexos.created' => 'DESC', 'Anexos.id' => 'DESC'])
                ->first();

            if ($anexoTipo5) {
                $tipoId = 5;
                $meta = $tiposMap[$tipoId] ?? ['nome' => 'Anexo #5'];
                $anexosProjetoPorTipo[$tipoId] = [
                    'tipo_id' => $tipoId,
                    'tipo_nome' => (string)$meta['nome'],
                    'arquivo' => (string)($anexoTipo5->anexo ?? ''),
                    'created' => $anexoTipo5->created ?? null,
                    'usuario_nome' => (string)($anexoTipo5->usuario->nome ?? ''),
                    'inscricao_origem_id' => null,
                ];
            }

            $tiposProjetoNao5 = array_values(array_filter($tiposProjetoIds, static fn($id) => (int)$id !== 5));
            if (!empty($tiposProjetoNao5)) {
                $anexosProjetoAtivos = $this->fetchTable('Anexos')->find()
                    ->contain(['Usuarios'])
                    ->where([
                        'Anexos.deleted IS' => null,
                        'Anexos.projeto_id' => (int)$inscricao->projeto_id,
                        'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                        'Anexos.anexos_tipo_id IN' => $tiposProjetoNao5,
                    ])
                    ->orderBy([
                        'Anexos.anexos_tipo_id' => 'ASC',
                        'Anexos.created' => 'DESC',
                        'Anexos.id' => 'DESC',
                    ])
                    ->all();
                foreach ($anexosProjetoAtivos as $anexoProjeto) {
                    $tipoId = (int)($anexoProjeto->anexos_tipo_id ?? 0);
                    if ($tipoId <= 0 || isset($anexosProjetoPorTipo[$tipoId])) {
                        continue;
                    }
                    $meta = $tiposMap[$tipoId] ?? ['nome' => 'Anexo #' . $tipoId];
                    $origemInscricaoId = !empty($anexoProjeto->projeto_bolsista_id)
                        ? (int)$anexoProjeto->projeto_bolsista_id
                        : null;
                    $anexosProjetoPorTipo[$tipoId] = [
                        'tipo_id' => $tipoId,
                        'tipo_nome' => (string)$meta['nome'],
                        'arquivo' => (string)($anexoProjeto->anexo ?? ''),
                        'created' => $anexoProjeto->created ?? null,
                        'usuario_nome' => (string)($anexoProjeto->usuario->nome ?? ''),
                        'inscricao_origem_id' => $origemInscricaoId,
                    ];
                }
            }

            sort($tiposProjetoIds);
            foreach ($tiposProjetoIds as $tipoEsperadoId) {
                $tipoEsperadoId = (int)$tipoEsperadoId;
                if (isset($anexosProjetoPorTipo[$tipoEsperadoId])) {
                    $anexosPorBloco['P'][] = $anexosProjetoPorTipo[$tipoEsperadoId];
                    continue;
                }
                $meta = $tiposMap[$tipoEsperadoId] ?? ['nome' => 'Anexo #' . $tipoEsperadoId];
                $anexosPorBloco['P'][] = [
                    'tipo_id' => $tipoEsperadoId,
                    'tipo_nome' => (string)$meta['nome'],
                    'arquivo' => '',
                    'created' => null,
                    'usuario_nome' => '',
                    'inscricao_origem_id' => null,
                ];
            }
        }

        $anexosProjetoTipos924 = [];
        $anexos924 = $this->fetchTable('Anexos')->find()
            ->contain(['Usuarios'])
            ->where([
                'Anexos.deleted IS' => null,
                'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                'Anexos.anexos_tipo_id IN' => [9, 24],
            ])
            ->orderBy([
                'Anexos.anexos_tipo_id' => 'ASC',
                'Anexos.created' => 'DESC',
                'Anexos.id' => 'DESC',
            ])
            ->all();
        foreach ($anexos924 as $anexo924) {
            $tipoId = (int)($anexo924->anexos_tipo_id ?? 0);
            $meta = $tiposMap[$tipoId] ?? ['nome' => 'Anexo #' . $tipoId];
            $anexosProjetoTipos924[] = [
                'tipo_id' => $tipoId,
                'tipo_nome' => (string)$meta['nome'],
                'arquivo' => (string)($anexo924->anexo ?? ''),
                'created' => $anexo924->created ?? null,
                'usuario_nome' => (string)($anexo924->usuario->nome ?? ''),
            ];
        }
        $anexosSubprojetoTela = [];
        $chavesSubprojeto = [];
        foreach ((array)$anexosPorBloco['S'] as $anexoS) {
            $chave = ((int)($anexoS['tipo_id'] ?? 0)) . '|' . (string)($anexoS['arquivo'] ?? '');
            if (isset($chavesSubprojeto[$chave])) {
                continue;
            }
            $anexosSubprojetoTela[] = $anexoS;
            $chavesSubprojeto[$chave] = true;
        }
        foreach ((array)$anexosPorBloco['P'] as $anexoP) {
            $tipoId = (int)($anexoP['tipo_id'] ?? 0);
            if (!in_array($tipoId, [12, 13], true)) {
                continue;
            }
            $chave = $tipoId . '|' . (string)($anexoP['arquivo'] ?? '');
            if (isset($chavesSubprojeto[$chave])) {
                continue;
            }
            $anexosSubprojetoTela[] = $anexoP;
            $chavesSubprojeto[$chave] = true;
        }

        // O tipo 14 (relatório final do cancelamento) deve aparecer na aba Subprojeto/Relatório
        // independentemente do bloco de origem no cadastro de tipos de anexo.
        foreach ($anexosPorBloco as $blocoAnexo) {
            foreach ((array)$blocoAnexo as $anexoItem) {
                $tipoId = (int)($anexoItem['tipo_id'] ?? 0);
                if ($tipoId !== 14) {
                    continue;
                }
                $chave = $tipoId . '|' . (string)($anexoItem['arquivo'] ?? '');
                if (isset($chavesSubprojeto[$chave])) {
                    continue;
                }
                $anexosSubprojetoTela[] = $anexoItem;
                $chavesSubprojeto[$chave] = true;
            }
        }
        usort($anexosSubprojetoTela, static function (array $a, array $b): int {
            return ((int)($a['tipo_id'] ?? 0)) <=> ((int)($b['tipo_id'] ?? 0));
        });

        $relatorioFinalAtual = null;
        foreach ((array)$inscricao->anexos as $anexo) {
            if ((int)($anexo->anexos_tipo_id ?? 0) === 14) {
                $relatorioFinalAtual = $anexo;
                break;
            }
        }

        $podeGerenciarRelatorioFinal = empty($inscricao->deleted)
            && !empty($inscricao->data_fim)
            && in_array((int)($inscricao->fase_id ?? 0), [13, 14], true)
            && (
                (int)($identity->id ?? 0) === (int)($inscricao->orientador ?? 0)
                || !empty($identity->yoda)
            );
        $podeEnviarNovoRelatorioFinal = $podeGerenciarRelatorioFinal && $relatorioFinalAtual === null;

        $historicos = $this->fetchTable('SituacaoHistoricos')->find()
            ->contain(['Usuarios', 'FaseOriginal', 'FaseAtual'])
            ->where(['SituacaoHistoricos.projeto_bolsista_id' => (int)$inscricao->id])
            ->orderBy([
                'SituacaoHistoricos.created' => 'DESC',
                'SituacaoHistoricos.id' => 'DESC',
            ])
            ->all();

        $avaliacoes = $this->fetchTable('AvaliadorBolsistas')->find()
            ->contain(['Avaliadors' => ['Usuarios']])
            ->where([
                'AvaliadorBolsistas.bolsista' => (int)$inscricao->id,
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
            'F' => 'Finalizado',
            'E' => 'Aguardando avaliação',
        ];

        $origemAtual = strtoupper((string)($inscricao->origem ?? ''));
        $controllerFluxo = $origemAtual === 'R' ? 'Renovacoes' : 'Inscricoes';

        $this->set(compact(
            'inscricao',
            'edital',
            'anexosPorBloco',
            'anexosProjetoTipos924',
            'anexosSubprojetoTela',
            'relatorioFinalAtual',
            'podeGerenciarRelatorioFinal',
            'podeEnviarNovoRelatorioFinal',
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

    public function uploadRelatorioFinal($inscricaoId = null)
    {
        $this->request->allowMethod(['post', 'put', 'patch']);

        $identity = $this->identityLogado;
        if (empty($inscricaoId)) {
            $this->Flash->error('Inscrição não informada.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'T']);
        }

        $inscricao = $this->fetchTable('ProjetoBolsistas')->find()
            ->where(['ProjetoBolsistas.id' => (int)$inscricaoId])
            ->first();

        if (!$inscricao) {
            $this->Flash->error('Inscrição não localizada.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'T']);
        }

        $podeGerenciarRelatorioFinal = empty($inscricao->deleted)
            && !empty($inscricao->data_fim)
            && in_array((int)($inscricao->fase_id ?? 0), [13, 14], true)
            && (
                (int)($identity->id ?? 0) === (int)($inscricao->orientador ?? 0)
                || !empty($identity->yoda)
            );

        if (!$podeGerenciarRelatorioFinal) {
            $this->Flash->error('Você não possui permissão para enviar o relatório final desta bolsa.');
            return $this->redirect(['action' => 'visualizar', (int)$inscricao->id]);
        }

        $relatorioFinalAtual = $this->fetchTable('Anexos')->find()
            ->where([
                'Anexos.projeto_bolsista_id' => (int)$inscricao->id,
                'Anexos.anexos_tipo_id' => 14,
                'Anexos.deleted IS' => null,
            ])
            ->first();
        if ($relatorioFinalAtual) {
            $this->Flash->error('Já existe um relatório final anexado para esta bolsa.');
            return $this->redirect(['action' => 'visualizar', (int)$inscricao->id, '#' => 'bloco-relatorio-final']);
        }

        $arquivoRelatorio = $this->request->getData('relatorio_final');
        if (!is_object($arquivoRelatorio) || $arquivoRelatorio->getClientFilename() === '') {
            $this->Flash->error('Selecione um arquivo para o relatório final.');
            return $this->redirect(['action' => 'visualizar', (int)$inscricao->id, '#' => 'bloco-relatorio-final']);
        }

        $okAnexo = $this->anexarInscricao(
            [14 => $arquivoRelatorio],
            !empty($inscricao->projeto_id) ? (int)$inscricao->projeto_id : null,
            (int)$inscricao->id,
            null,
            false
        );

        if ($okAnexo) {
            $this->Flash->success('Relatório final enviado com sucesso.');
        } else {
            $this->Flash->error('Não foi possível enviar o relatório final.');
        }

        return $this->redirect(['action' => 'visualizar', (int)$inscricao->id, '#' => 'bloco-relatorio-final']);
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
            $this->Flash->error('Inscricao não localizada ou não possui acesso para cancelamento.');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'V']);
        }

        if ($inscricao->deleted !== null) {
            $this->Flash->error('Registro inativado. não e possivel cancelar.');
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
                $erros[] = 'Anexe o relatorio final ou marque que não enviara neste momento.';
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
                                'ProjetoBolsistas.deleted IS' => null,
                            ])
                            ->orderBy(['ProjetoBolsistas.id' => 'DESC'])
                            ->first();
                        if ($renovacaoEmAndamento) {
                            $faseRenovacao = (int)$renovacaoEmAndamento->fase_id;
                            $renovacaoPatch = $tblProjetoBolsistas->patchEntity($renovacaoEmAndamento, [
                                'deleted' => date('Y-m-d H:i:s'),
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
                    'não foi possivel solicitar o cancelamento.'
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

    public function deletar($inscricaoId = null)
    {
        $identity = $this->identityLogado;
        if (!$this->ehTi()) {
            $this->Flash->error('Somente TI pode deletar.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        if (empty($inscricaoId)) {
            $this->Flash->error('Inscricao não informada para exclusao.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $inscricao = $tblProjetoBolsistas->find()
            ->contain(['Bolsistas', 'Orientadores', 'Editais'])
            ->where(['ProjetoBolsistas.id' => (int)$inscricaoId])
            ->first();

        if (!$inscricao) {
            $this->Flash->error('Inscricao não localizada para exclusao.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $erros = [];
        if ($inscricao->deleted !== null) {
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
                        'deleted' => date('Y-m-d H:i:s'),
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
                                'ProjetoBolsistas.deleted IS' => null,
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
                                'ProjetoBolsistas.deleted IS' => null,
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
                    'não foi possivel concluir a exclusao.'
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
            $this->Flash->error('Inscricao não informada.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $inscricao = $tblProjetoBolsistas->find()
            ->where(['ProjetoBolsistas.id' => (int)$inscricaoId])
            ->first();
        if (!$inscricao) {
            $this->Flash->error('Inscricao não localizada.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $faseOriginal = (int)$inscricao->fase_id;
        $erros = [];
        if ($inscricao->deleted !== null) {
            $erros[] = 'O registro esta inativado. não pode ser alterado.';
        }
        if ($faseOriginal === 14) {
            $contaSubstituto = $tblProjetoBolsistas->find()
                ->where([
                    'ProjetoBolsistas.bolsista_anterior' => (int)$inscricao->id,
                    'ProjetoBolsistas.deleted IS' => null,
                ])
                ->count();
            if ($contaSubstituto > 0) {
                $erros[] = 'E necessario primeiro inativar/cancelar a inscricao indicada para substituir esta.';
            }
        }

        $textoDataFim = !empty($inscricao->data_fim)
            ? 'A data de finalizacao da bolsa cadastrada (' . $inscricao->data_fim->i18nFormat('yyyy-MM-dd') . ') foi removida. O bolsista esta vigente novamente.'
            : 'não havia data de finalizacao registrada. O bolsista esta vigente novamente.';

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
                    'não foi possivel gravar a reativacao.'
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
            $this->Flash->error('Inscricao não informada.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $tblProjetoBolsistas = $this->fetchTable('ProjetoBolsistas');
        $inscricao = $tblProjetoBolsistas->find()
            ->contain(['Projetos'])
            ->where(['ProjetoBolsistas.id' => (int)$inscricaoId])
            ->first();
        if (!$inscricao) {
            $this->Flash->error('Inscricao não localizada.');
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

                    $nova->orientador = $orientadorDestino;
                    $nova->coorientador = null;
                    $nova->origem = $inscricao->origem;
                    $nova->data_inicio = date('Y-m-d H:i:s');
                    $nova->fase_id = 11;
                    $nova->troca_projeto = 0; // so e um se for alteração de projeto
                    $nova->heranca = 1;
                    $nova->vigente = 1;
                    $nova->deleted = null;
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
                        //'origem' => 'T',
                        'vigente' => 0,
                        'fase_id' => 20,
                        'data_fim' => date('Y-m-d H:i:s'),
                    ]);
                    $salvouAntiga = $tblProjetoBolsistas->save($inscricaoPatch);
                    if (!$salvouAntiga) {
                        throw new \RuntimeException('Erro ao atualizar a inscricao original.');
                    }
                    $this->historico(
                        (int)$inscricao->id,
                        $faseAntiga,
                        20,
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
