<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Avaliador $avaliador
 * @var array<int|string, string> $grandesAreas
 * @var array<int|string, string> $areas
 * @var string $retorno
 */
?>
<div class="container mt-4">
    <div class="card card-primary card-outline">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h4 class="mb-1">Alterar Competência do Avaliador</h4>
                    <p class="text-muted mb-0">
                        Atualize a grande área e a área do cadastro de avaliador nova.
                    </p>
                </div>
                <?= $this->Html->link('Voltar', $retorno ?: ['action' => 'listaAvaliadoresNova'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
            </div>

            <div class="border rounded p-3 mb-3 bg-light">
                <div class="row g-3">
                    <div class="col-md-4">
                        <strong>Avaliador</strong><br>
                        <?= h((string)($avaliador->usuario->nome ?? 'Não informado')) ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Edital</strong><br>
                        <?= h((string)($avaliador->editai->nome ?? 'Não informado')) ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Competência atual</strong><br>
                        <?= h((string)($avaliador->grandes_area->nome ?? 'Nenhuma')) ?>
                        <?php if (!empty($avaliador->area->nome)): ?>
                            | <?= h((string)$avaliador->area->nome) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?= $this->Form->create($avaliador, ['class' => 'row g-3']) ?>
                <?= $this->Form->hidden('retorno', ['value' => $retorno]) ?>
                <div class="col-md-6">
                    <?= $this->Form->control('grandes_area_id', [
                        'label' => 'Grande área',
                        'options' => $grandesAreas,
                        'empty' => 'Nenhuma',
                        'value' => $avaliador->grandes_area_id ?? '',
                        'class' => 'form-select',
                        'id' => 'grandes-area-id',
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $this->Form->control('area_id', [
                        'label' => 'Área',
                        'options' => $areas,
                        'empty' => 'Nenhuma',
                        'value' => $avaliador->area_id ?? '',
                        'class' => 'form-select',
                        'id' => 'area-id',
                    ]) ?>
                </div>
                <div class="col-12 d-flex flex-wrap gap-2">
                    <?= $this->Form->button('Salvar', ['class' => 'btn btn-primary']) ?>
                    <?= $this->Html->link('Cancelar', $retorno ?: ['action' => 'listaAvaliadoresNova'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

<script>
$(document).on('change', '#grandes-area-id', function () {
    const grandeAreaId = $(this).val();

    if (!grandeAreaId) {
        $('#area-id').html("<option value=''>Nenhuma</option>");
        return;
    }

    $.ajax({
        type: 'POST',
        url: "<?= $this->Url->build(['controller' => 'Avaliadores', 'action' => 'buscaAreas']) ?>",
        data: { id: grandeAreaId },
        dataType: 'json',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-CSRF-Token', <?= json_encode($this->request->getAttribute('csrfToken')) ?>);
            $('#area-id').html("<option value=''>Carregando...</option>");
        },
        success: function(json) {
            let html = "<option value=''>Nenhuma</option>";
            $.each(json, function(_, item) {
                html += `<option value="${item.id}">${item.nome}</option>`;
            });
            $('#area-id').html(html);
        },
        error: function() {
            $('#area-id').html("<option value=''>Erro ao carregar áreas</option>");
        }
    });
});
</script>
