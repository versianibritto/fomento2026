<div class="mt-n2">
    <?php
    if($erro) {
    ?>
    <div class="p-4">
        <div class="card">
            <div class="card-body">
                <h4 class="text-danger">Não foi possível Vincular os avaliadores:</h4>
                <ul>
                    <?=$erro?>
                </ul>
            </div>
        </div>
        <div class="row">
            <div class="col-12 text-center">
            <?=$this->Html->link(' Voltar ', ['action' => 'avaliarnovas', 'N'], ['class' => 'btn btn-danger mt-3'])?>
            </div>
        </div>
    </div>
    <?php
    } else {?>
        <div class="panel-heading">
            <h4>Vincular Avaliadores da área de <?=$projeto->area->nome;?></h4>
        </div>
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <span class="d-block"><strong>Data da inscrição: </strong><span><?=$inscricao->created == '' ? '<i class="text-danger">Não localizada</i>' : $inscricao->created->i18nFormat('dd/MM/yyyy')?></span></span>
                        <span class="d-block"><strong>Edital : </strong><span><?=$inscricao->editai->nome?></span></span>
                        <span class="d-block"><strong>Situação : </strong><span><?=($inscricao->vigente == 1 ? '<span class="badge bg-success me-2"> Vigente </span>' : ($inscricao->deleted == 1 ? '<span class="badge bg-danger me-2"> Excluído </span>' : '')) . $situacao[$inscricao->situacao][0]?></span></span>
                        <span class="d-block"><strong>Projeto Orientador: </strong><span><?=$inscricao->projeto->titulo?></span></span>
                        <span class="d-block"><strong>Orientador: </strong><span><?=$inscricao->orientadore->nome?></span></span>
                    </div>
                </div>
            </div>
        
            <div class="col-md-12">
                <h4>Bolsistas vinculados</h4>
                <?php
                if($inscricao->usuario != "") {
                ?>
                    <div class="card">
                        <div class="card-body">
                            <strong><?=$inscricao->usuario->nome?></strong>
                            <p><b>Curso:</b> <?=$inscricao->usuario->curso == ''? 'Não Informado':
                                                $inscricao->usuario->curso?></p>
                            <p class="mb-0">Inicio da vigência: <?=($inscricao->data_inicio==null?'Bolsa não implantada':date('d/m/Y', strtotime($inscricao->data_inicio)))?></p>
                            <p class="mb-0">Fim da vigência: <?=($inscricao->data_fim==null?($inscricao->data_inicio==null?'Bolsa não Implantada':'* Atualmente Vigente'):date('d/m/Y', strtotime($inscricao->data_fim)))?></p>

                            
                        </div>
                        
                    </div>
                <?php
                } elseif($bolsista) {
                ?>
                <div class="col-12">
                    <div class="bg-warning px-5 p-3 rounded mb-2 text-center" id="aviso-bolsista">
                        Você precisa vincular um bolsista para finalizar a inscrição até <?=date("d/m/Y", strtotime($inscricao->editai->fim_inscricao))?>
                    </div>
                    <div id="form-bolsista" style="display:none;">
                        <?=$this->Form->create(null, ['url' => ['action' => 'gravaBolsista']])?>
                        <?=$this->Form->control("inscricao_id", ['type' => 'hidden', 'value' => $inscricao->id]);?>
                        <div class="row mb-2">
                            <div class="col-md-3">
                                <?=$this->Form->control('cpf',['label'=>'CPF do bolsista', 'class'=>'form-control']);?>
                                <?=$this->Form->control('bolsista', ['type'=>'hidden']);?>
                            </div>
                            <div class="col-md-6">
                                <div class="input text">
                                    <label for="cpf">Nome do bolsista</label>
                                    <input type="text" name="nome" class="form-control" id="nome">
                                    <small class="text-danger" id="errobolsista" style="display: none;">Error message</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="input text">
                                    <label for="cpf">E-mail do bolsista</label>
                                    <input type="text" name="email" class="form-control" id="email">
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <button type="submit" class="btn btn-primary"> Gravar </button>
                            <button type="button" class="btn btn-danger" id="cancelarBolsista"> Cancelar </button>
                        </div>
                        <?=$this->Form->end()?>
                    </div>
                </div>
                <button type="button" class="btn btn-success mb-3" id="togleFormBolsista">
                    <i class="fa fa-user-plus"></i> Adicionar um bolsista
                </button>
                <?php
                }
                ?>
            </div>
            <div class="col-md-12">
            <h4>Coorientador</h4>

                <div class="card">
                    <div class="card-body">
                        <?php
                        if($inscricao->coorientador!=null) {
                        ?>                        
                            <strong><?=$inscricao->coorientadore->nome?></strong>
                            <p class="mb-0"><b>Universidade:</b> <?=$inscricao->coorientadore->instituicao_curso==''?'Não Informado':$inscricao->coorientadore->instituicao->sigla?></p>
                            <p><b>Curso:</b> <?=$inscricao->coorientadore->curso == ''? 'Não Informado':
                                                $inscricao->coorientadore->curso?></p>
                            <p class="mb-0">Resposta do Coorientador:
                                <?=($inscricao->resposta_coorientador==null?'***':(
                                    $inscricao->resposta_coorientador=='P'?'Pendente':(
                                        $inscricao->resposta_coorientador=='A'? 'Confirmado em '.date('d/m/Y H:i', strtotime($inscricao->data_resposta_coorientador)):(
                                            'Recusado em '.date('d/m/Y H:i', strtotime($inscricao->data_resposta_coorientador)).' - '.$inscricao->justificativa_recusa_coorientador
                                        )
                                    )
                                )
                                )?></p>
                                
                        <?php } ?>
                        <?php if($inscricao->coorientadore==null){?>
                            <div class="col-12">
                                <div class="bg-info px-5 p-3 rounded mb-2 text-center" id="aviso-bolsista">
                                    Nenhum coorientador cadastrado 
                                </div>

                                <?php
                                if ($inscricao->orientadore->vinculo_id != 1) {
                                ?>

                                <div class="bg-warning px-5 p-3 rounded mb-2 text-center" id="aviso-bolsista">
                                    É mandatório o cadastrar um coorientador para finalizar a inscrição até <?=date("d/m/Y", strtotime($inscricao->editai->p_nova))?>
                                </div>
                                <?php
                                }
                                ?>
                                
                            </div>
                        <?php }?>
                        <br><br>
                        <div id="form-coorientador" style="display:none;">
                            <?=$this->Form->create(null, ['url' => ['action' => 'verificacoorientador', $inscricao->id]])?>
                            <?=$this->Form->control("inscricao_id", ['type' => 'hidden', 'value' => $inscricao->id]);?>
                            <div class="row mb-2">
                                <div class="col-md-3">
                                    <?=$this->Form->control('cpf_corientador', ['label'=>'CPF do Coorientador (apenas números)', 'class'=>'form-control', 'SIZE'=>11, 'MAXLENGTH'=>11]);?>
                                    <?=$this->Form->control('coorientador', ['type'=>'hidden']);?>
                                </div>
                                <div class="col-md-6">
                                    <div class="input text">
                                        <label for="nome2">Nome do Coorientador</label>
                                        <input type="text" name="nome2" class="form-control" id="nome2">
                                        <small class="text-danger" id="errocoorientador" style="display: none;">Error message</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input text">
                                        <label for="email2">E-mail do bolsista</label>
                                        <input type="text" name="email2" class="form-control" id="email2">
                                    </div>
                                </div>
                            </div>  
                            <div class="col-12 mb-3">
                                <button type="submit" class="btn btn-primary"> Gravar </button>
                                <button type="button" class="btn btn-danger" id="cancelarCoorientador"> Cancelar </button>
                            </div>
                            <?=$this->Form->end()?>
                        </div>
                        <?php if(($inscricao->coorientador==null) && ($this->request->getAttribute('identity')['id']==$inscricao->orientador)){ ?>
                            <button type="button" class="btn btn-success mb-3" id="togleFormCoorientador">
                                <i class="fa fa-user-plus"></i> Adicionar um coorientador
                            </button>
                        <?php }?>
                        <?php if(($inscricao->coorientador!=null) && ($this->request->getAttribute('identity')['id']==$inscricao->orientador)){ ?>
                            <button type="button" class="btn btn-success mb-3" id="togleFormCoorientador">
                                <i class="fa fa-user-plus"></i> Alterar o coorientador
                            </button>
                        <?php }?>
                        
                        
                    </div>
                </div>
            </div>
        </div>




        <div class="panel-body">
            <?=$this->Form->create();?>
            <div class="card">
                <div class="card-body">
                    <div class="titulo-legenda">
                        <h5>Viculação de Avaliadores</h5>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                                echo $this->Form->control('avaliador_1',['label'=>'Avaliador 1 da Banca',  'required' => true, 'class' => 'form-control', 'options'=>$disponiveis,'empty'=>'- Escolha -']);
                            ?>
                        </div>
                    </div>
                    <?=$this->Form->control('projeto_id', ['type'=>'hidden', 'value'=>$projeto->id]);?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?=$this->Form->button(' Vincular ', ['class'=>'btn btn-success']);?>
                </div>
            </div>
            <?=$this->Form->end();?>
        </div>
    <?php  }?>
</div>