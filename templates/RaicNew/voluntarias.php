<?= $this->Form->create($raic, ['type' => 'file', 'id' => 'voluntarias']); ?>
<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <div class="p-3 mb-4 bg-light border rounded text-center">
                <h3 class="mb-2">Cadastro de Aluno na RAIC</h3>
                <div class="fw-semibold">RAIC Outras Agências de Fomento / Alunos Egressos</div>
                <div class="text-muted mt-1">Informe bolsista, orientador e dados da RAIC para concluir o cadastro manual.</div>
            </div>

            <div class="card bg-light border-0 mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h4 class="mb-0">Dados do(a) bolsista</h4>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <?= $this->Form->control('cpf', [
                                'label' => 'CPF do(a) bolsista (apenas números)',
                                'class' => 'form-control',
                                'required',
                                'SIZE' => 11,
                                'MAXLENGTH' => 11,
                            ]); ?>
                            <?= $this->Form->control('bolsista', ['type' => 'hidden']); ?>
                        </div>
                        <div class="col-md-6">
                            <div class="input text">
                                <label for="nome">Nome do(a) bolsista</label>
                                <input type="text" name="nome" class="form-control" id="nome">
                                <small class="text-danger" id="errobolsista" style="display: none;">Error message</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="input text">
                                <label for="email">E-mail do(a) bolsista</label>
                                <input type="text" name="email" class="form-control" id="email">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-light border-0 mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h4 class="mb-0">Dados do(a) orientador(a)</h4>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <?= $this->Form->control('cpf_orientador', [
                                'label' => 'CPF do(a) orientador(a) (apenas números)',
                                'class' => 'form-control',
                                'required',
                                'SIZE' => 11,
                                'MAXLENGTH' => 11,
                            ]); ?>
                            <?= $this->Form->control('orientador', ['type' => 'hidden']); ?>
                        </div>
                        <div class="col-md-6">
                            <div class="input text">
                                <label for="nome2">Nome do(a) orientador(a)</label>
                                <input type="text" name="nome2" class="form-control" id="nome2">
                                <small class="text-danger" id="erroorientador" style="display: none;">Error message</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="input text">
                                <label for="email2">E-mail do(a) orientador(a)</label>
                                <input type="text" name="email2" class="form-control" id="email2">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-light border-0 mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h4 class="mb-0">Dados da RAIC</h4>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <?= $this->Form->control('editai_id', [
                                'label' => 'RAIC',
                                'class' => 'form-control',
                                'options' => $editais,
                                'empty' => 'Selecione',
                                'required',
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $this->Form->control('unidade_id', [
                                'label' => 'Unidade',
                                'class' => 'form-control',
                                'options' => $unidades,
                                'empty' => 'Selecione',
                                'required',
                            ]) ?>
                        </div>
                        <div class="col-12">
                            <?= $this->Form->control('titulo', [
                                'label' => 'Título do subprojeto',
                                'type' => 'text',
                                'class' => 'form-control',
                                'required',
                            ]); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-light border-0 mb-4">
                <div class="card-body">
                    <h4 class="mb-1">Relatório</h4>
                    <p class="text-muted small mt-0 mb-4">
                        Carregue o relatório de atividades do(a) bolsista em PDF. Este anexo será considerado na avaliação.
                    </p>

                    <?= $this->Form->control('anexos[13]', [
                        'label' => 'Relatório (PDF) <span class="text-danger small">(Max 2M)</span>',
                        'id' => 'parcial',
                        'required',
                        'type' => 'file',
                        'class' => 'form-control',
                        'escape' => false,
                    ]) ?>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-center gap-2 pt-2">
                <?= $this->Form->button('Gravar', ['class' => 'btn btn-primary px-4']); ?>
                <?= $this->Html->link('Voltar', ['controller' => 'Index', 'action' => 'dashboard'], ['class' => 'btn btn-outline-danger px-4']) ?>
            </div>
        </div>
    </div>
</div>
<?= $this->Form->end(); ?>


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
    const unidadesOriginaisVoluntarias = <?= json_encode($unidades); ?>;
    const mapaUnidadesPorEditalVoluntarias = <?= json_encode($mapaUnidadesPorEdital); ?>;

    function atualizarUnidadesPorEditalVoluntarias() {
        const editalId = $('#editai-id').val();
        const $selectUnidade = $('#unidade-id');
        const valorAtual = $selectUnidade.val();
        const opcoes = (editalId && mapaUnidadesPorEditalVoluntarias[editalId])
            ? mapaUnidadesPorEditalVoluntarias[editalId]
            : unidadesOriginaisVoluntarias;

        $selectUnidade.empty();
        $('<option>').val('').text('Selecione').appendTo($selectUnidade);

        Object.entries(opcoes).forEach(function([id, label]) {
            $('<option>').val(id).text(label).appendTo($selectUnidade);
        });

        if (valorAtual && Object.prototype.hasOwnProperty.call(opcoes, valorAtual)) {
            $selectUnidade.val(valorAtual);
        } else {
            $selectUnidade.val('');
        }
    }

    $(document).ready(function() {
        $("form").on("submit", function() {
            $("#loading-overlay").fadeIn(); // Exibe o loading
        });
        atualizarUnidadesPorEditalVoluntarias();
    });

    $(document).on('change', '#editai-id', function() {
        atualizarUnidadesPorEditalVoluntarias();
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


    $(document).on('keyup','#cpf-orientador',function(){
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
                        $('#nome2').val((json.data.nome_social != null && json.data.nome_social != "") ? json.data.nome_social : json.data.nome).attr("disabled", "disabled").removeAttr("required");
                        if(json.data.email != null && json.data.email.trim() != "") {
                            $('#email2').val(json.data.email).attr("disabled", "disabled").removeAttr("required");
                        } else {
                            $('#email2').val("").removeAttr("disabled").attr("required", "required");
                        }
                        $('#orientador').val(json.data.id);
                        $('#erroorientador').hide();
                    } else {
                        $('#nome2').val("").removeAttr("disabled").attr("required", "required");
                        $('#email2').val("").removeAttr("disabled").attr("required", "required");
                        $('#orientador').val("");
                        $('#erroorientador').html(json.message);
                        $('#erroorientador').show();
                    }
                }
            });
        }else{
            $('#nome2').val(null);
            $('#email2').val(null);


        }
    });

    var parcial = document.getElementById("parcial");
    parcial.addEventListener("change", function(e) {
        var size = parcial.files[0].size;
        if(size > 2097152) { //1MB                    
        alert('Arquivo maior que 2Mb.  Selecione outro pois este tamanho nao é permitido.'); //Acima do limite
        parcial.value = ""; //Limpa o campo          
        }
        e.preventDefault();
    });
</script>
