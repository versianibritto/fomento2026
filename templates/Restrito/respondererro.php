<div class="container-fluid p-1 pt-1">
    <div class="row mb-3">
        <div class="col-12">
            <h4>Responder Erro #<?= h($erro->id) ?></h4>
            <p class="text-muted mb-0">Classifique e registre a resposta.</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>Usuario:</strong>
                    <?php if (!empty($erro->usuario_id)): ?>
                        <?= $this->Html->link(
                            h($erro->usuario_nome ?? 'Usuario'),
                            ['controller' => 'Users', 'action' => 'ver', $erro->usuario_id],
                            ['target' => '_blank', 'rel' => 'noopener']
                        ) ?>
                    <?php else: ?>
                        <?= h($erro->usuario_nome ?? '-') ?>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <strong>Email:</strong>
                    <?= !empty($erro->usuario_email)
                        ? h($erro->usuario_email)
                        : '<span class="badge bg-danger">não informado</span>' ?>
                </div>
            </div>
            <div class="row mt-2 mb-3">
                <div class="col-md-6">
                    <strong>Email Alternativo:</strong>
                    <?= !empty($erro->usuario_email_alternativo)
                        ? h($erro->usuario_email_alternativo)
                        : '<span class="badge bg-danger">não informado</span>' ?>
                </div>
                <div class="col-md-6">
                    <strong>Email Contato:</strong>
                    <?= !empty($erro->usuario_email_contato)
                        ? h($erro->usuario_email_contato)
                        : '<span class="badge bg-danger">não informado</span>' ?>
                </div>
            </div>
            <div class="row mt-2 mb-4">
                <div class="col-md-12">
                    <strong>URL:</strong> <?= h($erro->url ?? '-') ?>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12">
                    <strong>Mensagem:</strong> <?= h($erro->mensagem ?? '-') ?>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12">
                    <strong>Arquivo:</strong> <?= h($erro->arquivo ?? '-') ?>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12">
                    <strong>Linha:</strong> <?= h($erro->linha ?? '-') ?>
                </div>
            </div>
        </div>
    </div>

    <?= $this->Form->create($erro) ?>
        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <?= $this->Form->control('tipo', [
                            'label' => 'Tipo',
                            'options' => $tiposDisponiveis,
                            'empty' => 'Selecione',
                            'class' => 'form-control',
                            'required' => true,
                            'value' => $tipoSelecionado ?? '',
                        ]) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $this->Form->control('classificacao_id', [
                            'label' => 'Classificacao',
                            'options' => $classificacoesOptions,
                            'empty' => 'Selecione',
                            'class' => 'form-control',
                            'required' => true,
                        ]) ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <?= $this->Form->control('resposta', [
                            'label' => 'Resposta',
                            'type' => 'textarea',
                            'rows' => 4,
                            'class' => 'form-control',
                            'required' => true,
                        ]) ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <?= $this->Form->control('email_copia', [
                            'label' => 'Copia do Email',
                            'type' => 'textarea',
                            'rows' => 2,
                            'class' => 'form-control',
                        ]) ?>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <?= $this->Form->button('Salvar', ['class' => 'btn btn-primary']) ?>
                    <?= $this->Html->link('Voltar', ['action' => 'erros'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            </div>
        </div>
    <?= $this->Form->end() ?>
</div>

<script>
    (function () {
        var tipoSelect = document.getElementById('tipo');
        var classSelect = document.getElementById('classificacao-id');
        if (!tipoSelect || !classSelect) return;

        function filterClassificacoes() {
            var tipo = tipoSelect.value;
            var options = classSelect.querySelectorAll('option');
            options.forEach(function (opt) {
                if (opt.value === '') return;
                var optTipo = opt.getAttribute('data-tipo');
                var show = !tipo || optTipo === tipo;
                opt.disabled = !show;
                opt.style.display = show ? '' : 'none';
            });

            if (classSelect.value) {
                var selected = classSelect.options[classSelect.selectedIndex];
                if (selected && selected.disabled) {
                    classSelect.value = '';
                }
            }
        }

        tipoSelect.addEventListener('change', filterClassificacoes);
        filterClassificacoes();
    })();
</script>
