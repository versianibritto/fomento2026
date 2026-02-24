<div class="container mt-4">
    <div class="py-2 mb-3 border-bottom text-center">
        <?= $this->element('renovacoes_steps', [
            'edital' => $edital,
            'current' => 'gerarTermoRenovacao',
            'inscricao' => $inscricao ?? null,
        ]) ?>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="p-3 mb-4 bg-light border rounded text-center">
                <h3 class="mb-2">Gerar termo</h3>
                <div class="fw-semibold">Inscricao - <?= h($edital->nome) ?></div>
                <div class="text-muted mt-1">Revise as informacoes antes de confirmar.</div>
            </div>

            <div class="alert alert-warning border border-warning-subtle mb-4 sumula-alerta">
                <div class="fw-semibold mb-1">Antes de gerar o termo revise:</div>
                <div>Apos a geracao do termo, a inscricao nao podera ser alterada.</div>
                <ol class="mb-0 mt-2 ps-3">
                    <li>Bolsista: dados pessoais, anexos e cota afirmativa.</li>
                    <li>Coorientador: dados pessoais e termo de consentimento.</li>
                    <li>Projeto: titulo, resumo, areas e anexo.</li>
                    <li>Subprojeto: regras conforme o edital.</li>
                    <li>Sumula: itens e quantidades, quando aplicavel.</li>
                </ol>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <?= $this->Form->postLink('Gerar termo', ['controller' => 'Renovacoes', 'action' => 'validarGeracaoTermoRenovacao', $edital->id, $inscricao->id], ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>
</div>
<style>
.sumula-alerta {
    border: 1px solid #ffe69c;
    background: #fff8db;
    box-shadow: 0 0 0 2px rgba(255, 193, 7, .15);
}
</style>
