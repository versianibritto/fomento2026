<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Banca $banca
 * @var array<string, mixed> $dados
 * @var array<string, mixed> $opcoes
 * @var array<int, string> $avaliadoresDisponiveis
 */
$avaliadoresPorEvento = $opcoes['avaliadoresPorEvento'] ?? [];
$eventosPorEdital = $opcoes['eventosPorEdital'] ?? [];
?>

<div class="container-fluid p-1 pt-1">
    <div class="card card-primary card-outline">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h4 class="mb-1">Cadastro de Banca Workshop</h4>
                    <div class="text-muted">Informe os dados da banca e selecione até três avaliadores.</div>
                </div>
                <?= $this->Html->link('Voltar', ['controller' => 'Workshops', 'action' => 'bancas'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>

            <?= $this->Form->create($banca, ['id' => 'form-banca-workshop']) ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <?= $this->Form->control('nome', [
                            'label' => 'Nome de identificação da banca',
                            'value' => $dados['nome'] ?? '',
                            'class' => 'form-control',
                            'required' => true,
                        ]) ?>
                    </div>
                    <div class="col-md-6">
                        <?= $this->Form->control('grandes_areas_id', [
                            'label' => 'Grande área',
                            'options' => $opcoes['grandesAreas'] ?? [],
                            'empty' => 'Selecione',
                            'value' => $dados['grandes_areas_id'] ?? 0,
                            'class' => 'form-select',
                            'required' => true,
                        ]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $this->Form->control('data', [
                            'label' => 'Data',
                            'type' => 'date',
                            'value' => $dados['data'] ?? '',
                            'class' => 'form-control',
                            'required' => true,
                        ]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $this->Form->control('periodo', [
                            'label' => 'Período',
                            'options' => $opcoes['periodos'] ?? [],
                            'empty' => 'Selecione',
                            'value' => $dados['periodo'] ?? '',
                            'class' => 'form-select',
                            'required' => true,
                        ]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $this->Form->control('evento', [
                            'label' => 'Edital do evento Workshop',
                            'options' => $opcoes['eventos'] ?? [],
                            'empty' => 'Selecione',
                            'value' => $dados['evento'] ?? 0,
                            'class' => 'form-select',
                            'id' => 'evento-workshop',
                            'required' => true,
                        ]) ?>
                    </div>
                    <div class="col-md-12">
                        <?= $this->Form->control('editai_id', [
                            'label' => 'Edital no qual o Workshop é o evento',
                            'options' => $opcoes['editais'] ?? [],
                            'empty' => 'Selecione',
                            'value' => $dados['editai_id'] ?? 0,
                            'class' => 'form-select',
                            'id' => 'editai-id',
                            'required' => true,
                        ]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $this->Form->control('avaliador_1', [
                            'label' => 'Avaliador 1',
                            'type' => 'select',
                            'options' => $avaliadoresDisponiveis,
                            'empty' => 'Selecione',
                            'value' => $dados['avaliadores'][0] ?? 0,
                            'class' => 'form-select avaliador-workshop',
                            'id' => 'avaliador-1',
                        ]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $this->Form->control('avaliador_2', [
                            'label' => 'Avaliador 2',
                            'type' => 'select',
                            'options' => $avaliadoresDisponiveis,
                            'empty' => 'Selecione',
                            'value' => $dados['avaliadores'][1] ?? 0,
                            'class' => 'form-select avaliador-workshop',
                            'id' => 'avaliador-2',
                        ]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $this->Form->control('avaliador_3', [
                            'label' => 'Avaliador 3',
                            'type' => 'select',
                            'options' => $avaliadoresDisponiveis,
                            'empty' => 'Selecione',
                            'value' => $dados['avaliadores'][2] ?? 0,
                            'class' => 'form-select avaliador-workshop',
                            'id' => 'avaliador-3',
                        ]) ?>
                        <small class="text-muted">O terceiro avaliador é opcional.</small>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <?= $this->Form->button('Salvar banca', ['class' => 'btn btn-primary']) ?>
                    <?= $this->Html->link('Cancelar', ['controller' => 'Workshops', 'action' => 'bancas'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

<script>
    (function() {
        var avaliadoresPorEvento = <?= json_encode($avaliadoresPorEvento, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        var eventosPorEdital = <?= json_encode($eventosPorEdital, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        var selectEvento = document.getElementById('evento-workshop');
        var selectEdital = document.getElementById('editai-id');
        var selectsAvaliadores = Array.prototype.slice.call(document.querySelectorAll('.avaliador-workshop'));
        var selecionados = <?= json_encode(array_map('intval', (array)($dados['avaliadores'] ?? []))) ?>;
        var editalSelecionadoInicial = selectEdital.value || '';
        var editalOptions = Array.prototype.slice.call(selectEdital.options).map(function(option) {
            return {
                value: option.value,
                text: option.textContent,
                empty: option.value === ''
            };
        });

        function atualizarEditais() {
            var eventoId = parseInt(selectEvento.value || '0', 10);
            selectEdital.innerHTML = '';

            editalOptions.forEach(function(item) {
                if (!item.empty && parseInt(eventosPorEdital[item.value] || '0', 10) !== eventoId) {
                    return;
                }
                var option = document.createElement('option');
                option.value = item.value;
                option.textContent = item.text;
                option.selected = item.value === editalSelecionadoInicial;
                selectEdital.appendChild(option);
            });
        }

        function atualizarAvaliadores() {
            var eventoId = selectEvento.value || '';
            var opcoes = avaliadoresPorEvento[eventoId] || {};

            selectsAvaliadores.forEach(function(select, index) {
                select.innerHTML = '';
                var emptyOption = document.createElement('option');
                emptyOption.value = '';
                emptyOption.textContent = 'Selecione';
                select.appendChild(emptyOption);

                Object.keys(opcoes).forEach(function(id) {
                    var option = document.createElement('option');
                    option.value = id;
                    option.textContent = opcoes[id];
                    option.selected = parseInt(id, 10) === parseInt(selecionados[index] || 0, 10);
                    select.appendChild(option);
                });
            });
        }

        selectEvento.addEventListener('change', function() {
            selecionados = [];
            editalSelecionadoInicial = '';
            atualizarEditais();
            atualizarAvaliadores();
        });
        atualizarEditais();
        atualizarAvaliadores();
    })();
</script>
