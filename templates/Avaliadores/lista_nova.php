<div class="container-fluid p-1 pt-1">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <h4 class="mb-2">Lista de Avaliadores por Edital</h4>
                    <p class="text-muted mb-3">
                        Listagem dos avaliadores do tipo N, com filtros por nome, edital, grande área e área.
                    </p>

                    <?= $this->Form->create(null, [
                        'type' => 'get',
                        'class' => 'row g-3 align-items-end mb-4',
                    ]) ?>
                        <div class="col-md-2">
                            <?= $this->Form->control('ano', [
                                'label' => 'Ano',
                                'options' => $anosOptions,
                                'default' => $filtros['ano'] ?? null,
                                'class' => 'form-select',
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $this->Form->control('nome', [
                                'label' => 'Nome do avaliador',
                                'value' => $filtros['nome'] ?? '',
                                'class' => 'form-control',
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $this->Form->control('editai_id', [
                                'label' => 'Edital',
                                'options' => $editais,
                                'empty' => 'Todos',
                                'default' => $filtros['editai_id'] ?? 0,
                                'class' => 'form-select',
                            ]) ?>
                        </div>
                        <div class="col-md-2">
                            <?= $this->Form->control('grandes_area_id', [
                                'label' => 'Grande área',
                                'options' => $grandesAreas,
                                'empty' => 'Todas',
                                'default' => $filtros['grandes_area_id'] ?? 0,
                                'class' => 'form-select',
                                'id' => 'grandes-area-id',
                            ]) ?>
                        </div>
                        <div class="col-md-2">
                            <?= $this->Form->control('area_id', [
                                'label' => 'Área',
                                'options' => $areas,
                                'empty' => 'Todas',
                                'default' => $filtros['area_id'] ?? 0,
                                'class' => 'form-select',
                                'id' => 'area-id',
                            ]) ?>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <?= $this->Form->button('Filtrar', ['class' => 'btn btn-primary']) ?>
                            <?= $this->Html->link(
                                'Limpar',
                                ['controller' => 'Avaliadores', 'action' => 'listaNova'],
                                ['class' => 'btn btn-outline-secondary']
                            ) ?>
                        </div>
                    <?= $this->Form->end() ?>

                    <?php if ($avaliadores->count() === 0): ?>
                        <div class="alert alert-info mb-0">
                            Nenhum avaliador localizado com os filtros informados.
                        </div>
                    <?php else: ?>
                        <?php $this->Paginator->options(['url' => $this->request->getQueryParams()]); ?>
                        <div class="mb-3">
                            <?= $this->Html->link(
                                'Exportar CSV',
                                [
                                    'controller' => 'Avaliadores',
                                    'action' => 'listaNova',
                                    '?' => $this->request->getQueryParams() + ['exportar' => 1],
                                ],
                                ['class' => 'btn btn-success btn-sm']
                            ) ?>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Nome do avaliador</th>
                                        <th>CPF</th>
                                        <th>Grande área</th>
                                        <th>Área</th>
                                        <th>Ano</th>
                                        <th>Edital</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($avaliadores as $avaliador): ?>
                                        <tr>
                                            <td><?= h((string)($avaliador->usuario->nome ?? 'Não informado')) ?></td>
                                            <td><?= h((string)($avaliador->usuario->cpf ?? 'Não informado')) ?></td>
                                            <td><?= h((string)($avaliador->grandes_area->nome ?? 'Não informada')) ?></td>
                                            <td><?= h((string)($avaliador->area->nome ?? 'Não informada')) ?></td>
                                            <td><?= h((string)($avaliador->ano_convite ?? '-')) ?></td>
                                            <td><?= h((string)($avaliador->editai->nome ?? 'Não informado')) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 d-flex flex-wrap align-items-center gap-2">
                            <?= $this->Paginator->prev('« Anterior', ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                            <?= $this->Paginator->numbers([
                                'before' => '',
                                'after' => '',
                                'modulus' => 4,
                            ]) ?>
                            <?= $this->Paginator->next('Próxima »', ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                            <span class="ms-2 text-muted">
                                <?= $this->Paginator->counter('Página {{page}} de {{pages}} | Total: {{count}}') ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).on('change', '#grandes-area-id', function () {
    const grandeAreaId = $(this).val();

    if (!grandeAreaId) {
        $('#area-id').html("<option value=''>Todas</option>");
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
            let html = "<option value=''>Todas</option>";
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
