<div class="container mt-4">
    <div class="py-2 mb-3 border-bottom text-center">
        <?= $this->element('substituicoes_steps', [
            'current' => 'finalizar',
            'inscricao' => $inscricao ?? null,
        ]) ?>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="p-3 mb-4 bg-light border rounded text-center">
                <h3 class="mb-2">Anexar Termo</h3>
                <div class="fw-semibold">Substituição - <?= h($edital->nome) ?></div>
                <div class="text-muted mt-1">Esta etapa anexa o termo assinado da substituição.</div>
            </div>
        </div>
    </div>
</div>
