<div class="p-3">
<?php $s = ['I'=>'Nova', 'N'=>'Nova Finalizada', 'A'=>'Ativo', 
'R'=>'Renovação', 'C'=>'Cancelamento Solicitado', 'S'=>'Subst Pendente',
'E'=>'Encerrado','Q'=>'Cancelado',
'L'=>'Finalizando bolsa',
'F'=>'Renovação Finalizada'];?>

    <h4>Listagem Bolsas Novas</h4>

    <div class="well">
    <?=$this->Form->create(null,['url'=>['controller'=>'Bolsistas','action'=>'nova']]);?>
        <div class="input-group">
            <div class="input-group-btn search-panel">
                <?=$this->Form->control('busca',['label'=>false,'options'=>
                    $unidades,'class'=>'btn btn-default','style'=>'height:34px', 'empty'=>'Selecione'])?>
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
                    <th>Nome</th>
                        <th>Unidade de Avaliação</th>
                        <th>Área</th>
                        <th>Tipo</th>


                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach($novas as $u):
                        $nome = explode(" ", $u->usuario->nome);

                    ?>
                    <tr>
                        <td><?=$u->id.' - '.$nome[0] . ' ' . end($nome)?></td>
                        <td><?=($u->usuario->unidade_id==null?'Não informado':$u->usuario->unidade->sigla)?></td>
                        <td><?=($u->area_id==null?'Não informado':$u->area->nome)?></td>
                        <td><?=($u->tipo_avaliador=='N'?'Bolsa Nova':($u->tipo_avaliador=='R'?'Renovação':'Ambos'))?></td>

                        <td class="text-right">
                            <?=$this->Html->link('<i class="fas fa-eye"></i>', ['controller' => 'Usuarios', 'action' => 'ver', $u->usuario_id], ['class' => 'btn btn-info btn-xs', 'escape' => false]);?>
                        </td>
                    </tr>
                    <?php
                    endforeach;
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
