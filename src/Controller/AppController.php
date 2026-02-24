<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Session;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;


class AppController extends Controller
{
    protected array $resultado = [];
    protected array $cota = [];
    protected array $origem = [];
    protected array $deficiencia = [];
    protected array $racas = [];
    protected array $sexo = [];
    protected array $documentos = [];
    protected array $fonte = [];
    protected $SituacaoHistoricos;
    /**
     * Cache de contexto por edital para evitar reload na mesma request.
     *
     * @var array<int|string, array>
     */
    protected array $contextCache = [];
    

    public function initialize(): void
    {
        parent::initialize();

        //$this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        $this->loadComponent('Authentication.Authentication');
        $this->loadComponent('ExceptionReporter');
        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/4/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');

        $this->resultado = [
            "A" => "Aprovado", 
            "R" => "Reprovado", 
            "B" => "Banco Reserva", 
            "T" => "Aprovação Automática"];

        $this->cota = [
            'G' => 'Geral',
            'D' => 'Pessoas com Deficiência',
            'I' => 'Pessoas Indigenas',
            'N' => 'Pessoas Negras (Pretos/Pardos)',
            'T' => 'Pessoas Trans'];
        
        


        $this->origem = [
            "N" => "Nova", 
            "R" => "Renovação", 
            "S" => "Substituição", 
            "A" => "Subst Na Vigência", 
            "T" => "Mudança de orientação/projeto"];

            

        $this->deficiencia = [
            'S' => 'Sim',
            'X' => 'Não']; 

        $this->racas = [
            'B' => 'Branca',
            'N' => 'Preta',
            'P' => 'Parda',
            'A' => 'Amarela',
            'I' => 'Indigena',
            'X' => 'Não desejo informar'];        

        $this->sexo = [
            'M' => 'Masculino',
            'F' => 'Feminino',
            'T' => 'Transgênero',
            'X' => 'Não Declarar'];  
        
        $this->documentos = [
            'P' => 'Passaporte', 
            'R' => 'RG', 
            'C' => 'Conselho de classe', 
            'H' => 'Habilitação', 
            'T' => 'CTPS'];    
            
        $this->fonte=[
            'C'=>'CNPQ',
            'F'=>'Fiocruz',
            'P'=>'Cogepe',
            'T'=>'Temporario'];
    }

    protected function getIdentityAtual()
    {
        $identity = $this->Authentication->getIdentity();
        if (!$identity) {
            $identity = $this->request->getAttribute('identity');
        }
        return $identity;
    }

    protected function ehYoda(): bool
    {
        $identity = $this->getIdentityAtual();
        if (!$identity) {
            return false;
        }
        if (is_array($identity)) {
            return !empty($identity['yoda']);
        }
        return !empty($identity->yoda);
    }

    protected function ehTi(): bool
    {
        $identity = $this->getIdentityAtual();
        if (!$identity) {
            return false;
        }
        if (is_array($identity)) {
            return !empty($identity['yoda']) || !empty($identity['jedi']);
        }
        return !empty($identity->yoda) || !empty($identity->jedi);
    }

    

    public function idade($nascimento, $completo = true) 
    {
        if(date("Y", strtotime($nascimento)) >= 1900) {
            if($completo) {
                $idade = date_diff(
                    date_create(),
                    date_create(date('Y-m-d H:i:s', strtotime($nascimento))),
                )->format('%y.%m');
            } else {
                $idade = date_diff(
                    date_create(),
                    date_create(date('Y-m-d H:i:s', strtotime($nascimento))),
                )->format('%y');
            }            
        } else {
            
            $idade = (date('Y') - $nascimento->i18nFormat('yyyy'));
        }

    
        return $idade;
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
         $this->Authentication->addUnauthenticatedActions([
        'login',
        'loginUnico'
        ]);
    
        /*
        if(!in_array($this->request->getParam('action'), ['login', 'loginUnico', 'manutencao'])) {
            return $this->redirect(['controller' => 'Index', 'action' => 'manutencao']);
        }
            */

        /*
        if(!in_array($this->request->getParam('action'), ['login', 'loginUnico', 'manutencao'])) {
            return $this->redirect(['controller' => 'Index', 'action' => 'manutencao']);
        }
        */

        $this->set('usuario_logado', null);
        $subs = $canc = $atv = $atv_pdj = $subs_pdj = $canc_pdj = $feedback = 0;

       // dd($this->request->getAttribute('authentication'));
        if($this->Authentication->getIdentity()) {
            //$usuario = $this->Authentication->getIdentity();


            $_user = $this->Authentication->getIdentity();
            $usuario = TableRegistry::getTableLocator()->get('Usuarios')->get($_user->id);
            $this->Authentication->setIdentity($usuario);


            if(!$this->request->is('ajax')) {
                if(($usuario->last_data_update_date == '') || ($usuario->last_data_update_date->i18nFormat('yyyy-MM-dd HH:mm') < date('Y-m-d H:i', strtotime('6 months ago')))) { 
                    (($usuario->last_data_update_date == '')?($this->Flash->error('Esse é seu primeiro acesso!')):($this->Flash->error('Suas informações foram atualizadas há mais de 6 meses! Por favor, revise-as para continuar')));
                    $this->request->getSession()->write('force_update', true);
                    if($this->request->getParam('controller') != 'Users') { 
                        return $this->redirect(['controller' => 'Users', 'action' => 'editar', $usuario->id]);
                    }
                    if($this->request->getParam('action') != 'editar' ) {
                        return $this->redirect(['controller' => 'Users', 'action' => 'editar', $usuario->id]);
                    }
                }                                
            }

            $this->set('usuario_logado', [
                'id' => $usuario->id,
                'cpf' => $usuario->cpf,
                'nome' => $usuario->nome,
                'siape'=> $usuario->matricula_siape,
                'vinculo_id'=> $usuario->vinculo_id,
                'escolaridade_id'=> $usuario->escolaridade_id, 
                'unidade_id'=> $usuario->unidade_id, 
                'yoda'=>$usuario->yoda,
                'jedi'=>$usuario->jedi,
                'padauan'=>$usuario->padauan
            ]);

            /*
            if(date_diff((new DateTime($usuario->last_data_update_date)), (new DateTime('now'))) > 180 ) {
                return $this->redirect(['controller' => 'Usuarios', 'action' => 'editar']);
            }
            */
            

            
            if($usuario->yoda) {
                $canc = TableRegistry::getTableLocator()->get('ProjetoBolsistas')->find()->where(['situacao in' => ['C', 'W'], 'deleted'=>0])->count();
                $canc_pdj = TableRegistry::getTableLocator()->get('PdjInscricoes')->find()->where(['situacao in' => ['C', 'W'], 'deleted IS NULL'])->count();

                $subs = TableRegistry::getTableLocator()->get('ProjetoBolsistas')->find()->where(['situacao' => 'U' , 'deleted'=>0])->count();
                $subs_pdj = TableRegistry::getTableLocator()->get('PdjInscricoes')->find()->where(['situacao' => 'U' , 'deleted IS NULL'])->count();

                $atv = TableRegistry::getTableLocator()->get('ProjetoBolsistas')->find()->where(['vigente' => 1,  'deleted'=>0])->count();
                $atv_pdj = TableRegistry::getTableLocator()->get('PdjInscricoes')->find()->where(['vigente' => 1,  'deleted IS NULL'])->count();

                $feedback = TableRegistry::getTableLocator()->get('Feedbacks')->find()->where(['situacao' => 'N'])->count();


            }
                
            /*
            if($usuario->jedi) {
                $canc = TableRegistry::getTableLocator()->get('ProjetoBolsistas')->find()
                    ->join(['table'=>'usuarios',
                    'alias'=>'AB','type'=>'INNER', 
                    'conditions'=>'AB.id = ProjetoBolsistas.orientador'])
                    ->where(['situacao in' => ['C', 'W'], 'deleted'=>0,
                    'AB.unidade_id'=>$this->request->getAttribute('identity')['unidade_id']])
                    ->count();
                $subs = TableRegistry::getTableLocator()->get('ProjetoBolsistas')->find()
                    ->join(['table'=>'usuarios',
                    'alias'=>'AB','type'=>'INNER', 
                    'conditions'=>'AB.id = ProjetoBolsistas.orientador'])
                    ->where(['situacao' => 'U' , 'deleted'=>0,
                    'AB.unidade_id'=>$this->request->getAttribute('identity')['unidade_id']])
                    ->count();
                $atv = TableRegistry::getTableLocator()->get('ProjetoBolsistas')->find()
                ->join(['table'=>'usuarios',
                'alias'=>'AB','type'=>'INNER', 
                'conditions'=>'AB.id = ProjetoBolsistas.orientador'])
                ->where(['vigente' => 1,  'deleted'=>0,
                'AB.unidade_id'=>$this->request->getAttribute('identity')['unidade_id']])
                ->count();
                $atv_pdj = TableRegistry::getTableLocator()->get('PdjInscricoes')->find()
                ->join(['table'=>'usuarios',
                'alias'=>'AB','type'=>'INNER', 
                'conditions'=>'AB.id = PdjInscricoes.usuario_id'])
                ->where(['vigente' => 1,  'deleted IS NULL',
                'AB.unidade_id'=>$this->request->getAttribute('identity')['unidade_id']])
                ->count();
            }
                */
        }
        $this->set(compact('subs', 'subs_pdj', 'canc','canc_pdj',  'atv', 'atv_pdj','feedback'));

    }

    // funcao V4.5
    /*
    public function uploadArquivo($file, $pasta, $nome_arquivo = null, $limit = 2097152)
    {
        if(is_object($file)) {
            $arquivo = $file->getClientFilename();
            //dd($arquivo);
            if($file->getSize() > $limit) {
                return ['status' => false, 'mensagem' => "O tamanho excede o limite de " . floor($limit/(1024*1024)) . "Mb. O seu arquivo apresenta " . number_format(($file->getSize()/(1024*1024)),2,",","."). " Mb. "];
            }
            if($arquivo != ''){
                if ($nome_arquivo == null){
                    $splited = explode('.', $arquivo);
                    $extensao = end($splited);
                    $nome = uniqid() . '.' .$extensao;
                    //dd($file);
                }else{
                    $nome = $nome_arquivo;
                }

                $diretorio = WWW_ROOT . 'uploads' . DS . $pasta . DS . $nome;
                $file->moveTo($diretorio);
                return ['status' => true, 'mensagem' => "sucesso", 'arquivo' => $nome];
            } else {
                return ['status' => false, 'mensagem' => "Nenhum arquivo foi enviado"];
            }
        } else {
            return ['status' => false, 'mensagem' => "Nenhum arquivo foi enviado"];
        }        
    }
    */

    // funcao V4.5
    /*
    public function uploadArquivoImg($file, $pasta, $nome_arquivo = null, $limit = 2097152)
    {
        if(is_object($file)) {
            $arquivo = $file->getClientFilename();
            //dd($arquivo);
            if($file->getSize() > $limit) {
                return ['status' => false, 'mensagem' => "O tamanho excede o limite de " . floor($limit/(1024*1024)) . "Mb. O seu arquivo apresenta " . number_format(($file->getSize()/(1024*1024)),2,",","."). " Mb. "];
            }
            if($arquivo != ''){
                if ($nome_arquivo == null){
                    $splited = explode('.', $arquivo);
                    $extensao = end($splited);
                    $nome = uniqid() . '.' .$extensao;
                    //dd($file);
                }else{
                    $nome = $nome_arquivo;
                }

                $diretorio = WWW_ROOT . $pasta . DS . $nome;
                $file->moveTo($diretorio);
                return ['status' => true, 'mensagem' => "sucesso", 'arquivo' => $nome];
            } else {
                return ['status' => false, 'mensagem' => "Nenhum arquivo foi enviado"];
            }
        } else {
            return ['status' => false, 'mensagem' => "Nenhum arquivo foi enviado"];
        }        
    }
    */

    // ===== Uso em Edital =====
    protected function loadContext($editalId, $inscricaoId = null): array
    {
        $cacheKey = $editalId === null ? 'null' : (string)(int)$editalId;
        if (isset($this->contextCache[$cacheKey])) {
            return $this->contextCache[$cacheKey];
        }

        $identity = $this->Authentication->getIdentity();
        if (!$identity) {
            $this->Flash->error('Faça login para continuar.');
            return $this->contextCache[$cacheKey] = ['redirect' => $this->redirect(['controller' => 'Users', 'action' => 'login'])];
        }
        $ehTI = !empty($identity->yoda) || !empty($identity->jedi);

        if ($editalId === null) {
            $this->Flash->error('Edital não informado.');
            return $this->contextCache[$cacheKey] = ['redirect' => $this->redirect(['controller' => 'Index', 'action' => 'index'])];
        }

        $edital = $this->fetchTable('Editais')->find()
            ->where(['Editais.id' => (int)$editalId])
            ->first();

        if (!$edital) {
            $this->Flash->error('Edital não localizado.');
            return $this->contextCache[$cacheKey] = ['redirect' => $this->redirect(['controller' => 'Index', 'action' => 'index'])];
        }

        $inscricoesFiltroPeriodo = [];
        if (!empty($inscricaoId) && (int)$inscricaoId > 0) {
            $inscricoesFiltroPeriodo[] = (int)$inscricaoId;
        } else {
            $pass = (array)$this->request->getParam('pass');
            if (isset($pass[1]) && is_numeric((string)$pass[1]) && (int)$pass[1] > 0) {
                $inscricoesFiltroPeriodo[] = (int)$pass[1];
            }
        }

        if (!$ehTI && !$this->loadPeriodo($edital, $identity, 1, [], $inscricoesFiltroPeriodo)) {
            $this->Flash->error('Fora do período de inscrição.');
            return $this->contextCache[$cacheKey] = ['redirect' => $this->redirect(['controller' => 'Index', 'action' => 'index'])];
        }

        if (empty($identity->escolaridade_id) || (int)$identity->escolaridade_id !== 10) {
            $this->Flash->error('Inscrições restritas aos orientadores com doutorado.');
            return $this->contextCache[$cacheKey] = ['redirect' => $this->redirect(['controller' => 'Index', 'action' => 'index'])];
        }

        if (!empty($edital->unidades_permitidas)) {
            $permitidas = array_filter(array_map('intval', explode(',', (string)$edital->unidades_permitidas)));
            if (empty($identity->unidade_id) || !in_array((int)$identity->unidade_id, $permitidas, true)) {
                $this->Flash->error('Edital restrito a orientadores das unidades permitidas.');
                return $this->contextCache[$cacheKey] = ['redirect' => $this->redirect(['controller' => 'Index', 'action' => 'index'])];
            }
        }

        if (!empty($edital->vinculos_permitidos)) {
            $permitidos = array_filter(array_map('intval', explode(',', (string)$edital->vinculos_permitidos)));
            if (empty($identity->vinculo_id) || !in_array((int)$identity->vinculo_id, $permitidos, true)) {
                $this->Flash->error('Edital restrito a orientadores com vínculo permitido.');
                return $this->contextCache[$cacheKey] = ['redirect' => $this->redirect(['controller' => 'Index', 'action' => 'index'])];
            }
        }

        if (!empty($edital->cpf_permitidos)) {
            $cpf = preg_replace('/\D+/', '', (string)$identity->cpf);
            $lista = preg_split('/[\s,;]+/', (string)$edital->cpf_permitidos, -1, PREG_SPLIT_NO_EMPTY);
            $permitidos = [];
            foreach ($lista as $item) {
                $digits = preg_replace('/\D+/', '', (string)$item);
                if ($digits !== '') {
                    $permitidos[] = $digits;
                }
            }

            if ($cpf === '' || !in_array($cpf, $permitidos, true)) {
                $this->Flash->error('Edital restrito a curadores de Coleções Biológicas.');
                return $this->contextCache[$cacheKey] = ['redirect' => $this->redirect(['controller' => 'Index', 'action' => 'index'])];
            }
        }

        return $this->contextCache[$cacheKey] = [
            'edital' => $edital,
            'usuario' => $identity,
            'identity' => $identity,
        ];
    }

    protected function loadPeriodo($edital, $identity, ?int $editaisWkId = null, array $usuarioIds = [], array $inscricoes = []): bool
    {
        $agora = FrozenTime::now();
        $wkId = (int)$editaisWkId;
        $periodoValido = false;
        $temPrazoPadrao = in_array($wkId, [1, 8], true);

        if ($temPrazoPadrao && $wkId === 1) {
            $periodoValido = !empty($edital->inicio_inscricao) && !empty($edital->fim_inscricao)
                && $agora >= $edital->inicio_inscricao
                && $agora <= $edital->fim_inscricao;
        }
        if ($temPrazoPadrao && $wkId === 8) {
            $periodoValido = !empty($edital->inicio_avaliar) && !empty($edital->fim_avaliar)
                && $agora >= $edital->inicio_avaliar
                && $agora <= $edital->fim_avaliar;
        }
        if ($periodoValido) {
            return true;
        }

        $usuarioLogadoId = (int)($identity->id ?? 0);
        $usuariosFiltro = array_values(array_unique(array_map('intval', array_filter($usuarioIds, fn($v) => (int)$v > 0))));
        if ($usuarioLogadoId > 0 && !in_array($usuarioLogadoId, $usuariosFiltro, true)) {
            $usuariosFiltro[] = $usuarioLogadoId;
        }
        $inscricoesFiltro = array_values(array_unique(array_map('intval', array_filter($inscricoes, fn($v) => (int)$v > 0))));

        $condicoes = [
            'EditaisPrazos.editai_id' => (int)$edital->id,
            'EditaisPrazos.deleted IS' => null,
            'EditaisPrazos.inicio <=' => $agora,
            'EditaisPrazos.fim >=' => $agora,
        ];
        if ($editaisWkId !== null) {
            $condicoes['EditaisPrazos.editais_wk_id'] = $editaisWkId;
        }

        $excecao = $this->fetchTable('EditaisPrazos')->find()
            ->select(['EditaisPrazos.id'])
            ->where($condicoes)
            ->andWhere(function ($exp, $q) use ($usuariosFiltro, $inscricoesFiltro) {
                $or = [];

                // Excecao geral: sem usuario e sem inscricao.
                $or[] = [
                    'EditaisPrazos.usuario_id IS' => null,
                    'OR' => [
                        ['EditaisPrazos.inscricao IS' => null],
                        ['EditaisPrazos.inscricao' => ''],
                    ],
                ];

                // Excecao especifica por usuario.
                if (!empty($usuariosFiltro)) {
                    $or[] = ['EditaisPrazos.usuario_id IN' => $usuariosFiltro];
                }

                // Excecao especifica por inscricao (valor unico ou CSV).
                if (!empty($inscricoesFiltro)) {
                    foreach ($inscricoesFiltro as $inscricaoId) {
                        $inscricaoStr = (string)$inscricaoId;
                        $or[] = ['EditaisPrazos.inscricao' => $inscricaoStr];
                        $or[] = $q->newExpr(
                            'FIND_IN_SET(' . (int)$inscricaoId . ", REPLACE(COALESCE(EditaisPrazos.inscricao, ''), ' ', '')) > 0"
                        );
                    }
                }

                return ['OR' => $or];
            })
            ->first();

        return (bool)$excecao;
    }

    protected function loadInscricaoEditavel(array $context, $inscricaoId = null): array
    {
        $edital = $context['edital'];
        $identity = $context['identity'];
        $ehTI = !empty($identity->yoda) || !empty($identity->jedi);

        $inscricaoFiltroPeriodo = [];
        if (!empty($inscricaoId)) {
            $inscricaoFiltroPeriodo[] = (int)$inscricaoId;
        }

        if (!$ehTI && !$this->loadPeriodo($edital, $identity, 1, [], $inscricaoFiltroPeriodo)) {
            $this->Flash->error('Fora do período de inscrição.');
            return ['redirect' => $this->redirect(['controller' => 'Index', 'action' => 'index'])];
        }

        if (empty($inscricaoId)) {
            $this->Flash->error('Inscricao nao informada.');
            return ['redirect' => $this->redirect(['controller' => 'Index', 'action' => 'index'])];
        }

        $inscricao = $this->fetchTable('ProjetoBolsistas')->find()
            ->where([
                'ProjetoBolsistas.id' => (int)$inscricaoId,
                'ProjetoBolsistas.editai_id' => (int)$edital->id,
                'ProjetoBolsistas.deleted' => 0,
            ])
            ->first();

        if (!$inscricao) {
            $this->Flash->error('Inscricao nao localizada iu deletada. Reinicie o processo.');
            return ['redirect' => $this->redirect(['controller' => 'Index', 'action' => 'index'])];
        }

        if ((int)$inscricao->orientador !== (int)$identity->id && !$ehTI) {
            $this->Flash->error('Acesso negado. Somente o orientador pode alterar a inscrição');
            return ['redirect' => $this->redirect(['controller' => 'Index', 'action' => 'index'])];
        }

        if (!in_array((int)$inscricao->fase_id, [1, 3], true)) {
            $this->Flash->error('Inscricao indisponivel para edicao nesta fase.');
            return ['redirect' => $this->redirect(['controller' => 'Index', 'action' => 'index'])];
        }

        return ['inscricao' => $inscricao];
    }

    public function anexarInscricao($anexos, $projeto, $inscricao, $raic, $editar = false, $bloco = null, $pdj = null)
    {
        $tblAnexo = TableRegistry::getTableLocator()->get('Anexos');

        foreach ($anexos as $tipo => $anexo) {
            $tipoId = (int)$tipo;
            if (!is_object($anexo)) {
                continue;
            }
            $arquivo = $anexo->getClientFilename();
            if ($arquivo === '') {
                continue;
            }

            $dadosUpload = ['arquivo' => $anexo];
            $dadosUpload = $this->handleUpload($dadosUpload, 'arquivo', 'anexos');
            if (empty($dadosUpload['arquivo'])) {
                return false;
            }

            $criteria = [
                ($projeto !== null ? ['projeto_id' => $projeto] : 'projeto_id IS NULL'),
                ($inscricao !== null ? ['projeto_bolsista_id' => $inscricao] : 'projeto_bolsista_id IS NULL'),
                ($raic !== null ? ['raic_id' => $raic] : 'raic_id IS NULL'),
                ($pdj !== null ? ['pdj_inscricoe_id' => $pdj] : 'pdj_inscricoe_id IS NULL'),
                'deleted IS NULL',
                'anexos_tipo_id' => $tipoId,
            ];

            if ($editar) {
                $velhoAnexo = $tblAnexo->find()->where($criteria)->first();
                if ($velhoAnexo) {
                    $velhoAnexo->deleted = date('Y-m-d H:i:s');
                    if (!$tblAnexo->save($velhoAnexo)) {
                        return false;
                    }
                }
            } else {
                $tblAnexo->updateAll([
                    'deleted' => date('Y-m-d H:i:s'),
                ], $criteria);
            }

            $novoAnexo = $tblAnexo->newEmptyEntity();
            $novoAnexo->anexo = $dadosUpload['arquivo'];
            $novoAnexo->projeto_id = $projeto;
            $novoAnexo->projeto_bolsista_id = $inscricao;
            $novoAnexo->raic_id = $raic;
            $novoAnexo->pdj_inscricoe_id = $pdj;
            $novoAnexo->anexos_tipo_id = $tipoId;
            $novoAnexo->usuario_id = $this->request->getAttribute('identity')->id;
            if ($bloco !== null) {
                $novoAnexo->bloco = $bloco;
            }

            if (!$tblAnexo->save($novoAnexo)) {
                return false;
            }
        }

        return true;
    }

    protected function limparAnexosPorBloco(int $inscricaoId, string $bloco): void
    {
        $bloco = strtoupper(trim($bloco));
        if ($inscricaoId <= 0 || $bloco === '') {
            return;
        }
        $tipos = $this->fetchTable('AnexosTipos')->find()
            ->select(['id'])
            ->where([
                'AnexosTipos.bloco' => $bloco,
                'AnexosTipos.deleted' => 0,
            ])
            ->all();
        $tiposIds = [];
        foreach ($tipos as $tipo) {
            $tiposIds[] = (int)$tipo->id;
        }
        if (empty($tiposIds)) {
            return;
        }
        $this->fetchTable('Anexos')->updateAll(
            ['deleted' => date('Y-m-d H:i:s')],
            [
                'projeto_bolsista_id' => $inscricaoId,
                'anexos_tipo_id IN' => $tiposIds,
                'deleted IS' => null,
            ]
        );
    }

    /**
     * Ações rápidas de anexo via ícones (alterar/excluir) para inscrição.
     * Retorna:
     * - true: uma ação foi tratada (sucesso ou erro já sinalizado via Flash)
     * - null: nenhuma ação rápida de anexo foi solicitada
     */
    public function processarAcaoRapidaAnexoInscricao(array $dados, ?int $projetoId, int $inscricaoId, array $tiposPermitidos = []): ?bool
    {
        $tipos = array_map('intval', $tiposPermitidos);
        $anexoAcao = (string)($dados['anexo_acao'] ?? '');
        $anexoTipo = (int)($dados['anexo_tipo'] ?? 0);

        $tipoExcluir = 0;
        if ($anexoAcao === 'excluir' && $anexoTipo > 0) {
            $tipoExcluir = $anexoTipo;
        } else {
            $tipoExcluir = (int)($dados['excluir_anexo_tipo'] ?? 0);
        }
        if ($tipoExcluir > 0) {
            if (!in_array($tipoExcluir, $tipos, true)) {
                $this->Flash->error('Tipo de anexo inválido para exclusão.');
                return true;
            }

            try {
                $tblAnexos = $this->fetchTable('Anexos');
                $anexoAtual = $tblAnexos->find()
                    ->where([
                        'Anexos.projeto_bolsista_id' => $inscricaoId,
                        'Anexos.anexos_tipo_id' => $tipoExcluir,
                        'Anexos.deleted IS' => null,
                    ])
                    ->orderBy(['Anexos.id' => 'DESC'])
                    ->first();

                if (!$anexoAtual) {
                    $this->Flash->error('Anexo não localizado para exclusão.');
                    return true;
                }

                $anexoAtual->deleted = date('Y-m-d H:i:s');
                $tblAnexos->saveOrFail($anexoAtual);
                $this->Flash->success('Anexo excluído com sucesso.');
            } catch (\Throwable $e) {
                $this->ExceptionReporter->report($e, 'Erro no Sistema - excluir anexo');
                $this->Flash->error('Não foi possível excluir o anexo.');
            }
            return true;
        }

        $tipoAlterar = 0;
        if ($anexoAcao === 'alterar' && $anexoTipo > 0) {
            $tipoAlterar = $anexoTipo;
        } else {
            $tipoAlterar = (int)($dados['alterar_anexo_tipo'] ?? 0);
        }
        if ($tipoAlterar > 0) {
            if (!in_array($tipoAlterar, $tipos, true)) {
                $this->Flash->error('Tipo de anexo inválido para alteração.');
                return true;
            }

            $arquivo = $dados['anexos'][$tipoAlterar] ?? null;
            if (!is_object($arquivo) || $arquivo->getClientFilename() === '') {
                $this->Flash->error('Selecione um arquivo para alterar o anexo.');
                return true;
            }

            try {
                $ok = $this->anexarInscricao([$tipoAlterar => $arquivo], $projetoId, $inscricaoId, null, true);
                if (!$ok) {
                    $this->Flash->error('Não foi possível alterar o anexo.');
                } else {
                    $this->Flash->success('Anexo alterado com sucesso.');
                }
            } catch (\Throwable $e) {
                $this->ExceptionReporter->report($e, 'Erro no Sistema - alterar anexo');
                $this->Flash->error('Não foi possível alterar o anexo.');
            }
            return true;
        }

        return null;
    }

    public function validaCPF($cpf)
    {
        if(empty($cpf)) {
            return false;
        }
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
        if (strlen($cpf) != 11) {
            return false;
        }
        else if ($cpf == '00000000000' ||
                 $cpf == '11111111111' ||
                 $cpf == '22222222222' ||
                 $cpf == '33333333333' ||
                 $cpf == '44444444444' ||
                 $cpf == '55555555555' ||
                 $cpf == '66666666666' ||
                 $cpf == '77777777777' ||
                 $cpf == '88888888888' ||
                 $cpf == '99999999999'){
            return false;
         } else {
            for ($t = 9; $t < 11; $t++) {

                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf[$c] * (($t + 1) - $c);
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cpf[$c] != $d) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    public function checkCoorientador(
        int $coorientadorId,
        int $orientadorId,
        ?int $programaId = null,
        ?int $editalId = null,
        ?int $inscricaoIdExcluir = null
    ): ?string {
        $usuariosTable = TableRegistry::getTableLocator()->get('Usuarios');
        $vinculosTable = TableRegistry::getTableLocator()->get('Vinculos');
        if (($programaId ?? 0) <= 0 && !empty($editalId)) {
            $programaId = (int)(TableRegistry::getTableLocator()->get('Editais')->find()
                ->select(['programa_id'])
                ->where(['Editais.id' => (int)$editalId])
                ->first()
                ?->programa_id ?? 0);
        }

        $inscricaoTable = ((int)$programaId > 1)
            ? TableRegistry::getTableLocator()->get('ProjetoBolsistas')
            : TableRegistry::getTableLocator()->get('PdjInscricoes');

        $coorientador = $usuariosTable->find()
            ->select(['id', 'escolaridade_id', 'vinculo_id'])
            ->where(['Usuarios.id' => $coorientadorId])
            ->first();
        if (!$coorientador) {
            return 'Coorientador nao localizado na base.';
        }

        if ($coorientadorId === $orientadorId) {
            return 'O CPF informado e o mesmo do orientador. Indique um coorientador diferente.';
        }

        if ((int)($coorientador->escolaridade_id ?? 0) !== 10) {
            return 'Coorientador inelegivel: a escolaridade deve ser Doutorado (id=10).';
        }

        $orientador = $usuariosTable->find()
            ->select(['id', 'vinculo_id'])
            ->where(['Usuarios.id' => $orientadorId])
            ->first();

        if ($orientador && !empty($orientador->vinculo_id)) {
            $idsVinculo = [(int)$orientador->vinculo_id];
            if (!empty($coorientador->vinculo_id)) {
                $idsVinculo[] = (int)$coorientador->vinculo_id;
            }
            $idsVinculo = array_values(array_unique(array_filter($idsVinculo)));

            if (!empty($idsVinculo)) {
                $vinculos = $vinculosTable->find()
                    ->select(['id', 'servidor'])
                    ->where(['Vinculos.id IN' => $idsVinculo])
                    ->enableHydration(false)
                    ->all()
                    ->toArray();

                $porId = [];
                foreach ($vinculos as $item) {
                    $porId[(int)$item['id']] = (int)($item['servidor'] ?? 0);
                }

                $orientadorServidor = $porId[(int)$orientador->vinculo_id] ?? 0;
                $coorientadorServidor = $porId[(int)($coorientador->vinculo_id ?? 0)] ?? 0;
                if ($orientadorServidor === 0 && $coorientadorServidor !== 1) {
                    return 'Coorientador inelegivel: para orientador com vinculo nao servidor, o coorientador precisa ter vinculo de servidor.';
                }
            }
        }

        $condicoesInscricaoBase = [
            'coorientador' => $coorientadorId,
            'fase_id <' => 5,
        ];
        if ((int)$programaId === 1) {
            $condicoesInscricaoBase[] = 'deleted IS NULL';
        } elseif ((int)$programaId > 1) {
            $condicoesInscricaoBase['deleted'] = 0;
        } 
        if ((int)$programaId > 0) {
            $condicoesInscricaoBase['programa_id'] = (int)$programaId;
        }
       

        $qtdInscricoes = $inscricaoTable->find()
            ->where($condicoesInscricaoBase)
            ->count();
        if ($qtdInscricoes > 0) {
            return 'Coorientador inelegivel: ja existe inscricao em andamento deste coorientador no mesmo programa.';
        }
        return null;
    }

    public function adminOnly($restrito = false)
    {
        if(!$this->request->getAttribute('identity')['yoda'] && $restrito) {
            $this->Flash->error('Área restrita apenas a administradores.');

            return $this->redirect(['controller' => 'index', 'action' => 'dashboard']);
        }
        if(!$this->request->getAttribute('identity')['yoda'] && !$this->request->getAttribute('identity')['jedi']) {
            $this->Flash->error('Área restrita a administradores e coordenadores de unidades.');

            return $this->redirect(['controller' => 'index', 'action' => 'dashboard']);
        }

        return true;
    }

    /**
     * Exibe mensagem amigavel para o usuario e registra detalhes tecnicos no ExceptionReporter.
     */
    protected function flashFriendlyException(
        \Throwable $e,
        string $titulo,
        string $mensagemBase = 'Houve um erro na gravacao. Tente novamente.'
    ): void {
        $info = $this->ExceptionReporter->report($e, $titulo);
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

        $mensagem = $mensagemBase . $suffix;
        if (!empty($info['bloqueado'])) {
            $limite = (int)($info['limite_repeticao'] ?? 12);
            $mensagem .= '<br>O sr(a) ja cometeu esse erro ' . $limite . ' vezes.';
            $mensagem .= ' Verifique seu email para ver se ha resposta e aguarde orientacoes.';
        }
        $this->Flash->error($mensagem, ['escape' => false]);
    }

    public function historico($id, $original, $atual, $just, bool $throw = true): bool
    {
        $this->SituacaoHistoricos = TableRegistry::getTableLocator()->get('SituacaoHistoricos');
        $novo = $this->SituacaoHistoricos->newEmptyEntity();
        $novo->projeto_bolsista_id = $id;
        $novo->usuario_id = $this->request->getAttribute('identity')->id;
        $novo->fase_original = $original;
        $novo->fase_atual = $atual;
        $novo->justificativa = $just;

        if ($throw) {
            $this->SituacaoHistoricos->saveOrFail($novo);
            return true;
        }

        if ($this->SituacaoHistoricos->save($novo)) {
            return true;
        }

        $this->Flash->error('Houve um erro na gravacao. Tente novamente');
        return false;
    }
    
    public function acertaData($dt)
    {
        return date('Y-m-d', strtotime(str_replace('/', '-', $dt)));
    }

    public function acerta($dt)
    {
        return date('Y-m-d H:m:s', strtotime(str_replace('/', '-', $dt)));
    }

    public function handleUpload(
        array $dados,
        string $field,
        string $pasta,
        ?string $nomeArquivo = null,
        int $limit = 2097152
    ): array
    {
        if (!isset($dados[$field])) {
            return $dados;
        }

        $file = $dados[$field];
        if (!is_object($file) || $file->getClientFileName() === "") {
            unset($dados[$field]);
            return $dados;
        }

        if ($file->getSize() > $limit) {
            $this->Flash->error(
                "O tamanho excede o limite de " . floor($limit / (1024 * 1024)) . "Mb. O seu arquivo apresenta " .
                number_format(($file->getSize() / (1024 * 1024)), 2, ",", ".") . " Mb. "
            );
            unset($dados[$field]);
            return $dados;
        }

        if ($nomeArquivo === null) {
            $splited = explode('.', $file->getClientFileName());
            $extensao = end($splited);
            $nomeArquivo = date('Ymd_His') . '_' . uniqid() . '.' . $extensao;
        }

        $diretorio = WWW_ROOT . 'uploads' . DS . $pasta . DS . $nomeArquivo;
        $file->moveTo($diretorio);
        $dados[$field] = $nomeArquivo;

        return $dados;
    }
}
