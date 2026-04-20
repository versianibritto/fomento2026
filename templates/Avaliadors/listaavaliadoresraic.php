<div class="p-3">
<?php $s = ['I'=>'Nova', 'N'=>'Nova Finalizada', 'A'=>'Ativo', 
'R'=>'Renovação', 'C'=>'Cancelamento Solicitado', 'S'=>'Subst Pendente',
'E'=>'Encerrado','Q'=>'Cancelado',
'L'=>'Finalizando bolsa',
'F'=>'Renovação Finalizada',
'O'=>'Renovação não concluída'];?>

    <h4>Lista de Avaliadores Raic</h4>

    <?php if($this->request->getAttribute('identity')['yoda']){?>
        <div class="well">
            <?=$this->Form->create(null,['url'=>['controller'=>'Avaliadors','action'=>'listaavaliadoresraic']]);?>
                <div class="input-group">
                    <div class="input-group-btn search-panel">
                        <?=$this->Form->control('busca',['label'=>false,'options'=>$unidades,'class'=>'btn btn-default','style'=>'height:34px', 'empty'=>'Selecione'])?>
                    </div>
                    <span class="input-group-btn">
                        <?=$this->Form->button('Procurar',['class'=>'btn btn-default','escape'=>false]);?>
                    </span>
                    <span class="input-group-btn">
                        <?=$this->Html->link(' Todas as unidades  <i class="fas fa-eye"></i>', ['controller'=>'Avaliadors','action' => 'listaavaliadoresraic/1'], ['class' => 'btn btn btn-info mr-1', 'escape' => false])?>
                    </span>
                </div>
            <?=$this->Form->end();?>
        </div>
    <?php }?>
    












    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Inscrição</th>
                        <th>Bolsista</th>
                        <th>Orientador</th>
                        <th>Unidade</th>
                        <th>Avaliador</th>
                        <th>Data Raic</th>
                        <th>Situação</th>
                        <th></th>



                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach($lista as $b):
                        //dd($b);
                        $nome = explode(" ", $b->avaliador->usuario->nome);
                        $nome_b = explode(" ", $b->raic->usuario->nome);
                        $nome_o = explode(" ", $b->raic->orientadore->nome);
                    ?>
                    <tr style="<?=(($b->situacao=='F'?'color:#090!important':''))?>">
                        <td><?=$this->Html->link($b->bolsista.'   <i class="fas fa-eye"></i>', ['controller'=>'Raics','action' => 'ver', $b->bolsista], ['class' => 'btn btn-xs btn-info mr-1', 'escape' => false])?></td>
                        <td><?=$nome_b[0] . ' ' . end($nome_b) ?></td>
                        <td><?=$nome_o[0] . ' ' . end($nome_o) ?></td>
                        <td><?=$b->raic->orientadore->unidade->sigla?></td>
                        <td><?=$nome[0] . ' ' . end($nome) ?></td> 
                        <td><?=$b->raic->data_apresentacao?></td>
                        <td>
                            <?php if($b->deleted==1){?>
                                <?="- DELETADO - "?>
                            <?php }else{?>
                                <?=(($b->situacao=='F'?'Notas Lançadas':'Aguardando'))?>
                            <?php }?>
                        </td>
                        <td>
                            <?php if($this->request->getAttribute('identity')['yoda'] && $b->situacao=='F'){?>
                                <?=$this->Html->link('<i class="fas fa-file-alt"></i>', ['controller'=>'Avaliadors','action' => 'vernotas', $b->id], ['class' => 'btn btn-xs btn-danger mr-1 ', 'escape' => false])?>
                            <?php }?>
                        </td>
                    </tr>
                    <?php
                    endforeach;
                    ?>
                </tbody>
            </table>
            
        </div>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>
