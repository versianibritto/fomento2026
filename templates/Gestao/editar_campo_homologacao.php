<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $inscricao
 * @var string $campo
 * @var string $tituloCampo
 * @var array<string, string> $opcoesCampo
 * @var string $valorAtual
 * @var string $valorInformado
 * @var string $justificativa
 * @var array<int, array<string, mixed>> $anexosNecessarios
 * @var array<int, string> $erros
 */
$this->assign('title', 'Editar ' . $tituloCampo);
$formatDataAnexo = static function ($data): string {
    if ($data === null || $data === '') {
        return '';
    }
    if ($data instanceof \DateTimeInterface) {
        return \DateTimeImmutable::createFromInterface($data)->format('d/m/Y H:i');
    }
    $timestamp = strtotime((string)$data);
    return $timestamp ? date('d/m/Y H:i', $timestamp) : (string)$data;
};
$valorAtualTexto = $opcoesCampo[$valorAtual] ?? ($valorAtual !== '' ? $valorAtual : 'Não informado');
$mensagemExigenciaAnexo = '';
if ($campo === 'primeiro_periodo') {
    $mensagemExigenciaAnexo = 'Quando Primeiro período for Não, o anexo de vínculo/declaração do período é obrigatório. Quando for Sim, nenhum anexo é exigido por esta regra.';
} elseif ($campo === 'cota') {
    $mensagemExigenciaAnexo = 'Quando a cota selecionada exigir comprovação, o anexo correspondente será obrigatório. Para cota Geral, normalmente não há anexo exigido por esta regra.';
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">Editar <?= h($tituloCampo) ?> #<?= (int)$inscricao->id ?></h4>
        <?= $this->Html->link(
            'Voltar para homologação',
            ['controller' => 'Gestao', 'action' => 'telahomologacao', (int)$inscricao->id],
            ['class' => 'btn btn-outline-secondary btn-sm rounded-pill px-3']
        ) ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="text-muted small">Inscrição</div>
                    <div class="fw-semibold">#<?= (int)$inscricao->id ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Bolsista</div>
                    <div class="fw-semibold"><?= h($inscricao->bolsista_usuario->nome ?? '-') ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Valor atual</div>
                    <div class="fw-semibold"><?= h($valorAtualTexto) ?></div>
                </div>
            </div>

            <?php if (!empty($erros)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($erros as $erro): ?>
                        <div><?= h($erro) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?= $this->Form->create(null, ['type' => 'file', 'class' => 'row g-3']) ?>
                <div class="col-md-6">
                    <?= $this->Form->control($campo, [
                        'label' => $tituloCampo,
                        'type' => 'select',
                        'options' => $opcoesCampo,
                        'empty' => '- Selecione -',
                        'value' => $valorInformado,
                        'class' => 'form-select',
                        'required' => true,
                    ]) ?>
                </div>

                <div class="col-12">
                    <?php if ($mensagemExigenciaAnexo !== ''): ?>
                        <div class="alert alert-info py-2 mb-3">
                            <?= h($mensagemExigenciaAnexo) ?>
                        </div>
                    <?php endif; ?>
                    <label class="form-label">Anexos relacionados ao valor selecionado</label>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Anexo atual</th>
                                    <th>Novo anexo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($anexosNecessarios)): ?>
                                    <tr>
                                        <td colspan="3" class="text-muted text-center">Nenhum anexo exigido para este valor.</td>
                                    </tr>
                                <?php endif; ?>
                                <?php foreach ($anexosNecessarios as $anexo): ?>
                                    <?php
                                        $tipoId = (int)($anexo['tipo_id'] ?? 0);
                                        $arquivo = (string)($anexo['arquivo'] ?? '');
                                        $obrigatorio = !empty($anexo['obrigatorio']);
                                        $exigirNovoUpload = !empty($anexo['exigir_novo_upload']);
                                        $usuarioAnexo = trim((string)($anexo['usuario_nome'] ?? ''));
                                        $dataAnexoFmt = $formatDataAnexo($anexo['data_inclusao'] ?? null);
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= h($anexo['tipo_nome'] ?? '-') ?></div>
                                            <?php if ($obrigatorio): ?>
                                                <span class="badge bg-light text-danger border border-danger fw-normal">Obrigatório</span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-warning border border-warning fw-normal">Será removido ao salvar</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($arquivo !== ''): ?>
                                                <?= h($arquivo) ?>
                                                <div class="small text-muted">
                                                    <?= $usuarioAnexo !== '' ? 'Incluído por ' . h($usuarioAnexo) : 'Usuário não informado' ?>
                                                    <?= $dataAnexoFmt !== '' ? ' em ' . h($dataAnexoFmt) : '' ?>
                                                </div>
                                                <a href="/uploads/anexos/<?= h($arquivo) ?>" target="_blank" class="btn btn-light border btn-sm mt-1">
                                                    <i class="fa fa-download me-1"></i> Baixar
                                                </a>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Não informado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($obrigatorio): ?>
                                                <input
                                                    name="anexos[<?= $tipoId ?>]"
                                                    type="file"
                                                    class="form-control form-control-sm"
                                                    <?= ($arquivo === '' || $exigirNovoUpload) ? 'required' : '' ?>
                                                >
                                                <div class="form-text">
                                                    <?php if ($exigirNovoUpload): ?>
                                                        A nova cota exige este mesmo tipo de anexo, mas o conteúdo deve ser atualizado. Envie um novo arquivo.
                                                    <?php elseif ($arquivo === ''): ?>
                                                        Obrigatório para salvar este valor.
                                                    <?php else: ?>
                                                        Opcional. Envie somente se precisar substituir o anexo atual.
                                                    <?php endif; ?>
                                                    Ao enviar, o anexo anterior do mesmo tipo será substituído.
                                                </div>
                                            <?php else: ?>
                                                <div class="text-warning-emphasis small">
                                                    Este anexo não é compatível com a condição selecionada e será removido ao salvar.
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-12">
                    <?= $this->Form->control('justificativa', [
                        'label' => 'Justificativa',
                        'type' => 'textarea',
                        'value' => $justificativa,
                        'class' => 'form-control',
                        'rows' => 4,
                        'required' => true,
                    ]) ?>
                </div>

                <div class="col-12 d-flex gap-2 justify-content-end">
                    <?= $this->Html->link(
                        'Cancelar',
                        ['controller' => 'Gestao', 'action' => 'telahomologacao', (int)$inscricao->id],
                        ['class' => 'btn btn-outline-secondary']
                    ) ?>
                    <?= $this->Form->button('Salvar e voltar para homologação', ['class' => 'btn btn-primary']) ?>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
