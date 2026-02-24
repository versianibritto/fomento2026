<?php
$titulo = $isEdicao ? 'Editar Edital' : 'Cadastrar Novo Edital';
$textoBotao = $isEdicao ? 'Salvar edital' : 'Cadastrar edital';
$hasUnidades = !empty($unidadesPermitidas);
$hasVinculos = !empty($vinculosPermitidos);
$hasEscolaridades = !empty($escolaridadesPermitidas);
?>
<section class="mt-n3">
    <div class="card card-primary card-outline">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h2 class="mb-0"><?= $titulo ?></h2>
                <?= $this->Html->link('Voltar', $this->request->referer(), ['class' => 'btn btn-outline-secondary']) ?>
            </div>
            <hr>
            <?=$this->Form->create($edital, ['type' => 'file', 'class' => 'row g-3'])?>
                <div class="col-12">
                    <?=$this->Form->control('nome', ['label'=>'Nome do Edital', 'class' => 'form-control', 'size'=>45, 'maxlength'=>45, 'required' ]) ?>
                </div>
                <div class="col-md-6">
                    <?=$this->Form->control('arquivo', [
                        'id' => 'arquivo',
                        'type' => 'file',
                        'label' => 'Arquivo do Edital',
                        'class' => 'form-control',
                        'required' => empty($edital->arquivo),
                    ])?>
                </div>
                <div class="col-md-6">
                    <?php if ($isEdicao) { ?>
                        <div class="mt-4 p-2 border rounded <?= empty($edital->arquivo) ? 'border-danger bg-danger bg-opacity-10' : 'border-primary bg-primary bg-opacity-10' ?>">
                            <?php if (!empty($edital->arquivo)) { ?>
                                <a class="btn btn-primary btn-sm" href="/uploads/editais/<?= h($edital->arquivo) ?>" target="_blank" title="Baixar arquivo">
                                    <i class="fa fa-download"></i>
                                </a>
                                <span class="text-muted ms-2"><?= h($edital->arquivo) ?></span>
                            <?php } else { ?>
                                <strong class="text-danger">Nenhum arquivo anexado.</strong>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="col-md-6">
                    <?=$this->Form->control('modelo_cons_bols', [
                        'id' => 'modelo-cons-bols',
                        'type' => 'file',
                        'label' => 'Modelo consentimento - Bolsista',
                        'class' => 'form-control',
                    ])?>
                </div>
                <div class="col-md-6">
                    <?php if ($isEdicao) { ?>
                        <div class="mt-4 p-2 border rounded <?= empty($edital->modelo_cons_bols) ? 'border-danger bg-danger bg-opacity-10' : 'border-primary bg-primary bg-opacity-10' ?>">
                            <?php if (!empty($edital->modelo_cons_bols)) { ?>
                                <a class="btn btn-primary btn-sm" href="/uploads/editais/<?= h($edital->modelo_cons_bols) ?>" target="_blank" title="Baixar arquivo">
                                    <i class="fa fa-download"></i>
                                </a>
                                <span class="text-muted ms-2"><?= h($edital->modelo_cons_bols) ?></span>
                            <?php } else { ?>
                                <strong class="text-danger">Nenhum arquivo anexado.</strong>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="col-md-6">
                    <?=$this->Form->control('modelo_cons_coor', [
                        'id' => 'modelo-cons-coor',
                        'type' => 'file',
                        'label' => 'Modelo consentimento - Coorientador',
                        'class' => 'form-control',
                    ])?>
                </div>
                <div class="col-md-6">
                    <?php if ($isEdicao) { ?>
                        <div class="mt-4 p-2 border rounded <?= empty($edital->modelo_cons_coor) ? 'border-danger bg-danger bg-opacity-10' : 'border-primary bg-primary bg-opacity-10' ?>">
                            <?php if (!empty($edital->modelo_cons_coor)) { ?>
                                <a class="btn btn-primary btn-sm" href="/uploads/editais/<?= h($edital->modelo_cons_coor) ?>" target="_blank" title="Baixar arquivo">
                                    <i class="fa fa-download"></i>
                                </a>
                                <span class="text-muted ms-2"><?= h($edital->modelo_cons_coor) ?></span>
                            <?php } else { ?>
                                <strong class="text-danger">Nenhum arquivo anexado.</strong>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="col-md-6">
                    <?=$this->Form->control('modelo_relat_bols', [
                        'id' => 'modelo-relat-bols',
                        'type' => 'file',
                        'label' => 'Modelo relatório - Bolsista',
                        'class' => 'form-control',
                    ])?>
                </div>
                <div class="col-md-6">
                    <?php if ($isEdicao) { ?>
                        <div class="mt-4 p-2 border rounded <?= empty($edital->modelo_relat_bols) ? 'border-danger bg-danger bg-opacity-10' : 'border-primary bg-primary bg-opacity-10' ?>">
                            <?php if (!empty($edital->modelo_relat_bols)) { ?>
                                <a class="btn btn-primary btn-sm" href="/uploads/editais/<?= h($edital->modelo_relat_bols) ?>" target="_blank" title="Baixar arquivo">
                                    <i class="fa fa-download"></i>
                                </a>
                                <span class="text-muted ms-2"><?= h($edital->modelo_relat_bols) ?></span>
                            <?php } else { ?>
                                <strong class="text-danger">Nenhum arquivo anexado.</strong>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>

                <div class="col-md-3">
                    <?=$this->Form->control('programa_id', ['label'=>'Programa', 'class' => 'form-select', 'options'=>$programas, 'empty'=>'Selecione', 'required' ]) ?>
                </div>
                <div class="col-md-3">
                    <?=$this->Form->control('origem', ['label'=>'Forma de Ingresso', 'class' => 'form-select', 'options'=>$origem, 'empty'=>'Selecione', 'required' ]) ?>
                </div>
                <div class="col-md-3">
                    <?=$this->Form->control('visualizar', ['label'=>'Visualização (aparece na Index)', 'class' => 'form-select', 'options'=>['I'=>'Interno', 'E'=>'Externo'], 'empty'=>'Selecione', 'required' ]) ?>
                </div>
                <div class="col-md-3">
                    <?=$this->Form->control('evento', ['label'=>'Id do evento vinculado', 'class' => 'form-control' ]) ?>
                </div>

                <div class="col-12 mt-3">
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <h4 class="mb-2">Prazos</h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <?=$this->Form->control('divulgacao', ['label'=>'Divulgação', 'class' => 'form-control' ]) ?>
                                </div>
                                <div class="col-md-6">
                                    <?=$this->Form->control('resultado', ['label'=>'Resultado', 'class' => 'form-control' ]) ?>
                                </div>

                                <div class="col-md-6">
                                    <?=$this->Form->control('inicio_inscricao', ['label'=>'Início das Inscrições', 'class' => 'form-control', 'required' ]) ?>
                                </div>
                                <div class="col-md-6">
                                    <?=$this->Form->control('fim_inscricao', ['label'=>'Fim das Inscrições', 'class' => 'form-control', 'required' ]) ?>
                                </div>

                                <div class="col-md-6">
                                    <?=$this->Form->control('inicio_avaliar', ['label'=>'Início da Avaliação', 'class'=>'form-control','required' ]) ?>
                                </div>
                                <div class="col-md-6">
                                    <?=$this->Form->control('fim_avaliar', ['label'=>'Fim da Avaliação', 'class'=>'form-control', 'required' ]) ?>
                                </div>

                                <div class="col-md-6">
                                    <?=$this->Form->control('inicio_recurso', ['label'=>'Início do Recurso', 'class' => 'form-control' ]) ?>
                                </div>
                                <div class="col-md-6">
                                    <?=$this->Form->control('fim_recurso', ['label'=>'Fim do Recurso', 'class' => 'form-control' ]) ?>
                                </div>

                                <div class="col-md-6">
                                    <?=$this->Form->control('inicio_vigencia', ['label'=>'Início da Vigência', 'class'=>'form-control','required' ]) ?>
                                </div>
                                <div class="col-md-6">
                                    <?=$this->Form->control('fim_vigencia', ['label'=>'Fim da Vigência', 'class'=>'form-control', 'required' ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 mt-3">
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <h4 class="mb-2">Configurações</h4>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <?=$this->Form->control('limitaAnoDoutorado', ['label'=>'Limita ano de doutorado?', 'min'=>'1900','max'=>(date('Y')+5),'class' => 'form-control' ]) ?>
                                </div>
                                <div class="col-md-8">
                                    <?=$this->Form->control('link', ['label'=>'LINK', 'class' => 'form-control', 'maxlength' => 45, 'required' ]) ?>
                                </div>
                                <div class="col-md-4">
                                    <?=$this->Form->control('controller', [
                                        'label' => 'Controller',
                                        'class' => 'form-control',
                                        'maxlength' => 45,
                                    ]) ?>
                                </div>
                                <div class="col-md-12">
                                    <?=$this->Form->control('cpf_permitidos', [
                                        'label' => 'CPFs permitidos (curadores)',
                                        'type' => 'textarea',
                                        'class' => 'form-control',
                                        'rows' => 3,
                                        'placeholder' => 'Informe os CPFs separados por virgula ou linha',
                                    ]) ?>
                                </div>
                                

                                <div class="col-md-4">
                                    <div class="form-check form-switch mt-2">
                                        <?=$this->Form->control('unidade', ['type' => 'checkbox', 'label' => 'Restringir Unidade do orientador?', 'class' => 'form-check-input', 'checked' => $hasUnidades])?>
                                    </div>
                                </div>
                                <div class="col-md-8" id="listaunidades" style="<?= $hasUnidades ? '' : 'display:none;' ?>">
                                    <?=$this->Form->control('unidades_permitidas_ids', [
                                        'label' => 'Unidades permitidas',
                                        'type' => 'select',
                                        'multiple' => 'checkbox',
                                        'options' => $unidades,
                                        'value' => $unidadesPermitidas,
                                    ])?>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-check form-switch mt-2">
                                        <?=$this->Form->control('vinculo', ['type' => 'checkbox', 'label' => 'Restringir Vínculo do orientador?', 'class' => 'form-check-input', 'checked' => $hasVinculos])?>
                                    </div>
                                </div>
                                <div class="col-md-8" id="listavinculos" style="<?= $hasVinculos ? '' : 'display:none;' ?>">
                                    <?=$this->Form->control('vinculos_permitidos_ids', [
                                        'label' => 'Vínculos permitidos',
                                        'type' => 'select',
                                        'multiple' => 'checkbox',
                                        'options' => $vinculos,
                                        'value' => $vinculosPermitidos,
                                    ])?>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-check form-switch mt-2">
                                        <?=$this->Form->control('escolaridade', ['type' => 'checkbox', 'label' => 'Restringir Escolaridade do bolsista?', 'class' => 'form-check-input', 'checked' => $hasEscolaridades])?>
                                    </div>
                                </div>
                                <div class="col-md-8" id="listaescolaridade" style="<?= $hasEscolaridades ? '' : 'display:none;' ?>">
                                    <?=$this->Form->control('escolaridades_permitidas_ids', [
                                        'label' => 'Escolaridades permitidas',
                                        'type' => 'select',
                                        'multiple' => 'checkbox',
                                        'options' => $escolaridades,
                                        'value' => $escolaridadesPermitidas,
                                    ])?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <?= $this->Form->button($textoBotao, ['class'=>'btn btn-success']) ?>
                </div>
            <?=$this->Form->end()?>
        </div>
    </div>
</section>

<script>
	

    $('#unidade').change(function(){
        if($('#unidade').is(':checked')) {
            $('#listaunidades').show();
        } else {
            $('#listaunidades').hide();
            $('#listaunidades input[type="checkbox"]').prop('checked', false);
        }
    });

    $('#vinculo').change(function(){
        if($('#vinculo').is(':checked')) {
            $('#listavinculos').show();
        } else {
            $('#listavinculos').hide();
            $('#listavinculos input[type="checkbox"]').prop('checked', false);
        }
    });

    $('#escolaridade').change(function(){
        if($('#escolaridade').is(':checked')) {
            $('#listaescolaridade').show();
        } else {
            $('#listaescolaridade').hide();
            $('#listaescolaridade input[type="checkbox"]').prop('checked', false);
        }
    });

      

    function validarArquivo2Mb(inputId) {
        var input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener("change", function(e) {
            if (!input.files || !input.files[0]) {
                return;
            }
            var size = input.files[0].size;
            if(size > 2097152) {
                alert('Arquivo maior que 2Mb.  Selecione outro pois este tamanho nao é permitido.');
                input.value = "";
            }
            e.preventDefault();
        });
    }

    validarArquivo2Mb('arquivo');
    validarArquivo2Mb('modelo-cons-bols');
    validarArquivo2Mb('modelo-cons-coor');
    validarArquivo2Mb('modelo-relat-bols');
    
        
</script>
