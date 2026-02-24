<?php
$showSumula = ($edital->origem ?? null) === 'N';
$steps = [
    'dadosBolsista' => 'Dados do bolsista',
];
if ($showSumula) {
    $steps['sumula'] = 'Sumula';
}
$steps += [
    'projeto' => 'Projeto',
    'subprojeto' => 'Subprojeto',
    'coorientador' => 'Coorientador',
    'gerarTermo' => 'Gerar termo',
    'finalizar' => 'Finalizar',
];
$inscricaoId = $inscricao->id ?? null;
?>
<div class="mb-3">
    <div class="text-muted small mb-2">Etapas da inscricao</div>
    <div class="d-flex flex-wrap gap-2 justify-content-center">
        <?php foreach ($steps as $action => $label) { ?>
            <?php
                $url = ['controller' => 'Inscricoes', 'action' => $action, $edital->id];
                if ($inscricaoId) {
                    $url[] = $inscricaoId;
                }
            ?>
            <?= $this->Html->link(
                $label,
                $url,
                ['class' => 'btn btn-sm ' . ($current === $action ? 'btn-primary' : 'btn-outline-secondary')]
            ) ?>
        <?php } ?>
    </div>
</div>
