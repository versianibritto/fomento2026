<?php
/**
 * @var \App\Model\Entity\ProjetoBolsista $bolsista
 * @var array<string, string> $fontes
 * @var array<int, string> $erros
 * @var \App\Model\Entity\ProjetoBolsista|null $bolsaVigenteAtual
 */

$fonteAtual = (string)($bolsista->tipo_bolsa ?? '');
$fonteAtualNome = $fontes[$fonteAtual] ?? $fonteAtual;
$dataInicioAtual = '';
if (!empty($bolsista->data_inicio) && is_object($bolsista->data_inicio) && method_exists($bolsista->data_inicio, 'format')) {
    $dataInicioAtual = $bolsista->data_inicio->format('Y-m-d');
} elseif (!empty($bolsista->data_inicio)) {
    $dataInicioAtual = date('Y-m-d', strtotime((string)$bolsista->data_inicio));
}
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <div class="p-3 mb-4 bg-light border rounded">
                <h4 class="mb-2">Implementação manual da bolsa #<?= (int)$bolsista->id ?></h4>
                <div class="text-muted">Ação exclusiva da gestão.</div>
                <div class="alert alert-danger text-danger fw-semibold mt-3 mb-0">
                    <?php if ($bolsaVigenteAtual) : ?>
                        Este bolsista já possui a bolsa vigente #<?= (int)$bolsaVigenteAtual->id ?>.
                        Ao confirmar, esta inscrição será ativada na fase de reserva/substituição e ficará como não vigente.
                    <?php else : ?>
                        Ao confirmar, a inscrição será marcada como ativa e vigente.
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($erros)) : ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($erros as $erro) : ?>
                            <li><?= h($erro) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small">Bolsista</div>
                        <strong><?= h($bolsista->bolsista_usuario->nome ?? 'Não informado') ?></strong>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small">Orientador</div>
                        <strong><?= h($bolsista->orientadore->nome ?? 'Não informado') ?></strong>
                        <?php if (!empty($bolsista->orientadore->unidade->sigla)) : ?>
                            <span class="text-muted">- <?= h($bolsista->orientadore->unidade->sigla) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small">Edital</div>
                        <strong><?= h($bolsista->editai->nome ?? 'Não informado') ?></strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small">Fase atual</div>
                        <strong><?= h($bolsista->fase->nome ?? '-') ?></strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small">Fonte atual</div>
                        <strong><?= h($fonteAtualNome !== '' ? $fonteAtualNome : '-') ?></strong>
                    </div>
                </div>
            </div>

            <?= $this->Form->create($bolsista, ['class' => 'row g-3']) ?>
                <div class="col-md-6">
                    <?= $this->Form->control('tipo_bolsa', [
                        'label' => 'Fonte pagadora',
                        'type' => 'select',
                        'options' => $fontes,
                        'empty' => ' - Selecione - ',
                        'value' => $fonteAtual,
                        'class' => 'form-select',
                        'required' => true,
                    ]) ?>
                </div>

                <div class="col-md-6">
                    <?= $this->Form->control('data_inicio', [
                        'label' => 'Data de implementação da bolsa',
                        'type' => 'date',
                        'value' => $dataInicioAtual,
                        'class' => 'form-control',
                        'required' => true,
                    ]) ?>
                </div>

                <div class="col-12">
                    <?= $this->Form->control('justificativa', [
                        'label' => 'Justificativa',
                        'type' => 'textarea',
                        'class' => 'form-control',
                        'rows' => 4,
                        'required' => true,
                    ]) ?>
                </div>

                <div class="col-12 d-flex gap-2 justify-content-end">
                    <?= $this->Html->link('Voltar', ['controller' => 'Padrao', 'action' => 'visualizar', (int)$bolsista->id], ['class' => 'btn btn-outline-secondary']) ?>
                    <?= $this->Form->button('Confirmar implementação', ['class' => 'btn btn-primary']) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
