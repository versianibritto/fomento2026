<?php
$banco = [

    'host' => 'localhost',
    'username' => 'root',
    'password' => 'segredo',
    'database' => 'pibiclocal',

    /*
    'host' => '157.86.11.114',
    'username' => 'TST_PIBIC',
    'password' => '@123Pibic2023',
    'database' => 'TST_VPPCB_PIBIC',
    */
    
];

$situacao = [
    'I' => 'Inscrição em andamento',
    'N' => 'Solicitação realizada',
    'A' => 'Ativo',
    'O' => 'Renovação em andamento',
    'R' => 'Renovação',
    'E' => 'Encerrado',
    'U' => 'Processo de substituição',
    'S' => 'Substituído',
    'X' => 'Reprovado',
    'C' => 'Cancelamento solicitado',
    'Q' => 'Cancelado',
    'L' => 'Finalizando bolsa', 
    'B' => 'Banco Reserva',
    'P' => 'Aprovado',
    'F' => 'Renovação Finalizada',
    'W' => 'Cancelamento para substituir solicitado', 
    'Y' => 'Cancelado* (subst)'
];

$hoje=date('Y_m_d_H:m');


$query = "
            WHERE p.vigente=1 and p.deleted=0";
try
{
    $PDO = new PDO('mysql:host='.$banco['host'].';dbname='.$banco['database'], $banco['username'], $banco['password']);
}
catch ( PDOException $e )
{
    echo 'Erro ao conectar com o MySQL: ' . $e->getMessage();
}
$PDO->exec("set names utf8");
$result = $PDO->query( $query );
$rows = $result->fetchAll();
$arquivo = "
<table>
    <tr>
        
        <th>Projeto_bolsista_id</th>
        <th>usuario_id_bolsista</th>
        <th>nome_bolsista</th>
        <th>nome_Social_bolsista</th>

        <th>sexo</th>
        <th>cpf_bolsista</th>
        <th>documento</th>
        <th>documento_numero</th>
        <th>documento_emissor</th>
        <th>nascimento</th>
        <th>telefone</th>
        <th>telefone_contato</th>
        <th>celular</th>
        <th>whatsapp</th>
        <th>cep</th>
        <th>rua</th>
        <th>complemento</th>
        <th>bairro</th>
        <th>cidade</th>
        <th>estado</th>
        <th>email_bolsista</th>
        <th>curso</th>
        <th>nome_orientador</th>
        <th>nome_social__orientador</th>

        <th>email_orientador</th>
        <th>unidade_orientador</th>
        <th>projeto_id</th>
        <th>projeto_orientador</th>
        <th>programa</th>
        <th>fonte_pagadora</th>
        <th>cota</th>
        <th>Situacao</th>
        <th>vigente</th>
        <th>Origem</th>
        <th>deleted</th>

        <th>justificativa_cancelamento</th>
        <th>data_inicio</th>

        

        

    </tr>";
    foreach($rows as $r){

        $arquivo .= "<tr>
                    <td style='text-align:center'>".$r['id']."</td>
                    <td style='text-align:center'>".$r['bolsista_id']."</td>
                    <td style='text-align:center'>".utf8_decode($r['nome_bolsista'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['social_bolsista'])."</td>

                    <td style='text-align:center'>".utf8_decode($r['sexo']=='F'?'Feminino':($r['sexo']=='M'?'Masculino':'Não declarado'))."</td>
                    <td style='text-align:center'>".utf8_decode($r['cpf_bolsista'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['documento']=='R'?'RG':($r['documento']=='H'?'CNH':'Passaporte'))."</td>
                    <td style='text-align:center'>".utf8_decode($r['documento_numero'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['documento_emissor'])."</td>
                    <td style='text-align:center'>".$r['nascimento']."</td>
                    <td style='text-align:center'>".utf8_decode($r['telefone'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['telefone_contato'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['celular'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['whatsapp'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['cep'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['rua'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['complemento'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['bairro'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['cidade'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['estado'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['email_bolsista'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['curso'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['nome_orientador'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['social_orientador'])."</td>

                    <td style='text-align:center'>".utf8_decode($r['email_orientador'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['unidade_orientador'])."</td>
                    <td style='text-align:center'>".$r['projeto_id']."</td>
                    <td style='text-align:center'>".utf8_decode($r['projeto_orientador'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['programa']=='T'?'PIBITI':($r['programa']=='I'?'Iniciação Científica':'PIBIC'))."</td>
                    <td style='text-align:center'>".utf8_decode($r['tipo_bolsa']=='F'?'Fiocruz':($r['tipo_bolsa']=='C'?'CNPQ':($r['tipo_bolsa']=='T'?'Fiocruz 5 - temporário':'Cogepe')))."</td>
                    <td style='text-align:center'>".utf8_decode($r['cota']=='G'?'GERAL':($r['cota']=='N'?'Negros/Pardos':($r['cota']=='T'?'Transexual':'Deficiente')))."</td>
                    <td style='text-align:center'>".utf8_decode($situacao[$r['situacao']])."</td>
                    <td style='text-align:center'>".utf8_decode($r['vigente']==1?'Vigente':'Não')."</td>
                    <td style='text-align:center'>".utf8_decode($r['origem']=='N'?'Nova':($r['origem']=='R'?'Renovação':($r['origem']=='S'?'Substituição':($r['origem']=='A'?'Subst na imlementação':'Alteração Projeto'))))."</td>
                    <td style='text-align:center'>".utf8_decode($r['deleted']==1?'Deletado':'-')."</td>
                    <td style='text-align:center'>".utf8_decode($r['justificativa_cancelamento'])."</td>
                    <td style='text-align:center'>".$r['data_inicio']."</td>


                </tr>";
    }
$arquivo .= "</table>";
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: application/x-msexcel; charset=utf-8");
header ("Content-Disposition: attachment; filename=\"Vigentes_".($hoje).".xls\"" );
header ("Content-Description: Planilha Sistema PIBIC" );
print $arquivo;
exit;