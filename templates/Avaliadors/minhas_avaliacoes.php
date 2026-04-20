    

<div class="container-fluid p-1 pt-1">
    <h2 class="mt-2">Minhas Avaliações</h2>
    
    <div class="col-12">
        <div class="card card-primary card-outline card-outline-tabs"> 
            <div class="card-body" style="background-color: #858789;">
                <div class="tab-content" id="custom-tabs-four-tabContent">
                    <div class="tab-pane fade show active" id="custom-tabs-four-home" role="tabpanel" aria-labelledby="custom-tabs-four-home-tab">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h3>Avaliações de Bolsa Nova </h3>
                                    <?php if($avaliacoes_novas->all()->isEmpty()){?>
                                        
                                        <h5 style="color: #b30000; font-weight: bold; background-color: #ffe6e6; padding: 10px; border-radius: 5px;">
                                            Você não foi selecionado para avaliar bolsas novas este ano.
                                        </h5>
                                    <?php } else{?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped align-middle">
                                                <thead class="table-secondary">
                                                    <tr>
                                                        <th style="text-align:left" scope="col">Tipo</th>
                                                        <th style="text-align:left" scope="col">Edital</th>
                                                        <th style="text-align:left" scope="col">Unidade</th>
                                                        <th style="text-align:left" scope="col">Bolsista</th>
                                                        <th style="text-align:left" scope="col">Orientador</th>
                                                        <th style="text-align:left" scope="col">Situação</th>
                                                        <th scope="col" class="actions">Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($avaliacoes_novas as $a){
                                                        $nome = explode(" ", $a->projeto_bolsista->usuario->nome);
                                                        $nome_o = explode(" ", $a->projeto_bolsista->orientadore->nome);
                                                    ?>
                                                        <tr>
                                                            <td><?=$tipo[$a->tipo]?></td>
                                                            <td style="text-align:left"><?=($a->projeto_bolsista->id.' '.$a->projeto_bolsista->editai->nome);?></td>
                                                            <td style="text-align:left"><?=($a->projeto_bolsista->orientadore->unidade->sigla);?></td>
                                                            <td style="text-align:left"><?=($nome[0] . ' ' . end($nome));?></td>
                                                            <td style="text-align:left"><?=($nome_o[0] . ' ' . end($nome_o));?></td>
                                                            <td style="text-align:left"><?=($a->situacao=='F'?'<b style="color:#090">AVALIADO<b>':'<b style="color:#F90">AGUARDANDO</b>');?></td>
                                                            <td class="actions">
                                                            <?php
                                                            
                                                            if($a->situacao!='F')
                                                            {
                                                                print $this->Html->link('<i class="fa fa-check-circle"></i>',['controller'=>'avaliadors','action'=>'avaliar',$a->id],['class'=>'btn btn-xs btn-primary','escape'=>false]);
                                                            }
                                                            ?>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php } ?>                                             
                                </div>
                                <br>
                              
                            </div>
                            
                        </div>    
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h3>Avaliações Renovações / RAIC </h3>
                                    <?php if($avaliacoes_raic->all()->isEmpty()){?>
                                        
                                        <h5 style="color: #b30000; font-weight: bold; background-color: #ffe6e6; padding: 10px; border-radius: 5px;">
                                            Você não foi selecionado para avaliar Raics este ano.
                                        </h5>
                                    <?php } else{?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped align-middle">
                                                <thead class="table-secondary">
                                                    <tr>
                                                        <th style="text-align:left" scope="col">Tipo</th>
                                                        <th style="text-align:left" scope="col">Edital</th>
                                                        <th style="text-align:left" scope="col">Unidade</th>
                                                        <th style="text-align:left" scope="col">Bolsista</th>
                                                        <th style="text-align:left" scope="col">Orientador</th>
                                                        <th style="text-align:left" scope="col">Situação</th>
                                                        <th scope="col" class="actions">Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($avaliacoes_raic as $a){
                                                        if(!($a->raic==null)){
                                                            $nome = explode(" ", $a->raic->usuario->nome);
                                                            $nome_o = explode(" ", $a->raic->orientadore->nome);
                                                        }
                                                     ?>
                                                        <tr>
                                                            <td><?=($tipo[$a->tipo]);?></td>
                                                            <td style="text-align:left"><?=($a->bolsista.' '.($a->raic->editai_id==null?'x':$a->raic->editai->nome));?></td>
                                                            <td style="text-align:left"><?=($a->raic->orientadore->unidade->sigla);?></td>
                                                            <td style="text-align:left"><?=($nome[0] . ' ' . end($nome));?></td>
                                                            <td style="text-align:left"><?=($nome_o[0] . ' ' . end($nome_o));?></td>

                                                            <td style="text-align:left"><?=($a->situacao=='F'?'<b style="color:#090">AVALIADO<b>':'<b style="color:#F90">NÃO AVALIADO</b>');?></td>

                                                            

                                                            <td class="actions">
                                                                <?php if((!in_array($a->situacao, ['F']))): print $this->Html->link('<i class="fas fa-check-circle"></i>', ['controller' => 'avaliadors', 'action' => 'avaliarraic', $a->id], ['target' => '_blank', 'class' => 'btn btn-sm btn-success', 'title' => 'Detalhes da RAIC', 'escape' => false]); endif; 
                                                                ?>

                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php } ?>
                                                                   
                                </div>
                                <br>
                              
                            </div>
                            
                        </div>    
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h3>Avaliações PDJ Novas </h3>
                                    <?php if($avaliacoes_pdj_nova->all()->isEmpty()){?>
                                        
                                        <h5 style="color: #b30000; font-weight: bold; background-color: #ffe6e6; padding: 10px; border-radius: 5px;">
                                            Você não foi selecionado para avaliar inscrições PDJ novas este ano.
                                        </h5>
                                    <?php } else{?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped align-middle">
                                                <thead class="table-secondary">
                                                    <tr>
                                                        <th style="text-align:left" scope="col">Tipo</th>
                                                        <th style="text-align:left" scope="col">Edital</th>
                                                        <th style="text-align:left" scope="col">Unidade</th>
                                                        <th style="text-align:left" scope="col">Bolsista</th>
                                                        <th style="text-align:left" scope="col">Orientador</th>
                                                        <th style="text-align:left" scope="col">Situação</th>
                                                        <th scope="col" class="actions">Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($avaliacoes_pdj_nova as $a){
                                                        $nome = explode(" ", $a->pdj_inscrico->candidato->nome);
                                                        $nome_o = explode(" ", $a->pdj_inscrico->usuario->nome);
                                                     ?>
                                                        <tr>
                                                            <td><?=($tipo[$a->tipo]);?></td>
                                                            <td style="text-align:left"><?=($a->bolsista.' '.($a->pdj_inscrico->edital_id==null?'x':$a->pdj_inscrico->editai->nome));?></td>
                                                            <td style="text-align:left"><?=($a->pdj_inscrico->usuario->unidade->sigla);?></td>
                                                            <td style="text-align:left"><?=($nome[0] . ' ' . end($nome));?></td>
                                                            <td style="text-align:left"><?=($nome_o[0] . ' ' . end($nome_o));?></td>

                                                            <td style="text-align:left"><?=($a->situacao=='F'?'<b style="color:#090">AVALIADO<b>':'<b style="color:#F90">NÃO AVALIADO</b>');?></td>

                                                            

                                                            <td class="actions">
                                                                <?php if((!in_array($a->situacao, ['F']))): print $this->Html->link('<i class="fas fa-check-circle"></i>', ['controller' => 'avaliadors', 'action' => 'avaliarpdj', $a->id], ['target' => '_blank', 'class' => 'btn btn-sm btn-success', 'title' => 'Detalhes da RAIC', 'escape' => false]); endif; 
                                                                ?>

                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php } ?>
                                                                   
                                </div>
                                <br>
                              
                            </div>
                            
                        </div>    

                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h3>Avaliações PDJ Workshop </h3>
                                    <?php if($avaliacoes_pdj_work->all()->isEmpty()){?>
                                        
                                        <h5 style="color: #b30000; font-weight: bold; background-color: #ffe6e6; padding: 10px; border-radius: 5px;">
                                            Você não foi selecionado para avaliar inscrições PDJ novas este ano.
                                        </h5>
                                    <?php } else{?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped align-middle">
                                                <thead class="table-secondary">
                                                    <tr>
                                                        <th style="text-align:left" scope="col">Tipo</th>
                                                        <th style="text-align:left" scope="col">Edital</th>
                                                        <th style="text-align:left" scope="col">Unidade</th>
                                                        <th style="text-align:left" scope="col">Bolsista</th>
                                                        <th style="text-align:left" scope="col">Orientador</th>
                                                        <th style="text-align:left" scope="col">Situação</th>
                                                        <th scope="col" class="actions">Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($avaliacoes_pdj_work as $a){
                                                        $nome = explode(" ", $a->workshop->pdj_inscrico->candidato->nome);
                                                        $nome_o = explode(" ", $a->workshop->pdj_inscrico->usuario->nome);
                                                     ?>
                                                        <tr>
                                                            <td><?=($tipo[$a->tipo]);?></td>
                                                            <td style="text-align:left"><?=($a->workshop->pdj_inscricoe_id.' / '.$a->bolsista.' '.($a->workshop->editai_id==null?'x':$a->workshop->editai->nome));?></td>
                                                            <td style="text-align:left"><?=($a->workshop->pdj_inscrico->usuario->unidade->sigla);?></td>
                                                            <td style="text-align:left"><?=($nome[0] . ' ' . end($nome));?></td>
                                                            <td style="text-align:left"><?=($nome_o[0] . ' ' . end($nome_o));?></td>

                                                            <td style="text-align:left"><?=($a->situacao=='F'?'<b style="color:#090">AVALIADO<b>':'<b style="color:#F90">NÃO AVALIADO</b>');?></td>

                                                            

                                                            <td class="actions">
                                                                <?php if((!in_array($a->situacao, ['F']))): print $this->Html->link('<i class="fas fa-check-circle"></i>', ['controller' => 'avaliadors', 'action' => 'avaliarpdj', $a->id], ['target' => '_blank', 'class' => 'btn btn-sm btn-success', 'title' => 'Detalhes da RAIC', 'escape' => false]); endif; 
                                                                ?>

                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php } ?>
                                                                   
                                </div>
                                <br>
                              
                            </div>
                            
                        </div>    

                        
                          
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h3>Certificados Iniciação Científica</h3>
                                    <?php if($editais->all()->isEmpty()){?>
                                        
                                        <h5 style="color: #b30000; font-weight: bold; background-color: #ffe6e6; padding: 10px; border-radius: 5px;">
                                            Não existem certificados de avaliação
                                        </h5>
                                    <?php } else{?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped align-middle">
                                                <thead class="table-secondary">
                                                    <tr>
                                                        <th style="text-align:left" scope="col">Edital</th>
                                                        <th scope="col" class="actions">Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($editais as $e){?>
                                                        

                                                        <tr>
                                                            <td style="text-align:left"><?=($e->editai->nome);?></td>
                                                            <td class="actions">
                                                            <?php
                                                            if($e->editai_id!=null)
                                                            {
                                                                print $this->Html->link('<i class="fas fa-file-alt"></i> Certificado', ['controller'=>'certificados','action'=>'ver',$this->request->getAttribute('identity')['id'],'J',$e->editai_id],['class'=>'btn btn-xs btn-info', 'target'=>'_blank','escape'=>false]);                
                                                            }else{
                                                            }
                                                            
                                                            ?>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php } ?>
                                                                   
                                </div>
                                <br>
                              
                            </div>
                            
                        </div>    
                        
                    </div>
                    
                   
                </div>
            </div>
        </div>      
    </div>
</div>

    






