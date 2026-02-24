<div class="card">
    <div class="card-body">
        <?=$this->Form->create(null, ['url'=>['controller'=>'Users','action'=>'talentos'], 'class' => 'row row-cols-md-auto align-items-center'])?>
        <div class="input-group">
                <?=$this->Form->control('nome',['label'=>'Nome','class'=>'form-control','placeholder'=>'parte do nome ...', 'maxlength' => 150])?>
                <?=$this->Form->control('curso',['label'=>'Curso (Cursando)','class'=>'form-control','placeholder'=>'parte do texto ...', 'maxlength' => 45])?>
                <!--<?=$this->Form->control('sexo',['label'=>'Gênero','options'=>$sexo,'empty'=>'- Todos -','class'=>'form-control','style'=>'height:34px'])?>-->
                <!--<?=$this->Form->control('raca',['label'=>'Raça','options'=>$racas,'empty'=>'- Todos -','class'=>'form-control','style'=>'height:34px'])?>-->
                <!--<?=$this->Form->control('deficiencia',['label'=>'Deficiência','options'=>$deficiencia,'empty'=>'- Todos -','class'=>'form-control','style'=>'height:34px'])?>-->
                <?=$this->Form->control('programa',['label'=>'Programa/Edital de interesse','options'=>['I'=>'IC Manguinhos/ENSP', 'A'=>'IC Mata Atlântica', 'M'=>'IC Maré'],'empty'=>'- Todos -','class'=>'form-control','style'=>'height:34px'])?>
            </div>
        
    
          
            <div class="col-12">
                <button type="submit" class="btn btn-primary mb-2 px-3"><i class="fa fa-search me-3"></i> Buscar</button>
                <?= $this->Form->button('Trazer todos', ['Controller'=>'Usuarios','action' => 'talentos',true],['class'=>'btn btn-danger btn-xs pull-right']) ?>

            </div>
        <?=$this->Form->end()?>        
    </div>
</div>
<div class="col-12 d-flex">
    <div class="card flex-fill">
        <table class="table table-hover my-0">
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>Curso</th>
                    <th class="d-none d-md-table-cell">Gênero</th>
                    <th class="d-none d-md-table-cell">Raças</th>
                    <th class="d-none d-md-table-cell">Deficiência</th>



                    <th class="actions">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach($usuarios as $u) {
                    $nome=explode(" ", $u->nome);

                ?>
                <tr>
                    <td class="d-none d-md-table-cell"><?=$nome[0] . ' ' . end($nome)?></td>
                    <td class="d-none d-md-table-cell"><?=($u->curso==null?'Não cadastrado':$u->curso)?></td>
                    <td class="d-none d-md-table-cell"><?=$u->sexo==null?'Não cadastrado':($sexo[$u->sexo])?></td>
                    <td class="d-none d-md-table-cell"><?=($u->raca==null?'Não cadastrado':$racas[$u->raca])?></td>
                    <td class="d-none d-md-table-cell"><?=($u->deficiencia==null?'Não cadastrado':$deficiencia[$u->deficiencia])?></td>


                    <td class="actions">
                        <?=$this->Html->link('<i class="fa fa-eye"></i>', ['action' => 'curriculo', $u->id],['class' => 'btn btn-sm btn-info', 'escape' => false])?>

                        <!--<?=$this->Html->link('<i class="fa fa-trash"></i>', '#',['class' => 'btn btn-sm btn-danger', 'escape' => false])?>-->
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
