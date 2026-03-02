<?php
namespace App\Controller;

use App\Controller\AppController;
//use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;
use Cake\Http\Exception\BadRequestException;


class UsersController extends AppController
{
    protected array $racas = [];
    protected array $documentos = [];
    protected array $sexo = [];
    protected array $deficiencia = [];
    protected $Usuarios;
    protected $UsuarioHistoricos;
    protected $UsuariosAcessos;

    
    public function initialize(): void
    {
        parent::initialize();

        $this->Usuarios = TableRegistry::getTableLocator()->get('Usuarios');
        
        
        $this->viewBuilder()->setLayout('admin');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
       
        //$this->Authentication->addUnauthenticatedActions(['login', 'loginUnico']);      
        //$this->UserHist = TableRegistry::getTableLocator()->get('UsuarioHistoricos');
     
        $this->Authentication->allowUnauthenticated([
            'login', 
            'loginUnico'
        ]);
    }

  
    

    //ok
    // tela dos filtros do user
    public function index()
    {
        $identity = $this->request->getAttribute('identity');

        if (
            !$identity['yoda'] &&
            $identity['jedi'] === null &&
            $identity['padauan'] === null
        ) {
            $this->Flash->error(
                'Restrito a administradores.',
                ['escape' => false]
            );
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }
    }

    // ok
    // busca o usuario
    public function buscar()
    {
        if (!$this->request->is('post')) {
            return $this->redirect(['action' => 'index']);
        }

        $dados = $this->request->getData();

        if (
            empty($dados['nome']) &&
            empty($dados['cpf']) &&
            empty($dados['tipo_usuario'])
        ) {
            $this->Flash->error('Impute ao menos um filtro');
            return $this->redirect(['action' => 'index']);
        }

        $conditions = [];

        if (!empty($dados['nome'])) {
            $conditions['Usuarios.nome LIKE'] =
                '%' . str_replace(' ', '%', $dados['nome']) . '%';
        }

        if (!empty($dados['cpf'])) {
            $conditions['Usuarios.cpf LIKE'] =
                '%' . preg_replace('/\D/', '', $dados['cpf']) . '%';
        }

        if (!empty($dados['tipo_usuario'])) {
            switch ($dados['tipo_usuario']) {
                case 'yoda':
                    $conditions['Usuarios.yoda'] = true;
                    break;
                case 'jedi':
                    $conditions['Usuarios.jedi IS NOT'] = null;  // não nulo
                    $conditions['Usuarios.jedi !='] = '';        // não vazio
                    break;

                case 'padauan':
                    $conditions['Usuarios.padauan IS NOT'] = null; // não nulo
                    $conditions['Usuarios.padauan !='] = '';       // não vazio
                    break;
            }
        }

        $token = bin2hex(random_bytes(6));

        $this->request->getSession()->write(
            'busca_usuarios_' . $token,
            $conditions
        );

        return $this->redirect([
            'action' => 'resultado',
            '?' => ['ref' => $token]
        ]);
    }

    //ok
    //printa o resultado da busca
    public function resultado()
    {
        $token = $this->request->getQuery('ref');

        if (!$token) {
            return $this->redirect(['action' => 'index']);
        }

        $conditions = $this->request
            ->getSession()
            ->read('busca_usuarios_' . $token);

        if (empty($conditions)) {
            $this->Flash->error('Busca expirada ou inválida');
            return $this->redirect(['action' => 'index']);
        }

        $query = $this->Usuarios
            ->find()
            ->contain(['Unidades'])
            ->where($conditions);

        $this->paginate = [
            'limit' => 10,
            'sortableFields' => [
                'Usuarios.nome',
                'Usuarios.cpf',
                'Unidades.sigla',
                'Usuarios.yoda',
                'Usuarios.jedi',
                'Usuarios.padauan'
            ]
        ];

        $usuarios = $this->paginate($query);

        $this->set(compact('usuarios', 'token'));
    } 
    
    //ok
    public function login()
    {
        //dd($this->request->getData());
        $this->viewBuilder()->setLayout('default');
        $result = $this->Authentication->getResult();
        //dd($result);
        // regardless of POST or GET, redirect if user is logged in
      if ($result && $result->isValid()) {

                $usuario = $this->Authentication->getIdentity();

                

                // regra de negócio
                if ((int)$usuario->yoda === 1) {
                    return $this->redirect([
                        'controller' => 'Index',
                        'action' => 'dashyoda'
                    ]);
                }

                // padrão
                return $this->redirect([
                    'controller' => 'Index',
                    'action' => 'dashboard'
                ]);
            }

        // display error if user submitted and authentication failed
        if ($this->request->is('post') && !$result->isValid()) {
            $this->Flash->error('Usuário ou senha inválidos');
        }
    }

    private function logaAcesso($attr) : void 
    {
        file_put_contents(ROOT . DS . "logs". DS . "acesso.log", print_r($attr, true), FILE_APPEND);
    }

    public function loginUnico()
    {
        require_once(WWW_ROOT.'/simplesamlphp/lib/_autoload.php');
        $auth =  new \SimpleSAML\Auth\Simple('wso2-sp');   
        $retorno = 'https://fomentoapesquisa.fiocruz.br/login';
            try {
                //$auth->requireAuth(['ReturnTo' => $retorno, 'KeepPost' => true]);
                $auth->requireAuth(['ReturnTo' => $retorno, 'KeepPost' => true]);
            
                if ($auth->isAuthenticated()) { 
                    $attrs = $auth->getAttributes();
                    $this->logaAcesso($attrs);
                    if(isset($attrs['cpf'][0])){
                        $cpf = preg_replace('/[^0-9]/', '', $attrs['cpf'][0]);
                        if(!(parent::validaCPF($cpf))) {
                            $this->Flash->error('CPF retornado na autenticação é invalido: '.$cpf);
                            return $this->redirect(['controller' => 'Index', 'action'=>'index']);
                        }

                        if($attrs['email'][0]!=null){
                            $tipo_acesso='F';
                        }else{
                            $tipo_acesso='G';
                        }

                        $usuario = $this->Usuarios->find('all')->where(['cpf' => $cpf])->first();
                        
                        if(!$usuario) {

                            $acao='C';

                            $new_user = $this->Usuarios->newEmptyEntity();
                            $new_user->cpf = $cpf;
                            $new_user->nome = $attrs['nome completo'][0];
                            $new_user->email = $attrs['email'][0] ?? $attrs['email alternativo'][0];
                            $new_user->email_alternativo = $attrs['email alternativo'][0] ?? null;
                            $new_user->telefone = $attrs['telefone'][0];
                            $new_user->yoda = 0;
                            $new_user->jedi = null;
                            $new_user->padauan = null;
                            $new_user->active = 1;

                            $usuario = $this->Usuarios->save($new_user);
                            if ($usuario) {
                                $diff = $this->buildUserDiff([], $usuario, $this->getUserAuditFields());
                                $this->saveUserHistory((int)$usuario->id, $diff, 'C');
                            }

                        }else{ // se o usuario ja existe
                            $acao='U';
                            $original = $usuario->toArray();

                            if (
                                ($usuario->last_data_update_date == '') || 
                                ($usuario->last_data_update_date->i18nFormat('yyyy-MM-dd HH:mm') < date('Y-m-d H:i', strtotime('6 months ago')))
                                ) { //ultima atialização manual nao existe ou tem mais de seis meses

                                $usuario->nome = $attrs['nome completo'][0];
                                $usuario->email = $attrs['email'][0]?? $attrs['email alternativo'][0];
                                $usuario->email_alternativo = $attrs['email alternativo'][0] ?? null;
                                $usuario->telefone = $attrs['telefone'][0];
                            }

                            if(isset($attrs['email'][0]) && $usuario->email != $attrs['email'][0]) {
                                $usuario->email = $attrs['email'][0] ?? $attrs['email alternativo'][0];
                            }

                            if(isset($attrs['email alternativo'][0]) && (
                                $usuario->email_alternativo != $attrs['email alternativo'][0] || 
                                $usuario->email == null || $usuario->email == ""
                            )) {
                                if($usuario->email == null || $usuario->email == ""){
                                    $usuario->email = $attrs['email alternativo'][0] ?? null;
                                } else {
                                    $usuario->email_alternativo = $attrs['email alternativo'][0] ?? null;
                                }
                            }

                            if($usuario->nome != $attrs['nome completo'][0]) {
                                $usuario->nome = $attrs['nome completo'][0];
                            }
                        }
                        
                        $usuario->ultimoLogin = $usuario->loginAtual;
                        $usuario->loginAtual = date('Y-m-d H:i:s');
                        $usuario = $this->Usuarios->save($usuario);
                        if ($usuario) {
                            $diff = $this->buildUserDiff($original ?? [], $usuario, $this->getUserAuditFields());
                            $this->saveUserHistory((int)$usuario->id, $diff, 'A', $tipo_acesso);
                        }

                        // gravação de log
                            $this->UsuariosAcessos = TableRegistry::getTableLocator()->get('UsuariosAcessos');
                            $new_acesso = $this->UsuariosAcessos->newEmptyEntity(); 

                            $new_acesso->usuario_id=$usuario->id;
                            $new_acesso->tipo_acesso=$tipo_acesso;
                            $new_acesso->nome=$attrs['nome completo'][0];
                            $new_acesso->email=$attrs['email'][0] ?? $attrs['email alternativo'][0];
                            $new_acesso->email_alternativo=$attrs['email alternativo'][0] ?? null;
                            $new_acesso->acao=$acao;

                            $acesso = $this->UsuariosAcessos->save($new_acesso);

                        //

                        /*
                        DESABILITAÇÃO DE USUÁRIOS PELO LOGIN
                        if($usuario->active != 1) {
                            $this->Flash->error('Seu usuário foi desabilitado do sistema, por favor, contate a coordenação de bolsas (pibic@fiocruz.br) para ativá-lo!');
                            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
                        }
                        */
                        $this->Authentication->setIdentity($usuario);
                        return $this->redirect(['controller' => 'dashboard']);
                    } else {
                        $this->Flash->error('Não foi possível autenticar você pelo Login Único. Por favor, entre em contato com o SAC da Fiocruz. (CPF não retornado)');
                        return $this->redirect(['action'=>'index']);
                    }
                } else {
                    $this->Flash->error('Não foi possível autenticar você. Por favor, tente novamente.');
                    return $this->redirect(['action'=>'login']);
                }
            } catch (\Exception $th) {
                $this->Flash->error('Houve um erro processando a autenticação. Por favor tente novamente. Detalhes: ' . $th->getMessage());
                return $this->redirect(['controller' => 'Index', 'action'=>'index']);
            }     
    }   

    public function logout()
    {
        $result = $this->Authentication->getResult();
        // regardless of POST or GET, redirect if user is logged in
        if ($result && $result->isValid()) {
            $this->Authentication->logout();
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }
    }

    

    public function ver($id)
    {
        $identity = $this->request->getAttribute('identity');
        $identityObj = $this->Authentication->getIdentity();

        $temYoda = !empty($identity['yoda']);
        $temJedi = $identity['jedi'] !== null && $identity['jedi'] !== '';
        $temPadauan = $identity['padauan'] !== null && $identity['padauan'] !== '';
        $ehProprioUsuario = $identity['id'] == $id;
        $podeVisualizarPorInscricao = false;

        if (!$temYoda && !$temJedi && !$temPadauan && !$ehProprioUsuario) {
            $orientadorId = (int)($identity['id'] ?? 0);
            if ($orientadorId > 0) {
                $vinculoEmRascunho = $this->fetchTable('ProjetoBolsistas')->find()
                    ->select(['id'])
                    ->where([
                        'ProjetoBolsistas.deleted IS' => null,
                        'ProjetoBolsistas.fase_id' => 3,
                        'ProjetoBolsistas.orientador' => $orientadorId,
                        'OR' => [
                            'ProjetoBolsistas.bolsista' => (int)$id,
                            'ProjetoBolsistas.coorientador' => (int)$id,
                        ],
                    ])
                    ->first();
                $podeVisualizarPorInscricao = (bool)$vinculoEmRascunho;
            }
        }

        if (!$temYoda && !$temJedi && !$temPadauan && !$ehProprioUsuario && !$podeVisualizarPorInscricao) {
            $this->Flash->error('Acesso restrito.');
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }



        $usuario = $this->Usuarios->find()
        ->contain([
            'Unidades', 'Escolaridades', 'Vinculos', 'Instituicaos'])
        ->where(['Usuarios.id' => $id])->first();       


            $racas = $this->racas;
            $sexo = $this->sexo;
            $deficiencia = $this->deficiencia;
            $documentos = $this->documentos;

            $unidades = TableRegistry::getTableLocator()
                ->get('Unidades')
                ->find()
                ->where(['id IN' => explode(',', $usuario->jedi ?? '')]);

            $programas = TableRegistry::getTableLocator()
                ->get('Programas')
                ->find()
                ->where(['id IN' => explode(',', $usuario->padauan ?? '')]);


            $dashboardService = new \App\Service\DashboardUserService();
            $bolsas = $dashboardService
                ->detalhes(null, (int)$id, $identityObj)
                ->orderBy(['vigente' => 'DESC', 'id' => 'DESC']);


            

            $this->set(compact(
                'unidades',
                'documentos',
                'racas',
                'sexo',
                'deficiencia',
                'programas',      
                'usuario',
                'bolsas'
            ));

    }

    //retornar
    public function gestao(int $userId)
    {
        $this->viewBuilder()->setLayout('admin');
        $identity = $this->Authentication->getIdentity();

        // ===============================
        // 1) Dados fixos da tela
        // ===============================
        $usuario = $this->Usuarios->find()
            ->select(['id', 'nome'])
            ->where(['id' => $userId])
            ->firstOrFail();

        $programas = TableRegistry::getTableLocator()
            ->get('Programas')
            ->find()
            ->select(['id', 'sigla', 'nome'])
            ->where(['Programas.id NOT IN' => [7, 8]])
            ->orderBy(['sigla'])
            ->all();

        // ===============================
        // 2) Leitura da requisição
        // ===============================
        $query          = $this->request->getQuery();
        $tipo           = $query['tipo'] ?? null;               // tipo de consulta
        $papel          = $query['papel'] ?? [];               // checkbox papel
        $programaFilter = $query['programa'] ?? [];            // checkbox programa
        $situacao       = $query['situacao'] ?? [];            // checkbox situação
        $incluirDeletados = (bool)($query['incluir_deletados'] ?? false);
        $acao           = $query['acao'] ?? null;              // buscar ou excel

        $resultados = null;

        // ===============================
        // 2.1 Validar tipo somente se submit
        // ===============================
        if ($acao && !$tipo) {
            $this->Flash->error('O tipo de consulta é obrigatório.');
            return $this->redirect(['controller' => 'Users', 'action' => 'gestao', $userId]);
        }

        // ===============================
        // 3) Executa consulta via Service (filtros manuais)
        // ===============================
        if ($tipo) {
            $dashboardService = new \App\Service\DashboardUserService();

            // 3.1 Montar condições
            $conditions = [];

            // Papel: null significa "trazer todos"
            $papelChecked = !empty($papel) ? $papel : null;


            // Programa
            if (!empty($programaFilter)) {
                $conditions['Dashdetalhes.programa_id IN'] = $programaFilter;
            }

            // Situação
            if (!empty($situacao)) {
                $statusMap = [
                    'vigente'       => 1,
                    'egresso'       => 2,
                    'nao_efetivado' => 3,
                    'suspenso'      => 4
                ];
                $statusFiltros = array_map(fn($s) => $statusMap[$s] ?? null, $situacao);
                $statusFiltros = array_filter($statusFiltros);
                if (!empty($statusFiltros)) {
                    $conditions['Dashdetalhes.situacao_id IN'] = $statusFiltros;
                }
            }

            // Filtro padrão: apenas ativos para usuários comuns
            if (!$incluirDeletados && !in_array($identity->id, [1, 8088])) {
                $conditions['Dashdetalhes.ativo'] = 1;
            }

            // Vigência mínima
            $conditions[] = function (\Cake\Database\Expression\QueryExpression $exp) {
                return $exp->gt('Dashdetalhes.fim_vigencia', \Cake\I18n\FrozenTime::now());
            };

            // ===============================
            // 3.2 Consulta via service relatorio
            // ===============================
            $resultadosQuery = $dashboardService->relatorio(
                $userId,      // filtra pelo usuário
                $papelChecked, // filtra pelos papéis marcados
                $conditions,  // filtros adicionais
                ['identity' => $identity]
            );

            // Transformar em array para a view
            $resultados = $resultadosQuery->enableHydration(false)->toArray();

            // ===============================
            // 3.3 Exportação Excel (opcional)
            // ===============================
            if ($acao === 'excel') {
                // gerar Excel futuramente
            }
        }

        // ===============================
        // 4) Renderiza variáveis para a view
        // ===============================
        $this->set(compact(
            'usuario',
            'programas',
            'resultados',
            'tipo',
            'papel',
            'programaFilter',
            'situacao',
            'incluirDeletados'
        ));
    }


    

    //ok
    public function editar($id = null)
    {
        $identity = $this->Authentication->getIdentity();
        $identityId = (int)($identity->id ?? 0);
        $inscricaoEdicaoOrientadorId = null;
        $user = $this->Usuarios->get(($id ?? $identityId), ['contain' => ['Instituicaos', 'Streets'=>['Districts'=>['Cities']]]]);
        
        if ($user->id !== $identityId) {
            $podeEditar = (bool)($identity->yoda ?? false);

            if (!$podeEditar) {
                $vinculoEmRascunho = $this->fetchTable('ProjetoBolsistas')->find()
                    ->select(['id'])
                    ->where([
                        'ProjetoBolsistas.deleted IS' => null,
                        'ProjetoBolsistas.fase_id' => 3,
                        'ProjetoBolsistas.orientador' => $identityId,
                        'OR' => [
                            'ProjetoBolsistas.bolsista' => (int)$user->id,
                            'ProjetoBolsistas.coorientador' => (int)$user->id,
                        ],
                    ])
                    ->first();
                $podeEditar = (bool)$vinculoEmRascunho;
                if ($podeEditar) {
                    $inscricaoEdicaoOrientadorId = (int)$vinculoEmRascunho->id;
                }
            }

            if (!$podeEditar) {
                $this->Flash->error("O acesso a edição dos dados é restrito ao próprio usuário, à gestão ou a bolsista/coorientador vinculado em inscrição na fase de rascunho.");
                return $this->redirect(['controller' => 'Index', 'action' => 'index']);
            }
        }

        $racas = $this->racas;
        $sexo = $this->sexo;
        $deficiencia = $this->deficiencia;
        $documentos = $this->documentos;
        $vinculos = TableRegistry::getTableLocator()->get('Vinculos')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
        ->where(['deleted'=>0])->orderBy(['nome'=>'ASC']);
        $vinculoPesquisador40hId = $this->fetchTable('Vinculos')->find()
            ->select(['id'])
            ->where([
                'Vinculos.deleted' => 0,
                'Vinculos.nome LIKE' => '%pesquisador%40%',
            ])
            ->first()
            ?->id;

        if ($this->request->is(['patch', 'post', 'put'])) {
          
            
            //$user = $this->Usuarios->patchEntity($user, $this->request->getData());
            $dados = $this->request->getData();
            $original = $user->toArray();

            // Campos desabilitados no form nao sao enviados no POST.
            // Preserva o valor atual para evitar validacao indevida.
            if (!array_key_exists('escolaridade_id', $dados)) {
                $dados['escolaridade_id'] = $user->escolaridade_id;
            }
            if (!array_key_exists('ic', $dados)) {
                $dados['ic'] = $user->ic;
            }


            if($dados['data_nascimento']!=''){
                $user->data_nascimento = parent::acertaData($dados['data_nascimento']);
                $dados['data_nascimento'] = $user->data_nascimento;
            }else{
                $user->data_nascimento = null;
                $dados['data_nascimento'] = null;
            }
            if($dados['documento_emissao']!=''){
                $user->documento_emissao = parent::acertaData($dados['documento_emissao']);
                $dados['documento_emissao'] = $user->documento_emissao;
            }else{
                $user->documento_emissao = null;
                $dados['documento_emissao'] = null;
            }
            if($dados['ic']=='' || $dados['ic']==null){
                $dados['ic']=null;
            }else{
                $user->ic = $dados['ic'];
            }

            $escolaridadeId = (int)($dados['escolaridade_id'] ?? 0);
            if (!in_array($escolaridadeId, [6, 7], true)) {
                $dados['ic'] = null;
            }
            
            if($dados['unidade_id']=='' || $dados['unidade_id']==null){
                $dados['unidade_id'] = null;
            }else{
                $user->unidade_id = $dados['unidade_id'];
            }

            if(trim($dados['nome_social'])=='' || ($dados['nome_social'])==null){
                $dados['nome_social'] = null;
            }else{
                $user->nome_social = trim($dados['nome_social']);
            }

            if(!preg_match("/^(http:\/\/lattes.cnpq.br\/)?[0-9]{16}/", $dados['lattes']) && !preg_match("/^(https:\/\/lattes.cnpq.br\/)?[0-9]{16}/", $dados['lattes'])) {
                $this->Flash->error('Sua alteração não foi gravada: O endereço do lattes deve serguir o endereço padrão: http://lattes.cnpq.br/1432849906286574 ou https://lattes.cnpq.br/1432849906286574');
                return $this->redirect(['action' => 'editar', $id]);
            }

            if($dados['instituicao_curso']!='')
            {
                $this->Instituicao = TableRegistry::getTableLocator()->get('Instituicaos');
                $instituicao = $this->Instituicao->find('all')->where(['sigla'=>$dados['instituicao_curso']])->first();
                if(!$instituicao)
                {
                    $novaInstituicao = $this->Instituicao->newEmptyEntity();
                    $novaInstituicao->nome = 'ALTERAR NOME';
                    $novaInstituicao->sigla = $dados['instituicao_curso'];
                    $instituicao = $this->Instituicao->save($novaInstituicao);
                }
                $dados['instituicao_curso'] = $instituicao->id;
            }
            $user->email = trim($user->email);
            $user->email_alternativo = trim($dados['email_alternativo']);

            $user->last_data_update_date = date('Y-m-d H:i:s');

            $dadosValidacao = $dados + [
                'nome' => $user->nome,
                'email' => $user->email,
                'sexo' => $user->sexo,
                'raca' => $user->raca,
                'deficiencia' => $user->deficiencia,
                'escolaridade_id' => $user->escolaridade_id,
                'vinculo_id' => $user->vinculo_id,
                'unidade_id' => $user->unidade_id,
                'departamento' => $user->departamento,
                'laboratorio' => $user->laboratorio,
                'matricula_siape' => $user->matricula_siape,
            ];
            $errosObrigatorios = $this->validarObrigatoriosUsuario(
                $dadosValidacao,
                $vinculoPesquisador40hId ? (int)$vinculoPesquisador40hId : null
            );
            if (!empty($errosObrigatorios)) {
                $this->Flash->error(implode('<br>', $errosObrigatorios), ['escape' => false]);
                return $this->redirect(['action' => 'editar', $id]);
            }

            $user = $this->Usuarios->patchEntity($user, $dados);

            $diff = $this->buildUserDiff($original, $user, $this->getUserAuditFields());
            $observacaoEdicaoOrientador = $inscricaoEdicaoOrientadorId !== null
                ? 'Alteração realizada pelo orientador na inscrição #' . $inscricaoEdicaoOrientadorId . '.'
                : null;

            try {
                $this->Usuarios->getConnection()->transactional(function () use ($user, $diff, $observacaoEdicaoOrientador) {
                    if (!$this->Usuarios->save($user)) {
                        throw new \RuntimeException('Falha ao salvar usuário.');
                    }

                    $session = $this->request->getSession();
                    $forcedUpdate = (bool)$session->read('force_update');
                    if (empty($diff)) {
                        $diff = [
                            '_info' => [
                                'de' => null,
                                'para' => $forcedUpdate
                                    ? 'Atualizacao exigida pela aplicacao após 6 meses sem atualização. Foi atualizado sem alteracoes'
                                    : 'Salvou sem alteracoes',
                            ],
                        ];
                    }

                    if ($observacaoEdicaoOrientador !== null) {
                        $infoAtual = trim((string)($diff['_info']['para'] ?? ''));
                        $diff['_info'] = [
                            'de' => null,
                            'para' => $infoAtual !== ''
                                ? $infoAtual . ' ' . $observacaoEdicaoOrientador
                                : $observacaoEdicaoOrientador,
                        ];
                    }

                    if (!$this->saveUserHistory((int)$user->id, $diff, 'E')) {
                        throw new \RuntimeException('Falha ao salvar histórico.');
                    }

                    if ($forcedUpdate) {
                        $session->delete('force_update');
                    }
                });

                $this->Flash->success('Usuário atualizado com sucesso');
                if ($inscricaoEdicaoOrientadorId !== null) {
                    return $this->redirect(['controller' => 'Index', 'action' => 'dashdetalhes', 'A']);
                }
                if ($user->escolaridade_id < 10) {
                    $this->Flash->success('Prezado(a) Candidato(a), agradecemos seu interesse em fazer parte do nosso Banco de talentos e pelo(a) seu(ua) participação em nossos processos seletivos. Caso seja selecionado(a) por um de nossos orientadores, eles entrarão em contato para uma entrevista e posterior inscrição de seu nome no sistema.');
                }

                return $this->redirect(['action' => 'ver', $user->id]);
            } catch (\Throwable $e) {
                $info = $this->ExceptionReporter->report($e, 'Erro no Sistema - editar usuario');
                $suffix = '';
                if (!empty($info['id'])) {
                    $suffix = '<br>Codigo: Erro #' . $info['id'];
                    if (!empty($info['repeticao'])) {
                        $rep = $info['repeticoes'] ?? null;
                        $repText = $rep ? ' (' . $rep . 'a tentativa)' : '';
                        $suffix .= '<br>Repeticao detectada' . $repText . '. Esse erro ja foi registrado.';
                        $suffix .= '<br>Contate a gestao ou TI, abra um chamado e aguarde instruções.';
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
                return $this->redirect(['controller' => 'Index', 'action' => 'index']);
            }
        }

        $unidades = $this->Usuarios->Unidades->find('list', [
            'limit' => 100,
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where(['deleted' => 0])
            ->orderBy(['nome' => 'ASC']);
        
        $escolaridades = $this->Usuarios->Escolaridades->find('list', [
            'limit' => 20,
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where(['id >'=>5])
            ->orderBy(['nome' => 'ASC']);

        $ufs = TableRegistry::getTableLocator()->get('States')->find('list', [
            'keyField' => 'id',
            'valueField' => 'sigla',
        ])
            ->orderBy(['sigla' => 'ASC']);
        
        $this->set(compact('deficiencia', 'user', 'vinculos', 'racas', 'sexo', 'documentos','escolaridades', 'unidades', 'ufs', 'vinculoPesquisador40hId'));
    }

    public function cadastrarUsuario($cpf = null, $papel = null, $inscricaoId = null, $editalId = null)
    {
        $identity = $this->Authentication->getIdentity();
        if (!$identity) {
            $this->Flash->error('Faça login para continuar.');
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        if ($cpf == null) {
            $this->Flash->error('CPF não informado.');
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $isTi = in_array((int)$identity->id, [1, 8088], true);
        $papel = strtoupper(trim((string)$papel)); 
        $vinculoPesquisador40hId = $this->fetchTable('Vinculos')->find()
            ->select(['id'])
            ->where([
                'Vinculos.deleted' => 0,
                'Vinculos.nome LIKE' => '%pesquisador%40%',
            ])
            ->first()
            ?->id;
        
        $condicoesInscricaoBase = [];

        if (!$isTi) {
            if (!in_array($papel, ['B', 'C'], true)) {
                $this->Flash->error('Papel obrigatório e deve ser B (bolsista) ou C (coorientador).');
                return $this->redirect(['controller' => 'Index', 'action' => 'index']);
            }
            if ($inscricaoId ==null || $editalId == null) {
                $this->Flash->error('Inscrição e edital são obrigatórios para este cadastro.');
                return $this->redirect(['controller' => 'Index', 'action' => 'index']);
            }
        }

        if ($editalId !=null) {
            $edital = $this->fetchTable('Editais')->find()
                ->where(['Editais.id' => $editalId])
                ->first();

            if (!$edital) {
                $this->Flash->error('Edital inválido para este cadastro.');
                return $this->redirect(['controller' => 'Index', 'action' => 'index']);
            }
            $inscricaoTableName = $edital->programa_id===1 ? 'PdjInscricoes' : 'ProjetoBolsistas';
        }

        if ($inscricaoId !=null) {
            $condicoesInscricaoBase = [
                $inscricaoTableName . '.id' => $inscricaoId,
            ];
            if ($editalId > 0) {
                $condicoesInscricaoBase[$inscricaoTableName . '.editai_id'] = $editalId;
            }
    
            if (!$isTi) {
                $condicoesInscricaoBase[$inscricaoTableName . '.orientador'] = (int)$identity->id;
            }
            $inscricaoOrigem = $this->fetchTable($inscricaoTableName)->find()
                ->select(['id', 'orientador', 'editai_id'])
                ->where($condicoesInscricaoBase)
                ->first();
            if (!$inscricaoOrigem) {
                $this->Flash->error('Acesso negado. Somente o orientador da inscrição pode cadastrar/alterar neste contexto.');
                return $this->redirect(['controller' => 'Index', 'action' => 'index']);
            }
        }

        $user = $this->Usuarios->newEmptyEntity();
        $user->cpf = (string)$cpf;

        if ($this->request->is(['post', 'put', 'patch'])) {
            if (!parent::validaCPF($cpf)) {
                $this->Flash->error('CPF inválido na URL.');
                return $this->redirect(['controller' => 'Index', 'action' => 'index']);
            }

            $usuarioExistente = $this->Usuarios->find()
                ->where(['Usuarios.cpf' => $cpf])
                ->first();
            if ($usuarioExistente) {
                $this->Flash->error('CPF já estava cadastrado. Retorne no formulário e tente novamente.');
                return $this->redirect(['controller' => 'Index', 'action' => 'index']);
            }

            $dados = $this->request->getData();
            $dados['cpf'] = $cpf;
            $dados['active'] = 1;
            $dados['yoda'] = 0;
            $dados['jedi'] = null;
            $dados['padauan'] = null;

            if (!empty($dados['data_nascimento'])) {
                $dados['data_nascimento'] = parent::acertaData((string)$dados['data_nascimento']);
            } else {
                $dados['data_nascimento'] = null;
            }

            if (!empty($dados['documento_emissao'])) {
                $dados['documento_emissao'] = parent::acertaData((string)$dados['documento_emissao']);
            } else {
                $dados['documento_emissao'] = null;
            }

            $escolaridadeId = (int)($dados['escolaridade_id'] ?? 0);
            $icInformado = strtoupper(trim((string)($dados['ic'] ?? '')));
            if (!in_array($escolaridadeId, [6, 7], true) || $icInformado === '') {
                $dados['ic'] = null;
            } else {
                $dados['ic'] = $icInformado;
            }

            $errosObrigatorios = $this->validarObrigatoriosUsuario($dados, $vinculoPesquisador40hId ? (int)$vinculoPesquisador40hId : null);
            if (!empty($errosObrigatorios)) {
                $this->Flash->error(implode('<br>', $errosObrigatorios), ['escape' => false]);
                return $this->redirect($this->request->getRequestTarget());
            }

            $user = $this->Usuarios->patchEntity($user, $dados);

            $mensagemNaoVinculado = null;
            try {
                $this->Usuarios->getConnection()->transactional(function () use (&$user, $inscricaoId, $editalId, $papel, $inscricaoTableName, $condicoesInscricaoBase, &$mensagemNaoVinculado) {
                    $this->Usuarios->saveOrFail($user);
                    $this->saveUserHistory((int)$user->id, ['_info' => ['de' => null, 'para' => 'Cadastro de usuário']], 'C');

                    if (!empty($inscricaoId) && !empty($editalId) && in_array($papel, ['B', 'C'], true)) {
                        $campo = ($papel === 'C') ? 'coorientador' : 'bolsista';
                        $inscricao = $this->fetchTable($inscricaoTableName)->find()
                            ->where($condicoesInscricaoBase)
                            ->first();
                        if ($inscricao) {
                            $faseOriginal = (int)($inscricao->fase_id ?? 0);
                            if ($papel === 'C') {
                                $programaAtual = (int)($inscricao->programa_id ?? 0);
                                $mensagemElegibilidade = $this->checkCoorientador(
                                    (int)$user->id,
                                    (int)$inscricao->orientador,
                                    $programaAtual,
                                    (int)($inscricao->editai_id ?? 0)
                                );
                                if ($mensagemElegibilidade !== null) {
                                    $mensagemNaoVinculado = 'Usuário cadastrado, porém não vinculado como coorientador: ' . $mensagemElegibilidade;
                                    return;
                                }
                            }

                            $inscricao = $this->fetchTable($inscricaoTableName)->patchEntity($inscricao, [
                                $campo => (int)$user->id,
                            ]);
                            $this->fetchTable($inscricaoTableName)->saveOrFail($inscricao);
                            if ($inscricaoTableName === 'ProjetoBolsistas' && $faseOriginal > 0) {
                                $this->historico(
                                    (int)$inscricao->id,
                                    $faseOriginal,
                                    $faseOriginal,
                                    'Vinculação de usuário cadastrada no Dados Bolsista',
                                    true
                                );
                            }
                        }
                    }
                });

                if ($mensagemNaoVinculado !== null) {
                    $this->Flash->error($mensagemNaoVinculado);
                    if (!empty($inscricaoId) && !empty($editalId)) {
                        $acaoRetorno = ($papel === 'C') ? 'coorientador' : 'dadosBolsista';
                        return $this->redirect(['controller' => 'Inscricoes', 'action' => $acaoRetorno, $editalId, $inscricaoId]);
                    }
                    return $this->redirect(['action' => 'ver', $user->id]);
                }

                $this->Flash->success('Usuário cadastrado com sucesso.');
                if (!empty($inscricaoId) && !empty($editalId)) {
                    $acaoRetorno = ($papel === 'C') ? 'coorientador' : 'dadosBolsista';
                    return $this->redirect(['controller' => 'Inscricoes', 'action' => $acaoRetorno, $editalId, $inscricaoId]);
                }
                return $this->redirect(['action' => 'ver', $user->id]);
            } catch (\Throwable $e) {
                $info = $this->ExceptionReporter->report($e, 'Erro no Sistema - cadastrar usuario');
                $suffix = '';
                if (!empty($info['id'])) {
                    $suffix = '<br>Codigo: Erro #' . $info['id'];
                    if (!empty($info['repeticao'])) {
                        $rep = $info['repeticoes'] ?? null;
                        $repText = $rep ? ' (' . $rep . 'a tentativa)' : '';
                        $suffix .= '<br>Repeticao detectada' . $repText . '. Esse erro ja foi registrado.';
                        $suffix .= '<br>Contate a gestao ou TI, abra um chamado e aguarde instruções.';
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
                return $this->redirect(['controller' => 'Index', 'action' => 'index']);
            }
        }

        $racas = $this->racas;
        $sexo = $this->sexo;
        $deficiencia = $this->deficiencia;
        $documentos = $this->documentos;
        $vinculos = TableRegistry::getTableLocator()->get('Vinculos')->find('list', [
            'keyField' => 'id',
            'valueField' => 'nome',
        ])->where(['deleted' => 0])->orderBy(['nome' => 'ASC']);
        $cpfTravado = true;
        $cpfForcado = (string)$cpf;

        $unidades = $this->Usuarios->Unidades->find('list', [
            'limit' => 100,
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where(['deleted' => 0])
            ->orderBy(['nome' => 'ASC']);
        
        $escolaridades = $this->Usuarios->Escolaridades->find('list', [
            'limit' => 20,
            'keyField' => 'id',
            'valueField' => 'nome',
        ])
            ->where(['id >'=>5])
            ->orderBy(['nome' => 'ASC']);

        $ufs = TableRegistry::getTableLocator()->get('States')->find('list', [
            'keyField' => 'id',
            'valueField' => 'sigla',
        ])
            ->orderBy(['sigla' => 'ASC']);

        $cadastroNovo = true;
        $this->set(compact(
            'deficiencia',
            'user',
            'vinculos',
            'racas',
            'sexo',
            'documentos',
            'escolaridades',
            'unidades',
            'ufs',
            'cadastroNovo',
            'cpfTravado',
            'isTi',
            'cpfForcado',
            'editalId',
            'inscricaoId',
            'papel',
            'vinculoPesquisador40hId'
        ));
        return $this->render('cadastrar_usuario');
    }

    
    

    
    //ok
    public function veracessos($id)
    {
        if(!(in_array($this->request->getAttribute('identity')['id'], [1, 8088]))){
            $this->Flash->error("Acesso negado.");
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);

        }

        $mes = $this->request->getQuery('mes');
        $ano = $this->request->getQuery('ano');

        $this->UsuariosAcessos = TableRegistry::getTableLocator()->get('UsuariosAcessos');
        $lista = $this->UsuariosAcessos->find('all')
            ->where(['usuario_id' => $id])
            ->orderBy(['id' => 'DESC'])
            ->limit(30);

        if (!empty($mes) && !empty($ano)) {
            $mesInt = (int)$mes;
            $anoInt = (int)$ano;
            if ($mesInt >= 1 && $mesInt <= 12 && $anoInt > 1900) {
                $start = new \DateTimeImmutable(sprintf('%04d-%02d-01 00:00:00', $anoInt, $mesInt));
                $end = $start->modify('last day of this month')->setTime(23, 59, 59);
                $lista->where([
                    'UsuariosAcessos.created >=' => $start->format('Y-m-d H:i:s'),
                    'UsuariosAcessos.created <=' => $end->format('Y-m-d H:i:s'),
                ]);
            }
        }

        $listas = $this->paginate($lista, ['limit'=>10]);

        $this->set(compact('listas', 'mes', 'ano'));
    }



    public function getbolsistabycpf()
    {
        $cpf = preg_replace('/[^0-9]/', '', $this->request->getData('cpf'));
        if (parent::validaCPF($cpf)) {
            $query = $this->Usuarios->find()
                ->contain(['Instituicaos'])
                ->where(['cpf' => $cpf]);
            if ($query->count() <= 0) {
                $this->set('retorno', ['error' => true, 'message' => "O CPF informado não existe na base, favor informar nome e email do bolsista"]);
            } else {
                $query->enableHydration();
                $usuario = $query->first();
                $bolsasTable = $this->fetchTable('ProjetoBolsistas');
                $bolsasQuery = $bolsasTable->find()
                    ->select(['vigente', 'fase_id'])
                    ->where([
                        'ProjetoBolsistas.bolsista' => (int)$usuario->id,
                        'ProjetoBolsistas.deleted IS' => null,
                    ])
                    ->all()
                    ->toList();
                $bolsas = array_map(function ($item) {
                    return [
                        'vigente' => $item->vigente ?? null,
                        'fase_id' => $item->fase_id ?? null,
                    ];
                }, $bolsasQuery);
                $temVigente = $bolsasTable->find()
                    ->where([
                        'ProjetoBolsistas.bolsista' => (int)$usuario->id,
                        'ProjetoBolsistas.deleted IS' => null,
                        'ProjetoBolsistas.vigente' => 1,
                        'ProjetoBolsistas.fase_id' => 11,
                    ])
                    ->count();
                $temAndamento = $bolsasTable->find()
                    ->where([
                        'ProjetoBolsistas.bolsista' => (int)$usuario->id,
                        'ProjetoBolsistas.deleted IS' => null,
                        'ProjetoBolsistas.fase_id <' => 10,
                    ])
                    ->count();
                $usuarioData = $usuario ? $usuario->toArray() : [];
                $usuarioData['bolsas'] = $bolsas;
                $usuarioData['tem_vigente'] = $temVigente > 0;
                $usuarioData['tem_andamento'] = $temAndamento > 0;
                $this->set('retorno', ['error' => false, 'message' => "Success", "data" => $usuarioData]);
            }
        } else {
            $this->set('retorno', ['error' => true, 'message' => "O CPF informado é inválido", "data" => null]);
        }
        $this->viewBuilder()->setClassName('Json');
        $this->viewBuilder()->setOption('serialize', 'retorno');
    }

    public function getcoorientadorbycpf()
    {
        $cpf = preg_replace('/[^0-9]/', '', $this->request->getData('cpf'));
        if (parent::validaCPF($cpf)) {
            $query = $this->Usuarios->find('all')->where(['cpf' => $cpf]);
            if ($query->count() <= 0) {
                $this->set('retorno', ['error' => true, 'message' => "O CPF informado não existe na base, favor informar nome e email do coorientador"]);
            } else {
                $query->enableHydration();
                $usuario = $query->toList()[0];
                $this->set('retorno', ['error' => false, 'message' => "Success", "data" => $usuario]);
            }
        } else {
            $this->set('retorno', ['error' => true, 'message' => "O CPF informado é inválido", "data" => null]);
        }
        $this->viewBuilder()->setClassName('Json');
        $this->viewBuilder()->setOption('serialize', 'retorno');
    }

    public function minhasAvaliacoes()
    {
        $this->Avaliacoes = TableRegistry::getTableLocator()->get('AvaliadorBolsistas');
        //lista avaliações raic
        $avaliacoes_raic = $this->Avaliacoes
                           ->find('all')
                           ->contain(['Avaliadors','Raics'=>['Editais','Usuarios','Orientadores'=>'Unidades','Projetos'=>['Usuarios'=>'Unidades']],'Avaliations'])
                           ->where(['Avaliadors.usuario_id'=>$this->request->getAttribute('identity')['id'], 'AvaliadorBolsistas.tipo <> "N"'])
                           ->where(['AvaliadorBolsistas.deleted'=>0])
                           ->where(['AvaliadorBolsistas.ano'=>date('Y')]);

        $avaliacoes_novas = $this->Avaliacoes
                           ->find('all')                          
                           ->contain(['Avaliadors',
                                    'ProjetoBolsistas'=>['Editais','Usuarios','Orientadores'=>'Unidades','Projetos'=>['Usuarios'=>'Unidades']],'Avaliations'])
                           ->where(['Avaliadors.usuario_id'=>$this->request->getAttribute('identity')['id'], 'AvaliadorBolsistas.tipo'=>'N'])
                           ->where(['AvaliadorBolsistas.deleted'=>0])
                           ->where(['AvaliadorBolsistas.ano'=>date('Y')]);       
                           
        $editais = TableRegistry::getTableLocator()->get('AvaliadorBolsistas')
                           ->find()
                           ->select(['AvaliadorBolsistas.avaliador_id', 'AvaliadorBolsistas.editai_id', 'Editais.nome'])
                           ->distinct()
                           ->contain(['Editais'])
                           ->join(['table'=>'avaliadors',
                           'alias'=>'AB','type'=>'INNER', 
                           'conditions'=>'AB.id = AvaliadorBolsistas.avaliador_id'])
                           ->where(['AB.usuario_id'=>$this->request->getAttribute('identity')['id'], 'AvaliadorBolsistas.deleted'=>0]);

        $bolsista = null;
        $avaliacoes_anteriores=null;
       
        $this->set(compact('avaliacoes_raic', 'avaliacoes_novas', 'avaliacoes_anteriores', 'bolsista', 'editais'));
    }

    public function talentos($limpar=false)
    {
        //$this->loadComponent('Paginator');
        $w = [];
        $busca = $this->request->getSession()->read('buscaUsuarios');


        if ($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();
            if($dados['nome']!=''){
                array_push($w, ['Usuarios.nome'.' LIKE "%'.preg_replace('[ ]','%',($dados['nome'])).'%"']);
            }
            if($dados['curso']!=''){
                array_push($w, ['Usuarios.curso'.' LIKE "%'.preg_replace('[ ]','%',($dados['curso'])).'%"']);
            }
            
            /*
            if($dados['sexo']!=''){
                array_push($w , ['Usuarios.sexo' => ($dados['sexo'])]);
            }
            if($dados['raca']!=''){
                array_push($w , ['Usuarios.raca' => ($dados['raca'])]);
            }
            if($dados['deficiencia']!=''){
                array_push($w , ['Usuarios.deficiencia' => ($dados['deficiencia'])]);
            }
                */
            
            if($dados['programa']!=''){
                array_push($w , ['Usuarios.ic' => ($dados['programa'])]);
            }

            $this->request->getSession()->write('buscaUsuarios',$w);
            $this->redirect(['action'=>'talentos']);
    
        }else{
            $w = $busca;

        }

        if($limpar){
            $this->request->getSession()->delete('buscaUsuarios');
            $busca = null;
            $w = [];
            $this->redirect(['action'=>'talentos']);
        }

        $usuario = $this->Usuarios->find('all')->contain(['Unidades', 'Escolaridades', 'Vinculos'])->where([$w])->where(['escolaridade_id in (6,7)'])
        ->orderBy(['Usuarios.nome' => 'ASC']);
        $this->paginate = ['limit'=>10];
        $usuarios = $this->paginate($usuario);
        
        //dd($usuarios);
        $racas = $this->racas;
        $sexo = $this->sexo;
        $deficiencia = $this->deficiencia;
        //dd($sexo);


        $escolaridades = $this->Usuarios->Escolaridades->find('list', ['limit' => 20])->where(['id in (6,7)']);
        $unidades = $this->Usuarios->Unidades->find('list', ['limit' => 220])->orderBy(['Unidades.nome'=>'ASC']);
        $vinculos = $this->Usuarios->Vinculos->find('list', ['limit' => 30])->orderBy(['Vinculos.nome'=>'ASC']);

        $this->set(compact('sexo', 'deficiencia', 'usuarios', 'escolaridades', 'racas', 'unidades', 'vinculos'));
    }

    public function curriculo($id)
    {
        $usuario = $this->Usuarios->get(($id), [
            'contain' => [
            'Instituicaos', 
            'Escolaridades', 
            'Streets' => [
                        'Districts' => 'Cities'
                    ]]]);
            $racas = $this->racas;
            $deficiencia = $this->deficiencia;
            $sexo = $this->sexo;


            //dd($usuario);
        $this->set(compact('racas','usuario', 'deficiencia','sexo'));
    }

    //ok
    public function addperfil($id = null)
    {
        if(!(in_array($this->request->getAttribute('identity')['id'], [1, 8088, 16721]))){
            $this->Flash->error("Acesso negado.");
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);

        }
        $user = $this->Usuarios->get($id);
        
       

        if ($this->request->is(['patch', 'post', 'put'])) {
            $dados = $this->request->getData();

            $original = $user->toArray();
            $user = $this->Usuarios->patchEntity($user, $dados);

            $diff = $this->buildUserDiff($original, $user, $this->getUserAuditFields());


            //dd($user);
            try {
                $this->Usuarios->getConnection()->transactional(function () use ($user, $diff) {
                    if (!$this->Usuarios->save($user)) {
                        throw new \RuntimeException('Falha ao salvar usuário.');
                    }

                    if (!$this->saveUserHistory((int)$user->id, $diff, 'W')) {
                        throw new \RuntimeException('Falha ao salvar histórico.');
                    }
                });

                $this->Flash->success('Usuário atualizado com sucesso');
                return $this->redirect(['action' => 'ver', $user->id]);
            } catch (\Throwable $e) {
                $info = $this->ExceptionReporter->report($e, 'Erro no Sistema - addperfil');
                $suffix = '';
                if (!empty($info['id'])) {
                    $suffix = '<br>Codigo: Erro #' . $info['id'];
                    if (!empty($info['repeticao'])) {
                        $rep = $info['repeticoes'] ?? null;
                        $repText = $rep ? ' (' . $rep . 'a tentativa)' : '';
                        $suffix .= '<br>Repeticao detectada' . $repText . '. Esse erro ja foi registrado.';
                        $suffix .= '<br>Contate a gestao ou TI, abra um chamado e aguarde instruções.';
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
                return $this->redirect(['controller' => 'Index', 'action' => 'index']);
            }

        }

        $unidades = $this->Usuarios->Unidades->find('list', ['limit' => 100]);
        $programas = [
            'P' => 'PIBIC',
            'T' => 'PIBITI',
            'I' => 'IC MAnguinhos',
            'M' => 'IC Maré',
            'A' => 'IC Mata Atlântica',
            'J' => 'PDJ'
        ];   
        
              
        $this->set(compact('unidades', 'programas', 'user'));
    }

    protected function validarObrigatoriosUsuario(array $dados, ?int $vinculoPesquisador40hId = null): array
    {
        $erros = [];
        $get = function (string $chave) use ($dados) {
            return isset($dados[$chave]) ? trim((string)$dados[$chave]) : '';
        };

        $obrigatorios = [
            'nome' => 'Nome',
            'email' => 'Email',
            'data_nascimento' => 'Data de nascimento',
            'sexo' => 'Gênero',
            'raca' => 'Raça',
            'deficiencia' => 'Deficiência',
            'lattes' => 'Lattes',
            'escolaridade_id' => 'Escolaridade',
            'curso' => 'Curso',
            'ano_conclusao' => 'Conclusão',
            'instituicao_curso' => 'Instituição de ensino',
            'numero' => 'Número',
            'telefone' => 'Telefone',
            'celular' => 'Celular',
            'whatsapp' => 'Whatsapp',
            'documento' => 'Documento',
            'documento_numero' => 'Nº do documento',
            'documento_emissor' => 'Órgão emissor',
            'documento_uf_emissor' => 'UF de emissão',
            'documento_emissao' => 'Data de emissão',
        ];
        foreach ($obrigatorios as $campo => $label) {
            if ($get($campo) === '') {
                $erros[] = $label . ' é obrigatório(a).';
            }
        }

        $escolaridadeId = (int)($dados['escolaridade_id'] ?? 0);
        $vinculoId = (int)($dados['vinculo_id'] ?? 0);
        if ($escolaridadeId > 7 && $vinculoId <= 0) {
            $erros[] = 'Vínculo com a FIOCRUZ é obrigatório.';
        }
        if ($escolaridadeId > 7 && $vinculoId !== 7) {
            if ($get('unidade_id') === '') {
                $erros[] = 'Unidade é obrigatória.';
            }
            if ($get('departamento') === '') {
                $erros[] = 'Departamento é obrigatório.';
            }
            if ($get('laboratorio') === '') {
                $erros[] = 'Laboratório é obrigatório.';
            }
        }
        if ($escolaridadeId > 7 && $vinculoPesquisador40hId !== null && $vinculoId === $vinculoPesquisador40hId && $get('matricula_siape') === '') {
            $erros[] = 'Matrícula SIAPE é obrigatória para o vínculo Pesquisador 40h.';
        }
        if (in_array($escolaridadeId, [6, 7], true) && $get('ic') === '') {
            $erros[] = 'Programa/Edital Social de interesse é obrigatório.';
        }

        return $erros;
    }

    //=======================
    // bloco historico
    //=======================

        protected function buildUserDiff(array $original, object $user, array $fields): array
        {
            $diff = [];

                foreach ($fields as $field) {
                    $old = $original[$field] ?? null;
                    $new = $user->get($field);

                    if ($this->historyValuesEqual($old, $new)) {
                        continue;
                    }

                    $diff[$field] = [
                        'de' => $this->normalizeHistoryValue($old),
                        'para' => $this->normalizeHistoryValue($new),
                    ];
                }

            return $diff;
        }

        protected function getUserAuditFields(): array
        {
            return [
                'nome',
                'cpf',
                'email',
                'documento',
                'documento_numero',
                'documento_emissor',
                'documento_uf_emissor',
                'documento_emissao',
                'data_nascimento',
                'sexo',
                'lattes',
                'telefone',
                'telefone_contato',
                'celular',
                'whatsapp',
                'street_id',
                'numero',
                'complemento',
                'yoda',
                'jedi',
                'escolaridade_id',
                'curso',
                'ano_conclusao',
                'em_curso',
                'instituicao_curso',
                'vinculo_id',
                'matricula_siape',
                'unidade_id',
                'departamento',
                'laboratorio',
                'nome_social',
                'raca',
                'ic',
                'email_alternativo',
                'email_contato',
                'deficiencia',
                'padauan',
            ];
        }

        protected function normalizeHistoryValue(mixed $value): mixed
        {
            if ($value instanceof \DateTimeInterface) {
                return $value->format('Y-m-d');
            }

            if (is_string($value)) {
                $value = trim($value);
                if ($value === '') {
                    return null;
                }

                if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
                    $date = \DateTimeImmutable::createFromFormat('d/m/Y', $value);
                    return $date ? $date->format('Y-m-d') : $value;
                }

                if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
                    $ts = strtotime($value);
                    return $ts !== false ? date('Y-m-d', $ts) : substr($value, 0, 10);
                }

                return $value;
            }

            if (is_bool($value)) {
                return (int)$value;
            }

            return $value;
        }

        protected function historyValuesEqual(mixed $old, mixed $new): bool
        {
            $oldNorm = $this->normalizeHistoryValue($old);
            $newNorm = $this->normalizeHistoryValue($new);

            if ($oldNorm === $newNorm) {
                return true;
            }

            if (is_scalar($oldNorm) && is_scalar($newNorm)) {
                return (string)$oldNorm === (string)$newNorm;
            }

            return json_encode($oldNorm) === json_encode($newNorm);
        }

        protected function saveUserHistory(int $usuarioId, array $diff, string $contexto = 'E', ?string $origemAcesso = null): bool
        {
            $diff = $this->filterHistoryDiff($diff);
            if (empty($diff)) {
                return true;
            }
            $diff = $this->mapHistoryDiffToLabels($diff);

            $identity = $this->Authentication->getIdentity();
            $alteradoPor = $identity ? (int)$identity->id : null;

            $this->UsuarioHistoricos = TableRegistry::getTableLocator()->get('UsuarioHistoricos');
            $novo = $this->UsuarioHistoricos->newEmptyEntity();

            $novo->usuario_id = $usuarioId;
            $novo->alterado_por = $alteradoPor;
            $novo->contexto = $contexto;
            if ($contexto === 'A') {
                $novo->origem_acesso = $origemAcesso;
            }
            $novo->diff_json = $diff;
            $novo->created = new \Cake\I18n\FrozenTime();

            return (bool)$this->UsuarioHistoricos->save($novo);
        }

        protected function mapHistoryDiffToLabels(array $diff): array
        {
            foreach ($diff as $field => $valores) {
                if (!is_array($valores)) {
                    continue;
                }
                $diff[$field]['de'] = $this->mapHistoryFieldValue((string)$field, $valores['de'] ?? null);
                $diff[$field]['para'] = $this->mapHistoryFieldValue((string)$field, $valores['para'] ?? null);
            }

            return $diff;
        }

        protected function mapHistoryFieldValue(string $field, mixed $value): mixed
        {
            if ($value === null || $value === '') {
                return null;
            }

            $mapsFixos = [
                'sexo' => $this->sexo,
                'raca' => $this->racas,
                'deficiencia' => $this->deficiencia,
                'documento' => $this->documentos,
                'ic' => [
                    'I' => 'IC Manguinhos/Ensp',
                    'A' => 'IC Mata atlantica',
                    'M' => 'IC Maré',
                    'N' => 'Não me enquadro nestes editais',
                ],
                'yoda' => [
                    '0' => 'Não',
                    '1' => 'Sim',
                ],
                'em_curso' => [
                    '0' => 'Não',
                    '1' => 'Sim',
                ],
            ];

            if (isset($mapsFixos[$field])) {
                $key = is_scalar($value) ? (string)$value : null;
                if ($key !== null && array_key_exists($key, $mapsFixos[$field])) {
                    return $mapsFixos[$field][$key];
                }
            }

            if ($field === 'documento_uf_emissor') {
                $map = $this->getHistoryListMap('States', 'sigla');
                $key = is_scalar($value) ? (string)$value : null;
                if ($key !== null && array_key_exists($key, $map)) {
                    return $map[$key];
                }
            }

            if ($field === 'escolaridade_id') {
                $map = $this->getHistoryListMap('Escolaridades', 'nome');
                $key = is_scalar($value) ? (string)$value : null;
                if ($key !== null && array_key_exists($key, $map)) {
                    return $map[$key];
                }
            }

            if ($field === 'vinculo_id') {
                $map = $this->getHistoryListMap('Vinculos', 'nome');
                $key = is_scalar($value) ? (string)$value : null;
                if ($key !== null && array_key_exists($key, $map)) {
                    return $map[$key];
                }
            }

            if ($field === 'unidade_id') {
                $map = $this->getHistoryListMap('Unidades', 'nome');
                $key = is_scalar($value) ? (string)$value : null;
                if ($key !== null && array_key_exists($key, $map)) {
                    return $map[$key];
                }
            }

            return $value;
        }

        protected function getHistoryListMap(string $tableName, string $valueField): array
        {
            static $cache = [];
            $cacheKey = $tableName . ':' . $valueField;
            if (isset($cache[$cacheKey])) {
                return $cache[$cacheKey];
            }

            $map = $this->fetchTable($tableName)->find('list', [
                'keyField' => 'id',
                'valueField' => $valueField,
            ])->toArray();

            $cache[$cacheKey] = array_combine(
                array_map('strval', array_keys($map)),
                array_values($map)
            ) ?: [];

            return $cache[$cacheKey];
        }

        protected function filterHistoryDiff(array $diff): array
        {
            foreach ($diff as $field => $valores) {
                $old = $valores['de'] ?? null;
                $new = $valores['para'] ?? null;

                if ($this->historyValuesEqual($old, $new)) {
                    unset($diff[$field]);
                    continue;
                }

                $diff[$field]['de'] = $this->normalizeHistoryValue($old);
                $diff[$field]['para'] = $this->normalizeHistoryValue($new);
            }

            return $diff;
        }

        //ok
        //tela de historico do usuario
        public function verhistorico($id)
        {
            //teste se e yoda
            if((!$this->request->getAttribute('identity')['yoda'])){
                $this->Flash->error('Você não possui acesso a este módulo!');
                return $this->redirect(['controller' => 'Index', 'action' => 'index']);
            }

            $usuario = $this->Usuarios->get($id);

            $contextoFilter = $this->request->getQuery('contexto');
            $mes = $this->request->getQuery('mes');
            $ano = $this->request->getQuery('ano');

            $historicos = TableRegistry::getTableLocator()->get('UsuarioHistoricos')->find()
                ->contain(['Alterador'])
                ->where(['UsuarioHistoricos.usuario_id' => $id])
                ->orderBy(['UsuarioHistoricos.id' => 'DESC']);

            if (!empty($contextoFilter)) {
                $historicos->where(['UsuarioHistoricos.contexto' => $contextoFilter]);
            }

            if (!empty($ano)) {
                if (!empty($mes)) {
                    $start = new \Cake\I18n\FrozenTime(sprintf('%04d-%02d-01 00:00:00', (int)$ano, (int)$mes));
                    $end = $start->endOfMonth()->setTime(23, 59, 59);
                } else {
                    $start = new \Cake\I18n\FrozenTime(sprintf('%04d-01-01 00:00:00', (int)$ano));
                    $end = new \Cake\I18n\FrozenTime(sprintf('%04d-12-31 23:59:59', (int)$ano));
                }
                $historicos->where([
                    'UsuarioHistoricos.created >=' => $start,
                    'UsuarioHistoricos.created <=' => $end,
                ]);
            }

            if ($this->request->getQuery('acao') === 'excel') {
                $contextoMap = [
                    'E' => 'Edicao',
                    'A' => 'Acesso login unico',
                    'C' => 'Criacao',
                    'P' => 'Perfil',
                ];
                $origemAcessoMap = [
                    'F' => 'Login unico Fiocruz',
                    'G' => 'Gov.br',
                ];

                $excelQuery = clone $historicos;
                $excelQuery->limit(null);
                $rows = $excelQuery->all();
                $header = [
                    'usuario_id',
                    'usuario_nome',
                    'usuario_cpf',
                    'historico_id',
                    'created',
                    'contexto',
                    'alterado_por',
                    'origem_acesso',
                    'diff_json',
                ];

                $fh = fopen('php://temp', 'r+');
                fputcsv($fh, $header, ';');

                foreach ($rows as $historico) {
                    $diff = $historico->diff_json;
                    if (is_array($diff)) {
                        $diff = json_encode($diff, JSON_UNESCAPED_UNICODE);
                    }
                    $contextoLabel = $contextoMap[$historico->contexto] ?? ($historico->contexto ?? '');
                    $origemLabel = $origemAcessoMap[$historico->origem_acesso] ?? ($historico->origem_acesso ?? '');
                    $row = [
                        $usuario->id ?? '',
                        $usuario->nome ?? '',
                        $usuario->cpf ?? '',
                        $historico->id,
                        $historico->created ? $historico->created->format('d/m/Y H:i:s') : '',
                        $contextoLabel,
                        $historico->alterado_por ?? '',
                        $origemLabel,
                        $diff ?? '',
                    ];
                    fputcsv($fh, $row, ';');
                }

                rewind($fh);
                $csv = stream_get_contents($fh);
                fclose($fh);

                $filename = 'usuario_historicos_' . ($usuario->id ?? 'user') . '_' . date('Ymd_His') . '.csv';
                $this->response = $this->response
                    ->withType('csv')
                    ->withDownload($filename);
                $this->response->getBody()->write($csv);
                return $this->response;
            }

            $historicos = $this->paginate($historicos, ['limit' => 30]);

            
            
                $racas = $this->racas;
                $sexo = $this->sexo;
                $deficiencia = $this->deficiencia;
                $documentos = $this->documentos;
                $programa = TableRegistry::getTableLocator()
                    ->get('Programas')
                    ->find('list', ['keyField' => 'id', 'valueField' => 'sigla'])
                    ->toArray();

                $unidades = TableRegistry::getTableLocator()
                    ->get('Unidades')
                    ->find('list', ['keyField' => 'id', 'valueField' => 'sigla'])
                    ->toArray();

                $escolaridades = TableRegistry::getTableLocator()
                    ->get('Escolaridades')
                    ->find('list', ['keyField' => 'id', 'valueField' => 'nome'])
                    ->toArray();

                $vinculos = TableRegistry::getTableLocator()
                    ->get('Vinculos')
                    ->find('list', ['keyField' => 'id', 'valueField' => 'nome'])
                    ->toArray();

            $this->set(compact(
                'documentos',
                'racas',
                'sexo',
                'deficiencia',
                'historicos',
                'unidades',
                'programa',
                'escolaridades',
                'vinculos',
                'contextoFilter',
                'usuario',
                'mes',
                'ano'
            ));
        }
    // fim bloco historicos
    
}
