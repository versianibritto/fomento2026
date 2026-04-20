<style>
    .detalhe-projeto strong {
        width: 120px;
        display: inline-block;
    }
    .criterio-deletado {
        color: #900 !important;
    }
    .criterio-valido {
        color: #090 !important;
    }
    .avaliacao-box {
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 20px;
        background-color: #f9f9f9;
        margin-bottom: 30px;
    }
    .avaliacao-header h2 {
        font-size: 1.25rem;
        margin-bottom: 10px;
    }
</style>
<?php


$tipo = [
    'N' => 'Bolsa Nova IC',
    'Z' => 'Raic - Outras Agências',
    'V' => 'Raic - Renovação',
    'J' => 'Nova PDJ',
    'W' => 'Workshop'  
];
?>
<section>
    <div class="container">
        <div class="avaliacao-box">
            <?php if ($ab->situacao === 'F'): ?>
                <div class="avaliacao-header">
                    <h2>
                        <small>Avaliador: <?= ($this->request->getAttribute('identity')['yoda'] ? $ab->avaliador->usuario->nome : $ab->ordem) ?></small><br>
                        <small><?= ($tipo[$ab->tipo]) .': ID '. h($ab->bolsista) ?></small><br>
                        <small>Nota Avaliação: <?= h($ab->nota) ?></small>
                    </h2>
                </div>
                <br><br>

                <table class="table table-bordered table-hover mt-3">
                    <thead class="thead-light">
                        <tr>
                            <th>Critérios de Avaliação</th>
                            <th>Status</th>
                            <th>Nota</th>
                            <th>Intervalo de Valores</th>
                            <th>Parâmetros</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $avaliador = 0; ?>
                        <?php foreach ($avaliado as $q): ?>
                            <?php if ($q->avaliador_bolsista_id != $avaliador): ?>
                                <?php $avaliador = $q->avaliador_bolsista_id; ?>
                            <?php endif; ?>
                            <tr class="<?= $q->deleted == 1 ? 'criterio-deletado' : 'criterio-valido' ?>">
                                <td><?= h($q->question->questao) ?></td>
                                <td><?= $q->deleted == 1 ? 'Deletado' : 'Nota lançada' ?></td>
                                <td><?= h($q->nota) ?></td>
                                <td><?= h($q->question->limite_min . ' - ' . $q->question->limite_max) ?></td>
                                <td><?= h($q->question->prametros) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <br><br><br>

                <div class="mt-3">
                    <strong>Observação Geral:</strong><br>
                    <p><?= nl2br(h($ab->observacao)) ?></p>
                </div>
                                <br><br>
                <?php if (in_array($ab->tipo, ['V'])): ?>
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <strong>Parecer do Comitê de Ética:</strong><br>
                            <?= $ab->parecer == 'I' ? 'Não se aplica' : ($ab->parecer == 'N' ? 'É necessário, mas não anexou' : 'Anexou') ?>
                        </div>
                        <div class="col-md-4">
                            <strong>O aluno se destacou?</strong><br>
                            <?= $ab->destaque ? 'Sim' : 'Não' ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Indica o aluno ao Prêmio Destaque CNPq?</strong><br>
                            <?= $ab->indicado_premio_capes == 1 ? 'Avaliador informa - SIM' : 'Avaliador informa - NÃO' ?>
                        </div>
                        
                    </div>
                <?php endif; ?>

                <?php if (in_array($ab->tipo, ['V'])): ?>
                    <br><br>

                    <div class="row mt-3">
                            <div class="col-md-6">
                                <strong>A alteração do subprojeto mantém o objeto original?</strong><br>
                                <?= $ab->alteracao === null ? 'Não se aplica / Não foi alterado' : ($ab->alteracao === 1 ? 'Sim' : 'Não') ?>
                            </div>
                    </div>

                        <div class="mt-3">
                            <strong>Observação sobre a alteração do subprojeto:</strong><br>
                            <p><?= ($ab->alteracao === null ? 'Não se aplica / Não foi alterado':(($ab->observacao_alteracao==null || $ab->observacao_alteracao=='')?'Não informado':$ab->observacao_alteracao)) ?></p>
                        </div>
                <?php endif; ?>


                <?php if ($ab->tipo === 'N'): ?>
                    <div class="mt-4">
                        <strong>A alteração do projeto do orientador mantém o objeto original?</strong><br>
                        <?= $ab->alteracao === null ? 'Não se aplica' : ($ab->alteracao === 1 ? 'Sim' : 'Não') ?>
                        <div class="mt-2">
                            <strong>Observação sobre a alteração do projeto do orientador:</strong><br>
                            <p><?= $ab->alteracao === null ? 'Não se aplica' : nl2br(h($ab->observacao_alteracao)) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php if ($this->request->getAttribute('identity')['id'] == 8088 && $ab->situacao == 'F'): ?>
            <div class="text-right">
                <?= $this->Form->postLink(
                    '',
                    ['controller' => 'Avaliadors', 'action' => 'deletanotas'],
                    [
                        'data' => ['abId' => $ab->id],
                        'confirm' => 'Deseja REALMENTE deletar as notas?',
                        'class' => 'btn btn-sm btn-danger'
                    ]
                ) ?>
            </div>
        <?php endif; ?>
    </div>
</section>
