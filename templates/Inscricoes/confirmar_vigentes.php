<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <h4 class="mb-1">Inscricao - <?= h($edital->nome) ?></h4>
            <div class="card bg-light border-0 mb-3">
                <div class="card-body text-center">
                    <div class="text-danger fs-3 mb-1" aria-hidden="true">⚠️</div>
                    <div class="fw-bold text-danger text-uppercase mb-2">Atencao</div>
                    <div class="mx-auto" style="max-width: 640px;">
                        Voce possui inscricoes vigentes neste programa.<br>
                        Ao solicitar uma inscricao neste edital,
                        nao sera possivel realizar a renovacao delas.<br>
                        Ao confirmar esta ação não será possível futuramente renová-las.<br>
                        Deseja continuar?
                    </div>
                </div>
            </div>

            <?php if (!empty($vigentes)) : ?>
                <div class="table-responsive mb-3">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Bolsista</th>
                                <th>CPF</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vigentes as $vigente) : ?>
                                <tr>
                                    <td><?= h($vigente->bolsista_usuario->nome ?? '-') ?></td>
                                    <td>
                                        <?php
                                        $cpf = preg_replace('/\D+/', '', (string)($vigente->bolsista_usuario->cpf ?? ''));
                                        if (strlen($cpf) === 11) {
                                            echo h(substr($cpf, 0, 3) . '.***.***-' . substr($cpf, -2));
                                        } else {
                                            echo h($vigente->bolsista_usuario->cpf ?? '-');
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <?= $this->Form->create(null, [
                'url' => ['controller' => 'Inscricoes', 'action' => 'confirmarVigentes', $edital->id],
            ]) ?>
                <?= $this->Form->hidden('acao', ['value' => 'confirmar']) ?>
                <div class="d-flex justify-content-end gap-2">
                    <?= $this->Html->link('Cancelar', ['controller' => 'Index', 'action' => 'dashboard'], ['class' => 'btn btn-outline-secondary']) ?>
                    <?= $this->Form->button('Continuar', ['class' => 'btn btn-primary']) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
