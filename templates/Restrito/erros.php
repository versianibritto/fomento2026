<div class="container-fluid p-1 pt-1">
    <div class="row mb-3">
        <div class="col-12">
            <h4>Erros do Sistema</h4>
            <p class="text-muted mb-0">Ocorrencias registradas para suporte.</p>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <?= $this->Form->create(null, ['type' => 'get']) ?>
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                <?= $this->Form->label('status', 'Status') ?>
                    <?= $this->Form->select('status', [
                        '' => 'Todos',
                        'N' => 'Novos',
                        'R' => 'Respondidos',
                    ], ['class' => 'form-control', 'value' => $status ?? '']) ?>
                </div>
                <div class="col-md-3">
                    <?= $this->Form->label('repeticao', 'Repeticao') ?>
                    <?= $this->Form->select('repeticao', [
                        '' => 'Todos',
                        '0' => 'Nao',
                        '1' => 'Sim',
                    ], ['class' => 'form-control', 'value' => $repeticao ?? '']) ?>
                </div>
                <div class="col-md-2">
                <?= $this->Form->label('mes', 'Mes') ?>
                    <?= $this->Form->select('mes', [
                        '' => 'Todos',
                        '1' => 'Janeiro',
                        '2' => 'Fevereiro',
                        '3' => 'Marco',
                        '4' => 'Abril',
                        '5' => 'Maio',
                        '6' => 'Junho',
                        '7' => 'Julho',
                        '8' => 'Agosto',
                        '9' => 'Setembro',
                        '10' => 'Outubro',
                        '11' => 'Novembro',
                        '12' => 'Dezembro',
                    ], ['class' => 'form-control', 'value' => $mes ?? '']) ?>
                </div>
                <div class="col-md-2">
                <?= $this->Form->label('ano', 'Ano') ?>
                    <?php
                    $anoAtual = (int)date('Y');
                    $anos = ['' => 'Todos'];
                    for ($a = $anoAtual; $a >= $anoAtual - 6; $a--) {
                        $anos[(string)$a] = (string)$a;
                    }
                    ?>
                    <?= $this->Form->select('ano', $anos, ['class' => 'form-control', 'value' => $ano ?? '']) ?>
                </div>
                <div class="col-md-2">
                    <?php if (!empty($usuarioId)): ?>
                        <?= $this->Form->hidden('usuario_id', ['value' => $usuarioId]) ?>
                    <?php endif; ?>
                    <?= $this->Form->button('Filtrar', ['class' => 'btn btn-primary']) ?>
                    <?= $this->Html->link('Limpar', ['action' => 'erros'], ['class' => 'btn btn-outline-secondary ms-2']) ?>
                    <?= $this->Html->link(
                        'Exportar Excel',
                        [
                            'action' => 'erros',
                            '?' => array_filter([
                                'status' => $status ?? null,
                                'repeticao' => $repeticao ?? null,
                                'mes' => $mes ?? null,
                                'ano' => $ano ?? null,
                                'usuario_id' => $usuarioId ?? null,
                                'acao' => 'excel',
                            ]),
                        ],
                        ['class' => 'btn btn-outline-success ms-2']
                    ) ?>
                </div>
            </div>
            <?= $this->Form->end() ?>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Data</th>
                            <th>Usuario</th>
                            <th>URL</th>
                            <th>Repeticao</th>
                            <th>Repeticao ID</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($erros->count() === 0): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-3">
                                    Nenhum registro encontrado para os filtros selecionados.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($erros as $erro): ?>
                                <tr>
                                    <td><?= h($erro->id) ?></td>
                                <td><?= $erro->created ? h($erro->created->i18nFormat('dd/MM/yyyy HH:mm:ss')) : '-' ?></td>
                                    <td><?= h($erro->usuario_nome ?? '-') ?></td>
                                    <td><?= h($erro->url ?? '-') ?></td>
                                    <td>
                                        <?php if (!empty($erro->repeticao)): ?>
                                            <strong><?= h($erro->repeticoes) ?> X</strong>
                                        <?php else: ?>
                                            <span class="badge bg-success">Nao</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= !empty($erro->repeticao_de_id) ? '#' . h($erro->repeticao_de_id) : '-' ?></td>
                                <td>
                                    <?php if (($erro->status ?? 'N') === 'R'): ?>
                                        <?php if ($erro->respondido_em): ?>
                                            <strong><em><?= h($erro->respondido_em->i18nFormat('dd/MM/yyyy HH:mm:ss')) ?></em></strong>
                                        <?php else: ?>
                                            <strong><em>Respondido</em></strong>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-danger">N</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if (empty($erro->repeticao) && (($erro->status ?? 'N') !== 'R')): ?>
                                        <?= $this->Html->link(
                                            '<i class="fa fa-envelope"></i>',
                                            ['action' => 'respondererro', $erro->id],
                                            ['class' => 'btn btn-sm btn-outline-primary', 'escape' => false, 'title' => 'Responder']
                                        ) ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pagination">
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->prev('« Previous') ?>
            <?= $this->Paginator->next('Next »') ?>
            <?= $this->Paginator->counter() ?>
        </div>
    </div>
</div>
