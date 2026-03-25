<?php
$titulo = $isEdicao ? 'Editar Calendário' : 'Cadastrar Calendário';
$textoBotao = $isEdicao ? 'Salvar alterações' : 'Cadastrar calendário';
$diaValor = !empty($calendario->dia) ? $calendario->dia->format('Y-m-d') : '';
?>
<section class="mt-n3">
    <div class="card card-primary card-outline mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h2 class="mb-0"><?= h($titulo) ?></h2>
                <div class="d-flex gap-2">
                    <?= $this->Html->link('Lista de calendários', ['controller' => 'Restrito', 'action' => 'calendariosLista'], ['class' => 'btn btn-outline-secondary']) ?>
                    <?= $this->Html->link('Voltar', ['controller' => 'Restrito', 'action' => 'index'], ['class' => 'btn btn-outline-dark']) ?>
                </div>
            </div>
            <hr>

            <?php if ($isEdicao): ?>
                <?= $this->Form->create($calendario, ['class' => 'row g-3']) ?>
                    <div class="col-md-4">
                        <?= $this->Form->control('dia', [
                            'label' => 'Dia',
                            'type' => 'date',
                            'class' => 'form-control',
                            'value' => $diaValor,
                            'required' => true,
                            'templates' => ['inputContainer' => '{{content}}'],
                        ]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $this->Form->control('tipo', [
                            'label' => 'Tipo',
                            'type' => 'select',
                            'options' => $tipos,
                            'empty' => 'Selecione',
                            'class' => 'form-select',
                        ]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $this->Form->control('descricao', [
                            'label' => 'Descrição',
                            'class' => 'form-control',
                            'maxlength' => 100,
                        ]) ?>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <?= $this->Form->button($textoBotao, ['class' => 'btn btn-success']) ?>
                    </div>
                <?= $this->Form->end() ?>
            <?php else: ?>
                <?= $this->Form->create(null, ['id' => 'calendarios-form']) ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0" id="calendarios-table" style="min-width: 980px;">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 180px;">Dia</th>
                                    <th style="min-width: 220px;">Tipo</th>
                                    <th style="min-width: 320px;">Descrição</th>
                                    <th class="text-center" style="min-width: 60px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr data-index="0">
                                    <td>
                                        <input type="date" name="calendarios[0][dia]" class="form-control" required>
                                    </td>
                                    <td>
                                        <select name="calendarios[0][tipo]" class="form-select">
                                            <option value="">Selecione</option>
                                            <?php foreach ($tipos as $valor => $rotulo): ?>
                                                <option value="<?= h($valor) ?>"><?= h($rotulo) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="calendarios[0][descricao]" class="form-control" maxlength="100">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-row" disabled title="Remover">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-12 d-flex justify-content-between align-items-center mt-3">
                        <button type="button" class="btn btn-outline-primary" id="add-calendario">Adicionar linha</button>
                        <?= $this->Form->button($textoBotao, ['class' => 'btn btn-success']) ?>
                    </div>
                <?= $this->Form->end() ?>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php if (!$isEdicao): ?>
<script>
(function () {
    var tableBody = document.querySelector('#calendarios-table tbody');
    var addButton = document.getElementById('add-calendario');
    var tiposOptionsHtml = <?=
        json_encode(
            '<option value="">Selecione</option>' .
            implode('', array_map(
                fn($valor, $rotulo) => '<option value="' . h($valor) . '">' . h($rotulo) . '</option>',
                array_keys($tipos),
                array_values($tipos)
            ))
        )
    ?>;

    function updateRemoveButtons() {
        var buttons = tableBody.querySelectorAll('.remove-row');
        buttons.forEach(function (btn, idx) {
            btn.disabled = (buttons.length === 1 && idx === 0);
        });
    }

    function buildRow(index) {
        var row = document.createElement('tr');
        row.setAttribute('data-index', index);
        row.innerHTML = `
            <td><input type="date" name="calendarios[${index}][dia]" class="form-control" required></td>
            <td><select name="calendarios[${index}][tipo]" class="form-select">${tiposOptionsHtml}</select></td>
            <td><input type="text" name="calendarios[${index}][descricao]" class="form-control" maxlength="100"></td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm remove-row" title="Remover">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        return row;
    }

    if (addButton && tableBody) {
        addButton.addEventListener('click', function () {
            var nextIndex = tableBody.querySelectorAll('tr').length;
            tableBody.appendChild(buildRow(nextIndex));
            updateRemoveButtons();
        });

        tableBody.addEventListener('click', function (event) {
            var button = event.target.closest('.remove-row');
            if (!button) {
                return;
            }
            var row = button.closest('tr');
            if (row) {
                row.remove();
                updateRemoveButtons();
            }
        });

        updateRemoveButtons();
    }
})();
</script>
<?php endif; ?>
