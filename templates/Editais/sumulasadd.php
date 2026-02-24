<?php
$blocoOptionsHtml = '';
foreach ($blocos as $id => $nome) {
    $blocoOptionsHtml .= '<option value="' . h($id) . '">' . h($nome) . '</option>';
}
?>

<section class="mt-n3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h2 class="mb-0">Cadastrar sumulas</h2>
            <div class="text-muted mt-1">Edital: <?= h($edital->nome) ?></div>
        </div>
        <?= $this->Html->link('Voltar', $this->request->referer(), ['class' => 'btn btn-outline-secondary']) ?>
    </div>
    <hr class="my-4">

    <?=$this->Form->create(null, ['id' => 'sumulas-form', 'class' => 'row g-3'])?>
        <div class="col-12">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="sumulas-table" style="min-width: 1100px;">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 220px;">Bloco</th>
                            <th style="min-width: 320px;">Sumula</th>
                            <th style="min-width: 320px;">Parametro</th>
                            <th class="text-center" style="min-width: 60px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr data-index="0">
                            <td>
                                <?= $this->Form->control('sumulas.0.editais_sumula_bloco_id', [
                                    'label' => false,
                                    'type' => 'select',
                                    'options' => $blocos,
                                    'empty' => 'Selecione',
                                    'class' => 'form-select',
                                    'required' => true,
                                ]) ?>
                            </td>
                            <td>
                                <?= $this->Form->control('sumulas.0.sumula', [
                                    'label' => false,
                                    'type' => 'textarea',
                                    'class' => 'form-control',
                                    'rows' => 3,
                                    'required' => true,
                                ]) ?>
                            </td>
                            <td>
                                <?= $this->Form->control('sumulas.0.parametro', [
                                    'label' => false,
                                    'type' => 'textarea',
                                    'class' => 'form-control',
                                    'rows' => 3,
                                    'required' => true,
                                ]) ?>
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
        </div>

        <div class="col-12 d-flex justify-content-between align-items-center">
            <button type="button" class="btn btn-outline-primary" id="add-sumula">Adicionar linha</button>
            <?= $this->Form->button('Salvar sumulas', ['class' => 'btn btn-success']) ?>
        </div>
    <?=$this->Form->end()?>
</section>

<script>
    (function() {
        var tableBody = document.querySelector('#sumulas-table tbody');
        var addButton = document.getElementById('add-sumula');
        var optionsHtml = <?php echo json_encode($blocoOptionsHtml); ?>;

        function updateRemoveButtons() {
            var buttons = tableBody.querySelectorAll('.remove-row');
            buttons.forEach(function(btn, idx) {
                btn.disabled = (buttons.length === 1 && idx === 0);
            });
        }

        function buildRow(index) {
            var row = document.createElement('tr');
            row.setAttribute('data-index', index);
            row.innerHTML = `
                <td>
                    <select name="sumulas[${index}][editais_sumula_bloco_id]" class="form-select" required>
                        <option value="">Selecione</option>
                        ${optionsHtml}
                    </select>
                </td>
                <td><textarea name="sumulas[${index}][sumula]" class="form-control" rows="3" required></textarea></td>
                <td><textarea name="sumulas[${index}][parametro]" class="form-control" rows="3" required></textarea></td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-row" title="Remover">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            `;
            return row;
        }

        addButton.addEventListener('click', function() {
            var nextIndex = tableBody.querySelectorAll('tr').length;
            tableBody.appendChild(buildRow(nextIndex));
            updateRemoveButtons();
        });

        tableBody.addEventListener('click', function(event) {
            if (!event.target.classList.contains('remove-row')) {
                return;
            }
            var row = event.target.closest('tr');
            if (row) {
                row.remove();
                updateRemoveButtons();
            }
        });

        updateRemoveButtons();
    })();
</script>
