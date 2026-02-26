-- 26/12/2026 initial


-- alterar usuario e incluir subcoordendor
ALTER TABLE `unidades` ADD COLUMN `subcoordenador` INT NULL AFTER `whatsapp`, CHANGE COLUMN `usuario_id` `coordenador` INT NULL DEFAULT NULL ;
ALTER TABLE `unidades` ADD COLUMN `deleted` TINYINT NULL DEFAULT 0 AFTER `subcoordenador`;


-- criar programas
CREATE TABLE `programas` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `sigla` VARCHAR(45) NULL, `nome` VARCHAR(300) NULL, `letra` ENUM('P', 'T', 'I', 'J', 'M', 'A', 'V', 'W', 'G', 'D') NULL, `deleted` INT NULL, PRIMARY KEY (`id`));
INSERT INTO `programas` (`sigla`, `letra`, `deleted`) VALUES ('PDJ', 'J', '0');
INSERT INTO `programas` (`sigla`, `letra`, `deleted`) VALUES ('PIBIC', 'P', '0');
INSERT INTO `programas` (`sigla`, `letra`, `deleted`) VALUES ('PIBITI', 'T', '0');
INSERT INTO `programas` (`sigla`, `letra`, `deleted`) VALUES ('IC MAnguinhos', 'I', '0');
INSERT INTO `programas` (`sigla`, `letra`, `deleted`) VALUES ('IC Mata', 'A', '0');
INSERT INTO `programas` (`sigla`, `letra`, `deleted`) VALUES ('IC Maré', 'M', '0');
INSERT INTO `programas` (`sigla`, `letra`, `deleted`) VALUES ('Raics de Outras Agências', 'V', '0');
INSERT INTO `programas` (`sigla`, `letra`, `deleted`) VALUES ('Workshop', 'W', '0');
INSERT INTO `programas` (`sigla`, `letra`, `deleted`) VALUES ('IC Indigena', 'G', '0');
INSERT INTO `programas` (`sigla`, `letra`, `deleted`) VALUES ('IC PDJ', 'D', '0');


-- atualizando editais

ALTER TABLE `editais` ADD COLUMN `programa_id` INT NULL AFTER `evento`;
UPDATE editais e JOIN programas p ON e.tipo = p.letra SET e.programa_id = p.id;
-- se precisar coluca o modo safe where e.id>0;

        -- limpando a tabela editais
        ALTER TABLE `editais` DROP COLUMN `regra`, DROP COLUMN `sub_obrigatorio`, DROP COLUMN `hasCota`, DROP COLUMN `entrega_documentacao_presidencia`, DROP COLUMN `entrega_documentacao_unidade`, DROP COLUMN `tipo`;
        ALTER TABLE `editais` DROP COLUMN `unidade_id`, DROP COLUMN `p_nova`, DROP COLUMN `p_renovacao`, DROP COLUMN `fim_renovacao`, DROP COLUMN `inicio_renovacao`;


-- criar fases
CREATE TABLE `fases` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `nome` VARCHAR(45) NULL, `bloco` ENUM('I', 'H', 'R', 'A') NULL COMMENT 'Inscrição, homologação, Resultado, Andamento', `letra` ENUM('I', 'N', 'A', 'E', 'R', 'S', 'O', 'X', 'U', 'C', 'Q', 'L', 'B', 'P', 'W', 'Y', 'F', 'T', 'Z', 'D') NULL, `deleted` INT NULL DEFAULT 0, PRIMARY KEY (`id`));
ALTER TABLE `fases` CHANGE COLUMN `bloco` `bloco` ENUM('I', 'H', 'R', 'A', 'F') NULL DEFAULT NULL COMMENT 'Inscrição, Finalizada, homologação, Resultado, Andamento' ;

INSERT INTO `fases` (`nome`, `bloco`, `letra`) VALUES ('Rascunho Nova', 'I', 'I');
INSERT INTO `fases` (`nome`, `bloco`, `letra`) VALUES ('Nova Finalizada', 'F', 'N');
INSERT INTO `fases` (`nome`, `bloco`, `letra`) VALUES ('Rascunho Renovação', 'I', 'O');
INSERT INTO `fases` (`nome`, `bloco`, `letra`) VALUES ('Renovação Finalizada', 'F', 'F');
INSERT INTO `fases` (`nome`, `bloco`) VALUES ('Termo Gerado', 'I');
INSERT INTO `fases` (`nome`, `bloco`) VALUES ('Homologada', 'H');
INSERT INTO `fases` (`nome`, `bloco`) VALUES ('Não homologada', 'H');
INSERT INTO `fases` (`nome`, `bloco`, `letra`) VALUES ('Banco Reserva', 'R', 'B');
INSERT INTO `fases` (`nome`, `bloco`, `letra`) VALUES ('Aprovado', 'R', 'P');
INSERT INTO `fases` (`nome`, `bloco`, `letra`) VALUES ('Reprovado', 'R', 'X');
INSERT INTO `fases` (`nome`, `bloco`, `letra`) VALUES ('Ativo', 'A', 'A');
INSERT INTO `fases` (`nome`, `bloco`, `letra`) VALUES ('Cancelamento solicitado', 'A', 'C');
INSERT INTO `fases` (`nome`, `bloco`, `letra`) VALUES ('Cancelado', 'A', 'Q');
INSERT INTO `fases` (`nome`, `bloco`, `letra`) VALUES ('Substituído', 'A', 'S');
INSERT INTO `fases` (`nome`, `bloco`, `letra`) VALUES ('Processo de substituição', 'I', 'U');
INSERT INTO `fases` (`nome`, `bloco`, `letra`) VALUES ('Reserva/substituição', 'A', 'D');
INSERT INTO `fases` (`nome`, `bloco`, `letra`) VALUES ('Encerrado', 'A', 'E');
INSERT INTO `fases` (`nome`, `bloco`, `letra`) VALUES ('Finalizando bolsa', 'A', 'L');
INSERT INTO `fases` (`nome`, `bloco`, `letra`) VALUES ('Renovação Solicitada', 'A', 'R');
INSERT INTO `fases` (`nome`, `bloco`, `letra`) VALUES ('Troca de projeto/orientação', 'A', 'T');
INSERT INTO `fases` (`nome`, `bloco`, `letra`) VALUES ('Cancelamento com substituição posterior', 'A', 'W');
INSERT INTO `fases` (`nome`, `bloco`, `letra`) VALUES ('Cancelado, aguardando substituição', 'A', 'Y');
INSERT INTO `fases` (`nome`, `bloco`, `letra`) VALUES ('Suspenso/Licença', 'A', 'Z');


    -- atualizando projeto_bolsistas
    ALTER TABLE `projeto_bolsistas` ADD COLUMN `fase_id` INT NULL AFTER `resumo_relatorio`;
    UPDATE projeto_bolsistas e JOIN fases p ON e.situacao = p.letra SET e.fase_id = p.id;
        -- safe se precisar where e.id>0;

    -- atualizando pdj
    ALTER TABLE `pdj_inscricoes` ADD COLUMN `fase_id` INT NULL AFTER `pontos_bolsista`;
    UPDATE pdj_inscricoes e JOIN fases p ON e.situacao = p.letra SET e.fase_id = p.id;
        -- se precisar where e.id>0;


-- PDJ Inscrições => atores (bolsista, orientador + coorientador)
ALTER TABLE `pdj_inscricoes` ADD COLUMN `coorientador` INT NULL AFTER `fase_id`, CHANGE COLUMN `usuario_id` `orientador` INT NOT NULL ;

-- proj_bol atores (bolsista)
ALTER TABLE `projeto_bolsistas` CHANGE COLUMN `usuario_id` `bolsista` INT UNSIGNED NULL DEFAULT NULL ;

-- PDJ edital
ALTER TABLE `pdj_inscricoes` CHANGE COLUMN `edital_id` `editai_id` INT NULL DEFAULT NULL ;


-- views dah
CREATE 
    
VIEW `dashcounts` AS
    SELECT 
        COUNT(`pb`.`id`) AS `qtd`,
        `pb`.`bolsista` AS `bolsista`,
        `pb`.`orientador` AS `orientador`,
        `pb`.`coorientador` AS `coorientador`,
        `pb`.`fase_id` AS `fase_id`,
        `f`.`bloco` AS `bloco`,
        `pb`.`vigente` AS `vigente`
    FROM
        ((`projeto_bolsistas` `pb`
        LEFT JOIN `fases` `f` ON ((`f`.`id` = `pb`.`fase_id`)))
        LEFT JOIN `editais` `e` ON ((`e`.`id` = `pb`.`editai_id`)))
    WHERE
        ((`pb`.`deleted` = 0)
            AND (`e`.`fim_vigencia` > NOW()))
    GROUP BY `pb`.`bolsista` , `pb`.`orientador` , `pb`.`coorientador` , `pb`.`fase_id` , `f`.`bloco` , `pb`.`vigente` 
    UNION ALL SELECT 
        COUNT(`pdj`.`id`) AS `qtd`,
        `pdj`.`bolsista` AS `bolsista`,
        `pdj`.`orientador` AS `orientador`,
        `pdj`.`coorientador` AS `coorientador`,
        `pdj`.`fase_id` AS `fase_id`,
        `f`.`bloco` AS `bloco`,
        `pdj`.`vigente` AS `vigente`
    FROM
        ((`pdj_inscricoes` `pdj`
        LEFT JOIN `fases` `f` ON ((`f`.`id` = `pdj`.`fase_id`)))
        LEFT JOIN `editais` `e` ON ((`e`.`id` = `pdj`.`editai_id`)))
    WHERE
        ((`pdj`.`deleted` IS NULL)
            AND (`e`.`fim_vigencia` > NOW()))
    GROUP BY `pdj`.`bolsista` , `pdj`.`orientador` , `pdj`.`coorientador` , `pdj`.`fase_id` , `f`.`bloco` , `pdj`.`vigente`




CREATE 
    
VIEW `dashdetalhes` AS
    SELECT 
        `pb`.`id` AS `id`,
        `pb`.`bolsista` AS `bolsista`,
        `b`.`nome` AS `nome_bolsista`,
        `pb`.`orientador` AS `orientador`,
        `o`.`nome` AS `nome_orientador`,
        `pb`.`coorientador` AS `coorientador`,
        `c`.`nome` AS `nome_coorientador`,
        `pb`.`projeto_id` AS `projeto_id`,
        `pb`.`fase_id` AS `fase_id`,
        `f`.`nome` AS `nome_fase`,
        `f`.`bloco` AS `bloco`,
        `pb`.`vigente` AS `vigente`,
        `pb`.`editai_id` AS `editai_id`,
        `e`.`nome` AS `nome_edital`,
        `p`.`sigla` AS `nome_programa`,
        `pb`.`data_inicio` AS `data_inicio`,
        `pb`.`data_fim` AS `data_fim`,
        `e`.`fim_vigencia` AS `fim_vigencia`,
        (CASE
            WHEN (`pb`.`deleted` = 0) THEN 1
            ELSE 0
        END) AS `ativo`
    FROM
        ((((((`projeto_bolsistas` `pb`
        LEFT JOIN `usuarios` `b` ON ((`b`.`id` = `pb`.`bolsista`)))
        LEFT JOIN `usuarios` `o` ON ((`o`.`id` = `pb`.`orientador`)))
        LEFT JOIN `usuarios` `c` ON ((`c`.`id` = `pb`.`coorientador`)))
        LEFT JOIN `fases` `f` ON ((`f`.`id` = `pb`.`fase_id`)))
        LEFT JOIN `editais` `e` ON ((`e`.`id` = `pb`.`editai_id`)))
        LEFT JOIN `programas` `p` ON ((`p`.`id` = `e`.`programa_id`))) 
    UNION ALL SELECT 
        `pdj`.`id` AS `id`,
        `pdj`.`bolsista` AS `bolsista`,
        `b`.`nome` AS `nome_bolsista`,
        `pdj`.`orientador` AS `orientador`,
        `o`.`nome` AS `nome_orientador`,
        `pdj`.`coorientador` AS `coorientador`,
        `c`.`nome` AS `nome_coorientador`,
        `pdj`.`projeto_id` AS `projeto_id`,
        `pdj`.`fase_id` AS `fase_id`,
        `f`.`nome` AS `nome_fase`,
        `f`.`bloco` AS `bloco`,
        `pdj`.`vigente` AS `vigente`,
        `pdj`.`editai_id` AS `editai_id`,
        `e`.`nome` AS `nome_edital`,
        `p`.`sigla` AS `nome_programa`,
        `pdj`.`data_inicio` AS `data_inicio`,
        `pdj`.`data_fim` AS `data_fim`,
        `e`.`fim_vigencia` AS `fim_vigencia`,
        (CASE
            WHEN (`pdj`.`deleted` IS NULL) THEN 1
            ELSE 0
        END) AS `ativo`
    FROM
        ((((((`pdj_inscricoes` `pdj`
        LEFT JOIN `usuarios` `b` ON ((`b`.`id` = `pdj`.`bolsista`)))
        LEFT JOIN `usuarios` `o` ON ((`o`.`id` = `pdj`.`orientador`)))
        LEFT JOIN `usuarios` `c` ON ((`c`.`id` = `pdj`.`coorientador`)))
        LEFT JOIN `fases` `f` ON ((`f`.`id` = `pdj`.`fase_id`)))
        LEFT JOIN `editais` `e` ON ((`e`.`id` = `pdj`.`editai_id`)))
        LEFT JOIN `programas` `p` ON ((`p`.`id` = `e`.`programa_id`)))

CREATE 
    
VIEW `dashyodacounts` AS
    SELECT 
        COUNT(`pb`.`id`) AS `qtd`,
        `pb`.`fase_id` AS `fase_id`,
        `f`.`bloco` AS `bloco`,
        `pb`.`vigente` AS `vigente`,
        `e`.`programa_id` AS `programa_id`,
        `pb`.`editai_id` AS `editai_id`
    FROM
        ((`projeto_bolsistas` `pb`
        LEFT JOIN `fases` `f` ON ((`f`.`id` = `pb`.`fase_id`)))
        LEFT JOIN `editais` `e` ON ((`e`.`id` = `pb`.`editai_id`)))
    WHERE
        ((`pb`.`deleted` = 0)
            AND (`e`.`fim_vigencia` > NOW()))
    GROUP BY `pb`.`fase_id` , `f`.`bloco` , `pb`.`vigente` , `e`.`programa_id` , `pb`.`editai_id` 
    UNION ALL SELECT 
        COUNT(`pdj`.`id`) AS `qtd`,
        `pdj`.`fase_id` AS `fase_id`,
        `f`.`bloco` AS `bloco`,
        `pdj`.`vigente` AS `vigente`,
        `e`.`programa_id` AS `programa_id`,
        `pdj`.`editai_id` AS `editai_id`
    FROM
        ((`pdj_inscricoes` `pdj`
        LEFT JOIN `fases` `f` ON ((`f`.`id` = `pdj`.`fase_id`)))
        LEFT JOIN `editais` `e` ON ((`e`.`id` = `pdj`.`editai_id`)))
    WHERE
        ((`pdj`.`deleted` IS NULL)
            AND (`e`.`fim_vigencia` > NOW()))
    GROUP BY `pdj`.`fase_id` , `f`.`bloco` , `pdj`.`vigente` , `e`.`programa_id` , `pdj`.`editai_id`


-- alterações na tabela usuarios
ALTER TABLE `usuarios` 
DROP COLUMN `aPIBIC`,
DROP COLUMN `cPIBIC`,
DROP COLUMN `bPIBIC`,
DROP COLUMN `oPIBIC`,
DROP COLUMN `aPIBITI`,
DROP COLUMN `cPIBITI`,
DROP COLUMN `oPIBITI`,
DROP COLUMN `bPIBITI`,
DROP COLUMN `coorientador_pibic`,
DROP COLUMN `orientador_pibic`,
DROP COLUMN `bolsista_pibic`,
DROP COLUMN `pontos_bolsista`,
DROP COLUMN `pontos`,
DROP COLUMN `lattes_validado`,
DROP COLUMN `filhos_menor`,
DROP COLUMN `naturalidade_id`;

-- limpar campos de gestão
SELECT id, yoda, jedi, padauan FROM usuarios where padauan='';
update usuarios set padauan = null where padauan='' and id>0;

SELECT id, yoda, jedi, padauan FROM usuarios where jedi='';
update usuarios set jedi = null where jedi='' and id>0;


-- nova tabela hist usuarios
CREATE TABLE `usuarios_historicos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `created` TIMESTAMP NULL,
  `usuario_id` INT UNSIGNED NULL,
  `alterado_por` INT UNSIGNED NULL,
  `contexto` ENUM('E', 'A', 'C') NULL COMMENT 'Editar, Acesso login unico, Criação',
  `diff_json` JSON NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_usuarios_historicos_alterado_por`
    FOREIGN KEY (`alterado_por`) REFERENCES `usuarios` (`id`));

-- incluir contexto P (Perfil) na tabela usuarios_historicos
ALTER TABLE `usuarios_historicos`
    MODIFY COLUMN `contexto` ENUM('E', 'A', 'C', 'P') NULL
    COMMENT 'Editar, Acesso login unico, Criação, Perfil';

-- coluna para origem do acesso (apenas contexto A)
ALTER TABLE `usuarios_historicos`
    ADD COLUMN `origem_acesso` ENUM('F', 'G') NULL
    COMMENT 'Login unico Fiocruz, Gov.br';

-- tabela de excecoes do sistema
CREATE TABLE `ti_exceptions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `created` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` ENUM('N', 'R') NULL COMMENT 'Nova, Respondida',
  `classificacao_id` INT UNSIGNED NULL,
  `usuario_id` INT UNSIGNED NULL,
  `usuario_nome` VARCHAR(150) NULL,
  `usuario_email` VARCHAR(150) NULL,
  `usuario_email_alternativo` VARCHAR(150) NULL,
  `usuario_email_contato` VARCHAR(150) NULL,
  `url` VARCHAR(255) NULL,
  `host` VARCHAR(150) NULL,
  `mensagem` TEXT NULL,
  `arquivo` VARCHAR(255) NULL,
  `linha` INT NULL,
  `hash` VARCHAR(64) NULL,
  `repeticoes` INT NULL DEFAULT 1,
  `repeticao` TINYINT(1) NULL DEFAULT 0,
  `repeticao_de_id` INT UNSIGNED NULL,
  `ultima_ocorrencia` DATETIME NULL,
  `resposta` TEXT NULL,
  `respondido_por` INT UNSIGNED NULL,
  `respondido_em` DATETIME NULL,
  PRIMARY KEY (`id`)
);

-- tipos de classificacao (TI)
CREATE TABLE `ti_tipos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tipo` ENUM('U', 'S', 'I', 'O') NULL COMMENT 'Usuario, Sistema, Infra, Outros',
  `nome` VARCHAR(150) NULL,
  `deleted` TINYINT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
);


INSERT INTO `ti_tipos` (`tipo`, `nome`, `deleted`) VALUES ('U', 'REPETIÇÃO', '0');
INSERT INTO `ti_tipos` (`tipo`, `nome`, `deleted`) VALUES ('U', 'Dados errados no imput do usuario', '0');
INSERT INTO `ti_tipos` (`tipo`, `nome`, `deleted`) VALUES ('U', 'Ação não permitida, regra invalida e afins', '0');
INSERT INTO `ti_tipos` (`tipo`, `nome`, `deleted`) VALUES ('U', 'Submit duplicado (dedo nervoso)', '0');
INSERT INTO `ti_tipos` (`tipo`, `nome`, `deleted`) VALUES ('U', 'Dados do usuario com problema (validação de regra)', '0');
INSERT INTO `ti_tipos` (`tipo`, `nome`, `deleted`) VALUES ('S', 'Erro de sintaxe', '0');
INSERT INTO `ti_tipos` (`tipo`, `nome`, `deleted`) VALUES ('S', 'Falha na base de dados', '0');
INSERT INTO `ti_tipos` (`tipo`, `nome`, `deleted`) VALUES ('S', 'Regra de negócio mal implantada', '0');
INSERT INTO `ti_tipos` (`tipo`, `nome`, `deleted`) VALUES ('I', 'Problema no Login Unico', '0');
INSERT INTO `ti_tipos` (`tipo`, `nome`, `deleted`) VALUES ('I', 'Queda do servidor', '0');


--renomear tabela de acessos
ALTER TABLE `acessos` 
RENAME TO  `usuarios_acessos` ;

-- email de contato do usuario
ALTER TABLE `usuarios` 
ADD COLUMN `email_contato` VARCHAR(45) NULL AFTER `padauan`;

-- normalizacao de aliases da view report_inscricoes_geral
CREATE OR REPLACE
    ALGORITHM = UNDEFINED
    DEFINER = `TST_PIBIC`@`157.86.%`
    SQL SECURITY DEFINER
VIEW `fomento`.`report_inscricoes_geral` AS
    SELECT 
        `p`.`id` AS `id`,
        `p`.`usuario_id` AS `bolsista`,
        `u`.`nome` AS `bolsista_nome`,
        `u`.`cpf` AS `cpf_bolsista`,
        `u`.`documento` AS `documento_bolsista`,
        `u`.`documento_numero` AS `documento_numero_bolsista`,
        `u`.`documento_emissor` AS `documento_emissor_bolsista`,
        `u`.`telefone` AS `telefone_bolsista`,
        `u`.`telefone_contato` AS `telefone_contato_bolsista`,
        `u`.`celular` AS `celular_bolsista`,
        `u`.`whatsapp` AS `whatsapp_bolsista`,
        `u`.`email` AS `email_bolsista`,
        `u`.`email_alternativo` AS `email_alternativo_bolsista`,
        `u`.`email_contato` AS `email_contato_bolsista`,
        `u`.`sexo` AS `sexo_bolsista`,
        `u`.`deficiencia` AS `deficiencia_bolsista`,
        `u`.`raca` AS `raca_bolsista`,
        `fomento`.`streets`.`cep` AS `cep_bolsista`,
        UPPER(`fomento`.`streets`.`nome`) AS `rua_bolsista`,
        `u`.`complemento` AS `complemento_bolsista`,
        UPPER(`d`.`nome`) AS `bairro_bolsista`,
        UPPER(`fomento`.`cities`.`nome`) AS `cidade_bolsista`,
        `st`.`sigla` AS `estado_bolsista`,
        `z`.`id` AS `orientador_id`,
        `z`.`nome` AS `orientador_nome`,
        `z`.`cpf` AS `orientador_cpf`,
        `z`.`vinculo_id` AS `vinculo_id_orientador`,
        `v`.`nome` AS `vinculo_orientador`,
        `z`.`telefone` AS `telefone_orientador`,
        `z`.`telefone_contato` AS `telefone_contato_orientador`,
        `z`.`celular` AS `celular_orientador`,
        `z`.`whatsapp` AS `whatsapp_orientador`,
        `z`.`email` AS `email_orientador`,
        `z`.`email_alternativo` AS `email_alternativo_orientador`,
        `z`.`email_contato` AS `email_contato_orientador`,
        `s`.`id` AS `unidade_id_orientador`,
        `s`.`sigla` AS `unidade_sigla_orientador`,
        `c`.`id` AS `coorientador_id`,
        `c`.`nome` AS `coorientador_nome`,
        `c`.`cpf` AS `coorientador_cpf`,
        `c`.`vinculo_id` AS `vinculo_id_coorientador`,
        `n`.`nome` AS `vinculo_coorientador`,
        `c`.`telefone` AS `telefone_coorientador`,
        `c`.`telefone_contato` AS `telefone_contato_coorientador`,
        `c`.`celular` AS `celular_coorientador`,
        `c`.`whatsapp` AS `whatsapp_coorientador`,
        `c`.`email` AS `email_coorientador`,
        `c`.`email_alternativo` AS `email_alternativo_coorientador`,
        `c`.`email_contato` AS `email_contato_coorientador`,
        `w`.`id` AS `unidade_id_coorientador`,
        `w`.`sigla` AS `unidade_sigla_coorientador`,
        `p`.`projeto_id` AS `projeto_id`,
        `j`.`titulo` AS `projeto_titulo`,
        `a`.`nome` AS `area`,
        `g`.`nome` AS `grande_area`,
        `l`.`nome` AS `linha_fiocruz`,
        `af`.`nome` AS `area_fiocruz`,
        `p`.`sp_titulo` AS `subprojeto_titulo`,
        `p`.`fase_id` AS `fase_id`,
        `fs`.`nome` AS `fase_nome`,
        `fs`.`bloco` AS `bloco`,
        `p`.`resultado` AS `resultado`,
        `p`.`vigente` AS `vigente`,
        `p`.`origem` AS `origem`,
        `p`.`justificativa_cancelamento` AS `justificativa_cancelamento`,
        `p`.`programa_id` AS `programa_id`,
        `prog`.`nome` AS `programa_nome`,
        `p`.`cota` AS `cota`,
        `p`.`editai_id` AS `editai_id`,
        `e`.`nome` AS `editai_nome`,
        `e`.`inicio_inscricao` AS `inicio_inscricao`,
        `e`.`p_nova` AS `fim_inscricao`,
        `e`.`inicio_vigencia` AS `inicio_vigencia`,
        `e`.`fim_vigencia` AS `fim_vigencia`,
        `p`.`filhos_menor` AS `filhos_menor`,
        `z`.`sexo` AS `sexo_orientador`
    FROM
        ((((((((((((((((((`fomento`.`projeto_bolsistas` `p`
        LEFT JOIN `fomento`.`usuarios` `u` ON ((`u`.`id` = `p`.`usuario_id`)))
        LEFT JOIN `fomento`.`streets` ON ((`u`.`street_id` = `fomento`.`streets`.`id`)))
        LEFT JOIN `fomento`.`districts` `d` ON ((`d`.`id` = `fomento`.`streets`.`district_id`)))
        LEFT JOIN `fomento`.`cities` ON ((`fomento`.`cities`.`id` = `d`.`city_id`)))
        LEFT JOIN `fomento`.`states` `st` ON ((`st`.`id` = `fomento`.`cities`.`state_id`)))
        LEFT JOIN `fomento`.`usuarios` `z` ON ((`z`.`id` = `p`.`orientador`)))
        LEFT JOIN `fomento`.`vinculos` `v` ON ((`v`.`id` = `z`.`vinculo_id`)))
        LEFT JOIN `fomento`.`unidades` `s` ON ((`s`.`id` = `z`.`unidade_id`)))
        LEFT JOIN `fomento`.`usuarios` `c` ON ((`c`.`id` = `p`.`coorientador`)))
        LEFT JOIN `fomento`.`vinculos` `n` ON ((`n`.`id` = `c`.`vinculo_id`)))
        LEFT JOIN `fomento`.`unidades` `w` ON ((`w`.`id` = `c`.`unidade_id`)))
        LEFT JOIN `fomento`.`editais` `e` ON ((`e`.`id` = `p`.`editai_id`)))
        LEFT JOIN `fomento`.`projetos` `j` ON ((`j`.`id` = `p`.`projeto_id`)))
        LEFT JOIN `fomento`.`areas` `a` ON ((`a`.`id` = `j`.`area_id`)))
        LEFT JOIN `fomento`.`grandes_areas` `g` ON ((`g`.`id` = `a`.`grandes_area_id`)))
        LEFT JOIN `fomento`.`linhas` `l` ON ((`l`.`id` = `j`.`linha_id`)))
        LEFT JOIN `fomento`.`areas_fiocruz` `af` ON ((`af`.`id` = `l`.`areas_fiocruz_id`)))
        LEFT JOIN `fomento`.`fases` `fs` ON ((`fs`.`id` = `p`.`fase_id`)))
        LEFT JOIN `fomento`.`programas` `prog` ON ((`prog`.`id` = `p`.`programa_id`)))
    WHERE
        (`p`.`deleted` = 0) 
    UNION ALL SELECT 
        `p`.`id` AS `id`,
        `p`.`bolsista` AS `bolsista`,
        `u`.`nome` AS `bolsista_nome`,
        `u`.`cpf` AS `cpf_bolsista`,
        `u`.`documento` AS `documento_bolsista`,
        `u`.`documento_numero` AS `documento_numero_bolsista`,
        `u`.`documento_emissor` AS `documento_emissor_bolsista`,
        `u`.`telefone` AS `telefone_bolsista`,
        `u`.`telefone_contato` AS `telefone_contato_bolsista`,
        `u`.`celular` AS `celular_bolsista`,
        `u`.`whatsapp` AS `whatsapp_bolsista`,
        `u`.`email` AS `email_bolsista`,
        `u`.`email_alternativo` AS `email_alternativo_bolsista`,
        `u`.`email_contato` AS `email_contato_bolsista`,
        `u`.`sexo` AS `sexo_bolsista`,
        `u`.`deficiencia` AS `deficiencia_bolsista`,
        `u`.`raca` AS `raca_bolsista`,
        `fomento`.`streets`.`cep` AS `cep_bolsista`,
        UPPER(`fomento`.`streets`.`nome`) AS `rua_bolsista`,
        `u`.`complemento` AS `complemento_bolsista`,
        UPPER(`d`.`nome`) AS `bairro_bolsista`,
        UPPER(`fomento`.`cities`.`nome`) AS `cidade_bolsista`,
        `st`.`sigla` AS `estado_bolsista`,
        `z`.`id` AS `orientador_id`,
        `z`.`nome` AS `orientador_nome`,
        `z`.`cpf` AS `orientador_cpf`,
        `z`.`vinculo_id` AS `vinculo_id_orientador`,
        `v`.`nome` AS `vinculo_orientador`,
        `z`.`telefone` AS `telefone_orientador`,
        `z`.`telefone_contato` AS `telefone_contato_orientador`,
        `z`.`celular` AS `celular_orientador`,
        `z`.`whatsapp` AS `whatsapp_orientador`,
        `z`.`email` AS `email_orientador`,
        `z`.`email_alternativo` AS `email_alternativo_orientador`,
        `z`.`email_contato` AS `email_contato_orientador`,
        `s`.`id` AS `unidade_id_orientador`,
        `s`.`sigla` AS `unidade_sigla_orientador`,
        NULL AS `coorientador_id`,
        NULL AS `coorientador_nome`,
        NULL AS `coorientador_cpf`,
        NULL AS `vinculo_id_coorientador`,
        NULL AS `vinculo_coorientador`,
        NULL AS `telefone_coorientador`,
        NULL AS `telefone_contato_coorientador`,
        NULL AS `celular_coorientador`,
        NULL AS `whatsapp_coorientador`,
        NULL AS `email_coorientador`,
        NULL AS `email_alternativo_coorientador`,
        NULL AS `email_contato_coorientador`,
        NULL AS `unidade_id_coorientador`,
        NULL AS `unidade_sigla_coorientador`,
        `p`.`projeto_id` AS `projeto_id`,
        `j`.`titulo` AS `projeto_titulo`,
        NULL AS `area`,
        `g`.`nome` AS `grande_area`,
        NULL AS `linha_fiocruz`,
        NULL AS `area_fiocruz`,
        NULL AS `subprojeto_titulo`,
        `p`.`fase_id` AS `fase_id`,
        `fs`.`nome` AS `fase_nome`,
        `fs`.`bloco` AS `bloco`,
        `p`.`resultado` AS `resultado`,
        `p`.`vigente` AS `vigente`,
        `p`.`origem` AS `origem`,
        `p`.`justificativa_cancelamento` AS `justificativa_cancelamento`,
        `p`.`programa_id` AS `programa_id`,
        `prog`.`nome` AS `programa_nome`,
        `p`.`cota` AS `cota`,
        `p`.`edital_id` AS `editai_id`,
        `e`.`nome` AS `editai_nome`,
        `e`.`inicio_inscricao` AS `inicio_inscricao`,
        `e`.`p_nova` AS `fim_inscricao`,
        `e`.`inicio_vigencia` AS `inicio_vigencia`,
        `e`.`fim_vigencia` AS `fim_vigencia`,
        NULL AS `filhos_menor`,
        `z`.`sexo` AS `sexo_orientador`
    FROM
        (((((((((((( `fomento`.`pdj_inscricoes` `p`
        LEFT JOIN `fomento`.`usuarios` `u` ON ((`u`.`id` = `p`.`bolsista`)))
        LEFT JOIN `fomento`.`streets` ON ((`u`.`street_id` = `fomento`.`streets`.`id`)))
        LEFT JOIN `fomento`.`districts` `d` ON ((`d`.`id` = `fomento`.`streets`.`district_id`)))
        LEFT JOIN `fomento`.`cities` ON ((`fomento`.`cities`.`id` = `d`.`city_id`)))
        LEFT JOIN `fomento`.`states` `st` ON ((`st`.`id` = `fomento`.`cities`.`state_id`)))
        LEFT JOIN `fomento`.`usuarios` `z` ON ((`z`.`id` = `p`.`usuario_id`)))
        LEFT JOIN `fomento`.`vinculos` `v` ON ((`v`.`id` = `z`.`vinculo_id`)))
        LEFT JOIN `fomento`.`unidades` `s` ON ((`s`.`id` = `z`.`unidade_id`)))
        LEFT JOIN `fomento`.`editais` `e` ON ((`e`.`id` = `p`.`edital_id`)))
        LEFT JOIN `fomento`.`projetos` `j` ON ((`j`.`id` = `p`.`projeto_id`)))
        LEFT JOIN `fomento`.`grandes_areas` `g` ON ((`g`.`id` = `p`.`area_id`)))
        LEFT JOIN `fomento`.`fases` `fs` ON ((`fs`.`id` = `p`.`fase_id`)))
        LEFT JOIN `fomento`.`programas` `prog` ON ((`prog`.`id` = `p`.`programa_id`)))
    WHERE
        (`p`.`deleted` IS NULL);

-- criar tabela editais_prazos otimizada para consultas comuns
CREATE TABLE `editais_prazos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `editai_id` INT NULL,
  `deleted` TIMESTAMP NULL,
  `created` TIMESTAMP NULL,
  `modified` TIMESTAMP NULL,
  `tipo` ENUM('I', 'A', 'S') NULL,
  `usuario_id` INT NULL,
  `inscricao` INT NULL,
  `inicio` TIMESTAMP NULL,
  `fim` TIMESTAMP NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `editais_prazos` 
CHANGE COLUMN `tipo` `editais_wk_id` INT NULL DEFAULT NULL ;

ALTER TABLE `editais_prazos` 
ADD COLUMN `tabela` VARCHAR(45) NULL AFTER `fim`;


-- crono editais
CREATE TABLE `editais_wks` (
  `int` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(45) NULL,
  PRIMARY KEY (`int`));

INSERT INTO `editais_wks` (`nome`) VALUES ('Inscrição');
INSERT INTO `editais_wks` (`nome`) VALUES ('Resultado');
INSERT INTO `editais_wks` (`nome`) VALUES ('Avaliação');
INSERT INTO `editais_wks` (`nome`) VALUES ('Substituição');


-- editais
ALTER TABLE `editais` 
ADD COLUMN `link` VARCHAR(45) NULL AFTER `programa_id`;

ALTER TABLE `editais` 
ADD COLUMN `cpf_permitidos` TEXT NULL AFTER `link`;


-- bloco das sumulas
CREATE TABLE `editais_sumulas_blocos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(45) NULL,
  `deleted` TIMESTAMP NULL,
  `created` TIMESTAMP NULL,
  `modified` TIMESTAMP NULL,
  PRIMARY KEY (`id`));

-- sumulas
CREATE TABLE `editais_sumulas` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `created` TIMESTAMP NULL,
  `modified` TIMESTAMP NULL,
  `deleted` TIMESTAMP NULL,
  `editai_id` INT NULL,
  `sumula` TEXT NULL,
  `parametro` TEXT NULL,
  `editais_sumula_bloco_id` INT NULL,
  PRIMARY KEY (`id`));

  -- projeto bolsistas (programa)
  ALTER TABLE `projeto_bolsistas` 
ADD COLUMN `programa_id` INT NULL AFTER `fase_id`;

    UPDATE projeto_bolsistas pb
JOIN programas p
  ON pb.programa COLLATE utf8mb4_unicode_ci = p.letra COLLATE utf8mb4_unicode_ci
SET pb.programa_id = p.id where pb.id>0


-- pdj (programa)
ALTER TABLE `pdj_inscricoes` 
ADD COLUMN `programa_id` INT NULL AFTER `coorientador`;

UPDATE pdj_inscricoes pb
SET pb.programa_id = 1 where pb.id>0

-- situacao historicos
ALTER TABLE `situacao_historicos` 
ADD COLUMN `fase_original` INT NULL AFTER `justificativa`,
ADD COLUMN `fase_atual` INT NULL AFTER `fase_original`,
ADD COLUMN `editai_id` INT NULL AFTER `fase_atual`;

ALTER TABLE .`situacao_historicos` 
DROP COLUMN `editai_id`;

 UPDATE situacao_historicos pb
JOIN fases p
  ON pb.situacao_original = p.letra 
SET pb.fase_original = p.id where pb.id>0

 UPDATE situacao_historicos pb
JOIN fases p
  ON pb.situacao_atual = p.letra 
SET pb.fase_atual = p.id where pb.id>0

-- tipo anexos
ALTER TABLE `tipo_anexos` 
ADD COLUMN `bloco` VARCHAR(45) NULL COMMENT 'bolsiata, coorientador, projeto, subprojeto, inscrição' AFTER `nome`, RENAME TO  `anexos_tipos` ;

ALTER TABLE `anexos_tipos` 
CHANGE COLUMN `bloco` `bloco` ENUM('B', 'C', 'P', 'S', 'I') NULL DEFAULT NULL COMMENT 'bolsiata, coorientador, projeto, subprojeto, inscrição' ;

ALTER TABLE `anexos_tipos` 
ADD COLUMN `deleted` TINYINT NULL DEFAULT 0 AFTER `bloco`;

UPDATE `anexos_tipos` SET `deleted` = '1' WHERE (`id` = '21');


UPDATE `anexos_tipos` SET `bloco` = 'P' WHERE (`id` = '1');
UPDATE `anexos_tipos` SET `bloco` = 'P' WHERE (`id` = '2');
UPDATE `anexos_tipos` SET `bloco` = 'P' WHERE (`id` = '3');
UPDATE `anexos_tipos` SET `bloco` = 'P' WHERE (`id` = '4');
UPDATE `anexos_tipos` SET `bloco` = 'P' WHERE (`id` = '5');
UPDATE `anexos_tipos` SET `bloco` = 'P' WHERE (`id` = '6');
UPDATE `anexos_tipos` SET `bloco` = 'P' WHERE (`id` = '7');
UPDATE `anexos_tipos` SET `bloco` = 'P' WHERE (`id` = '8');
UPDATE `anexos_tipos` SET `bloco` = 'I' WHERE (`id` = '9');
UPDATE `anexos_tipos` SET `bloco` = 'B' WHERE (`id` = '10');
UPDATE `anexos_tipos` SET `bloco` = 'I' WHERE (`id` = '11');
UPDATE `anexos_tipos` SET `bloco` = 'I' WHERE (`id` = '12');
UPDATE `anexos_tipos` SET `bloco` = 'I' WHERE (`id` = '13');
UPDATE `anexos_tipos` SET `bloco` = 'I' WHERE (`id` = '14');
UPDATE `anexos_tipos` SET `bloco` = 'B' WHERE (`id` = '15');
UPDATE `anexos_tipos` SET `bloco` = 'B' WHERE (`id` = '16');
UPDATE `anexos_tipos` SET `bloco` = 'B' WHERE (`id` = '17');
UPDATE `anexos_tipos` SET `bloco` = 'B' WHERE (`id` = '18');
UPDATE `anexos_tipos` SET `bloco` = 'B' WHERE (`id` = '19');
UPDATE `anexos_tipos` SET `bloco` = 'S' WHERE (`id` = '20');
UPDATE `anexos_tipos` SET `bloco` = 'B' WHERE (`id` = '21');

-- anexos
ALTER TABLE `projeto_anexos` 
ADD COLUMN `pdj_inscricoe_id` INT NULL AFTER `raic_id`,
CHANGE COLUMN `tipo_anexo_id` `anexos_tipo_id` INT UNSIGNED NOT NULL , RENAME TO  `anexos` ;

ALTER TABLE `anexos` 
ADD COLUMN `bloco` ENUM('B', 'C', 'P', 'S', 'I') NULL AFTER `pdj_inscricoe_id`;

UPDATE anexos pb
JOIN anexos_tipos p
  ON pb.anexos_tipo_id = p.id 
SET pb.bloco = p.bloco where pb.id>0

INSERT INTO `anexos_tipos` (`nome`, `bloco`) VALUES ('Termo consentimento - Bolsista', 'B');
INSERT INTO `anexos_tipos` (`nome`, `bloco`) VALUES ('Termo consentimento - Coorientador', 'C');
INSERT INTO `anexos_tipos` (`nome`, `bloco`) VALUES ('Termo da Inscrição', 'I');

-- alteração nos tipos de anexo
ALTER TABLE `anexos_tipos` 
ADD COLUMN `condicional` TINYINT NULL AFTER `deleted`,
ADD COLUMN `programa` VARCHAR(45) NULL AFTER `condicional`,
ADD COLUMN `cota` VARCHAR(45) NULL AFTER `programa`;

update anexos_tipos set condicional=0 where id>0;

-- completando a tabela
UPDATE `anexos_tipos` SET `programa` = '1' WHERE (`id` = '9');
UPDATE `anexos_tipos` SET `programa` = '1' WHERE (`id` = '10');
UPDATE `anexos_tipos` SET `nome` = 'Certidão de nascimento (Bolsista PDJ)', `bloco` = 'B', `programa` = '1' WHERE (`id` = '11');
UPDATE `anexos_tipos` SET `programa` = '1' WHERE (`id` = '12');
UPDATE `anexos_tipos` SET `condicional` = '1' WHERE (`id` = '14');
UPDATE `anexos_tipos` SET `condicional` = '1' WHERE (`id` = '16');
UPDATE `anexos_tipos` SET `cota` = 'D' WHERE (`id` = '17');
UPDATE `anexos_tipos` SET `condicional` = '1' WHERE (`id` = '18');
UPDATE `anexos_tipos` SET `condicional` = '1' WHERE (`id` = '19');
INSERT INTO `anexos_tipos` (`nome`, `bloco`, `deleted`, `condicional`, `programa`) VALUES ('Comprovante de residência', 'B', '0', '0', '4, 5, 6');
INSERT INTO `anexos_tipos` (`nome`, `bloco`, `deleted`, `condicional`, `cota`) VALUES ('Documento Infigena', 'B', '0', '0', 'I');


-- primeiro periodo em proj bolsista
ALTER TABLE `projeto_bolsistas` ADD COLUMN `primeiro_periodo` TINYINT NULL AFTER `programa_id`;


-- gravar a tela sumula
CREATE TABLE `inscricao_sumulas` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `created` TIMESTAMP NULL,
  `modified` TIMESTAMP NULL,
  `editais_sumula_id` INT NULL,
  `editai_id` INT NULL,
  `editais_sumula_bloco_id` INT NULL,
  `projeto_bolsista_id` INT NULL,
  `pdj_inscricoe_id` INT NULL,
  `quantidade` INT NULL,
  PRIMARY KEY (`id`));


-- editais modelos de arquivo
ALTER TABLE `editais` 
ADD COLUMN `modelo_cons_bols` VARCHAR(45) NULL AFTER `cpf_permitidos`,
ADD COLUMN `modelo_cons_coor` VARCHAR(45) NULL AFTER `modelo_cons_bols`,
ADD COLUMN `modelo_relat_bols` VARCHAR(45) NULL AFTER `modelo_cons_coor`;


-- atualizando gases
UPDATE `fases` SET `deleted` = '1' WHERE (`id` = '1');
UPDATE `fases` SET `deleted` = '1' WHERE (`id` = '2');
UPDATE `fases` SET `deleted` = '1' WHERE (`id` = '3');
UPDATE `fases` SET `deleted` = '1' WHERE (`id` = '4');
UPDATE `fases` SET `nome` = 'Rascunho', `deleted` = '0' WHERE (`id` = '3');
UPDATE `fases` SET `nome` = 'Finalizada', `deleted` = '0' WHERE (`id` = '4');


-- alteração em editais
ALTER TABLE `editais` 
ADD COLUMN `controller` VARCHAR(45) NULL AFTER `modelo_relat_bols`;


-- view dasdetalhes

CREATE  OR REPLACE 
VIEW `dashdetalhes` AS
    SELECT 
        `pb`.`id` AS `id`,
        `pb`.`bolsista` AS `bolsista`,
        `b`.`nome` AS `nome_bolsista`,
        `pb`.`orientador` AS `orientador`,
        `o`.`nome` AS `nome_orientador`,
        `pb`.`coorientador` AS `coorientador`,
        `c`.`nome` AS `nome_coorientador`,
        `pb`.`projeto_id` AS `projeto_id`,
        `pb`.`fase_id` AS `fase_id`,
        `f`.`nome` AS `nome_fase`,
        `f`.`bloco` AS `bloco`,
        `pb`.`vigente` AS `vigente`,
        `pb`.`editai_id` AS `editai_id`,
        `e`.`nome` AS `nome_edital`,
        e.controller as controller,
        `p`.`sigla` AS `nome_programa`,
        `pb`.`data_inicio` AS `data_inicio`,
        `pb`.`data_fim` AS `data_fim`,
        `e`.`fim_vigencia` AS `fim_vigencia`,

        (CASE
            WHEN (`pb`.`deleted` = 0) THEN 1
            ELSE 0
        END) AS `ativo`
    FROM
        ((((((`projeto_bolsistas` `pb`
        LEFT JOIN `usuarios` `b` ON ((`b`.`id` = `pb`.`bolsista`)))
        LEFT JOIN `usuarios` `o` ON ((`o`.`id` = `pb`.`orientador`)))
        LEFT JOIN `usuarios` `c` ON ((`c`.`id` = `pb`.`coorientador`)))
        LEFT JOIN `fases` `f` ON ((`f`.`id` = `pb`.`fase_id`)))
        LEFT JOIN `editais` `e` ON ((`e`.`id` = `pb`.`editai_id`)))
        LEFT JOIN `programas` `p` ON ((`p`.`id` = `e`.`programa_id`))) 
    UNION ALL SELECT 
        `pdj`.`id` AS `id`,
        `pdj`.`bolsista` AS `bolsista`,
        `b`.`nome` AS `nome_bolsista`,
        `pdj`.`orientador` AS `orientador`,
        `o`.`nome` AS `nome_orientador`,
        `pdj`.`coorientador` AS `coorientador`,
        `c`.`nome` AS `nome_coorientador`,
        `pdj`.`projeto_id` AS `projeto_id`,
        `pdj`.`fase_id` AS `fase_id`,
        `f`.`nome` AS `nome_fase`,
        `f`.`bloco` AS `bloco`,
        `pdj`.`vigente` AS `vigente`,
        `pdj`.`editai_id` AS `editai_id`,
        `e`.`nome` AS `nome_edital`,
        e.controller as controller,
        `p`.`sigla` AS `nome_programa`,
        `pdj`.`data_inicio` AS `data_inicio`,
        `pdj`.`data_fim` AS `data_fim`,
        `e`.`fim_vigencia` AS `fim_vigencia`,
        (CASE
            WHEN (`pdj`.`deleted` IS NULL) THEN 1
            ELSE 0
        END) AS `ativo`
    FROM
        ((((((`pdj_inscricoes` `pdj`
        LEFT JOIN `usuarios` `b` ON ((`b`.`id` = `pdj`.`bolsista`)))
        LEFT JOIN `usuarios` `o` ON ((`o`.`id` = `pdj`.`orientador`)))
        LEFT JOIN `usuarios` `c` ON ((`c`.`id` = `pdj`.`coorientador`)))
        LEFT JOIN `fases` `f` ON ((`f`.`id` = `pdj`.`fase_id`)))
        LEFT JOIN `editais` `e` ON ((`e`.`id` = `pdj`.`editai_id`)))
        LEFT JOIN `programas` `p` ON ((`p`.`id` = `e`.`programa_id`)));


-- atualização de tipos anexos
UPDATE `anexos_tipos` SET `nome` = 'Relatório Parcial', `bloco` = 'B', `condicional` = '1' WHERE (`id` = '13');
UPDATE `anexos_tipos` SET `bloco` = 'B' WHERE (`id` = '12');
UPDATE `anexos_tipos` SET `bloco` = 'B' WHERE (`id` = '14');


-- dash detalhes

CREATE  OR REPLACE 
    
VIEW `dashdetalhes` AS
    SELECT 
        `pb`.`id` AS `id`,
        `pb`.`bolsista` AS `bolsista`,
        `b`.`nome` AS `nome_bolsista`,
        `pb`.`orientador` AS `orientador`,
        `o`.`nome` AS `nome_orientador`,
        `pb`.`coorientador` AS `coorientador`,
        `c`.`nome` AS `nome_coorientador`,
        `pb`.`projeto_id` AS `projeto_id`,
        `pb`.`fase_id` AS `fase_id`,
        `f`.`nome` AS `nome_fase`,
        `f`.`bloco` AS `bloco`,
        `pb`.`vigente` AS `vigente`,
        `pb`.`editai_id` AS `editai_id`,
        `e`.`nome` AS `nome_edital`,
        `e`.`controller` AS `controller`,
        `p`.`sigla` AS `nome_programa`,
        `pb`.`data_inicio` AS `data_inicio`,
        `pb`.`data_fim` AS `data_fim`,
        `pb`.`programa_id` AS `programa_id`,
        `e`.`fim_vigencia` AS `fim_vigencia`,
        (CASE
            WHEN (`pb`.`deleted` = 0) THEN 1
            ELSE 0
        END) AS `ativo`
    FROM
        ((((((`projeto_bolsistas` `pb`
        LEFT JOIN `usuarios` `b` ON ((`b`.`id` = `pb`.`bolsista`)))
        LEFT JOIN `usuarios` `o` ON ((`o`.`id` = `pb`.`orientador`)))
        LEFT JOIN `usuarios` `c` ON ((`c`.`id` = `pb`.`coorientador`)))
        LEFT JOIN `fases` `f` ON ((`f`.`id` = `pb`.`fase_id`)))
        LEFT JOIN `editais` `e` ON ((`e`.`id` = `pb`.`editai_id`)))
        LEFT JOIN `programas` `p` ON ((`p`.`id` = `e`.`programa_id`))) 
    UNION ALL SELECT 
        `pdj`.`id` AS `id`,
        `pdj`.`bolsista` AS `bolsista`,
        `b`.`nome` AS `nome_bolsista`,
        `pdj`.`orientador` AS `orientador`,
        `o`.`nome` AS `nome_orientador`,
        `pdj`.`coorientador` AS `coorientador`,
        `c`.`nome` AS `nome_coorientador`,
        `pdj`.`projeto_id` AS `projeto_id`,
        `pdj`.`fase_id` AS `fase_id`,
        `f`.`nome` AS `nome_fase`,
        `f`.`bloco` AS `bloco`,
        `pdj`.`vigente` AS `vigente`,
        `pdj`.`editai_id` AS `editai_id`,
        `e`.`nome` AS `nome_edital`,
        `e`.`controller` AS `controller`,
        `p`.`sigla` AS `nome_programa`,
        `pdj`.`data_inicio` AS `data_inicio`,
        `pdj`.`data_fim` AS `data_fim`,
		`pdj`.`programa_id` AS `programa_id`,

        `e`.`fim_vigencia` AS `fim_vigencia`,
        (CASE
            WHEN (`pdj`.`deleted` IS NULL) THEN 1
            ELSE 0
        END) AS `ativo`
    FROM
        ((((((`pdj_inscricoes` `pdj`
        LEFT JOIN `usuarios` `b` ON ((`b`.`id` = `pdj`.`bolsista`)))
        LEFT JOIN `usuarios` `o` ON ((`o`.`id` = `pdj`.`orientador`)))
        LEFT JOIN `usuarios` `c` ON ((`c`.`id` = `pdj`.`coorientador`)))
        LEFT JOIN `fases` `f` ON ((`f`.`id` = `pdj`.`fase_id`)))
        LEFT JOIN `editais` `e` ON ((`e`.`id` = `pdj`.`editai_id`)))
        LEFT JOIN `programas` `p` ON ((`p`.`id` = `e`.`programa_id`)));

-- ///////////////////////////////////////////
--///////////////////////////////////////////
-- aqui
-- ajustes na pb
ALTER TABLE `projeto_bolsistas` 
ADD COLUMN `troca_projeto` TINYINT NULL DEFAULT 0 AFTER `primeiro_periodo`,
ADD COLUMN `heranca` TINYINT NULL DEFAULT 0 AFTER `troca_projeto`;

ALTER TABLE `projeto_bolsistas` 
ADD COLUMN `pontos_bolsista` DOUBLE(6,2) NULL AFTER `heranca`,
ADD COLUMN `area_pdj` INT NULL AFTER `pontos_bolsista`;

ALTER TABLE `projeto_bolsistas` 
CHANGE COLUMN `deleted` `deleted_2` INT NOT NULL DEFAULT '0' ;

ALTER TABLE `projeto_bolsistas` 
ADD COLUMN `deleted` TIMESTAMP NULL AFTER `area_pdj`;

ALTER TABLE `projeto_bolsistas` 
ADD COLUMN `matriz` INT NULL AFTER `deleted`;

-- atualizando o campo matriz
--///////////////////////////////////
select bolsista, orientador, projeto_id, min(id) 
from projeto_bolsistas group by bolsista, orientador, projeto_id;

UPDATE projeto_bolsistas pb
JOIN (
  SELECT bolsista, orientador, projeto_id, MIN(id) AS matriz_id
  FROM projeto_bolsistas
  GROUP BY bolsista, orientador, projeto_id
) m
  ON m.bolsista   = pb.bolsista
 AND m.orientador = pb.orientador
 AND m.projeto_id = pb.projeto_id
SET pb.matriz = m.matriz_id where pb.id>0;

select id, orientador, bolsista, projeto_id, created from projeto_bolsistas where matriz is null
-- //////////////////////////////////////////////

ALTER TABLE `fomento2026`.`projeto_bolsistas` 
ADD COLUMN `pdj_inscricoe_id` INT NULL AFTER `matriz`;


-- ajustes nas views após atualização dos PDJ

CREATE  OR REPLACE    
VIEW `dashyodacounts` AS
    SELECT 
        COUNT(`pb`.`id`) AS `qtd`,
        `pb`.`fase_id` AS `fase_id`,
        `f`.`bloco` AS `bloco`,
        `pb`.`vigente` AS `vigente`,
        `e`.`programa_id` AS `programa_id`,
        `pb`.`editai_id` AS `editai_id`
    FROM
        ((`projeto_bolsistas` `pb`
        LEFT JOIN `fases` `f` ON ((`f`.`id` = `pb`.`fase_id`)))
        LEFT JOIN `editais` `e` ON ((`e`.`id` = `pb`.`editai_id`)))
    WHERE
        ((`pb`.`deleted` is null)
            AND (`e`.`fim_vigencia` > NOW()))
    GROUP BY `pb`.`fase_id` , `f`.`bloco` , `pb`.`vigente` , `e`.`programa_id` , `pb`.`editai_id`;


CREATE  OR REPLACE    
VIEW `dashdetalhes` AS
    SELECT 
        `pb`.`id` AS `id`,
        `pb`.`bolsista` AS `bolsista`,
        `b`.`nome` AS `nome_bolsista`,
        `pb`.`orientador` AS `orientador`,
        `o`.`nome` AS `nome_orientador`,
        `pb`.`coorientador` AS `coorientador`,
        `c`.`nome` AS `nome_coorientador`,
        `pb`.`projeto_id` AS `projeto_id`,
        `pb`.`fase_id` AS `fase_id`,
        `f`.`nome` AS `nome_fase`,
        `f`.`bloco` AS `bloco`,
        `pb`.`vigente` AS `vigente`,
        `pb`.`editai_id` AS `editai_id`,
        `e`.`nome` AS `nome_edital`,
        `e`.`controller` AS `controller`,
        `p`.`sigla` AS `nome_programa`,
        `pb`.`data_inicio` AS `data_inicio`,
        `pb`.`data_fim` AS `data_fim`,
        `pb`.`programa_id` AS `programa_id`,
        `e`.`fim_vigencia` AS `fim_vigencia`,
        (CASE
            WHEN (`pb`.`deleted` is null) THEN 1
            ELSE 0
        END) AS `ativo`
    FROM
        ((((((`projeto_bolsistas` `pb`
        LEFT JOIN `usuarios` `b` ON ((`b`.`id` = `pb`.`bolsista`)))
        LEFT JOIN `usuarios` `o` ON ((`o`.`id` = `pb`.`orientador`)))
        LEFT JOIN `usuarios` `c` ON ((`c`.`id` = `pb`.`coorientador`)))
        LEFT JOIN `fases` `f` ON ((`f`.`id` = `pb`.`fase_id`)))
        LEFT JOIN `editais` `e` ON ((`e`.`id` = `pb`.`editai_id`)))
        LEFT JOIN `programas` `p` ON ((`p`.`id` = `e`.`programa_id`)));


CREATE  OR REPLACE 
VIEW `dashcounts` AS
    SELECT 
        COUNT(`pb`.`id`) AS `qtd`,
        `pb`.`bolsista` AS `bolsista`,
        `pb`.`orientador` AS `orientador`,
        `pb`.`coorientador` AS `coorientador`,
        `pb`.`fase_id` AS `fase_id`,
        `f`.`bloco` AS `bloco`,
        `pb`.`vigente` AS `vigente`
    FROM
        ((`projeto_bolsistas` `pb`
        LEFT JOIN `fases` `f` ON ((`f`.`id` = `pb`.`fase_id`)))
        LEFT JOIN `editais` `e` ON ((`e`.`id` = `pb`.`editai_id`)))
    WHERE
        ((`pb`.`deleted` is null)
            AND (`e`.`fim_vigencia` > NOW()))
    GROUP BY `pb`.`bolsista` , `pb`.`orientador` , `pb`.`coorientador` , `pb`.`fase_id` , `f`.`bloco` , `pb`.`vigente`;
