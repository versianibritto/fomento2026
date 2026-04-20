<?=$this->Form->create($avaliador);?>
<div class="col-md-12">
    <h3>Informe a grande área</h3>
    <?=$this->Form->control('id',['type'=>'hidden'])?>
    <?=$this->Form->control('grandes_area_id',['label'=>'Área','empty'=>'- Selecione -','options'=>$areas,'class'=>'form-control','required'=>true])?>
    <div class="clearfix"></div>
    <?=$this->Form->button(' Gravar ',['class'=>'btn btn-primary']);?>
</div>
<?=$this->Form->end();?>
