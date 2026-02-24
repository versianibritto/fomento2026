<div class="container-fluid p-1 pt-1">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h4 class="mb-0">Blocos de Sumula</h4>
                        <?= $this->Html->link('Voltar', ['controller' => 'Restrito', 'action' => 'index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                    </div>

                    <?= $this->Form->create(null, [
                        'url' => ['controller' => 'Editais', 'action' => 'sumulasblocosadd'],
                        'class' => 'row g-2 align-items-end mb-3',
                    ]) ?>
                        <div class="col-md-6">
                            <?= $this->Form->control('nome', [
                                'label' => 'Novo bloco',
                                'class' => 'form-control',
                                'maxlength' => 45,
                                'required' => true,
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $this->Form->button('Cadastrar', ['class' => 'btn btn-success']) ?>
                        </div>
                    <?= $this->Form->end() ?>

                    <?php if (!empty($sumulasBlocos)) { ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 70px;">ID</th>
                                        <th>Nome</th>
                                        <th style="width: 140px;">Status</th>
                                        <th style="width: 140px;">Acoes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sumulasBlocos as $bloco) { ?>
                                        <tr>
                                            <td><?= h($bloco->id) ?></td>
                                            <td><?= h($bloco->nome) ?></td>
                                            <td><?= $bloco->deleted ? 'Deletado' : 'Ativo' ?></td>
                                            <td class="text-end">
                                                <?php if (!$bloco->deleted) { ?>
                                                    <?= $this->Html->link(
                                                        '<i class="fas fa-edit"></i>',
                                                        ['controller' => 'Editais', 'action' => 'sumulasblocosedit', $bloco->id],
                                                        ['class' => 'btn btn-outline-primary btn-sm me-1', 'escape' => false, 'title' => 'Editar']
                                                    ) ?>
                                                    <?= $this->Html->link(
                                                        '<i class="fas fa-trash"></i>',
                                                        ['controller' => 'Editais', 'action' => 'sumulasblocosdelete', $bloco->id],
                                                        ['class' => 'btn btn-outline-danger btn-sm', 'escape' => false, 'title' => 'Excluir', 'confirm' => 'Tem certeza que deseja excluir este bloco?']
                                                    ) ?>
                                                <?php } else { ?>
                                                    <span class="text-muted"><?= $bloco->deleted ? $bloco->deleted->format('d/m/Y H:i') : '' ?></span>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } else { ?>
                        <span class="text-muted">Nenhum bloco cadastrado.</span>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
