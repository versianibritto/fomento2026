<?php
declare(strict_types=1);

namespace App\Controller;
use Cake\ORM\TableRegistry;
use Cake\I18n\FrozenTime;
use App\Controller\AppController;
use Cake\Mailer\MailerAwareTrait;

use Cake\Database\Exception as DatabaseException;
use Cake\Datasource\ConnectionManager;
use DateTime;

/**
 * Avaliadors Controller
 *
 * @property \App\Model\Table\AvaliadorsTable $Avaliadors
 * @method \App\Model\Entity\Avaliador[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AvaliadorsController extends AppController
{
    use MailerAwareTrait;


    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setLayout('admin');  
        $this->tipo = [
            'N' => 'Nova',
            'V' => 'Renovação',
            'Z' => 'Outras Agencias',
            'J' => 'PDJ Nova',
            'W' => 'Workshop'
        ];  
    }

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Lista = TableRegistry::getTableLocator()->get('AvaliadorLista');
        $this->AvaliadorBolsistas = TableRegistry::getTableLocator()->get('AvaliadorBolsistas');
        $this->Raics = TableRegistry::getTableLocator()->get('Raics');
        $this->Aval = TableRegistry::getTableLocator()->get('AvaliadorBolsistas');
        $this->Bolsista = TableRegistry::getTableLocator()->get('ProjetoBolsistas');
        $this->Avaliadors = TableRegistry::getTableLocator()->get('Avaliadors');

    }

    public function limpar($secao, $action )
    {

        $this->request->getSession()->delete($secao);
        $busca = null;
       // $w = [];
        return $this->redirect(['action'=>$action]);
    }

    //ok 2025
    // coordenador de unidade
    // lista os avaliadores para serrem convidados
    public function avaliadoresraic()
    {
        if(
            (!($this->request->getAttribute('identity')['yoda'])) && 
            ($this->request->getAttribute('identity')['jedi']==null)
            
        ){
            $this->Flash->error('Área restrita a Coordenadores de Unidade e Auxiliares');
            return $this->redirect(['controller'=>'Index', 'action'=>'dashboard']);
        }

       
        $w = [];      
        $busca = $this->request->getSession()->read('buscaAvaliadoresRaic', [$w]);

        if ($this->request->is(['post', 'put', 'patch'])) {

            $dados = $this->request->getData();
           
            if(($dados['unidade'] != '')){

                array_push($w , ['Avaliadors.unidade_id' => ($dados['unidade'])]);

                /*
                $lista = $this->Avaliadors->find('all')
                ->contain([
                    'Usuarios'=>'Unidades', 
                    'GrandesAreas',
                    'Areas',
                    'Unidades',
                    'Editais'])
                ->where([$w])
                ->where(['Avaliadors.tipo_avaliador IN ("R")'])
                ->where(['Avaliadors.deleted'=>0])
                ->order(['Avaliadors.ano_convite'=>'desc','Usuarios.nome'=>'ASC']);
                */

                $lista = $this->Lista->find('all')
                ->contain(['Avaliadors'=>['Usuarios'=>'Unidades', 
                            'GrandesAreas',
                            'Areas','SubAreas',
                            'Especialidades',
                            'AreasFiocruz','Linhas', 'Unidades', 'Editais']])
                ->where([$w])
                ->where(['Avaliadors.tipo_avaliador IN ("R")'])
                ->order(['Avaliadors.ano_convite'=>'desc','Usuarios.nome'=>'ASC'])
                ;

            }else{
                $this->Flash->error('Selecione um filtro');
                return $this->redirect(['action' => 'avaliadoresraic']);
            }

            $this->request->getSession()->write('buscaAvaliadoresRaic',$w);
            $listas = $this->paginate($lista, ['limit'=>10]);   

        }else{
            $w = $busca;
            if($busca[0]!=[]){
                $lista = $this->Lista->find('all')
                ->contain(['Avaliadors'=>['Usuarios'=>'Unidades', 
                            'GrandesAreas',
                            'Areas','SubAreas',
                            'Especialidades',
                            'AreasFiocruz','Linhas', 'Unidades', 'Editais']])
                ->where([$w])
                ->where(['Avaliadors.tipo_avaliador IN ("R")'])
                ->order(['Avaliadors.ano_convite'=>'desc','Usuarios.nome'=>'ASC'])
                ;
                
                $listas = $this->paginate($lista, ['limit'=>10]);  
            }else{
                $listas=null;
            }
        }
        

        if($this->request->getAttribute('identity')['yoda']){
            $unidades = TableRegistry::getTableLocator()->get('Unidades')->find('list', ['limit' => 220])->order(['Unidades.sigla'=>'ASC']);
        }else{
            if(($this->request->getAttribute('identity')['jedi']!=null) ){
                $un=[];
                $unidades = TableRegistry::getTableLocator()->get('Unidades')
                ->find('list', ['limit' => 220])
                ->where(['Unidades.id IN ('.$this->request->getAttribute('identity')['jedi'].')'])
                ->order(['Unidades.sigla'=>'ASC']);
            }
        }

        $anoAtual = date('Y');
        $anos = [
            $anoAtual => $anoAtual,
            $anoAtual - 1 => $anoAtual - 1
        ];

        $this->set(compact('listas','unidades', 'anos'));
        
    }

    //ok 2025
    // coordenador de unidade
    // lista os avaliadores para serrem convidados
    public function avaliadoresnova()
    {
        if((!($this->request->getAttribute('identity')['yoda']))){
            $this->Flash->error('Área restrita a Gestão de fomento');
            return $this->redirect(['controller'=>'Index', 'action'=>'dashboard']);
        }

       
        $w = [];      
        $busca = $this->request->getSession()->read('buscaAvaliadoresNova', [$w]);

        if ($this->request->is(['post', 'put', 'patch'])) {

            $dados = $this->request->getData();
           
            if(($dados['edital'] != '')){

                array_push($w , ['Avaliadors.editai_id' => ($dados['edital'])]);


                $lista = $this->Avaliadors->find('all')
                ->contain(['Usuarios'=>'Unidades', 
                            'GrandesAreas',
                            'Areas','SubAreas',
                            'Especialidades',
                            'AreasFiocruz','Linhas', 'Unidades', 'Editais'])
                            ->where([$w])
                            ->order(['Usuarios.nome'=>'ASC'])
                ;



            }

            $this->request->getSession()->write('buscaAvaliadoresNova',$w);
            $listas = $this->paginate($lista, ['limit'=>10]);   

        }else{
            $w = $busca;
            if($busca[0]!=[]){
                $lista = $this->Avaliadors->find('all')
                ->contain(['Usuarios'=>'Unidades', 
                            'GrandesAreas',
                            'Areas','SubAreas',
                            'Especialidades',
                            'AreasFiocruz','Linhas', 'Unidades', 'Editais'])
                            ->where([$w])
                            ->order(['Usuarios.nome'=>'ASC'])
                ;
                
                $listas = $this->paginate($lista, ['limit'=>10]);  
            }else{
                $listas=null;
            }
        }
        

        

        $editais = TableRegistry::getTableLocator()->get('Editais')->find('list')
        ->where([
            'inicio_vigencia > NOW()',
            'origem = "N"'])
        ->order(['id' => 'DESC']);



        $this->set(compact('listas','editais'));
        
    }

    //ok 2025 convidar avaliador 
    // convida/confirma o avaliador para a unidade selecionada
    public function convidar($id = null, $tipo = null, $unidade = null)
    {
       
        if(
            (!($this->request->getAttribute('identity')['yoda'])) && 
            ($this->request->getAttribute('identity')['jedi']==null)
            
        ){
            $this->Flash->error('Área restrita a Coordenadores de Unidades e auxiliares');
            return $this->redirect(['controller'=>'Index', 'action'=>'dashboard']);
        }

        $editais = TableRegistry::getTableLocator()->get('Editais')->find()
        ->where([
            'inicio_avaliar < NOW()',
            'tipo = "V"',
            'fim_avaliar > NOW()'])
        ->order(['id' => 'DESC'])->first();

        if (empty($editais)) {
            $this->Flash->error('Não há nenhuma RAIC em periodo de vinculação de avaliadores');
            return $this->redirect(['controller'=>'Index', 'action'=>'dashboard']);
        }

        //dd($editais);

        $this->request->allowMethod(['post', 'put']);
        $avaliador = $this->Avaliadors->get($id);
        $novo = $this->Avaliadors->newEmptyEntity();
        $novo->usuario_id=$avaliador->usuario_id;
        $novo->grandes_area_id=$avaliador->grandes_area_id;
        $novo->area_id=$avaliador->area_id;
        $novo->sub_area_id=$avaliador->sub_area_id;
        $novo->especialidade_id=$avaliador->especialidade_id;
        $novo->areas_fiocruz_id=$avaliador->areas_fiocruz_id;
        $novo->linha_id=$avaliador->linha_id;
        $novo->ano_convite=date('Y');
        $novo->ano_aceite=date('Y');
        $novo->voluntario=$avaliador->voluntario;
        $novo->tipo_avaliador=$tipo;
        $novo->deleted=0;
        $novo->editai_id=$editais->id;
        


        if($unidade!=null){
            $novo->unidade_id=$unidade;
        }else{
           $novo->unidade_id=null;
        }
         
        $novo = $this->Avaliadors->patchEntity($novo, $this->request->getData());
            if ($this->Avaliadors->save($novo)) {
                $this->Flash->success('Registrado o convite do avaliador para este ano');

                return $this->redirect($this->request->referer());
            }
            $this->Flash->error('Houve um erro durante o convite. Tente novamente');
        


    
            return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
        }

    

    //ok 2025 tela cpf para cadastrar um avaliador
    public function add($tipo=null)
    {

        if($tipo==null){
            $this->Flash->error('Erro na validação dos parâmetros');
            return $this->redirect(['controller'=>'Index', 'action'=>'dashboard']);
        }

        if($tipo=='R'){
            if((!($this->request->getAttribute('identity')['yoda'])) && 
                ($this->request->getAttribute('identity')['jedi']==null)){
                $this->Flash->error('Área restrita a Coordenadores de Unidade e auxiliares');
                return $this->redirect(['controller'=>'Index', 'action'=>'dashboard']);
            }

            $editais = TableRegistry::getTableLocator()->get('Editais')->find()
            ->where([
                'inicio_avaliar < NOW()',
                'tipo = "V"',
                'fim_avaliar > NOW()'])
            ->order(['id' => 'DESC'])->first();

            if (empty($editais)) {
                $this->Flash->error('Não há nenhuma RAIC em periodo de vinculação de avaliadores');
                return $this->redirect(['controller'=>'Index', 'action'=>'dashboard']);
            }
            
        }

        if($tipo=='N'){
            if((!($this->request->getAttribute('identity')['yoda'])) ){
                $this->Flash->error('Área restrita a Gestão de Fomento');
                return $this->redirect(['controller'=>'Index', 'action'=>'dashboard']);
            }           
        }


        $avaliador = $this->Avaliadors->newEmptyEntity();

        if($this->request->is(['post', 'put', 'patch'])) {

            $dados = $this->request->getData();

            // Verifica se o CPF avaliador é válido
                $cpf = preg_replace('/[^0-9]/', '', $dados['cpf']);
                if(parent::validaCPF($cpf)) {
                    // Verifica se o usuário existe no Banco
                        $usuario = TableRegistry::getTableLocator()->get('Usuarios')
                        ->find()->where(['cpf' => $dados['cpf']])->first();

                        if(!$usuario){
                            //se nao existe grava
                            $tblUsuario = TableRegistry::getTableLocator()->get("Usuarios");
                            $bolsista = $tblUsuario->newEmptyEntity();
                            $bolsista->cpf = $cpf;
                            $bolsista->nome = $dados['nome'];
                            $bolsista->email = $dados['email'];

                            $nb = $tblUsuario->save($bolsista);

                            if(!$nb) {
                                $erro = true;
                                $this->Flash->error('Não foi possível gravar o Avaliador, por favor verifique os campos, todos precisam ser preenchidos.');
                                return $this->redirect(['action' => 'add', $tipo]);

                            }
                            $usuario=$bolsista;

                        }
                    //
                    
                }else{
                    $this->Flash->error('O formato do CPF é inválido');
                    return $this->redirect(['action' => 'add', $tipo]);
                }
            
            //


            if($usuario) {

                //verifica se ja e um avaliador este ano
                    if($tipo=='R'){
                        $teste = $this->Avaliadors->find('all')
                                ->where(['usuario_id' => $usuario->id])
                                ->where(['tipo_avaliador' => 'R'])
                                ->where(['editai_id' => $editais->id])
                                ->where(['unidade_id' => $dados['unidade']])
                                ->first()
                        ;

                    }else{
                        $teste = $this->Avaliadors->find('all')
                                ->where(['usuario_id' => $usuario->id])
                                ->where(['editai_id' => $dados['editai_id']])
                                ->first()
                        ;
                    }

                    if($teste) {
                        $this->Flash->error('O cadastro NÂO FOI REALIZADO!' );
                        if($tipo=='R'){
                            $this->Flash->error('O avaliador ja esta cadastrado na unidade selecionada para a Raic atual ('.$editais->nome.')' );
                        }
                        if($tipo=='N'){
                            $this->Flash->error('O avaliador ja esta cadastrado no edital selecionado' );
                        }
                        $this->Flash->error('Informe outro avaliador ou outra unidade / edital para esse registro' );

                        return $this->redirect(['action' => 'add', $tipo]);
                    }
                //


                $avaliador->usuario_id=$usuario->id;
                $avaliador->ano_convite=date('Y');
                $avaliador->ano_aceite=date('Y');
                $avaliador->editai_id=($tipo=='R'?$editais->id:$dados['editai_id']);
                $avaliador->tipo_avaliador=$tipo;
                $avaliador->unidade_id=($tipo=='R'?$dados['unidade']:null);


               // $avaliador = $this->Avaliadors->patchEntity($avaliador, $dados);
                if ($this->Avaliadors->save($avaliador)) {
                    $this->Flash->success('Avaliador cadastrado com sucesso');
    
                    return $this->redirect(['controller' => 'Avaliadors', 'action' => ($tipo=='R'?'avaliadoresraic':'avaliadoresnova')]);     
                }
                $this->Flash->error('Houve um erro. tente novamente');
            }else{
                $this->Flash->error('O cpf informado não esta em nossa base. Solicite ao usuario o cadastramento');
                return $this->redirect(['action' => 'add', $tipo]);
            }

            
        }

        $ed_vigentes=null;
        if($this->request->getAttribute('identity')['yoda']){
            $unidades = TableRegistry::getTableLocator()->get('Unidades')->find('list', ['limit' => 220])->order(['Unidades.sigla'=>'ASC']);
            $ed_vigentes = TableRegistry::getTableLocator()->get('Editais')
            ->find('list')
            ->where(['inicio_vigencia > NOW()'])
            ->where(['origem'=>'N'])
    
            ->order(['Editais.nome'=>'Asc']);
        }else{
            if(($this->request->getAttribute('identity')['jedi']!=null) ){
                $un=[];
                $unidades = TableRegistry::getTableLocator()->get('Unidades')
                ->find('list', ['limit' => 220])
                ->where(['Unidades.id IN ('.$this->request->getAttribute('identity')['jedi'].')'])
                ->order(['Unidades.sigla'=>'ASC']);
            }
        }

        $this->set(compact('unidades', 'avaliador', 'ed_vigentes', 'tipo'));

        
    }

    public function areas($id)
    {
        $avaliador = $this->Avaliadors->get($id, [
            'contain' => [],
        ]);
                $areas = TableRegistry::getTableLocator()->get('GrandesAreas')->find('list');
        if($this->request->is(['post','put','patch']))
        {
            $avaliador = $this->Avaliadors->patchEntity($avaliador, $this->request->getData());
            $gravado = $this->Avaliadors->save($avaliador);
            if($gravado){
                $this->Flash->success('Atualizado com sucesso!');
                if($avaliador->tipo_avaliador=='R') {
                    return $this->redirect(['controller' => 'Avaliadors', 'action' => 'avaliadoresraic']);
                }
                if($this->request->getAttribute('identity')['yoda']) {
                    return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
                }



            }else{
                $this->Flash->danger('Erro atualizando a grande área!');
            }
            return $this->redirect($this->request->referer());
        }
        $this->set(compact('avaliador', 'areas'));
    }

    public function cnpq($id)
    {
        $avaliador = $this->Avaliadors->get($id, [
            'contain' => [],
        ]);
        
        $areas = TableRegistry::getTableLocator()->get('Areas')->find('list')->where(['grandes_area_id'=>$avaliador->grandes_area_id]);
        if($this->request->is(['post','put','patch']))
        {
            $avaliador = $this->Avaliadors->patchEntity($avaliador, $this->request->getData());
            $gravado = $this->Avaliadors->save($avaliador);
            if($gravado){
                $this->Flash->success('Atualizado com sucesso!');
                if($avaliador->tipo_avaliador=='R') {
                    return $this->redirect(['controller' => 'Avaliadors', 'action' => 'avaliadoresraic']);
                }
                if($this->request->getAttribute('identity')['yoda']) {
                    return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
                }
            }else{
                $this->Flash->danger('Erro atualizando a área!');
            }
            return $this->redirect($this->request->referer());
        }
        $this->set(compact('avaliador', 'areas'));
    }

    public function editArea($id)
    {
        $avaliador = $this->Avaliadors->get($id, [
            'contain' => ['Editais'],
        ]);
        
        if($this->request->is(['post','put','patch']))
        {
            $avaliador = $this->Avaliadors->patchEntity($avaliador, $this->request->getData());
            $gravado = $this->Avaliadors->save($avaliador);
            if($gravado){
                $this->Flash->success('Atualizado com sucesso!');
                if($avaliador->tipo_avaliador=='R') {
                    return $this->redirect(['controller' => 'Avaliadors', 'action' => 'avaliadoresraic']);
                }
                if($this->request->getAttribute('identity')['yoda']) {
                    return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
                }
            }else{
                $this->Flash->danger('Erro atualizando a área!');
            }
            return $this->redirect($this->request->referer());
        }

        if($avaliador->editai->tipo=='J'){
            $areasF = TableRegistry::getTableLocator()->get('GrandesAreas')->find('list')->where(['GrandesAreas.id > 9'])->order([
                'GrandesAreas.nome' => 'ASC'
            ]);
        }
        else{
            $areasF = TableRegistry::getTableLocator()->get('GrandesAreas')->find('list')->order([
                'GrandesAreas.nome' => 'ASC'
            ]);
        }

        
        
        $this->set(compact('avaliador', 'areasF'));
    }

   

    public function buscaPorArea()
    {
        $this->request->allowMethod(['post', 'ajax']);
        $this->autoRender = false;

        $id = $this->request->getData('id');
        $areasTable = TableRegistry::getTableLocator()->get('Areas');

        $query = $areasTable->find('all')
            ->select(['id', 'nome']);

        if (!empty($id)) {
            $query->where(['grandes_area_id' => $id]);
        }

        $areas = $query->all();

        $resultado = [];
        foreach ($areas as $area) {
            $resultado[] = ['id' => $area->id, 'nome' => $area->nome];
        }

        return $this->response->withType('application/json')->withStringBody(json_encode($resultado));
    }


    

    

    //(parent::validaCPF($cpf))

    function addAvaliadorMassivo() {
        
        if((!$this->request->getAttribute('identity')['yoda']) && ($this->request->getAttribute('identity')['jedi']==null)){
            $this->Flash->error('Restrito a Coordenação de unidade');
            return $this->redirect(['controller'=>'Index', 'action'=>'dashboard']);
        }

        
        if($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();

            $cpfsInput = isset($dados['cpfs']) ? $dados['cpfs'] : ''; 


            $editaisSelecionados = array_values(array_filter($dados['editais'], function($valor) {
                return !empty($valor) && $valor != '0';
            }));
            
            if($editaisSelecionados==null){
                $this->Flash->error('Informe os editais');
                return $this->redirect(['controller'=>'Avaliadors', 'action'=>'addAvaliadorMassivo']);
            }

            // Valida e processa os CPFs
            $cpfs = array_map('trim', explode(',', $cpfsInput));

            $usuariosTable = TableRegistry::getTableLocator()->get('Usuarios');
            $avaliadoresTable = TableRegistry::getTableLocator()->get('Avaliadors');
           

            $anoAtual = FrozenTime::now()->year;
            $erros = [];
            $avaliadoresParaSalvar = [];


            foreach ($cpfs as $cpf) {
                if (empty($cpf) || !parent::validaCPF($cpf)) {
                    array_push($erros, 'cpf => '.$cpf.', status => "inválido / faltando dígito');
                }
                
                $usuario = $usuariosTable->find()->where(['cpf' => $cpf])->first();
                
                if (!$usuario) {
                    array_push($erros, 'cpf => '.$cpf.', status => usuario não localizado na base');
                }

                if($usuario){
                    foreach ($editaisSelecionados as $editalId){
                        $avaliador = $avaliadoresTable->find()
                        ->where(['usuario_id' => $usuario->id])
                        ->first();

                        if($avaliador){

                            $avaliador_edital = $avaliadoresTable->find()
                            ->where(['usuario_id' => $usuario->id])
                            ->where(['editai_id' => $editalId])
                            ->first();


                            if($avaliador_edital){
                                array_push($erros, 'cpf => '.$cpf.', status => Já estava vinculado ao edital '.$editalId);
                            }else{
                                $novoAvaliador = $avaliadoresTable->newEntity([
                                    'usuario_id' => $avaliador->usuario_id,
                                    'grandes_area_id' => $avaliador->grandes_area_id,
                                    'area_id' => $avaliador->area_id,
                                    'sub_area_id' => $avaliador->sub_area_id,
                                    'especialidade_id' => $avaliador->especialidade_id,
                                    'areas_fiocruz_id' => $avaliador->areas_fiocruz_id,
                                    'linha_id' => $avaliador->linha_id,
                                    'ano_convite' => $anoAtual,  // Atualiza o ano_convite
                                    'ano_aceite' => $anoAtual,   // Atualiza o ano_aceite
                                    'tipo_avaliador' => 'N',
                                    'deleted' => 0,
                                    'editai_id' => $editalId,    // Atribui o edital selecionado
                                    'aceite' => 1,
                                ]);
                                $avaliadoresParaSalvar[] = $novoAvaliador;

                            }
                            
                        }else{
                            $novoAvaliador = $avaliadoresTable->newEntity([
                                'usuario_id' => $usuario->id,
                                'ano_convite' => $anoAtual,  // Atualiza o ano_convite
                                'ano_aceite' => $anoAtual,   // Atualiza o ano_aceite
                                'tipo_avaliador' => 'N',
                                'deleted' => 0,
                                'editai_id' => $editalId,    // Atribui o edital selecionado
                                'aceite' => 1,
                            ]);
                            $avaliadoresParaSalvar[] = $novoAvaliador;

                        }


                    }
                }
                
            }
            // Após percorrer todos os CPFs e validar, iniciar a transação com o método transactional
            try {
                // Usando o método transactional
                $avaliadoresTable->getConnection()->transactional(function ($connection) use ($avaliadoresParaSalvar, $avaliadoresTable, &$erros) {
                    if (!empty($avaliadoresParaSalvar)) {
                        foreach ($avaliadoresParaSalvar as $item) {

                            if (!$avaliadoresTable->save($item)) {
                                throw new \Exception("Erro ao salvar o avaliador para o CPF: {$item['cpf']}");
                            }

                        }
                        $this->Flash->success('os demais cpfs foram gravados com sucesso');

                    
                    }
                    if($erros){
                        $mensagemErro = '<ul><li>' . implode('</li><li>', $erros) . '</li></ul>';
                        $this->Flash->error($mensagemErro, ['escape' => false]);
                    }


                });
            } catch (\Exception $e) {
                // Em caso de erro, retorna a mensagem
                $retorno[] = ["status" => "Erro na gravação em lote", "detalhes" => $e->getMessage()];
            }
            
            return $this->redirect(['controller'=>'Avaliadors', 'action'=>'addAvaliadorMassivo']);

        }


        $editais = [];
        $unidades = [];


        if($this->request->getAttribute('identity')['yoda']){
            $editais = TableRegistry::getTableLocator()->get('Editais')
                ->find('list')->where([
                'inicio_vigencia > NOW()',
                'origem IN ("N", "W")'
            ])->toArray();
        }

        
        
        $this->set(compact('editais', 'unidades'));

    }
 
    

    //ok 2023 confirma avaliador
    public function confirmar($id = null)
    {
       if(!$this->request->getAttribute('identity')['yoda']){
           $this->Flash->error('Área restrita a Coordenadores');
           return $this->redirect(['controller'=>'/', 'action'=>'/']);
       }

       if($this->request->getAttribute('identity')['yoda'])
       {
           $avaliadores = $this->Lista->find('all')
           ->contain(['Avaliadors'=>['Usuarios'=>'Unidades', 
                       'GrandesAreas',
                       'Areas','SubAreas',
                       'Especialidades',
                       'AreasFiocruz','Linhas', 'Unidades']])
           ->where(['Avaliadors.tipo_avaliador IN ("N")'])
           ->order(['Avaliadors.ano_convite'=>'desc','Usuarios.nome'=>'ASC'])
           ;
       }else{
           if($this->request->getAttribute('identity')['jedi'])
           {
               $avaliadores = $this->Lista->find('all')
               ->contain(['Avaliadors'=>['Usuarios'=>'Unidades', 
                           'GrandesAreas',
                           'Areas','SubAreas',
                           'Especialidades',
                           'AreasFiocruz','Linhas', 'Unidades']])
               ->where(['Avaliadors.tipo_avaliador IN ("R")'])
               ->where(['Avaliadors.unidade_id'=>$this->request->getAttribute('identity')['unidade_id'] ])

               ->order(['Avaliadors.ano_convite'=>'desc','Usuarios.nome'=>'ASC'])
               ;
           }
       }


       $this->set(compact('avaliadores'));
       $this->set('_serialize', ['avaliadores']);
    }


      

    //???
    public function lista($limpar=false)
    {

        if((!$this->request->getAttribute('identity')['yoda'])
        &&(!$this->request->getAttribute('identity')['jedi'])){
        
            $this->Flash->default('Você não possui acesso a este módulo!');
            return $this->redirect(['controller'=>'projetos', 'action'=>'index']);
        }
        $w = [];
        $busca = $this->request->getSession()->read('pibic_busca_avaliadors');

        //se for post e pq filtraram a busca
        if($this->request->is('post')){
            if($this->request->getData('busca')!=''){
                $w = [$this->request->getData('busca').' LIKE "%'.preg_replace('[ ]','%',$this->request->getData('valor')).'%"'];
                $this->request->getSession()->write('pibic_busca_avaliadors',$w);
                $this->redirect(['action'=>'lista']);
            }
        // se nao tiver buscado nada  
        }elseif($busca != ''){
            $w = $busca;
        }

        if($limpar){
            $this->request->getSession()->delete('pibic_busca_avaliadors');
            $busca = null;
            $w = [];
            $this->redirect(['action'=>'lista']);
        }
     
        if(($this->request->getAttribute('identity')['yoda'])){
        
                $usuarios = $this->Avaliadors->find('all')->contain([
                    'Usuarios',
                    'Unidades',
                    'Areas',
                    'AreasFiocruz',
                    'Linhas'
                ])
                ->join(['table'=>'usuarios',
                'alias'=>'AB','type'=>'INNER', 
                'conditions'=>'AB.id = Avaliadors.usuario_id'])
                ->where([$w])
                ->order([
                    'AB.nome' => 'Asc']);
        }

        if(($this->request->getAttribute('identity')['jedi'])){
            $usuarios = $this->Avaliadors->find('all')->contain([
                'Usuarios',
                'Unidades',
                'Areas',
                'AreasFiocruz',
                'Linhas',
            ])
            ->join(['table'=>'usuarios',
            'alias'=>'AB','type'=>'INNER', 
            'conditions'=>'AB.id = Avaliadors.usuario_id'])
            ->where(['Avaliadors.unidade_id'=>$this->request->getAttribute('identity')['unidade_id']])
            ->where([$w])

            ->order([
                'AB.nome' => 'Asc']);
        }
        
    
        
        

        $this->paginate = ['limit'=>10];
        $users = $this->paginate($usuarios);

        $this->set(compact('users','busca'));
        $this->set('_serialize', ['users']);
    }

    //??? funciona como a lista so que para nova
    public function nova($limpar=false)
    {
        if((!$this->request->getAttribute('identity')['yoda']) 
        ){
            $this->Flash->error('Restrito a administradores');
            return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
        }

        $unidades = TableRegistry::getTableLocator()->get('Unidades')
            ->find('list');

        if($this->request->is('post')){

            $dados = $this->request->getData();
            $unidade=$dados['busca'];

            if($this->request->getAttribute('identity')['yoda']){
               
                if($dados['busca']==''){

                    $novas = $this->Avaliadors->find('all')->contain([
                        'Usuarios'=>'Unidades',
                        'Unidades',
                        'Areas',
                        'AreasFiocruz',
                        'Linhas'
                    ])
                    ->join(['table'=>'usuarios',
                    'alias'=>'AB','type'=>'INNER', 
                    'conditions'=>'AB.id = Avaliadors.usuario_id'])
                    ->where(['Avaliadors.tipo_avaliador'=>'N'])

                    ->order([
                        'AB.nome' => 'Asc']);

                }else{

                    $novas = $this->Avaliadors->find('all')->contain([
                        'Usuarios'=>'Unidades',
                        'Unidades',
                        'Areas',
                        'AreasFiocruz',
                        'Linhas'
                    ])
                    ->join(['table'=>'usuarios',
                    'alias'=>'AB','type'=>'INNER', 
                    'conditions'=>'AB.id = Avaliadors.usuario_id'])
                    ->where(['Avaliadors.unidade_id'=>$unidade])
                    ->where(['Avaliadors.tipo_avaliador'=>'N'])
                    ->order([
                        'AB.nome' => 'Asc']);

                    
                }
            }

        }else{
            if($this->request->getAttribute('identity')['yoda']){
                $novas = $this->Avaliadors->find('all')->contain([
                    'Usuarios'=>'Unidades',
                    'Unidades',
                    'Areas',
                    'AreasFiocruz',
                    'Linhas'
                ])
                ->join(['table'=>'usuarios',
                'alias'=>'AB','type'=>'INNER', 
                'conditions'=>'AB.id = Avaliadors.usuario_id'])
                ->where(['Avaliadors.tipo_avaliador'=>'N'])

                ->order([
                    'AB.nome' => 'Asc']);

            }
            
            
        }
        
        
      
        

        $this->set(compact('novas', 'editais'));
       
    }

    public function view($id = null)
    {
        $avaliador = $this->Avaliadors->get($id, [
            'contain' => ['Usuarios', 'GrandesAreas', 'Areas', 'SubAreas', 'Especialidades', 'AreasFiocruzs', 'Linhas', 'Unidades', 'Editais', 'AvaliadorBolsistas', 'AvaliadorProjetos'],
        ]);

        $this->set(compact('avaliador'));
    }

    

    

    

   
    public function a()
    {
        $avaliador = $this->Avaliadors->newEmptyEntity();
        if ($this->request->is('post')) {
            $avaliador = $this->Avaliadors->patchEntity($avaliador, $this->request->getData());
            if ($this->Avaliadors->save($avaliador)) {
                $this->Flash->success(__('The avaliador has been saved.'));

                return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
            }
            $this->Flash->error(__('The avaliador could not be saved. Please, try again.'));
        }
        $usuarios = $this->Avaliadors->Usuarios->find('list', ['limit' => 200])->all();
        $grandesAreas = $this->Avaliadors->GrandesAreas->find('list', ['limit' => 200])->all();
        $areas = $this->Avaliadors->Areas->find('list', ['limit' => 200])->all();
        $subAreas = $this->Avaliadors->SubAreas->find('list', ['limit' => 200])->all();
        $especialidades = $this->Avaliadors->Especialidades->find('list', ['limit' => 200])->all();
        $areasFiocruzs = $this->Avaliadors->AreasFiocruzs->find('list', ['limit' => 200])->all();
        $linhas = $this->Avaliadors->Linhas->find('list', ['limit' => 200])->all();
        $unidades = $this->Avaliadors->Unidades->find('list', ['limit' => 200])->all();
        $editais = $this->Avaliadors->Editais->find('list', ['limit' => 200])->all();
        $this->set(compact('avaliador', 'usuarios', 'grandesAreas', 'areas', 'subAreas', 'especialidades', 'areasFiocruzs', 'linhas', 'unidades', 'editais'));
    }

  
    public function edit($id = null)
    {
        $avaliador = $this->Avaliadors->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $avaliador = $this->Avaliadors->patchEntity($avaliador, $this->request->getData());
            if ($this->Avaliadors->save($avaliador)) {
                $this->Flash->success(__('The avaliador has been saved.'));

                return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
            }
            $this->Flash->error(__('The avaliador could not be saved. Please, try again.'));
        }
        $usuarios = $this->Avaliadors->Usuarios->find('list', ['limit' => 200])->all();
        $grandesAreas = $this->Avaliadors->GrandesAreas->find('list', ['limit' => 200])->all();
        $areas = $this->Avaliadors->Areas->find('list', ['limit' => 200])->all();
        $subAreas = $this->Avaliadors->SubAreas->find('list', ['limit' => 200])->all();
        $especialidades = $this->Avaliadors->Especialidades->find('list', ['limit' => 200])->all();
        $areasFiocruzs = $this->Avaliadors->AreasFiocruzs->find('list', ['limit' => 200])->all();
        $linhas = $this->Avaliadors->Linhas->find('list', ['limit' => 200])->all();
        $unidades = $this->Avaliadors->Unidades->find('list', ['limit' => 200])->all();
        $editais = $this->Avaliadors->Editais->find('list', ['limit' => 200])->all();
        $this->set(compact('avaliador', 'usuarios', 'grandesAreas', 'areas', 'subAreas', 'especialidades', 'areasFiocruzs', 'linhas', 'unidades', 'editais'));
    }

   

    //avaliar bolsa nova
    public function avaliar_old($id)
    {
       
       

        //busca o avaliadorbolsista
        $this->AvaliadorBolsistas = TableRegistry::getTableLocator()->get('AvaliadorBolsistas');
        $ab = $this->AvaliadorBolsistas
            ->find('all')
            ->contain(['Avaliadors', 
            'ProjetoBolsistas'=>['Projetos','Usuarios', 'Orientadores'=>'Unidades', 'Coorientadores']])
            ->where(['AvaliadorBolsistas.id'=>$id])->first();
            //dd($ab->avaliador_id);


        //trago o avaliador
        $this->Avaliadors = TableRegistry::getTableLocator()->get('Avaliadors');
        $aval = $this->Avaliadors
            ->find('all')
            ->contain('Usuarios')->where(['Avaliadors.id'=>$ab->avaliador_id])->first();

           // dd($aval->usuario_id);

        //verifica se o logado é avaliador vinculado
        if($aval->usuario_id != $this->request->getAttribute('identity')['id']){
            $this->Flash->error('Você não foi designado para avaliar este Projeto!');
            return $this->redirect(['controller'=>'Projetos']);
        }

        //verifica se ja foi avaliado
        if($ab->situacao=='F'){
            $this->Flash->error('A avaliação deste projeto já foi lançada por você');
            return $this->redirect(['controller'=>'Projetos']);
        }

        $raic = TableRegistry::getTableLocator()
        ->get('ProjetoBolsistas')
        ->get($ab->bolsista,
        ['contain'=>['Usuarios','Projetos','Orientadores'=>'Unidades','Coorientadores']]);

        // gravação das notas
        if($this->request->is(['post','put','patch'])){
            $erro = 0;
            $comp=0;
            foreach($this->request->getData('q') as $questao => $nota)
            {
                $this->Avaliacao = TableRegistry::getTableLocator()->get('Avaliations');
                $avaliacao = $this->Avaliacao->newEmptyEntity();

                $avaliacao->avaliador_bolsista_id = $ab->id;
                $avaliacao->question_id = $questao;
                $avaliacao->nota = $nota;
                $avaliacao->observacao_avaliador = $this->request->getData('observacao_avaliador');
                $avaliacao->parecer = $this->request->getData('parecer');

                $comp=$comp+$nota;
                if(!$this->Avaliacao->save($avaliacao)){
                    $erro++;
                    
                }
            }


            //atualizando o avaliadorBolsista
            if($erro==0){
                $ab->observacao = $this->request->getData('observacao_avaliador');
                $ab->situacao = 'F';
                $ab->nota=$comp;
                //dd($comp);

                if(!$this->AvaliadorBolsistas->save($ab)){
                    $this->Flash->error('Erro durante a finalização!');
                    
                }


                
                $this->Flash->success('Avaliação finalizada com sucesso!');
                return $this->redirect(['action'=>'minhas-avaliacoes', $this->request->getAttribute('identity')['id']]);

                

                /*

                // FIXANDO A PONTUAÇÃO INDIVIDUAL (BEGIN)
                $this->Usuarios = TableRegistry::getTableLocator()->get('Users');
                $usuario = $this->Users->get($raic->usuario_id);
                $usuario->pontos_bolsista = ($cr+5);
                $this->Users->save($usuario);
                // FIXANDO A PONTUAÇÃO INDIVIDUAL (END)
                return $this->redirect(['action'=>'avaliar',$raic->id]);

                */



            }else{
                $this->Flash->error('Erro durante a gravação da avaliação!');
            }
        }




        /*

        $raic->cr_acumulado = ($raic->cr_acumulado>10?($raic->cr_acumulado/10):$raic->cr_acumulado);
        if($raic->cr_acumulado<7){
            $cr = 0;
        }elseif($raic->cr_acumulado<8){
            $cr = 3;
        }elseif($raic->cr_acumulado<9){
            $cr = 4;
        }elseif($raic->cr_acumulado>=9){
            $cr = 5;
        }
        */
        
        $avaliado = TableRegistry::getTableLocator()
        ->get('Avaliations')->find('all')
        ->contain(['Questions'])
        ->where(['avaliador_bolsista_id'=>$ab->id]);
        
        $ja_avaliado = TableRegistry::getTableLocator()
        ->get('Avaliations')->find('all')
        ->contain(['Questions',
        'AvaliadorBolsistas'=>['Avaliadors'=>['Usuarios'=>'Unidades']]])
        //->where(['avaliador_bolsista_id IN (SELECT id FROM avaliador_bolsistas WHERE bolsista = '.$id.')'])->order(['avaliador_bolsista_id'=>'DESC'])
        ->where(['Avaliations.avaliador_bolsista_id'=>$ab->id]);
        
        $questoes = TableRegistry::getTableLocator()
        ->get('Questions')->find('all')
        ->where(['editai_id'=>$ab->editai_id,'tipo'=>'N']);
        //$this->set(compact('raic','avaliado','ja_avaliado','questoes','ab','cr'));
        $this->set(compact('questoes','ab', 'avaliado', 'ja_avaliado', 'raic'));
    }
    public function avaliar($id)
    {      

        //busca o avaliadorbolsista
            $this->AvaliadorBolsistas = TableRegistry::getTableLocator()->get('AvaliadorBolsistas');
            $ab = $this->AvaliadorBolsistas
                ->find('all')
                ->where(['AvaliadorBolsistas.id'=>$id])->first()
            ;
        //

        //trago o edital e verifico prazo
            $ed = TableRegistry::getTableLocator()->get('Editais')->find()
            ->where(['id' => $ab->editai_id])->first();

            if ($ed==null) {
                $this->Flash->error('Não existe edital vinculado');
                return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
            } else {
                // Inicio das inscrições
                if ($ed->inicio_avaliar > FrozenTime::now()) {
                    $this->Flash->error('Período de lançamentode notas ainda nao iniciado');
                    return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
                }

                // Fim das inscrições
                if ($ed->fim_avaliar < FrozenTime::now()) {
                    $this->Flash->error('Período de lançamentode notas ENCERRADO');
                    return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
                }
            }
        
        //

        //trago o avaliador
            $this->Avaliadors = TableRegistry::getTableLocator()->get('Avaliadors');
            $aval = $this->Avaliadors
                ->find('all')
                ->contain('Usuarios')->where(['Avaliadors.id'=>$ab->avaliador_id])->first()
            ;
        //

        //verifica se o logado é avaliador vinculado
            if($aval->usuario_id != $this->request->getAttribute('identity')['id'] && !in_array($this->request->getAttribute('identity')['id'], [1, 8088])){
                $this->Flash->error('Você não foi designado para avaliar este Projeto!');
                return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
            }
        //

        //verifica se o avaliador foi desvinculado
            if($aval->deleted==1){
                $this->Flash->error('Você foi desvinculado como avaliador. #D');
                return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
            }
        //

        //verifica se o avaliador foi desvinculado desta banca
            if($ab->deleted==1){
                $this->Flash->error('Você foi desvinculado desta avaliação. #E');
                return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
            }
        //

        //verifica se ja foi avaliado
            if($ab->situacao=='F'){
                $this->Flash->error('A avaliação deste projeto já foi lançada por você');
                return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
            }
        //

       
        //TRAGO A inscrição - projeto_bolsista e seus anexos

            $inscricao = TableRegistry::getTableLocator()->get("ProjetoBolsistas")
            ->get($ab->bolsista, ["contain" => ['ProjetoAnexos' => 'TipoAnexos',
                        'Editais',
                        'ProjetosDados', 
                        'Projetos' => [
                            'Linhas'=> 'AreasFiocruz', 
                            'Areas', 
                            'ProjetoAnexos' => ['TipoAnexos'/*, 'conditions' => 'ProjetoAnexos.projeto_bolsista_id IS NULL'*/]
                        ],
                        'SituacaoHistoricos'=>['Usuarios', 'sort' => ['SituacaoHistoricos.id' => 'desc']],
                        'FonteHistoricos'=>['Usuarios', 'sort' => ['FonteHistoricos.id' => 'desc']],
                        'Orientadores' => ['Unidades'], 
                        'Coorientadores'=>['Unidades'],
                        'Usuarios'=>['Instituicaos']]
            ]);

            //VERIFICO SE A inscrição FOI DELETADA
                if($inscricao->deleted==1){
                    $this->Flash->error('A solicitação de bolsa foi deletada: '. $inscricao->justificativa_cancelamento);
                    return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
                }
            //

        

            $doc_bolsista = TableRegistry::getTableLocator()->get('ProjetoAnexos')->find()->contain(['TipoAnexos', 'Usuarios'])
            ->where([
                'projeto_bolsista_id' => $inscricao->id,
                'deleted IS NULL',
                '(tipo_anexo_id IN (15, 16, 17, 18, 19, 21))'
            ]);

            // somente proj completo 
            $anexo_proj = TableRegistry::getTableLocator()->get('ProjetoAnexos')->find()->contain(['TipoAnexos', 'Usuarios'])
            ->where([
                'projeto_id' => $inscricao->projeto_id,
                '(deleted IS NULL)',
                '(projeto_bolsista_id IS NULL)',
                '(tipo_anexo_id IN (5))'
            ]);

            // documentos de coite e nascimento dos filhos da orientadors
            $anexo_orientador = TableRegistry::getTableLocator()->get('ProjetoAnexos')->find()->contain(['TipoAnexos', 'Usuarios'])
            ->where([
                'projeto_id' => $inscricao->projeto_id,
                '(deleted IS NULL)',
                '(tipo_anexo_id IN (1, 2, 3, 4, 6, 7, 8, 9, 11))'
            ]);

            $anexo_sub = TableRegistry::getTableLocator()->get('ProjetoAnexos')->find()->contain(['TipoAnexos', 'Usuarios'])
            ->where([
                'projeto_bolsista_id' => $inscricao->id,
                '(deleted IS NULL)',
                '(tipo_anexo_id IN (13, 20))'
            ]);

        //
        

        
        // gravação das notas
        if($this->request->is(['post','put','patch'])){

            try {
                $avaliadoresTable = TableRegistry::getTableLocator()->get('Avaliadors');

                // Usando o método transactional
                $avaliadoresTable->getConnection()->transactional(function ($connection) use ($ab, &$erros) {
                    
                $erro = 0;
                $comp=0;
                foreach($this->request->getData('q') as $questao => $nota)
                {
                    $this->Avaliacao = TableRegistry::getTableLocator()->get('Avaliations');
                    $avaliacao = $this->Avaliacao->newEmptyEntity();
    
                    $avaliacao->avaliador_bolsista_id = $ab->id;
                    $avaliacao->question_id = $questao;
                    $avaliacao->nota = $nota;
                    $avaliacao->observacao_avaliador = $this->request->getData('observacao_avaliador');
                    $avaliacao->parecer = $this->request->getData('parecer');
    
                    
                    
                    $comp=$comp+$nota;
                    if(!$this->Avaliacao->save($avaliacao)){
                        $erro++;
                        
                    }
                }
    
    
                if($erro==0){
                    
                    //atualizando o avaliadorBolsista
                        $ab->observacao = $this->request->getData('observacao_avaliador');
                        $ab->situacao = 'F';
                        $ab->nota=$comp;
    
                        $ab->alteracao = $this->request->getData('alteracao');
                        $ab->observacao_alteracao = $this->request->getData('observacao_alteracao');
                        $ab->parecer = $this->request->getData('parecer');
    
        
            
                        if(!$this->AvaliadorBolsistas->save($ab)){
                            $this->Flash->error('Erro durante a finalização!');
                            
                        }
                    //
                    
                }else{
                    $this->Flash->error('Erro durante a gravação da avaliação!');
                }
                $this->Flash->success('Avaliação finalizada com sucesso!');
                return $this->redirect(['action'=>'minhas-avaliacoes', $this->request->getAttribute('identity')['id']]);
        

                });
            } catch (\Exception $e) {
                // Captura os dados para envio do e-mail
                $userId = $this->request->getAttribute('identity')['id'];
                $userNome = $this->request->getAttribute('identity')['nome'];
                $userEmail = $this->request->getAttribute('identity')['email'];
                $userAlternativo = $this->request->getAttribute('identity')['email_alternativo'];
                $dataHora = FrozenTime::now()->format('Y-m-d H:i:s');
                $urlAcessada = $this->request->getUri()->getPath();
                $mensagemErro = $e->getMessage();
                $host = $this->request->host();
                $linhaErro = $e->getLine();  // Linha do erro
                $arquivoErro = $e->getFile(); // Arquivo onde o erro ocorreu




                $corpoEmail = "
                <h3>Lançamento de Notas - Nova</h3>
                <p><strong>Usuário ID:</strong> {$userId}</p>
                <p><strong>Usuário Nome:</strong> {$userNome}</p>
                <p><strong>Usuário Email:</strong> {$userEmail}</p>
                <p><strong>Usuário email alternativo:</strong> {$userAlternativo}</p>

                <p><strong>Data e Hora:</strong> {$dataHora}</p>
                <p><strong>Servidor:</strong> {$host}</p>
                <p><strong>URL Acessada:</strong> {$urlAcessada}</p>
                <p><strong>Mensagem de Erro:</strong> {$mensagemErro}</p>
                <p><strong>Arquivo:</strong> {$arquivoErro}</p>
                <p><strong>Linha:</strong> {$linhaErro}</p>
                ";
            
                // Configuração do e-mail
                $to = ['mariaversiani@yahoo.com.br', 'maria.britto@fiocruz.br']; // Alterar para o e-mail correto
                $assunto = 'Erro no Sistema - Lançamento de Notas - Nova';
            
                // Enviar e-mail
                $this->getMailer('Projetos')->send('erroTi', [$to, $assunto, $corpoEmail]);
        

                $this->Flash->error('Houve um erro na gravação. Tente novamente. Detalhes: ' . $e->getMessage());
                return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
            }    

        }


        
        $questoes = TableRegistry::getTableLocator()
        ->get('Questions')->find('all')
        ->where(['editai_id'=>$ab->editai_id,'tipo'=>'N']);
        //$this->set(compact('raic','avaliado','ja_avaliado','questoes','ab','cr'));
        
        $situacao = $this->situacao;
        $origem = $this->origem;

        $this->set(compact('situacao', 'origem', 'questoes','ab', 'anexo_sub', 'anexo_orientador', 'anexo_proj', 'doc_bolsista', 'inscricao'));
    }

    public function avaliarpdj($id)
    {      

        //busca o avaliadorbolsista
            $this->AvaliadorBolsistas = TableRegistry::getTableLocator()->get('AvaliadorBolsistas');
            $ab = $this->AvaliadorBolsistas
                ->find('all')
                ->where(['AvaliadorBolsistas.id'=>$id])->first()
            ;
        //

        //trago o edital e verifico prazo
            $ed = TableRegistry::getTableLocator()->get('Editais')->find()
            ->where(['id' => $ab->editai_id])->first();

            if ($ed==null) {
                $this->Flash->error('Não existe edital vinculado');
                return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
            } else {
                // Inicio das inscrições
                if ($ed->inicio_avaliar > FrozenTime::now()) {
                    $this->Flash->error('Período de lançamentode notas ainda nao iniciado');
                    return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
                }

                // Fim das inscrições
                if ($ed->fim_avaliar < FrozenTime::now()) {
                    $this->Flash->error('Período de lançamentode notas ENCERRADO');
                    return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
                }
            }
        
        //


        //trago o avaliador
            $this->Avaliadors = TableRegistry::getTableLocator()->get('Avaliadors');
            $aval = $this->Avaliadors
                ->find('all')
                ->contain('Usuarios')->where(['Avaliadors.id'=>$ab->avaliador_id])->first()
            ;
        //

        //verifica se o logado é avaliador vinculado
            if($aval->usuario_id != $this->request->getAttribute('identity')['id'] && !in_array($this->request->getAttribute('identity')['id'], [1, 8088])){
                $this->Flash->error('Você não foi designado para avaliar este Projeto!');
                return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
            }
        //

        //verifica se o avaliador foi desvinculado
            if($aval->deleted==1){
                $this->Flash->error('Você foi desvinculado como avaliador. #D');
                return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
            }
        //

        //verifica se o avaliador foi desvinculado desta banca
            if($ab->deleted==1){
                $this->Flash->error('Você foi desvinculado desta avaliação. #E');
                return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
            }
        //

        //verifica se ja foi avaliado
            if($ab->situacao=='F'){
                $this->Flash->error('A avaliação deste projeto já foi lançada por você');
                return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
            }
        //

       
        //TRAGO A inscrição - projeto_bolsista e seus anexos

            if($ab->tipo=='J'){
                $inscricao = TableRegistry::getTableLocator()->get("PdjInscricoes")
                ->get($ab->bolsista, ["contain" => ['Projetos',
                'Editais',
                'Usuarios'=>'Unidades',
                'PdjHistoricos'=>['Usuarios', 'sort' => ['PdjHistoricos.id' => 'desc']],
                'Candidatos' =>[
                    'Instituicaos',
                    'Escolaridades'
                    ]]
                ]);

            }

            $work=null;
            if($ab->tipo=='W'){
                $work = TableRegistry::getTableLocator()->get("Workshops")
                ->get($ab->bolsista, ["contain" => ['Usuarios', 
                    'PdjInscricoes'=>['GrandesAreas'],
                    'Projetos',
                    'Orientadores'=>'Unidades',
                    'Cadastro',

                    'WorkshopHistoricos'=>['Usuarios']]
                ]);

                $inscricao = TableRegistry::getTableLocator()->get("PdjInscricoes")
                ->get($work->pdj_inscricoe_id, ["contain" => ['Projetos',
                'Editais',
                'Usuarios'=>'Unidades',
                'PdjHistoricos'=>['Usuarios', 'sort' => ['PdjHistoricos.id' => 'desc']],
                'Candidatos' =>[
                    'Instituicaos',
                    'Escolaridades'
                    ]]
                ]);

                //VERIFICO SE o work FOI DELETADA
                    if($work->deleted==1){
                        $this->Flash->error('O Workshop foi deletaddo');
                        return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
                    }
                //

                
            }

            //VERIFICO SE A inscrição FOI DELETADA
                if($inscricao->deleted!=null){
                    $this->Flash->error('A solicitação de bolsa foi deletada: '. $inscricao->justificativa_cancelamento);
                    return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
                }
            //


            //anexos
                $doc_bolsista = TableRegistry::getTableLocator()->get('ProjetoAnexos')->find()->contain(['TipoAnexos', 'Usuarios'])
                ->where([
                    'projeto_bolsista_id' => $inscricao->id,    
                    'deleted IS NULL',
                    '(tipo_anexo_id IN (10, 11, 21))'
                ]);

                
        
                $anexo_proj = TableRegistry::getTableLocator()->get('ProjetoAnexos')->find()->contain(['TipoAnexos', 'Usuarios'])
                ->where([
                    'projeto_id' => $inscricao->projeto_id,
                    '(deleted IS NULL)',
                    '(projeto_bolsista_id IS NULL)',
                    '(tipo_anexo_id IN (5, 9))'
                ]);
                $anexo_orientador = TableRegistry::getTableLocator()->get('ProjetoAnexos')->find()->contain(['TipoAnexos', 'Usuarios'])
                ->where([
                    'projeto_id' => $inscricao->projeto_id,
                    //'projeto_bolsista_id' => $inscricao->id,
                    'projeto_bolsista_id' => $inscricao->id, 
                    '(deleted IS NULL)',
                    '(tipo_anexo_id IN (1, 2, 3, 4, 6, 7, 8, 9, 12))'
                ]);
            //
        //

        //VERIFICO o agendamento da raic
            $libera_form=false;
            if($ab->tipo=='W' && $work->data_apresentacao->i18nFormat("YMMdd") <= date("Ymd")){
                $libera_form=true;
            }
            if($ab->tipo=='J'){
                $libera_form=true;

            }
        //
        

        
        // gravação das notas
        if($this->request->is(['post','put','patch'])){

            try {
                $avaliadoresTable = TableRegistry::getTableLocator()->get('Avaliadors');

                // Usando o método transactional
                $avaliadoresTable->getConnection()->transactional(function ($connection) use ($ab, &$erros) {
                    
                $erro = 0;
                $comp=0;
                foreach($this->request->getData('q') as $questao => $nota)
                {
                    $this->Avaliacao = TableRegistry::getTableLocator()->get('Avaliations');
                    $avaliacao = $this->Avaliacao->newEmptyEntity();
    
                    $avaliacao->avaliador_bolsista_id = $ab->id;
                    $avaliacao->question_id = $questao;
                    $avaliacao->nota = $nota;
                    $avaliacao->observacao_avaliador = $this->request->getData('observacao_avaliador');
                    $avaliacao->parecer = $this->request->getData('parecer');
    
                    
                    
                    $comp=$comp+$nota;
                    if(!$this->Avaliacao->save($avaliacao)){
                        $erro++;
                        
                    }
                }
    
    
                if($erro==0){
                    
                    //atualizando o avaliadorBolsista
                        $ab->observacao = $this->request->getData('observacao_avaliador');
                        $ab->situacao = 'F';
                        $ab->nota=$comp;
    
                        $ab->alteracao = $this->request->getData('alteracao');
                        $ab->observacao_alteracao = $this->request->getData('observacao_alteracao');
                        $ab->parecer = $this->request->getData('parecer');
                        $ab->destaque = $this->request->getData('destaque');
                        $ab->indicado_premio_capes = $this->request->getData('indicado_premio_capes');

        
            
                        if(!$this->AvaliadorBolsistas->save($ab)){
                            $this->Flash->error('Erro durante a finalização!');
                            
                        }
                    //
                    
                }else{
                    $this->Flash->error('Erro durante a gravação da avaliação!');
                }
                $this->Flash->success('Avaliação finalizada com sucesso!');
                return $this->redirect(['action'=>'minhas-avaliacoes', $this->request->getAttribute('identity')['id']]);
        

                });
            } catch (\Exception $e) {
                // Captura os dados para envio do e-mail
                $userId = $this->request->getAttribute('identity')['id'];
                $userNome = $this->request->getAttribute('identity')['nome'];
                $userEmail = $this->request->getAttribute('identity')['email'];
                $userAlternativo = $this->request->getAttribute('identity')['email_alternativo'];
                $dataHora = FrozenTime::now()->format('Y-m-d H:i:s');
                $urlAcessada = $this->request->getUri()->getPath();
                $mensagemErro = $e->getMessage();
                $host = $this->request->host();
                $linhaErro = $e->getLine();  // Linha do erro
                $arquivoErro = $e->getFile(); // Arquivo onde o erro ocorreu




                $corpoEmail = "
                <h3>Lançamento de Notas - PDJ</h3>
                <p><strong>Usuário ID:</strong> {$userId}</p>
                <p><strong>Usuário Nome:</strong> {$userNome}</p>
                <p><strong>Usuário Email:</strong> {$userEmail}</p>
                <p><strong>Usuário email alternativo:</strong> {$userAlternativo}</p>

                <p><strong>Data e Hora:</strong> {$dataHora}</p>
                <p><strong>Servidor:</strong> {$host}</p>
                <p><strong>URL Acessada:</strong> {$urlAcessada}</p>
                <p><strong>Mensagem de Erro:</strong> {$mensagemErro}</p>
                <p><strong>Arquivo:</strong> {$arquivoErro}</p>
                <p><strong>Linha:</strong> {$linhaErro}</p>
                ";
            
                // Configuração do e-mail
                $to = ['mariaversiani@yahoo.com.br', 'maria.britto@fiocruz.br']; // Alterar para o e-mail correto
                $assunto = 'Erro no Sistema - Lançamento de Notas - PDJ';
            
                // Enviar e-mail
                $this->getMailer('Projetos')->send('erroTi', [$to, $assunto, $corpoEmail]);
        

                $this->Flash->error('Houve um erro na gravação. Tente novamente. Detalhes: ' . $e->getMessage());
                return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
            }    

        }


        
        $questoes = TableRegistry::getTableLocator()
        ->get('Questions')->find('all')
        ->where(['editai_id'=>$ab->editai_id]);
        //$this->set(compact('raic','avaliado','ja_avaliado','questoes','ab','cr'));
        
        $situacao = $this->situacao;
        $origem = $this->origem;

        $this->set(compact('situacao', 'origem', 'questoes','ab', 'doc_bolsista', 'anexo_proj', 'anexo_orientador', 'work', 'inscricao', 'libera_form'));
    }


    public function avaliarraic($id)
    {
        //busca o avaliadorbolsista
            $this->AvaliadorBolsistas = TableRegistry::getTableLocator()->get('AvaliadorBolsistas');
            $ab = $this->AvaliadorBolsistas
                ->find('all')
                ->contain(['Avaliadors'=>'Usuarios'])
                ->where(['AvaliadorBolsistas.id'=>$id])->first()
            ;
        //


        
        //trago o edital e verifico prazo
            $ed = TableRegistry::getTableLocator()->get('Editais')->find()
            ->where(['id' => $ab->editai_id])->first();

            if ($ed==null) {
                $this->Flash->error('Não existe edital vinculado');
                return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
            } else {
                // Inicio das inscrições
                if ($ed->inicio_avaliar > FrozenTime::now()) {
                    $this->Flash->error('Período de lançamentode notas ainda nao iniciado');
                    return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
                }
    
                // Fim das inscrições
                if ($ed->fim_avaliar < FrozenTime::now()) {
                    $this->Flash->error('Período de lançamentode notas ENCERRADO');
                    return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
                }
            }
        
        //



        //verifica se o logado é avaliador vinculado
            if($ab->avaliador->usuario_id != $this->request->getAttribute('identity')['id'] && !in_array($this->request->getAttribute('identity')['id'], [1, 8088]) )
            {
                $this->Flash->error('Você não foi designado para avaliar este Projeto!');
                return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
            }
        //

        //verifica se o avaliador foi desvinculado
            if($ab->avaliador->deleted==1){
                $this->Flash->error('Você foi desvinculado como avaliador. #D');
                return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
            }
        //

        //verifica se o avaliador foi desvinculado desta banca
            if($ab->deleted==1){
                $this->Flash->error('Você foi desvinculado desta banca. #E');
                return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
            }
        //

        //verifica se ja foi avaliado
            if($ab->situacao=='F'){
                $this->Flash->error('A avaliação deste projeto já foi lançada por você');
                return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
            }
        //

        
        //TRAGO A RAIC e seus anexos
            $this->Raics = TableRegistry::getTableLocator()->get('Raics');
            $raic = $this->Raics
                ->find()
                ->contain([
                    'Usuarios', 
                    'ProjetoBolsistas',
                    'Projetos'=>['Areas', 'Linhas'],
                    'Orientadores',
                    'Coorientadores',
                    'Cadastro',
                    'Libera',
                    'Editais',
                    'Unidades',
                    'RaicHistoricos'=>['Usuarios']
                ])->where(['Raics.id'=>$ab->bolsista])->first();

          
            $anexo_sub_novo=null;
            $anexo_sub_velho=null;
            $anexos_projetos=null;

            if($raic->tipo_bolsa=='R'){
                //anexos da Raic
                $relatorio = TableRegistry::getTableLocator()->get('ProjetoAnexos')->find()->contain(['TipoAnexos', 'Usuarios'])
                ->where([
                    'projeto_bolsista_id' => $raic->projeto_bolsista_id,
                    '(deleted IS NULL)',
                    '(tipo_anexo_id IN (13))'

                ]);

                $anexos_projetos = TableRegistry::getTableLocator()->get('ProjetoAnexos')->find()->contain(['TipoAnexos', 'Usuarios'])
                ->where([
                    'projeto_id' => $raic->projeto_bolsista->projeto_id,
                    '(deleted IS NULL)',
                    '(tipo_anexo_id IN (1, 2, 3, 4, 5, 6, 7, 8, 9))'

                ])->order(['ProjetoAnexos.tipo_anexo_id'=>'ASC', 'ProjetoAnexos.created'=>'DESC']);

                
                if($raic->projeto_bolsista->subprojeto_renovacao=='D'){
                    $anexo_sub_novo = TableRegistry::getTableLocator()->get('ProjetoAnexos')->find()->contain(['TipoAnexos', 'Usuarios'])
                    ->where([
                        'projeto_bolsista_id' => $raic->projeto_bolsista_id,
                        '(deleted IS NULL)',
                        '(tipo_anexo_id IN (20))'

                    ]);
                    $anexo_sub_velho = TableRegistry::getTableLocator()->get('ProjetoAnexos')->find()->contain(['TipoAnexos', 'Usuarios'])
                    ->where([
                        'projeto_bolsista_id' => $raic->projeto_bolsista->referencia_inscricao_anterior,
                        '(deleted IS NULL)',
                        '(tipo_anexo_id IN (20))'

                    ]);
                    
                }
                if($raic->projeto_bolsista->subprojeto_renovacao=='I'){
                    //chamo de velho mas pego a referencia da inscrição atual pq foi so copiado
                    $anexo_sub_velho = TableRegistry::getTableLocator()->get('ProjetoAnexos')->find()->contain(['TipoAnexos', 'Usuarios'])
                    ->where([
                        'projeto_bolsista_id' => $raic->projeto_bolsista_id,
                        '(deleted IS NULL)',
                        '(tipo_anexo_id IN (20))'

                    ]);
                    
                    
                }

                
            }else{
                $relatorio = TableRegistry::getTableLocator()->get('ProjetoAnexos')->find()->contain(['TipoAnexos', 'Usuarios'])
                ->where([
                    'raic_id' => $raic->id,
                    '(deleted IS NULL)',
                    '(tipo_anexo_id IN (13, 20))'
                ]);
            }
        //

        //VERIFICO SE A RAIC FOI DELETADA
            if($raic->deleted==1){
                $this->Flash->error('A Raic foi deletada.');
                return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
            }
        //

        //VERIFICO SE A renovação FOI DELETADA
            if($raic->projeto_bolsista_id!=null){
                if($raic->projeto_bolsista->deleted==1){
                    $this->Flash->error('A solicitação de renovação foi deletada: '. $raic->projeto_bolsista->justificativa_cancelamento);
                    return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);       
                }      
            }
        //

        //VERIFICO o agendamento da raic
            $libera_form=false;
            if($raic->data_apresentacao->i18nFormat("YMMdd") <= date("Ymd")){
                $libera_form=true;
            }
        //

        // gravação das notas
            if($this->request->is(['post','put','patch'])){
                $erro = 0;
                $comp=0;
                foreach($this->request->getData('q') as $questao => $nota)
                {
                    $this->Avaliacao = TableRegistry::getTableLocator()->get('Avaliations');
                    $avaliacao = $this->Avaliacao->newEmptyEntity();

                    $avaliacao->avaliador_bolsista_id = $ab->id;
                    $avaliacao->question_id = $questao;
                    $avaliacao->nota = $nota;
                    $avaliacao->observacao_avaliador = $this->request->getData('observacao_avaliador');
                    $avaliacao->parecer = $this->request->getData('parecer');

                    $comp=$comp+$nota;
                    if(!$this->Avaliacao->save($avaliacao)){
                        $erro++;
                        
                    }
                }


                if($erro==0){
                    
                    //atualizando o avaliadorBolsista
                        $ab->observacao = $this->request->getData('observacao_avaliador');
                        $ab->situacao = 'F';
                        $ab->nota=$comp;

                        $ab->destaque = $this->request->getData('destaque');
                        $ab->indicado_premio_capes = $this->request->getData('indicado_premio_capes');
                        $ab->alteracao = $this->request->getData('alteracao');
                        $ab->observacao_alteracao = $this->request->getData('observacao_alteracao');
                        $ab->parecer = $this->request->getData('parecer');

            
                        if(!$this->AvaliadorBolsistas->save($ab)){
                            $this->Flash->error('Erro durante a finalização!');
                            
                        }
                    //
                    
                    //atualizando os dados da Raic
                        $raic->observacao_avaliador = $this->request->getData('observacao_avaliador');
                        $raic->nota_final=$comp;

                        $raic->indicado_premio_capes = $this->request->getData('indicado_premio_capes');
                        $raic->destaque = $this->request->getData('destaque');

                        if(!$this->Raics->save($raic)){
                            $this->Flash->error('Erro durante a atualização da raic!');
                        }
                    //
    
                    $this->Flash->success('Avaliação finalizada com sucesso!');
                    return $this->redirect(['action'=>'minhas-avaliacoes', $this->request->getAttribute('identity')['id']]);

                
                }else{
                    $this->Flash->error('Erro durante a gravação da avaliação!');
                }
            }
        //

        $programa = $this->programa;

        $questoes = TableRegistry::getTableLocator()
        ->get('Questions')->find('all')
        ->where(['editai_id'=>$ab->editai_id, 'deleted'=>0]);
        //$this->set(compact('raic','avaliado','ja_avaliado','questoes','ab','cr'));
        
        $this->set(compact('anexos_projetos', 'relatorio','programa','anexo_sub_novo','anexo_sub_velho','libera_form','questoes','ab', /*'avaliado', 'ja_avaliado',*/ 'raic'));
    }

    //função de ver a raic para o avaliador nao coordenador
    public function verraic($id)
    {


        //libera acesso na mao
            $libera=true;
            if($libera==false){
                $this->Flash->error('Fora do prazo para avaliação');
                return $this->redirect($this->request->referer());
            }
        //

        //busca o avaliadorbolsista
            $this->AvaliadorBolsistas = TableRegistry::getTableLocator()->get('AvaliadorBolsistas');
            $ab = $this->AvaliadorBolsistas
                ->find('all')
                ->where(['AvaliadorBolsistas.id'=>$id])->first()
            ;
        //

        //trago o avaliador
            $this->Avaliadors = TableRegistry::getTableLocator()->get('Avaliadors');
            $aval = $this->Avaliadors
                ->find('all')
                ->contain('Usuarios')->where(['Avaliadors.id'=>$ab->avaliador_id])->first()
            ;
        //


        //verifica se o logado é avaliador vinculado
            if($aval->usuario_id != $this->request->getAttribute('identity')['id']){
                $this->Flash->error('Você não foi designado para avaliar este Projeto!');
                return $this->redirect(['controller'=>'Projetos']);
            }
        //

        //verifica se ja foi avaliado
            if($ab->situacao=='F'){
                $this->Flash->error('A avaliação deste projeto já foi lançada por você');
                return $this->redirect(['controller'=>'Projetos']);
            }
        //

        //verifica se é o coordenador da banca
            /* RETIRADO
            if($ab->coordenador==1){
                $this->Flash->error('Esta tela é para visualização dos avaliadores. Sr coordenador, favor acessar a tela de avaliação');
                return $this->redirect(['controller'=>'Projetos']);
            }
            */
        //

        //TRAZ A RAIC
            $raic = TableRegistry::getTableLocator()
            ->get('Raics')
            ->get($ab->bolsista,
            ['contain'=>['Usuarios','ProjetoBolsistas'=>['Projetos','Orientadores'=>['Unidades'], 'Coorientadores']]])
            ;
        //

        // gravação das notas
        if($this->request->is(['post','put','patch'])){
            $erro = 0;
            $comp=0;
            foreach($this->request->getData('q') as $questao => $nota)
            {
                $this->Avaliacao = TableRegistry::getTableLocator()->get('Avaliations');
                $avaliacao = $this->Avaliacao->newEmptyEntity();

                $avaliacao->avaliador_bolsista_id = $ab->id;
                $avaliacao->question_id = $questao;
                $avaliacao->nota = $nota;
                $avaliacao->observacao_avaliador = $this->request->getData('observacao_avaliador');
                $avaliacao->parecer = $this->request->getData('parecer');


                $comp=$comp+$nota;
                
            }


            


            
        }


        $avaliado = TableRegistry::getTableLocator()
        ->get('Avaliations')->find('all')
        ->contain(['Questions'])
        ->where(['avaliador_bolsista_id'=>$ab->id]);
        
        $ja_avaliado = TableRegistry::getTableLocator()
        ->get('Avaliations')->find('all')
        ->contain(['Questions',
        'AvaliadorBolsistas'=>['Avaliadors'=>['Usuarios'=>'Unidades']]])
        //->where(['avaliador_bolsista_id IN (SELECT id FROM avaliador_bolsistas WHERE bolsista = '.$id.')'])->order(['avaliador_bolsista_id'=>'DESC'])
        ->where(['Avaliations.avaliador_bolsista_id'=>$ab->id]);
        
        $questoes = TableRegistry::getTableLocator()
        ->get('Questions')->find('all')
        ->where(['editai_id'=>$ab->editai_id,'tipo'=>'R']);
        //$this->set(compact('raic','avaliado','ja_avaliado','questoes','ab','cr'));
        $this->set(compact('questoes','ab', 'avaliado', 'ja_avaliado', 'raic'));
    }

    

    

    // deleta valaiador
    public function deletaavaliador($id=null)
    {
        $this->request->allowMethod(['post', 'put']);
            $ab = TableRegistry::getTableLocator()->get('AvaliadorBolsistas')->find('all')
                ->where(['id'=>$id])->first();
            
            // verifica se o usuario logado e o ypda
                if(!$this->request->getAttribute('identity')['yoda']) {
                    $this->Flash->error('Somente um administrador tem acesso a esta ação');
                    return $this->redirect(['controller'=>'Bolsistas','action' => 'avaliarnovas']);
                }
            //

            // verifica o lançamento de nota bolsa nova
                if($ab->tipo=='N') {
                    if($ab->situacao=='F') {
                        $this->Flash->error('Este avaliador já lançou a nota e nao pode ser desvinculado');
                        return $this->redirect(['controller'=>'Bolsistas','action' => 'avaliarnovas']);
                    }
                }
            //

            // verifica o lançamento de nota raic
                if($ab->tipo=='V') {
                    if($ab->coordenador==1) {
                        $this->Flash->error('Este coordenador já lançou a nota e nao pode ser desvinculado');
                        return $this->redirect(['controller'=>'Bolsistas','action' => 'avaliarnovas']);
                    }

                    if($ab->coordenador==0) {
                        $banca = TableRegistry::getTableLocator()->get('AvaliadorBolsistas')->find('all')
                        ->where(['bolsista'=>$ab->bolsista])
                        ->where (['coordenador'=>1])
                        ->where (['deleted'=>0])
                        ->where (['tipo'=>'V'])
                        ->first();
                        if($banca!=null) {
                            $this->Flash->error('Esta renovação já teve as notas lançadas p esta banca e nao pode ser desvinculada');
                            return $this->redirect(['controller'=>'Bolsistas','action' => 'avaliarnovas']);
                        }


                    }
                }
            //

           
            //verifica se esta deletado
                if($ab->deleted == 1) {
                    $this->Flash->error('O registro ja estava excluido');
                    return $this->redirect(['controller'=>'Bolsistas','action' => 'avaliarnovas']);
            
                }
            //
            
            
         
            $ab->deleted = 1;

            if($this->AvaliadorBolsistas->save($ab)) {
                if($ab->tipo=='N') {
                    $bol = TableRegistry::getTableLocator()->get('ProjetoBolsistas')
                        ->find('all')
                        ->where(['id'=>$ab->bolsista])
                        ->first();
                    $alt1 = $this->Bolsista->updateAll([
                        'ordem' => $bol->ordem-1,
                    ], ['id' => $bol->id]);
                }
                $this->Flash->success('O registro foi desvinculado com sucesso');
                return $this->redirect(['controller'=>'Bolsistas','action' => 'avaliarnovas']);
            }else{
                $this->Flash->error('Erro ao desvincular o avaliador');
                return $this->redirect(['controller'=>'Bolsistas','action' => 'avaliarnovas']);
            }
            return $this->redirect(['controller'=>'Bolsistas','action' => 'avaliarnovas']);

        

    }
    
    public function substituiavaliador($id)
    {
        
       
        $erro = '';
        //testa jedi <> 1/8088
           // if(!in_array($this->request->getAttribute('identity')['id'], ['1', '8088', '860'])){
            if(!($this->request->getAttribute('identity')['yoda'])){

                $erro .= '<li> Somente a gestão pode vincular avaliadores para bolsa nova </li>';
            
            }
        //

        $ab = TableRegistry::getTableLocator()->get('AvaliadorBolsistas')->find('all')
                ->where(['id'=>$id])->first();
      
        if($ab->tipo=='J'){
            $inscricao = TableRegistry::getTableLocator()->get("PdjInscricoes")
            ->get($ab->bolsista, ["contain" => [
                        'Editais',
                        'Projetos' => [
                            'Linhas'=> 'AreasFiocruz', 
                            'Areas', 
                            'ProjetoAnexos' => ['TipoAnexos'/*, 'conditions' => 'ProjetoAnexos.projeto_bolsista_id IS NULL'*/]
                        ],
                        'Usuarios' => ['Unidades'], 
                        'Candidatos'=>['Instituicaos']]]);

        }else{
            $inscricao = TableRegistry::getTableLocator()->get("ProjetoBolsistas")
            ->get($ab->bolsista, ["contain" => ['ProjetoAnexos' => 'TipoAnexos',
                        'Editais',
                        'ProjetosDados', 
                        'Projetos' => [
                            'Linhas'=> 'AreasFiocruz', 
                            'Areas', 
                            'ProjetoAnexos' => ['TipoAnexos'/*, 'conditions' => 'ProjetoAnexos.projeto_bolsista_id IS NULL'*/]
                        ],
                        'SituacaoHistoricos'=>['Usuarios', 'sort' => ['SituacaoHistoricos.id' => 'desc']],
                        'FonteHistoricos'=>['Usuarios', 'sort' => ['FonteHistoricos.id' => 'desc']],
                        'Orientadores' => ['Unidades'], 
                        'Coorientadores'=>['Unidades'],
                        'Usuarios'=>['Instituicaos']]]);
        }

        $doc_bolsista = TableRegistry::getTableLocator()->get('ProjetoAnexos')->find()->contain(['TipoAnexos', 'Usuarios'])
        ->where([
            'projeto_bolsista_id' => $inscricao->id,
            'deleted IS NULL',
            '(tipo_anexo_id IN (15, 16, 17, 18, 19, 21))'
        ]);

        $anexo_proj = TableRegistry::getTableLocator()->get('ProjetoAnexos')->find()->contain(['TipoAnexos', 'Usuarios'])
        ->where([
            'projeto_id' => $inscricao->projeto_id,
            '(deleted IS NULL)',
            '(projeto_bolsista_id IS NULL)',
            '(tipo_anexo_id IN (5))'
        ]);

        $anexo_orientador = TableRegistry::getTableLocator()->get('ProjetoAnexos')->find()->contain(['TipoAnexos', 'Usuarios'])
        ->where([
            'projeto_id' => $inscricao->projeto_id,
            '(deleted IS NULL)',
            '(tipo_anexo_id IN (1, 2, 3, 4, 6, 7, 8, 9, 11))'
        ]);

        $anexo_sub = TableRegistry::getTableLocator()->get('ProjetoAnexos')->find()->contain(['TipoAnexos', 'Usuarios'])
        ->where([
            'projeto_bolsista_id' => $inscricao->id,
            '(deleted IS NULL)',
            '(tipo_anexo_id IN (13, 20))'
        ]);





       

        //lista de avaliadores
            $projeto = TableRegistry::getTableLocator()->get('Projetos')->find('all')
            ->contain(['Areas'])
            ->where(['Projetos.id'=>$inscricao->projeto_id])
            ->first();

            $fora=[];

            if($ab->tipo=='J'){
                 $ed=$inscricao->edital_id;
                $orienta=$inscricao->usuario_id;
                $fora=[
                $orienta,
            ];

            }else{

                $fora=[
                    $inscricao->orientador,
                ];

                if($inscricao->coorientador){
                    $fora[] = $inscricao->coorientador;
                }
                //dd($fora);
            }

            $this->Livres = TableRegistry::getTableLocator()->get('Avaliadors');
            $this->Livres->setDisplayField('nome');

            if($ab->tipo=='J'){

                $disponiveis = $this->Livres->find('list',['fields'=>['Avaliadors.ano_aceite','Avaliadors.id','Avaliadors__nome'=>'(CONCAT(Usuarios.nome, \'  (\', COALESCE(Unidades.sigla, "Unidade não informada"), \') \', COALESCE(soma.quantos,0), \'  (\', COALESCE(GrandesAreas.nome, "Não informada"), " - ", COALESCE(Areas.nome, "Não informada"), \')\'))']])
                                        ->contain(['GrandesAreas','Areas','Usuarios'=>'Unidades'])
                                        ->join([
                                            'table'=>'(SELECT count(id) as quantos, avaliador_id FROM avaliador_bolsistas WHERE tipo = \'J\' AND deleted = 0 AND ano = '.date('Y').' GROUP BY avaliador_id)',
                                            'alias'=>'soma',
                                            'type'=>'LEFT',
                                            'conditions'=>'soma.avaliador_id = Avaliadors.id'
                                        ])
                                        ->where([
                                           // 'Avaliadors.ano_aceite'=>date('Y'),
                                            'Avaliadors.editai_id'=>$inscricao->edital_id,
                                            'Avaliadors.tipo_avaliador IN (\'N\',\'A\')',
                                            'Avaliadors.id >'=>2236,
                                            'Avaliadors.deleted ='=>0,
                                            'Avaliadors.usuario_id NOT IN ' => $fora

                                        ])->order(['(area_id = '.$inscricao->area_id.') DESC','Usuarios.nome'=>'ASC']);
            }else{

                $disponiveis = $this->Livres->find('list',['fields'=>['Avaliadors.ano_aceite','Avaliadors.id','Avaliadors__nome'=>'(CONCAT(Usuarios.nome, \'  (\', COALESCE(Unidades.sigla, "Unidade não informada"), \') \', COALESCE(soma.quantos,0), \'  (\', COALESCE(GrandesAreas.nome, "Não informada"), " - ", COALESCE(Areas.nome, "Não informada"), \')\'))']])
                                            ->contain(['GrandesAreas','Areas','Usuarios'=>'Unidades'])
                                            ->join([
                                                'table'=>'(SELECT count(id) as quantos, avaliador_id FROM avaliador_bolsistas WHERE tipo = \'N\' AND deleted = 0 AND ano = '.date('Y').' GROUP BY avaliador_id)',
                                                'alias'=>'soma',
                                                'type'=>'LEFT',
                                                'conditions'=>'soma.avaliador_id = Avaliadors.id'
                                            ])
                                            ->where([
                                                //'Avaliadors.ano_aceite'=>date('Y'),
                                                'Avaliadors.editai_id'=>$inscricao->editai_id,
                                                'Avaliadors.tipo_avaliador IN (\'N\',\'A\')',
                                                'Avaliadors.id >'=>2236,
                                                'Avaliadors.deleted ='=>0,
                                                'Avaliadors.usuario_id NOT IN ' => $fora

                                            ])->order(['(area_id = '.$projeto->area_id.') DESC','Usuarios.nome'=>'ASC']);
                // 'Avaliadors.id NOT IN (SELECT avaliador_id FROM avaliador_bolsistas WHERE projeto_id = '.$id.' AND ano = '.date('Y').')'
            }
        //
        
        if($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();

            //testa os orientador:
                if(
                    ($inscricao->orientador==$dados['avaliador_1']) 
                    
                    ){
                    $erro .= '<li>O orientador nao pode fazer parte da banca </li>';
                }
            //


            //testa o coorientador:
                if(
                    ($inscricao->coorientador==$dados['avaliador_1']) 
                    ){
                    $erro .= '<li>O Coorientador nao pode fazer parte da banca </li>';
                }
            //
            

            if($erro==''){
                //GRAVAÇÃO DOS AVALIADORES
                    $avaliador1 = $this->Aval->newEmptyEntity();
                //
                //DADOS COMUNS:
                    $avaliador1->bolsista  =  $inscricao->id;
                    $avaliador1->tipo      =  $ab->tipo;
                    $avaliador1->situacao  =  'E';
                    $avaliador1->editai_id =  ($ab->tipo=='J'?$inscricao->edital_id:$inscricao->editai_id);
                    $avaliador1->ano       =  date('Y');
                //
                
                // dados particulares
                    
                    $avaliador1->avaliador_id  =$dados['avaliador_1'];
                    $avaliador1->ordem=$ab->ordem;
                //

                if ($this->Aval->save($avaliador1)) {
                    // se salvou o av 1, atualiza a ordem do proj bolsista
                        $alt1 = $this->AvaliadorBolsistas->updateAll([
                            'deleted' => 1,
                        ], ['id' => $ab->id]);

                    //    
                 
                }else{
                    $this->Flash->error('1 -Houve um erro na gravação dos avaliadores');
                }
                    
                return $this->redirect(['controller'=>'Projetos','action' => 'avaliarnovas']);
                
            }
            $this->Flash->error('Houve um erro na vinculação. Tente Novamente');
        }
        
        
        $situacao = $this->situacao;
        $origem = $this->origem;
    

        $this->set(compact('situacao', 'origem', 'anexo_sub', 'anexo_orientador', 'anexo_proj', 'doc_bolsista','erro', 'disponiveis', 'inscricao', 'projeto'));
    }

    //lista os avaliadores bolsistas p ver quem deu nota - raic
    public function listaavaliadoresraic($limpar=false)
    {
        parent::adminOnly();

        $w = [];
        if($this->request->getAttribute('identity')['yoda']) {
            $unidade_id = $this->request->getData('busca') ?? $this->request->getSession()->read('filtroBuscaRaic');
            if($unidade_id) {
                $this->request->getSession()->write('filtroBuscaRaic', $unidade_id);
                $w = ['Orientadores.unidade_id' => $unidade_id];
            }
            if ($limpar) {
                $this->request->getSession('filtroBuscaRaic')->destroy();
                $w = [];
            }
        } else {
             // traz o coordenador do INCQS unidade id 37
             $caso = TableRegistry::getTableLocator()->get('Unidades')->find('all')->where(['id'=>37])->first();
             //verifica se o logado e o coordenados do INCQS
             if($this->request->getAttribute('identity')['id']==$caso->usuario_id){
                $w = ['Orientadores.unidade_id IN (37, 36, 32)' ];

             }else{
                $w = ['Orientadores.unidade_id' => $this->request->getAttribute('identity')['unidade_id']];

             }
                 
        }

        $lista = $this->Aval->find('all')->contain([
            'Avaliadors'=>['Usuarios'],
            'Raics'=>[
                'Usuarios', 
                'Orientadores'=> 'Unidades',
                'Unidades',
                'Editais'
            ]
        ])
        ->where([
            'AvaliadorBolsistas.ano'=>date('Y'),
            'AvaliadorBolsistas.tipo' => 'V', 
            'AvaliadorBolsistas.deleted' => 0, 

            //'AvaliadorBolsistas.coordenador'=>1,
            $w
        ])
        ->order([
                'AvaliadorBolsistas.bolsista' => 'DESC', 
        ]);
      
        $unidades = TableRegistry::getTableLocator()->get('Unidades')->find('list')->where(['id > 0']);
        $lista = $this->paginate($lista);

        $this->set(compact('lista', 'unidades'));
    }
    
    public function listaavaliadoresnova()
    {
        if((!$this->request->getAttribute('identity')['yoda'])){
            $this->Flash->error('Restrito à Gestão');
            return $this->redirect(['controller'=>'Index', 'action'=>'dashboard']);
        }


        $w = [];      
        $busca = $this->request->getSession()->read('buscaAvnova', [$w]);

        if ($this->request->is(['post', 'put', 'patch'])) {

            $dados = $this->request->getData();

            //traz o edital filtrado
            $edital_selecionado = TableRegistry::getTableLocator()->get('Editais')
            ->find('all')->where(['id'=>$dados['editai_id']])->first();

            if($dados['editai_id'] != ''){

                array_push($w , ['AvaliadorBolsistas.editai_id' => ($dados['editai_id'])]);

                if($dados['situacao'] != ''){
                    array_push($w , ['AvaliadorBolsistas.situacao' => ($dados['situacao'])]);
                }  
                
                $lista = $this->Aval->find('all')->contain([
                    'Avaliadors'=>['Usuarios'],
                    'ProjetoBolsistas'=>[
                        'Usuarios', 
                        'Orientadores'=> 'Unidades',
                        'Editais'
                    ]
                ])
                ->where([
                    'AvaliadorBolsistas.tipo' => 'N', 
                    'AvaliadorBolsistas.deleted' => 0, 
                    $w
                ])
                ->order([
                        'Orientadores.nome' => 'ASC', 
                        'AvaliadorBolsistas.ordem'=>'ASC'
                ]);
            
                            
            }else{
                $this->Flash->error('Selecione um edital');
                return $this->redirect(['action' => 'listaavaliadoresnova']);
            }
            $this->request->getSession()->write('buscaAvnova',$w);

            $listas = $this->paginate($lista, ['limit'=>10]);    



        }else{
            
            $w = $busca;
            if($busca[0]!=[]){
                $lista = $this->Aval->find('all')->contain([
                    'Avaliadors'=>['Usuarios'],
                    'ProjetoBolsistas'=>[
                        'Usuarios', 
                        'Orientadores'=> 'Unidades',
                        'Editais'
                    ]
                ])
                ->where([
                    'AvaliadorBolsistas.tipo' => 'N', 
                    'AvaliadorBolsistas.deleted' => 0, 
                    $w
                ])
                ->order([
                        'Orientadores.nome' => 'ASC', 
                        'AvaliadorBolsistas.ordem'=>'ASC'
                ]);
        
                $listas = $this->paginate($lista, ['limit'=>10]);  
            }else{
                $listas=null;
            }
        }

        
        

        $editais = TableRegistry::getTableLocator()->get('Editais')
        ->find('list')
        ->where(['inicio_vigencia > NOW()'])
        ->where(['origem'=>'N'])

        ->order(['Editais.nome'=>'Asc']);
 
        $this->set(compact('listas','editais'));
        
    }


     //lista os avaliadores bolsistas p ver quem deu nota - nova
     public function listaavaliadoresnova_2($limpar=false)
     {
         parent::adminOnly();
 
         $w = [];
         if($this->request->getAttribute('identity')['yoda']) {
             $unidade_id = $this->request->getData('busca') ?? $this->request->getSession()->read('filtroBuscaRaic');
             if($unidade_id) {
                 $this->request->getSession()->write('filtroBuscaRaic', $unidade_id);
                 $w = ['Orientadores.unidade_id' => $unidade_id];
             }
             if ($limpar) {
                 $this->request->getSession('filtroBuscaRaic')->destroy();
                 $w = [];
             }
         } else {
              // traz o coordenador do INCQS unidade id 37
              $caso = TableRegistry::getTableLocator()->get('Unidades')->find('all')->where(['id'=>37])->first();
              //verifica se o logado e o coordenados do INCQS
              if($this->request->getAttribute('identity')['id']==$caso->usuario_id){
                 $w = ['Orientadores.unidade_id IN (37, 36, 32)' ];
 
              }else{
                 $w = ['Orientadores.unidade_id' => $this->request->getAttribute('identity')['unidade_id']];
 
              }
                  
         }
 
         $lista = $this->Aval->find('all')->contain([
             'Avaliadors'=>['Usuarios'],
             'ProjetoBolsistas'=>[
                 'Usuarios', 
                 'Orientadores'=> 'Unidades',
                 'Editais'
             ]
         ])
         ->where([
             'AvaliadorBolsistas.ano'=>date('Y'),
             'AvaliadorBolsistas.tipo' => 'N', 
             'AvaliadorBolsistas.deleted' => 0, 

             $w
         ])
         ->order([
                 'AvaliadorBolsistas.bolsista' => 'DESC', 
         ]);
       
         $unidades = TableRegistry::getTableLocator()->get('Unidades')->find('list')->where(['id > 0']);
         $lista = $this->paginate($lista);
 
         $this->set(compact('lista', 'unidades'));
     }

    
    //avaliar bolsa nova
    public function vernotas($id)
    {
        
        //busca o avaliador bolsista
            $this->AvaliadorBolsistas = TableRegistry::getTableLocator()->get('AvaliadorBolsistas');
            $ab = $this->AvaliadorBolsistas
                ->find('all')
                ->contain(['Avaliadors'=>'Usuarios', 'Raics'])
                ->where(['AvaliadorBolsistas.id'=>$id])->first();
        //

        //trata para achar o orientador
            if($ab->tipo=='V'){
                $this->Raics = TableRegistry::getTableLocator()->get('Raics');
                $rc = $this->Raics
                    ->find('all')
                    ->where(['Raics.id'=>$ab->bolsista])->first();
                $orienta=$rc->orientador;
                $bolsa=$rc->usuario_id;
            }
            if($ab->tipo=='N'){
                $this->Bolsista = TableRegistry::getTableLocator()->get('ProjetoBolsistas');
                $pb = $this->Bolsista
                    ->find('all')
                    ->where(['ProjetoBolsistas.id'=>$ab->bolsista])->first();
                $orienta=$pb->orientador;
                $bolsa=$pb->usuario_id;

            }
            if($ab->tipo=='W'){
                $this->Workshops = TableRegistry::getTableLocator()->get('Workshops');
                $rc = $this->Workshops
                    ->find('all')
                    ->where(['Workshops.id'=>$ab->bolsista])->first();
                $orienta=$rc->orientador;
                $bolsa=$rc->usuario_id;
            }
            if($ab->tipo=='J'){
                $this->PdjInscricoes = TableRegistry::getTableLocator()->get('PdjInscricoes');
                $pb = $this->PdjInscricoes
                    ->find('all')
                    ->where(['PdjInscricoes.id'=>$ab->bolsista])->first();
                $orienta=$pb->usuario_id;
                $bolsa=$pb->bolsista;

            }

        //

        //verifica logado
            if(!$this->request->getAttribute('identity')['yoda']){
                if(in_array($ab->tipo, ['V', 'N'])){

                    if($this->request->getAttribute('identity')['id']!=$orienta){
                        $this->Flash->error('SEM ACESSO');
                        return $this->redirect(['controller'=>'Index', 'action' => 'dashboard' ]);
                    }
                
                }
                if(in_array($ab->tipo, ['W', 'J'])){
                    if(!in_array($this->request->getAttribute('identity')['id'], [$orienta, $bolsa])){

                        $this->Flash->error('SEM ACESSO');
                        return $this->redirect(['controller'=>'Index', 'action' => 'dashboard' ]);
                    }
                
                }
            }
        //
       

        //TRAZ AS AVALIATIONS
            $avaliado = TableRegistry::getTableLocator()
            ->get('Avaliations')->find('all')
            ->contain(['Questions'])
            ->where(['Avaliations.deleted'=>0])
            ->where(['Avaliations.avaliador_bolsista_id'=>$ab->id]);
        //
        
        
        $this->set(compact('ab', 'avaliado'));
    }

    

    public function deletanotas()
    {
        // testa se o logado e o orientador
            if(!in_array($this->request->getAttribute('identity')['id'], [1, 8088])){
                $this->Flash->error('Acesso Restrito');
                return $this->redirect(['controller'=>'Index', 'action' => 'dashboard' ]);
            }
        //
        $this->viewBuilder()->autoRender = false;

        $avaliador_bolsista_id = $this->request->getData('abId');
        $this->Avaliations = TableRegistry::getTableLocator()->get('Avaliations');
        $deletados = $this->Avaliations->updateAll(['deleted' => 1], ['avaliador_bolsista_id' => $avaliador_bolsista_id]);
        $this->Flash->default("Foram alterados $deletados registros na tabela Avaliations");

        $ab = $this->AvaliadorBolsistas->get($avaliador_bolsista_id);
        $ab->situacao = 'E';
        $ab->observacao	= null;
        $ab->nota = null;
        if ($this->AvaliadorBolsistas->save($ab)) {
            if($ab->tipo == 'V') {
                $altRaics = $this->AvaliadorBolsistas->updateAll([
                    'situacao' => 'E',
                    'observacao' => null,
                    'nota' => null,
                ], ['bolsista' => $ab->bolsista, 'ano' => $ab->ano, 'tipo' => 'V']);
                $this->Flash->default("Foram alteradas as notas de $altRaics avaliadores de RAIC");
                $raic = $this->Raics->get($ab->bolsista);
                $raic->nota_final = null;
                $raic->observacao_avaliador = null;
                $raic->destaque = null;
                $raic->indicado_premio_capes = null;
                if($this->Raics->save($raic)) {
                    $this->Flash->success('Notas excluídas com sucesso');
                }else{
                    $this->Flash->error('Erro ao alterar a tabela RAIC');
                }
            }
        } else {
            $this->Flash->error('Erro ao alterar a tabela AvaliadorBolsistas');
        }
        return $this->redirect(['controller' => 'Avaliadors', 'action' => 'vernotas', $avaliador_bolsista_id]);
    }

    public function minhasAvaliacoes()
    {
        $this->Avaliacoes = TableRegistry::getTableLocator()->get('AvaliadorBolsistas');
        //lista avaliações raic
        $avaliacoes_raic = $this->Avaliacoes
                           ->find('all')
                           ->contain(['Avaliadors','Raics'=>['Editais','Usuarios','Orientadores'=>'Unidades','Projetos'=>['Usuarios'=>'Unidades']],'Avaliations'])
                           ->where([
                            'Avaliadors.usuario_id' => $this->request->getAttribute('identity')['id'],
                            'AvaliadorBolsistas.tipo IN' => ['Z', 'V']
                            ])                           
                            ->where(['AvaliadorBolsistas.deleted'=>0])
                           ->where(['AvaliadorBolsistas.ano'=>date('Y')]);

        $avaliacoes_novas = $this->Avaliacoes
                           ->find('all')                          
                           ->contain(['Avaliadors',
                                    'ProjetoBolsistas'=>['Editais','Usuarios','Orientadores'=>'Unidades','Projetos'=>['Usuarios'=>'Unidades']],'Avaliations'])
                           ->where(['Avaliadors.usuario_id'=>$this->request->getAttribute('identity')['id'], 'AvaliadorBolsistas.tipo'=>'N'])
                           ->where(['AvaliadorBolsistas.deleted'=>0])
                           ->where(['AvaliadorBolsistas.ano'=>date('Y')]);   
                           

        $avaliacoes_pdj_nova = $this->Avaliacoes
                           ->find('all')
                           ->contain(['Avaliadors','PdjInscricoes'=>['Editais','Candidatos','Usuarios'=>'Unidades','Projetos'=>['Usuarios'=>'Unidades']],'Avaliations'])
                           ->where([
                            'Avaliadors.usuario_id' => $this->request->getAttribute('identity')['id'],
                            'AvaliadorBolsistas.tipo IN' => ['J']
                            ])                           
                            ->where(['AvaliadorBolsistas.deleted'=>0])
                           ->where(['AvaliadorBolsistas.ano'=>date('Y')]);

        $avaliacoes_pdj_work = $this->Avaliacoes
                           ->find('all')
                           ->contain(['Avaliadors','Workshops'=>['Editais', 'PdjInscricoes'=>['Editais','Candidatos','Usuarios'=>'Unidades','Projetos'=>['Usuarios'=>'Unidades']]],'Avaliations'])
                           ->where([
                            'Avaliadors.usuario_id' => $this->request->getAttribute('identity')['id'],
                            'AvaliadorBolsistas.tipo IN' => ['W']
                            ])                           
                            ->where(['AvaliadorBolsistas.deleted'=>0])
                           ->where(['AvaliadorBolsistas.ano'=>date('Y')]);
                           
        $editais = TableRegistry::getTableLocator()->get('AvaliadorBolsistas')
                           ->find()
                           ->select(['AvaliadorBolsistas.ano', 'AvaliadorBolsistas.avaliador_id', 'AvaliadorBolsistas.editai_id', 'Editais.nome'])
                           ->distinct()
                           ->contain(['Editais'])
                           ->join(['table'=>'avaliadors',
                           'alias'=>'AB','type'=>'INNER', 
                           'conditions'=>'AB.id = AvaliadorBolsistas.avaliador_id'])
                           ->where(['AB.usuario_id'=>$this->request->getAttribute('identity')['id'], 
                                'AvaliadorBolsistas.deleted'=>0, 
                                'Editais.tipo NOT IN ("J", "W")'])
                           ->order(['AvaliadorBolsistas.ano'=>'DESC']);

        
        $bolsista = null;
        $avaliacoes_anteriores=null;
        $tipo = $this->tipo;

        
       
        $this->set(compact('avaliacoes_pdj_nova','avaliacoes_pdj_work',  'tipo', 'avaliacoes_raic', 'avaliacoes_novas', 'avaliacoes_anteriores', 'bolsista', 'editais'));
    }
}

