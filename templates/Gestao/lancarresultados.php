<?php
$resultadoSelecionado = (string)($resultadoSelecionado ?? '');
$idsRaw = (string)($idsRaw ?? '');
$preview = $preview ?? null;
$resultadoLabel = $resultadoLabels[$resultadoSelecionado] ?? $resultadoSelecionado;
$iconeRecusa = static function (string $motivo): string {
    $motivo = mb_strtolower($motivo);
    if (str_contains($motivo, 'deletada') || str_contains($motivo, 'inativa')) {
        return '<span class="text-danger" title="Deletada/Inativa"><i class="fa fa-times-circle"></i></span>';
    }
    if (str_contains($motivo, 'já possui resultado')) {
        return '<span class="text-warning" title="Resultado já lançado"><i class="fa fa-ban"></i></span>';
    }
    if (str_contains($motivo, 'não homologada')) {
        return '<span class="text-secondary" title="Não homologada"><i class="fa fa-check-square-o"></i></span>';
    }
    if (str_contains($motivo, 'origem inválida')) {
        return '<span class="text-info" title="Origem inválida"><i class="fa fa-random"></i></span>';
    }
    if (str_contains($motivo, 'não localizada')) {
        return '<span class="text-dark" title="Não localizada"><i class="fa fa-search"></i></span>';
    }
    return '<span class="text-muted" title="Recusada"><i class="fa fa-exclamation-circle"></i></span>';
};
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">Lançar Resultados</h4>
        <a href="<?= $this->Url->build(['controller' => 'Index', 'action' => 'dashyoda']) ?>"
           class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa fa-arrow-left me-1"></i> Voltar para o Dashboard
        </a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="mb-3">
                <div class="fw-semibold">Atualização massiva de resultado</div>
                <div class="text-muted small">
                    Informe as inscrições separadas por vírgula, espaço ou quebra de linha. O sistema valida homologação, origem, exclusão lógica e se o resultado já foi lançado.
                </div>
            </div>

            <?= $this->Form->create(null) ?>
                <?= $this->Form->hidden('modo', ['value' => 'validar']) ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <?= $this->Form->control('resultado', [
                            'label' => 'Resultado a lançar',
                            'options' => [
                                'A' => 'Aprovado',
                                'R' => 'Reprovado',
                                'B' => 'Banco/Reserva',
                            ],
                            'empty' => 'Selecione',
                            'value' => $resultadoSelecionado,
                            'class' => 'form-select',
                            'required' => true,
                        ]) ?>
                    </div>
                    <div class="col-12">
                        <?= $this->Form->control('inscricoes', [
                            'type' => 'textarea',
                            'label' => 'Inscrições',
                            'value' => $idsRaw,
                            'rows' => 7,
                            'class' => 'form-control',
                            'placeholder' => 'Ex.: 123, 456, 789',
                            'required' => true,
                        ]) ?>
                    </div>
                    <div class="col-12 d-flex justify-content-end">
                        <?= $this->Form->button('Validar lote', ['class' => 'btn btn-primary']) ?>
                    </div>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>

    <?php if ($preview !== null): ?>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-light">
                    <div class="card-body">
                        <div class="text-muted small">IDs informados</div>
                        <div class="fs-4 fw-bold"><?= count($preview['ids_informados'] ?? []) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-success-subtle">
                    <div class="card-body">
                        <div class="text-muted small">Aptas para atualizar</div>
                        <div class="fs-4 fw-bold"><?= count($preview['aptas'] ?? []) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-danger-subtle">
                    <div class="card-body">
                        <div class="text-muted small">Recusadas</div>
                        <div class="fs-4 fw-bold"><?= count($preview['recusadas'] ?? []) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <div class="fw-semibold">Prévia da operação</div>
                        <div class="text-muted small">Resultado selecionado: <strong><?= h($resultadoLabel) ?></strong></div>
                    </div>
                    <?php if (!empty($preview['aptas'])): ?>
                        <?= $this->Form->create(null, ['class' => 'm-0']) ?>
                            <?= $this->Form->hidden('modo', ['value' => 'confirmar']) ?>
                            <?= $this->Form->hidden('resultado', ['value' => $resultadoSelecionado]) ?>
                            <?= $this->Form->hidden('inscricoes', ['value' => implode(',', $preview['ids_informados'] ?? [])]) ?>
                            <?= $this->Form->button('Confirmar lançamento', [
                                'class' => 'btn btn-success',
                                'onclick' => "return confirm('Confirma o lançamento do resultado para todas as inscrições aptas?');",
                            ]) ?>
                        <?= $this->Form->end() ?>
                    <?php endif; ?>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="fw-semibold mb-2">Inscrições aptas</div>
                            <?php if (!empty($preview['aptas'])): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Inscrição</th>
                                                <th>Origem</th>
                                                <th>Fase destino</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($preview['aptas'] as $item): ?>
                                                <tr>
                                                    <td>#<?= h((string)$item['id']) ?></td>
                                                    <td><?= h($origemLabels[(string)$item['origem']] ?? (string)$item['origem']) ?></td>
                                                    <td><?= h($faseLabels[(int)$item['fase_destino']] ?? (string)$item['fase_destino']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-muted small">Nenhuma inscrição apta para atualização.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="fw-semibold mb-2">Inscrições recusadas</div>
                            <?php if (!empty($preview['recusadas'])): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th></th>
                                                <th>Inscrição</th>
                                                <th>Motivo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($preview['recusadas'] as $item): ?>
                                                <tr>
                                                    <td class="text-center"><?= $iconeRecusa((string)$item['motivo']) ?></td>
                                                    <td>#<?= h((string)$item['id']) ?></td>
                                                    <td><?= h((string)$item['motivo']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-muted small">Nenhuma recusa na validação.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
