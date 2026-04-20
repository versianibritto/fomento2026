<style>
    .detalhe-projeto strong {
        width: 120px;
    }
</style>
<section>

    <h3>AVALIAÇÃO PDJ <?=($ab->tipo=='J'? 'NOVA':($ab->tipo=='W'?'WORKSHOP':'x'))?>
        <?=$this->Html->link(' Voltar ',['controller'=>'Usuarios', 'action'=>'minhas_avaliacoes'],['class'=>'btn btn-danger float-end','style'=>'margin-top:-10px']);?>
    </h3>

    <div class="p-3">
    <div class="card">
        <div class="card-body" style="background-color: #858789;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="text-default m-0">
                    <?php if($ab->tipo=='W'){ ?>
                        Detalhes do Workshop # <?=$work->id.' / Renovação # '.$work->pdj_inscricoe_id.($work->deleted ? ' (DELETADA/INATIVA)' : '')?>
                    <?php } ?>
                    <?php if($ab->tipo=='J'){ ?>
                        Detalhes da Incrição # <?=$inscricao->id.($inscricao->deleted!=null ? ' (DELETADA/INATIVA)' : '')?>
                    <?php } ?>
                </h3>
            </div>
            

            
            <div class="card">
                <div class="card-body">
                    <h4>
                        <strong class="text-default">
                            <p> Aluno <?=($ab->tipo=='W'?' de Renovação ':' de Bolsa Nova')
                            . $this->Html->link(' clique para detalhes da inscrição: '.$inscricao->id, ['controller' => 'Pdj', 'action' => 'detalhepdj', $inscricao->id], ['target'=>'_blank'])?> </p></strong>
                    </h4>                        
                    <br>Bolsista: <strong> <?=$inscricao->candidato->nome?></strong>
                    <br>Orientador: <strong><?=$inscricao->usuario->nome?></strong>
                    <br>Unidade <strong><?=$inscricao->usuario->unidade->sigla?> </strong>
                    <br><br>
                    
                    <?php if(($inscricao->usuario_cadastro!=null) ){?>
                        <small><strong><p> Cadastrado por: <?=($inscricao->cadastro->nome).' em '.($inscricao->created==null?'Não informado':$work->created->i18nFormat('dd/MM/YYYY H:mm:ss'))?> </p></strong></small>
                    <?php }?>

                    <strong>
                        <div class="text-danger">
                            <?php
                                if($inscricao->deleted!=null) {
                                    print '<em><p> Inscrição DELETEDA <br>' . 
                                    (
                                        (($inscricao->justificativa_cancelamento!= null)?
                                        'Justificativa' . $inscricao->justificativa_cancelamento:'Justificativa não informada'
                                    )
                                    )
                                    . '</p></em>';
                                    
                                } 
                                
                            ?>
                        </div>
                    </strong>

                </div>
            </div>
            <?php if($ab->tipo=='W'){ ?>
                <div class="card">
                    <div class="card-body">
                        <h3 class="text-default">Dados da Apresentação do Workshop</h3>
                        <br><em>Data da Apresentação <?=($work->data_apresentacao != null?($work->data_apresentacao->i18nFormat('dd/MM/YYYY')):'não cadastrada') ?>

                        <?php if(in_array($work->tipo_bolsa, ['R'])) { ?>
                            <br><em>Tipo de Apresentação <?=($work->tipo_apresentacao != null?($work->tipo_apresentacao=='O'?'Oral':'Pôster'):'não cadastrada') ?>
                            <br><em>Local de Apresentação <?=($work->local_apresentacao != null?($work->local_apresentacao):'não cadastrado') ?>                        
                            <br><em>Programa: PDJ
                           
                            
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
            

            <div class="card">
                <div class="card-body" style="background-color: #ebedef;">
                    <div class="card">
                    <div class="card-body">
                        <h3 class="text-default">Projeto</h3>
                        <br>
                        <h4>Título: <strong> <?=$inscricao->projeto->titulo?></strong></h4>
                        
                        
                    </div>
                </div>
                <h3 class="text-default">Anexos</h3>

                    <div class="row">
                        <?php if ($anexo_proj->count() > 0): ?>
                            <?php
                            // Agrupar por tipo_anexo_id
                            $agrupadosPorTipo = [];
                            $maisRecentes = [];

                            foreach ($anexo_proj as $anexo) {
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
                                            <i class="fa fa-folder-open text-primary me-3" style="font-size: 1rem;"></i>
                                            <h5 class="mb-0 fw-bold text-dark" style="font-size: 1rem;">
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
                                            <i class="fa fa-folder-open text-primary me-3" style="font-size: 1rem;"></i>
                                            <h5 class="mb-0 fw-bold text-dark" style="font-size: 1rem;">
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
                    <div class="row">
                        <?php if ($doc_bolsista->count() > 0): ?>
                            <?php
                            // Agrupar por tipo_anexo_id
                            $agrupadosPorTipo = [];
                            $maisRecentes = [];

                            foreach ($doc_bolsista as $anexo) {
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
                                            <i class="fa fa-folder-open text-primary me-3" style="font-size: 1rem;"></i>
                                            <h5 class="mb-0 fw-bold text-dark" style="font-size: 1rem;">
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
    </div>
</div>
    
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-12 datas">
                        <h2>FORMULÁRIO DE AVALIAÇÃO</h2>
                        <h4 style="color: #b30000; font-weight: bold;">
                        <?php if($ab->tipo=='W') {?>
                            O formulário de avaliação ficará disponível a partir da data da apresentação (<?=$work->data_apresentacao->i18nFormat("dd/MM/Y")?>)</h4> 
                                       
                        <?php } ?>
                        <?php if($ab->tipo=='J' && !$libera_form) {?>
                            O formulário de avaliação ainda não foi liberado</h4> 
                        <?php } ?>

                        <?php if($ab->situacao!='F' && $libera_form){?>

                            
                            <?=$this->Form->create();?>
                            <?=$this->Form->control('avaliador_bolsista_id', ['type'=>'hidden', 'value'=>$work->id]);?>
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
                        <?php }?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
