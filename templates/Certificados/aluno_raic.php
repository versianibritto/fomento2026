<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Certificado</title>

    <style>

    </style>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; font-size: 14px;">
    <div style="position: absolute; z-index: 1;">
        <img src="/img/certificado_nome.svg" alt="">
    </div>

    <div style="position: absolute;
    z-index: 2;
    width: 400px;
    margin-top: 231px;
    margin-left: 356px;">
           <p style="font-size: 20px; font-weight: bold; margin:2px 0;"><?=$user->nome;?></p>
           <p style="font-size: 12px; margin:2px 0;">CPF: <?=$user->cpf;?></p>
    </div>



     <div style="position: absolute;
     z-index: 2;
     width: 400px;
     margin-top: 290px;
     margin-left: 356px;">
            <strong><?=$texto[0];?></strong>
     </div>

    <div style="position: absolute;
    z-index: 2;
    width: 400px;
    margin-top: 340px;
    margin-left: 356px; text-align: justify">
        <?=$texto[1];?>
        <p><br /></p>
        <?php
        if (isset($dia[1])) {
        ?>
        No período de <?=$dia[0].' a '.$dia[1];?>
        <?php
        } else {
        ?>
        Em <?=$dia[0];?>
        <?php
        }
        ?>
        <strong style="display:block">Totalizando:</strong>
        <?=$horas;?>h de atividades extra-acadêmicas
    </div>

    <div style="position: absolute;
    z-index: 2;
    width: 400px;
    margin-top: 519px;
    margin-left: 364px;
    text-align: right;
    font-size:10px";>
            Valide este certificado em http://pibic.fiocruz.br/certificados/validar/<?=$codigo?>
     </div>
</body>
</html>
