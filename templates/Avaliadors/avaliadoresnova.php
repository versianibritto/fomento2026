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
    title="Lista dos Avaliadores Bolsa Nova separados por edital. Serão trazido apenas as informações dos editais que ainda estão no período de avaliação" class="d-inline-block">
    Listagem de Avaliadores Nova por Edital <i class="fa fa-info-circle"></i>
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
                <?=$this->Form->create(null, ['url'=>['controller'=>'Avaliadors','action'=>'avaliadoresnova'], 'class' => 'row row-cols-md-auto align-items-center'])?>
                    <div class="input-group">
                        <?=$this->Form->control('edital',['label'=>'Edital','options'=>$editais,'empty'=>'- Selecione -','class'=>'form-control','style'=>'height:34px', 'required'])?>

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
                    <?=$this->Html->link('Clique para adicionar um Novo Avaliador', ['controller' => 'Avaliadors', 'action' => 'add', 'N'], ['class' => 'btn btn-info btn-xs', 'escape' => false]);?>
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
                        $nome = explode(" ", $a->usuario->nome);
                        $unidade = ($a->usuario->unidade_id!=null?$a->usuario->unidade->sigla:'N/A');
                        $ref = ($nome[0] . ' ' . end($nome) . ' / '.$unidade);

                   ?>
                    <tr>
                        
                        <td>
                            <?=$this->Html->link($ref, ['controller' => 'Users', 'action' => 'ver', $a->usuario_id], ['escape' => false,'target'=>'_blank']);?>
                        </td>
                        <td><?=($a->ano_convite) ?></td>
                        <td><?=($a->editai_id!=null?$a->editai->nome:'N/A') ?></td>

                        <td><?=(isset($a->grandes_area->nome)?$a->grandes_area->nome:' N/A ') ?></td>
                        <td><?=(isset($a->area->nome)?$a->area->nome:' N/A ') ?></td>

                        
                        <td>
                            <?= $this->Html->link(
                                    '<i class="fas fa-file-alt me-1"></i>Alterar áreas',
                                    ['controller' => 'Avaliadors', 'action' => 'editArea', $a->id],
                                    ['class' => 'btn btn-sm btn-outline-primary rounded-pill shadow-sm', 'escape' => false]
                                )
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

