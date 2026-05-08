<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface|null $inscricao
 * @var array<int, string> $colunas
 * @var array<string, array<string, mixed>> $metadados
 * @var int $buscaId
 */
$this->assign('title', 'Editar projeto_bolsistas');

$valorCampo = function ($valor): string {
    if ($valor === null) {
        return '';
    }
    if ($valor instanceof \DateTimeInterface) {
        return $valor->format('Y-m-d H:i:s');
    }
    if (is_bool($valor)) {
        return $valor ? '1' : '0';
    }

    return (string)$valor;
};
?>
<section class="mt-n3">
    <div class="card card-primary card-outline mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h2 class="mb-1">Editar projeto_bolsistas</h2>
                    <div class="text-muted">Ação restrita à TI. Apenas campos autorizados ficam disponíveis e toda alteração grava histórico com justificativa obrigatória.</div>
                </div>
                <div class="d-flex gap-2">
                    <?= $this->Html->link('Voltar', ['controller' => 'Restrito', 'action' => 'index'], ['class' => 'btn btn-outline-dark']) ?>
                    <?php if ($inscricao): ?>
                        <?= $this->Html->link('Visualizar inscrição', ['controller' => 'Padrao', 'action' => 'visualizar', (int)$inscricao->id], ['class' => 'btn btn-outline-secondary']) ?>
                    <?php endif; ?>
                </div>
            </div>
            <hr>

            <?= $this->Form->create(null, ['type' => 'get', 'class' => 'row g-2 align-items-end mb-4']) ?>
                <div class="col-md-4">
                    <?= $this->Form->control('id', [
                        'label' => 'ID da inscrição',
                        'type' => 'number',
                        'value' => $buscaId ?: '',
                        'class' => 'form-control',
                        'required' => true,
                    ]) ?>
                </div>
                <div class="col-md-2">
                    <?= $this->Form->button('Carregar', ['class' => 'btn btn-primary w-100']) ?>
                </div>
            <?= $this->Form->end() ?>

            <?php if ($inscricao): ?>
                <div class="alert alert-danger">
                    Esta tela altera diretamente campos autorizados da tabela <strong>projeto_bolsistas</strong>. Revise os dados antes de salvar.
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">ID</div>
                            <strong><?= (int)$inscricao->id ?></strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">Fase</div>
                            <strong><?= h((string)($inscricao->fase->nome ?? $inscricao->fase_id ?? '-')) ?></strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">Bolsista</div>
                            <strong><?= h((string)($inscricao->bolsista_usuario->nome ?? $inscricao->bolsista ?? '-')) ?></strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">Edital</div>
                            <strong><?= h((string)($inscricao->editai->nome ?? $inscricao->editai_id ?? '-')) ?></strong>
                        </div>
                    </div>
                </div>

                <?= $this->Form->create(null, [
                    'class' => 'row g-3',
                    'onsubmit' => "return confirm('Confirma a alteração direta em projeto_bolsistas? Esta ação será registrada em histórico.');",
                ]) ?>
                    <?= $this->Form->hidden('id', ['value' => (int)$inscricao->id]) ?>

                    <div class="col-12">
                        <?= $this->Form->control('_justificativa_tecnica', [
                            'label' => 'Justificativa da alteração técnica',
                            'type' => 'textarea',
                            'class' => 'form-control',
                            'rows' => 3,
                            'required' => true,
                        ]) ?>
                    </div>

                    <?php foreach ($colunas as $coluna): ?>
                        <?php
                            $tipo = (string)($metadados[$coluna]['type'] ?? '');
                            $isTextoLongo = in_array($tipo, ['text'], true);
                            $valor = $valorCampo($inscricao->get($coluna));
                        ?>
                        <div class="<?= $isTextoLongo ? 'col-12' : 'col-md-4' ?>">
                            <?= $this->Form->control($coluna, [
                                'label' => $coluna . ' (' . $tipo . ')',
                                'type' => $isTextoLongo ? 'textarea' : 'text',
                                'value' => $valor,
                                'class' => 'form-control',
                                'rows' => $isTextoLongo ? 4 : null,
                                'required' => false,
                            ]) ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="col-12 d-flex justify-content-end gap-2">
                        <?= $this->Html->link('Cancelar', ['controller' => 'Padrao', 'action' => 'visualizar', (int)$inscricao->id], ['class' => 'btn btn-outline-secondary']) ?>
                        <?= $this->Form->button('Salvar alteração técnica', [
                            'class' => 'btn btn-danger',
                        ]) ?>
                    </div>
                <?= $this->Form->end() ?>
            <?php endif; ?>
        </div>
    </div>
</section>
