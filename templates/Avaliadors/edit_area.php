<?= $this->Form->create($avaliador); ?>

<div class="col-md-12">
    <h3>Informe a área CNPq</h3>
    <?= $this->Form->control('id', ['type' => 'hidden']) ?>

    <div class="row mb-2">
        <div class="col-md-4">
            <?= $this->Form->control('grandes_area_id', [
                'id' => 'grandes-area-id', 
                'label' => 'Grande área (CNPQ)',
                'options' => $areasF, 
                'empty' => ' - Selecione - ',
                'class' => 'form-control'
            ]); ?>
        </div>

        <?php if($avaliador->editai->tipo!='J') { ?>
            <div class="col-md-4">
                <?= $this->Form->control('area_id', [
                    'id' => 'area-id',
                    'label' => 'Área CNPQ',
                    'options' => [], 
                    'empty' => ' - Selecione uma área - ',
                    'class' => 'form-control'
                ]); ?>
            </div>
        <?php } ?>
    </div>
</div>
<?=$this->Form->button(' Gravar ',['class'=>'btn btn-primary']);?>

<?= $this->Form->end(); ?>
<script>
$(document).on('change', '#grandes-area-id', function () {
    const grandeAreaId = $(this).val();

    if (!grandeAreaId) {
        $('#area-id').html("<option value=''> - Selecione uma área - </option>");
        return;
    }

    $.ajax({
        type: "POST",
        url: "<?= $this->Url->build(['controller' => 'Avaliadors', 'action' => 'buscaPorArea']) ?>",
        data: { id: grandeAreaId },
        dataType: "json",
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-CSRF-Token', <?= json_encode($this->request->getAttribute('csrfToken')) ?>);
            $('#area-id').html("<option>Carregando...</option>");
        },
        success: function(json) {
            let html = "<option value=''> - Selecione uma área - </option>";
            $.each(json, function(i, item) {
                html += `<option value='${item.id}'>${item.nome}</option>`;
            });
            $('#area-id').html(html);
        },
        error: function() {
            $('#area-id').html("<option value=''>Erro ao carregar áreas</option>");
        }
    });
});
</script>
