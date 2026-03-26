<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Mailer;
use Cake\Mailer\MailerAwareTrait;
use DateTime;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\FrozenTime;




/**
 * Raics Controller
 *
 * @property \App\Model\Table\RaicsTable $Raics
 * @method \App\Model\Entity\Raic[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class RaicsController extends AppController
{
        use MailerAwareTrait;

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Bolsista = TableRegistry::getTableLocator()->get('ProjetoBolsistas');
        //$this->Lista = TableRegistry::getTableLocator()->get('ListaRaics');
        $this->Aval = TableRegistry::getTableLocator()->get('AvaliadorBolsistas');

        $this->viewBuilder()->setLayout('admin');
    }
     

    //ok 2025
    public function agendar($id)
    {
        $erro = '';

        //busca a Raic
        $inscricao = TableRegistry::getTableLocator()->get('Raics')
            ->get($id,['contain'=>[
                    'ProjetoBolsistas'=>['Orientadores', 'Usuarios'], 'Orientadores', 'Usuarios'
                ]        
            ])
        ;  

        //permissão: testa jedi <> 1/8088
            if($this->request->getAttribute('identity')['jedi']!=null){
                $jediArray = explode(',', $this->request->getAttribute('identity')['jedi']);
            }else{
                $jediArray=[];

            }


            if(
                (!($this->request->getAttribute('identity')['yoda'])) && 
                (!(in_array($inscricao->unidade_id, $jediArray)))
            ){

                $this->Flash->error('Somente a Coordenação da Unidade e a Gestão de Fomento pode agendar a RAic');
                return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);
            }
        //

        $ed = TableRegistry::getTableLocator()->get('Editais')->find()->where(['id' => $inscricao->editai_id])->first();

        if ($ed->evento==null) {
            $this->Flash->error('A inscrição nao esta vinculada a uma RAIC');
            return $this->redirect(['controller'=>'Listas', 'action' => 'raics']);       
        } else {
            $evento = TableRegistry::getTableLocator()->get('Editais')->find()->where(['id' => $ed->evento])->first();
            if (empty($evento)) {
                $this->Flash->error('A inscrição nao esta vinculada a uma RAIC');
                return $this->redirect(['controller'=>'Listas', 'action' => 'raics']);       
            } else {
                // Inicio das inscrições
                if ($evento->inicio_avaliar > FrozenTime::now()) {
                    $this->Flash->error('Período de agendamento ainda nao iniciado');
                    return $this->redirect(['controller'=>'Listas', 'action' => 'raics']);
                }
    
                // Fim das inscrições
                if ($evento->fim_avaliar < FrozenTime::now()) {
                    $this->Flash->error('Período de agendamento ENCERRADO');
                    return $this->redirect(['controller'=>'Listas', 'action' => 'raics']);        
                }
            }
        }

        
        //testa se é aluno de renovação
            if($inscricao->tipo_bolsa=='R'){
                //testa projeto_bolsista deletado
                    if($inscricao->projeto_bolsista->deleted==1){
                        $this->Flash->error('Houve desistência na solicitação  de renovação');
                        return $this->redirect(['controller'=>'Listas', 'action' => 'raics']);    
                    }
                //
                //testa projeto_bolsista situacao
                    if($inscricao->projeto_bolsista->situacao=='O'){
                        $this->Flash->error('A inscrição de renovação nao foi finalizada');
                        return $this->redirect(['controller'=>'Listas', 'action' => 'raics']);  
                    }
                //
            }
        //

        //testa se a raic esta deletada
            if($inscricao->deleted==1){
                $this->Flash->error('A Raic foi detetada');
                return $this->redirect(['controller'=>'Listas', 'action' => 'raics']);  
                    
            }
        //

        //testa se a raic esta deletada
            if($inscricao->data_apresentacao!=null){
                $this->Flash->error('A Raic ja foi agendada!');
                return $this->redirect(['controller'=>'Listas', 'action' => 'raics']);  
                
            }
        //

        

        if($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();

            //verifica ano da raic
                if((new FrozenTime($dados['data_apresentacao']))->year != date('Y')){
                    $this->Flash->error('Você só pode cadastrar a Raic para o evento atual atual. A data de apresentação tem que ser no ano atual');
                    return $this->redirect(['action'=>'agendar', $id]);
                }

                if($dados['data_apresentacao']!=''){
                    $dados['data_apresentacao'] = parent::acertaData($dados['data_apresentacao']);
                }else{
                    $dados['data_apresentacao'] = null;
                }
            //

            //testa os orientador:
                if(
                    ($inscricao->orientador==$dados['coordenador']) 
                    ||($inscricao->orientador==$dados['avaliador_1']) 
                    ||($inscricao->orientador==$dados['avaliador_2']) 
                    // ||($inscricao->orientador==$dados['avaliador_3'])
                    ){
                    $this->Flash->error('O orientador nao pode fazer parte da banca');
                    return $this->redirect(['action'=>'agendar', $id]);

                }
            //

            //testa o coorientador:
                if($inscricao->tipo=='R'){
                    if($inscricao->projeto_bolsista->coorientador!=null){
                        if(
                            ($inscricao->projeto_bolsista->coorientador==$dados['coordenador']) 
                            ||($inscricao->projeto_bolsista->coorientador==$dados['avaliador_1']) 
                            ||($inscricao->projeto_bolsista->coorientador==$dados['avaliador_2']) 
                            ){
                                $this->Flash->error('O Coorientador nao pode fazer parte da banca');
                                return $this->redirect(['action'=>'agendar', $id]);
                        }
                    }
                }
            //

            //testa repetição:
                if(($dados['coordenador']==$dados['avaliador_1']) ||
                ($dados['coordenador']==$dados['avaliador_2']) ||
                ($dados['avaliador_1']==$dados['avaliador_2']) 
                ){
                    $this->Flash->error('Não pode haver repetição');
                    return $this->redirect(['action'=>'agendar', $id]);
                }
            //

            
            
            try {

                $connection = $this->Raics->getConnection();
                $connection->transactional(function () use ($dados, $erro, $evento, $inscricao ) {

                    $inscricao_ok = $this->Raics->patchEntity($inscricao, $dados);
                    if ($this->Raics->save($inscricao)) {
                        if(!($this->historico($inscricao->id, 'Raic Agendada.', 'Raic Agendada.', 'Agendamento padrão e vinculação de banca'))){
                            throw new \Exception('Erro ao Gravar o Histórico. A RAIC não foi atualizada');
                        }

                        //GRAVAÇÃO DOS AVALIADORES
                            //instancia
                                $coordenador = $this->Aval->newEmptyEntity();
                                $avaliador1 = $this->Aval->newEmptyEntity();
                                $avaliador2 = $this->Aval->newEmptyEntity();
                            //
                                
                            //DADOS COMUNS:
                                if($inscricao->tipo_bolsa=='Z'){
                                    $tipo='Z';
                                }else{
                                    $tipo='V';
                                }

                                $coordenador->bolsista  = $avaliador1->bolsista  = $avaliador2->bolsista   = $inscricao->id;
                                $coordenador->tipo      = $avaliador1->tipo      = $avaliador2->tipo       = $tipo;
                                $coordenador->situacao  = $avaliador1->situacao  = $avaliador2->situacao   = 'E';
                                $coordenador->editai_id = $avaliador1->editai_id = $avaliador2->editai_id  = $inscricao->editai_id;
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

                            if(!($this->Aval->save($coordenador))){
                                throw new \Exception('Erro o primeiro avaliador. A RAIC não foi atualizada');
                            }
                            if(!($this->Aval->save($avaliador1))){
                                throw new \Exception('Erro o segundo avaliador. A RAIC não foi atualizada');
                            }
                            if(!($this->Aval->save($avaliador2))){
                                throw new \Exception('Erro o terceito avaliador. A RAIC não foi atualizada');
                            }

                            
                        //
                    }else{
                        throw new \Exception('Erro ao salvar o agendamento da Raic. A RAIC não foi agendada');
                    }                        
                    
                
                        
                });
                $this->Flash->success('A Raic foi agendada com sucesso.');
                return $this->redirect(['controller'=>'Listas', 'action' => 'raics']);
            
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
                <h3>Erro no Sistema - Agendar</h3>
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
                $assunto = 'Erro no Sistema - Agendar';
            
                // Enviar e-mail
                $this->getMailer('Projetos')->send('erroTi', [$to, $assunto, $corpoEmail]);
        

                $this->Flash->error('Houve um erro na gravação. Tente novamente. Detalhes: ' . $e->getMessage());
                return $this->redirect(['action' => 'agendar', $inscricao->id]);
            } 

            

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
            'Avaliadors.editai_id'=>$evento->id,
            'Avaliadors.deleted'=>0,
            'Avaliadors.usuario_id <>'=>$inscricao->orientador,
            //'Avaliadors.unidade_id'=>$this->request->getAttribute('identity')['unidade_id']
            'Avaliadors.unidade_id'=>$inscricao->unidade_id

        ])
        ->order(['Usuarios.nome'=>'ASC']);

        $this->set(compact( 'erro', 'avaliadores', 'inscricao'));
    }

    //ok 2025 
    public function alteraapresentacao($id)
    {
        
        $erro = '';

        //busca a Raic
            $raic = TableRegistry::getTableLocator()->get('Raics')
            ->get($id,['contain'=>[
                    'ProjetoBolsistas'=>['Orientadores', 'Usuarios'], 'Orientadores', 'Usuarios'
                ]        
            ])
        ;  


        
        //permissão: testa jedi <> 1/8088
            if($this->request->getAttribute('identity')['jedi']!=null){
                $jediArray = explode(',', $this->request->getAttribute('identity')['jedi']);
            }else{
                $jediArray=[];

            }


            if(
                (!($this->request->getAttribute('identity')['yoda'])) && 
                (!(in_array($raic->unidade_id, $jediArray)))
            ){

                $this->Flash->error('Somente a Coordenação da Unidade e a Gestão de Fomento pode agendar a RAic');
                return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);
            }
        //

        $ed = TableRegistry::getTableLocator()->get('Editais')->find()->where(['id' => $raic->editai_id])->first();

        if ($ed->evento==null) {
            $this->Flash->error('A inscrição nao esta vinculada a uma RAIC');
            return $this->redirect(['controller'=>'Listas', 'action' => 'raics']);       
        } else {
            $evento = TableRegistry::getTableLocator()->get('Editais')->find()->where(['id' => $ed->evento])->first();
            if (empty($evento)) {
                $this->Flash->error('A inscrição nao esta vinculada a uma RAIC');
                return $this->redirect(['controller'=>'Listas', 'action' => 'raics']);       
            } else {
                // Inicio das inscrições
                if ($evento->inicio_avaliar > FrozenTime::now()) {
                    $this->Flash->error('Período de agendamento ainda nao iniciado');
                    return $this->redirect(['controller'=>'Listas', 'action' => 'raics']);
                }

                // Fim das inscrições
                if ($evento->fim_avaliar < FrozenTime::now()) {
                    $this->Flash->error('Período de agendamento ENCERRADO');
                    return $this->redirect(['controller'=>'Listas', 'action' => 'raics']);        
                }
            }
        }

        
        //testa se é aluno de renovação
            if($raic->tipo_bolsa=='R'){
                //testa projeto_bolsista deletado
                    if($raic->projeto_bolsista->deleted==1){
                        $this->Flash->error('Houve desistência na solicitação  de renovação');
                        return $this->redirect(['controller'=>'Listas', 'action' => 'raics']);    
                    }
                //
                //testa projeto_bolsista situacao
                    if($raic->projeto_bolsista->situacao=='O'){
                        $this->Flash->error('A inscrição de renovação nao foi finalizada');
                        return $this->redirect(['controller'=>'Listas', 'action' => 'raics']);  
                    }
                //
            }
        //

        //testa se a raic esta deletada
            if($raic->deleted==1){
                $this->Flash->error('A Raic foi detetada');
                return $this->redirect(['controller'=>'Listas', 'action' => 'raics']);  
                
            }
        //

        //testa se a raic foi agendada
            if($raic->data_apresentacao==null){
                $this->Flash->error('A Raic não foi agendada ainda e portanto, nao pode ser alterada!');
                return $this->redirect(['controller'=>'Listas', 'action' => 'raics']);  
                
            }
        //
        $original='Data de apresentação original: '.($raic->data_apresentacao==null?'N/A':($raic->data_apresentacao->i18nFormat('dd/MM/Y'))).
        '; Tipo de apresentação original: '.($raic->tipo_apresentacao=='O'?'Oral':($raic->tipo_apresentacao=='P'?'Pôster':'Não identificado')).
        '; Local Original: '.(($raic->local_apresentacao==null || $raic->local_apresentacao=='')?'Não informado':$raic->local_apresentacao);
        

        
        //testa se a raic ja passou
            if(!in_array($this->request->getAttribute('identity')['id'], ['1', '8088'])){
                if ($raic->data_apresentacao->format('Y-m-d') <= FrozenTime::now()->format('Y-m-d')) {
                    $this->Flash->error('A raic já ocorreu ou esta ocorrendo hoje. Entre em contato com a Gestão de Fomento.');
                    return $this->redirect(['controller'=>'Listas', 'action' => 'raics']);  
                }
                
            }
        //
        

        
        if($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();

            //verifica ano da raic
                if((new FrozenTime($dados['data_apresentacao']))->year != date('Y')){
                    $this->Flash->error('Você só pode cadastrar a Raic para o evento atual atual. A data de apresentação tem que ser no ano atual');
                    return $this->redirect(['action'=>'agendar', $id]);
                }

                if($dados['data_apresentacao']!=''){
                    $dados['data_apresentacao'] = parent::acertaData($dados['data_apresentacao']);
                }else{
                    $dados['data_apresentacao'] = null;
                }
            //

            //teste da justificativa
                $tamanho=mb_strlen(trim($dados['justificativa']));
                if($tamanho<20){
                    $this->Flash->error('Justificativa informada: "'.$dados['justificativa'].'". O campo Justificatifa tem menos que 20 caracteres. O reagendamento não foi gravado');
                    return $this->redirect(['action' => 'alteraapresentacao', $raic->id]);
                }
            //

            
		
            $raic = $this->Raics->patchEntity($raic, $dados);
            if($erro==''){
                if ($this->Raics->save($raic)) {

                    $this->historico($raic->id, 'Raic reagendada. Dados Originais: ' . $original, 'Raic reagendada. Dados Originais: ' . $original, $dados['justificativa']);

                    $this->Flash->success('A Raic foi alterada com sucesso.');
                    return $this->redirect(['action' => 'ver', $raic->id]);
                }
            }
            $this->Flash->error('Houve um erro no agendamento da Raic. Tente Novamente');
        }
        $this->set(compact('erro', 'raic'));
    }

    //ok 2025
    public function alterabanca($id)
    {
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

        //permissão: testa jedi <> 1/8088
            if($this->request->getAttribute('identity')['jedi']!=null){
                $jediArray = explode(',', $this->request->getAttribute('identity')['jedi']);
            }else{
                if(!($this->request->getAttribute('identity')['yoda'])){
                    $jediArray=[];
                    $this->Flash->error('Somente a Coordenação da Unidade pode alterar a Banca da RAic');
                    return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);
                }
            }

            if(
                (!(in_array($this->request->getAttribute('identity')['id'], ['1', '8088']))) && 
                (!(in_array($raic->unidade_id, $jediArray)))
            ){

                $this->Flash->error('Somente a Coordenação da Unidade pode alterar a Banca da RAic');
                return $this->redirect(['controller'=>'Raics', 'action' => 'ver', $raic->id]);
            }
        //
    

        
        //testa se é aluno de renovação
            if($raic->tipo_bolsa=='R'){
                //testa projeto_bolsista deletado
                    if($raic->projeto_bolsista->deleted==1){
                        $this->Flash->error('Houve desistência na solicitação  de renovação');
                        return $this->redirect(['controller'=>'Raics', 'action' => 'ver', $raic->id]);
                    }
                //
                //testa projeto_bolsista situacao
                    if($raic->projeto_bolsista->situacao=='O'){
                        $this->Flash->error('A inscrição de renovação nao foi finalizada');
                        return $this->redirect(['controller'=>'Raics', 'action' => 'ver', $raic->id]);
                    }
                //
            }
        //

        //testa se a raic esta deletada
            if($raic->deleted==1){
                $this->Flash->error('A Raic foi detetada');
                return $this->redirect(['controller'=>'Raics', 'action' => 'ver', $raic->id]);
                
            }
        //

        //testa se a raic esta deletada
            if($raic->data_apresentacao==null){
                $this->Flash->error('A Raic não foi agendada ainda e portanto, nao pode ser alterada!');
                return $this->redirect(['controller'=>'Raics', 'action' => 'ver', $raic->id]);
                
            }
        //

        // testa avaliador_bolsista deleteda
            if($avaliador_bolsista->deleted==1){
                $this->Flash->error('O avaliador já havia sido desvinculado!');
                return $this->redirect(['controller'=>'Raics', 'action' => 'ver', $raic->id]);
                           }
            
        //

        // testa avaliador_bolsista deu notas
            if($avaliador_bolsista->situacao=='F'){
                $this->Flash->error('A nota deste avaliador ja foi lançada');
                return $this->redirect(['controller'=>'Raics', 'action' => 'ver', $raic->id]);
            }
            
        //

        //testa se a raic ja passou
            if (date('Ymd')>=$raic->data_apresentacao->i18nFormat('YMMdd')){
                $this->Flash->error('A Raic já ocorreu e não pode ter a banca alterada');
                return $this->redirect(['controller'=>'Raics', 'action' => 'ver', $raic->id]);
               
            }
        //
        
        if($this->request->is(['post', 'put', 'patch'])) {
            $dados = $this->request->getData();

            //testa os orientador:
                if($raic->orientador==$dados['coordenador']){
                    $this->Flash->error('A Raic já ocorreuu e nao pode ter a banca alterada');
                    return $this->redirect(['controller'=>'Raics', 'action' => 'alterabanca', $raic->id]);
                }
            //

            //testa o coorientador:
                if($raic->tipo_bolsa=='R'){
                    if($raic->projeto_bolsista->coorientador==$dados['coordenador']){
                        $this->Flash->error('O Coorientador nao pode fazer parte da banca');
                        return $this->redirect(['controller'=>'Raics', 'action' => 'alterabanca', $raic->id]);
                        
                    }
                }
            //

            
		
            if($erro==''){

                //GRAVAÇÃO DOS AVALIADORES
                    $coordenador = $this->Aval->newEmptyEntity();
                //

                //DADOS COMUNS:
                    $coordenador->bolsista  = $raic->id;
                    $coordenador->tipo      = $avaliador_bolsista->tipo;
                    $coordenador->situacao  = 'E';
                    $coordenador->editai_id = $raic->editai_id;
                    $coordenador->ano       = date('Y');
                //

                // dados particulares
                    $coordenador->avaliador_id  =$dados['coordenador'];
                    $coordenador->ordem = $avaliador_bolsista->ordem;
                //

                //dd($coordenador);
                if ($this->Aval->save($coordenador)) {

                    //deleta o avaliador anterior
                        $this->AvaliadorBolsistas = TableRegistry::getTableLocator()->get('AvaliadorBolsistas');
                        $alt_aval = $this->AvaliadorBolsistas->updateAll([
                            'deleted' => 1,
                        ], ['id' => $avaliador_bolsista->id]);
                    //
                    
                    $this->Flash->success('Avaliador alterado com sucesso');

                }else{
                    $erros = $coordenador->getErrors();
                    if (!empty($erros)) {
                        foreach ($erros as $campo => $mensagens) {
                            foreach ($mensagens as $mensagem) {
                                $this->Flash->error("Erro em {$campo}: {$mensagem}");
                            }
                        }
                    } else {
                        $this->Flash->error("Erro o primeiro avaliador. A RAIC não foi atualizada");
                    }
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

        $this->set(compact('erro', 'avaliadores', 'raic'));
    }

    //ok 2025
    public function historico($id, $original, $atual, $just, bool $throw = true): bool
    {
        $this->RaicHistoricos = TableRegistry::getTableLocator()->get('RaicHistoricos');
        $novo = $this->RaicHistoricos->newEmptyEntity(); 
        $novo->raic_id = $id;
        $novo->usuario_id          = $this->request->getAttribute('identity')->id;
        $novo->alteracao = $original;
        $novo->justificativa = $just;
    
        $nv = $this->RaicHistoricos->save($novo);
        if(!($nv)) {
            
            $this->Flash->error('Houve um erro na gravação. Tente novamente');
            return false;

        }
        return true;
    
    }
    
    //ok 2025
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
            'Editais',
            'Unidades',
            'RaicHistoricos'=>['Usuarios']
        ],
        ]);

        if($this->request->getAttribute('identity')['jedi']!=null){
            $jediArray = explode(',', $this->request->getAttribute('identity')['jedi']);
        }else{
            $jediArray=[];
        }

        // Verifica se tem permissão
        if(!(
            ($this->request->getAttribute('identity')['yoda']) || 
            ((in_array($raic->unidade_id, $jediArray))) ||
            (in_array($this->request->getAttribute('identity')['id'], [$raic->orientador, $raic->usuario_id]))
        )){
            $this->Flash->error('Sem permissão de visualização desta Raic');
            return $this->redirect(['controller' => 'Index', 'action' => 'dashboard']);
        }

        if($raic->tipo_bolsa=='R'){
            $anexo_sub = TableRegistry::getTableLocator()->get('ProjetoAnexos')->find()->contain(['TipoAnexos', 'Usuarios'])
            ->where([
                'projeto_bolsista_id' => $raic->projeto_bolsista_id,
                '(deleted IS NULL)'
            ]);
        }else{
            $anexo_sub = TableRegistry::getTableLocator()->get('ProjetoAnexos')->find()->contain(['TipoAnexos', 'Usuarios'])
            ->where([
                'raic_id' => $raic->id,
                '(deleted IS NULL)',
                '(tipo_anexo_id IN (13, 20))'
            ]);
        }

        if($raic->tipo_bolsa=='Z'){
            $tipo='Z';
        }else{
            $tipo='V';
        }

        $lista = $this->Aval->find('all')->contain([
            'Avaliadors'=>'Usuarios'
            
        ])
        ->where([
            'AvaliadorBolsistas.bolsista' => $id, 
            'AvaliadorBolsistas.tipo' => $tipo 

        ]);
        $programa = $this->programa;


        $this->set(compact('raic', 'lista', 'programa', 'anexo_sub'));
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

    /*
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

        
        // Verifica se ja foi respondido
        $this->Gabaritos = TableRegistry::getTableLocator()->get('Gabaritos');
        $feito = $this->Gabaritos->find('all')
        ->where(['Gabaritos.projeto_bolsista_id'=>$renova->id])->first();

        if($feito!= null){
            $this->Flash->error('Você já respondeu ao questionário');
            return $this->redirect(['controller'=>'Raics','action'=>'verquestionario', $renova->id]);
        }
        


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
    */
    

    /*
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
    */

    /*
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
    */

    /*
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
    */

    //ok 2025
    public function liberacertificado($id = null)
    {
        
        //busca raic para esta ação
            $raic = $this->Raics->get($id);
        //

        // verifica se o logado é adm ou jedi/yoda
            //permissão: testa jedi <> 1/8088
            if($this->request->getAttribute('identity')['jedi']!=null){
                $jediArray = explode(',', $this->request->getAttribute('identity')['jedi']);
            }else{
                $jediArray=[];

            }


            if(
                (!($this->request->getAttribute('identity')['yoda'])) && 
                (!(in_array($raic->unidade_id, $jediArray)))
            ){

                $this->Flash->error('Somente a Coordenação da Unidade e a Gestão de Fomento pode agendar a RAic');
                return $this->redirect(['controller'=>'Index', 'action' => 'dashboard']);
            }
        //
        
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
            $this->historico($raic->id, 'Certificado Liberado', 'Certificado Liberado', 'Usuário responável registrou a presença do aluno');

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
