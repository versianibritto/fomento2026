<div class="container mt-4">
    <div class="py-2 mb-3 border-bottom text-center">
        <?= $this->element('renovacoes_steps', [
            'edital' => $edital,
            'current' => 'dadosBolsistaRenovacao',
            'inscricao' => $inscricao ?? null,
        ]) ?>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="p-3 mb-4 bg-light border rounded text-center">
                <h3 class="mb-2">Dados do bolsista</h3>
                <div class="fw-semibold">Inscrição - <?= h($edital->nome) ?></div>
                <div class="text-muted mt-1">Rascunho: nenhum campo é obrigatório nesta etapa.</div>
            </div>

            <div class="card bg-light border-0 mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h4 class="mb-0">Dados pessoais</h4>
                    </div>
                    <?php
                    $cpfExib = '';
                    if (!empty($inscricao->bolsista_usuario->cpf)) {
                        $digits = preg_replace('/\D+/', '', (string)$inscricao->bolsista_usuario->cpf);
                        $cpfExib = strlen($digits) === 11
                            ? substr($digits, 0, 3) . '.' . substr($digits, 3, 3) . '.' . substr($digits, 6, 3) . '-' . substr($digits, 9, 2)
                            : $inscricao->bolsista_usuario->cpf;
                    }
                    $naoInformado = '<span class="badge bg-danger">Não informado</span>';
                    ?>
                    <div class="row g-3">
                        <?php if (empty($inscricao->bolsista)) : ?>
                            <div class="col-12">
                                <div class="alert alert-secondary mb-0">
                                    <div class="fw-semibold">Nenhum bolsista vinculado</div>
                                    <div class="small">Você pode incluir agora ou continuar o preenchimento e voltar depois.</div>
                                </div>
                            </div>
                        <?php else : ?>
                        <div class="col-md-4">
                            <div class="text-muted small">CPF</div>
                            <div class="fw-semibold"><?= $cpfExib ? h($cpfExib) : $naoInformado ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Nome</div>
                            <div class="fw-semibold"><?= !empty($inscricao->bolsista_usuario->nome) ? h($inscricao->bolsista_usuario->nome) : $naoInformado ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Nome social</div>
                            <div class="fw-semibold"><?= !empty($inscricao->bolsista_usuario->nome_social) ? h($inscricao->bolsista_usuario->nome_social) : $naoInformado ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">E-mail</div>
                            <div class="fw-semibold"><?= !empty($inscricao->bolsista_usuario->email) ? h($inscricao->bolsista_usuario->email) : $naoInformado ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">E-mail alternativo</div>
                            <div class="fw-semibold"><?= !empty($inscricao->bolsista_usuario->email_alternativo) ? h($inscricao->bolsista_usuario->email_alternativo) : $naoInformado ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">E-mail de contato</div>
                            <div class="fw-semibold"><?= !empty($inscricao->bolsista_usuario->email_contato) ? h($inscricao->bolsista_usuario->email_contato) : $naoInformado ?></div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex flex-wrap gap-2">
                                <?= $this->Html->link(
                                    'Visualizar dados do bolsista',
                                    ['controller' => 'Users', 'action' => 'ver', (int)$inscricao->bolsista],
                                    ['class' => 'btn btn-outline-secondary btn-sm']
                                ) ?>
                                <?= $this->Html->link(
                                    'Editar dados do bolsista',
                                    ['controller' => 'Users', 'action' => 'editar', (int)$inscricao->bolsista],
                                    ['class' => 'btn btn-outline-primary btn-sm']
                                ) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="col-12">
                            <div class="border rounded bg-white p-2 p-md-3 mt-2">
                                <?php
                                $refId = $referenciaDetalhe['id'] ?? null;
                                $refEdital = $referenciaDetalhe['nome_edital'] ?? null;
                                $refDataInicioRaw = $referenciaDetalhe['data_inicio'] ?? null;
                                $refDataInicio = '-';
                                if (!empty($refDataInicioRaw)) {
                                    $timestampRef = strtotime((string)$refDataInicioRaw);
                                    $refDataInicio = $timestampRef ? date('d/m/Y', $timestampRef) : (string)$refDataInicioRaw;
                                }
                                ?>
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                    <h5 class="mb-0">Vínculo do bolsista</h5>
                                </div>
                                <div class="small text-muted mt-2">
                                    Na renovação não é possível alterar o bolsista.
                                </div>
                                <div class="row g-2 mt-1">
                                    <div class="col-md-4">
                                        <div class="text-muted small">Inscrição de referência</div>
                                        <div class="fw-semibold"><?= $refId !== null ? h((string)$refId) : '-' ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-muted small">Data de início</div>
                                        <div class="fw-semibold"><?= h($refDataInicio) ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-muted small">Edital</div>
                                        <div class="fw-semibold"><?= !empty($refEdital) ? h((string)$refEdital) : '-' ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-light border-0 mb-3">
                <div class="card-body">
                    <h4 class="mb-1">Anexos</h4>
                    <p class="text-muted small mt-0 mb-4">
                        Envie os documentos por etapa. Itens com arquivo já enviado podem ser baixados, alterados ou excluídos.<br>
                        Todos os anexos disponibilizados nesta tela serão exigidos na finalização da inscrição.<br>
                        Verifique se os anexos estão de acordo com o bolsista vinculado nesta renovação.
                    </p>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="alert alert-info border h-100 mb-0 p-3">
                                <div class="fw-semibold mb-2">Termo de consentimento do bolsista</div>
                                <div class="small mb-2">
                                    Para o anexo de termo de consentimento, siga esta sequência:
                                </div>
                                <ol class="small mb-2 ps-3">
                                    <li>Baixe o modelo disponibilizado para este edital.</li>
                                    <li>Preencha os dados solicitados.</li>
                                    <li>Colete a(as) assinatura(as) necessária(as).</li>
                                    <li>Retorne a esta tela e faça o upload do termo assinado.</li>
                                    <li>É permitida assinatura eletrônica do Gov.br.</li>
                                </ol>
                                <?php if (!empty($edital->modelo_cons_bols)) : ?>
                                    <a href="/uploads/editais/<?= h($edital->modelo_cons_bols) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-download me-1"></i>Baixar modelo do termo de consentimento do bolsista
                                    </a>
                                <?php else : ?>
                                    <div class="small text-danger fw-semibold">Modelo de consentimento do bolsista não cadastrado para este edital.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?= $this->Form->create(null, ['type' => 'file']) ?>
                        <?= $this->Form->hidden('acao', ['value' => 'salvar_anexos']) ?>
                        <?= $this->Form->hidden('alterar_anexo_tipo', ['value' => '', 'id' => 'alterar-anexo-tipo']) ?>
                        <?= $this->Form->hidden('anexo_acao', ['value' => '', 'id' => 'anexo-acao']) ?>
                        <?= $this->Form->hidden('anexo_tipo', ['value' => '', 'id' => 'anexo-tipo']) ?>
                        <fieldset class="anexos-areas">
                            <div class="mt-2 mb-2">
                                <div class="row g-3" id="anexos-dinamicos" data-programa-id="<?= (int)($edital->programa_id ?? 0) ?>">
                                    <?php foreach ($anexosTiposDefault as $tipo) : ?>
                                        <?php
                                            $tipoId = (int)$tipo->id;
                                            $rotulo = (string)$tipo->nome;
                                            $isCondicional = (int)($tipo->condicional ?? 0) === 1;
                                        ?>
                                        <div class="col-md-6 anexo-item-geral">
                                            <?php if (empty($anexos[$tipoId])) : ?>
                                                <?= $this->Form->control("anexos[$tipoId]", [
                                                    'type' => 'file',
                                                    'label' => h($rotulo) . ($isCondicional ? ' (condicional)' : ''),
                                                    'class' => 'form-control',
                                                ]) ?>
                                            <?php else : ?>
                                                <label class="form-label d-block"><?= h($rotulo) ?><?= $isCondicional ? ' <span class="text-muted small">(condicional)</span>' : '' ?></label>
                                                <div class="anexo-arquivo-atual">
                                                    <div class="d-flex align-items-center justify-content-between gap-2 flex-nowrap">
                                                        <div class="small text-muted text-truncate">
                                                            <?= h($anexos[$tipoId]) ?>
                                                        </div>
                                                        <div class="d-inline-flex align-items-center gap-1 flex-nowrap">
                                                            <a href="/uploads/anexos/<?= h($anexos[$tipoId]) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                                                <i class="fa fa-download"></i>
                                                            </a>
                                                            <label for="anexo-<?= (int)$tipoId ?>" class="btn btn-light border btn-sm py-0 px-2 mb-0" title="Alterar">
                                                                <i class="fa fa-edit"></i>
                                                            </label>
                                                            <button
                                                                type="button"
                                                                class="btn btn-light border-danger text-danger btn-sm py-0 px-2 mb-0"
                                                                onclick="confirmarExclusaoAnexo(<?= (int)$tipoId ?>, this.form)"
                                                                title="Excluir"
                                                            >
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <input
                                                        id="anexo-<?= (int)$tipoId ?>"
                                                        name="anexos[<?= (int)$tipoId ?>]"
                                                        type="file"
                                                        class="d-none anexo-file"
                                                        data-tipo="<?= (int)$tipoId ?>"
                                                    >
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php foreach ($anexosTiposPrograma as $tipo) : ?>
                                        <?php
                                            $tipoId = (int)$tipo->id;
                                            $rotulo = (string)$tipo->nome;
                                        ?>
                                        <div class="col-md-6 anexo-item-geral">
                                            <?php if (empty($anexos[$tipoId])) : ?>
                                                <?= $this->Form->control("anexos[$tipoId]", [
                                                    'type' => 'file',
                                                    'label' => h($rotulo),
                                                    'class' => 'form-control',
                                                ]) ?>
                                            <?php else : ?>
                                                <label class="form-label d-block"><?= h($rotulo) ?></label>
                                                <div class="anexo-arquivo-atual">
                                                    <div class="d-flex align-items-center justify-content-between gap-2 flex-nowrap">
                                                        <div class="small text-muted text-truncate">
                                                            <?= h($anexos[$tipoId]) ?>
                                                        </div>
                                                        <div class="d-inline-flex align-items-center gap-1 flex-nowrap">
                                                            <a href="/uploads/anexos/<?= h($anexos[$tipoId]) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                                                <i class="fa fa-download"></i>
                                                            </a>
                                                            <label for="anexo-<?= (int)$tipoId ?>" class="btn btn-light border btn-sm py-0 px-2 mb-0" title="Alterar">
                                                                <i class="fa fa-edit"></i>
                                                            </label>
                                                            <button
                                                                type="button"
                                                                class="btn btn-light border-danger text-danger btn-sm py-0 px-2 mb-0"
                                                                onclick="confirmarExclusaoAnexo(<?= (int)$tipoId ?>, this.form)"
                                                                title="Excluir"
                                                            >
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <input
                                                        id="anexo-<?= (int)$tipoId ?>"
                                                        name="anexos[<?= (int)$tipoId ?>]"
                                                        type="file"
                                                        class="d-none anexo-file"
                                                        data-tipo="<?= (int)$tipoId ?>"
                                                    >
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="row g-3 align-items-start">
                                    <div class="col-md-6">
                                        <?= $this->Form->control('primeiro_periodo', [
                                            'label' => 'Bolsista está no primeiro período?',
                                            'type' => 'select',
                                            'options' => [
                                                '1' => 'Sim',
                                                '0' => 'Não',
                                            ],
                                            'empty' => 'Selecione',
                                            'class' => 'form-control',
                                            'value' => $inscricao->primeiro_periodo !== null ? (string)$inscricao->primeiro_periodo : null,
                                        ]) ?>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row g-3">
                                        <?php foreach ($anexosTiposPrimeiroPeriodo as $tipo) : ?>
                                            <?php
                                                $tipoIdPrimeiroPeriodo = (int)$tipo->id;
                                                $rotuloPrimeiroPeriodo = (string)$tipo->nome;
                                                $targetPrimeiroPeriodo = '0';
                                            ?>
                                            <div
                                                class="col-12 anexo-item-primeiro-periodo"
                                                data-primeiro-periodo-target="<?= h($targetPrimeiroPeriodo) ?>"
                                                data-anexo-tipo-id="<?= (int)$tipoIdPrimeiroPeriodo ?>"
                                            >
                                                <?php if (empty($anexos[$tipoIdPrimeiroPeriodo])) : ?>
                                                    <?= $this->Form->control("anexos[$tipoIdPrimeiroPeriodo]", [
                                                        'type' => 'file',
                                                        'label' => h($rotuloPrimeiroPeriodo),
                                                        'class' => 'form-control',
                                                    ]) ?>
                                                <?php else : ?>
                                                    <label class="form-label d-block"><?= h($rotuloPrimeiroPeriodo) ?></label>
                                                    <div class="anexo-arquivo-atual">
                                                        <div class="d-flex align-items-center justify-content-between gap-2 flex-nowrap">
                                                            <div class="small text-muted text-truncate">
                                                                <?= h($anexos[$tipoIdPrimeiroPeriodo]) ?>
                                                            </div>
                                                            <div class="d-inline-flex align-items-center gap-1 flex-nowrap">
                                                                <a href="/uploads/anexos/<?= h($anexos[$tipoIdPrimeiroPeriodo]) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                                                    <i class="fa fa-download"></i>
                                                                </a>
                                                                <label for="anexo-<?= (int)$tipoIdPrimeiroPeriodo ?>" class="btn btn-light border btn-sm py-0 px-2 mb-0" title="Alterar">
                                                                    <i class="fa fa-edit"></i>
                                                                </label>
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-light border-danger text-danger btn-sm py-0 px-2 mb-0"
                                                                    onclick="confirmarExclusaoAnexo(<?= (int)$tipoIdPrimeiroPeriodo ?>, this.form)"
                                                                    title="Excluir"
                                                                >
                                                                    <i class="fa fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <input
                                                            id="anexo-<?= (int)$tipoIdPrimeiroPeriodo ?>"
                                                            name="anexos[<?= (int)$tipoIdPrimeiroPeriodo ?>]"
                                                            type="file"
                                                            class="d-none anexo-file"
                                                            data-tipo="<?= (int)$tipoIdPrimeiroPeriodo ?>"
                                                        >
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($anexosTiposCondicional)) : ?>
                            <div class="mt-4">
                                <div class="row g-3">
                                    <?php foreach ($anexosTiposCondicional as $tipo) : ?>
                                        <?php
                                            $tipoId = (int)$tipo->id;
                                            $rotulo = (string)$tipo->nome;
                                        ?>
                                        <div class="col-md-6">
                                            <?php if (empty($anexos[$tipoId])) : ?>
                                                <?= $this->Form->control("anexos[$tipoId]", [
                                                    'type' => 'file',
                                                    'label' => h($rotulo),
                                                    'class' => 'form-control',
                                                ]) ?>
                                            <?php else : ?>
                                                <label class="form-label d-block"><?= h($rotulo) ?></label>
                                                <div class="anexo-arquivo-atual">
                                                    <div class="d-flex align-items-center justify-content-between gap-2 flex-nowrap">
                                                        <div class="small text-muted text-truncate">
                                                            <?= h($anexos[$tipoId]) ?>
                                                        </div>
                                                        <div class="d-inline-flex align-items-center gap-1 flex-nowrap">
                                                            <a href="/uploads/anexos/<?= h($anexos[$tipoId]) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                                                <i class="fa fa-download"></i>
                                                            </a>
                                                            <label for="anexo-<?= (int)$tipoId ?>" class="btn btn-light border btn-sm py-0 px-2 mb-0" title="Alterar">
                                                                <i class="fa fa-edit"></i>
                                                            </label>
                                                            <button
                                                                type="button"
                                                                class="btn btn-light border-danger text-danger btn-sm py-0 px-2 mb-0"
                                                                onclick="confirmarExclusaoAnexo(<?= (int)$tipoId ?>, this.form)"
                                                                title="Excluir"
                                                            >
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <input
                                                        id="anexo-<?= (int)$tipoId ?>"
                                                        name="anexos[<?= (int)$tipoId ?>]"
                                                        type="file"
                                                        class="d-none anexo-file"
                                                        data-tipo="<?= (int)$tipoId ?>"
                                                    >
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="mt-4">
                                <div class="row g-3 align-items-start">
                                    <div class="col-md-6">
                                        <?= $this->Form->control('cota', [
                                            'label' => 'Cota',
                                            'type' => 'select',
                                            'options' => $cotas ?? [],
                                            'empty' => 'Selecione',
                                            'class' => 'form-control',
                                            'value' => $inscricao->cota ?? null,
                                            'disabled' => true,
                                        ]) ?>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row g-3">
                                            <?php foreach ($anexosTiposCota as $tipo) : ?>
                                                <?php
                                                    $tipoId = (int)$tipo->id;
                                                    $rotulo = (string)$tipo->nome;
                                                    $regraCota = strtoupper(trim((string)($tipo->cota ?? '')));
                                                    $isCondicional = (int)($tipo->condicional ?? 0) === 1;
                                                ?>
                                                <div
                                                    class="col-md-6 anexo-item-cota"
                                                    data-cota-regra="<?= h($regraCota) ?>"
                                                >
                                                    <?php if (empty($anexos[$tipoId])) : ?>
                                                        <?= $this->Form->control("anexos[$tipoId]", [
                                                            'type' => 'file',
                                                            'label' => h($rotulo) . ($isCondicional ? ' (condicional)' : ''),
                                                            'class' => 'form-control',
                                                        ]) ?>
                                                    <?php else : ?>
                                                        <label class="form-label d-block"><?= h($rotulo) ?><?= $isCondicional ? ' <span class="text-muted small">(condicional)</span>' : '' ?></label>
                                                        <div class="anexo-arquivo-atual">
                                                            <div class="d-flex align-items-center justify-content-between gap-2 flex-nowrap">
                                                                <div class="small text-muted text-truncate">
                                                                    <?= h($anexos[$tipoId]) ?>
                                                                </div>
                                                                <div class="d-inline-flex align-items-center gap-1 flex-nowrap">
                                                                    <a href="/uploads/anexos/<?= h($anexos[$tipoId]) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download">
                                                                        <i class="fa fa-download"></i>
                                                                    </a>
                                                                    <label for="anexo-<?= (int)$tipoId ?>" class="btn btn-light border btn-sm py-0 px-2 mb-0" title="Alterar">
                                                                        <i class="fa fa-edit"></i>
                                                                    </label>
                                                                    <button
                                                                        type="button"
                                                                        class="btn btn-light border-danger text-danger btn-sm py-0 px-2 mb-0"
                                                                        onclick="confirmarExclusaoAnexo(<?= (int)$tipoId ?>, this.form)"
                                                                        title="Excluir"
                                                                    >
                                                                        <i class="fa fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <input
                                                                id="anexo-<?= (int)$tipoId ?>"
                                                                name="anexos[<?= (int)$tipoId ?>]"
                                                                type="file"
                                                                class="d-none anexo-file"
                                                                data-tipo="<?= (int)$tipoId ?>"
                                                            >
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div id="anexos-vazio" class="anexos-cota-vazio mt-2 d-none">
                                            Nenhum anexo condicional para a cota vinculada.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">Salvar rascunho e continuar</button>
                                </div>  
                            </div>
                        </fieldset>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.anexos-areas .anexo-arquivo-atual {
    border: 1px solid #dfe3e7;
    border-radius: .5rem;
    background: #f8f9fa;
    padding: .5rem .75rem;
}
.anexos-areas .form-label {
    font-weight: 400;
    margin-bottom: .35rem;
}
.anexos-areas .anexos-cota-vazio {
    border: 1px dashed #d9dee5;
    border-radius: .5rem;
    background: #f8f9fb;
    color: #6c757d;
    font-size: .875rem;
    padding: .5rem .75rem;
}
</style>
<script>
document.querySelectorAll('.anexo-file').forEach(function (input) {
    input.addEventListener('change', function () {
        if (!this.files || this.files.length === 0) {
            return;
        }
        const form = this.closest('form');
        if (!form) {
            return;
        }
        const hidden = form.querySelector('#alterar-anexo-tipo');
        if (hidden) {
            hidden.value = this.getAttribute('data-tipo') || '';
        }
        const anexoAcao = form.querySelector('#anexo-acao');
        const anexoTipo = form.querySelector('#anexo-tipo');
        if (anexoAcao) {
            anexoAcao.value = 'alterar';
        }
        if (anexoTipo) {
            anexoTipo.value = this.getAttribute('data-tipo') || '';
        }
        form.submit();
    });
});

function confirmarExclusaoAnexo(tipoId, form) {
    if (!form) {
        return;
    }
    if (!confirm('Deseja excluir este anexo?')) {
        return;
    }
    const anexoAcao = form.querySelector('#anexo-acao');
    const anexoTipo = form.querySelector('#anexo-tipo');
    if (anexoAcao) {
        anexoAcao.value = 'excluir';
    }
    if (anexoTipo) {
        anexoTipo.value = String(tipoId || '');
    }
    form.submit();
}

function regraIncluiValor(regra, valor) {
    const texto = String(regra || '').trim().toUpperCase();
    if (!texto) {
        return true;
    }
    const alvo = String(valor || '').trim().toUpperCase();
    if (!alvo) {
        return false;
    }
    const itens = texto.split(',').map(function (v) { return v.trim(); }).filter(Boolean);
    return itens.includes(alvo);
}

function aplicarFiltroAnexos() {
    const selectCota = document.getElementById('cota');
    const cotaSelecionada = selectCota ? String(selectCota.value || '').trim().toUpperCase() : '';
    const itens = document.querySelectorAll('.anexo-item-cota');
    let visiveis = 0;

    itens.forEach(function (item) {
        const regraCota = item.getAttribute('data-cota-regra') || '';
        const exibir = regraIncluiValor(regraCota, cotaSelecionada);

        item.classList.toggle('d-none', !exibir);
        if (!exibir) {
            const inputFile = item.querySelector('input[type=\"file\"]');
            if (inputFile) {
                inputFile.value = '';
            }
            return;
        }
        visiveis += 1;
    });

    const alertaVazio = document.getElementById('anexos-vazio');
    if (alertaVazio) {
        alertaVazio.classList.toggle('d-none', visiveis > 0);
    }
}

function aplicarFiltroPrimeiroPeriodo() {
    const selectPrimeiroPeriodo = document.getElementById('primeiro-periodo');
    const valorSelecionado = selectPrimeiroPeriodo ? String(selectPrimeiroPeriodo.value || '').trim() : '';
    const itens = document.querySelectorAll('.anexo-item-primeiro-periodo');

    itens.forEach(function (item) {
        const alvo = String(item.getAttribute('data-primeiro-periodo-target') || '').trim();
        const exibir = alvo === '' || (valorSelecionado !== '' && valorSelecionado === alvo);
        item.classList.toggle('d-none', !exibir);
        if (!exibir) {
            const inputFile = item.querySelector('input[type="file"]');
            if (inputFile) {
                inputFile.value = '';
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const selectCota = document.getElementById('cota');
    if (selectCota) {
        selectCota.addEventListener('change', aplicarFiltroAnexos);
    }
    const selectPrimeiroPeriodo = document.getElementById('primeiro-periodo');
    if (selectPrimeiroPeriodo) {
        selectPrimeiroPeriodo.addEventListener('change', aplicarFiltroPrimeiroPeriodo);
    }
    aplicarFiltroAnexos();
    aplicarFiltroPrimeiroPeriodo();
});
</script>
