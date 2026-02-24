<div class="container mt-4">
    <div class="py-2 mb-3 border-bottom text-center">
        <?= $this->element('inscricoes_steps', [
            'edital' => $edital,
            'current' => 'gerarTermo',
            'inscricao' => $inscricao ?? null,
        ]) ?>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="p-3 mb-4 border rounded bg-light">
                <h3 class="mb-2">Pendencias para gerar termo</h3>
                <div class="text-muted">
                    A inscricao possui inconsistencias. Corrija os blocos abaixo e tente novamente.
                </div>
            </div>

            <?php foreach ((array)$falhas as $falha): ?>
                <div class="border rounded p-3 mb-3">
                    <div class="fw-semibold mb-2"><?= h($falha['nome'] ?? 'Bloco') ?></div>
                    <ul class="mb-3">
                        <?php foreach ((array)($falha['erros'] ?? []) as $erro): ?>
                            <li><?= h((string)$erro) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <div>
                        <?= $this->Html->link(
                            'Ir para correcao deste bloco',
                            (array)($falha['url'] ?? ['action' => 'gerarTermo', $edital->id, $inscricao->id]),
                            ['class' => 'btn btn-outline-primary btn-sm']
                        ) ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <?= $this->Html->link('Voltar', ['action' => 'gerarTermo', $edital->id, $inscricao->id], ['class' => 'btn btn-secondary']) ?>
            </div>
        </div>
    </div>
</div>
