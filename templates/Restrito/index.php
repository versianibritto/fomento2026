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

                    <hr class="my-4">

                    <div class="card border">
                        <div class="card-body">
                            <h5 class="card-title mb-2">Replicação de Anexos</h5>
                            <p class="text-muted mb-3">
                                Executa rotinas de replicação do anexo tipo 20 para substituição e renovação.
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <?= $this->Form->postLink(
                                    'Replicar Anexos (Substituição)',
                                    ['controller' => 'Restrito', 'action' => 'replicarAnexos'],
                                    [
                                        'class' => 'btn btn-outline-primary',
                                        'confirm' => 'Confirma a replicação de anexos para inscrições de substituição?',
                                    ]
                                ) ?>
                                <?= $this->Form->postLink(
                                    'Replicar Anexos (Renovação)',
                                    ['controller' => 'Restrito', 'action' => 'replicarAnexosRenova'],
                                    [
                                        'class' => 'btn btn-outline-success',
                                        'confirm' => 'Confirma a replicação de anexos para inscrições de renovação?',
                                    ]
                                ) ?>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="card border">
                        <div class="card-body">
                            <h5 class="card-title mb-2">Rotinas PDJ</h5>
                            <p class="text-muted mb-3">
                                Migração e correção de referência dos anexos PDJ para projeto_bolsistas.
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <?= $this->Form->postLink(
                                    'Executar Migração PDJ',
                                    ['controller' => 'Restrito', 'action' => 'migrarPdjParaProjetoBolsistas'],
                                    [
                                        'class' => 'btn btn-outline-warning',
                                        'confirm' => 'Confirma a migração de pdj_inscricoes para projeto_bolsistas?',
                                    ]
                                ) ?>
                                <?= $this->Form->postLink(
                                    'Reestabelecer Anexos PDJ Migrados',
                                    ['controller' => 'Restrito', 'action' => 'reestabelecerAnexosPdjMigrados'],
                                    [
                                        'class' => 'btn btn-outline-danger',
                                        'confirm' => 'Confirma o reestabelecimento de anexos PDJ migrados (incluindo registros deletados)?',
                                    ]
                                ) ?>
                                <?= $this->Form->postLink(
                                    'Migrar Históricos PDJ',
                                    ['controller' => 'Restrito', 'action' => 'migrarHistoricosPdjParaSituacaoHistoricos'],
                                    [
                                        'class' => 'btn btn-outline-dark',
                                        'confirm' => 'Confirma a migração de pdj_historicos para situacao_historicos?',
                                    ]
                                ) ?>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="card border">
                        <div class="card-body">
                            <h5 class="card-title mb-2">Atualizar Deleted (Timestamp)</h5>
                            <p class="text-muted mb-3">
                                Preenche deleted com a maior data entre created, modified e data_cancelamento
                                quando data_cancelamento está preenchida.
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <?= $this->Form->postLink(
                                    'Atualizar Deleted (Timestamp)',
                                    ['controller' => 'Restrito', 'action' => 'atualizarDeletedTimestamp'],
                                    [
                                        'class' => 'btn btn-outline-secondary',
                                        'confirm' => 'Confirma a atualização de deleted (timestamp) para registros com data_cancelamento preenchida?',
                                    ]
                                ) ?>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="card border">
                        <div class="card-body">
                            <h5 class="card-title mb-2">Deleção Sistêmica de Inscrições Expiradas</h5>
                            <p class="text-muted mb-3">
                                Deleta logicamente inscrições não finalizadas em tempo de inscrição do edital, garantindo vigente = 0 e gravando histórico.
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <?= $this->Html->link(
                                    'Abrir rotina',
                                    ['controller' => 'Gestao', 'action' => 'limparinscricoesexpiradas'],
                                    ['class' => 'btn btn-outline-danger']
                                ) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
