<div class="p-3">
<?php $s = ['I'=>'Nova', 'N'=>'Nova Finalizada', 'A'=>'Ativo', 
'R'=>'Renovação', 'C'=>'Cancelamento Solicitado', 'S'=>'Subst Pendente',
'E'=>'Encerrado','Q'=>'Cancelado',
'L'=>'Finalizando bolsa',
'F'=>'Renovação Finalizada'];?>

    <h4>Listagem Bolsas Novas</h4>

    <div class="well">
    <?=$this->Form->create(null,['url'=>['controller'=>'Bolsistas','action'=>'novas']]);?>
        <div class="input-group">
            <div class="input-group-btn search-panel">
                <?=$this->Form->control('busca',['label'=>false,'options'=>
                    $editais,'class'=>'btn btn-default','style'=>'height:34px', 'empty'=>'Selecione'])?>
            </div>
            <span class="input-group-btn">
                <?=$this->Form->button('Procurar',['class'=>'btn btn-default','escape'=>false]);?>
            </span>
        </div>
    <?=$this->Form->end();?>
    </div>
    












    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Inscrição</th>
                        <th>Bolsista</th>
                        <th>Orientador</th>
                        <th>Unidade</th>
                        <th>Data Solicitação</th>
                        <th>Situação</th>
                        <th>Programa</th>


                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach($bolsistas as $b):
                        $nome = explode(" ", $b->usuario->nome);
                        $nome_o = explode(" ", $b->orientadore->nome);

                    ?>
                    <tr>
                        
                        <td><?=$this->Html->link($b->id.'   <i class="fas fa-eye"></i>', ['controller'=>'Bolsistas','action' => 'ver', $b->id], ['class' => 'btn btn-xs btn-info mr-1', 'escape' => false])?></td>
                        <td><?=$nome[0] . ' ' . end($nome) ?></td>
                        <td><?=$nome_o[0] . ' ' . end($nome_o) ?></td>
                        <td><?=$b->orientadore->unidade->sigla?></td>
                        <td><?=($b->created==null?' - x - ':$b->created->i18nFormat('dd/MM/YYYY'))?></td>
                        <td><?=($b->deleted==0?$s[$b->situacao]:' - DELETADO -')?></td>
                        <td><?=($b->editai->nome)?></td>
                    </tr>
                    <?php
                    endforeach;
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
