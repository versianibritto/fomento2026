<style>
    .detalhe-projeto strong {
        width: 120px;
    }
</style>
<section>

    <h3>AVALIAÇÃO RAIC
        <?=$this->Html->link(' Voltar ',['controller'=>'Usuarios', 'action'=>'minhas_avaliacoes'],['class'=>'btn btn-danger float-end','style'=>'margin-top:-10px']);?>
    </h3>

    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h4><?=$raic->titulo?></h4>
                    <?=$raic->deleted==1?'RAIC DELETADA':($raic->projeto_bolsista->deleted==1?'INSCRIÇÃO DELETADA':'') ;?>
                    <span class="d-block"><strong>Data de cadastro: </strong><span class="float-end"><?=$raic->created == '' ? '<i class="text-danger">Não localizada</i>' : $raic->created->i18nFormat('dd/MM/YYYY')?></span></span>
                    <span class="d-block"><strong>Grande área <span style="color:#1a8cd1;">(CNPq)</span>:</strong><span class="float-end"><?=$raic->projeto_bolsista->projeto->area->nome?></span></span>
                    <span class="d-block"><strong>Área de pesquisa <span style="color:#1a8cd1;">FIOCRUZ</span>:</strong><span class="float-end"><?=$raic->projeto_bolsista->projeto->linha->areas_fiocruz->nome?></span></span>
                    <span class="d-block"><strong>Linha de pesquisa na <span style="color:#1a8cd1;">FIOCRUZ</span>:</strong><span class="float-end"><?=$raic->projeto_bolsista->projeto->linha->nome?></span></span>
                    <p></p>
                    <span class="d-block"><strong>Bolsista:</strong><span class="float-end"><?=$raic->usuario->nome_social ? $raic->usuario->nome_social . ' *' : $raic->usuario->nome;?></span></span>
                    <span class="d-block"><strong>Orientador:</strong><span class="float-end"><?=$raic->projeto_bolsista->orientadore->nome?></span></span>
                    <span class="d-block"><strong>Coorientador:</strong>
                        <span class="float-end">
                        <?php
                        if($raic->projeto_bolsista->coorientador == null) {
                            print 'Não definido';
                        } else {
                            print $raic->projeto_bolsista->coorientadore->nome . 
                            ($raic->projeto_bolsista->resposta_coorientador=='A'?'<span class="ms-2 badge bg-success">Aceito</span>':($raic->projeto_bolsista->resposta_coorientador=='P'?'<span class="ms-2 badge bg-warning">Pendente</span>':'<span class="ms-2 badge bg-danger">Recusado</span>'));
                        }
                        ?>
                        </span>
                    </span>
                    <div class="row">
                    <?php
                    if(sizeof($raic->projeto_bolsista->projeto_anexos) > 0) {
                        print '<ul class="list-group-horizontal mt-3">';
                        foreach($raic->projeto_bolsista->projeto_anexos as $anexo) {
                            print '<li class="list-group-item">' . 
                                $anexo->tipo_anexo->nome . '<br />' .
                                $this->Html->link('<i class="fa fa-download"></i> download', ['controller' => 'uploads', 'action' => 'anexos', $anexo->anexo], ['class' => 'btn btn-xs btn-info', 'escape' => false]);                                
                            '</li>';
                        }
                        print '</ul>';
                    } else {
                        print '<p class="mt-3 mb-0 text-danger">Não há anexos vinculados ao subprojeto.</p>';
                    }
                    ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <ul class="nav nav-pills" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">
                        Dados do bolsista
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">
                        Dados do projeto do Orientador
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact-tab-pane" type="button" role="tab" aria-controls="contact-tab-pane" aria-selected="false">
                        Subprojeto/Relatório do bolsista
                    </button>
                </li>
            </ul>
            <div class="tab-content mt-2" id="myTabContent">
                <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <strong>Matrícula: </strong> 
                            <?php 
                            if(($raic->projeto_bolsista->matricula)!=null) { ?> 
                                <small> <?=$this->Html->link($raic->projeto_bolsista->matricula,  ['controller' => 'uploads', 'action' => 'documentos',  $raic->projeto_bolsista->matricula], ['target' => '_blank'])?></small>
                            <?php } else { ?>
                                <small> Não Informado </small>
                            <?php } ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Histórico: </strong>
                            <?php if(($raic->projeto_bolsista->historico) != null) { ?> 
                                <small><?=$this->Html->link($raic->projeto_bolsista->historico,  ['controller' => 'uploads', 'action' => 'documentos',  $raic->projeto_bolsista->historico],  ['target' => '_blank'])?> </small>
                            <?php } else { ?>
                                <small> Não Informado </small>
                            <?php } ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Cota Afirmativa:</strong>
                            <?php
                            $cota = ["G" => "Geral / Ampla concorrência", "N" => "Pessoas Negras/Pardas", "T" => "Pessoas Trans", "I" => "Pessoas Indígena", "D" => "Pessoas com deficiência"]; 
                            print $cota[$raic->projeto_bolsista->cota]?>
                        </div>
                        <div class="col-md-3">
                            <strong>Atestado (cota):</strong>
                            <?php if(($raic->projeto_bolsista->atestado)!=null && $raic->projeto_bolsista->cota=='D' ) { ?>
                            <small> <?=$this->Html->link($raic->projeto_bolsista->atestado,  ['controller' => 'uploads', 'action' => 'documentos',  $raic->projeto_bolsista->atestado],  ['target' => '_blank'])?> </small>
                            <?php  } elseif(($raic->atestado) == null && $raic->cota=='D' ) { ?>
                                <small><strong class="text-danger">Não Informado</strong></small>
                            <?php } else {
                                print '<small><strong class="text-info">Não aplicável</strong></small>';
                            } ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Programa </strong>
                            <?= ($raic->projeto_bolsista->programa == 'P'? 'Pibic': (
                            $raic->projeto_bolsista->programa=='T'?'Pibiti':'Iniciação Científica'))?>
                        </div>
                        <div class="col-md-3">
                            <strong>Curso </strong>
                            <?= ($raic->usuario->curso == ''? 'Não Informado': (
                            $raic->usuario->curso.($raic->usuario->instituicao_curso==''?'':' - '.($raic->usuario->instituicao->sigla))))?>

                        </div>
                        <div class="col-md-3">
                            <strong>Previsão de Conclusão </strong>
                            <?= ($raic->usuario->ano_conclusao == ''? 'Não Informado': $raic->usuario->ano_conclusao)?>
                        </div>
                        <div class="col-md-3">
                            <strong> Alteração no subprojeto: </strong>
                            <?=$raic->projeto_bolsista->subprojeto_renovacao=='D'?'Sim':'Não'?>
                        </div>
                        <div class="col-12">
                            <?php if($raic->projeto_bolsista->subprojeto_renovacao=='D'){?>
                
                                    <?=$this->Html->link(' (Ver detalhes da vigência anterior - '.$raic->projeto_bolsista->referencia_inscricao_anterior.') ', ['controller' => 'Bolsistas', 'action' => 'ver', $raic->projeto_bolsista->referencia_inscricao_anterior], ['target' => '_blank']);?>
                            
                            <?php }?>
                        </div>
                        <div class="col-12 mt-2">
                            <?php if($raic->projeto_bolsista->subprojeto_renovacao=='D'){?>
                                <strong> Justificativa para a alteração: </strong>
                                <?=$raic->projeto_bolsista->justificativa_alteracao?>
                            <?php }?>
                        </div>
                        <div class="col-12 mt-2">
                            <strong> Dados apresentados na RAIC: </strong>
                            <?=$raic->projeto_bolsista->referencia_raic==0?
                            'Dados do subprojeto cadastrado com alteração':
                            'Apresentação dos dados da vigência anterior'?>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
                    <h4>Projeto #<?=$raic->projeto_bolsista->projeto_id?> : <?=$raic->projeto_bolsista->projeto->titulo;?></h4>
                    <div class="accordion" id="accordionExample">
                        <div class="accordion-item">
                            <h2 class="accordion-header" style="background-color:#c5dcfd;">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne" style="border:none;background-color:transparent;font-size:15px;padding-bottom:10px;padding-left:15px;">
                                    Clique para ler o resumo
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                                <div class="accordion-body">
                                <?=$raic->projeto_bolsista->projeto->resumo;?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    if(sizeof($raic->projeto_bolsista->projeto->projeto_anexos) > 0) {
                        print '<ul class="list-group-horizontal mt-3">';
                        foreach($raic->projeto_bolsista->projeto->projeto_anexos as $anexo) {
                            print '<li class="list-group-item flex-fill">' . 
                                $anexo->tipo_anexo->nome . '<br />' .
                                $this->Html->link('<i class="fa fa-download"></i> download', ['controller' => 'uploads', 'action' => 'anexos', $anexo->anexo], ['class' => 'btn btn-xs btn-info', 'escape' => false]);                                
                            '</li>';
                        }
                        print '</ul>';
                    } else {
                        print '<p class="mt-3 mb-0 text-danger">Não há anexos vinculados ao subprojeto.</p>';
                    }
                    ?>
                </div>
                <div class="tab-pane fade" id="contact-tab-pane" role="tabpanel" aria-labelledby="contact-tab" tabindex="0">
                    <h4>Projeto #<?=$raic->projeto_bolsista_id?> : <?=$raic->projeto_bolsista->sp_titulo;?></h4>
                    <div class="accordion" id="accordionSubProjeto">
                        <div class="accordion-item">
                            <h2 class="accordion-header" style="background-color:#c5dcfd;">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo" style="border:none;background-color:transparent;font-size:15px;padding-bottom:10px;padding-left:15px;">
                                    Clique para ler o resumo
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionSubProjeto">
                                <div class="accordion-body">
                                <?=$raic->projeto_bolsista->sp_resumo;?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Relatório parcial</h5>                            
                            <?php
                            if(trim($raic->projeto_bolsista->relatorio) == "") {
                                print '<p class="mt-3 mb-0 text-danger">Não há relatório parcial vinculados ao subprojeto.</p>';
                            } else {
                                print $this->Html->link('<i class="fa fa-download"></i> download', ['controller' => 'uploads', 'action' => 'documentos', $raic->projeto_bolsista->relatorio], ['class' => 'btn btn-xs btn-info', 'escape' => false]);
                            }
                            ?>
                        </div>
                        <div class="col-md-6 mt-2">
                            <h5>Relatório do egresso</h5>
                            <?php
                            if(($raic->projeto_bolsista->egresso) == "") {
                                print '<p class="mt-3 mb-0 text-danger">Não há relatório do egresso vinculados ao subprojeto.</p>';
                            } else {
                                print $this->Html->link('<i class="fa fa-download"></i> download', ['controller' => 'uploads', 'action' => 'documentos', $raic->projeto_bolsista->egresso], ['class' => 'btn btn-xs btn-info', 'escape' => false]);
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-12 datas">
                        <h2>FORMULÁRIO DE AVALIAÇÃO</h2>
                        <h4 style="color: #b30000; font-weight: bold;">
                        O formulário de avaliação ficará disponível a partir da data da apresentação (<?=$raic->data_apresentacao->i18nFormat("dd/MM/Y")?>)</h4> 
                                       
                        <h4></h4>
                        <h5><strong>Local da Apresentação: <?=$raic->local_apresentacao?></strong></h5>


                        <?php if($ab->situacao!='F' && $libera_form){?>

                            
                            <?=$this->Form->create();?>
                            <?=$this->Form->control('avaliador_bolsista_id', ['type'=>'hidden', 'value'=>$raic->id]);?>
                            <table class="table">
                                <thead>
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
                                    <tr>
                                        <td colspan="3">
                                            O orientador anexou o Parecer do Comitê de Ética em Pesquisa?
                                        </td>
                                        <td colspan="1">
                                            <?=$this->Form->control('parecer',['label'=>false,'options'=>['I'=>'Não se aplica','N'=>'É necessário mas não anexou', 'S'=>'Anexou'],'empty'=>' - Selecione - ','required','class'=>'form-control'])?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4">
                                            <?=$this->Form->control('observacao_avaliador',['label'=>'Observações:','rows'=>4,'class'=>'form-control', 'required'])?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4">
                                            <?=$this->Form->control('destaque',['label'=>'O aluno se destacou?', 'options'=>[0=>'Não',1=>'Sim'], 'empty'=>'- Escolha -', 'class'=>'form-control', 'required']);?>
                                        </td>
                                    </tr>
                                    <tr>


                                        <td colspan="4">
                                            <?=$this->Form->control('indicado_premio_capes',['label'=>'Indica o aluno ao Prêmio Destaque CNPq?', 'options'=>[0=>'Não',1=>'Sim'], 'empty'=>'- Escolha -', 'class'=>'form-control', 'required']);?>
                                        </td>
                                    </tr>
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
                                </tbody>
                            </table>
                            <div class="clearfix"></div>
                            <?=$this->Form->button(' Finalizar lançamento de notas ',['class'=>'btn btn-success btn-block'])?>
                            <?=$this->Form->end()?>
                        <?php }?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
