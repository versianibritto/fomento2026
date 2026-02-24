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


$query = "select 
            p.id, 
            p.usuario_id as bolsista_id, 
            UPPER(u.nome) as nome_bolsista, 
            UPPER(u.nome_social) as social_bolsista, 

            u.sexo,
            u.cpf as cpf_bolsista, 
            u.telefone,
            u.telefone_contato,
            u.celular,
            u.whatsapp,
            u.email as email_bolsista, 
            UPPER(z.nome) as nome_orientador,
            UPPER(z.nome_social) as social_orientador,

            z.email as email_orientador,
            sg.sigla as unidade_orientador, 
            a.nome as area,
            l.nome as linha_fiocruz,
            f.nome as area_fiocruz,





            p.projeto_id, 
            t.titulo as projeto_orientador,
            p.sp_titulo as subprojeto,

            p.programa,
            p.cota,
            p.situacao as situacao,
            p.subprojeto_renovacao as sub_alterado,
            p.justificativa_alteracao as justificativa_alteracao,
            p.apresentar_raic as apresentar_raic,
            p.referencia_inscricao_anterior as referencia_inscricao_anterior

            FROM `projeto_bolsistas`p 

            left join usuarios u on u.id=p.usuario_id
            
            left join usuarios z on z.id=p.orientador
            left join unidades sg on sg.id=z.unidade_id
            left join projetos t on t.id=p.projeto_id
            left join areas a on t.area_id=a.id
            left join linhas l on t.linha_id=l.id
            left join areas_fiocruz f on f.id=l.areas_fiocruz_id
            WHERE p.situacao in ('F', 'O') and p.deleted=0";
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
        <th>nome_Social_bolsista</th>
        <th>sexo</th>
        <th>cpf_bolsista</th>
        <th>telefone</th>
        <th>telefone_contato</th>
        <th>celular</th>
        <th>whatsapp</th>
        <th>email_bolsista</th>
        <th>nome_orientador</th>
        <th>nome_social__orientador</th>

        <th>email_orientador</th>
        <th>unidade_orientador</th>
        <th>area CNPQ</th>
        <th>area Fiocruz</th>
        <th>linha Fiocruz</th>

        <th>projeto_id</th>
        <th>projeto_orientador</th>
        <th>subprojeto</th>

        <th>programa</th>
        <th>cota</th>
        <th>Situacao</th>
        <th>sub_alterado?</th>
        <th>justificativa_alteracao</th>
        <th>apresentar_raic?</th>

        <th>referencia_inscricao_anterior</th>

        

        

    </tr>";
    foreach($rows as $r){

        $arquivo .= "<tr>
                    <td style='text-align:center'>".$r['id']."</td>
                    <td style='text-align:center'>".utf8_decode($r['nome_bolsista'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['social_bolsista'])."</td>

                    <td style='text-align:center'>".utf8_decode($r['sexo']=='F'?'Feminino':($r['sexo']=='M'?'Masculino':'Não declarado'))."</td>
                    <td style='text-align:center'>".utf8_decode($r['cpf_bolsista'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['telefone'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['telefone_contato'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['celular'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['whatsapp'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['email_bolsista'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['nome_orientador'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['social_orientador'])."</td>

                    <td style='text-align:center'>".utf8_decode($r['email_orientador'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['unidade_orientador'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['area'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['linha_fiocruz'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['area_fiocruz'])."</td>
                    <td style='text-align:center'>".$r['projeto_id']."</td>
                    <td style='text-align:center'>".utf8_decode($r['projeto_orientador'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['subprojeto'])."</td>

                    <td style='text-align:center'>".utf8_decode($r['programa']=='T'?'PIBITI':($r['programa']=='I'?'Iniciação Científica':'PIBIC'))."</td>
                    <td style='text-align:center'>".utf8_decode($r['cota']=='G'?'GERAL':($r['cota']=='N'?'Negros/Pardos':($r['cota']=='T'?'Transexual':'Deficiente')))."</td>
                    <td style='text-align:center'>".utf8_decode($situacao[$r['situacao']])."</td>
                    <td style='text-align:center'>".utf8_decode($r['sub_alterado']=='I'?'Manteve o da insc anterior':'Novo subproj cadastrado')."</td>
                    <td style='text-align:center'>".utf8_decode($r['justificativa_alteracao'])."</td>
                    <td style='text-align:center'>".utf8_decode($r['apresentar_raic']==1?'Sim':'Não')."</td>
                    <td style='text-align:center'>".$r['referencia_inscricao_anterior']."</td>


                </tr>";
    }
$arquivo .= "</table>";
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: application/x-msexcel; charset=utf-8");
header ("Content-Disposition: attachment; filename=\"Renov_Resumo_".($hoje).".xls\"" );
header ("Content-Description: Planilha Sistema PIBIC" );
print $arquivo;
exit;