<div class="container-fluid p-1 pt-1">
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <h4 class="mb-2">Area Restrita TI</h4>
                    <p class="text-muted mb-3">Acesso exclusivo para administradores tecnicos.</p>

                    <?php if (!empty($atalhos)): ?>
                        <div class="row g-3">
                            <?php foreach ($atalhos as $atalho): ?>
                                <div class="col-md-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start">
                                                <i class="<?= h($atalho['icon'] ?? 'fas fa-tools') ?> me-2 mt-1"></i>
                                                <div>
                                                    <h5 class="card-title mb-1"><?= h($atalho['titulo']) ?></h5>
                                                    <p class="card-text text-muted mb-2">
                                                        <?= h($atalho['descricao'] ?? '') ?>
                                                    </p>
                                                    <?= $this->Html->link(
                                                        'Acessar',
                                                        $atalho['url'],
                                                        ['class' => 'btn btn-sm ' . ($atalho['class'] ?? 'btn-outline-primary')]
                                                    ) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert bg-warning">
                            Nenhum atalho configurado para esta area.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
