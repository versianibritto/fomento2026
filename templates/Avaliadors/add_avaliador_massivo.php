
<div class="row">

    <h3 class="text-default" data-bs-toggle="tooltip" title="Cadastro massivo de avaliador por edital" class="d-inline-block">
    Habilitar Avaliadores <i class="fa fa-info-circle"></i>
    </h3>
   
    <div class="bg px-5 p-3 rounded mb-2 text-center" id="aviso-bolsista" style='background:#ccd1d1'>
        <h5 style='color:#900'>      
            O perfil <strong>Coordenação da Unidade</strong> tem acesso aos Avaliadores RAIC;
            <br>
            O perfil <strong>Coordenação de Fomento</strong> tem acesso aos avaliadores de bolsa nova dos programas ICS e aos editais de renovação e nova do programa PDJ;

        </h5>
    </div>     
    <br><br>
    
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <?=$this->Form->create()?>
                
                    <?php if ($this->request->getAttribute('identity')['jedi']!=null){?>
                        <!-- Campos para as unidades -->
                        <div class="mt-3">
                            <?=$this->Form->control('unidade_id', [
                                'label' => 'Selecione a unidade',
                                'options' => $unidades,
                                'empty' => 'Selecione',

                                'class' => 'form-control',
                                'required' => true
                            ])?>
                        </div>
                    <?php }?>

                    

                    <!-- Campos para os editais -->
                    <div class="mt-3">
                        <label for="editais">Selecione os Editais</label>
                        <div class="form-group">
                            <?php foreach ($editais as $id => $nome): ?>
                                <div class="form-check" style="margin-bottom: 8px;">
                                    <?=$this->Form->checkbox("editais[$id]", [
                                        'value' => $id,
                                        'id' => 'editais-' . $id,
                                        'style' => 'width: 20px; height: 20px; border: 2px solid #333; margin-right: 8px;',
                                        'class' => 'form-check-input'
                                    ])?>
                                    <label for="editais-<?=$id?>" class="form-check-label" style="font-size: 1.1em;">
                                        <?=$nome?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>



                    <!-- Campo para inserir CPFs -->
                    <div class="mt-3">
                        <?=$this->Form->control('cpfs', [
                            'label' => 'Digite os CPFs (separados por vírgula)',
                            'type' => 'text',
                            'class' => 'form-control',
                            'required' => true
                        ])?>
                    </div>

                    <!-- Botões do formulário -->
                    <div class="col-12 mt-2">
                        <button type="submit" class="btn btn-primary mb-2 px-3">
                            <i class="fa fa-search me-3"></i> Habilitar
                        </button>
                        <?= $this->Html->link('Limpar filtros', ['Controller' => 'Listas', 'action' => 'limpar', 'buscaVigentes', 'vigentes'], ['class' => 'btn btn-danger mb-2 px-3 pull-right']) ?>
                    </div>

                <?=$this->Form->end()?>
            </div>
        </div>
    </div>
</div>


