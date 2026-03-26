<?php
declare(strict_types=1);

namespace App\Controller;
use Cake\ORM\TableRegistry;


/**
 * Raics Controller
 *
 * @property \App\Model\Table\RaicsTable $Raics
 * @method \App\Model\Entity\Raic[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class RaicsController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Bolsista = TableRegistry::getTableLocator()->get('ProjetoBolsistas');
        $this->Lista = TableRegistry::getTableLocator()->get('ListaRaics');
        $this->Aval = TableRegistry::getTableLocator()->get('AvaliadorBolsistas');

        $this->viewBuilder()->setLayout('admin');
    }
   
    public function index()
    {
        $this->paginate = [
            'contain' => ['Usuarios', 'ProjetoBolsistas'],
        ];
        $raics = $this->paginate($this->Raics);

        $this->set(compact('raics'));
    }

    public function lista($limpar=false)
    {
        if((!$this->request->getAttribute('identity')['yoda']) && 
        (!$this->request->getAttribute('identity')['jedi'])
        ){
            $this->Flash->error('Restrito a administradores');
            return $this->redirect(['action'=>'index']);
        }


        if($this->request->is('post')){

            // tratando a busca por unidades, apenas para adm
            if($this->request->getAttribute('identity')['yoda']){
                $dados = $this->request->getData();
                $unidade_id=$dados['busca'];


                if($dados['busca']==''){
                    $lista = $this->Lista->find('all')->contain([
                        'Usuarios', 
                        'Orientadores'=>'Unidades',
                        'Unidades',
                        'Raics',
                        'Editais',
                        'ProjetoBolsistas'
                    ])
                    ->where(['ListaRaics.deleted'=>0])

                    ->order([
                        'ListaRaics.created' => 'DESC', 
                    ]);
                    
                }else{
                    

                    $lista = $this->Lista->find('all')->contain([
                        'Usuarios', 
                        'Orientadores'=>'Unidades',
                        'Unidades',
                        'Raics',
                        'Editais',
                        'ProjetoBolsistas'

                    ])
                    ->where(['ListaRaics.deleted'=>0])

                    ->where(['orientador_unidade_id'=>$unidade_id])
                    ->order([
                        'ListaRaics.created' => 'DESC', 
                    ]);
                }
            }
            
            


            //se for jedi nao tem busca pq so traz sua propria unidade
        }else{
            if($this->request->getAttribute('identity')['jedi']){

                // traz o coordenador do INCQS unidade id 37
                $caso = TableRegistry::getTableLocator()
                ->get('Unidades')->find('all')->where(['id'=>37])->first();
                //verifica se o logado e o coordenados do INCQS
                if($this->request->getAttribute('identity')['id']==$caso->usuario_id){
                    $lista = $this->Lista->find('all')->contain([
                        'Usuarios', 
                        'Orientadores'=>'Unidades',
                        'Unidades',
                        'Raics',
                        'Editais',
                        'ProjetoBolsistas'

                    ])
                    ->where(['ListaRaics.deleted'=>0])

                    ->where(['orientador_unidade_id IN'=>['37', '36', '32', '10', '5']])
                    ->order([
                        'ListaRaics.created' => 'DESC', 
                    ]);
                }
                if($this->request->getAttribute('identity')['id']!=$caso->usuario_id){

                    $lista = $this->Lista->find('all')->contain([
                        'Usuarios', 
                        'Orientadores'=>'Unidades',
                        'Unidades',
                        'Raics',
                        'Editais',
                        'ProjetoBolsistas'

                    ])
                    ->where(['ListaRaics.deleted'=>0])
    
                    ->where(['orientador_unidade_id'=>$this->request->getAttribute('identity')['unidade_id']])
                    ->order([
                        'ListaRaics.created' => 'DESC', 
                    ]);
                }





                
            }
            if($this->request->getAttribute('identity')['yoda']){
                $lista = $this->Lista->find('all')->contain([
                    'Usuarios', 
                    'Orientadores'=>'Unidades',
                    'Unidades',
                    'Raics',
                    'Editais',
                    'ProjetoBolsistas'

                ])
                ->where(['ListaRaics.deleted'=>0])

                ->order([
                    'ListaRaics.created' => 'DESC', 
                ]);
            }
        }
        
        
      
        $unidades = TableRegistry::getTableLocator()->get('Unidades')->find('list',['limit'=>100])->where(['id > 0']);


        $this->set(compact('lista', 'unidades'));
    }

    public function agendar($id)
    {
        //liberar na mao
        $liberada=true;
        $erro = '';

        $inscricao = TableRegistry::getTableLocator()->get('ProjetoBolsistas')
            ->get($id,['contain'=>[
                
                    'Usuarios',
                    'Orientadores'=>'Unidades',
                    'Coorientadores'
                ]        
            ])
        ;  

        //testa periodo
            if(!$liberada){
                $erro .= '<li> Fora do prazo para agendamento da Raic </li>';
            }
        //

        //testa jedi <> 1/8088
            if(!in_array($this->request->getAttribute('identity')['id'], ['1', '8088'])){
                if(!$this->request->getAttribute('identity')['jedi']){
                    $erro .= '<li> Somente a gestão da unidade pode marcar a Raic </li>';
                }
            }
        //

        
        //testa unidade se for jedi
            $caso = TableRegistry::getTableLocator()->get('Unidades')
                    ->find('all')->where(['id'=>37])->first();
            
            if($this->request->getAttribute('identity')['jedi']){
                
                //verifica se o logado e o coordenados do INCQS
                if($this->request->getAttribute('identity')['id']==$caso->usuario_id){
                    if(!in_array($inscricao->orientadore->unidade_id, ['37', '36', '32', '10', '5'])){
                        $erro .= '<li> Unidade não acessível para sua gestão </li>';
                    }
                }else{
                    if(($this->request->getAttribute('identity')['unidade_id']!=$inscricao->orientadore->unidade_id)){
                        $erro .= '<li> Unidade não acessível para sua gestão </li>';
                    }

                }
                    
            }else{
                if(!in_array($this->request->getAttribute('identity')['id'], ['1', '8088'])){
                    $erro .= '<li> Restrito a gestao da unidade  </li>';
                    
                }
            }



            if($this->request->getAttribute('identity')['jedi']&&($this->request->getAttribute('identity')['unidade_id']!=$inscricao->orientadore->unidade_id)){
                $caso = TableRegistry::getTableLocator()->get('Unidades')->find('all')->where(['id'=>37])->first();
                
                //verifica se o logado e o coordenados do INCQS
                if($this->request->getAttribute('identity')['id']==$caso->usuario_id){
                    if(!in_array($inscricao->orientadore->unidade_id, ['37', '36', '32', '10', '5'])){
                        $erro .= '<li> Unidade não acessível para sua gestão </li>';
                    }
                }else{
                    $erro .= '<li> Unidade não acessível para sua gestão </li>';

                }
                    
            }
        //

        //testa projeto_bolsista deletado
            if($inscricao->deleted==1){
                $erro .= '<li> Houve desistência na solicitação  de renovação </li>';
            }
        //

        //testa se vai apresentar raic (opcional para fev/23)
            if($inscricao->apresentar_raic!=1){
                $erro .= '<li> O bolsista ingressou no mês de FEV e o orientador optou por não apresentar RAIC </li>';
            }
        //

        //busca raic para esta renovação
            $busca_raic = $this->Raics->find()
            ->where(['Raics.projeto_bolsista_id' => $inscricao->id])
            ->where(['Raics.deleted'=>0])->first()
            ;
            
        //

        // testa se ja tem raic
            if(!$busca_raic){
                if($inscricao->deleted==1){
                    $erro .= '<li>Não existe Raic cadastrada ou foi deletada pois a solicitação de renovação foi excluída </li>';
                }else{
                    $erro .= '<li>Não existe Raic cadastrada ou foi deletada. Entre em contato com a administração de Bolsas Fiocruz </li>';
                }
            }        
            
            
        //

        if($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();

            if($dados['data_apresentacao']!=''){
                $dados['data_apresentacao'] = parent::acertaData($dados['data_apresentacao']);
            }else{
                $dados['data_apresentacao'] = null;
            }

         

            //testa os orientador:
                if(
                    ($inscricao->orientador==$dados['coordenador']) 
                    ||($inscricao->orientador==$dados['avaliador_1']) 
                    ||($inscricao->orientador==$dados['avaliador_2']) 
                    // ||($inscricao->orientador==$dados['avaliador_3'])
                    ){
                    $erro .= '<li>O orientador nao pode fazer parte da banca </li>';
                }
            //

            //testa o coorientador:
                if(
                    ($inscricao->coorientador==$dados['coordenador']) 
                    ||($inscricao->coorientador==$dados['avaliador_1']) 
                    ||($inscricao->coorientador==$dados['avaliador_2']) 
                    ){
                    $erro .= '<li>O Coorientador nao pode fazer parte da banca </li>';
                }
            //

            //testa repetição:
                if(($dados['coordenador']==$dados['avaliador_1']) ||
                ($dados['coordenador']==$dados['avaliador_2']) ||
                ($dados['avaliador_1']==$dados['avaliador_2']) 
                ){
                    $erro .= '<li>Não pode haver repetição </li>';
                }
            //
		
            $busca_raic = $this->Raics->patchEntity($busca_raic, $dados);
            if($erro==''){
                if ($this->Raics->save($busca_raic)) {
                    $this->Flash->success('A Raic foi agendada com sucesso. É necessário vincular os avaliadores');

                    //GRAVAÇÃO DOS AVALIADORES
                        $coordenador = $this->Aval->newEmptyEntity();
                        $avaliador1 = $this->Aval->newEmptyEntity();
                        $avaliador2 = $this->Aval->newEmptyEntity();
                    //

                    //DADOS COMUNS:
                        $coordenador->bolsista  = $avaliador1->bolsista  = $avaliador2->bolsista   = $busca_raic->id;
                        $coordenador->tipo      = $avaliador1->tipo      = $avaliador2->tipo       = 'V';
                        $coordenador->situacao  = $avaliador1->situacao  = $avaliador2->situacao   = 'E';
                        $coordenador->editai_id = $avaliador1->editai_id = $avaliador2->editai_id  = $busca_raic->editai_id;
                        $coordenador->ano       = $avaliador1->ano       = $avaliador2->ano        = date('Y');
                    //

                    // dados particulares
                        $coordenador->coordenador  =1;
                        $coordenador->avaliador_id  =$dados['coordenador'];
                        $avaliador1->avaliador_id  =$dados['avaliador_1'];
                        $avaliador2->avaliador_id  =$dados['avaliador_2'];
                        $coordenador->ordem=1;
                        $avaliador1->ordem=2;
                        $avaliador2->ordem=3;
                    //




                    if ($this->Aval->save($coordenador)) {
                        if ($this->Aval->save($avaliador1)) {
                            if ($this->Aval->save($avaliador2)) {
                                $this->Flash->success('Os avaliadores Foram salvos com sucesso');
                                
                            }else{
                                $this->Flash->error('2 -Houve um erro na gravação dos avaliadores');

                            }
                        }else{
                            $this->Flash->error('1 -Houve um erro na gravação dos avaliadores');
                        }
                    }else{
                        $this->Flash->error('0 -Houve um erro na gravação dos avaliadores');
                    }



                    return $this->redirect(['action' => 'lista']);
                }
            }
            $this->Flash->error('Houve um erro no agendamento da Raic. Tente Novamente');
        }
        
              
        $this->Livres = TableRegistry::getTableLocator()->get('Avaliadors');
        $this->Livres->setDisplayField('nome');
        $avaliadores = $this->Livres->find('list',[
            'fields'=>['Avaliadors.id','Avaliadors__nome'=>'(CONCAT(Usuarios.nome, \'  (\', COALESCE(Unidades.sigla, "Unidade não informada"), \') \', COALESCE(soma.quantos,0), \'  (\', COALESCE(GrandesAreas.nome, "Não informada"), " - ", COALESCE(Areas.nome, "Não informada"), \')\'))']])
        ->contain(['GrandesAreas','Areas','Usuarios'=>'Unidades'])
        ->join([
            'table'=>'(SELECT count(id) as quantos, avaliador_id FROM avaliador_bolsistas where editai_id='.$inscricao->editai_id.' GROUP BY avaliador_id)',
            'alias'=>'soma',
            'type'=>'LEFT',
            'conditions'=>'soma.avaliador_id = Avaliadors.id'
        ])
        ->where([
            'Avaliadors.ano_aceite'=>date('Y'),
            'Avaliadors.deleted'=>0,
            'Avaliadors.usuario_id <>'=>$inscricao->orientador,
            //'Avaliadors.unidade_id'=>$this->request->getAttribute('identity')['unidade_id']
            'Avaliadors.unidade_id'=>$inscricao->orientadore->unidade_id

        ])
        ->order(['Usuarios.nome'=>'ASC']);

        $this->set(compact('liberada', 'erro', 'avaliadores', 'inscricao'));
    }

    public function alteraapresentacao($id)
    {
        //liberar na mao
        $liberada=false;
        $erro = '';

        //traz a raic
            $raic = TableRegistry::getTableLocator()->get('Raics')
                ->get($id,['contain'=>[
                    
                        'ProjetoBolsistas'=>['Orientadores', 'Usuarios']
                    ]        
                ])
            ;  
        //

        //testa periodo
            if(!$liberada){
                $erro .= '<li> Fora do prazo para agendamento da Raic </li>';
            }
        //

        //testa jedi <> 1/8088
            if(!in_array($this->request->getAttribute('identity')['id'], ['1', '8088'])){
                if(!$this->request->getAttribute('identity')['jedi']){
                    $erro .= '<li> Somente a gestão da unidade pode marcar a Raic </li>';
                }
            }
        //

        
        //testa unidade se for jedi
            $caso = TableRegistry::getTableLocator()->get('Unidades')
                    ->find('all')->where(['id'=>37])->first();
            
            if($this->request->getAttribute('identity')['jedi']){
                
                //verifica se o logado e o coordenados do INCQS
                if($this->request->getAttribute('identity')['id']==$caso->usuario_id){
                    if(!in_array($raic->unidade_id, ['37', '36', '32', '10', '5'])){
                        $erro .= '<li> Unidade não acessível para sua gestão #2 </li>';
                    }
                }else{
                    if(($this->request->getAttribute('identity')['unidade_id']!=$raic->unidade_id)){
                        $erro .= '<li> Unidade não acessível para sua gestão #1 </li>';
                    }

                }
                    
            }else{
                if(!in_array($this->request->getAttribute('identity')['id'], ['1', '8088'])){
                    $erro .= '<li> Restrito a gestao da unidade  </li>';
                    
                }
            }



            if($this->request->getAttribute('identity')['jedi']&&($this->request->getAttribute('identity')['unidade_id']!=$raic->unidade_id)){
                $caso = TableRegistry::getTableLocator()->get('Unidades')->find('all')->where(['id'=>37])->first();
                
                //verifica se o logado e o coordenados do INCQS
                if($this->request->getAttribute('identity')['id']==$caso->usuario_id){
                    if(!in_array($raic->unidade_id, ['37', '36', '32', '10', '5'])){
                        $erro .= '<li> Unidade não acessível para sua gestão #3 </li>';
                    }
                }else{
                    $erro .= '<li> Unidade não acessível para sua gestão #4 </li>';

                }
                    
            }
        //

        //testa projeto_bolsista deletado
            if($raic->projeto_bolsista->deleted==1){
                $erro .= '<li> Houve desistência na solicitação  de renovação </li>';
            }
        //

        //testa projeto_bolsista deletado
            if($raic->deleted==1){
                $erro .= '<li> A raic foi deletada </li>';
            }
        //

        //testa se a raic ja passou
        if(!in_array($this->request->getAttribute('identity')['id'], ['1', '8088'])){
            if (date('Ymd')>=$raic->data_apresentacao->i18nFormat('YMMdd')){
                $erro .= '<li> A raic já ocorreu ou esta ocorrendo hoje </li>';
            }
        }
        //
        

        
        if($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();

            if($dados['data_apresentacao']!=''){
                $dados['data_apresentacao'] = parent::acertaData($dados['data_apresentacao']);
            }else{
                $dados['data_apresentacao'] = null;
            }

		
            $raic = $this->Raics->patchEntity($raic, $dados);
            if($erro==''){
                if ($this->Raics->save($raic)) {
                    $this->Flash->success('A Raic foi alterada com sucesso.');

                    return $this->redirect(['action' => 'ver', $raic->id]);
                }
            }
            $this->Flash->error('Houve um erro no agendamento da Raic. Tente Novamente');
        }
        
              
        

        $this->set(compact('erro', 'raic'));
    }

    public function alterabanca($id)
    {
        //liberar na mao
        $liberada=true;
        $erro = '';


        //traz o avaliador_bolsista
            $avaliador_bolsista = TableRegistry::getTableLocator()->get('AvaliadorBolsistas')
                ->get($id,['contain'=>[
                    
                    ]        
                ])
            ;  
        //

        //traz a raic
            $raic = $this->Raics->find()->contain(['ProjetoBolsistas'])
                ->where(['Raics.id' => $avaliador_bolsista->bolsista])
                ->first()
            ;
        //
       
        //testa periodo
            if(!$liberada){
                $erro .= '<li> Fora do prazo para agendamento da Raic </li>';
            }
        //
        

        //testa jedi <> 1/8088
            if(!in_array($this->request->getAttribute('identity')['id'], ['1', '8088'])){
                if(!$this->request->getAttribute('identity')['jedi']){
                    $erro .= '<li> Somente a gestão da unidade pode marcar a Raic </li>';
                }
            }
        //

        
        //testa unidade se for jedi
            $caso = TableRegistry::getTableLocator()->get('Unidades')
                    ->find('all')->where(['id'=>37])->first();
            
            if($this->request->getAttribute('identity')['jedi']){
                
                //verifica se o logado e o coordenados do INCQS
                if($this->request->getAttribute('identity')['id']==$caso->usuario_id){
                    if(!in_array($raic->unidade_id, ['37', '36', '32', '10', '5'])){
                        $erro .= '<li> Unidade não acessível para sua gestão </li>';
                    }
                }else{
                    if(($this->request->getAttribute('identity')['unidade_id']!=$raic->unidade_id)){
                        $erro .= '<li> Unidade não acessível para sua gestão </li>';
                    }

                }
                    
            }else{
                if(!in_array($this->request->getAttribute('identity')['id'], ['1', '8088'])){
                    $erro .= '<li> Restrito a gestao da unidade  </li>';
                    
                }
            }



            if($this->request->getAttribute('identity')['jedi']&&($this->request->getAttribute('identity')['unidade_id']!=$raic->unidade_id)){
                $caso = TableRegistry::getTableLocator()->get('Unidades')->find('all')->where(['id'=>37])->first();
                
                //verifica se o logado e o coordenados do INCQS
                if($this->request->getAttribute('identity')['id']==$caso->usuario_id){
                    if(!in_array($raic->unidade_id, ['37', '36', '32', '10', '5'])){
                        $erro .= '<li> Unidade não acessível para sua gestão </li>';
                    }
                }else{
                    $erro .= '<li> Unidade não acessível para sua gestão </li>';

                }
                    
            }
        //

        //testa projeto_bolsista deletado
            if($raic->projeto_bolsista->deleted==1){
                $erro .= '<li> Houve desistência na solicitação  de renovação </li>';
            }
        //

        

        // testa raic deleteda
            if($raic->deleted==1){
                $erro .= '<li>Esta Raic foi deletada </li>';
            }
            
        //

        // testa avaliador_bolsista deleteda
            if($avaliador_bolsista->deleted==1){
                $erro .= '<li>Este avaliador foi deletado </li>';
            }
            
        //

        // testa avaliador_bolsista deu notas
            if($avaliador_bolsista->situacao=='F'){
                $erro .= '<li>Este avaliador já lançou as notas. Não é possível alterar </li>';
            }
            
        //

        /*
        //testa se a raic ja passou
            if (date('Ymd')>=$raic->data_apresentacao->i18nFormat('YMMdd')){
                $erro .= '<li> A raic já ocorreu ou esta ocorrendo hoje </li>';

            }
        //
        */
        if($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();

                  

            //testa os orientador:
                if($raic->orientador==$dados['coordenador']){
                    $erro .= '<li>O orientador nao pode fazer parte da banca </li>';
                }
            //

            //testa o coorientador:
                if($raic->projeto_bolsista->coorientador==$dados['coordenador']){
                    $erro .= '<li>O Coorientador nao pode fazer parte da banca </li>';
                }
            //

            
		
            if($erro==''){

                    //GRAVAÇÃO DOS AVALIADORES
                        $coordenador = $this->Aval->newEmptyEntity();
                    //

                    //DADOS COMUNS:
                        $coordenador->bolsista  = $raic->id;
                        $coordenador->tipo      = 'V';
                        $coordenador->situacao  = 'E';
                        $coordenador->editai_id = $raic->editai_id;
                        $coordenador->ano       = date('Y');
                    //

                    // dados particulares
                        $coordenador->avaliador_id  =$dados['coordenador'];
                        $coordenador->ordem = $avaliador_bolsista->ordem;
                    //




                    if ($this->Aval->save($coordenador)) {

                        //deleta o avaliador anterior
                            $this->AvaliadorBolsistas = TableRegistry::getTableLocator()->get('AvaliadorBolsistas');
                            $alt_aval = $this->AvaliadorBolsistas->updateAll([
                                'deleted' => 1,
                            ], ['id' => $avaliador_bolsista->id]);
                        //
                        
                        $this->Flash->success('Avaliador alterado com sucesso');

                    }else{
                        $this->Flash->error('0 -Houve um erro na gravação dos avaliadores');

                    }



                    return $this->redirect(['action' => 'ver', $raic->id]);
                
            }
            $this->Flash->error('Houve um erro no agendamento da Raic. Tente Novamente');
        }
        
              
        $this->Livres = TableRegistry::getTableLocator()->get('Avaliadors');
        $this->Livres->setDisplayField('nome');
        $avaliadores = $this->Livres->find('list',[
            'fields'=>['Avaliadors.id','Avaliadors__nome'=>'(CONCAT(Usuarios.nome, \'  (\', COALESCE(Unidades.sigla, "Unidade não informada"), \') \', COALESCE(soma.quantos,0), \'  (\', COALESCE(GrandesAreas.nome, "Não informada"), " - ", COALESCE(Areas.nome, "Não informada"), \')\'))']])
        ->contain(['GrandesAreas','Areas','Usuarios'=>'Unidades'])
        ->join([
            'table'=>'(SELECT count(id) as quantos, avaliador_id FROM avaliador_bolsistas where editai_id='.$raic->editai_id.' GROUP BY avaliador_id)',
            'alias'=>'soma',
            'type'=>'LEFT',
            'conditions'=>'soma.avaliador_id = Avaliadors.id'
        ])
        ->where([
            'Avaliadors.ano_aceite'=>date('Y'),
            'Avaliadors.deleted'=>0,
            'Avaliadors.usuario_id <>'=>$raic->orientador,
            //'Avaliadors.unidade_id'=>$this->request->getAttribute('identity')['unidade_id']
            'Avaliadors.unidade_id'=>$raic->unidade_id

        ])
        ->order(['Usuarios.nome'=>'ASC']);

        $this->set(compact('liberada', 'erro', 'avaliadores', 'raic'));
    }


    
    public function ver($id = null)
    {
        
        $raic = $this->Raics->get($id, [
            'contain' => ['Usuarios', 
            'ProjetoBolsistas',
            'Projetos'=>['Areas', 'Linhas'],
            'Orientadores',
            'Coorientadores',
            'Cadastro',
            'Libera',

            'Unidades'
        ],
        ]);

        // Verifica se tem permissão
        if(!$this->request->getAttribute('identity')['yoda']) {

            if($this->request->getAttribute('identity')['jedi']){

                // traz o coordenador do INCQS unidade id 37
                $caso = TableRegistry::getTableLocator()->get('Unidades')->find('all')->where(['id'=>37])->first();
                //verifica se o logado e o coordenados do INCQS
                if($this->request->getAttribute('identity')['id']==$caso->usuario_id){
                    if(!in_array($raic->unidade_id, ['37', '36', '32', '10', '5'])){

                        $this->Flash->error('Esta Raic é da gestão de outra unidade #');
                        return $this->redirect(['controller' => 'Raics', 'action' => 'lista']);
                    }
                }
                // se nao for do incqs
                if($this->request->getAttribute('identity')['id']!=$caso->usuario_id){
                    // Verifica se for jedi testa a unidade
                    if($this->request->getAttribute('identity')['unidade_id']!=$raic->unidade_id) {
                        $this->Flash->error('Esta Raic é da gestão de outra unidade');
                        return $this->redirect(['controller' => 'Raics', 'action' => 'lista']);
                    }
                }
            }else{
                if(!in_array($this->request->getAttribute('identity')['id'], [$raic->orientador, $raic->usuario_id])){
                $this->Flash->error('Somente a gestão tem acesso a este módulo');
                return $this->redirect(['controller' => 'Index', 'action' => 'resumo']);
                }

            }
        }


        $lista = $this->Aval->find('all')->contain([
            'Avaliadors'=>'Usuarios'
            
        ])
        ->where([
            'AvaliadorBolsistas.bolsista' => $id, 
            'AvaliadorBolsistas.tipo' => 'V' 

        ]);

        $this->set(compact('raic', 'lista'));
    }

    /* questionario baseado na raic
    public function questionario($id)
    {
        //libera acesso na mao
        $libera=true;
        if($libera==false){
            $this->Flash->error('Fora do prazo para responder o questionario');
            return $this->redirect($this->request->referer());
        }

        // Verifica se ja foi respondido
        $this->Gabaritos = TableRegistry::getTableLocator()->get('Gabaritos');
        $feito = $this->Gabaritos->find('all')
        ->where(['Gabaritos.raic_id'=>$id])->first();

        if($feito!= null){
            $this->Flash->error('Você já respondeu ao questionário');
            return $this->redirect(['controller'=>'Raics','action'=>'verquestionario', $id]);
        }

        // traz a lista
        $this->Listas = TableRegistry::getTableLocator()->get('Listas');
        $lista = $this->Listas->find('all')
        ->where(['Listas.ano'=>date('Y')])->first();

        // traz a raic
        $raic = $this->Raics->find('all')->contain(['ProjetoBolsistas', 'Gabaritos'])
        ->where(['Raics.id'=>$id])->first();

        //verifica e o usuario logado
        if($raic->usuario_id != $this->request->getAttribute('identity')['id']){
            $this->Flash->error('Você não é o bolsista dessa Raic!');
            return $this->redirect($this->request->referer());
        }

        //verifica se esta deletada
        if($raic->deleted==1){
            $this->Flash->error('Esta Raic foi deletada');
            return $this->redirect($this->request->referer());
        }

        // gravação das notas
        if($this->request->is(['post','put','patch'])){
            
            $q = $this->request->getData('q');
            $erro = 0;
                    foreach($q as $criterio => $resposta)
                    {
                        $this->Avaliacao = TableRegistry::getTableLocator()->get('Gabaritos');
                        $nota = $this->Avaliacao->newEmptyEntity();
                        $nota->usuario_id = $this->request->getAttribute('identity')['id'];
                        $nota->raic_id = $raic->id;
                        $nota->projeto_bolsista_id = $raic->projeto_bolsista_id;


                        $nota->formulario_id = $criterio;
                        $nota->resposta = $resposta;
                        
                        if(!$this->Avaliacao->save($nota)){
                            $erro++;
                        }
                        $nota = null;
                    }
                    if($erro>0){
                        $this->Flash->error('Deu ruim!');
                    }else{
                        $this->Flash->success('Questionário gravado com sucesso!');
                    }
                    return $this->redirect(['controller'=>'Bolsistas','action'=>'raic']);
        }


        $formulario = TableRegistry::getTableLocator()
        ->get('Formularios')->find('all')->contain(['Respostas'=>'Combos'])
        ->where(['Formularios.lista_id'=>$lista->id]);


        $combo4 = TableRegistry::getTableLocator()
        ->get('Combos')->find('all')
        ->where(['Combos.resposta_id'=>4]);

        $combo3 = TableRegistry::getTableLocator()
        ->get('Combos')->find('all')
        ->where(['Combos.resposta_id'=>3]);




        $this->set(compact('formulario',  'raic', 'lista', 'combo4', 'combo3'));
    }
    */

    //questionario baseado no projeto_bolsista renovação
    public function questionario()
    {
        //libera acesso na mao
        $libera=false;
        if($libera==false){
            $this->Flash->error('Fora do prazo para responder o questionario');
            return $this->redirect($this->request->referer());
        }

        // traz a renovação do logado
        $this->ProjetoBolsistas = TableRegistry::getTableLocator()->get('ProjetoBolsistas');

        $renova = $this->ProjetoBolsistas->find('all')
        ->where(['ProjetoBolsistas.usuario_id'=>$this->request->getAttribute('identity')['id']])
        ->where (['ProjetoBolsistas.situacao'=>'F'])
        ->where(['ProjetoBolsistas.deleted'=>0])->first();

        if($renova== null){
            $this->Flash->error('Você não é aluno de renovação atualmente');
            return $this->redirect($this->request->referer());
        }

        /*
        // Verifica se ja foi respondido
        $this->Gabaritos = TableRegistry::getTableLocator()->get('Gabaritos');
        $feito = $this->Gabaritos->find('all')
        ->where(['Gabaritos.projeto_bolsista_id'=>$renova->id])->first();

        if($feito!= null){
            $this->Flash->error('Você já respondeu ao questionário');
            return $this->redirect(['controller'=>'Raics','action'=>'verquestionario', $renova->id]);
        }
        */


        // Verifica se ja foi respondido
        $this->GabaritosBolsistas = TableRegistry::getTableLocator()->get('GabaritosBolsistas');
        $feito = $this->GabaritosBolsistas->find('all')
        ->where(['GabaritosBolsistas.projeto_bolsista_id'=>$renova->id])->first();

        if($feito!= null){
            return $this->redirect(['controller'=>'Raics','action'=>'verquestionario', $renova->id]);
        }
        

        // traz a lista
        $this->Listas = TableRegistry::getTableLocator()->get('Listas');
        $lista = $this->Listas->find('all')
        ->where(['Listas.ano'=>date('Y')])->first();

        
        //verifica se esta deletada
        if($renova->deleted==1){
            $this->Flash->error('Esta solicitacao foi deletada');
            return $this->redirect($this->request->referer());
        }

        // gravação das notas
        if($this->request->is(['post','put','patch'])){

            $q = $this->request->getData('q');
            $erro = 0;

            //gravação do questionario_bolsista
            $this->GabaritosBolsistas = TableRegistry::getTableLocator()->get('GabaritosBolsistas');
            $referencia = $this->GabaritosBolsistas->newEmptyEntity();
            $referencia->usuario_id = $this->request->getAttribute('identity')['id'];
            $referencia->projeto_bolsista_id = $renova->id;
            $referencia->deleted=0;
            $referencia->finalizado=0;

            if(!$this->GabaritosBolsistas->save($referencia)){
                $erro++;
            }

            
            
                    foreach($q as $criterio => $resposta)
                    {
                        $this->Avaliacao = TableRegistry::getTableLocator()->get('Gabaritos');
                        $nota = $this->Avaliacao->newEmptyEntity();
                        $nota->usuario_id = $this->request->getAttribute('identity')['id'];
                        $nota->projeto_bolsista_id = $renova->id;


                        $nota->formulario_id = $criterio;
                        $nota->resposta = $resposta;
                        
                        if(!$this->Avaliacao->save($nota)){
                            $erro++;
                        }
                        $nota = null;
                    }
                    if($erro>0){
                        $this->Flash->error('Erro!');
                    }else{
                        $this->Flash->success('Questionário gravado com sucesso!');
                    }
                    return $this->redirect(['controller'=>'Bolsistas','action'=>'raic']);
        }


        $formulario = TableRegistry::getTableLocator()
        ->get('Formularios')->find('all')->contain(['Respostas'=>'Combos'])
        ->where(['Formularios.lista_id'=>$lista->id]);


        $combo4 = TableRegistry::getTableLocator()
        ->get('Combos')->find('all')
        ->where(['Combos.resposta_id'=>4]);

        $combo3 = TableRegistry::getTableLocator()
        ->get('Combos')->find('all')
        ->where(['Combos.resposta_id'=>3]);




        $this->set(compact('formulario',  'renova', 'lista', 'combo4', 'combo3'));
    }

    //questionario baseado no projeto_bolsista renovação
    public function editarquestionario($id)
    {
        //libera acesso na mao
        $libera=false;
        if($libera==false){
            $this->Flash->error('Fora do prazo para responder o questionario');
            return $this->redirect($this->request->referer());
        }

        
        // traz o item a ser editado
        $this->Gabaritos = TableRegistry::getTableLocator()->get('Gabaritos');
        $respondido = $this->Gabaritos->find('all')
        ->contain(['Formularios'=>['Respostas'=>'Combos']])
        ->where(['Gabaritos.id'=>$id])->first();

        // verifica se pode ser editado
        $this->GabaritosBolsistas = TableRegistry::getTableLocator()->get('GabaritosBolsistas');
        $feito = $this->GabaritosBolsistas->find('all')
        ->where(['GabaritosBolsistas.projeto_bolsista_id'=>$respondido->projeto_bolsista_id])->first();

        if($feito->finalizado== 1){
            $this->Flash->error('Você ja finalizou o questionário, não é possivel alterar');
            return $this->redirect($this->request->referer());
        }
        
        //verifica se esta deletada
        if($feito->deleted==1){
            $this->Flash->error('Esta solicitacao foi deletada');
            return $this->redirect($this->request->referer());
        } 
    
        // gravação das notas
        if($this->request->is(['post','put','patch'])){
            $erro=0;
            $dados = $this->request->getData();

            $this->Avaliacao = TableRegistry::getTableLocator()->get('Gabaritos');
            $respondido = $this->Avaliacao->patchEntity($respondido, $dados);

            if($dados['resposta']==''||$dados['resposta']==null){
                $respondido->resposta = null;

            }else{
                $respondido->resposta = $dados['resposta'];

            }
            
            if(!$this->Avaliacao->save($respondido)){
                $erro++;
            }
                if($erro>0){
                    $this->Flash->error('Erro!');
                }else{
                    $this->Flash->success('Questionário gravado com sucesso!');
                }
                return $this->redirect(['controller'=>'Raics','action'=>'verquestionario', $respondido->projeto_bolsista_id]);
            }

        $combo4 = TableRegistry::getTableLocator()
        ->get('Combos')->find('list')
        ->where(['Combos.resposta_id'=>4]);

        $combo3 = TableRegistry::getTableLocator()
        ->get('Combos')->find('list')
        ->where(['Combos.resposta_id'=>3]);




        $this->set(compact('respondido', 'combo4', 'combo3'));
    }

    public function verquestionario($id)
    {
        //libera acesso na mao
        $libera=true;
        if($libera==false){
            $this->Flash->error('Fora do prazo para responder o questionario');
            return $this->redirect($this->request->referer());
        }

        // Verifica se ja foi respondido
        $this->Gabaritos = TableRegistry::getTableLocator()->get('Gabaritos');
        $feito = $this->Gabaritos->find('all')
        ->contain(['Formularios'=>['Respostas'=>'Combos']])
        ->where(['Gabaritos.projeto_bolsista_id'=>$id]);
        //dd($feito->usuario_id   );

        if($feito== null){
            $this->Flash->error('Você não respondeu ao questionário');
            return $this->redirect($this->request->referer());
        }

        // Verifica se ja foi finalizado
        $this->GabaritosBolsistas = TableRegistry::getTableLocator()->get('GabaritosBolsistas');
        $final = $this->GabaritosBolsistas->find('all')
        ->where(['GabaritosBolsistas.projeto_bolsista_id'=>$id])->first();
        

        // traz a lista
        $this->Listas = TableRegistry::getTableLocator()->get('Listas');
        $lista = $this->Listas->find('all')
        ->where(['Listas.ano'=>date('Y')])->first();

       


        


        $gabarito = TableRegistry::getTableLocator()->get('Gabaritos')
        ->find('all')->contain(['Formularios'=>['Respostas'=>'Combos']])
        ->where(['gabaritos.projeto_bolsista_id'=>$id]);


        $combo4 = TableRegistry::getTableLocator()
        ->get('Combos')->find('all')
        ->where(['Combos.resposta_id'=>4]);

        $combo3 = TableRegistry::getTableLocator()
        ->get('Combos')->find('all')
        ->where(['Combos.resposta_id'=>3]);




        $this->set(compact('final','gabarito',  'lista', 'combo4', 'combo3', 'feito'));
    }

    //questionario baseado no projeto_bolsista renovação
    public function finalizaquestionario($id)
    {
        
        if($this->request->is(['post','put','patch'])){

            //libera acesso na mao
            $libera=false;
            if($libera==false){
                $this->Flash->error('Fora do prazo para responder o questionario');
                return $this->redirect($this->request->referer());
            }

            // Verifica se ja foi finalizado
            $this->GabaritosBolsistas = TableRegistry::getTableLocator()->get('GabaritosBolsistas');
            $final = $this->GabaritosBolsistas->find('all')
            ->where(['GabaritosBolsistas.id'=>$id])->first();

            $erro=0;
            $final->finalizado=1;
            if(!$this->GabaritosBolsistas->save($final)){
                $erro++;
            }
                if($erro>0){
                    $this->Flash->error('Erro ao finalizar o questionario!');
                }else{
                    $this->Flash->success('Questionário Finalizado com sucesso');
                }
                return $this->redirect(['controller'=>'Raics','action'=>'verquestionario', $final->projeto_bolsista_id]);
            }

       

        $this->set(compact('erro', 'final'));
    }

    public function liberacertificado($id = null)
     {
        
        //busca raic para esta renovação
            $raic = $this->Raics->get($id);
        //

        // verifica se o logado é adm ou jedi/yoda
            if($this->request->getAttribute('identity')['yoda']==1){
                if($this->request->getAttribute('identity')['jedi']==1){
                    if($this->request->getAttribute('identity')['unidade_id']<>$raic->unidade_id){
                        $this->Flash->error('Somente coordenação da unidade');
                        return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
                    }
                    
                }
            }
        //

        //verifica se esta deletada
            if($raic->deleted==1) {
                $this->Flash->error('A raic foi deletada');
                return $this->redirect(['controller' => 'Raics', 'action' => 'ver', $raic->id]);
            }            
        //

        //testa se a raic ja passou ou se esta agendada
            if ($raic->data_apresentacao==null){
                $this->Flash->error('A raic ainda nao foi agendada.');
                return $this->redirect(['controller' => 'Raics', 'action' => 'ver', $raic->id]);
            }
            if (date('Ymd')<=$raic->data_apresentacao->i18nFormat('YMMdd')){
                $this->Flash->error('A raic ainda não ocorreu. A presença so poderá ser registrada após o evento');
                return $this->redirect(['controller' => 'Raics', 'action' => 'ver', $raic->id]);
            }
        //

        //verifica se esta liberado
            if($raic->presenca=='S') {
                $this->Flash->error('O certificado já estava liberado.');
                return $this->redirect(['controller' => 'Raics', 'action' => 'ver', $raic->id]);
            }            
        //
        
        $this->request->allowMethod(['post', 'put']);
 
        $raic->presenca='S';
        $raic->usuario_libera=$this->request->getAttribute('identity')['id'];
        $raic->data_liberacao=date('Y-m-d H:i:s');
 
            if ($this->Raics->save($raic)) {
                $this->Flash->success('Certificado liberado');
                return $this->redirect($this->request->referer());
            }
            $this->Flash->error('Houve um erro durante a liberação. Tente novamente');
         
 
 
        
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }

  
    /* ver questionario baseado na raic
    public function verquestionario($id)
    {
        //libera acesso na mao
        $libera=true;
        if($libera==false){
            $this->Flash->error('Fora do prazo para responder o questionario');
            return $this->redirect($this->request->referer());
        }

        // Verifica se ja foi respondido
        $this->Gabaritos = TableRegistry::getTableLocator()->get('Gabaritos');
        $feito = $this->Gabaritos->find('all')
        ->contain(['Formularios'=>['Respostas'=>'Combos']])
        ->where(['Gabaritos.raic_id'=>$id]);

        

        // traz a lista
        $this->Listas = TableRegistry::getTableLocator()->get('Listas');
        $lista = $this->Listas->find('all')
        ->where(['Listas.ano'=>date('Y')])->first();

        // traz a raic
        $raic = $this->Raics->find('all')->contain(['ProjetoBolsistas', 'Gabaritos'])
        ->where(['Raics.id'=>$id])->first();

        //verifica e o usuario logado
        if($raic->usuario_id != $this->request->getAttribute('identity')['id']){
            $this->Flash->error('Você não é o bolsista dessa Raic!');
            return $this->redirect($this->request->referer());
        }

        

        


        $formulario = TableRegistry::getTableLocator()
        ->get('Formularios')->find('all')->contain(['Respostas'=>'Combos'])
        ->where(['Formularios.lista_id'=>$lista->id]);


        $combo4 = TableRegistry::getTableLocator()
        ->get('Combos')->find('all')
        ->where(['Combos.resposta_id'=>4]);

        $combo3 = TableRegistry::getTableLocator()
        ->get('Combos')->find('all')
        ->where(['Combos.resposta_id'=>3]);




        $this->set(compact('formulario',  'raic', 'lista', 'combo4', 'combo3', 'feito'));
    }
    */

    /*
    public function add()
    {
        $raic = $this->Raics->newEmptyEntity();
        if ($this->request->is('post')) {
            $raic = $this->Raics->patchEntity($raic, $this->request->getData());
            if ($this->Raics->save($raic)) {
                $this->Flash->success(__('The raic has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The raic could not be saved. Please, try again.'));
        }
        $usuarios = $this->Raics->Usuarios->find('list', ['limit' => 200])->all();
        $projetoBolsistas = $this->Raics->ProjetoBolsistas->find('list', ['limit' => 200])->all();
        $this->set(compact('raic', 'usuarios', 'projetoBolsistas'));
    }

   
    public function edit($id = null)
    {
        $raic = $this->Raics->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $raic = $this->Raics->patchEntity($raic, $this->request->getData());
            if ($this->Raics->save($raic)) {
                $this->Flash->success(__('The raic has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The raic could not be saved. Please, try again.'));
        }
        $usuarios = $this->Raics->Usuarios->find('list', ['limit' => 200])->all();
        $projetoBolsistas = $this->Raics->ProjetoBolsistas->find('list', ['limit' => 200])->all();
        $this->set(compact('raic', 'usuarios', 'projetoBolsistas'));
    }

    
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $raic = $this->Raics->get($id);
        if ($this->Raics->delete($raic)) {
            $this->Flash->success(__('The raic has been deleted.'));
        } else {
            $this->Flash->error(__('The raic could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }*/
}