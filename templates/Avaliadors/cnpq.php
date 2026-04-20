<?=$this->Form->create($avaliador);?>
<div class="col-md-12">
    <h3>Informe a área Cnpq</h3>
    <?=$this->Form->control('id',['type'=>'hidden'])?>
    <?=$this->Form->control('area_id',['label'=>'Área (CPNq)','class'=>'form-control', 'empty'=>' - Selecione - ','options'=>$areas]);?>
    <div class="clearfix"></div>
    <?=$this->Form->button(' Gravar ',['class'=>'btn btn-primary']);?>
</div>
<?=$this->Form->end();?>
