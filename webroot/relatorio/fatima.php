<?php
$banco = [

    'host' => '192.168.3.32',
    'username' => 'root',
    'password' => 'uaarae11',
    'database' => 'pibic_atual',

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


$query = "select distinct (t.id),
            

            UPPER(z.nome) as nome_orientador,
            UPPER(z.nome_social) as social_orientador,

            z.email as email_orientador,
            z.unidade_id,
            sg.sigla as unidade_orientador, 
            p.projeto_id, 

            t.titulo as projeto_orientador,
            p.programa,
            p.vigente

            FROM `projeto_bolsistas`p 

            left join usuarios u on u.id=p.usuario_id
            left join usuarios z on z.id=p.orientador
            left join unidades sg on sg.id=z.unidade_id
            left join projetos t on t.id=p.projeto_id



            WHERE (t.titulo like '%biodiversidade%') or (t.resumo like '%biodiversidade%') or (p.sp_titulo like '%biodiversidade%') or (p.sp_titulo like '%biodiversidade%')";
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
        
        <th>nome_orientador</th>
        <th>nome_social__orientador</th>

        <th>email_orientador</th>
        <th>unidade_orientador</th>
        <th>projeto_id</th>
        <th>projeto_orientador</th>
        <th>programa</th>
        <th>data inicio</th>
        <th>vigente</th>



        

        

    </tr>";
    foreach($rows as $r){

        $arquivo .= "<tr>
                    <td style='text-align:center'>".utf8_decode($r['nome_orientador'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['social_orientador'])."</td>

                    <td style='text-align:center'>".utf8_decode($r['email_orientador'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['unidade_orientador'])."</td>
                    <td style='text-align:center'>".$r['projeto_id']."</td>
                    <td style='text-align:center'>".utf8_decode($r['projeto_orientador'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['programa']=='T'?'PIBITI':($r['programa']=='I'?'Iniciação Científica':'PIBIC'))."</td>
                    <td style='text-align:center'>".$r['data_inicio']."</td>
                    <td style='text-align:center'>".utf8_decode($r['vigente']==1?'Vigente':'Não')."</td>



                </tr>";
    }
$arquivo .= "</table>";
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: application/x-msexcel; charset=utf-8");
header ("Content-Disposition: attachment; filename=\"Fatima".($hoje).".xls\"" );
header ("Content-Description: Planilha Sistema PIBIC" );
print $arquivo;
exit;