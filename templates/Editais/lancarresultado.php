<section class="mt-n3">
    <div class="card card-primary card-outline">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h2 class="mb-0">Lançar resultado</h2>
                <?= $this->Html->link('Voltar', $this->request->referer(), ['class' => 'btn btn-outline-secondary']) ?>
            </div>
            <div class="text-muted mt-1">Edital: <?= h($edital->nome) ?></div>
            <hr>
            <?=$this->Form->create($edital, ['type' => 'file', 'class' => 'row g-3'])?>
                <div class="col-md-6">
                    <?=$this->Form->control('resultado_arquivo', [
                        'id' => 'arquivo',
                        'type' => 'file',
                        'label' => 'Arquivo do Resultado',
                        'class' => 'form-control',
                        'required' => empty($edital->resultado_arquivo),
                    ])?>
                    <?php if (!empty($edital->resultado_arquivo)) { ?>
                        <div class="form-check mt-2">
                            <?=$this->Form->checkbox('remover_resultado', ['id' => 'remover-resultado', 'class' => 'form-check-input'])?>
                            <label class="form-check-label text-danger" for="remover-resultado">Apagar resultado atual</label>
                        </div>
                    <?php } ?>
                </div>
                <div class="col-md-6">
                    <div class="mt-4 p-2 border rounded <?= empty($edital->resultado_arquivo) ? 'border-danger bg-danger bg-opacity-10' : 'border-primary bg-primary bg-opacity-10' ?>">
                        <?php if (!empty($edital->resultado_arquivo)) { ?>
                            <a class="btn btn-primary btn-sm" href="/uploads/editais/<?= h($edital->resultado_arquivo) ?>" target="_blank">
                                Abrir resultado atual
                            </a>
                            <span class="text-muted ms-2"><?= h($edital->resultado_arquivo) ?></span>
                        <?php } else { ?>
                            <strong class="text-danger">Nenhum resultado anexado.</strong>
                        <?php } ?>
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <?= $this->Form->button('Anexar resultado', ['class'=>'btn btn-success']) ?>
                </div>
            <?=$this->Form->end()?>
        </div>
    </div>
</section>
