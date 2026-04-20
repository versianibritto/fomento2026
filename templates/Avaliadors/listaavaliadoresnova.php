<h3>Listagem de Avalidores e Notas Lançadas - Bolsa Nova</h3>
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <?=$this->Form->create(null, ['url'=>['controller'=>'Avaliadors','action'=>'listaavaliadoresnova'], 'class' => 'row row-cols-md-auto align-items-center'])?>
                    <div class="input-group">
                        <?=$this->Form->control('editai_id',['label'=>'Edital de interesse','options'=>$editais,'empty'=>'- Selecione uma opção -','class'=>'form-control','style'=>'height:34px', 'required'])?>
                        <?=$this->Form->control('situacao',['label'=>'Situação','options'=>['F'=>'Notas Lançadas', 'E'=>'Aguardando Lançamento'],'empty'=>'- Todos -','class'=>'form-control','style'=>'height:34px'])?>

                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary mb-2 px-3"><i class="fa fa-search me-3"></i> Buscar</button>
                        <?= $this->Html->link('Limpar filtros', ['Controller'=>'Avaliadors','action' => 'limpar', 'buscaAvnova', 'listaavaliadoresnova'],['class'=>'btn btn-danger btn-xs pull-right']) ?>

                    </div>
                <?=$this->Form->end()?> 
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <?=$this->Form->create(null,['url'=>['controller'=>'Relatorios','action'=>'listaavaliadoresnova']]);?>
                <div class="input-group">
                    <div class="input-group-btn search-panel">
                    <?=$this->Form->control('editai_id',['label'=>'Edital de interesse','options'=>$editais,'empty'=>'- Selecione uma opção -','class'=>'form-control','style'=>'height:34px', 'required'])?>
                    <?=$this->Form->control('situacao',['label'=>'Situação','options'=>['F'=>'Notas Lançadas', 'E'=>'Aguardando Lançamento'],'empty'=>'- Todos -','class'=>'form-control','style'=>'height:34px'])?>

                    </div>
                    
                    <span class="input-group-btn pt-1">
                        <button class="btn btn-info ms-2 mt-3">
                            <i class="fa fa-file-excel"></i> Exportar excel
                        </button>
                    </span>
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
                        <th>Inscrição</th>
                        <th>Orientador</th>
                        <th>Bolsista</th>
                        <th>Unidade</th>
                        <th>Programa/Edital</th>
                        <th>Avaliador</th>
                        <th>Situação</th>
                        <th>Nota</th>
                        <th></th>

                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach($listas as $b) {
                        $nome = explode(" ", $b->avaliador->usuario->nome);
                        $nome_b = explode(" ", $b->projeto_bolsista->usuario->nome);
                        $nome_o = explode(" ", $b->projeto_bolsista->orientadore->nome);


                        $caminho=($b->projeto_bolsista->editai->tipo=='J'? 'detalhepdj':'detalhesubprojeto');
                    ?>
                    <tr style="<?=(($b->situacao=='F'?'color:#090!important':''))?>">
                        <td><?=$this->Html->link($b->bolsista.'   <i class="fas fa-eye"></i>', ['controller'=>'Projetos','action' => $caminho, $b->bolsista], ['class' => 'btn btn-xs btn-info mr-1', 'escape' => false])?></td>
                        <td><?=$nome_o[0] . ' ' . end($nome_o) ?></td>
                        <td><?=$nome_b[0] . ' ' . end($nome_b) ?></td>
                        <td><?=($b->projeto_bolsista->orientadore->unidade_id==null?'Unidade do Orientador não informada':$b->projeto_bolsista->orientadore->unidade->sigla)?></td>
                        <td><?=($b->projeto_bolsista->editai_id==null?'-X-':$b->projeto_bolsista->editai->nome)?></td>
                        <td><?=$nome[0] . ' ' . end($nome).' - '.$b->ordem ?></td>
                        <td><?=($b->situacao=='F'?'Notas Lançadas':($b->situacao=='E'?'Aguardando':'x'))?></td>
                        <td><?=$b->nota ?></td>
                        <td>
                        <?php if($this->request->getAttribute('identity')['yoda'] && $b->situacao=='F'){?>
                            <?=$this->Html->link('<i class="fas fa-file-alt"></i>', ['controller'=>'Avaliadors','action' => 'vernotas', $b->id], ['class' => 'btn btn-xs btn-danger mr-1 ', 'escape' => false])?>
                        <?php }?>
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

