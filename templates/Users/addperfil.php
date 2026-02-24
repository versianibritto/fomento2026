
<?=$this->Form->create($user)?>

<div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h4>Cadastro de perfil do usuário <?=$user->nome?>, CPF <?=$user->cpf?></h4>
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4"> 
                                <?=$this->Form->control('yoda', ['label'=>'Gestão de Fomento','options' => ['0'=>'Não', '1'=>'Sim'], 'empty' => ' - Selecione - ', 'class' => 'form-control', 'required'=>true]) ?>
                            </div>
                            <div class="col-md-4"> 
                                <?=$this->Form->control('jedi', ['label'=>'Coordenaão de unidade', 'class' => 'form-control', 'required'=>false]) ?>
                            </div>
                            <div class="col-md-4"> 
                                <?=$this->Form->control('padauan', ['label'=>'Coordenaão de programa', 'class' => 'form-control', 'required'=>false]) ?>
                            </div>
                            
                           
                        </div>
                        
                    </div>                   
                </div>   
                
            </div>              
        </div>
    </div>

<div class="row mb-3">
    <div class="col-md-2">
        <?=$this->Form->button("Gravar", ['class' => 'btn btn-success'])?>
    </div>
</div>
<?=$this->Form->end()?>

