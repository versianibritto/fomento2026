<?php
$banco = [

    'host' => 'localhost',
    'username' => 'root',
    'password' => 'Uaarae11@',
    'database' => 'pibicv3',

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


$query = "select 
                p.id, 
                UPPER(u.nome) as nome_bolsista, 
                u.cpf as cpf_bolsista, 
                u.telefone,
                u.telefone_contato,
                u.celular,
                u.whatsapp,

                u.email as email_bolsista, 
                UPPER(z.nome) as nome_orientador,
                z.email as email_orientador,
                sg.sigla as unidade_orientador, 
                p.programa,
                p.cota,
                p.situacao as situacao,
                date_format(entrada.data_inicio, '%d/%m/%Y') as data_inicio,
                p.vigente,
                p.deleted,
                p.justificativa_cancelamento,
                p.origem

                FROM `projeto_bolsistas`p 

                left join usuarios u on u.id=p.usuario_id


                left join usuarios z on z.id=p.orientador
                left join unidades sg on sg.id=z.unidade_id
                left join (SELECT usuario_id, projeto_id, MIN(data_inicio) as data_inicio FROM `projeto_bolsistas` group by usuario_id, projeto_id) as entrada ON (entrada.usuario_id =  u.id AND entrada.projeto_id=p.projeto_id)
                WHERE p.vigente=1 and p.deleted=0 and p.origem='R'";
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
        <th>nome_bolsista</th>
        <th>cpf_bolsista</th>
        <th>telefone</th>
        <th>telefone_contato</th>
        <th>celular</th>
        <th>whatsapp</th>
        <th>email_bolsista</th>
        <th>nome_orientador</th>
        <th>email_orientador</th>
        <th>unidade_orientador</th>
        <th>programa</th>
        <th>cota</th>
        <th>vigente</th>
        <th>Origem</th>
        <th>data_inicio</th>

        

        

    </tr>";
    foreach($rows as $r){

        $arquivo .= "<tr>
                    <td style='text-align:center'>".$r['id']."</td>
                    <td style='text-align:center'>".utf8_decode($r['nome_bolsista'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['cpf_bolsista'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['telefone'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['telefone_contato'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['celular'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['whatsapp'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['email_bolsista'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['nome_orientador'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['email_orientador'])."</td>
                    <td style='text-align:center'>".$r['unidade_orientador']."</td>
                    <td style='text-align:center'>".($r['programa']=='T'?'PIBITI':'PIBIC')."</td>
                    <td style='text-align:center'>".utf8_decode($r['cota']=='G'?'GERAL':($r['cota']=='N'?'Negros/Pardos':($r['cota']=='T'?'Transexual':'Deficiente')))."</td>
                    <td style='text-align:center'>".utf8_decode($r['vigente']==1?'Vigente':'Não')."</td>
                    <td style='text-align:center'>".utf8_decode($r['origem']=='N'?'Nova':($r['origem']=='R'?'Renovação':($r['origem']=='S'?'Substituição':($r['origem']=='A'?'Subst na imlementação':'Alteração Projeto'))))."</td>
                    <td style='text-align:center'>".$r['data_inicio']."</td>


                </tr>";
    }
$arquivo .= "</table>";
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: application/x-msexcel; charset=utf-8");
header ("Content-Disposition: attachment; filename=\"Renova_email.xls\"" );
header ("Content-Description: Planilha Sistema PIBIC" );
print $arquivo;
exit;