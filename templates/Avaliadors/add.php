<?=$this->Form->create($avaliador, ['id'=>'aval']);?>

    <h3>CADASTRAR NOVO(A) AVALIADOR(A)</h3>
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-3">
                        <?=$this->Form->control('cpf', ['label'=>'CPF do(a) Avaliador(a) (apenas números)', 'class'=>'form-control', 'required', 'SIZE'=>11, 'MAXLENGTH'=>11]);?>
                        <?=$this->Form->control('bolsista', ['type'=>'hidden']);?>
                    </div>
                    <div class="col-md-9">
                        <div class="input text">
                            <label for="nome">Nome do(a) Avaliador(a)</label>
                            <input type="text" name="nome" class="form-control" id="nome">
                            <small class="text-danger" id="errobolsista" style="display: none;">Error message</small>
                        </div>
                    </div>
                    
                </div>  
                <div class="row mb-2">
                    <?php if ($tipo=='R'){ ?>
                        <div class="col-md-3">
                            <div class="input select">
                                <?= $this->Form->control('unidade', [
                                    'label' => 'Unidade de Avaliação RAIC',
                                    'type' => 'select',
                                    'options' => $unidades,
                                    'empty' => '- Selecione -',
                                    'class' => 'form-control',
                                    'required'
                                ]); ?>
                            </div>
                        </div>
                    <?php }?>

                    <?php if ($tipo=='N'){ ?>

                        <div class="col-md-3">
                            <div class="input select">
                                <?= $this->Form->control('editai_id', [
                                    'label' => 'Edital de Avaliação',
                                    'type' => 'select',
                                    'options' => $ed_vigentes,
                                    'empty' => '- Selecione -',
                                    'class' => 'form-control',
                                    'required'
                                ]); ?>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="col-md-9">
                        <div class="input text">
                            <label for="email">E-mail do(a) Avaliador(a)</label>
                            <input type="text" name="email" class="form-control" id="email">
                        </div>
                    </div>
                </div>                
            </div>
        </div>
    </div>

    
    <div class="text-center p-5">
        <?=$this->Form->button(' Gravar',['class'=>'btn btn-primary']);?>
        <?= $this->Html->link(" Voltar ", ['controller'=>'Index', 'action' => 'dashboard'], ['class'=>'btn btn-danger pull-right','style'=>'margin-right:10px']) ?>

    </div>
<?=$this->Form->end();?>


<style>
    /* Estilizando o overlay de carregamento */
    #loading-overlay {
        display: none; /* Escondido por padrão */
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }

    /* Spinner */
    #loading-spinner {
        width: 80px;
        height: 80px;
        border: 8px solid #f3f3f3;
        border-top: 8px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
<div id="loading-overlay">
    <div id="loading-spinner"></div>
</div>

    

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script>

    $(document).ready(function() {
        $("form").on("submit", function() {
            $("#loading-overlay").fadeIn(); // Exibe o loading
        });
    });

    $(document).ready(function() {
        $('#aval')[0].reset(); // Reseta os campos do formulário
    });
    
    $(document).on('keyup','#cpf',function(){
        obj = $(this);
        if(obj.val().length >= 11){
            $.ajax({
                type: "POST",
                url: "<?=$this->Url->build(['controller'=>'Users', 'action'=>'getbolsistabycpf'])?>",
                async: false,
                data: {cpf: obj.val()},
                dataType: "json",
                beforeSend: function(xhr){
                    xhr.setRequestHeader('X-CSRF-Token', <?=json_encode($this->request->getAttribute('csrfToken'));?>);
                },
                success: function(json){
                    if(!json.error) {
                        $('#nome').val((json.data.nome_social != null && json.data.nome_social != "") ? json.data.nome_social : json.data.nome).attr("disabled", "disabled").removeAttr("required");
                        if(json.data.email != null && json.data.email.trim() != "") {
                            $('#email').val(json.data.email).attr("disabled", "disabled").removeAttr("required");
                        } else {
                            $('#email').val("").removeAttr("disabled").attr("required", "required");
                        }
                        $('#bolsista').val(json.data.id);
                        $('#errobolsista').hide();
                    } else {
                        $('#nome').val("").removeAttr("disabled").attr("required", "required");
                        $('#email').val("").removeAttr("disabled").attr("required", "required");
                        $('#bolsista').val("");
                        $('#errobolsista').html(json.message);
                        $('#errobolsista').show();
                    }
                }
            });
        }else{
            $('#nome').val(null);
            $('#email').val(null);


        }
    });

    
</script>
