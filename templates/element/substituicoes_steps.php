<?php
$steps = [
    'dadosBolsista' => 'Dados do bolsista',
    'coorientador' => 'Coorientador',
    'gerarTermo' => 'Gerar termo',
    'finalizar' => 'Finalizar',
];
$inscricaoId = $inscricao->id ?? null;
?>
<div class="mb-3 substituicoes-steps">
    <div class="text-muted small mb-2">Etapas da substituição</div>
    <div class="d-flex flex-wrap gap-2 justify-content-center">
        <?php foreach ($steps as $action => $label) { ?>
            <?php
                $url = ['controller' => 'Substituicoes', 'action' => $action];
                if ($inscricaoId) {
                    $url[] = $inscricaoId;
                }
            ?>
            <?= $this->Html->link(
                $label,
                $url,
                ['class' => 'btn btn-sm substituicoes-steps__btn ' . ($current === $action ? 'substituicoes-steps__btn--current' : 'substituicoes-steps__btn--default')]
            ) ?>
        <?php } ?>
    </div>
</div>
<style>
.substituicoes-steps__btn--current {
    background-color: #f0c64d;
    border-color: #f0c64d;
    color: #3f3200;
}

.substituicoes-steps__btn--current:hover,
.substituicoes-steps__btn--current:focus {
    background-color: #e3b636;
    border-color: #e3b636;
    color: #3f3200;
}

.substituicoes-steps__btn--default {
    background-color: transparent;
    border-color: #d6d8db;
    color: #6c757d;
}

.substituicoes-steps__btn--default:hover,
.substituicoes-steps__btn--default:focus {
    background-color: #fff3bf;
    border-color: #e7c95a;
    color: #5c4700;
}
</style>
