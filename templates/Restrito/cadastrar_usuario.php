<?php
/**
 * @var \App\View\AppView $this
 * @var array{cpf?: string, nome?: string, email?: string} $dados
 */
?>
<div class="container mt-4">
    <div class="card card-primary card-outline">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h4 class="mb-1">Cadastrar Usuário</h4>
                    <p class="text-muted mb-0">
                        Informe o CPF para consultar a base. Se não existir, preencha nome e e-mail para cadastrar.
                    </p>
                </div>
                <?= $this->Html->link(
                    'Voltar',
                    ['controller' => 'Restrito', 'action' => 'index'],
                    ['class' => 'btn btn-sm btn-outline-secondary']
                ) ?>
            </div>

            <?= $this->Form->create(null, ['class' => 'row g-3', 'id' => 'form-cadastrar-usuario-ti']) ?>
                <div class="col-md-4">
                    <?= $this->Form->control('cpf', [
                        'label' => 'CPF',
                        'class' => 'form-control',
                        'required' => true,
                        'maxlength' => 14,
                        'value' => $dados['cpf'] ?? '',
                        'id' => 'cpf',
                        'placeholder' => 'Somente números ou CPF formatado',
                    ]) ?>
                    <div class="form-text" id="cpf-status"></div>
                </div>

                <div class="col-md-5">
                    <?= $this->Form->control('nome', [
                        'label' => 'Nome',
                        'class' => 'form-control',
                        'required' => true,
                        'value' => $dados['nome'] ?? '',
                        'id' => 'nome',
                    ]) ?>
                </div>

                <div class="col-md-3">
                    <?= $this->Form->control('email', [
                        'label' => 'E-mail',
                        'type' => 'email',
                        'class' => 'form-control',
                        'required' => true,
                        'value' => $dados['email'] ?? '',
                        'id' => 'email',
                    ]) ?>
                </div>

                <div class="col-12 d-flex flex-wrap gap-2">
                    <?= $this->Form->button('Cadastrar usuário', [
                        'class' => 'btn btn-primary',
                        'id' => 'btn-cadastrar',
                    ]) ?>
                    <a href="#" class="btn btn-outline-primary d-none" id="link-usuario-existente">Abrir usuário</a>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

<script>
$(document).on('keyup change', '#cpf', function () {
    const cpf = ($(this).val() || '').replace(/\D/g, '');

    $('#link-usuario-existente').addClass('d-none').attr('href', '#');
    $('#cpf-status').removeClass('text-danger text-success text-muted').text('');

    if (cpf.length < 11) {
        $('#nome, #email').val('').prop('readonly', false);
        $('#btn-cadastrar').prop('disabled', false);
        return;
    }

    $.ajax({
        type: 'POST',
        url: "<?= $this->Url->build(['controller' => 'Restrito', 'action' => 'buscarUsuarioCpf']) ?>",
        data: { cpf: cpf },
        dataType: 'json',
        beforeSend: function (xhr) {
            xhr.setRequestHeader('X-CSRF-Token', <?= json_encode($this->request->getAttribute('csrfToken')) ?>);
            $('#cpf-status').addClass('text-muted').text('Consultando CPF...');
        },
        success: function (json) {
            $('#cpf-status').removeClass('text-danger text-success text-muted');

            if (json.error) {
                $('#nome, #email').val('').prop('readonly', false);
                $('#btn-cadastrar').prop('disabled', true);
                $('#cpf-status').addClass('text-danger').text(json.message);
                return;
            }

            if (json.exists) {
                $('#nome').val(json.data.nome || '').prop('readonly', true);
                $('#email').val(json.data.email || '').prop('readonly', true);
                $('#btn-cadastrar').prop('disabled', true);
                $('#cpf-status').addClass('text-success').text(json.message);
                $('#link-usuario-existente')
                    .removeClass('d-none')
                    .attr('href', "<?= $this->Url->build(['controller' => 'Users', 'action' => 'ver']) ?>/" + json.data.id);
                return;
            }

            $('#nome, #email').val('').prop('readonly', false);
            $('#btn-cadastrar').prop('disabled', false);
            $('#cpf-status').addClass('text-muted').text(json.message);
            $('#nome').trigger('focus');
        },
        error: function () {
            $('#cpf-status')
                .removeClass('text-success text-muted')
                .addClass('text-danger')
                .text('Erro ao consultar CPF.');
        }
    });
});
</script>
