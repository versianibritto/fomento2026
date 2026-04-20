<style>
    .detalhe-projeto strong {
        width: 120px;
    }
</style>
<section>

    <h3>AVALIAÇÃO DE BOLSA NOVA #<?=$inscricao->id?>
        <?=$this->Html->link(' Voltar ',['controller'=>'Usuarios', 'action'=>'minhas_avaliacoes'],['class'=>'btn btn-danger float-end','style'=>'margin-top:-10px']);?>
    </h3>

    <div class="row mt-3">
        <div class="col-md-12">
            
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <span class="d-block"><strong>Data da inscrição: </strong><span><?=$inscricao->created == '' ? '<i class="text-danger">Não localizada</i>' : $inscricao->created->i18nFormat('dd/MM/YYYY')?></span></span>
                        <span class="d-block"><strong>Edital : </strong><span><?=($inscricao->editai_id==null?'-':($inscricao->editai_id==3?'* LEGADO *'.$inscricao->editai_id:$inscricao->editai->nome))?></span></span>
                        <span class="d-block"><strong>Situação : </strong><span><?=($inscricao->situacao == 'Z' ? '<span class="badge bg-success me-2"> Suspenso </span>' :(($inscricao->vigente == 1 ? '<span class="badge bg-success me-2"> Vigente </span>' : ($inscricao->deleted == 1 ? '<span class="badge bg-danger me-2"> Excluído </span>' : '')))) . $situacao[$inscricao->situacao]?></span></span>
                        <span class="d-block"><strong>Orientador: </strong><span><?=$inscricao->orientadore->nome?></span></span>
                        
                        <span class="d-block"><span><?=($inscricao->prorrogacao == 1 ? '<span class="badge bg-danger me-2"> BOLSA EXTENDIDA </span>': '')?></span></span>
                        <span class="d-block"><strong>Coorientador:</strong>
                        <span>
                        <?php
                        if($inscricao->coorientador == null) {
                            print 'Não definido';
                        } else {
                            print $inscricao->coorientadore->nome . 
                            ($inscricao->resposta_coorientador=='A'?'<span class="ms-2 badge bg-success">Aceito</span>':($inscricao->resposta_coorientador=='P'?'<span class="ms-2 badge bg-warning">Pendente</span>':'<span class="ms-2 badge bg-danger">Recusado</span>'));
                        }
                        ?>
                        </span>
                    </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h4>Dados da Inscrição</h4>

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
                    <br>
                    <div class="card">
                        <div class="card-body" style="background-color: #ebedef;">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Cota Afirmativa:</strong>
                                    <?php
                                        $cota = ["G" => "Geral / Ampla concorrência", "N" => "Pessoas Negras/Pardas", "T" => "Pessoas Trans", "I" => "Pessoas Indígena", "D" => "Pessoas com deficiência"]; 
                                    ?>
                                    <?=$inscricao->cota==null?'Não informada':$cota[$inscricao->cota]?>

                                </div>
                                <div class="col-md-6">
                                    <strong>Origem:</strong>
                                    
                                    <?=$inscricao->cota==null?'Não informada':$origem[$inscricao->origem]?>

                                </div>
                            </div>

                                
                                
                            <div class="col-md-12">
                                <br>
                                <?=$inscricao->subprojeto_renovacao=='D'?'Sim':'Não'?>
                                <?php if($inscricao->subprojeto_renovacao=='D'){?>
                                    <?=$this->Html->link(' (Ver detalhes da vigência anterior - '.$inscricao->referencia_inscricao_anterior.') ', ['controller' => 'Projetos', 'action' => 'detalhesubprojeto', $inscricao->referencia_inscricao_anterior], ['target' => '_blank']);?>
                                <?php }?>
                            </div>
                                
                            <div class="col-12 mt-2">
                                <?php if($inscricao->subprojeto_renovacao=='D'){?>
                                    <br>
                                    <strong> Justificativa para a alteração: </strong>
                                    <?=$inscricao->justificativa_alteracao?>
                                <?php }?>
                            </div>
                            
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body" style="background-color: #ebedef;">
                            <h3 class="text-default">Documentos Anexos do Bolsista</h3>

                            <div class="row">
                                <?php if(($doc_bolsista->count()) > 0) {
                                    foreach($doc_bolsista as $anexo) { ?>
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
                                    print '<p class="mt-3 mb-0 text-danger">Não há anexos de documentos dos bolsistas vinculados.</p>';

                                }?>
                                    
                            </div>
                        </div>
                    </div>

                    
                      
                </div>
                <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
                    <br>
                    <div class="card">
                        <div class="card-body" style="background-color: #ebedef;">
                            <h4>Projeto #<?=$inscricao->projeto_id?> : <?=$inscricao->projeto->titulo;?></h4>
                            <div class="accordion" id="accordionExample">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" style="background-color:#c5dcfd;">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne" style="border:none;background-color:transparent;font-size:15px;padding-bottom:10px;padding-left:15px;">
                                            Clique para ler o resumo
                                        </button>
                                    </h2>
                                    <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                        <?=$inscricao->projeto->resumo;?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body" style="background-color: #ebedef;">
                        <h3 class="text-default">Anexos do Projeto do Orientador</h3>

                            <div class="row">
                                
                                <?php if(($anexo_proj->count()) > 0) {

                                    // Agrupa os mais recentes por tipo_anexo_id
                                    $maisRecentes = [];
                                    foreach ($anexo_proj as $anexo) {
                                        $tipoId = $anexo->tipo_anexo_id;
                                        if (!isset($maisRecentes[$tipoId]) || $anexo->created > $maisRecentes[$tipoId]->created) {
                                            $maisRecentes[$tipoId] = $anexo;
                                        }
                                    }
                                    

                                    foreach($anexo_proj as $anexo) { ?>
                                        <?php
                                            $destaqueAtual = ($maisRecentes[$anexo->tipo_anexo_id]->id === $anexo->id);
                                        ?>
                                        <div class="col-12">
                                            <div class="d-flex align-items-center mb-3 p-3" style="background-color: #ffffff; border-left: 5px solid #0d6efd; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                                <i class="fa fa-folder-open text-primary me-3" style="font-size: 1.5rem;"></i>
                                                <h5 class="mb-0 fw-bold text-dark" style="font-size: 1.25rem;">
                                                    <?= h($anexo->tipo_anexo->nome) ?>
                                                </h5>
                                            </div>
                                        </div>

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
                                    <?php } ?>
                                <?php }else {
                                    print '<p class="mt-3 mb-0 text-danger">Não há anexos do projeto do orientador vinculados.</p>';

                                }?>
                                
                            </div>
                            <div class="row">
                                <?php if ($anexo_orientador->count() > 0): ?>
                                    <?php
                                    // Agrupar por tipo_anexo_id
                                    $agrupadosPorTipo = [];
                                    $maisRecentes = [];

                                    foreach ($anexo_orientador as $anexo) {
                                        $tipoId = $anexo->tipo_anexo_id;
                                        $agrupadosPorTipo[$tipoId][] = $anexo;

                                        // Identifica o mais recente por tipo
                                        if (!isset($maisRecentes[$tipoId]) || $anexo->created > $maisRecentes[$tipoId]->created) {
                                            $maisRecentes[$tipoId] = $anexo;
                                        }
                                    }
                                    ?>

                                    <?php foreach ($agrupadosPorTipo as $tipoId => $anexos): ?>
                                        <div class="row mb-4">
                                            <div class="col-12">
                                                <div class="d-flex align-items-center mb-3 p-3" style="background-color: #ffffff; border-left: 5px solid #0d6efd; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                                    <i class="fa fa-folder-open text-primary me-3" style="font-size: 1.5rem;"></i>
                                                    <h5 class="mb-0 fw-bold text-dark" style="font-size: 1.25rem;">
                                                        <?= h($anexos[0]->tipo_anexo->nome) ?>
                                                    </h5>
                                                </div>
                                            </div>


                                            <?php foreach ($anexos as $anexo): ?>
                                                <?php $destaqueAtual = ($maisRecentes[$tipoId]->id === $anexo->id); ?>
                                                <div class="col-md-3">
                                                    <div class="card mb-3 <?= $destaqueAtual ? 'border border-danger shadow-sm' : '' ?>">
                                                        <div class="card-body">
                                                            <?php if ($destaqueAtual): ?>
                                                                <div style="background-color: #fff3cd; color: #856404; font-weight: 500; font-size: 0.85rem; padding: 4px; text-align: center; border-bottom: 1px solid #ffeeba;">
                                                                    Mais recente
                                                                </div>
                                                            <?php endif; ?>
                                                            <p class="mt-2 mb-1">
                                                                <?= $anexo->tipo_anexo->nome; ?>

                                                                <small>Incluído em <?= date("d/m/Y H:i", strtotime($anexo->created)) ?> por <?= h($anexo->usuario->nome) ?></small>
                                                            </p>
                                                            <a href="/uploads/anexos/<?= h($anexo->anexo) ?>" target="_blank" class="badge bg-info text-white">
                                                                <i class="fa fa-download me-1"></i> Baixar anexo
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="mt-3 mb-0 text-danger">Não há documentos de comitês vinculados.</p>
                                <?php endif; ?>

                                    
                            </div>
                        </div>
                    </div>

                        
                    
                </div>
                <br>
                <div class="tab-pane fade" id="contact-tab-pane" role="tabpanel" aria-labelledby="contact-tab" tabindex="0">
                    <div class="card">
                        <div class="card-body" style="background-color: #ebedef;">
                            <h4>SubProjeto #<?=$inscricao->id?> : <?=$inscricao->sp_titulo;?></h4>
                            <div class="accordion" id="accordionSubProjeto">
                                <div class="accordion-item">
                                    <h2 class="accordion-header" style="background-color:#c5dcfd;">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo" style="border:none;background-color:transparent;font-size:15px;padding-bottom:10px;padding-left:15px;">
                                            Clique para ler o RESUMO DO SUBPROJETO
                                        </button>
                                    </h2>
                                    <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionSubProjeto">
                                        <div class="accordion-body">
                                            <?=(($inscricao->sp_resumo==null) || ($inscricao->sp_resumo=="")?'<p class="mt-3 mb-0 text-danger">Resumo do Subprojeto não informado.</p>':$inscricao->sp_resumo );?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br>
                            
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body" style="background-color: #ebedef;">
                            <h3 class="text-default">Anexos do Subrojeto / Relatório do Bolsista</h3>

                            <div class="row">
                                <?php if(($anexo_sub->count()) > 0) {
                                    foreach($anexo_sub as $anexo) { ?>
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
                                    print '<p class="mt-3 mb-0 text-danger">Não há documentos de comites vinculados.</p>';

                                }?>
                                    
                            </div>
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
                        
                        <?php if($ab->situacao!='F' ){?>
                            <?=$this->Form->create();?>
                            <?=$this->Form->control('avaliador_bolsista_id', ['type'=>'hidden', 'value'=>$inscricao->id]);?>
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
                                   
                                </tbody>
                            </table>
                            <div class="clearfix"></div>
                            <?=$this->Form->button(' Finalizar lançamento de notas ',['class'=>'btn btn-success btn-block'])?>
                            <?=$this->Form->end()?>
                        <?php }else{?>
                            <strong>Vcê já avaliou esta Inscrição</strong>
                        <?php }?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<style>
    /* Estilizando o overlay de carregamento */
    #loading-overlay {
        display: none; /* Escondido por padrão */
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    /* Spinner */
    #loading-spinner {
        width: 80px;
        height: 80px;
        border: 8px solid #f3f3f3;
        border-top: 8px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
<div id="loading-overlay">
    <div id="loading-spinner"></div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>

    $(document).ready(function() {
        $("form").on("submit", function() {
            $("#loading-overlay").fadeIn(); // Exibe o loading
        });
    });
    </script>
