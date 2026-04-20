<style>
.detalhe-projeto strong {
    width: 120px;
}
</style>
<section>
    <h3>AVALIAÇÃO RAIC <?=$this->Html->link(' Voltar ',['controller'=>'Usuarios', 'action'=>'minhas_avaliacoes'],['class'=>'btn btn-danger pull-right','style'=>'margin-top:-10px']);?></h3>
    
    <div class="row">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="titulo-legenda">
                                DADOS DO SUB PROJETO / BOLSISTA
                            </div>
                            <h3 class="mb-0">
                                <p> Sub Projeto: <?=$raic->projeto_bolsista->sp_titulo?> </p>
                                <small><p> Orientador: <?=$raic->projeto_bolsista->orientadore->nome?> </p></small>
                                <small><p> Coorientador: <?=($raic->projeto_bolsista->coorientador==null?'Não Indicado':$raic->projeto_bolsista->coorientadore->nome)?> </p></small>

                            </h3>

                            <div class="p-2 rounded mt-2" style="background-color:#eee;font-style:italic">
                                <div class="datas">
                                    <div id="id-bolsista">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4><a href="#projeto-bolsista" 
                                                data-toggle="collapse" 
                                                data-parent=".datas">
                                                <?=($raic->projeto_bolsista->sp_resumo==''?
                                                '<span class="btn btn-sm btn-danger"><i class="fa fa-exclamation-triangle"></i> Resumo não informado </span>':
                                                '<span class="btn btn-sm btn-warning"><i class="fa fa-exclamation-triangle"></i> clique aqui para abrir o resumo do SUB PROJETO </span>');?></a></h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="projeto-bolsista" class="collapse">
                                        <span class="badge">RESUMO</span>
                                        <?=$raic->projeto_bolsista->sp_resumo;?>
                                        
                                    </div>
                                </div>
                            </div>
                            <p>Sub Projeto completo <?=$this->Html->link($raic->projeto_bolsista->anexo, ['controller' => 'uploads', 'action' => 'anexos', $raic->projeto_bolsista->anexo], ['target' => '_blank'])?></p>
                            <?php if(($raic->projeto_bolsista->relatorio)!=null): ?> Relatório Parcial: 
                                <small>    
                                    <?=$this->Html->link($raic->projeto_bolsista->relatorio, 
                                    ['controller' => 'uploads', 'action' => 'documentos', 
                                    $raic->projeto_bolsista->relatorio], ['target' => '_blank'])?>                       
                                </small>
                            <?php endif; ?>
                            <?php if(($raic->projeto_bolsista->relatorio)==null): ?> Relatório Parcial: 
                                <small> <strong>  Não Informado</strong>  </small>
                            <?php endif; ?>
                            <p></p>

                            <?php if(($raic->projeto_bolsista->egresso)!=null): ?> Relatório do Egresso: 
                                <small>    
                                    <?=$this->Html->link($raic->projeto_bolsista->egresso, 
                                    ['controller' => 'uploads', 'action' => 'documentos', 
                                    $raic->projeto_bolsista->egresso], 
                                    ['target' => '_blank'])?>                       
                                </small>
                            <?php endif; ?>
                            <?php if(($raic->projeto_bolsista->egresso)==null): ?> Relatório do Egresso: 
                                <small> <strong>Não se aplica</strong>  </small>
                            <?php endif; ?>
                            <p></p>


                            <?php if(($raic->projeto_bolsista->matricula)!=null): ?> Matrícula: 
                                <small>    
                                    <?=$this->Html->link($raic->projeto_bolsista->matricula, 
                                    ['controller' => 'uploads', 'action' => 'documentos', 
                                    $raic->projeto_bolsista->matricula], ['target' => '_blank'])?>                       
                                </small>
                            <?php endif; ?>
                            <?php if(($raic->projeto_bolsista->matricula)==null): ?> Matrícula: 
                                <small> <strong>  Não Informado</strong>  </small>
                            <?php endif; ?>
                            <p></p>


                            <?php if(($raic->projeto_bolsista->historico)!=null): ?> Histórico: 
                                <small>    
                                    <?=$this->Html->link($raic->projeto_bolsista->historico, 
                                    ['controller' => 'uploads', 'action' => 'documentos', 
                                    $raic->projeto_bolsista->historico], 
                                    ['target' => '_blank'])?>                       
                                </small>
                            <?php endif; ?>
                            <?php if(($raic->projeto_bolsista->historico)==null): ?> Histórico: 
                                <small> <strong>Não Informado</strong>  </small>
                            <?php endif; ?>
                            <p></p>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="titulo-legenda">
                                DADOS DO PROJETO ORIENTADOR
                            </div>
                            <h3 class="mb-0">
                                <p> Projeto: <?=$raic->projeto_bolsista->projeto->titulo?> </p>
                            
                            </h3>

                            <div class="p-2 rounded mt-2" style="background-color:#eee;font-style:italic">
                                <div class="datas">
                                    <div id="id-orientador">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4><a href="#projeto-orientador" 
                                                data-toggle="collapse" 
                                                data-parent=".datas">
                                                <?=($raic->projeto_bolsista->projeto->resumo==''?
                                                '<span class="btn btn-sm btn-danger"><i class="fa fa-exclamation-triangle"></i> Resumo não informado </span>':
                                                '<span class="btn btn-sm btn-warning"><i class="fa fa-exclamation-triangle"></i> clique aqui para abrir o resumo do PROJETO </span>');?></a></h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="projeto-orientador" class="collapse">
                                        <span class="badge">RESUMO</span>
                                        <?=$raic->projeto_bolsista->projeto->resumo;?>
                                        
                                    </div>
                                </div>
                            </div>
                            <p>Projeto completo <?=$this->Html->link($raic->projeto_bolsista->projeto->anexos, ['controller' => 'uploads', 'action' => 'anexos', $raic->projeto_bolsista->projeto->anexos], ['target' => '_blank'])?></p>
                            <div class="row">
                                <div class="col-md-12">
                                    <?php if(($raic->projeto_bolsista->projeto->parecer_comite)!=null): ?> Parecer Comite: 
                                        <small>    
                                            <?=$this->Html->link($raic->projeto_bolsista->projeto->parecer_comite, ['controller' => 'uploads', 'action' => 'parecer', $raic->projeto_bolsista->projeto->parecer_comite], ['target' => '_blank'])?>                       
                                        </small>
                                    <?php endif; ?>
                                    <?php if((($raic->projeto_bolsista->projeto->parecer_comite)==null)||(($raic->projeto_bolsista->projeto->parecer_comite)=='')): ?> Parecer Comite: 
                                        <small> <strong>  Informado que não se aplica</strong>  </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <?php if(($raic->projeto_bolsista->projeto->autorizacao_sisgen)!=null): ?> Autorização Sisgen: 
                                        <small>    
                                            <?=$this->Html->link($raic->projeto_bolsista->projeto->autorizacao_sisgen, 
                                            ['controller' => 'uploads', 'action' => 'autorizacao', $raic->projeto_bolsista->projeto->autorizacao_sisgen], 
                                            ['target' => '_blank'])?>                       
                                        </small>
                                    <?php endif; ?>
                                    <?php if(($raic->projeto_bolsista->projeto->autorizacao_sisgen)==null): ?> Autorização Sisgen: 
                                        <small> <strong>Informado que não se aplica</strong>  </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-12 datas" style="border:1px solid #999">
                        <?php
                            if(($raic->situacao=='F') && $this->request->getAttribute('identity')['yoda']){
                        ?>
                            <h2>Bolsista já avaliado<br /></h2>
                            <table class="table">
                            
                        <?php
                        }
                        ?>
                        <?php if($raic->situacao!='F'){?>
                            <h2>FORMULÁRIO DE AVALIAÇÃO</h2>
                            <?=$this->Form->create($raic);?>
                            <?=$this->Form->control('avaliador_bolsista_id', ['type'=>'hidden', 'value'=>$raic->id]);?>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Critério de Avaliação</th>
                                        <th>Nota máxima</th>
                                        <th>Parâmetros</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach($questoes as $q){
                                    ?>
                                    <tr>
                                        <td><?=$q->questao;?></td>
                                        <td><?=$q->limite_max;?></td>
                                        <td><?=$q->prametros;?></td>
                                        
                                    </tr>
                                    <?php
                                    }
                                    ?>
                                    
                                </tbody>
                            </table>

                        <?php }?>
                        
                    </div>
                </div>    
            </div>
        </div>
    </div>
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
</section>