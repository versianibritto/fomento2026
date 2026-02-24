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

$hoje=date('Y_m_d_H:m');


$query = "select 
            p.id, 
            p.usuario_id as bolsista_id, 
            UPPER(u.nome) as nome_bolsista, 
            u.sexo,
            u.cpf as cpf_bolsista, 
            u.documento,
            u.documento_numero,
            u.documento_emissor,
            date_format(u.data_nascimento, '%d/%m/%Y') as nascimento,
            u.telefone,
            u.telefone_contato,
            u.celular,
            u.whatsapp,
            s.cep as cep,
            UPPER(s.nome) as rua,
            u.complemento as complemento,
            UPPER(d.nome) as bairro,
            UPPER(c.nome) as cidade,
            st.sigla as estado,
            u.email as email_bolsista, 
            u.curso,
            UPPER(z.nome) as nome_orientador,
            z.email as email_orientador,
            z.unidade_id,
            sg.sigla as unidade_orientador, 
            p.projeto_id, 
            t.titulo as projeto_orientador,
            p.programa,
            p.cota,
            p.situacao as situacao,
            date_format(DATE_ADD(p.data_inicio, INTERVAL 3 HOUR), '%d/%m/%Y') as data_inicio,
            p.vigente,
            p.deleted,
            p.justificativa_cancelamento,
            p.origem,
            p.tipo_bolsa,

            k.id as id_2023,
            k.resultado as resultado_2023,
            date_format(DATE_ADD(k.data_inicio, INTERVAL 3 HOUR), '%d/%m/%Y') as data_inicio2023,
            k.situacao as situacao_2023,
            k.vigente as vigente_2023,
            k.justificativa_cancelamento as justificativa_cancelamento_2023


            FROM `projeto_bolsistas`p 

            left join usuarios u on u.id=p.usuario_id
            left join streets s on u.street_id=s.id
            left join districts d on d.id=s.district_id
                left join cities c on c.id=d.city_id
                    left join states st on st.id=c.state_id

            left join usuarios z on z.id=p.orientador
            left join unidades sg on sg.id=z.unidade_id
            left join projetos t on t.id=p.projeto_id

            left join (select id, usuario_id, resultado, data_inicio, situacao, vigente, justificativa_cancelamento from  projeto_bolsistas where editai_id in (17,18,19,20) and deleted=0) k on k.usuario_id=p.usuario_id

            WHERE p.editai_id in (13,14,15,16) and p.data_inicio is not null";
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
        <th>data_inicio_2022</th>
        <th>id_2023</th>
        <th>resultado_2023</th>
        <th>data_inicio_2023</th>
        <th>situacao_2023</th>
        <th>vigente_2023</th>
        <th>justificativa_cancelamento_2023</th>


        

        

    </tr>";
    foreach($rows as $r){

        $arquivo .= "<tr>
                    <td style='text-align:center'>".$r['id']."</td>
                    <td style='text-align:center'>".$r['bolsista_id']."</td>
                    <td style='text-align:center'>".utf8_decode($r['nome_bolsista'])."</td>
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

                    <td style='text-align:center'>".$r['id_2023']."</td>
                    <td style='text-align:center'>".utf8_decode($r['resultado_2023']=='A'?'Aprovado':($r['resultado_2023']=='B'?'Reserva':(($r['resultado_2023']=='R'?'Reprovado':(($r['resultado_2023']=='T'?'Aprovação automatica':'----'))))))."</td>
                    <td style='text-align:center'>".$r['data_inicio2023']."</td>
                    <td style='text-align:center'>".utf8_decode($situacao[$r['situacao_2023']])."</td>
                    <td style='text-align:center'>".utf8_decode($r['vigente_2023']==1?'Vigente':'Não')."</td>

                    <td style='text-align:center'>".utf8_decode($r['justificativa_cancelamento_2023'])."</td>

          


                </tr>";
    }
$arquivo .= "</table>";
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: application/x-msexcel; charset=utf-8");
header ("Content-Disposition: attachment; filename=\"Naorenvados".($hoje).".xls\"" );
header ("Content-Description: Planilha Sistema PIBIC" );
print $arquivo;
exit;