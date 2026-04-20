<?php
if($this->request->getAttribute('identity')['yoda']){
    $prog=[
        'I'=>'IC Manguinhos/ENSP', 
        'A'=>'IC Mata Atlântica', 
        'M'=>'IC Maré', 
        'P'=>'PIBIC', 
        'T'=>'PIBITI', 
        'J'=>'PDJ',  
        'X'=>'Todos os ICs'
    ];
}else{
    $prog=[
        'I'=>'IC Manguinhos/ENSP', 
        'A'=>'IC Mata Atlântica', 
        'M'=>'IC Maré', 
        'P'=>'PIBIC', 
        'T'=>'PIBITI', 
        'J'=>'PDJ'
    ];
}

?>

<div class="row">

    <h3 class="text-default" data-bs-toggle="tooltip" 
    title="Lista dos Avaliadores Raic" class="d-inline-block">
    Listagem de Avaliadores Raic <i class="fa fa-info-circle"></i>
    </h3>
   
    <div class="bg px-5 p-3 rounded mb-2 text-center" id="aviso-bolsista" style='background:#ccd1d1'>
        <h5 style='color:#900'>      
            
            Apenas o perfil <strong>Coordenação da Unidade</strong> tem acesso à listagem de avaliadores Raic;
            <br>
        </h5>
    </div>     
    <br><br>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <?=$this->Form->create(null, ['url'=>['controller'=>'Avaliadors','action'=>'avaliadoresraic'], 'class' => 'row row-cols-md-auto align-items-center'])?>
                    <div class="input-group">
                        <?=$this->Form->control('unidade',['label'=>'Unidade de interesse','options'=>$unidades,'empty'=>'- Selecione -','class'=>'form-control','style'=>'height:34px', 'required'])?>

                    </div>
                    <div class="col-12 mt-2">
                        <button type="submit" class="btn btn-primary mb-2 px-3"><i class="fa fa-search me-3"></i> Buscar</button>
                        <?= $this->Html->link('Limpar filtros', ['Controller'=>'Avaliadors','action' => 'limpar', 'buscaAvaliadoresRaic', 'avaliadoresraic'],['class'=>'btn btn-danger mb-2 px-3 pull-right']) ?>

                    </div>
                <?=$this->Form->end()?> 
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="input-group-btn search-panel">
                    <h4 class="text-center mt-2">
                        Se o(a) avalidor(a) não esta na listagem. clique no botão abaixo para cadastar um novo avaliador
                    </h4>
                </div>
                
                <div class="text-center mt-3">
                    <?=$this->Html->link('Clique para adicionar um Novo Avaliador', ['controller' => 'Avaliadors', 'action' => 'add', 'R'], ['class' => 'btn btn-info btn-xs', 'escape' => false]);?>
                </div>
                <?=$this->Form->end();?>
            </div>
        </div>
    </div>
</div>

<?php if($listas==null) { ?>
    <div class="col-12">
        <div class="bg-info px-5 p-3 rounded mb-2 text-center" id="aviso-bolsista">
            Para trazer uma listagem, selecione o filtro. 
        </div>            
    </div>  
<?php } if($listas!=null)  {?>

    <?php if($listas->count()==0) { ?>
        <div class="col-12">
            <div class="bg-warning px-5 p-3 rounded mb-2 text-center" id="aviso-bolsista">
                Nenhum registro encontrado. Selecione outro filtro
            </div>            
        </div>  
    <?php }?>
    
    

    <div class="col-12 d-flex">
        <div class="card flex-fill">
            <table class="table table-hover my-0">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Unidade de avaliação</th>
                        <th>Ano</th>
                        <th>Edital</th>
                        <th>Grande Área CNPq</th>
                        <th>Área CNPq</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach($listas as $a) {
                        $nome = explode(" ", $a->avaliador->usuario->nome);
                        $unidade = ($a->avaliador->usuario->unidade_id!=null?$a->avaliador->usuario->unidade->sigla:'N/A');
                        $ref = ($nome[0] . ' ' . end($nome) . ' / '.$unidade);

                   ?>
                    <tr>
                        
                        <td>
                            <?=$this->Html->link($ref, ['controller' => 'Users', 'action' => 'ver', $a->avaliador->usuario_id], ['escape' => false,'target'=>'_blank']);?>
                        </td>
                        <td><?=(isset($a->avaliador->unidade)?$a->avaliador->unidade->sigla:'Não informada')?></td>
                        <td><?=($a->avaliador->ano_convite) ?></td>
                        <td><?=($a->avaliador->editai_id!=null?$a->avaliador->editai->nome:'N/A') ?></td>

                        <td><?=(isset($a->avaliador->grandes_area->nome)?$a->avaliador->grandes_area->nome:(($this->request->getAttribute('identity')['yoda'] || ($this->request->getAttribute('identity')['jedi']))?$this->Html->link('Adicionar',['action'=>'areas', $a->avaliador->id],['class'=>'_colorbox']):'Não informado')) ?></td>
                        <td>
                            <?php if($a->avaliador->area_id!=null){?>
                                <?=($a->avaliador->area->nome)?>
                            <?php }?>

                            <?php if($a->avaliador->area_id==null){?>
                                <?php if($a->avaliador->grandes_area_id==null){?>
                                    <?='Informe a grande área'?>
                                <?php }?>

                                <?php if($a->avaliador->grandes_area_id!=null){?>
                                    <?php if($this->request->getAttribute('identity')['jedi'] || $this->request->getAttribute('identity')['yoda']){?>
                                        <?= $this->Html->link('Adicionar',['action'=>'cnpq', $a->avaliador->id],['class'=>'_colorbox']) ?>
                                    <?php }?>
                                    <?php if(!$this->request->getAttribute('identity')['jedi'] & !$this->request->getAttribute('identity')['yoda']){?>
                                        <?= 'Não informado' ?>
                                    <?php }?>
                                    
                                <?php }?>
                                
                            <?php }?>
                        </td>
                        <td>
                           <?php
                            if($a->avaliador->ano_convite==date('Y') && $a->avaliador->ano_aceite!=date('Y'))
                            {
                                print $this->Form->postLink('<i class="fa fa-hourglass-half fa-spin"></i>&nbsp;&nbsp;&nbsp;Confirmar&nbsp;&nbsp;', ['action' => 'confirmar', $a->avaliador->id], ['class'=>'btn btn-warning btn-xs','escape'=>false]);
                            }elseif($a->avaliador->ano_aceite==date('Y') && $a->avaliador->ano_convite==date('Y') ){
                                print '<span class="btn btn-success btn-xs active">Confirmado</span>';
                            }elseif($a->avaliador->ano_aceite!=date('Y') && $a->avaliador->ano_convite!=date('Y') ){
                                print $this->Form->postLink('Adicionar à Lista', ['action' => 'convidar', $a->avaliador->id, 'R', $a->avaliador->unidade_id], ['class'=>'btn btn-danger btn-xs','escape'=>false]);

                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="pagination">
    <?= $this->Paginator->numbers() ?>
    <?= $this->Paginator->prev('« Previous') ?>
    <?= $this->Paginator->next('Next »') ?>
    <?= $this->Paginator->counter() ?>
</div>
<?php }?> 

