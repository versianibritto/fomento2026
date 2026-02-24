<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <h4 class="mb-1">Inscricao - <?= h($edital->nome) ?></h4>
            <div class="text-muted mb-3">O(a) sr(a) possui mais de uma inscricao neste programa. Indique qual deseja realizar a inscricao.</div>

            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Nome do bolsista</th>
                            <th>Nº da inscricao</th>
                            <th>Nº do projeto</th>
                            <th class="text-end">Acao</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($renovacoes as $renovacao): ?>
                            <tr>
                                <td><?= h($renovacao->bolsista_usuario->nome ?? '-') ?></td>
                                <td><?= h($renovacao->id ?? '-') ?></td>
                                <td><?= h($renovacao->projeto_id ?? '-') ?></td>
                                <td class="text-end">
                                    <?= $this->Html->link('Selecionar', [
                                        'controller' => 'Renovacoes',
                                        'action' => 'selecionarRenovacaoInscricao',
                                        $edital->id,
                                        '?' => [
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
