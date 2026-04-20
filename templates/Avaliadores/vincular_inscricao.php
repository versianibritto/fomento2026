<?php
$vinculoExistente = !empty($vinculosAtivos);
$projetoTitulo = trim((string)($inscricao->projeto->titulo ?? ''));
if ($projetoTitulo === '') {
    $projetoTitulo = trim((string)($inscricao->sp_titulo ?? ''));
}
if ($projetoTitulo === '') {
    $projetoTitulo = 'Não informado';
}
?>

<div class="container mt-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="mb-0"><?= $vinculoExistente ? 'Substituir' : 'Vincular' ?> Avaliadores da Inscrição #<?= (int)$inscricao->id ?></h4>
        <?= $this->Html->link('Voltar', ['controller' => 'Avaliadores', 'action' => 'listaInscricoes'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <strong>Edital</strong><br>
                    <?= h((string)($inscricao->editai->nome ?? 'Não informado')) ?>
                </div>
                <div class="col-md-4">
                    <strong>Bolsista</strong><br>
                    <?= h((string)($inscricao->bolsista_usuario->nome ?? 'Não informado')) ?>
                </div>
                <div class="col-md-4">
                    <strong>Orientador</strong><br>
                    <?= h((string)($inscricao->orientadore->nome ?? 'Não informado')) ?>
                </div>
                <div class="col-md-8">
                    <strong>Projeto</strong><br>
                    <?= h($projetoTitulo) ?>
                </div>
                <div class="col-md-4">
                    <strong>Área do projeto</strong><br>
                    <?= h((string)($inscricao->projeto->area->grandes_area->nome ?? 'Não informada')) ?>
                    <?php if (!empty($inscricao->projeto->area->nome)): ?>
                        | <?= h((string)$inscricao->projeto->area->nome) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($vinculoExistente): ?>
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h5 class="mb-3">Avaliadores Atuais</h5>
                <div class="row g-3">
                    <?php foreach ($vinculosAtivos as $vinculo): ?>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100 bg-light">
                                <strong>Ordem <?= (int)($vinculo->ordem ?? 0) ?></strong><br>
                                <?= h((string)($vinculo->avaliador->usuario->nome ?? 'Não informado')) ?><br>
                                <span class="text-muted small">
                                    <?= h((string)($vinculo->avaliador->grandes_area->nome ?? 'Não informada')) ?>
                                    <?php if (!empty($vinculo->avaliador->area->nome)): ?>
                                        | <?= h((string)$vinculo->avaliador->area->nome) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="alert alert-light border">
                Os filtros de grande área e área servem apenas para facilitar a busca. Eles não limitam a regra de negócio: você pode limpar os filtros e escolher qualquer avaliador disponível no edital.
            </div>

            <?= $this->Form->create(null) ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h5 class="mb-3">Avaliador 1</h5>
                            <div class="row g-3">
                                <div class="col-12">
                                    <?= $this->Form->control('grandes_area_1', [
                                        'label' => 'Grande área',
                                        'type' => 'select',
                                        'options' => $grandesAreas,
                                        'empty' => 'Todas as grandes áreas',
                                        'value' => $filtrosAvaliador1['grandes_area_id'] ?? 0,
                                        'class' => 'form-select js-grande-area',
                                        'data-bloco' => '1',
                                    ]) ?>
                                </div>
                                <div class="col-12">
                                    <?= $this->Form->control('area_1', [
                                        'label' => 'Área',
                                        'type' => 'select',
                                        'options' => $areasAvaliador1,
                                        'empty' => 'Todas as áreas',
                                        'value' => $filtrosAvaliador1['area_id'] ?? 0,
                                        'class' => 'form-select js-area',
                                        'data-bloco' => '1',
                                    ]) ?>
                                </div>
                                <div class="col-12">
                                    <?= $this->Form->control('avaliador_1', [
                                        'label' => 'Avaliador',
                                        'type' => 'select',
                                        'options' => $avaliadoresAvaliador1,
                                        'empty' => 'Selecione',
                                        'value' => $avaliador1Atual,
                                        'required' => true,
                                        'class' => 'form-select js-avaliador',
                                        'data-bloco' => '1',
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h5 class="mb-3">Avaliador 2</h5>
                            <div class="row g-3">
                                <div class="col-12">
                                    <?= $this->Form->control('grandes_area_2', [
                                        'label' => 'Grande área',
                                        'type' => 'select',
                                        'options' => $grandesAreas,
                                        'empty' => 'Todas as grandes áreas',
                                        'value' => $filtrosAvaliador2['grandes_area_id'] ?? 0,
                                        'class' => 'form-select js-grande-area',
                                        'data-bloco' => '2',
                                    ]) ?>
                                </div>
                                <div class="col-12">
                                    <?= $this->Form->control('area_2', [
                                        'label' => 'Área',
                                        'type' => 'select',
                                        'options' => $areasAvaliador2,
                                        'empty' => 'Todas as áreas',
                                        'value' => $filtrosAvaliador2['area_id'] ?? 0,
                                        'class' => 'form-select js-area',
                                        'data-bloco' => '2',
                                    ]) ?>
                                </div>
                                <div class="col-12">
                                    <?= $this->Form->control('avaliador_2', [
                                        'label' => 'Avaliador',
                                        'type' => 'select',
                                        'options' => $avaliadoresAvaliador2,
                                        'empty' => 'Selecione',
                                        'value' => $avaliador2Atual,
                                        'required' => true,
                                        'class' => 'form-select js-avaliador',
                                        'data-bloco' => '2',
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <?= $this->Form->button($vinculoExistente ? 'Salvar substituição' : 'Salvar vinculação', ['class' => 'btn btn-primary']) ?>
                    <?= $this->Html->link('Cancelar', ['controller' => 'Avaliadores', 'action' => 'listaInscricoes'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

<script>
$(document).on('change', '.js-grande-area', function () {
    const bloco = $(this).data('bloco');
    const grandeAreaId = $(this).val();
    const areaField = $(`#area-${bloco}`);
    const avaliadorField = $(`#avaliador-${bloco}`);

    if (!grandeAreaId) {
        areaField.html("<option value=''>Todas as áreas</option>");
        carregarAvaliadores(bloco);
        return;
    }

    $.ajax({
        type: 'POST',
        url: "<?= $this->Url->build(['controller' => 'Avaliadores', 'action' => 'buscaAreas']) ?>",
        data: { id: grandeAreaId },
        dataType: 'json',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-CSRF-Token', <?= json_encode($this->request->getAttribute('csrfToken')) ?>);
            areaField.html("<option value=''>Carregando áreas...</option>");
            avaliadorField.html("<option value=''>Carregando avaliadores...</option>");
        },
        success: function(json) {
            let html = "<option value=''>Todas as áreas</option>";
            $.each(json, function(_, item) {
                html += `<option value="${item.id}">${item.nome}</option>`;
            });
            areaField.html(html);
            carregarAvaliadores(bloco);
        },
        error: function() {
            areaField.html("<option value=''>Erro ao carregar áreas</option>");
            avaliadorField.html("<option value=''>Erro ao carregar avaliadores</option>");
        }
    });
});

$(document).on('change', '.js-area', function () {
    carregarAvaliadores($(this).data('bloco'));
});

function carregarAvaliadores(bloco) {
    const grandeAreaId = $(`#grandes-area-${bloco}`).val();
    const areaId = $(`#area-${bloco}`).val();
    const avaliadorField = $(`#avaliador-${bloco}`);

    $.ajax({
        type: 'POST',
        url: "<?= $this->Url->build(['controller' => 'Avaliadores', 'action' => 'buscaAvaliadoresInscricao', $inscricao->id]) ?>",
        data: {
            grandes_area_id: grandeAreaId,
            area_id: areaId
        },
        dataType: 'json',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-CSRF-Token', <?= json_encode($this->request->getAttribute('csrfToken')) ?>);
            avaliadorField.html("<option value=''>Carregando avaliadores...</option>");
        },
        success: function(json) {
            let html = "<option value=''>Selecione</option>";
            $.each(json, function(_, item) {
                html += `<option value="${item.id}">${item.nome}</option>`;
            });
            avaliadorField.html(html);
        },
        error: function() {
            avaliadorField.html("<option value=''>Erro ao carregar avaliadores</option>");
        }
    });
}
</script>
