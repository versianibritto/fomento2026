<div class="p-3">
    <h4>Avaliadores Cadastrados</h4>
    <div class="well">
    <?=$this->Form->create(null,['url'=>['controller'=>'Avaliadors','action'=>'lista']]);?>
        <div class="input-group">
            <div class="input-group-btn search-panel">
                <?=$this->Form->control('busca',['label'=>false,'options'=>
                    [
                        'AB.nome'=>'Buscar por nome'
                        
                    ],'class'=>'btn btn-default','style'=>'height:34px'])?>
            </div>
            <?=$this->Form->control('valor',['label'=>false,'class'=>'form-control','placeholder'=>'parte do texto ...'])?>
            <span class="input-group-btn">
                <?=$this->Form->button('Procurar',['class'=>'btn btn-default','escape'=>false]);?>
            </span>
        </div>
    <?=$this->Form->end();?>
    </div>
    <?php
    if(isset($usca)&&(sizeof($busca)>0)){
    $b = preg_split('[ LIKE ]',$busca[0]);
    ?>
    <div class="alert alert-info">
        Você está buscando  quem possui <?=str_replace('%','',$b[1]);?> no <?=$b[0];?>
        <?= $this->Form->postLink('Trazer todos', ['action' => 'index',true],['class'=>'btn btn-danger btn-xs pull-right']) ?>
    </div>
    <?php
    }
    ?>
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
                    foreach($users as $u):
                        $nome = explode(" ", $u->usuario->nome);
                    ?>
                    <tr>
                        <td><?=$u->id.' - '.$nome[0] . ' ' . end($nome)?></td>
                        <td><?=($u->unidade_id==null?'Não informado':$u->unidade->sigla)?></td>
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