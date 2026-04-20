<div class="p-3">

    <h4>Avaliadores Raic</h4>
   
    <?=$this->Html->link('Clique para adicionar um Novo Avaliador', ['controller' => 'Avaliadors', 'action' => 'add'], ['class' => 'btn btn-info btn-xs', 'escape' => false]);?>

    <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Unidade de avaliação</th>
                        <th>Ano</th>

                        <th>tipo</th>
                        <th>Grande Área CNPq</th>
                        <th>Área CNPq</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($avaliadores as $a): 
                        $nome = explode(" ", $a->avaliador->usuario->nome);
                    ?>
                    <tr>
                        
                        <td>
                            <?=$this->Html->link('<i class="fas fa-eye"></i>', ['controller' => 'Usuarios', 'action' => 'ver', $a->avaliador->usuario_id], ['class' => 'btn btn-info btn-xs', 'escape' => false]);?>
                            <?=($nome[0] . ' ' . end($nome)) ?>
                        </td>
                        <td><?=(isset($a->avaliador->unidade)?$a->avaliador->unidade->sigla:'Não informada')?></td>
                        <td><?=($a->avaliador->ano_convite) ?></td>
                        <td><?=($a->avaliador->tipo_avaliador)?></td>
                        <td><?=(isset($a->avaliador->grandes_area->nome)?$a->avaliador->grandes_area->nome:(($this->request->getAttribute('identity')['yoda'] || ($this->request->getAttribute('identity')['jedi']))?$this->Html->link('Adicionar',['action'=>'areas', $a->avaliador->id],['class'=>'_colorbox']):'Não informado')) ?></td>
                        <td>
                            <?php if($a->avaliador->area_id!=null){?>
                                <?=($a->avaliador->area->nome)?>
                            <?php }?>

                            <?php if($a->avaliador->area_id==null){?>
                                <?php if($a->avaliador->grandes_area_id==null){?>
                                    <?='Informe a grande área'?>
                                <?php }?>

                                <?php if($a->avaliador->grandes_area_id!=null){?>
                                    <?php if($this->request->getAttribute('identity')['jedi'] || $this->request->getAttribute('identity')['yoda']){?>
                                        <?= $this->Html->link('Adicionar',['action'=>'cnpq', $a->avaliador->id],['class'=>'_colorbox']) ?>
                                    <?php }?>
                                    <?php if(!$this->request->getAttribute('identity')['jedi'] & !$this->request->getAttribute('identity')['yoda']){?>
                                        <?= 'Não informado' ?>
                                    <?php }?>
                                    
                                <?php }?>
                                
                            <?php }?>
                            
                            
                            
                            
                            </td>
                        <td>
                           <?php
                            if($a->avaliador->ano_convite==date('Y') && $a->avaliador->ano_aceite!=date('Y'))
                            {
                                print $this->Form->postLink('<i class="fa fa-hourglass-half fa-spin"></i>&nbsp;&nbsp;&nbsp;Confirmar&nbsp;&nbsp;', ['action' => 'confirmar', $a->avaliador->id], ['class'=>'btn btn-warning btn-xs','escape'=>false]);
                            }elseif($a->avaliador->ano_aceite==date('Y') && $a->avaliador->ano_convite==date('Y') ){
                                print '<span class="btn btn-success btn-xs active">Confirmado</span>';
                            }elseif($a->avaliador->ano_aceite!=date('Y') && $a->avaliador->ano_convite!=date('Y') ){
                                print $this->Form->postLink('Adicionar à Lista', ['action' => 'convidar', $a->avaliador->id], ['class'=>'btn btn-danger btn-xs','escape'=>false]);

                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                    endforeach;
                    ?>
                </tbody>
            </table>

    

    
</div>
