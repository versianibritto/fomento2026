<div class="row">
    <div class="col-12">
        <h3 class="text-default d-inline-block" data-bs-toggle="tooltip" title="Lista LOGS">
            Listagem de Acessos
        </h3>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <?= $this->Form->create(null, ['type' => 'get']) ?>
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <?= $this->Form->label('mes', 'Mes') ?>
                <?= $this->Form->select('mes', [
                    '' => 'Todos',
                    '1' => 'Janeiro',
                    '2' => 'Fevereiro',
                    '3' => 'Marco',
                    '4' => 'Abril',
                    '5' => 'Maio',
                    '6' => 'Junho',
                    '7' => 'Julho',
                    '8' => 'Agosto',
                    '9' => 'Setembro',
                    '10' => 'Outubro',
                    '11' => 'Novembro',
                    '12' => 'Dezembro',
                ], ['class' => 'form-control', 'value' => $mes ?? '']) ?>
            </div>
            <div class="col-md-3">
                <?= $this->Form->label('ano', 'Ano') ?>
                <?php
                $anoAtual = (int)date('Y');
                $anos = ['' => 'Todos'];
                for ($a = $anoAtual; $a >= $anoAtual - 6; $a--) {
                    $anos[(string)$a] = (string)$a;
                }
                ?>
                <?= $this->Form->select('ano', $anos, ['class' => 'form-control', 'value' => $ano ?? '']) ?>
            </div>
            <div class="col-md-4">
                <?= $this->Form->button('Filtrar', ['class' => 'btn btn-primary']) ?>
                <?= $this->Html->link('Limpar', ['action' => 'veracessos', $this->request->getParam('pass.0')], ['class' => 'btn btn-outline-secondary ms-2']) ?>
            </div>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>

<?php if($listas==null) { ?>
    <div class="col-12">
        <div class="bg-info px-5 p-3 rounded mb-2 text-center" id="aviso-bolsista">
            Não há registros de log para este usuario após a implantação em 28/06/25. 
        </div>            
    </div>  
<?php } if($listas!=null)  {?>

    <?php if($listas->count()==0) { ?>
        <div class="col-12">
            <div class="bg-warning px-5 p-3 rounded mb-2 text-center" id="aviso-bolsista">
                #0 Não há registros de log para este usuario após a implantação em 28/06/25. 
            </div>            
        </div>  
    <?php }?>
    
    

    <div class="col-12 d-flex">
        <div class="card flex-fill">
            <table class="table table-hover my-0">
                <thead>
                    <tr>
                        <th>id</th>
                        <th>Data</th>
                        <th>Nome</th>
                        <th>email</th>
                        <th>email secundário</th>
                        <th>Ação</th>
                        <th>Acesso</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($listas as $b) { ?>
                    <tr>
                        <td><?=$b->id ?></td>
                        <td><?=($b->created==null?'Não Informado':(date("d/m/Y \à\s H:i", strtotime($b->created))))?></td>
                        <td><?=$b->nome?></td>
                        <td><?=$b->email==null?'N/A':$b->email?></td>
                        <td><?=$b->email_alternativo==null?'N/A':$b->email_alternativo?></td>
                        <td><?=$b->acao?></td>
                        <td><?=$b->tipo_acesso?></td>
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
