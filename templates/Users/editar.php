<?php $mostrarSiape = !empty($vinculoPesquisador40hId) && (string)($user->vinculo_id ?? '') === (string)$vinculoPesquisador40hId; ?>
<?=$this->Form->create($user, ['autocomplete' => 'off', 'class' => 'user-edit-form'])?>
<script>
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
</script>

<div class="col-12">
    <div class="card shadow-sm border-0">
        <?=$this->Form->hidden('vinculo_pesq_40h_id', ['value' => $vinculoPesquisador40hId ?? '', 'id' => 'vinculo-pesq-40h-id'])?>
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                <h4 class="mb-0">Dados do Usuário</h4>
                <div class="text-muted small">Campos obrigatórios marcados pelo sistema.</div>
            </div>

            <div class="card mb-3 border-0 bg-light">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <h5 class="mb-0">Dados Pessoais</h5>
                        <span class="ms-2 text-muted small">Identificação e endereço</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-2"> 
                                <?=$this->Form->control('cpf', ['label'=>'CPF', 'class' => 'form-control', 'maxlength' => 25, 'disabled' => 'true']) ?>
                        </div>
                        <div class="col-12 col-md-10"> 
                                <?=$this->Form->control('nome', ['class' => 'form-control', 'maxlength' => 150, 'disabled' => 'true']) ?>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-12"> 
                                <?=$this->Form->control('nome_social', ['label'=>'Nome Social <i style="font-weight:100;color:#900">(Se desejar cadastrar um nome social, este será utilizado em todos os documentos desta plataforma)</i>', 'class' => 'form-control', 'size'=>255, 'maxlength'=>255, 'escape'=>false]) ?>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-12 col-md-2"> 
                                <?=$this->Form->control('cep', ['id' => 'txtCEP', 'label' => 'CEP', 'class' => 'form-control', 'maxlength' => 20, 'required', 'value' => ($user->street_id != null ? $user->street->cep : null)]) ?>
                        </div>
                        <div class="col-12 col-md-6"> 
                                <?=$this->Form->control('endereco', ['id' => 'endereco','label' => 'Endereço', 'class' => 'form-control', 'maxlength' => 255]) ?>
                                <?=$this->Form->control('street_id', ['id' => 'street_id','type' => 'hidden', 'class' => 'form-control']) ?>
                        </div>
                        <div class="col-6 col-md-2"> 
                                <?=$this->Form->control('numero', ['label' => 'Número', 'class' => 'form-control', 'size'=>50, 'maxlength'=>50, 'required'=>true]) ?>
                        </div>
                        <div class="col-6 col-md-2"> 
                                <?=$this->Form->control('complemento', ['class' => 'form-control', 'size'=>50, 'maxlength'=>50]) ?>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-12 col-md-3"> 
                                <?=$this->Form->control('data_nascimento', ['label' => 'Data de nascimento', 'class' => 'form-control', 'required'=>true]) ?>
                        </div>
                        <div class="col-12 col-md-3"> 
                                <?php if($this->request->getAttribute('identity')['yoda']){ ?>
                                    <?=$this->Form->control('sexo', ['label'=>'Gênero','options' => $sexo, 'empty' => ' - Selecione - ', 'class' => 'form-control', 'required'=>true]) ?>
                                    <?php } ?>
                                <?php if(!$this->request->getAttribute('identity')['yoda']){ ?>
                                    <?php if($user->sexo != null){ ?>
                                        <?=$this->Form->control('sexo', ['label'=>'Gênero','options' => $sexo, 'empty' => ' - Selecione - ', 'class' => 'form-control', 'disabled'=>true]) ?>
                                    <?php } ?>
                                    <?php if($user->sexo == null){ ?>
                                        <?=$this->Form->control('sexo', ['label'=>'Gênero','options' => $sexo, 'empty' => ' - Selecione - ', 'class' => 'form-control', 'required'=>true]) ?>
                                    <?php } ?>
                                <?php } ?>
                        </div>
                        <div class="col-12 col-md-3"> 
                                <?php if($this->request->getAttribute('identity')['yoda']){ ?>
                                    <?=$this->Form->control('raca', ['label'=>'Raça','options' => $racas, 'empty' => ' - Selecione - ', 'class' => 'form-control', 'required'=>true]) ?>
                                <?php } ?>
                                <?php if(!$this->request->getAttribute('identity')['yoda']){ ?>
                                    <?php if($user->raca != null){ ?>
                                        <?=$this->Form->control('raca', ['label'=>'Raça','options' => $racas, 'empty' => ' - Selecione - ', 'class' => 'form-control', 'disabled'=>true]) ?>
                                    <?php } ?>
                                    <?php if($user->raca == null){ ?>
                                        <?=$this->Form->control('raca', ['label'=>'Raça','options' => $racas, 'empty' => ' - Selecione - ', 'class' => 'form-control', 'required'=>true]) ?>
                                    <?php } ?>
                                <?php } ?>
                        </div>
                        <div class="col-12 col-md-3"> 
                                <?php if($this->request->getAttribute('identity')['yoda']){ ?>
                                    <?=$this->Form->control('deficiencia', ['label'=>'Deficiência','options' => $deficiencia, 'empty' => ' - Selecione - ', 'class' => 'form-control', 'required'=>true]) ?>
                                <?php } ?>
                                <?php if(!$this->request->getAttribute('identity')['yoda']){ ?>
                                    <?php if($user->deficiencia != null){ ?>
                                        <?=$this->Form->control('deficiencia', ['label'=>'Deficiência','options' => $deficiencia, 'empty' => ' - Selecione - ', 'class' => 'form-control', 'disabled' =>true]) ?>
                                    <?php } ?>
                                    <?php if($user->deficiencia == null){ ?>
                                        <?=$this->Form->control('deficiencia', ['label'=>'Deficiência','options' => $deficiencia, 'empty' => ' - Selecione - ', 'class' => 'form-control', 'required'=>true]) ?>
                                    <?php } ?>
                                <?php } ?>
                        </div>
                    </div>
                </div>   
            </div>

            <div class="card mb-3 border-0 bg-light">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <h5 class="mb-0">Dados acadêmicos</h5>
                        <span class="ms-2 text-muted small">Formação e programa</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-3"> 
                                <?=$this->Form->control('lattes', ['class' => 'form-control', 'maxlength'=>255, 'required'=>true]) ?>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-12 col-md-2">
                                <?php if($this->request->getAttribute('identity')['yoda']){ ?>
                                    <?=$this->Form->control('escolaridade_id', ['label'=>'Escolaridade','options' => $escolaridades, 'empty' => ' - Selecione - ', 'class' => 'form-control', 'required'=>true]) ?>
                                <?php } ?>
                                <?php if(!$this->request->getAttribute('identity')['yoda']){ ?>
                                    <?php if($user->escolaridade_id != null){ ?>
                                        <?=$this->Form->control('escolaridade_id', ['label'=>'Escolaridade','options' => $escolaridades, 'empty' => ' - Selecione - ', 'class' => 'form-control', 'disabled' =>true]) ?>
                                    <?php } ?>
                                    <?php if($user->escolaridade_id == null){ ?>
                                        <?=$this->Form->control('escolaridade_id', ['label'=>'Escolaridade','options' => $escolaridades, 'empty' => ' - Selecione - ', 'class' => 'form-control', 'required'=>true]) ?>
                                    <?php } ?>
                                <?php } ?>
                                
                        </div>
                        <div class="col-12 col-md-3">
                                <?php
                                echo $this->Form->control('curso', ['class'=>'form-control', 'maxlength'=>45, 'required'=>true]);
                                ?>
                        </div>
                        <div class="col-12 col-md-2">
                                <?php
                                echo $this->Form->control('ano_conclusao', ['label'=>'Conclusão','min'=>'1900','max'=>(date('Y')+5), 'class'=>'form-control', 'size'=>4, 'maxlength'=>4, 'required'=>true]);
                                ?>
                        </div>
                        <div class="col-12 col-md-1 d-flex align-items-end">
                                <?php
                                echo $this->Form->control('em_curso', ['label'=>'Previsto', 'type'=>'checkbox']);
                                ?>
                        </div>
                        <div class="col-12 col-md-4">
                                <?php
                                echo $this->Form->control('instituicao_curso', ['label'=>'Instituição de Ensino <small class="text-danger">*apenas a sigla</small>','class'=>'form-control', 'value' => $user->instituicao->sigla,'escape' => false, 'maxlength'=>120, 'required'=>true]);
                                ?>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-12 col-md-6" id='divIc' <?=(($user->ic==null)) ? "style='display:none'" : ""?>> 
                                <?=$this->Form->control('ic', ['label'=>'Programa/Edital Social de interesse', 'class' => 'form-control', 'empty'=>'Selecione', 'options'=>['I'=>'IC Manguinhos/Ensp', 'A'=>'IC Mata atlantica', 'M'=>'IC Maré', 'N'=>'Não me enquadro nestes editais']]) ?>
                        </div>
                    </div>
                    <div class="row g-3 mt-1" id='divMang' style='display:none'>              
                        <div class="col-12">
                            <div class="alert alert-warning mb-0">
                                <strong>Atenção:</strong> Esse edital abrange as seguintes localizações:
                                <ul class="mb-0">
                                    <li>Manguinhos e áreas adjacentes</li>
                                    <li>Curicica</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mt-2" id='divAtl' style='display:none'>
                        <div class="col-12">
                            <div class="alert alert-warning mb-0">
                                <strong>Atenção:</strong> O edital IC Mata Atlântica abrange as seguintes localizações:
                                <ul class="mb-0">
                                    <li>Jacarepaguá</li>
                                    <li>Pedra Branca</li>
                                    <li>Vargem Pequena</li> 
                                    <li>Vargem Grande</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 mt-2" id='divMare' style='display:none'>
                        <div class="col-12">
                            <div class="alert alert-warning mb-0">
                                <strong>Atenção:</strong> O edital IC Maré abrange as seguintes localizações:
                                <ul class="mb-0">
                                    <li>Marcílio Dias</li>
                                    <li>Praia de Ramos</li>
                                    <li>Roquete Pinto</li>
                                    <li>Parque União</li>
                                    <li>Rubens Vaz</li>
                                    <li>Nova Holanda</li>
                                    <li>Parque Maré</li>
                                    <li>Nova Maré</li>
                                    <li>Baixa do Sapateiro</li>
                                    <li>Morro do Timbáu</li>
                                    <li>Vila dos Pinheiros</li>
                                    <li>Conjunto bento Ribeiro Dantas</li>
                                    <li>Conjunto Pinheiros</li>
                                    <li>Vila dos Pinheiros</li>
                                    <li>Conjunto Salsa e Merengue</li>
                                    <li>Conjunto Vila do João</li>
                                    <li>Conjunto Esperança</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>         
            </div>   

            <div class="card mb-3 border-0 bg-light">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <h5 class="mb-0">Contatos</h5>
                        <span class="ms-2 text-muted small">E-mails e telefones</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-3"> 
                                <?=$this->Form->control('email', ['type' => 'email', 'class' => 'form-control', 'maxlength' => 100, 'disabled' => 'true']) ?>
                        </div>
                        <div class="col-12 col-md-3"> 
                                <?=$this->Form->control('email_alternativo', ['type' => 'email', 'class' => 'form-control', 'maxlength'=>100]) ?>
                        </div>
                        <div class="col-12 col-md-3"> 
                                <?=$this->Form->control('email_contato', ['type' => 'email', 'class' => 'form-control', 'maxlength'=>100]) ?>
                        </div>
                        <div class="col-6 col-md-2"> 
                                <?=$this->Form->control('telefone', ['class' => 'form-control', 'maxlength'=>25, 'required'=>true]) ?>
                        </div>
                        <div class="col-6 col-md-2"> 
                                <?=$this->Form->control('celular', ['class' => 'form-control', 'maxlength'=>25, 'required'=>true]) ?>
                        </div>
                        <div class="col-12 col-md-2"> 
                                <?=$this->Form->control('whatsapp', ['class' => 'form-control', 'maxlength'=>25, 'required'=>true]) ?>
                        </div>
                    </div>     
                </div>  

            <div class="card mb-3 border-0 bg-light">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <h5 class="mb-0">Documentos</h5>
                        <span class="ms-2 text-muted small">Dados de identificação</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-2"> 
                                <?=$this->Form->control('documento', ['options' => $documentos, 'empty' => ' - Selecione - ', 'class' => 'form-control', 'required'=>true]) ?>
                        </div>
                        <div class="col-12 col-md-2"> 
                                <?=$this->Form->control('documento_numero', ['label' => 'Nº do documento', 'class' => 'form-control', 'maxlength'=>45, 'required'=>true]) ?>
                        </div>
                        <div class="col-12 col-md-2"> 
                                <?=$this->Form->control('documento_emissor', ['label' => 'Órgão emissor', 'class' => 'form-control', 'maxlength'=>45, 'required'=>true]) ?>
                        </div>
                        <div class="col-12 col-md-2"> 
                                <?=$this->Form->control('documento_uf_emissor', [
                                    'label' => 'UF de emissão',
                                    'class' => 'form-control',
                                    'options' => $ufs ?? [],
                                    'empty' => ' - Selecione - ',
                                    'required' => true,
                                ]) ?>
                        </div>
                        <div class="col-12 col-md-2"> 
                                <?=$this->Form->control('documento_emissao', ['label' => 'Data de emissão', 'class' => 'form-control', 'required'=>true]) ?>
                        </div>    
                    </div>     
                </div>  
                
            <div class="card mb-3 border-0 bg-light">
                <div class="card-body" id='divVinculo' <?=(in_array($user->escolaridade_id, [6,7])) ? "style='display:none'" : ""?>>
                    <div class="d-flex align-items-center mb-3">
                        <h5 class="mb-0">Dados Institucionais</h5>
                        <span class="ms-2 text-muted small">Vínculo e unidade</span>
                    </div>
                    <div class="row g-3">
                        <?php if($this->request->getAttribute('identity')['yoda']){ ?>
                            <div class="col-12 col-md-6"> 
                                    <?=$this->Form->control('vinculo_id', ['label' => 'Vínculo com a FIOCRUZ', 'options' => $vinculos, 'empty'=>'Selecione', 'class' => 'form-control']) ?>
                            </div>
                        <?php } ?>
                        <?php if(!$this->request->getAttribute('identity')['yoda'] && ($user->vinculo_id!=null)){ ?>
                            <div class="col-12 col-md-6"> 
                                    <?=$this->Form->control('vinculo_id', ['label' => 'Vínculo com a FIOCRUZ', 'options' => $vinculos, 'empty'=>'Selecione', 'class' => 'form-control', 'disabled'=>'true']) ?>
                            </div>
                        <?php } ?>
                        <?php if(!$this->request->getAttribute('identity')['yoda'] && ($user->vinculo_id==null)){ ?>
                            <div class="col-12 col-md-6"> 
                                    <?=$this->Form->control('vinculo_id', ['label' => 'Vínculo com a FIOCRUZ', 'options' => $vinculos, 'empty'=>'Selecione', 'class' => 'form-control']) ?>
                            </div>
                        <?php } ?>
                        <div class="col-12 col-md-6" id="divSiape" style="<?= $mostrarSiape ? '' : 'display:none;' ?>">
                                <?=$this->Form->control('matricula_siape', ['label' => 'Matrícula SIAPE', 'class' => 'form-control', 'maxlength'=>45]) ?>
                        </div>
                    </div>
                    <div class="row g-3 mt-1" id='divInterno' <?=(($user->vinculo_id==null)) ? "style='display:none'" : ""?>>
                        <div class="col-12 col-md-3"> 
                                <?php if($this->request->getAttribute('identity')['yoda']){ ?>
                                    <?=$this->Form->control('unidade_id', ['label' => 'Unidade', 'class' => 'form-control', 'options' => $unidades, 'empty'=>'Selecione']) ?>
                                <?php } ?>
                                <?php if(!$this->request->getAttribute('identity')['yoda'] && ($user->unidade_id!=null)){ ?>
                                    <?=$this->Form->control('unidade_id', ['label' => 'Unidade', 'class' => 'form-control', 'options' => $unidades, 'empty'=>'Selecione', 'readonly']) ?>
                                <?php } ?>
                                <?php if(!$this->request->getAttribute('identity')['yoda'] && ($user->unidade_id==null)){ ?>
                                    <?=$this->Form->control('unidade_id', ['label' => 'Unidade', 'class' => 'form-control', 'options' => $unidades, 'empty'=>'Selecione']) ?>
                                <?php } ?>
                        </div>
                        <div class="col-12 col-md-3"> 
                                <?=$this->Form->control('departamento', ['label' => 'Departamento', 'class' => 'form-control', 'maxlength'=>45]) ?>
                        </div>
                        <div class="col-12 col-md-3"> 
                                <?=$this->Form->control('laboratorio', ['label' => 'Laboratório', 'class' => 'form-control', 'maxlength'=>45]) ?>
                        </div>
                    </div>     
                </div>  
            </div>              
        </div>
    </div>

<div class="row g-3">
    <div class="col-12 col-md-3">
        <?=$this->Form->button("Gravar", ['class' => 'btn btn-success w-100'])?>
    </div>
    <div class="col-12 col-md-9 d-flex align-items-center text-muted small">
        Revise os dados antes de salvar. Campos ocultos aparecem conforme as escolhas.
    </div>
</div>
<?=$this->Form->end()?>

<script>
function toggleRequired(selector, required) {
    var $el = $(selector);
    if (!$el.length || $el.is(':disabled')) {
        return;
    }
    if (required) {
        $el.attr('required', 'required');
    } else {
        $el.removeAttr('required');
    }
}

function limparCampos(selectors) {
    selectors.forEach(function (selector) {
        var $el = $(selector);
        if (!$el.length || $el.is(':disabled')) {
            return;
        }
        $el.val(null);
    });
}

function atualizarAvisosIc() {
    var ic = String($('#ic').val() || '').toUpperCase();
    $('#divMang').toggle(ic === 'I');
    $('#divAtl').toggle(ic === 'A');
    $('#divMare').toggle(ic === 'M');
}

function atualizarDependenciasEscolaridade() {
    var escolaridade = parseInt($('#escolaridade-id').val(), 10);
    var escolaridadeValida = !isNaN(escolaridade);
    var isGraduacaoPos = escolaridadeValida && escolaridade > 7;
    var isSocial = escolaridadeValida && (escolaridade === 6 || escolaridade === 7);

    $('#divIc').toggle(isSocial);
    toggleRequired('#ic', isSocial);
    if (!isSocial) {
        limparCampos(['#ic']);
        $('#divMang, #divAtl, #divMare').hide();
    } else {
        atualizarAvisosIc();
    }

    $('#divVinculo').toggle(isGraduacaoPos);
    toggleRequired('#vinculo-id', isGraduacaoPos);

    if (!isGraduacaoPos) {
        limparCampos(['#vinculo-id', '#matricula-siape', '#unidade-id', '#departamento', '#laboratorio']);
        $('#divInterno').hide();
        $('#divSiape').hide();
        toggleRequired('#matricula-siape', false);
        toggleRequired('#unidade-id', false);
        toggleRequired('#departamento', false);
        toggleRequired('#laboratorio', false);
        return;
    }

    atualizarDependenciasVinculo();
}

function atualizarDependenciasVinculo() {
    var escolaridade = parseInt($('#escolaridade-id').val(), 10);
    var isGraduacaoPos = !isNaN(escolaridade) && escolaridade > 7;
    var vinculo = String($('#vinculo-id').val() || '');
    var vinculoPesq40h = String($('#vinculo-pesq-40h-id').val() || '');
    var exigeInterno = isGraduacaoPos && vinculo !== '' && vinculo !== '7';
    var exigeSiape = isGraduacaoPos && vinculoPesq40h !== '' && vinculo === vinculoPesq40h;

    $('#divSiape').toggle(exigeSiape);
    toggleRequired('#matricula-siape', exigeSiape);
    if (!exigeSiape) {
        limparCampos(['#matricula-siape']);
    }

    $('#divInterno').toggle(exigeInterno);
    toggleRequired('#unidade-id', exigeInterno);
    toggleRequired('#departamento', exigeInterno);
    toggleRequired('#laboratorio', exigeInterno);

    if (!exigeInterno) {
        limparCampos(['#unidade-id', '#departamento', '#laboratorio']);
    }
}

$(document).on('blur', '#lattes', function () {
    var valor = String($(this).val() || '').trim();
    if (!valor) {
        return;
    }
    var regex = /^(https?:\/\/lattes\.cnpq\.br\/)?[0-9]{16}$/;
    if (!regex.test(valor)) {
        alert('Sua alteração não foi gravada: o endereço do lattes deve seguir o padrão http://lattes.cnpq.br/1432849906286574 ou https://lattes.cnpq.br/1432849906286574');
    }
});

$(document).on('change', '#escolaridade-id', atualizarDependenciasEscolaridade);
$(document).on('change', '#vinculo-id', atualizarDependenciasVinculo);
$(document).on('change', '#ic', atualizarAvisosIc);

$(document).ready(function () {
    atualizarDependenciasEscolaridade();
    atualizarAvisosIc();
    if ($('#txtCEP').val().length == 8) {
        carregaEndereco($('#txtCEP'));
    }
});

$(document).on('keyup', '#txtCEP', function () {
    if ($(this).val().length == 8) {
        carregaEndereco($(this));
        $('#numero').focus();
    }
});

    function carregaEndereco(obj) {
        $.ajax({
            type: "POST",
            url: "<?=$this->Url->build(['controller'=>'enderecos', 'action'=>'buscaEnderecoCompleto'])?>",
            async: false,
            data: {
                txtCEP: obj.val()
            },
            beforeSend: function (xhr) {
                xhr.setRequestHeader("X-CSRF-Token", $('[name="_csrfToken"]').val());
            },
            dataType: "json",
            success: function (json) {
                var end = json[0];
                if (end != null) {
                    $('#endereco').val(end['nome'] + ', ' + end['district']['nome'] + "," + end['district'][
                        'city'
                    ]['nome'] + "," + end['district']['city']['state']['sigla']);
                    $('#street_id').val(end['id']);
                } else {
                    alert('Endereço não encontrado, favor verificar o CEP');
                }
            }
        });
    }


</script>
