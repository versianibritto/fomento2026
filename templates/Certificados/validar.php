<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="description" content="Neon Admin Panel" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="author" content="Everton CB Junior" />
	<title>Bolsas VPPCB</title>
	<?= $this->Html->css("bootstrap.min")?>
	<?= $this->Html->css("font-awesome.min")?>
	<?= $this->Html->css("bolsas")?>
	<?= $this->Html->script("jquery")?>
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <main class="login">
        <div class="col-md-offset-3 col-sm-offset-3 col-md-12 col-sm-6 col-xs-12">
           <div class="pagebreak"></div>
            <div style="padding:0px 70px 50px; text-align:justity !important;font-size:14px">
                <div>
                    <?=$this->Html->image('logo_b.png',['style'=>'width:25%'])?>
                </div>
                <div>
                    <p style="text-align:center">                
                        <h2>Sistema de Gestão de Bolsas</h2>
                    </p>
                </div>
                <div>
                <div class="col-xs-12">
                    <p>
                    <?php
                    if($verificado==null){
                    ?>
                    <div class="alert alert-danger">
                        O código do certificado informado não se encontra na nossa base de dados, deseja informar um outro código?
                    </div>
                    <?php
                    }else{
                    ?>
                        Atestamos que o certificado apresentado, confirma que <strong style="font-size:18px"><?=$user->nome;?></strong>, portador
                    do CPF <?=$user->cpf;?> <?=$texto[0]." ".$texto[1];?> sob a coordenação da Vice Presidência de Pesquisa e Coleções Biológicas da
                    Fundação Oswaldo Cruz <?=(isset($dia[1])?"no período de ".date('d/m/Y', strtotime($dia[0]))." a ".date('d/m/Y', strtotime($dia[1])):"no dia ".(($dia[0])));?>
                    outorgando <?=$horas;?> horas de atividade acadêmico-científicas.
                    <?php
                    }
                    ?>
                    </p>
                </div>

                </div>
                <p></p>
                
            </div>
        </div>
        <div class="col-md-4 col-md-offset-4">
            <fieldset class="form-certificado">
                <?=$this->Form->create(null,['url'=>['controller'=>'certificados','action'=>'validar']]);?>
                    <h4>Deseja validar outro certificado ou declaração?</h4>
                    <input type="text" name="codigo" required="true" class="form-control" placeholder="<?=$codigo?$codigo:'Digite o código do certificado';?>">
                    <div class="clearfix"></div>
                    <button class="btn btn-primary"> Validar </button>
                <?=$this->Form->end();?>
            </fieldset>
        </div>
    </main>
	<?=$this->Html->script("bootstrap.min");?>
	<?=$this->Html->script("menu.js");?>
</body>
</html>
