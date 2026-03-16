<?php
$modo = (string)($modo ?? 'A');
$titulo = (string)($titulo ?? 'Processamento de Vigências');
$editalId = (int)($editalId ?? 0);
$preview = $preview ?? null;
$descricao = $modo === 'A'
    ? 'Ativa massivamente as inscrições aprovadas do edital, preenchendo data de início com a vigência do edital, marcando vigente e levando a fase para 11.'
    : 'Encerra massivamente as bolsas vigentes do edital, preenchendo data de fim com a vigência final do edital e marcando vigente como não.';
$dataAplicacao = null;
if (!empty($preview['edital'])) {
    $dataAplicacao = $modo === 'A'
        ? (!empty($preview['edital']->inicio_vigencia) ? $preview['edital']->inicio_vigencia->i18nFormat('dd/MM/yyyy') : '--')
        : (!empty($preview['edital']->fim_vigencia) ? $preview['edital']->fim_vigencia->i18nFormat('dd/MM/yyyy') : '--');
}
?>

<div class="container mt-4">
    <style>
    .vigencias-alerta {
        border-radius: .85rem;
    }
    .vigencias-alerta.vigencias-alerta-destaque {
        border-width: 2px;
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .08);
        padding: 1rem 1.1rem;
    }
    .vigencias-preview-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        padding: 0 0 1rem;
        margin: 0 0 1rem;
        border-bottom: 1px solid #eceff3;
    }
    .vigencias-preview-head .titulo {
        font-weight: 600;
        margin-bottom: .2rem;
    }
    .vigencias-preview-head .subtitulo {
        color: #6c757d;
        font-size: .92rem;
    }
    @media (max-width: 767.98px) {
        .vigencias-preview-head {
            flex-direction: column;
            align-items: stretch;
        }
        .vigencias-preview-head form .btn {
            width: 100%;
        }
    }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold"><?= h($titulo) ?></h4>
        <a href="<?= $this->Url->build(['controller' => 'Index', 'action' => 'dashyoda']) ?>"
           class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa fa-arrow-left me-1"></i> Voltar para o Dashboard
        </a>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="mb-3">
                <div class="fw-semibold">Selecione o edital</div>
                <div class="text-muted small"><?= h($descricao) ?></div>
            </div>

            <?= $this->Form->create(null) ?>
                <?= $this->Form->hidden('etapa', ['value' => 'validar']) ?>
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <?= $this->Form->control('edital_id', [
                            'label' => 'Edital',
                            'options' => $editais ?? [],
                            'empty' => 'Selecione',
                            'value' => $editalId ?: '',
                            'class' => 'form-select',
                            'required' => true,
                        ]) ?>
                    </div>
                    <div class="col-md-4 d-flex justify-content-end">
                        <?= $this->Form->button('Validar lote', ['class' => 'btn btn-primary']) ?>
                    </div>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>

    <?php if ($preview !== null && !empty($preview['edital'])): ?>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-light">
                    <div class="card-body">
                        <div class="text-muted small">Edital</div>
                        <div class="fw-semibold"><?= h($preview['edital']->nome ?? '-') ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-success-subtle">
                    <div class="card-body">
                        <div class="text-muted small">Aptas</div>
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
                <div class="alert <?= $modo === 'A' ? 'alert-success' : 'alert-warning' ?> border mb-4 vigencias-alerta vigencias-alerta-destaque">
                    <?php if ($modo === 'A'): ?>
                        <div class="fw-bold mb-1">Atenção à ativação massiva</div>
                        <div>
                            Na confirmação, todas as inscrições aptas serão ativadas com <strong>data de início em <?= h($dataAplicacao ?? '--') ?></strong>, <strong>vigente = Sim</strong> e <strong>fase <?= h($faseLabels[11] ?? '11') ?></strong>.
                        </div>
                        <div class="mt-2">
                            Após a ativação, o orientador <strong>já poderá manipular a inscrição</strong> normalmente, inclusive para <strong>substituir, cancelar e demais ações permitidas</strong>. As inscrições passarão para o status <strong>Ativo</strong> e <strong>aparecerão no relatório de vigentes</strong>.
                        </div>
                    <?php else: ?>
                        <div class="fw-bold mb-1">Atenção ao encerramento massivo</div>
                        <div>
                            Na confirmação, todas as bolsas aptas serão encerradas com <strong>data de fim em <?= h($dataAplicacao ?? '--') ?></strong>, <strong>vigente = Não</strong> e <strong>fase <?= h($faseLabels[17] ?? '17') ?></strong>.
                        </div>
                        <div class="mt-2">
                            Após o encerramento, o orientador <strong>não poderá mais fazer nenhuma gestão da bolsa</strong> e esses registros <strong>não aparecerão mais no relatório de vigentes</strong>.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="vigencias-preview-head">
                    <div>
                        <div class="titulo">Prévia do processamento</div>
                        <div class="subtitulo">Revise os registros aptos e recusados antes de confirmar a operação.</div>
                    </div>
                    <?php if (!empty($preview['aptas'])): ?>
                        <?= $this->Form->create(null, ['class' => 'm-0']) ?>
                            <?= $this->Form->hidden('etapa', ['value' => 'confirmar']) ?>
                            <?= $this->Form->hidden('edital_id', ['value' => (int)$preview['edital']->id]) ?>
                            <?= $this->Form->button('Confirmar processamento', [
                                'class' => 'btn btn-success',
                                'onclick' => "return confirm('Confirma o processamento massivo deste edital?');",
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
                                                <th>Fase atual</th>
                                                <th>Fase destino</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($preview['aptas'] as $item): ?>
                                                <tr>
                                                    <td>#<?= h((string)$item['id']) ?></td>
                                                    <td><?= h($faseLabels[(int)$item['fase_atual']] ?? (string)$item['fase_atual']) ?></td>
                                                    <td><?= h($faseLabels[(int)$item['fase_destino']] ?? (string)$item['fase_destino']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-muted small">Nenhum registro apto para processamento.</div>
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
                                                <th>Inscrição</th>
                                                <th>Motivo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($preview['recusadas'] as $item): ?>
                                                <tr>
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
