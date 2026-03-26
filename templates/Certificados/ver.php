<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <?php $nomeArquivo = 'Certificado_' . (int)$certificado->bolsista_id . '_' . date('Ymd_His'); ?>
    <title><?= h($nomeArquivo) ?></title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            margin: 0;
            background: #eef1f4;
        }

        .toolbar {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 10;
            display: flex;
            gap: 12px;
            padding: 10px 12px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid #d8dee6;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.12);
        }

        .toolbar .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 999px;
            border: 1px solid #c8d0d9;
            background: #ffffff;
            color: #1f2937;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }

        .toolbar .btn-primary {
            background: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }

        .certificado-wrap {
            padding: 24px 0 40px;
            display: flex;
            justify-content: center;
        }

        .certificado {
            position: relative;
            width: min(96vw, 297mm);
            aspect-ratio: 841.89 / 595.281;
            background: #fff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
            line-height: 1.35;
            overflow: hidden;
        }

        .certificado-bg {
            position: absolute;
            inset: 0;
            z-index: 1;
        }

        .certificado-bg img {
            display: block;
            width: 100%;
            height: 100%;
        }

        .certificado-conteudo {
            position: absolute;
            inset: 0;
            z-index: 2;
        }

        .bloco-nome,
        .bloco-texto-1,
        .bloco-texto-2,
        .bloco-validacao {
            position: absolute;
            width: 47.51%;
        }

        .bloco-nome {
            top: 38.80%;
            left: 42.29%;
        }

        .bloco-texto-1 {
            top: 48.71%;
            left: 42.29%;
        }

        .bloco-texto-2 {
            top: 57.11%;
            left: 42.29%;
            text-align: justify;
        }

        .bloco-validacao {
            top: 87.18%;
            left: 43.23%;
            text-align: right;
            font-size: 10px;
        }

        .nome-principal {
            font-size: clamp(18px, 1.8vw, 20px);
            font-weight: bold;
            margin: 2px 0;
        }

        .cpf-linha {
            font-size: clamp(11px, 1.1vw, 12px);
            margin: 2px 0;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 6mm;
            }

            body {
                background: #fff;
            }

            .toolbar {
                display: none;
            }

            .certificado-wrap {
                padding: 0;
            }

            .certificado {
                width: 100%;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" class="btn btn-primary" onclick="window.print()">
            Imprimir / Salvar em PDF
        </button>
        <a href="javascript:window.close();" class="btn">
            Fechar
        </a>
    </div>

    <div class="certificado-wrap">
        <div class="certificado">
            <div class="certificado-bg">
                <img src="/img/certificado_nome.svg" alt="">
            </div>

            <div class="certificado-conteudo">
                <div class="bloco-nome">
                    <p class="nome-principal"><?=$user->nome;?></p>
                    <p class="cpf-linha">CPF: <?=$user->cpf;?></p>
                </div>

                <div class="bloco-texto-1">
                    <strong><?=$texto[0];?></strong>
                </div>

                <div class="bloco-texto-2">
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

                <div class="bloco-validacao">
                    Valide este certificado em http://pibic.fiocruz.br/certificados/validar/<?=$codigo?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
