<?php
$steps = [
    'dadosBolsistaRenovacao' => 'Dados do bolsista',
    'projetoRenovacao' => 'Projeto',
    'subprojetoRenovacao' => 'Subprojeto',
    'coorientadorRenovacao' => 'Coorientador',
    'gerarTermoRenovacao' => 'Gerar termo',
    'finalizarRenovacao' => 'Finalizar',
];
$inscricaoId = $inscricao->id ?? null;
?>
<div class="mb-3 renovacoes-steps">
    <div class="text-muted small mb-2">Etapas da renovacao</div>
    <div class="d-flex flex-wrap gap-2 justify-content-center">
        <?php foreach ($steps as $action => $label) { ?>
            <?php
                $url = ['controller' => 'Renovacoes', 'action' => $action, $edital->id];
                if ($inscricaoId) {
                    $url[] = $inscricaoId;
                }
            ?>
            <?= $this->Html->link(
                $label,
                $url,
                ['class' => 'btn btn-sm ' . ($current === $action ? 'btn-success' : 'btn-outline-success')]
            ) ?>
        <?php } ?>
    </div>
</div>
