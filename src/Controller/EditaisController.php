<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Mailer\Mailer;
use Cake\Mailer\MailerAwareTrait;
use DateTime;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\FrozenTime; 
use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;



class EditaisController extends AppController
{
    use MailerAwareTrait;

    protected array $origem = [];
    protected array $editaisWksMap = [];
    protected $Questions;
    protected $EditaisPrazos;
    protected $EditaisWks;
    
    public function initialize(): void
    {
        parent::initialize();

        $this->origem = [
            'N' => 'Nova',
            'R' => 'Renovação',
            'V' => 'Raic'
        ];  
        
         
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        if (!$this->ehTi()) {
            $this->Flash->error('Acesso restrito à TI.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $this->Questions = $this->fetchTable('Questions');
        $this->EditaisPrazos = $this->fetchTable('EditaisPrazos');
        $this->EditaisWks = $this->fetchTable('EditaisWks');
       

    }

    //ok
    private function normalizeDate(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return parent::acerta($value);
    }

    //ok
    public function lista($limpar=false)
    {
        $this->viewBuilder()->setLayout('admin');

        $session = $this->request->getSession();
        if ($limpar) {
            $session->delete('busca_editais');
        }

        $w = [];
        $busca = $session->read('busca_editais', $w);

        $programas = $this->Editais->Programas->find('list', ['limit' => 100]);


        if ($this->request->is(['post', 'put', 'patch'])) {

            $dados = $this->request->getData();
           
            if(!empty($dados['programa_id'])){
                $w['Editais.programa_id'] = (int)$dados['programa_id'];
            }
            if(!empty($dados['origem'])){
                $w['Editais.origem'] = $dados['origem'];
            }
            if (isset($dados['ativo']) && $dados['ativo'] !== '') {
                $w['Editais.deleted'] = $dados['ativo'] === '1' ? 0 : 1;
            }

           
            $session->write('busca_editais', $w);
        } else {
            $w = $busca;
        }

        $edital = $this->Editais->find()
            ->contain(['Programas'])
            ->where($w)
            ->order(['Editais.id' => 'DESC']);
        $editais = $this->paginate($edital, ['limit'=>10]);

        $editaisAtivos = $this->Editais->find('list', [
            'keyField' => 'id',
            'valueField' => function ($row) {
                return $row->id . ' - ' . ($row->nome ?: 'Sem nome');
            },
        ])
            ->where(['Editais.deleted' => 0])
            ->order(['Editais.id' => 'DESC'])
            ->toArray();

        $origem = $this->origem;
        $this->set(compact('editais', 'origem', 'programas', 'editaisAtivos'));
    }

    public function modeloLote()
    {
        $this->request->allowMethod(['post']);

        $camposPermitidos = [
            'modelo_cons_bols' => 'Modelo consentimento bolsista',
            'modelo_cons_coor' => 'Modelo consentimento coorientador',
            'modelo_relat_bols' => 'Modelo relatório bolsista',
        ];

        $dados = $this->request->getData();
        $campo = $dados['tipo_modelo'] ?? null;
        $idsSelecionados = array_filter(array_map('intval', (array)($dados['editais_ids'] ?? [])));

        if (empty($camposPermitidos[$campo])) {
            $this->Flash->error('Tipo de arquivo inválido.');
            return $this->redirect(['action' => 'lista']);
        }

        if (empty($idsSelecionados)) {
            $this->Flash->error('Selecione ao menos um edital para aplicar o arquivo.');
            return $this->redirect(['action' => 'lista']);
        }

        $upload = ['arquivo_modelo_lote' => $dados['arquivo_modelo_lote'] ?? null];
        $upload = $this->handleUpload($upload, 'arquivo_modelo_lote', 'editais');
        $arquivo = $upload['arquivo_modelo_lote'] ?? null;

        if (empty($arquivo)) {
            $this->Flash->error('Selecione um arquivo válido para upload.');
            return $this->redirect(['action' => 'lista']);
        }

        $idsValidos = $this->Editais->find()
            ->select(['id'])
            ->where([
                'Editais.id IN' => $idsSelecionados,
                'Editais.deleted' => 0,
            ])
            ->enableHydration(false)
            ->all()
            ->extract('id')
            ->toList();

        if (empty($idsValidos)) {
            $this->Flash->error('Nenhum edital ativo foi encontrado entre os selecionados.');
            return $this->redirect(['action' => 'lista']);
        }

        $this->Editais->updateAll(
            [$campo => $arquivo],
            ['Editais.id IN' => $idsValidos]
        );

        $this->Flash->success('Arquivo aplicado em ' . count($idsValidos) . ' edital(is) com sucesso.');
        return $this->redirect(['action' => 'lista']);
    }

    //ok
    public function gravar($id = null)
    {
        $this->viewBuilder()->setLayout('admin');

        $isEdicao = (bool)$id;
        if ($id) {
            $edital = $this->Editais->find()
                ->contain([
                    /*'Unidades', 'Vinculos', */'Usuarios'])
                ->where(['Editais.id' => $id])->first();
            if ($edital && (int)$edital->deleted === 1) {
                $this->Flash->error('Edital excluído. Não é possível editar.');
                return $this->redirect(['action' => 'lista']);
            }
        } else {
            $edital = $this->Editais->newEmptyEntity();
        }

        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();
            $edital = $this->prepareEdital($edital, $dados);

            if (empty($edital->arquivo)) {
                $this->Flash->error('O arquivo do edital nao pode ficar vazio.');
                return $this->redirect($this->request->getRequestTarget());
            }

            if ($this->Editais->save($edital)) {
                $this->Flash->success('Edital gravado com sucesso');

                return $this->redirect(['action' => 'lista']);
            }
            $this->Flash->error('Houve um erro. Tente Novamente');
        }

        $origem = $this->origem;
        $programas = $this->Editais->Programas->find('list', ['limit' => 100]);
        $unidades = $this->fetchTable('Unidades')->find('list', [
            'limit' => 220,
            'keyField' => 'id',
            'valueField' => 'sigla',
        ])->where(['Unidades.deleted' => 0])->orderBy(['Unidades.sigla' => 'ASC']);
        $vinculos = $this->fetchTable('Vinculos')->find('list', [
            'limit' => 220,
            'keyField' => 'id',
            'valueField' => 'nome',
        ])->where(['Vinculos.deleted' => 0])->orderBy(['Vinculos.nome' => 'ASC']);
        $escolaridades = $this->fetchTable('Escolaridades')->find('list', [
            'limit' => 500,
            'keyField' => 'id',
            'valueField' => 'nome',
        ])->where(['Escolaridades.id >' => 5])->orderBY(['Escolaridades.nome' => 'ASC']);

        $unidadesPermitidas = !empty($edital->unidades_permitidas) ? array_map('intval', explode(',', $edital->unidades_permitidas)) : [];
        $vinculosPermitidos = !empty($edital->vinculos_permitidos) ? array_map('intval', explode(',', $edital->vinculos_permitidos)) : [];
        $escolaridadesPermitidas = !empty($edital->escolaridades_permitidas) ? array_map('intval', explode(',', $edital->escolaridades_permitidas)) : [];

        $this->set(compact(
            'edital',
            'programas',
            'origem',
            'unidades',
            'vinculos',
            'escolaridades',
            'unidadesPermitidas',
            'vinculosPermitidos',
            'escolaridadesPermitidas',
            'isEdicao'
        ));
        $this->render('gravar');
    }

    //ok chamado pr gravar
    private function prepareEdital($edital, array $dados)
    {
        $dados = $this->handleUpload($dados, 'arquivo', 'editais');
        $dados = $this->handleUpload($dados, 'modelo_cons_bols', 'editais');
        $dados = $this->handleUpload($dados, 'modelo_cons_coor', 'editais');
        $dados = $this->handleUpload($dados, 'modelo_relat_bols', 'editais');

        if (isset($dados['inicio_inscricao'])) {
            $edital->inicio_inscricao = $dados['inicio_inscricao'];
        }
        if (isset($dados['fim_inscricao'])) {
            $edital->fim_inscricao = $dados['fim_inscricao'];
            $edital->p_nova = $dados['fim_inscricao'];
        }

        if (!empty($dados['inicio_renovacao'])) {
            $edital->inicio_renovacao = parent::acerta($dados['inicio_renovacao']);
        }
        if (!empty($dados['fim_renovacao'])) {
            $edital->fim_renovacao = parent::acerta($dados['fim_renovacao']);
        }

        if (!empty($dados['inicio_vigencia'])) {
            $edital->inicio_vigencia = parent::acerta($dados['inicio_vigencia']);
        }
        if (!empty($dados['fim_vigencia'])) {
            $edital->fim_vigencia = parent::acerta($dados['fim_vigencia']);
        }
        if (!empty($dados['inicio_recurso'])) {
            $edital->inicio_recurso = parent::acerta($dados['inicio_recurso']);
        }
        if (!empty($dados['fim_recurso'])) {
            $edital->fim_recurso = parent::acerta($dados['fim_recurso']);
        }

        $unidadesPermitidas = $dados['unidades_permitidas_ids'] ?? [];
        $vinculosPermitidos = $dados['vinculos_permitidos_ids'] ?? [];
        $escolaridadesPermitidas = $dados['escolaridades_permitidas_ids'] ?? [];

        $edital->unidades_permitidas = !empty($dados['unidade']) && $unidadesPermitidas
            ? implode(',', $unidadesPermitidas)
            : null;
        $edital->vinculos_permitidos = !empty($dados['vinculo']) && $vinculosPermitidos
            ? implode(',', $vinculosPermitidos)
            : null;
        $edital->escolaridades_permitidas = !empty($dados['escolaridade']) && $escolaridadesPermitidas
            ? implode(',', $escolaridadesPermitidas)
            : null;

        $edital->usuario_id = $this->request->getAttribute('identity')['id'];
        $edital->deleted = 0;

        return $this->Editais->patchEntity($edital, $dados);
    }

    //ok
    public function delete($id)
    {
        $this->viewBuilder()->setLayout('admin');
        $this->request->allowMethod(['post']);

        if ($id === null) {
            $this->Flash->error('URL incorreta');
            return $this->redirect(['controller' => '/', 'action' => '/']);
        }

        $edital = $this->Editais->find()
            ->where(['Editais.id' => $id])
            ->first();

        if (!$edital) {
            $this->Flash->error('Edital não localizado');
            return $this->redirect(['action' => 'lista']);
        }

        $edital->deleted = 1;
        if ($this->Editais->save($edital)) {
            $this->Flash->success('Edital excluído com sucesso');
        } else {
            $this->Flash->error('Houve um erro ao excluir o edital');
        }

        return $this->redirect(['action' => 'lista']);
    }


    //ok
    public function lancarresultado($id)
    {
        $this->viewBuilder()->setLayout('admin');

        $edital = $this->Editais->find()
        ->contain([
            /*'Unidades', 'Vinculos', */'Usuarios'])
        ->where(['Editais.id' => $id])->first();
        
        if ($this->request->is(['post', 'put', 'patch'])) {

            $dados = $this->request->getData();
            if (!empty($dados['remover_resultado'])) {
                $dados['resultado_arquivo'] = null;
                unset($dados['remover_resultado']);
            } else {
                $dados = $this->handleUpload($dados, 'resultado_arquivo', 'editais');
            }

            $edital = $this->Editais->patchEntity($edital, $dados);
          
            if ($this->Editais->save($edital)) {
                $this->Flash->success('Resultado Lançado com sucesso');

                return $this->redirect(['action' => 'ver', $id]);
            }
            $this->Flash->error('Houve um erro. Tente Novamente');
        }
       
        $this->set(compact('edital'));
    }

    //ok
    public function ver($id)
    {
        $this->viewBuilder()->setLayout('admin');

        $edital = $this->Editais->find()
        ->contain([
            /*'Unidades', 'Vinculos', */'Usuarios', 'Questions', 'Programas', 'EditaisSumulas' => ['EditaisSumulasBlocos']])
        ->where(['Editais.id' => $id])->first();
        
        $origem = $this->origem;
        $programas = $this->fetchTable('Programas')->find('list', ['limit' => 200])->toArray();
        $unidades = [];
        $vinculos = [];
        $escolaridades = [];

        if (!empty($edital->unidades_permitidas)) {
            $unidades = $this->fetchTable('Unidades')
                ->find('all')
                ->where(['Unidades.id IN' => explode(',', $edital->unidades_permitidas)]);
        }
        if (!empty($edital->vinculos_permitidos)) {
            $vinculos = $this->fetchTable('Vinculos')
                ->find('all')
                ->where(['Vinculos.id IN' => explode(',', $edital->vinculos_permitidos)]);
        }
        if (!empty($edital->escolaridades_permitidas)) {
            $escolaridades = $this->fetchTable('Escolaridades')
                ->find('all')
                ->where(['Escolaridades.id IN' => explode(',', $edital->escolaridades_permitidas)]);
        }

        $prazos = $this->EditaisPrazos->find()
            ->contain(['EditaisWks', 'Usuarios'])
            ->where([
                'EditaisPrazos.editai_id' => (int)$edital->id,
            ])
            ->orderBy([
                'EditaisPrazos.inicio' => 'ASC',
                'EditaisPrazos.fim' => 'ASC',
                'EditaisPrazos.id' => 'ASC',
            ])
            ->all();

        $sumulasBlocos = $this->fetchTable('EditaisSumulasBlocos')
            ->find()
            ->orderBy(['EditaisSumulasBlocos.nome' => 'ASC'])
            ->all();

        $this->set(compact('escolaridades', 'edital', 'origem', 'unidades', 'vinculos', 'programas', 'prazos', 'sumulasBlocos'));

            
    }

    //-----------------------
    // inicio prazos
    //-----------------------
        //ok
        public function prazos($id)
        {
            $this->viewBuilder()->setLayout('admin');

            $edital = $this->Editais->find()
                ->where(['Editais.id' => $id])
                ->first();

            if (!$edital) {
                $this->Flash->error('Edital não localizado');
                return $this->redirect(['action' => 'lista']);
            }

            $wkOptions = $this->EditaisWks->find('list')
                ->orderBy(['EditaisWks.nome' => 'ASC'])
                ->toArray();

            if ($this->request->is(['post', 'put', 'patch'])) {
                $dados = $this->request->getData('prazos') ?? [];
                $rows = [];
                foreach ($dados as $row) {
                    $wkId = isset($row['editais_wk_id']) ? (int)$row['editais_wk_id'] : null;
                    if (!$wkId) {
                        continue;
                    }
                    $inicio = $this->normalizeDate($row['inicio'] ?? null);
                    $fim = $this->normalizeDate($row['fim'] ?? null);
                    $usuarioId = isset($row['usuario_id']) ? (int)$row['usuario_id'] : null;
                    $cpf = $row['cpf'] ?? null;
                    $inscricao = $row['inscricao'] ?? null;
                    $tabela = isset($row['tabela']) ? strtoupper(trim((string)$row['tabela'])) : null;

                    if (!empty($inscricao) && !in_array($tabela, ['I', 'J'], true)) {
                        $this->Flash->error('Informe o tipo (I ou J) quando houver inscrição.');
                        return $this->redirect($this->referer());
                    }

                    $rows[] = [
                        'editai_id' => $edital->id,
                        'usuario_id' => $usuarioId ?: null,
                        'editais_wk_id' => $wkId ?: null,
                        'inicio' => $inicio ?: null,
                        'fim' => $fim ?: null,
                        'cpf' => $cpf ?: null,
                        'inscricao' => $inscricao ?: null,
                        'tabela' => $tabela ?: null,
                    ];
                }

                if (empty($rows)) {
                    $this->Flash->error('Nenhum prazo válido para cadastrar.');
                    return $this->redirect($this->referer());
                }

                $entities = $this->EditaisPrazos->newEntities($rows);
                if ($this->EditaisPrazos->saveMany($entities)) {
                    $this->Flash->success('Prazos cadastrados com sucesso.');
                    return $this->redirect(['action' => 'ver', $edital->id]);
                }

                $this->Flash->error('Houve um erro ao cadastrar os prazos.');
            }

            $this->set(compact('edital', 'wkOptions'));
        }

        //ok
        public function prazodelete($id)
        {
            $this->request->allowMethod(['post', 'get']);

            $prazo = $this->EditaisPrazos->find()
                ->where(['EditaisPrazos.id' => $id])
                ->first();

            if (!$prazo) {
                $this->Flash->error('Prazo não localizado');
                return $this->redirect($this->referer());
            }

            $prazo->deleted = FrozenTime::now();
            if ($this->EditaisPrazos->save($prazo)) {
                $this->Flash->success('Prazo excluído com sucesso');
            } else {
                $this->Flash->error('Houve um erro ao excluir o prazo');
            }

            return $this->redirect(['action' => 'ver', $prazo->editai_id]);
        }
    //-----------------------
    // inicio prazos
    //-----------------------


    //-----------------------
    // inicio sumulas bloco
    //-----------------------
        public function sumulasblocosadd()
        {
            $this->request->allowMethod(['post']);

                $dados = $this->request->getData();
                $nome = trim((string)($dados['nome'] ?? ''));
                if ($nome === '') {
                    $this->Flash->error('Informe o nome do bloco.');
                    return $this->redirect($this->referer());
                }

                $bloco = $this->fetchTable('EditaisSumulasBlocos')->newEmptyEntity();
                $bloco->nome = $nome;

                if ($this->fetchTable('EditaisSumulasBlocos')->save($bloco)) {
                    $this->Flash->success('Bloco cadastrado com sucesso.');
                } else {
                    $this->Flash->error('Houve um erro ao cadastrar o bloco.');
                }

            return $this->redirect($this->referer());
        }

        public function sumulasblocos()
        {
            $this->viewBuilder()->setLayout('admin');

            $sumulasBlocos = $this->fetchTable('EditaisSumulasBlocos')
                ->find()
                ->orderBy(['EditaisSumulasBlocos.nome' => 'ASC'])
                ->all();

            $this->set(compact('sumulasBlocos'));
        }

        public function sumulasblocosedit($id)
        {
        $this->viewBuilder()->setLayout('admin');

            $bloco = $this->fetchTable('EditaisSumulasBlocos')->find()
                ->where(['EditaisSumulasBlocos.id' => $id])
                ->first();

            if (!$bloco) {
                $this->Flash->error('Bloco não localizado.');
                return $this->redirect($this->referer());
            }

            if ($this->request->is(['post', 'put', 'patch'])) {
                $dados = $this->request->getData();
                $nome = trim((string)($dados['nome'] ?? ''));
                if ($nome === '') {
                    $this->Flash->error('Informe o nome do bloco.');
                    return $this->redirect($this->referer());
                }
                $bloco->nome = $nome;

            if ($this->fetchTable('EditaisSumulasBlocos')->save($bloco)) {
                $this->Flash->success('Bloco atualizado com sucesso.');
                return $this->redirect(['action' => 'sumulasblocos']);
            }
                $this->Flash->error('Houve um erro ao atualizar o bloco.');
            }

            $this->set(compact('bloco'));
        }

        public function sumulasblocosdelete($id)
        {
            $this->request->allowMethod(['post', 'get']);

            $bloco = $this->fetchTable('EditaisSumulasBlocos')->find()
                ->where(['EditaisSumulasBlocos.id' => $id])
                ->first();

            if (!$bloco) {
                $this->Flash->error('Bloco não localizado.');
                return $this->redirect($this->referer());
            }

            $bloco->deleted = FrozenTime::now();
            if ($this->fetchTable('EditaisSumulasBlocos')->save($bloco)) {
                $this->Flash->success('Bloco excluído com sucesso.');
            } else {
                $this->Flash->error('Houve um erro ao excluir o bloco.');
            }

            return $this->redirect($this->referer());
        }
    //-----------------------
    // fim sumulas bloco
    //-----------------------

    //-----------------------
    // inicio sumulas
    //-----------------------
        public function sumulasadd($ed)
        {
            $this->viewBuilder()->setLayout('admin');

            $edital = null;
            if ($ed !== null) {
                $edital = $this->Editais->find()
                    ->where(['Editais.id' => $ed])
                    ->first();
            }

            if (!$edital) {
                $this->Flash->error('URL incorreta');
                return $this->redirect(['controller' => '/', 'action' => '/']);
            }

            $blocos = $this->fetchTable('EditaisSumulasBlocos')
                ->find('list')
                ->where(['EditaisSumulasBlocos.deleted IS' => null])
                ->orderBy(['EditaisSumulasBlocos.nome' => 'ASC'])
                ->toArray();

            if ($this->request->is('post')) {
                $dados = $this->request->getData('sumulas') ?? [];
                $rows = [];

                foreach ($dados as $item) {
                    $sumula = trim((string)($item['sumula'] ?? ''));
                    $parametro = trim((string)($item['parametro'] ?? ''));
                    $blocoId = $item['editais_sumula_bloco_id'] ?? '';

                    $allEmpty = $sumula === '' && $parametro === '' && $blocoId === '';
                    if ($allEmpty) {
                        continue;
                    }
                    if ($sumula === '' || $parametro === '' || $blocoId === '') {
                        $this->Flash->error('Preencha todos os campos da súmula antes de salvar.');
                        return $this->redirect($this->referer());
                    }

                    $item['sumula'] = $sumula;
                    $item['parametro'] = $parametro;
                    $rows[] = $item;
                }

                if (empty($rows)) {
                    $this->Flash->error('Nenhuma súmula válida para cadastrar.');
                    return $this->redirect($this->referer());
                }

                try {
                    $sumulasTable = $this->fetchTable('EditaisSumulas');
                    $connection = $sumulasTable->getConnection();
                    $connection->transactional(function () use ($rows, $edital, $sumulasTable) {
                        foreach ($rows as $item) {
                            $sumula = $sumulasTable->newEmptyEntity();
                            $sumula->editai_id = $edital->id;

                            $sumula = $sumulasTable->patchEntity($sumula, $item);

                            if (!$sumulasTable->save($sumula)) {
                                throw new \Exception('Erro ao salvar uma das súmulas.');
                            }
                        }

                        $this->Flash->success('As súmulas foram salvas com sucesso.');
                        return $this->redirect(['controller' => 'Editais', 'action' => 'ver', $edital->id]);
                    });
                } catch (\Throwable $e) {
                    $info = $this->ExceptionReporter->report($e, 'Erro no Sistema - Cadastro de súmulas');
                    $suffix = '';
                    if (!empty($info['id'])) {
                        $suffix = '<br>Codigo: Erro #' . $info['id'];
                        if (!empty($info['repeticao'])) {
                            $rep = $info['repeticoes'] ?? null;
                            $repText = $rep ? ' (' . $rep . 'a tentativa)' : '';
                            $suffix .= '<br>Repeticao detectada' . $repText . '. Esse erro ja foi registrado.';
                            $suffix .= '<br>Contate a gestao ou TI, abra um chamado e aguarde instrucoes.';
                        } else {
                            $suffix .= '<br>Registro novo.';
                        }
                    }
                    $mensagem = 'Houve um erro na gravacao. Tente novamente.' . $suffix;
                    if (!empty($info['bloqueado'])) {
                        $limite = (int)($info['limite_repeticao'] ?? 12);
                        $mensagem .= '<br>O sr(a) ja cometeu esse erro ' . $limite . ' vezes.';
                        $mensagem .= ' Verifique seu email para ver se ha resposta e aguarde orientacoes.';
                    }
                    $this->Flash->error($mensagem, ['escape' => false]);
                    return $this->redirect($this->referer());
                }
            }

            $this->set(compact('edital', 'blocos'));
        }

        public function sumulasedit($id)
        {
            $this->viewBuilder()->setLayout('admin');

            $sumulasTable = $this->fetchTable('EditaisSumulas');
            $sumula = $id ? $sumulasTable->find()
                ->contain(['EditaisSumulasBlocos'])
                ->where(['EditaisSumulas.id' => $id])
                ->first() : null;

            if (!$sumula) {
                $this->Flash->error('URL incorreta');
                return $this->redirect(['controller' => '/', 'action' => '/']);
            }

            $blocos = $this->fetchTable('EditaisSumulasBlocos')
                ->find('list')
                ->where(['EditaisSumulasBlocos.deleted IS' => null])
                ->orderBy(['EditaisSumulasBlocos.nome' => 'ASC'])
                ->toArray();

            if ($this->request->is(['post', 'put', 'patch'])) {
                $dados = $this->request->getData();

                try {
                    $connection = $sumulasTable->getConnection();
                    $connection->transactional(function () use ($dados, $sumula, $sumulasTable) {
                        $sumula = $sumulasTable->patchEntity($sumula, $dados);

                        if (!$sumulasTable->save($sumula)) {
                            throw new \Exception('Erro ao salvar uma das súmulas.');
                        }

                        $this->Flash->success('As súmulas foram salvas com sucesso.');
                        return $this->redirect(['controller' => 'Editais', 'action' => 'ver', $sumula->editai_id]);
                    });
                } catch (\Throwable $e) {
                    $info = $this->ExceptionReporter->report($e, 'Erro no Sistema - Edição de súmula');
                    $suffix = '';
                    if (!empty($info['id'])) {
                        $suffix = '<br>Codigo: Erro #' . $info['id'];
                        if (!empty($info['repeticao'])) {
                            $rep = $info['repeticoes'] ?? null;
                            $repText = $rep ? ' (' . $rep . 'a tentativa)' : '';
                            $suffix .= '<br>Repeticao detectada' . $repText . '. Esse erro ja foi registrado.';
                            $suffix .= '<br>Contate a gestao ou TI, abra um chamado e aguarde instrucoes.';
                        } else {
                            $suffix .= '<br>Registro novo.';
                        }
                    }
                    $mensagem = 'Houve um erro na gravacao. Tente novamente.' . $suffix;
                    if (!empty($info['bloqueado'])) {
                        $limite = (int)($info['limite_repeticao'] ?? 12);
                        $mensagem .= '<br>O sr(a) ja cometeu esse erro ' . $limite . ' vezes.';
                        $mensagem .= ' Verifique seu email para ver se ha resposta e aguarde orientacoes.';
                    }
                    $this->Flash->error($mensagem, ['escape' => false]);
                    return $this->redirect($this->referer());
                }
            }

            $this->set(compact('sumula', 'blocos'));
        }

        public function sumulasdelete($id)
        {
            $this->viewBuilder()->setLayout('admin');

            $sumulasTable = $this->fetchTable('EditaisSumulas');
            $sumula = $id ? $sumulasTable->find()
                ->where(['EditaisSumulas.id' => $id])
                ->first() : null;

            if (!$sumula) {
                $this->Flash->error('URL incorreta');
                return $this->redirect(['controller' => '/', 'action' => '/']);
            }

            if ($this->request->is(['post'])) {
                $sumula->deleted = FrozenTime::now();

                if ($sumulasTable->save($sumula)) {
                    $this->Flash->success('Exclusão da súmula realizada com sucesso.');
                } else {
                    $this->Flash->error('Houve um erro solicitando a exclusão, por favor, tente novamente.');
                }

                return $this->redirect(['controller' => 'Editais', 'action' => 'ver', $sumula->editai_id]);
            }
        }
    //-----------------------
    // fim sumulas
    //----------------------- 

   
    //-----------------------
    // inicio quesitos
    //-----------------------
        public function quesitosadd($ed)
        {
            $this->viewBuilder()->setLayout('admin');

            $edital=null;

            if($ed!=null){
                $edital = $this->Editais->find()
                ->where(['Editais.id' => $ed])->first();
            }else{
                $this->Flash->error('URL incorreta');
                return $this->redirect(['controller'=>'/', 'action'=>'/']);

            }

            
            if ($this->request->is('post')) {

                $dados = $this->request->getData('questions') ?? [];
                $rows = [];

                foreach ($dados as $item) {
                    $questao = trim((string)($item['questao'] ?? ''));
                    $prametros = trim((string)($item['prametros'] ?? ''));
                    $limiteMin = $item['limite_min'] ?? '';
                    $limiteMax = $item['limite_max'] ?? '';

                    $allEmpty = $questao === '' && $prametros === '' && $limiteMin === '' && $limiteMax === '';
                    if ($allEmpty) {
                        continue;
                    }
                    if ($questao === '' || $prametros === '' || $limiteMin === '' || $limiteMax === '') {
                        $this->Flash->error('Preencha todos os campos do quesito antes de salvar.');
                        return $this->redirect($this->referer());
                    }

                    $item['questao'] = $questao;
                    $item['prametros'] = $prametros;
                    $rows[] = $item;
                }

                if (empty($rows)) {
                    $this->Flash->error('Nenhum quesito válido para cadastrar.');
                    return $this->redirect($this->referer());
                }

                try {

                    $connection = $this->Questions->getConnection();
                    $connection->transactional(function () use ($rows, $edital) {

                        
                        foreach ($rows as $item) {
                            $quesito = $this->Questions->newEmptyEntity();
        
                            // Valores fixos
                            $quesito->ano = date('Y');
                            $quesito->tipo = $edital->origem;
                            $quesito->editai_id = $edital->id;
        
                            $quesito = $this->Questions->patchEntity($quesito, $item);
        
                            if (!$this->Questions->save($quesito)) {
                                throw new \Exception('Erro ao salvar um dos quesitos.');
                            }
                        }

                    

                        $this->Flash->success('Os quesitos foram salvos com sucesso');

                        return $this->redirect(['controller' => 'Editais', 'action' => 'ver', $edital->id]);

                    });
                
                } catch (\Throwable $e) {
                    $info = $this->ExceptionReporter->report($e, 'Erro no Sistema - Cadastro de quesitos');
                    $suffix = '';
                    if (!empty($info['id'])) {
                        $suffix = '<br>Codigo: Erro #' . $info['id'];
                        if (!empty($info['repeticao'])) {
                            $rep = $info['repeticoes'] ?? null;
                            $repText = $rep ? ' (' . $rep . 'a tentativa)' : '';
                            $suffix .= '<br>Repeticao detectada' . $repText . '. Esse erro ja foi registrado.';
                            $suffix .= '<br>Contate a gestao ou TI, abra um chamado e aguarde instrucoes.';
                        } else {
                            $suffix .= '<br>Registro novo.';
                        }
                    }
                    $mensagem = 'Houve um erro na gravacao. Tente novamente.' . $suffix;
                    if (!empty($info['bloqueado'])) {
                        $limite = (int)($info['limite_repeticao'] ?? 12);
                        $mensagem .= '<br>O sr(a) ja cometeu esse erro ' . $limite . ' vezes.';
                        $mensagem .= ' Verifique seu email para ver se ha resposta e aguarde orientacoes.';
                    }
                    $this->Flash->error($mensagem, ['escape' => false]);
                    return $this->redirect($this->referer());
                }

                
            }
        
            $this->set(compact('edital'));
        }

        public function quesitosedit($id)
        {
            $this->viewBuilder()->setLayout('admin');


            if($id!=null){
                $quesito = $this->Questions->find()
                ->where(['Questions.id' => $id])->first();
            }else{
                $this->Flash->error('URL incorreta');
                return $this->redirect(['controller'=>'/', 'action'=>'/']);

            }

            
            if ($this->request->is(['post', 'put', 'patch'])) {

                $dados = $this->request->getData();

                try {

                    $connection = $this->Questions->getConnection();
                    $connection->transactional(function () use ($dados, $quesito) {

                        
                        $quesito = $this->Questions->patchEntity($quesito, $dados);
        
                        if (!$this->Questions->save($quesito)) {
                            throw new \Exception('Erro ao salvar um dos quesitos.');
                        }

                    

                        $this->Flash->success('Os quesitos foram salvos com sucesso');

                        return $this->redirect(['controller' => 'Editais', 'action' => 'ver', $quesito->editai_id]);

                    });
                
                } catch (\Throwable $e) {
                    $info = $this->ExceptionReporter->report($e, 'Erro no Sistema - Edição de quesito');
                    $suffix = '';
                    if (!empty($info['id'])) {
                        $suffix = '<br>Codigo: Erro #' . $info['id'];
                        if (!empty($info['repeticao'])) {
                            $rep = $info['repeticoes'] ?? null;
                            $repText = $rep ? ' (' . $rep . 'a tentativa)' : '';
                            $suffix .= '<br>Repeticao detectada' . $repText . '. Esse erro ja foi registrado.';
                            $suffix .= '<br>Contate a gestao ou TI, abra um chamado e aguarde instrucoes.';
                        } else {
                            $suffix .= '<br>Registro novo.';
                        }
                    }
                    $mensagem = 'Houve um erro na gravacao. Tente novamente.' . $suffix;
                    if (!empty($info['bloqueado'])) {
                        $limite = (int)($info['limite_repeticao'] ?? 12);
                        $mensagem .= '<br>O sr(a) ja cometeu esse erro ' . $limite . ' vezes.';
                        $mensagem .= ' Verifique seu email para ver se ha resposta e aguarde orientacoes.';
                    }
                    $this->Flash->error($mensagem, ['escape' => false]);
                    return $this->redirect($this->referer());
                }

                
            }
        
            $this->set(compact('quesito'));
        }

        public function quesitosdelete($id)
        {
            $this->viewBuilder()->setLayout('admin');


            if($id!=null){
                $quesito = $this->Questions->find()
                ->where(['Questions.id' => $id])->first();
            }else{
                $this->Flash->error('URL incorreta');
                return $this->redirect(['controller'=>'/', 'action'=>'/']);

            }


            if($this->request->is(['post'])) {
                $quesito->deleted=1;

                if($this->Questions->save($quesito)) {
                    $this->Flash->success("Exclusão do quesito realizada com sucesso");
                    
                } else {
                    $this->Flash->error("Houve um erro solicitando a exclusão, por favor, tente novamente");
                }

                return $this->redirect(['controller' => 'Editais', 'action' => 'ver', $quesito->editai_id]);
            }
        }
    //-----------------------
    // fim quesitos
    //-----------------------

    

}
