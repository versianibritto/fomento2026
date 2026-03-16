<div class="container mt-4">
    <div class="py-2 mb-3 border-bottom text-center">
        <?= $this->element('substituicoes_steps', [
            'current' => 'dadosBolsista',
            'inscricao' => $inscricao ?? null,
        ]) ?>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="p-3 mb-4 bg-light border rounded text-center">
                <h3 class="mb-2">Dados do bolsista</h3>
                <div class="fw-semibold">Substituição - <?= h($edital->nome) ?></div>
                <div class="text-muted mt-1">Revise os dados do novo bolsista e os anexos exigidos.</div>
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
                            <div class="border rounded bg-white p-3 p-md-4 mt-2">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                    <h5 class="mb-0">Ações do bolsista</h5>
                                    <span class="text-muted small">Informe o CPF para <?= empty($inscricao->bolsista) ? 'incluir' : 'alterar' ?> o bolsista.</span>
                                </div>

                                <?= $this->Form->create(null, ['class' => 'row g-2 align-items-end mb-3']) ?>
                                    <?= $this->Form->hidden('acao', ['value' => 'incluir_bolsista']) ?>
                                    <div class="col-md-7">
                                        <?= $this->Form->control('cpf_bolsista', [
                                            'label' => empty($inscricao->bolsista) ? 'CPF do bolsista' : 'Novo CPF do bolsista',
                                            'class' => 'form-control',
                                            'maxlength' => 25,
                                            'required' => true,
                                            'placeholder' => 'Somente números ou CPF formatado',
                                        ]) ?>
                                    </div>
                                    <div class="col-md-5 d-grid d-md-flex justify-content-md-end">
                                        <button type="submit" class="btn btn-primary"><?= empty($inscricao->bolsista) ? 'Incluir bolsista' : 'Alterar bolsista' ?></button>
                                    </div>
                                <?= $this->Form->end() ?>

                                <?php if (!empty($inscricao->bolsista)) : ?>
                                <div class="pt-2 border-top">
                                    <?= $this->Form->create(null, ['class' => 'd-grid d-md-flex justify-content-md-end']) ?>
                                        <?= $this->Form->hidden('acao', ['value' => 'excluir_bolsista']) ?>
                                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Deseja excluir o bolsista vinculado a esta inscrição?');">Excluir bolsista</button>
                                    <?= $this->Form->end() ?>
                                </div>
                                <?php endif; ?>
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
                        Todos os anexos disponibilizados nesta tela serão exigidos na finalização da substituição.<br>
                        Sempre que alterar um bolsista, verifique se os anexos estão de acordo com a atualização do aluno.
                    </p>
                    <div class="alert alert-info border mb-4">
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
                    <?= $this->Form->create(null, ['type' => 'file']) ?>
                        <?= $this->Form->hidden('acao', ['value' => 'salvar_anexos']) ?>
                        <?= $this->Form->hidden('alterar_anexo_tipo', ['value' => '', 'id' => 'alterar-anexo-tipo']) ?>
                        <?= $this->Form->hidden('anexo_acao', ['value' => '', 'id' => 'anexo-acao']) ?>
                        <?= $this->Form->hidden('anexo_tipo', ['value' => '', 'id' => 'anexo-tipo']) ?>
                        <fieldset class="anexos-areas">
                            <div class="mt-2 mb-2">
                                <div class="row g-3" id="anexos-dinamicos" data-programa-id="<?= (int)($edital->programa_id ?? 0) ?>">
                                    <?php foreach ($anexosTiposDefault as $tipo) : ?>
                                        <?php $tipoId = (int)$tipo->id; $rotulo = (string)$tipo->nome; $isCondicional = (int)($tipo->condicional ?? 0) === 1; ?>
                                        <div class="col-md-6 anexo-item-geral">
                                            <?php if (empty($anexos[$tipoId])) : ?>
                                                <?= $this->Form->control("anexos[$tipoId]", ['type' => 'file', 'label' => h($rotulo) . ($isCondicional ? ' (condicional)' : ''), 'class' => 'form-control']) ?>
                                            <?php else : ?>
                                                <label class="form-label d-block"><?= h($rotulo) ?><?= $isCondicional ? ' <span class="text-muted small">(condicional)</span>' : '' ?></label>
                                                <div class="anexo-arquivo-atual">
                                                    <div class="d-flex align-items-center justify-content-between gap-2 flex-nowrap">
                                                        <div class="small text-muted text-truncate"><?= h($anexos[$tipoId]) ?></div>
                                                        <div class="d-inline-flex align-items-center gap-1 flex-nowrap">
                                                            <a href="/uploads/anexos/<?= h($anexos[$tipoId]) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download"><i class="fa fa-download"></i></a>
                                                            <label for="anexo-<?= (int)$tipoId ?>" class="btn btn-light border btn-sm py-0 px-2 mb-0" title="Alterar"><i class="fa fa-edit"></i></label>
                                                            <button type="button" class="btn btn-light border-danger text-danger btn-sm py-0 px-2 mb-0" onclick="confirmarExclusaoAnexo(<?= (int)$tipoId ?>, this.form)" title="Excluir"><i class="fa fa-trash"></i></button>
                                                        </div>
                                                    </div>
                                                    <input id="anexo-<?= (int)$tipoId ?>" name="anexos[<?= (int)$tipoId ?>]" type="file" class="d-none anexo-file" data-tipo="<?= (int)$tipoId ?>">
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php foreach ($anexosTiposPrograma as $tipo) : ?>
                                        <?php $tipoId = (int)$tipo->id; $rotulo = (string)$tipo->nome; ?>
                                        <div class="col-md-6 anexo-item-geral">
                                            <?php if (empty($anexos[$tipoId])) : ?>
                                                <?= $this->Form->control("anexos[$tipoId]", ['type' => 'file', 'label' => h($rotulo), 'class' => 'form-control']) ?>
                                            <?php else : ?>
                                                <label class="form-label d-block"><?= h($rotulo) ?></label>
                                                <div class="anexo-arquivo-atual">
                                                    <div class="d-flex align-items-center justify-content-between gap-2 flex-nowrap">
                                                        <div class="small text-muted text-truncate"><?= h($anexos[$tipoId]) ?></div>
                                                        <div class="d-inline-flex align-items-center gap-1 flex-nowrap">
                                                            <a href="/uploads/anexos/<?= h($anexos[$tipoId]) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download"><i class="fa fa-download"></i></a>
                                                            <label for="anexo-<?= (int)$tipoId ?>" class="btn btn-light border btn-sm py-0 px-2 mb-0" title="Alterar"><i class="fa fa-edit"></i></label>
                                                            <button type="button" class="btn btn-light border-danger text-danger btn-sm py-0 px-2 mb-0" onclick="confirmarExclusaoAnexo(<?= (int)$tipoId ?>, this.form)" title="Excluir"><i class="fa fa-trash"></i></button>
                                                        </div>
                                                    </div>
                                                    <input id="anexo-<?= (int)$tipoId ?>" name="anexos[<?= (int)$tipoId ?>]" type="file" class="d-none anexo-file" data-tipo="<?= (int)$tipoId ?>">
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="row g-3 align-items-start">
                                    <div class="col-md-4">
                                        <?= $this->Form->control('primeiro_periodo', [
                                            'label' => 'Bolsista está no primeiro período?',
                                            'type' => 'select',
                                            'options' => ['1' => 'Sim', '0' => 'Não'],
                                            'empty' => 'Selecione',
                                            'class' => 'form-control',
                                            'value' => $inscricao->primeiro_periodo !== null ? (string)$inscricao->primeiro_periodo : null,
                                        ]) ?>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="row g-3">
                                        <?php foreach ($anexosTiposPrimeiroPeriodo as $tipo) : ?>
                                            <?php $tipoIdPrimeiroPeriodo = (int)$tipo->id; $rotuloPrimeiroPeriodo = (string)$tipo->nome; $targetPrimeiroPeriodo = '0'; ?>
                                            <div class="col-12 anexo-item-primeiro-periodo" data-primeiro-periodo-target="<?= h($targetPrimeiroPeriodo) ?>" data-anexo-tipo-id="<?= (int)$tipoIdPrimeiroPeriodo ?>">
                                                <?php if (empty($anexos[$tipoIdPrimeiroPeriodo])) : ?>
                                                    <?= $this->Form->control("anexos[$tipoIdPrimeiroPeriodo]", ['type' => 'file', 'label' => h($rotuloPrimeiroPeriodo), 'class' => 'form-control']) ?>
                                                <?php else : ?>
                                                    <label class="form-label d-block"><?= h($rotuloPrimeiroPeriodo) ?></label>
                                                    <div class="anexo-arquivo-atual">
                                                        <div class="d-flex align-items-center justify-content-between gap-2 flex-nowrap">
                                                            <div class="small text-muted text-truncate"><?= h($anexos[$tipoIdPrimeiroPeriodo]) ?></div>
                                                            <div class="d-inline-flex align-items-center gap-1 flex-nowrap">
                                                                <a href="/uploads/anexos/<?= h($anexos[$tipoIdPrimeiroPeriodo]) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download"><i class="fa fa-download"></i></a>
                                                                <label for="anexo-<?= (int)$tipoIdPrimeiroPeriodo ?>" class="btn btn-light border btn-sm py-0 px-2 mb-0" title="Alterar"><i class="fa fa-edit"></i></label>
                                                                <button type="button" class="btn btn-light border-danger text-danger btn-sm py-0 px-2 mb-0" onclick="confirmarExclusaoAnexo(<?= (int)$tipoIdPrimeiroPeriodo ?>, this.form)" title="Excluir"><i class="fa fa-trash"></i></button>
                                                            </div>
                                                        </div>
                                                        <input id="anexo-<?= (int)$tipoIdPrimeiroPeriodo ?>" name="anexos[<?= (int)$tipoIdPrimeiroPeriodo ?>]" type="file" class="d-none anexo-file" data-tipo="<?= (int)$tipoIdPrimeiroPeriodo ?>">
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
                                        <?php $tipoId = (int)$tipo->id; $rotulo = (string)$tipo->nome; ?>
                                        <div class="col-md-6">
                                            <?php if (empty($anexos[$tipoId])) : ?>
                                                <?= $this->Form->control("anexos[$tipoId]", ['type' => 'file', 'label' => h($rotulo), 'class' => 'form-control']) ?>
                                            <?php else : ?>
                                                <label class="form-label d-block"><?= h($rotulo) ?></label>
                                                <div class="anexo-arquivo-atual">
                                                    <div class="d-flex align-items-center justify-content-between gap-2 flex-nowrap">
                                                        <div class="small text-muted text-truncate"><?= h($anexos[$tipoId]) ?></div>
                                                        <div class="d-inline-flex align-items-center gap-1 flex-nowrap">
                                                            <a href="/uploads/anexos/<?= h($anexos[$tipoId]) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download"><i class="fa fa-download"></i></a>
                                                            <label for="anexo-<?= (int)$tipoId ?>" class="btn btn-light border btn-sm py-0 px-2 mb-0" title="Alterar"><i class="fa fa-edit"></i></label>
                                                            <button type="button" class="btn btn-light border-danger text-danger btn-sm py-0 px-2 mb-0" onclick="confirmarExclusaoAnexo(<?= (int)$tipoId ?>, this.form)" title="Excluir"><i class="fa fa-trash"></i></button>
                                                        </div>
                                                    </div>
                                                    <input id="anexo-<?= (int)$tipoId ?>" name="anexos[<?= (int)$tipoId ?>]" type="file" class="d-none anexo-file" data-tipo="<?= (int)$tipoId ?>">
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="mt-4">
                                <div class="row g-3 align-items-start">
                                    <div class="col-md-4">
                                        <?php if (!empty($cotaTravada)) : ?>
                                            <?= $this->Form->hidden('cota', ['value' => $inscricao->cota ?? '']) ?>
                                        <?php endif; ?>
                                        <?= $this->Form->control('cota', [
                                            'label' => 'Cota',
                                            'type' => 'select',
                                            'options' => $cotasDisponiveis ?? [],
                                            'empty' => empty($cotaTravada) ? 'Selecione' : false,
                                            'class' => 'form-control',
                                            'value' => $inscricao->cota ?? null,
                                            'disabled' => !empty($cotaTravada),
                                        ]) ?>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="row g-3">
                                            <?php foreach ($anexosTiposCota as $tipo) : ?>
                                                <?php $tipoId = (int)$tipo->id; $rotulo = (string)$tipo->nome; $regraCota = strtoupper(trim((string)($tipo->cota ?? ''))); $isCondicional = (int)($tipo->condicional ?? 0) === 1; ?>
                                                <div class="col-12 anexo-item-cota" data-cota-regra="<?= h($regraCota) ?>">
                                                    <?php if (empty($anexos[$tipoId])) : ?>
                                                        <?= $this->Form->control("anexos[$tipoId]", ['type' => 'file', 'label' => h($rotulo) . ($isCondicional ? ' (condicional)' : ''), 'class' => 'form-control']) ?>
                                                    <?php else : ?>
                                                        <label class="form-label d-block"><?= h($rotulo) ?><?= $isCondicional ? ' <span class="text-muted small">(condicional)</span>' : '' ?></label>
                                                        <div class="anexo-arquivo-atual">
                                                            <div class="d-flex align-items-center justify-content-between gap-2 flex-nowrap">
                                                                <div class="small text-muted text-truncate"><?= h($anexos[$tipoId]) ?></div>
                                                                <div class="d-inline-flex align-items-center gap-1 flex-nowrap">
                                                                    <a href="/uploads/anexos/<?= h($anexos[$tipoId]) ?>" target="_blank" class="btn btn-light border btn-sm py-0 px-2" title="Download"><i class="fa fa-download"></i></a>
                                                                    <label for="anexo-<?= (int)$tipoId ?>" class="btn btn-light border btn-sm py-0 px-2 mb-0" title="Alterar"><i class="fa fa-edit"></i></label>
                                                                    <button type="button" class="btn btn-light border-danger text-danger btn-sm py-0 px-2 mb-0" onclick="confirmarExclusaoAnexo(<?= (int)$tipoId ?>, this.form)" title="Excluir"><i class="fa fa-trash"></i></button>
                                                                </div>
                                                            </div>
                                                            <input id="anexo-<?= (int)$tipoId ?>" name="anexos[<?= (int)$tipoId ?>]" type="file" class="d-none anexo-file" data-tipo="<?= (int)$tipoId ?>">
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div id="anexos-vazio" class="alert alert-warning mt-3 d-none mb-0">
                                            Nenhum anexo condicional aplicável para a cota selecionada.
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

(function () {
    const selectPrimeiroPeriodo = document.querySelector('[name="primeiro_periodo"]');
    const itensPrimeiroPeriodo = document.querySelectorAll('.anexo-item-primeiro-periodo');
    const selectCota = document.querySelector('[name="cota"]');
    const itensCota = document.querySelectorAll('.anexo-item-cota');
    const avisoVazio = document.getElementById('anexos-vazio');

    function togglePrimeiroPeriodo() {
        if (!selectPrimeiroPeriodo || itensPrimeiroPeriodo.length === 0) {
            return;
        }
        const valorAtual = String(selectPrimeiroPeriodo.value || '');
        itensPrimeiroPeriodo.forEach(function (item) {
            const alvo = String(item.getAttribute('data-primeiro-periodo-target') || '');
            item.classList.toggle('d-none', alvo !== valorAtual);
        });
    }

    function toggleCota() {
        if (!selectCota || itensCota.length === 0) {
            return;
        }
        const valorAtual = String(selectCota.value || '').toUpperCase();
        let exibidos = 0;
        itensCota.forEach(function (item) {
            const regra = String(item.getAttribute('data-cota-regra') || '').toUpperCase();
            const regras = regra.split(',').map(function (parte) { return parte.trim(); }).filter(Boolean);
            const mostrar = valorAtual !== '' && regras.includes(valorAtual);
            item.classList.toggle('d-none', !mostrar);
            if (mostrar) {
                exibidos += 1;
            }
        });
        if (avisoVazio) {
            avisoVazio.classList.toggle('d-none', valorAtual === '' || exibidos > 0);
        }
    }

    if (selectPrimeiroPeriodo) {
        selectPrimeiroPeriodo.addEventListener('change', togglePrimeiroPeriodo);
        togglePrimeiroPeriodo();
    }
    if (selectCota) {
        selectCota.addEventListener('change', toggleCota);
        toggleCota();
    }
})();
</script>
