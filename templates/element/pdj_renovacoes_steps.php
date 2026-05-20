<?php
$steps = [
    'dadosBolsistaRenovacao' => 'Dados do bolsista',
    'projetoRenovacao' => 'Projeto',
    'gerarTermoRenovacao' => 'Gerar termo',
    'finalizarRenovacao' => 'Anexar o termo',
];
$inscricaoId = $inscricao->id ?? null;
$faseAtual = (int)($inscricao->fase_id ?? 0);
?>
<div class="mb-3 pdj-renovacoes-steps">
    <div class="text-muted small mb-2">Etapas da renovação PDJ</div>
    <div class="d-flex flex-wrap gap-2 justify-content-center">
        <?php foreach ($steps as $action => $label) { ?>
            <?php
                $url = ['controller' => 'PdjRenovacoes', 'action' => $action, $edital->id];
                if ($inscricaoId) {
                    $url[] = $inscricaoId;
                }
                $finalizarBloqueado = $action === 'finalizarRenovacao' && $faseAtual !== 5;
            ?>
            <?php if ($finalizarBloqueado): ?>
                <span class="btn btn-sm btn-outline-success disabled" title="Gere o termo antes de anexar o termo assinado.">
                    <?= h($label) ?>
                </span>
            <?php else: ?>
                <?= $this->Html->link(
                    $label,
                    $url,
                    ['class' => 'btn btn-sm ' . ($current === $action ? 'btn-success' : 'btn-outline-success')]
                ) ?>
            <?php endif; ?>
        <?php } ?>
    </div>
</div>
