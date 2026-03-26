<?php
namespace App\Controller;
use App\Controller\AppController;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;

class CertificadosController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();

       
        $this->viewBuilder()->setLayout('admin');
    }
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        //$this->Auth->allow(['validar']);
        $this->Authentication->addUnauthenticatedActions([
            'validar'
        ]);
    }
   

    public function ver($usuario=null, $tipo='R',$ano=null)
    {
        if($ano==null){
            $ano = date('Y');
        }
        if($usuario==null)
        {
            $this->Flash->error('A solicitação não contém todas as informações necessárias para gerar!');
            return $this->redirect($this->referer());
        }
        //se for raic
            if($tipo=='R'){
                $voluntario = TableRegistry::getTableLocator()->get('Raics')->get($usuario);
            
                //tratativa de notas se for raic de aluno renovação
                    if($voluntario->tipo_bolsa == 'R'){
                        //o que acontecia antes da 'presença' em 2023
                            if($voluntario->editai_id<17){
                                $pode = TableRegistry::getTableLocator()->get('AvaliadorBolsistas')
                                    ->find('all')
                                    ->where(['bolsista'=>$voluntario->id])
                                    ->where(['tipo'=>'V'])
                                    ->where(['deleted'=>0])
                                    ->where(['situacao'=>'F'])
                                    ->where(['coordenador'=>1])

                                    ->count()
                                ;
                                //para anterior a 2023, o lançamento da nora e so pelo coordenador e nao depende de presença
                                if($pode==0){
                                    $this->Flash->error('O coordenador da banca não lançou a nota.');
                                    return $this->redirect($this->referer());
                                }


                            }
                        //
                        
                        // tratativa 2023
                            if($voluntario->editai_id>16){

                                $pode = TableRegistry::getTableLocator()->get('AvaliadorBolsistas')
                                    ->find('all')
                                    ->where(['bolsista'=>$voluntario->id])
                                    ->where(['tipo'=>'V'])
                                    ->where(['deleted'=>0])
                                    ->where(['situacao'=>'E'])
                                    ->count()
                                ;

                                if($pode>0){
                                    $this->Flash->error('Faltam notas a serem lançadas.');
                                    return $this->redirect($this->referer());
                                }

                                if($pode==0){
                                    if($voluntario->presenca<>'S'){
                                    $this->Flash->error('O seu certificado nao foi liberado. Solicite ao coordenador da sua unidade');
                                    return $this->redirect($this->referer());
                                    }
                                }
                            }
                        //
                    }
                //
                // se for raic voluntario
                    if($voluntario->tipo_bolsa == 'V'){
                        
                        $pode = 1;
                    }
                //
                // se for voluntario a partir de 2025
                // tratativa 2023
                    if($voluntario->tipo_bolsa == 'Z'){


                        $pode = TableRegistry::getTableLocator()->get('AvaliadorBolsistas')
                            ->find('all')
                            ->where(['bolsista'=>$voluntario->id])
                            ->where(['tipo'=>'Z'])
                            ->where(['deleted'=>0])
                            ->where(['situacao'=>'E'])
                            ->count()
                        ;

                        if($pode>0){
                            $this->Flash->error('Raic de outras agencias de fomento a partir de 2025: Faltam notas a serem lançadas.');
                            return $this->redirect($this->referer());
                        }

                        if($pode==0){
                            if($voluntario->presenca<>'S'){
                            $this->Flash->error('Raic de outras agencias de fomento a partir de 2025: O seu certificado nao foi liberado. Solicite ao coordenador da sua unidade');
                            return $this->redirect($this->referer());
                            }
                        }
                       
                    }
                //

                //

                /*
                if($pode==0){

                    $pode = TableRegistry::getTableLocator()->get('Raics')->find('all')->where(['id'=>$usuario, 'tipo_bolsa'=>['R','V']])->count();
                    if($pode==0){
                        $this->Flash->error('Você não compareceu à RAIC ou não teve sua avaliação lançada, assim não podemos emitir seu certificado.');
                        return $this->redirect($this->referer());
                    }
                }
                */
            } 
        //
        if($tipo=='J' && $this->request->getAttribute('identity')['id']!=860){
            $avaliou = TableRegistry::getTableLocator()->get('AvaliadorBolsistas')->find('all')
                                    ->join(['table'=>'avaliadors',
                                    'alias'=>'AB','type'=>'INNER', 
                                    'conditions'=>'AB.id = AvaliadorBolsistas.avaliador_id'])
                                    ->where(['AB.usuario_id'=>$usuario, 
                                            'AvaliadorBolsistas.situacao'=>'F', 
                                            'AvaliadorBolsistas.editai_id'=>$ano,
                                            'AvaliadorBolsistas.deleted'=>0])->count()
            ;

            $nao_avaliou = TableRegistry::getTableLocator()->get('AvaliadorBolsistas')->find('all')
                                    ->join(['table'=>'avaliadors',
                                    'alias'=>'AB','type'=>'INNER', 
                                    'conditions'=>'AB.id = AvaliadorBolsistas.avaliador_id'])
                                    ->where(['AB.usuario_id'=>$usuario, 
                                    'AvaliadorBolsistas.situacao'=>'E', 
                                    'AvaliadorBolsistas.editai_id'=>$ano,
                                    'AvaliadorBolsistas.deleted'=>0])->count()
            ;

            if($avaliou && !$nao_avaliou){
                $pode=1;
                $edital = TableRegistry::getTableLocator()->get('Editais')->find('all')->where(['id'=>$ano])->first();

            }else{
                $pode=0;

            }

            
            if($pode==0){
                $this->Flash->error('Você não foi avaliador neste edital ou ainda tem avaliações para concluir.');
                return $this->redirect($this->referer());
            }
        }

        if($tipo=='C'){
            
            //tras o modulo indicado
            $modulo = TableRegistry::getTableLocator()->get('CursosBolsistas')
            ->find('all')
                        ->where(['id'=>$usuario])->first();

            //verifica se o logado é o aluno se nao for yoda
            if($this->request->getAttribute('identity')['yoda']==0){
                if($this->request->getAttribute('identity')['id']!=$modulo->usuario_id){
                    $this->Flash->error('A inscrição não corresponde ao usuário logado');
                    return $this->redirect($this->referer());
                }

            }

            //teste das notas
            //tras as aulas do modulo indicado
            $aulas_modulo = TableRegistry::getTableLocator()->get('AulaBolsistas')->find('all')
                        ->where(['cursos_bolsista_id'=>$usuario]);
            $numero = TableRegistry::getTableLocator()->get('Aulas')->find('all')
                        ->where(['cursos_modulo_id'=>$modulo->cursos_modulo_id])->count();

            //somatorio das notas
            if($aulas_modulo!=null){
                $nota_modulo=0;
                foreach($aulas_modulo as $am){
                    $nota_modulo=$nota_modulo+$am->nota;
                }

            }

            if($numero>0){
                $media=$nota_modulo/$numero;

            }else{
                $this->Flash->error('Não existem aulas para este módulo. aguarde o lançamento');
                    return $this->redirect($this->referer());
            }
            //testa a nota
            //dd($media);
            if($media<7){
                $this->Flash->error('Você não concluiu o curso/módulo.');
                return $this->redirect($this->referer());
            }
            
        } 


        

        $certificado = TableRegistry::getTableLocator()->get('Certificados')->find('all')
            ->where(['Certificados.tipo'=>$tipo, 'bolsista_id'=>$usuario])
            ->first();
            //dd($certificado);

        if($certificado==null)
        {            
            $certificado = $this->emitir($usuario, $tipo, $ano);

        }
        switch($tipo){
            case 'R':

                $user = TableRegistry::getTableLocator()->get('Usuarios')->find('all')->where(['id IN (SELECT usuario_id FROM raics WHERE id = '.$usuario.')'])->first();
                $raic = TableRegistry::getTableLocator()->get('Raics')->find('all')->contain(['Orientadores'=>'Unidades'])->where(['Raics.id'=>$usuario])->first();
                if($raic->tipo_bolsa != 'V'){
                    $texto[0] = 'Participou da '.($raic->data_apresentacao==null?'N/A':($raic->data_apresentacao->i18nFormat('YYYY')-1992)).'ª RAIC-PIBIC/PIBITI da FIOCRUZ, unidade '.$raic->orientadore->unidade->sigla.'.';
                } else {
                    $texto[0] = 'Participou da '.($raic->data_apresentacao==null?'N/A':($raic->data_apresentacao->i18nFormat('YYYY')-1992)).'ª RAIC-PIBIC/PIBITI da FIOCRUZ como voluntário, unidade '.$raic->orientadore->unidade->sigla.'.';
                }
                //$texto[1] = '<b>Com o projeto:</b> '.ucfirst(mb_strtolower($raic->titulo));
                $texto[1] = '<b>Com o projeto:</b> '.($raic->titulo);

                $dia[0] = ($raic->data_apresentacao==null?'Não Informado': $raic->data_apresentacao->i18nFormat("dd/MM/Y"));
                $horas = 3;
                break;
            case 'A':

                $cursou = TableRegistry::getTableLocator()->get('AulaBolsistas')->find('all')->contain(['Aulas','Usuarios'])->where(['AulaBolsistas.id'=>$usuario])->first();
                $user = $cursou->usuario;
                $texto[0] = 'Concluiu o curso:';
                $texto[1] = $cursou->aula->nome;
                //$dia[0] = $cursou->created->format('d/m/Y');
                $dia[0] = $cursou->modified->format('d/m/Y');
                //$dia[1] = $cursou->modified->format('d/m/Y');
                $horas = $cursou->aula->horas;
                break;
            case 'C':
                $modulo = TableRegistry::getTableLocator()->get('CursosBolsistas')
                ->find('all')->contain(['CursosModulos', 'CursosOferecidos'])
                            ->where(['CursosBolsistas.id'=>$usuario])->first();
                
                $info_modulo = TableRegistry::getTableLocator()->get('CursosModulos')
                ->find('all')->where(['CursosModulos.id'=>$modulo->cursos_modulo_id])->first();

                $user = TableRegistry::getTableLocator()->get('Usuarios')
                ->find('all')->where(['id'=>$modulo->usuario_id])->first();
               // dd($modulo->created);
                $texto[0] = 'Concluiu o módulo:';
                $texto[1] = $modulo->cursos_modulo->nome.' do curso '.$modulo->cursos_oferecido->nome;
                $dia[0] = $modulo->created->i18nFormat('MM').'/'.$modulo->created->i18nFormat('YYYY');
                $horas = $info_modulo->horas;
                break;
            case 'J':

                $av_bol = TableRegistry::getTableLocator()->get('AvaliadorBolsistas')
                ->find('all')
                ->contain(['Avaliadors'=>'Usuarios'])
                ->where(['Avaliadors.usuario_id'=>$usuario])
                ->where(['AvaliadorBolsistas.editai_id'=>$ano])
                ->first()
                ;
                //dd($av_bol);


                $avaliador = TableRegistry::getTableLocator()->get('Avaliadors')->find('all')
                ->contain(['Usuarios','GrandesAreas','Areas','SubAreas'])
                ->where(['Avaliadors.id'=>$av_bol->avaliador_id])->first();
                $user = $avaliador->usuario;
                //dd($avaliador->id);
                $texto[0] = 'Participou da avaliação dos bolsistas de '. ($edital->nome==null?'':$edital->nome).' da FIOCRUZ em '.$edital->inicio_inscricao->i18nFormat('YYYY');
                $texto[1] = 'Avaliando na área (CNPq) '.$avaliador->area->nome;
                if($edital->inicio_inscricao->i18nFormat('YYYY') < 2025){
                    $dia[0] = '19-05-'.$edital->inicio_inscricao->i18nFormat('YYYY');
                    $dia[1] = '31-07-'.$edital->inicio_inscricao->i18nFormat('YYYY');
                    $horas = 120;

                } else{
                    $dia=[];
                    $horas = '';

                }

        }
        $codigo = $certificado->codigo;
        $this->viewBuilder()->setLayout('ajax');
        $this->set(compact('certificado','texto','codigo','user','dia','horas', 'tipo'));
    }

    private function emitir($usuario, $tipo, $ano)
    {
        $this->Certificados = TableRegistry::getTableLocator()->get('Certificados');
        $novo_certificado = $this->Certificados->newEmptyEntity();
        $novo_certificado->bolsista_id = $usuario;
        $novo_certificado->tipo = $tipo;
        $novo_certificado->ano = $ano;

        $novo_certificado->codigo = uniqid($usuario);
        $certificado = $this->Certificados->save($novo_certificado);
        return $certificado;
    }

    public function validar($codigo=null)
    {
        if($this->request->is('post'))
        {
            $codigo = $this->request->getData('codigo');
        }
        if($codigo==null){
            $this->Flash->error('Não há o que validar!');
            return $this->redirect(['controller'=>'/','action'=>'/']);
        }
        $certificado = $this->Certificados->find('all')->where(['codigo'=>$codigo])->first();
        $verificado = ($certificado==null?false:true);
        if($verificado){
            switch($certificado->tipo)
            {
                case 'R':
                    $user = TableRegistry::getTableLocator()->get('Usuarios')->find('all')->where(['id IN (SELECT usuario_id FROM raics WHERE id = '.$certificado->bolsista_id.')'])->first();
                    $raic = TableRegistry::getTableLocator()->get('Raics')->find('all')->contain(['Orientadores'=>'Unidades'])->where(['Raics.id'=>$certificado->bolsista_id])->first();
                    $texto[0] = 'Participou da '.(($raic->data_apresentacao->i18nFormat('YYYY'))-1992).'ª RAIC-PIBIC/PIBITI da FIOCRUZ, unidade <b>'.$raic->orientadore->unidade->nome.' ('.$raic->orientadore->unidade->sigla.')</b>, com o projeto:';
                    $texto[1] = '<i>'.mb_strtoupper($raic->titulo).'</i><br />Orientad'.($user->sexo=='M'?'o':'a').' '.($raic->orientadore->sexo=='M'?'por':'pela').' Dr'.($raic->orientadore->sexo=='M'?'º':'ª').'. '.mb_strtoupper($raic->orientadore->nome);
                    $dia[0] = $raic->data_apresentacao->i18nFormat('dd/MM/YYYY');
                    $horas = 3;

                    break;
                case 'A':
                    $cursou = TableRegistry::getTableLocator()->get('AulaBolsistas')->find('all')->contain(['Aulas','Usuarios'])->where(['AulaBolsistas.id'=>$certificado->bolsista_id])->first();
                    $user = TableRegistry::getTableLocator()->get('Usuarios')->find('all')->where(['id IN (SELECT usuario_id FROM aula_bolsistas WHERE id = '.$certificado->bolsista_id.')'])->first();
                    $texto[0] = 'Concluiu o curso:';
                    $texto[1] = $cursou->aula->nome;
                    $dia[0] = $cursou->modified->format('d/m/Y');
                    //$dia[1] = null;
                    $horas = $cursou->aula->horas;
                    break;
                case 'J':
                    $avaliador = TableRegistry::getTableLocator()->get('Avaliadors')->find('all')->contain(['Usuarios','GrandesAreas','Areas','SubAreas'])->where(['Avaliadors.usuario_id'=>$certificado->bolsista_id])->first();
                    $user = $avaliador->usuario;
                    //dd($avaliador);
                    $texto[0] = 'participou do processo de avaliação dos bolsistas nos Programas PIBIC/PIBITI da FIOCRUZ em '.$avaliador->ano_aceite;
                    $texto[1] = ', <b>AVALIANDO PROJETOS</b> na área (CNPq) <i>'.$avaliador->grandes_area->nome.' - '.$avaliador->area->nome.' - '.$avaliador->sub_area->nome.'</i>';
                    $dia[0] = $avaliador->ano_aceite.'-05-05';
                    $dia[1] = $avaliador->ano_aceite.'-07-31';
                    $horas = 120;
            }
        }
        $this->viewBuilder()->setLayout('ajax');
        $this->set(compact('verificado','certificado','texto','codigo','user','dia','horas'));
    }
}