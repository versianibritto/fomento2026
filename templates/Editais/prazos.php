<section class="mt-n3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <h2 class="mb-0">Cadastrar prazos</h2>
            <div class="text-muted mt-1">Edital: <?= h($edital->nome) ?></div>
        </div>
        <?= $this->Html->link('Voltar', ['action' => 'ver', $edital->id], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
    <hr>

    <?=$this->Form->create(null, ['class' => 'row g-3'])?>
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0" id="prazos-table" style="min-width: 1100px;">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 180px;">Tipo</th>
                                    <th style="min-width: 170px;">Início</th>
                                    <th style="min-width: 170px;">Fim</th>
                                    <th style="min-width: 140px;">Usuário ID</th>
                                    <th style="min-width: 140px;">Inscrição</th>
                                    <th style="min-width: 110px;">Tipo</th>
                                    <th class="text-center" style="min-width: 60px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr data-index="0">
                                    <td>
                                        <?=$this->Form->control('prazos.0.editais_wk_id', [
                                            'label' => false,
                                            'class' => 'form-select',
                                            'options' => $wkOptions,
                                            'empty' => 'Selecione',
                                            'required' => true,
                                        ])?>
                                    </td>
                                    <td>
                                        <?=$this->Form->control('prazos.0.inicio', [
                                            'type' => 'datetime-local',
                                            'label' => false,
                                            'class' => 'form-control',
                                            'required' => true,
                                        ])?>
                                    </td>
                                    <td>
                                        <?=$this->Form->control('prazos.0.fim', [
                                            'type' => 'datetime-local',
                                            'label' => false,
                                            'class' => 'form-control',
                                            'required' => true,
                                        ])?>
                                    </td>
                                    <td>
                                        <?=$this->Form->control('prazos.0.usuario_id', [
                                            'label' => false,
                                            'class' => 'form-control',
                                            'type' => 'number',
                                            'min' => 1,
                                            'step' => 1,
                                            'inputmode' => 'numeric',
                                        ])?>
                                    </td>
                                    <td>
                                        <?=$this->Form->control('prazos.0.inscricao', [
                                            'label' => false,
                                            'class' => 'form-control inscricao-input',
                                            'maxlength' => 300,
                                        ])?>
                                    </td>
                                    <td>
                                        <?=$this->Form->control('prazos.0.tabela', [
                                            'label' => false,
                                            'class' => 'form-select tabela-select',
                                            'options' => ['I' => 'IC', 'J' => 'PDJ'],
                                            'empty' => 'Selecione',
                                        ])?>
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
                    <button type="button" class="btn btn-outline-primary" id="add-row">Adicionar linha</button>
                    <?= $this->Form->button('Salvar prazos', ['class' => 'btn btn-success']) ?>
                </div>
    <?=$this->Form->end()?>
</section>

<script>
    (function() {
        var tableBody = document.querySelector('#prazos-table tbody');
        var addButton = document.getElementById('add-row');

        function updateRemoveButtons() {
            var buttons = tableBody.querySelectorAll('.remove-row');
            buttons.forEach(function(btn, idx) {
                btn.disabled = (buttons.length === 1 && idx === 0);
            });
        }

        function updateTabelaRequired(row) {
            var inscricaoInput = row.querySelector('.inscricao-input');
            var tabelaSelect = row.querySelector('.tabela-select');
            if (!inscricaoInput || !tabelaSelect) {
                return;
            }
            tabelaSelect.required = inscricaoInput.value.trim() !== '';
        }

        function buildRow(index) {
            var row = document.createElement('tr');
            row.setAttribute('data-index', index);
            row.innerHTML = `
                <td>
                    <select name="prazos[${index}][editais_wk_id]" class="form-select" required>
                        <option value="">Selecione</option>
                        <?php foreach ($wkOptions as $id => $nome) { ?>
                            <option value="<?= (int)$id ?>"><?= h($nome) ?></option>
                        <?php } ?>
                    </select>
                </td>
                <td><input type="datetime-local" name="prazos[${index}][inicio]" class="form-control" required></td>
                <td><input type="datetime-local" name="prazos[${index}][fim]" class="form-control" required></td>
                <td><input type="number" name="prazos[${index}][usuario_id]" class="form-control" min="1" step="1" inputmode="numeric"></td>
                <td><input type="text" name="prazos[${index}][inscricao]" class="form-control inscricao-input" maxlength="300"></td>
                <td>
                    <select name="prazos[${index}][tabela]" class="form-select tabela-select">
                        <option value="">Selecione</option>
                        <option value="I">IC</option>
                        <option value="J">PDJ</option>
                    </select>
                </td>
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
            var row = buildRow(nextIndex);
            tableBody.appendChild(row);
            updateTabelaRequired(row);
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

        tableBody.addEventListener('input', function(event) {
            if (!event.target.name || event.target.name.indexOf('[inscricao]') === -1) {
                return;
            }
            var row = event.target.closest('tr');
            if (!row) {
                return;
            }
            updateTabelaRequired(row);
        });

        updateRemoveButtons();
        tableBody.querySelectorAll('tr').forEach(function(row) {
            updateTabelaRequired(row);
        });
    })();
</script>
