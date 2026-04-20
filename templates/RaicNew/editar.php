<?php
$cpfBolsista = preg_replace('/\D+/', '', (string)($raic->usuario->cpf ?? ''));
$cpfOrientador = preg_replace('/\D+/', '', (string)($raic->orientadore->cpf ?? ''));
?>

<?= $this->Form->create($raic, ['type' => 'file', 'id' => 'raic-editar']); ?>
<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <div class="p-3 mb-4 bg-light border rounded text-center">
                <h3 class="mb-2">Editar RAIC</h3>
                <div class="fw-semibold">Registro #<?= (int)$raic->id ?></div>
                <div class="text-muted mt-1">Atualize os dados permitidos desta RAIC.</div>
            </div>

            <?php if ($ehTi): ?>
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
                                    'value' => $cpfBolsista,
                                    'required',
                                    'SIZE' => 11,
                                    'MAXLENGTH' => 11,
                                ]); ?>
                                <?= $this->Form->control('bolsista', [
                                    'type' => 'hidden',
                                    'value' => (int)$raic->usuario_id,
                                ]); ?>
                            </div>
                            <div class="col-md-6">
                                <div class="input text">
                                    <label for="nome">Nome do(a) bolsista</label>
                                    <input type="text" name="nome" class="form-control" id="nome" value="<?= h((string)($raic->usuario->nome ?? '')) ?>">
                                    <small class="text-danger" id="errobolsista" style="display: none;">Error message</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input text">
                                    <label for="email">E-mail do(a) bolsista</label>
                                    <input type="text" name="email" class="form-control" id="email" value="<?= h((string)($raic->usuario->email ?? '')) ?>">
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
                                    'value' => $cpfOrientador,
                                    'required',
                                    'SIZE' => 11,
                                    'MAXLENGTH' => 11,
                                ]); ?>
                                <?= $this->Form->control('orientador', [
                                    'type' => 'hidden',
                                    'value' => (int)$raic->orientador,
                                ]); ?>
                            </div>
                            <div class="col-md-6">
                                <div class="input text">
                                    <label for="nome2">Nome do(a) orientador(a)</label>
                                    <input type="text" name="nome2" class="form-control" id="nome2" value="<?= h((string)($raic->orientadore->nome ?? '')) ?>">
                                    <small class="text-danger" id="erroorientador" style="display: none;">Error message</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input text">
                                    <label for="email2">E-mail do(a) orientador(a)</label>
                                    <input type="text" name="email2" class="form-control" id="email2" value="<?= h((string)($raic->orientadore->email ?? '')) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card bg-light border-0 mb-3">
                    <div class="card-body">
                        <h4 class="mb-3">Vínculos da RAIC</h4>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="text-muted small">Bolsista</div>
                                <div class="fw-semibold"><?= h((string)($raic->usuario->nome ?? 'Não informado')) ?></div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small">Orientador</div>
                                <div class="fw-semibold"><?= h((string)($raic->orientadore->nome ?? 'Não informado')) ?></div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small">Unidade</div>
                                <div class="fw-semibold"><?= h((string)($raic->unidade->sigla ?? 'Não informada')) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card bg-light border-0 mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h4 class="mb-0">Dados da RAIC</h4>
                    </div>

                    <div class="row g-3">
                        <?php if ($ehTi): ?>
                            <div class="col-md-4">
                                <?= $this->Form->control('unidade_id', [
                                    'label' => 'Unidade',
                                    'class' => 'form-control',
                                    'options' => $unidades,
                                    'empty' => 'Selecione',
                                    'value' => (int)$raic->unidade_id,
                                    'required',
                                ]) ?>
                            </div>
                            <div class="col-md-8">
                                <div class="text-muted small">Edital</div>
                                <div class="fw-semibold"><?= h((string)($raic->editai->nome ?? ('#' . (int)$raic->editai_id))) ?></div>
                            </div>
                        <?php endif; ?>
                        <div class="col-12">
                            <?= $this->Form->control('titulo', [
                                'label' => 'Título do subprojeto',
                                'type' => 'text',
                                'class' => 'form-control',
                                'value' => (string)($raic->titulo ?? ''),
                                'required',
                            ]); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-light border-0 mb-4">
                <div class="card-body">
                    <h4 class="mb-1">Relatório</h4>
                    <p class="text-muted small mt-0 mb-3">
                        Envie um novo arquivo apenas se desejar substituir o relatório atual.
                    </p>

                    <?php if (!empty($anexoRelatorio?->anexo)): ?>
                        <label class="form-label d-block">Relatório (PDF)</label>
                        <div class="anexo-arquivo-atual border rounded bg-white p-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between gap-2 flex-nowrap">
                                <div class="small text-muted text-truncate">
                                    <?= h((string)$anexoRelatorio->anexo) ?>
                                </div>
                                <div class="d-inline-flex align-items-center gap-1 flex-nowrap">
                                    <a href="/uploads/anexos/<?= h((string)$anexoRelatorio->anexo) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                        <i class="fa fa-download"></i>
                                    </a>
                                    <label for="parcial" class="btn btn-light border btn-sm py-0 px-2 mb-0" title="Alterar">
                                        <i class="fa fa-edit"></i>
                                    </label>
                                </div>
                            </div>
                            <input
                                id="parcial"
                                name="anexos[13]"
                                type="file"
                                class="d-none"
                            >
                        </div>
                    <?php else: ?>
                        <?= $this->Form->control('anexos[13]', [
                            'label' => 'Novo relatório (PDF) <span class="text-danger small">(Max 2M)</span>',
                            'id' => 'parcial',
                            'type' => 'file',
                            'class' => 'form-control',
                            'escape' => false,
                        ]) ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-center gap-2 pt-2">
                <?= $this->Form->button('Salvar alterações', ['class' => 'btn btn-primary px-4']); ?>
                <?= $this->Html->link('Cancelar', ['controller' => 'RaicNew', 'action' => 'ver', $raic->id], ['class' => 'btn btn-outline-danger px-4']) ?>
            </div>
        </div>
    </div>
</div>
<?= $this->Form->end(); ?>

<style>
    #loading-overlay {
        display: none;
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
            $("#loading-overlay").fadeIn();
        });
    });

    $(document).on('keyup', '#cpf', function() {
        obj = $(this);
        if (obj.val().length >= 11) {
            $.ajax({
                type: "POST",
                url: "<?= $this->Url->build(['controller' => 'Users', 'action' => 'getbolsistabycpf']) ?>",
                async: false,
                data: {cpf: obj.val()},
                dataType: "json",
                beforeSend: function(xhr){
                    xhr.setRequestHeader('X-CSRF-Token', <?= json_encode($this->request->getAttribute('csrfToken')); ?>);
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
        }
    });

    $(document).on('keyup', '#cpf-orientador', function() {
        obj = $(this);
        if (obj.val().length >= 11) {
            $.ajax({
                type: "POST",
                url: "<?= $this->Url->build(['controller' => 'Users', 'action' => 'getbolsistabycpf']) ?>",
                async: false,
                data: {cpf: obj.val()},
                dataType: "json",
                beforeSend: function(xhr){
                    xhr.setRequestHeader('X-CSRF-Token', <?= json_encode($this->request->getAttribute('csrfToken')); ?>);
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
        }
    });

    var parcial = document.getElementById("parcial");
    if (parcial) {
        parcial.addEventListener("change", function(e) {
            if (!parcial.files || !parcial.files[0]) {
                return;
            }
            var size = parcial.files[0].size;
            if(size > 2097152) {
                alert('Arquivo maior que 2Mb. Selecione outro pois este tamanho nao e permitido.');
                parcial.value = "";
            }
            e.preventDefault();
        });
    }
</script>
