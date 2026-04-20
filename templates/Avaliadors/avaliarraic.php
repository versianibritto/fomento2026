<style>
    .detalhe-projeto strong {
        width: 120px;
    }
</style>
<section>
    <div class="card">
        <div class="card-body">
            <h4>
                <strong class="text-default"><p> Aluno <?=$raic->tipo_bolsa=='R'?' de Renovação '.$this->Html->link(' clique para detalhes da solicitação de renovação: '.$raic->projeto_bolsista_id, ['controller' => 'Projetos', 'action' => 'detalhesubprojeto', $raic->projeto_bolsista_id], ['target'=>'_blank']):' de Outras Agências de Fomento'?> </p></strong>
            </h4> 
            <br>Inscrição RAIC: <strong> <?='# '.$raic->id?></strong>                       
            <br>Bolsista: <strong> <?=$raic->usuario->nome?></strong>
            <br>Orientador: <strong><?=$raic->orientadore->nome?></strong>
            <br>Unidade <strong><?=$raic->unidade->sigla?> </strong>
            <br><br>
            <?php if(($raic->usuario_cadastro==null) && ($raic->tipo_bolsa=='R')){?>
                <small><strong><p> Cadastrado por <?=($raic->orientadore->nome).' em '.($raic->created==null?'Não informado':$raic->created->i18nFormat('dd/MM/YYYY'))?> </p></strong></small>
            <?php }?>
            <?php if(($raic->usuario_cadastro==null) && ($raic->tipo_bolsa=='V')){?>
                <small><strong><p> Cadastro  <?=' automático em '.($raic->created==null?'Não informado':$raic->created->i18nFormat('dd/MM/YYYY'))?> </p></strong></small>
            <?php }?>
            <?php if(($raic->usuario_cadastro!=null) ){?>
                <small><strong><p> Cadastrado por:<?=($raic->cadastro->nome).' em '.($raic->created==null?'Não informado':$raic->created->i18nFormat('dd/MM/YYYY H:mm:ss'))?> </p></strong></small>
            <?php }?>

            <strong>
                <div class="text-danger">
                    <?php
                        if($raic->deleted== 1) {
                            print '<em><p> Raic DELETEDA <br>' . 
                            (
                                ($raic->projeto_bolsista_id==null?'':( (($raic->projeto_bolsista->justificativa_cancelamento!= null) && ($raic->projeto_bolsista->justificativa_cancelamento!='') )?
                                'Justificativa' . $raic->projeto_bolsista->justificativa_cancelamento:'Justificativa não informada'
                            ))
                            )
                            . '</p></em>';
                            
                        } 
                        
                    ?>
                </div>
            </strong>

        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h4><strong class="text-default">Dados da Apresentação da Raic</strong></h4>
            <br><em>Data da Apresentação <?=($raic->data_apresentacao != null?($raic->data_apresentacao->i18nFormat('dd/MM/YYYY')):'não cadastrada') ?>

            <?php if(in_array($raic->tipo_bolsa, ['R', 'Z'])) { ?>
                <br><em>Tipo de Apresentação <?=($raic->tipo_apresentacao != null?($raic->tipo_apresentacao=='O'?'Oral':'Pôster'):'não cadastrada') ?>
                <br><em>Local de Apresentação <?=($raic->local_apresentacao != null?($raic->local_apresentacao):'não cadastrado') ?>                        
                <br><em>Programa: 
                    <?= $raic->tipo_bolsa=='Z'?'Aluno de não Renovação':($programa[$raic->projeto_bolsista->programa].' / '.$raic->editai->nome)?>
                
                
            <?php } ?>
        </div>
    </div>


    <div class="card">
        <div class="card-body">
            <h3 class="text-default">Subprojeto Apresentado na Raic</h3>
            <br>
            <h4>Título: <strong> <?=$raic->titulo?></strong></h4>
            <?php if($raic->tipo_bolsa=='R'){ ?> 
                <div class="accordion" id="accordionSubProjeto">
                    <div class="accordion-item">
                        <h2 class="accordion-header" style="background-color:#c5dcfd;">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo" style="border:none;background-color:transparent;font-size:15px;padding-bottom:10px;padding-left:15px;">
                                Clique para ler o RESUMO DO SUBPROJETO
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionSubProjeto">
                            <div class="accordion-body">
                                <?=(($raic->resumo==null) || ($raic->resumo=="")?'<p class="mt-3 mb-0 text-danger">Resumo do Subprojeto não informado.</p>':$raic->resumo );?>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <p>
                    <strong><h5>Situação do Subprojeto na Renovação</h5></strong>
                    <?= 
                        ($raic->projeto_bolsista->subprojeto_renovacao=='I'?'Manteve os dados da vigência anterior na renovação': 
                        ($raic->projeto_bolsista->subprojeto_renovacao=='D'?'Cadastrado novo subprojeto na renovação':'Não verificado'))
                    ?>   
                <br>
                <?= 
                    ($raic->projeto_bolsista->referencia_raic==0?'Apresentação de um novo subprojeto na Raic': 
                    ($raic->projeto_bolsista->referencia_raic==1?'Apresentação do subprojeto anterior':'Não verificado'))
                ?>     
                <br><br>
                <h5 style="color: #b30000; font-weight: bold;">
                    Justificativa da alteração de subprojeto:
                </h5> 
                    <strong style="color: #b30000; font-weight: bold;">
                        <?= ($raic->projeto_bolsista->subprojeto_renovacao=='I'?'Não se aplica - Subprojeto Mantido': $raic->projeto_bolsista->justificativa_alteracao)?>                       
                    </strong>
                </p>
                    
            <?php } ?>
            <div class="card">
                <div class="card-body">
                    <h4 class="text-default">Anexos da Raic</h4>
                    <div class="row">
                        <?php if(!empty($relatorio)) {
                            
                            foreach($relatorio as $anexo) { ?>
                                <div class="col-md-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <p>
                                                <?=$anexo->tipo_anexo->nome;?>
                                                <small class="d-block">Incluído em <?=date("d/m/Y H:i", strtotime($anexo->created))?> por <?=$anexo->usuario->nome?></small>
                                            </p>
                                            <a href="/uploads/anexos/<?=$anexo->anexo?>" target="_blank" class="badge badge-info"><i class="fa fa-download me-2"></i> Baixar anexo </a>
                                            
                                            
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php }else {
                            print '<p class="mt-3 mb-0 text-danger">Não há anexos de relatório para esta Raic de outra Agência de Fomento.</p>';
                        }?>
                            
                    </div>
                    <?php if($raic->tipo_bolsa!='Z') { ?>

                        <div class="row">
                            <?php if($raic->projeto_bolsista->subprojeto_renovacao=='D') { ?>
                                <?php if(!empty($anexo_sub_novo)) {
                                    
                                    foreach($anexo_sub_novo as $anexo) {
                                        $destaqueNovo = ($raic->projeto_bolsista->referencia_raic == 0); ?>
                                        <div class="col-md-3">
                                            <div class="card mb-3 <?= $destaqueNovo ? 'border border-danger shadow-sm' : '' ?>">
                                                <div class="card-body">
                                                    <div style="background-color: #fcebea; color: #721c24; font-weight: 500; font-size: 0.85rem; padding: 4px; text-align: center; border-bottom: 1px solid #f5c6cb;">
                                                        Novo <?= $destaqueNovo ? '<span class="badge bg-danger ms-2">Apresentado</span>' : '' ?>
                                                    </div>
                                                    <p class="mt-2">
                                                        <?= $anexo->tipo_anexo->nome; ?>
                                                        <small class="d-block">Incluído em <?= date("d/m/Y H:i", strtotime($anexo->created)) ?> por <?= $anexo->usuario->nome ?></small>
                                                    </p>
                                                    <a href="/uploads/anexos/<?= $anexo->anexo ?>" target="_blank" class="badge bg-info text-white">
                                                        <i class="fa fa-download me-2"></i> Baixar anexo
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                <?php } else {
                                        echo '<p class="mt-3 mb-0 text-danger">Não há anexos de subprojeto novo.</p>';
                                } ?>

                                <?php if(!empty($anexo_sub_velho)) {
                                    
                                    foreach($anexo_sub_velho as $anexo) {
                                        $destaqueVelho = ($raic->projeto_bolsista->referencia_raic == 1); ?>
                                        <div class="col-md-3">
                                            <div class="card mb-3 <?= $destaqueVelho ? 'border border-danger shadow-sm' : '' ?>">
                                                <div class="card-body">
                                                    <div style="background-color: #e7f5ff; color: #0c5460; font-weight: 500; font-size: 0.85rem; padding: 4px; text-align: center; border-bottom: 1px solid #b6effb;">
                                                        Anterior <?= $destaqueVelho ? '<span class="badge bg-danger ms-2">Selecionado</span>' : '' ?>
                                                    </div>
                                                    <p class="mt-2">
                                                        <?= $anexo->tipo_anexo->nome; ?>
                                                        <small class="d-block">Incluído em <?= date("d/m/Y H:i", strtotime($anexo->created)) ?> por <?= $anexo->usuario->nome ?></small>
                                                    </p>
                                                    <a href="/uploads/anexos/<?= $anexo->anexo ?>" target="_blank" class="badge bg-info text-white">
                                                        <i class="fa fa-download me-2"></i> Baixar anexo
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                <?php } else {
                                    echo '<p class="mt-3 mb-0 text-danger">Não há anexos de subprojeto anterior.</p>';
                                } ?>

                            <?php } ?>
                            <?php if($raic->projeto_bolsista->subprojeto_renovacao=='I') { ?>
                                <?php if(!empty($anexo_sub_velho)) {
                                    
                                    foreach($anexo_sub_velho as $anexo) {
                                        $destaqueVelho = ($raic->projeto_bolsista->referencia_raic == 1); ?>
                                        <div class="col-md-3">
                                            <div class="card mb-3 <?= $destaqueVelho ? 'border border-danger shadow-sm' : '' ?>">
                                                <div class="card-body">
                                                    <div style="background-color: #e7f5ff; color: #0c5460; font-weight: 500; font-size: 0.85rem; padding: 4px; text-align: center; border-bottom: 1px solid #b6effb;">
                                                        Mantido <?= $destaqueVelho ? '<span class="badge bg-danger ms-2">Selecionado</span>' : '' ?>
                                                    </div>
                                                    <p class="mt-2">
                                                        <?= $anexo->tipo_anexo->nome; ?>
                                                        <small class="d-block">Incluído em <?= date("d/m/Y H:i", strtotime($anexo->created)) ?> por <?= $anexo->usuario->nome ?></small>
                                                    </p>
                                                    <a href="/uploads/anexos/<?= $anexo->anexo ?>" target="_blank" class="badge bg-info text-white">
                                                        <i class="fa fa-download me-2"></i> Baixar anexo
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                <?php } else {
                                    echo '<p class="mt-3 mb-0 text-danger">Não há anexos de subprojeto anterior.</p>';
                                } ?>

                            <?php } ?>
                        </div>

                        <?php
                            // Agrupa os mais recentes por tipo_anexo_id
                            $maisRecentes = [];
                            foreach ($anexos_projetos as $anexo) {
                                $tipoId = $anexo->tipo_anexo_id;
                                if (!isset($maisRecentes[$tipoId]) || $anexo->created > $maisRecentes[$tipoId]->created) {
                                    $maisRecentes[$tipoId] = $anexo;
                                }
                            }
                        ?>

                        <div class="card">
                            <div class="card-body">
                                <h4 class="text-default">Anexos do Projeto do Orientador</h4>
                                <div class="row">
                                    <?php if (!empty($anexos_projetos)): ?>
                                        
                                        <?php foreach ($anexos_projetos as $anexo): ?>
                                            <?php
                                                $destaqueAtual = ($maisRecentes[$anexo->tipo_anexo_id]->id === $anexo->id);
                                            ?>
                                            <div class="col-md-3">
                                                <div class="card mb-3 <?= $destaqueAtual ? 'border border-danger shadow-sm' : '' ?>">
                                                    <div class="card-body">
                                                        <?php if ($destaqueAtual): ?>
                                                            <div style="background-color: #fff3cd; color: #856404; font-weight: 500; font-size: 0.85rem; padding: 4px; text-align: center; border-bottom: 1px solid #ffeeba;">
                                                                Mais recente
                                                            </div>
                                                        <?php endif; ?>
                                                        <p class="mt-2">
                                                            <?= $anexo->tipo_anexo->nome; ?>
                                                            <small class="d-block">Incluído em <?= date("d/m/Y H:i", strtotime($anexo->created)) ?> por <?= $anexo->usuario->nome ?></small>
                                                        </p>
                                                        <a href="/uploads/anexos/<?= $anexo->anexo ?>" target="_blank" class="badge bg-info text-white">
                                                            <i class="fa fa-download me-2"></i> Baixar anexo
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="mt-3 mb-0 text-danger">Não há anexos de referentes ao projeto do Orientador.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                </div>
            </div>
            

        </div>
    </div>

    
    <h3>AVALIAÇÃO RAIC</h3>
    <div class="col-12">
        <div class="card card-primary card-outline card-outline-tabs"> 
            <div class="card-body" style="background-color: #858789;">
                <div class="tab-content" id="custom-tabs-four-tabContent">
                    <div class="tab-pane fade show active" id="custom-tabs-four-home" role="tabpanel" aria-labelledby="custom-tabs-four-home-tab">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                <div class="col-md-12 datas">
                                    <h2>FORMULÁRIO DE AVALIAÇÃO</h2>
                                    <h4 style="color: #b30000; font-weight: bold;">
                                        O formulário de avaliação ficará disponível a partir da data da apresentação (<?=$raic->data_apresentacao->i18nFormat("dd/MM/Y")?>)
                                    </h4> 

                                    <?php if($ab->situacao!='F' && $libera_form){?>

                                        <br><br>
                                        <?=$this->Form->create();?>
                                        <?=$this->Form->control('avaliador_bolsista_id', ['type'=>'hidden', 'value'=>$raic->id]);?>
                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-body">                                       
                                                    <table class="table">
                                                        <thead class="table-secondary">
                                                            <tr>
                                                                <th>Critério de Avaliação</th>
                                                                <th>Nota máxima</th>
                                                                <th>Parâmetros</th>
                                                                <th>Sua nota</th>
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
                                                                <td><input name="q[<?=$q->id;?>]" type="number" step="0.01" required
                                                                        min="<?=$q->limite_min;?>" max="<?=$q->limite_max;?>" class="form-control"
                                                                        style="width:80px"></td>

                                                            </tr>
                                                            <?php
                                                            }
                                                            ?>
                                                            
                                                        </tbody>
                                                    </table>
                                                    <br><br>
                                                    <?=$this->Form->control('observacao_avaliador',['label'=>'Observações:','rows'=>4,'class'=>'form-control', 'required'])?>

                                                </div>
                                            </div>
                                        </div>
                                        
                                        
                                                <?php if($raic->tipo_bolsa=='R'){?>
                                                    <div class="col-md-12">
                                                        <div class="card">
                                                            <div class="card-body">  
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <?=$this->Form->control('parecer',['label'=>'O orientador anexou o Parecer do Comitê de Ética em Pesquisa?','options'=>['I'=>'Não se aplica','N'=>'É necessário mas não anexou', 'S'=>'Anexou'],'empty'=>' - Selecione - ','required','class'=>'form-control'])?>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <?=$this->Form->control('destaque',['label'=>'O aluno se destacou?', 'options'=>[0=>'Não',1=>'Sim'], 'empty'=>'- Escolha -', 'class'=>'form-control', 'required']);?>
                                                                    </div><div class="col-md-4">
                                                                        <?=$this->Form->control('indicado_premio_capes',['label'=>'Indica o aluno ao Prêmio Destaque CNPq?', 'options'=>[0=>'Não',1=>'Sim'], 'empty'=>'- Escolha -', 'class'=>'form-control', 'required']);?>
                                                                    </div>

                                                                </div>
                                                                <?php if($raic->projeto_bolsista->subprojeto_renovacao=='D'){?>
                                                        <tr>
                                                            <td colspan="4">
                                                                <?=$this->Form->control('alteracao',['label'=>'A alteração do subprojeto mantém o objeto original da vigência anterior?', 'options'=>[0=>'Não',1=>'Sim'], 'empty'=>'- Escolha -', 'class'=>'form-control', 'required']);?>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="4">
                                                                <?=$this->Form->control('observacao_alteracao',['label'=>'Observação sobre a alteração do subprojeto:','rows'=>4,'class'=>'form-control', 'required'])?>
                                                            </td>
                                                        </tr>
                                                    <?php }?>

                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                <?php }?>
                                        <div class="clearfix"></div>
                                        <?=$this->Form->button(' Finalizar lançamento de notas ',['class'=>'btn btn-success btn-block'])?>
                                        <?=$this->Form->end()?>
                                    <?php }?>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    


    
</section>
