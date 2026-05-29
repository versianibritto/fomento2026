<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $inscricao
 * @var array<string, string> $resultadoOptions
 * @var string $resultadoAtual
 * @var array<int, string> $erros
 */
$resultadoJaLancado = trim((string)($inscricao->resultado ?? '')) !== '';
$labelAcaoResultado = $resultadoJaLancado ? 'Alterar resultado' : 'Lançar resultado';
$this->assign('title', $labelAcaoResultado);

$homologacaoTexto = match ((string)($inscricao->homologado ?? '')) {
    'S' => 'Homologada',
    'P' => 'Homologada com pendência',
    'N' => 'Não homologada',
    default => 'Não definida',
};

$faseNome = !empty($inscricao->fase->nome) ? (string)$inscricao->fase->nome : '-';
$bolsistaNome = !empty($inscricao->bolsista_usuario->nome) ? (string)$inscricao->bolsista_usuario->nome : '-';
$orientadorNome = !empty($inscricao->orientadore->nome) ? (string)$inscricao->orientadore->nome : '-';
$editalNome = !empty($inscricao->editai->nome) ? (string)$inscricao->editai->nome : '-';
?>
<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <div class="p-3 mb-4 bg-light border rounded">
                <h4 class="mb-2"><?= h($labelAcaoResultado) ?> #<?= (int)$inscricao->id ?></h4>
                <div class="text-muted">Ação exclusiva da gestão.</div>
                <div class="text-muted">
                    Inscrições não homologadas ou homologadas com pendência somente podem ser alteradas para Reprovada.
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small">Bolsista</div>
                        <div class="fw-semibold"><?= h($bolsistaNome) ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small">Orientador</div>
                        <div class="fw-semibold"><?= h($orientadorNome) ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small">Edital</div>
                        <div class="fw-semibold"><?= h($editalNome) ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small">Fase atual</div>
                        <div class="fw-semibold"><?= h($faseNome) ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small">Homologação</div>
                        <div class="fw-semibold"><?= h($homologacaoTexto) ?></div>
                    </div>
                </div>
            </div>

            <?php if (!empty($erros)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($erros as $erro): ?>
                        <div><?= h($erro) ?></div>
                    <?php endforeach; ?>
                </div>
                <?= $this->Html->link(
                    'Voltar',
                    ['action' => 'visualizar', (int)$inscricao->id],
                    ['class' => 'btn btn-outline-secondary']
                ) ?>
            <?php else: ?>
                <?= $this->Form->create($inscricao, ['class' => 'row g-3']) ?>
                    <div class="col-md-6">
                        <?= $this->Form->control('resultado', [
                            'label' => 'Resultado',
                            'type' => 'select',
                            'options' => $resultadoOptions,
                            'empty' => '- Selecione -',
                            'value' => $resultadoAtual,
                            'class' => 'form-select',
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
                        <?= $this->Html->link(
                            'Voltar',
                            ['action' => 'visualizar', (int)$inscricao->id],
                            ['class' => 'btn btn-outline-secondary']
                        ) ?>
                        <?= $this->Form->button($labelAcaoResultado, ['class' => 'btn btn-primary']) ?>
                    </div>
                <?= $this->Form->end() ?>
            <?php endif; ?>
        </div>
    </div>
</div>
