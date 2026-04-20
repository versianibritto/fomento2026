<?php
$tipoApresentacaoAtual = (string)($raic->tipo_apresentacao ?? '');
$localAtual = (string)($raic->local_apresentacao ?? '');
$dataAtual = !empty($raic->data_apresentacao) ? $raic->data_apresentacao->format('Y-m-d') : '';
?>

<div class="container mt-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="mb-0"><?= $jaAgendada ? 'Reagendar' : 'Agendar' ?> RAIC #<?= (int)$raic->id ?></h4>
        <?= $this->Html->link('Voltar', ['controller' => 'RaicNew', 'action' => 'ver', $raic->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <strong>Bolsista</strong><br>
                    <?= h((string)($raic->usuario->nome ?? 'Não informado')) ?>
                </div>
                <div class="col-md-4">
                    <strong>Orientador</strong><br>
                    <?= h((string)($raic->orientadore->nome ?? 'Não informado')) ?>
                </div>
                <div class="col-md-4">
                    <strong>Edital RAIC</strong><br>
                    <?= h((string)($raic->editai->nome ?? ('#' . (int)$raic->editai_id))) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?= $this->Form->create($raic) ?>
                <div class="row g-3">
                    <div class="col-md-4">
                        <?= $this->Form->control('data_apresentacao', [
                            'label' => 'Data de Apresentação',
                            'type' => 'date',
                            'value' => $dataAtual,
                            'required' => true,
                            'class' => 'form-control',
                        ]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $this->Form->control('tipo_apresentacao', [
                            'label' => 'Tipo de Apresentação',
                            'type' => 'select',
                            'options' => [
                                'O' => 'Oral',
                                'P' => 'Painel',
                            ],
                            'empty' => 'Selecione',
                            'value' => $tipoApresentacaoAtual,
                            'required' => true,
                            'class' => 'form-select',
                        ]) ?>
                    </div>
                    <div class="col-md-12">
                        <?= $this->Form->control('local_apresentacao', [
                            'label' => 'Local de Apresentação',
                            'type' => 'text',
                            'value' => $localAtual,
                            'required' => true,
                            'class' => 'form-control',
                        ]) ?>
                    </div>
                </div>

                <?php if (!$jaAgendada): ?>
                    <hr>

                    <h5 class="mb-3">Banca</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <?= $this->Form->control('avaliador_1', [
                                'label' => 'Avaliador 1',
                                'type' => 'select',
                                'options' => $avaliadores,
                                'empty' => 'Selecione',
                                'value' => $avaliador1Atual,
                                'required' => true,
                                'class' => 'form-select',
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $this->Form->control('avaliador_2', [
                                'label' => 'Avaliador 2',
                                'type' => 'select',
                                'options' => $avaliadores,
                                'empty' => 'Selecione',
                                'value' => $avaliador2Atual,
                                'required' => true,
                                'class' => 'form-select',
                            ]) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mt-4 d-flex gap-2">
                    <?= $this->Form->button($jaAgendada ? 'Salvar Reagendamento' : 'Salvar Agendamento', ['class' => 'btn btn-primary']) ?>
                    <?= $this->Html->link('Cancelar', ['controller' => 'RaicNew', 'action' => 'ver', $raic->id], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
