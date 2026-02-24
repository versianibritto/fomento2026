<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <h4 class="mb-1">Inscricao - <?= h($edital->nome) ?></h4>
            <div class="text-muted mb-3">Selecione a bolsa vigente para renovacao.</div>

            <?= $this->element('inscricoes_steps', ['edital' => $edital, 'current' => 'dadosBolsista']) ?>

            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Bolsista</th>
                            <th>CPF</th>
                            <th>Projeto</th>
                            <th class="text-end">Acao</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($renovacoes as $renovacao): ?>
                            <tr>
                                <td><?= h($renovacao->bolsista->nome ?? '-') ?></td>
                                <td>
                                    <?php
                                    $cpf = preg_replace('/\D+/', '', (string)($renovacao->bolsista->cpf ?? ''));
                                    if (strlen($cpf) === 11) {
                                        echo h(substr($cpf, 0, 3) . '.***.***-' . substr($cpf, -2));
                                    } else {
                                        echo h($renovacao->bolsista->cpf ?? '-');
                                    }
                                    ?>
                                </td>
                                <td><?= h($renovacao->projeto ?? $renovacao->projeto_id ?? '-') ?></td>
                                <td class="text-end">
                                    <?= $this->Html->link('Selecionar', [
                                        'controller' => 'Inscricoes',
                                        'action' => 'dadosBolsista',
                                        $edital->id,
                                        '?' => [
                                            'bolsista_id' => $renovacao->bolsista,
                                            'referencia_id' => $renovacao->id,
                                        ],
                                    ], ['class' => 'btn btn-sm btn-primary']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
