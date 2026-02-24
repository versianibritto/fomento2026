<div class="card card-primary card-outline" id="carga-modelo-lote">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h4 class="mb-0">Lista dos Editais</h4>
            <?=$this->Html->link("Cadastrar novo edital", ['controller' => 'Editais', 'action' => 'gravar'], ['class' => 'btn btn-sm btn-success', 'escape' => false]); ?>
        </div>
        <hr>
        <?=$this->Form->create(null, ['url' => ['controller' => 'Editais', 'action' => 'lista'], 'class' => 'row g-3 align-items-end']);?>
            <div class="col-md-4">
            <?=$this->Form->control('programa_id',['label'=>'Programa','options'=>$programas,'empty'=>'- Todos -','class'=>'form-control','style'=>'height:34px'])?>

            </div>
            <div class="col-md-4">
                <?=$this->Form->control('origem', [
                    'label' => 'Forma de Ingresso',
                    'options' => $origem,
                    'empty' => '- Todos -',
                    'class' => 'form-select',
                ])?>
            </div>
            <div class="col-md-4">
                <?=$this->Form->control('ativo', [
                    'label' => 'Ativo',
                    'options' => ['1' => 'Sim', '0' => 'Não'],
                    'empty' => '- Todos -',
                    'class' => 'form-select',
                ])?>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-search me-2"></i> Buscar
                </button>
            </div>
        <?=$this->Form->end()?>        
    </div>
</div>
<div class="card card-primary card-outline">
    <div class="card-body">
        <h5 class="mb-3">Carga única de modelo para múltiplos editais</h5>
        <?=$this->Form->create(null, [
            'url' => ['controller' => 'Editais', 'action' => 'modeloLote'],
            'type' => 'file',
            'class' => 'row g-3 align-items-end',
        ]);?>
            <div class="col-md-4">
                <?=$this->Form->control('tipo_modelo', [
                    'label' => 'Tipo de arquivo',
                    'type' => 'select',
                    'required' => true,
                    'empty' => '- Selecione -',
                    'options' => [
                        'modelo_cons_bols' => 'Modelo consentimento bolsista',
                        'modelo_cons_coor' => 'Modelo consentimento coorientador',
                        'modelo_relat_bols' => 'Modelo relatório bolsista',
                    ],
                    'class' => 'form-select',
                ])?>
            </div>
            <div class="col-md-4">
                <?=$this->Form->control('arquivo_modelo_lote', [
                    'label' => 'Arquivo (máx. 2MB)',
                    'type' => 'file',
                    'required' => true,
                    'class' => 'form-control',
                ])?>
            </div>
            <div class="col-md-12">
                <?=$this->Form->control('editais_ids', [
                    'label' => 'Editais de destino',
                    'type' => 'select',
                    'multiple' => true,
                    'required' => true,
                    'options' => $editaisAtivos,
                    'class' => 'form-select',
                    'size' => 8,
                ])?>
                <small class="text-muted">Selecione um ou mais editais ativos para receber o mesmo arquivo.</small>
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">
                    Aplicar arquivo em lote
                </button>
            </div>
        <?=$this->Form->end()?>
    </div>
</div>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Edital</th>
                        <th>Programa</th>
                        <th class="d-none d-md-table-cell">Ingresso</th>
                        <th class="d-none d-md-table-cell">Inscricao</th>
                        <th class="d-none d-md-table-cell">Ativo</th>
                        <th class="d-none d-md-table-cell">Ver</th>
                        <th class="actions">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($editais as $u) { ?>
                    <tr>
                        <td>
                            <?=$this->Html->link(
                                ($u->id . ' - ' . ($u->nome == null ? 'Não Informado' : $u->nome)),
                                ['action' => 'ver', $u->id]
                            )?>
                        </td>
                        <td><?=($u->programa_id ? ($u->programa->sigla ?? 'Não informado') : 'Não informado')?></td>
                        <td class="d-none d-md-table-cell"><?= ($u->origem==null?'Não informado':($origem[$u->origem]))?></td>
                        <td class="d-none d-md-table-cell">
                            <?php if ($u->inicio_inscricao && $u->fim_inscricao) { ?>
                                <?=$u->inicio_inscricao->i18nFormat('dd/MM/Y')?> - <?=$u->fim_inscricao->i18nFormat('dd/MM/Y')?>
                            <?php } else { ?>
                                Não informado
                            <?php } ?>
                        </td>
                        <td class="d-none d-md-table-cell"><?= $u->deleted ? 'Não' : 'Sim' ?></td>
                        <td class="d-none d-md-table-cell">
                            <?php if ($u->visualizar === 'E') { ?>
                                <span class="badge bg-success">E</span>
                            <?php } elseif ($u->visualizar === 'I') { ?>
                                <span class="badge bg-danger">I</span>
                            <?php } else { ?>
                                <span class="text-muted">-</span>
                            <?php } ?>
                        </td>
                        <td class="actions">
                            <?php if ((int)$u->deleted === 0) { ?>
                                <?=$this->Form->postLink('<i class="fa fa-trash"></i>', ['action' => 'delete', $u->id], ['confirm' => 'Tem certeza que deseja excluir este edital?', 'class' => 'btn btn-sm btn-danger', 'escape' => false])?>
                            <?php } ?>
                            <?php if ((int)$u->deleted === 0) { ?>
                                <?=$this->Html->link('<i class="fa fa-edit"></i>', ['action' => 'gravar', $u->id],['class' => 'btn btn-sm btn-primary', 'escape' => false])?>
                            <?php } ?>
                            <?=$this->Html->link('<i class="fa fa-flag"></i>', ['action' => 'lancarresultado', $u->id],['class' => 'btn btn-sm btn-warning', 'escape' => false])?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div><?= $this->Paginator->counter() ?></div>
            <div class="pagination mb-0">
                <?= $this->Paginator->prev('« Anterior') ?>
                <?= $this->Paginator->numbers() ?>
                <?= $this->Paginator->next('Próximo »') ?>
            </div>
        </div>
    </div>
</div>
