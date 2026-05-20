<?php
$steps = [
    'dadosBolsista' => 'Dados do bolsista',
    'sumulaBolsista' => 'Súmula do bolsista',
    'sumulaOrientador' => 'Súmula do orientador',
    'projeto' => 'Projeto',
    'gerarTermo' => 'Gerar termo',
    'finalizar' => 'Anexar o termo',
];
$inscricaoId = $inscricao->id ?? null;
$faseAtual = (int)($inscricao->fase_id ?? 0);
?>
<div class="mb-3">
    <div class="text-muted small mb-2">Etapas da inscrição PDJ</div>
    <div class="d-flex flex-wrap gap-2 justify-content-center">
        <?php foreach ($steps as $action => $label) { ?>
            <?php
                $url = ['controller' => 'PdjInscricoes', 'action' => $action, $edital->id];
                if ($inscricaoId) {
                    $url[] = $inscricaoId;
                }
                $finalizarBloqueado = $action === 'finalizar' && $faseAtual !== 5;
            ?>
            <?php if ($finalizarBloqueado): ?>
                <span class="btn btn-sm btn-outline-secondary disabled" title="Gere o termo antes de anexar o termo assinado.">
                    <?= h($label) ?>
                </span>
            <?php else: ?>
                <?= $this->Html->link(
                    $label,
                    $url,
                    ['class' => 'btn btn-sm ' . ($current === $action ? 'btn-primary' : 'btn-outline-secondary')]
                ) ?>
            <?php endif; ?>
        <?php } ?>
    </div>
</div>
