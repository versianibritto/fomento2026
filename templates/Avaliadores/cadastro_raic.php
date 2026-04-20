<div class="container-fluid p-1 pt-1">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <h4 class="mb-2">Cadastro Massivo de Avaliadores RAIC</h4>
                    <p class="text-muted mb-3">
                        Selecione o edital RAIC aberto, a unidade, a grande área, a área e informe os CPFs separados por vírgula.
                    </p>

                    <?= $this->Form->create(null, ['class' => 'row g-3']) ?>
                        <?= $this->Form->hidden('acao', ['value' => 'analisar']) ?>
                        <div class="col-md-3">
                            <?= $this->Form->control('editai_id', [
                                'label' => 'Edital RAIC',
                                'options' => $editais,
                                'empty' => 'Selecione',
                                'default' => $dados['editai_id'] ?? 0,
                                'class' => 'form-select',
                                'required' => true,
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $this->Form->control('unidade_id', [
                                'label' => 'Unidade',
                                'options' => $unidades,
                                'empty' => 'Selecione',
                                'default' => $dados['unidade_id'] ?? 0,
                                'class' => 'form-select',
                                'required' => true,
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $this->Form->control('grandes_area_id', [
                                'label' => 'Grande área',
                                'options' => $grandesAreas,
                                'empty' => 'Selecione',
                                'default' => $dados['grandes_area_id'] ?? 0,
                                'class' => 'form-select',
                                'id' => 'grandes-area-id',
                                'required' => true,
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $this->Form->control('area_id', [
                                'label' => 'Área',
                                'options' => $areas,
                                'empty' => 'Selecione',
                                'default' => $dados['area_id'] ?? 0,
                                'class' => 'form-select',
                                'id' => 'area-id',
                                'required' => true,
                            ]) ?>
                        </div>
                        <div class="col-12">
                            <?= $this->Form->control('cpfs', [
                                'label' => 'CPFs',
                                'type' => 'textarea',
                                'rows' => 5,
                                'value' => $dados['cpfs'] ?? '',
                                'class' => 'form-control',
                                'placeholder' => 'Ex.: 11111111111, 22222222222, 33333333333',
                                'required' => true,
                            ]) ?>
                            <small class="text-muted">Você pode separar por vírgula, espaço, quebra de linha ou ponto e vírgula.</small>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <?= $this->Form->button('Analisar CPFs', ['class' => 'btn btn-primary']) ?>
                            <?= $this->Html->link(
                                'Limpar',
                                ['controller' => 'Avaliadores', 'action' => 'cadastroRaic'],
                                ['class' => 'btn btn-outline-secondary']
                            ) ?>
                        </div>
                    <?= $this->Form->end() ?>

                    <?php if (!empty($resultado['processado'])): ?>
                        <hr class="my-4">
                        <div class="d-flex flex-wrap gap-3 mb-3">
                            <span class="badge bg-primary">CPFs informados: <?= (int)$resultado['totalInformados'] ?></span>
                            <span class="badge bg-success">Elegíveis: <?= (int)$resultado['totalElegiveis'] ?></span>
                            <span class="badge bg-danger">Inelegíveis: <?= (int)$resultado['totalInelegiveis'] ?></span>
                        </div>

                        <?php if (!empty($resultado['elegiveis'])): ?>
                            <div class="card border-success mb-4">
                                <div class="card-body">
                                    <h5 class="card-title text-success mb-3">Elegíveis para cadastro</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>CPF</th>
                                                    <th>Nome</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($resultado['elegiveis'] as $item): ?>
                                                    <tr>
                                                        <td><?= h((string)$item['cpf']) ?></td>
                                                        <td><?= h((string)$item['nome']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($resultado['inelegiveis'])): ?>
                            <div class="card border-danger">
                                <div class="card-body">
                                    <h5 class="card-title text-danger mb-3">Inelegibilidades</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>CPF</th>
                                                    <th>Nome</th>
                                                    <th>Motivo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($resultado['inelegiveis'] as $item): ?>
                                                    <tr>
                                                        <td><?= h((string)$item['cpf']) ?></td>
                                                        <td><?= h((string)($item['nome'] ?? 'Não localizado')) ?></td>
                                                        <td><?= h((string)$item['motivo']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!$resultado['confirmado'] && !empty($resultado['elegiveis'])): ?>
                            <div class="alert alert-warning mt-4">
                                Revise os inelegíveis acima. Ao confirmar, apenas os CPFs elegíveis serão cadastrados.
                            </div>
                            <?= $this->Form->create(null, ['class' => 'd-flex flex-wrap gap-2']) ?>
                                <?= $this->Form->hidden('acao', ['value' => 'confirmar']) ?>
                                <?= $this->Form->hidden('editai_id', ['value' => (int)($dados['editai_id'] ?? 0)]) ?>
                                <?= $this->Form->hidden('unidade_id', ['value' => (int)($dados['unidade_id'] ?? 0)]) ?>
                                <?= $this->Form->hidden('grandes_area_id', ['value' => (int)($dados['grandes_area_id'] ?? 0)]) ?>
                                <?= $this->Form->hidden('area_id', ['value' => (int)($dados['area_id'] ?? 0)]) ?>
                                <?= $this->Form->hidden('cpfs', ['value' => (string)($dados['cpfs'] ?? '')]) ?>
                                <?= $this->Form->button('Confirmar cadastro dos elegíveis', [
                                    'class' => 'btn btn-danger',
                                    'onclick' => "return confirm('Confirma o cadastro massivo dos avaliadores elegíveis?');",
                                ]) ?>
                            <?= $this->Form->end() ?>
                        <?php endif; ?>
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
        $('#area-id').html("<option value=''>Selecione</option>");
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
            let html = "<option value=''>Selecione</option>";
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
