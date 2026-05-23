Requisitos reversos - Renovação PDJ

Este documento descreve o fluxo atual da renovação PDJ, com regras de negócio, campos, validações, anexos e transições usadas pelo sistema.

================================================================================

1. Entrada no fluxo de renovação

O orientador inicia a renovação a partir de um edital disponível. O sistema procura inscrições vigentes do mesmo orientador e programa para usar como referência da renovação.

Regras de acesso:

- O usuário precisa estar logado.
- Fora do perfil técnico, o edital precisa estar no período de inscrição.
- Fora do perfil técnico, o orientador precisa ter escolaridade de doutorado.
- O edital pode restringir unidades, vínculos e CPFs permitidos.
- Somente o orientador dono da inscrição, ou perfil técnico autorizado, pode editar a renovação.
- A renovação só é editável nas fases `1` e `3`.

Regras de elegibilidade:

- O orientador não pode possuir inscrição de bolsa nova em andamento no mesmo programa (`origem = N`, `fase_id < 10`).
- A inscrição de referência precisa ser do mesmo orientador e programa.
- A inscrição de referência precisa estar vigente (`vigente = 1`) e na fase `11`.
- A inscrição de referência não pode estar deletada.
- Se o CPF do bolsista estiver na lista `cpf_invalidos` do edital, a renovação não é permitida.

Seleção da referência:

- Se houver uma única inscrição vigente elegível, o sistema cria ou carrega automaticamente a renovação.
- Se houver mais de uma inscrição vigente elegível, o sistema exibe a tela de seleção.
- Se não houver inscrição elegível, o sistema informa que não há inscrição apta para renovar.

================================================================================

2. Criação ou carregamento da renovação

Ao selecionar uma inscrição vigente, o sistema procura renovação já criada para a mesma referência.

Se já existir renovação:

- O sistema redireciona para a tela de dados do bolsista da renovação.
- Se a renovação estiver nas fases `1` ou `3`, a inscrição de referência é marcada com fase `19`, caso ainda não esteja.
- O histórico registra que a inscrição de referência foi usada por uma renovação existente.

Se não existir renovação:

- O edital precisa possuir `evento`, pois o workshop da renovação é criado automaticamente.
- A nova renovação é criada em `projeto_bolsistas`.
- A renovação recebe `origem = R`, `fase_id = 3`, `vigente = 0`, `autorizacao = 0` e `prorrogacao = 0`.
- A renovação guarda `referencia_inscricao_anterior` apontando para a inscrição vigente usada como base.
- São copiados da referência: bolsista, projeto, cota, matriz e área PDJ.
- A inscrição de referência é marcada com fase `19`.
- O histórico registra a criação da renovação e a marcação da referência.

Criação automática do workshop:

- É criado um registro em `workshops` com `tipo_bolsa = R`.
- O workshop fica vinculado ao bolsista, orientador, projeto, edital, evento e `projeto_bolsista_id` da renovação.
- O histórico do workshop registra a criação automática para renovação PDJ.

Replicação de anexos:

- Anexos ativos do bloco `P` vinculados ao projeto da referência são copiados para a nova renovação.
- Para cada tipo de anexo do bloco `P`, é copiado o anexo ativo mais recente.
- Os anexos tipos `10` e `21` ativos da inscrição de referência também são replicados para a renovação.

================================================================================

3. Dados do bolsista

Nesta etapa o orientador confere o bolsista vindo da inscrição de referência e atualiza os anexos do bloco `B`.

Regras do bolsista:

- Na renovação não é permitido incluir, alterar ou excluir o bolsista.
- O bolsista é herdado da inscrição de referência.
- A cota é herdada da referência e validada contra as cotas permitidas do edital.
- Para programa `9`, a cota permitida é apenas `I` (Pessoas Indígenas).
- Para os demais programas, as cotas aceitas são `G`, `I`, `N`, `T` e `D`.
- O campo `primeiro_periodo` pode ser salvo como `0` ou `1`.

Validações do bolsista na geração do termo:

- O bolsista precisa estar informado.
- A escolaridade do bolsista deve estar nas escolaridades permitidas do edital, quando o edital definir essa lista.
- O ano de conclusão do doutorado do bolsista deve estar preenchido e entre 1900 e o ano atual.
- O CPF do bolsista não pode estar em `cpf_invalidos` do edital.
- A cota do bolsista é obrigatória.

Regras de anexos do bloco B:

- Anexos com `condicional = 0`, sem regra de programa e sem regra de cota, são obrigatórios gerais.
- Anexos com regra de programa são obrigatórios quando o programa do edital estiver na lista do anexo.
- Anexos com regra de cota são obrigatórios quando a cota da renovação estiver na lista do anexo.
- Anexos condicionais com regra de cota também podem ser obrigatórios quando a regra de cota e programa corresponder.
- O anexo tipo `11` é ignorado na validação da renovação.
- O anexo tipo `12` é obrigatório quando o programa do edital é `1` e a origem da inscrição é `R`.
- O anexo tipo `16` é exibido e exigido quando `primeiro_periodo = 0`.
- Os anexos tipos `18` e `19` são exibidos como condicionais quando o bolsista é menor de idade.

Ao salvar:

- O sistema salva `primeiro_periodo`.
- O sistema processa alterações/exclusões rápidas de anexos permitidos.
- O sistema grava os uploads dos anexos informados.
- O histórico registra a atualização de dados do bolsista na renovação.
- Se o edital tiver `origem = N`, o próximo passo é a súmula da renovação.
- Caso contrário, o próximo passo é o projeto da renovação.

================================================================================

4. Súmula da renovação

A etapa de súmula só é usada quando o edital possui `origem = N`. Nos demais casos, o sistema redireciona para o projeto da renovação.

Campos da súmula:

- Quantidade para cada item de súmula do edital.
- Ano de conclusão do doutorado.
- Resposta sobre ingresso na Fiocruz por meio dos concursos de 2016 e 2024.
- Para orientadora, resposta sobre filhos menores de 5 anos.

Regras de preenchimento:

- As súmulas do edital são listadas a partir de `editais_sumulas`.
- Cada quantidade deve ser um número inteiro entre `0` e `50`.
- Campo vazio é salvo como pendência e será barrado na geração do termo.
- Quando o orientador não possuir produção em um item, deve informar `0`.
- O ano de conclusão do doutorado, quando informado, precisa estar entre 1900 e o ano atual.
- A resposta de recém-servidor aceita apenas `0` ou `1`.
- A resposta de filhos menores aceita apenas `0`, `1` ou `2`.

Regras de gravação:

- Os dados auxiliares são salvos na renovação: `filhos_menor`, `ano_doutorado` e `recem_servidor`.
- Os itens da súmula são gravados em `inscricao_sumulas`.
- O vínculo com a renovação usa `projeto_bolsista_id`.
- O campo `pdj_inscricoe_id` fica `NULL`.
- Registros de súmula que não pertencem mais aos itens ativos do edital são removidos.

Anexos da súmula:

- Anexo tipo `27` é usado quando `filhos_menor > 0`.
- Anexo tipo `29` é usado quando `recem_servidor = 1`.
- A geração do termo exige o anexo tipo `27` quando a regra de filhos menores se aplica.
- A geração do termo exige o anexo tipo `29` quando a regra de recém-servidor se aplica.

================================================================================

5. Projeto da renovação

A etapa de projeto exibe o projeto herdado da inscrição de referência. Na renovação, o projeto do orientador não é livremente alterado; apenas pendências específicas podem ser preenchidas.

Campos exibidos:

- Título do projeto.
- Área de inscrição PDJ.
- Grande área CNPq.
- Área CNPq.
- Área de pesquisa Fiocruz.
- Linha de pesquisa Fiocruz.
- Instituições financiadoras.
- Palavras-chave.
- Resumo do projeto.
- Justificativa da bolsa.

Regras de edição:

- Se a área de inscrição PDJ já estiver preenchida, ela é apenas exibida.
- Se a área de inscrição PDJ estiver vazia, o orientador pode preenchê-la.
- Se a Área CNPq do projeto estiver vazia, o orientador pode preenchê-la.
- Se a Linha Fiocruz do projeto estiver vazia, o orientador pode preenchê-la.
- Se o resumo do projeto estiver vazio, o orientador pode preenchê-lo.
- A justificativa da bolsa pode ser preenchida ou atualizada na renovação.

Validações:

- Área CNPq informada precisa existir na lista de áreas.
- Linha Fiocruz informada precisa existir na lista de linhas.
- Resumo do projeto, quando editável, deve respeitar os limites de texto definidos pelo validador padrão.
- Justificativa da bolsa deve respeitar o limite de 4000 caracteres.
- Área de inscrição PDJ precisa existir nas grandes áreas com `id > 9`.

Anexos do projeto:

- Anexos do bloco `P` são exibidos na tela.
- O anexo tipo `5` é priorizado na lista e é obrigatório para geração do termo.
- Se o anexo tipo `5` já existir, ele pode ser baixado, mas não pode ser alterado pela tela.
- Se o anexo tipo `5` não existir, o upload fica disponível; depois de inserido, não deve ser alterado.
- Outros anexos do bloco `P` podem ser atualizados ou excluídos, mas não são obrigatórios para gerar o termo.
- O anexo tipo `5` pode ser encontrado vinculado diretamente ao projeto, não apenas à renovação.

Validações do projeto na geração do termo:

- Projeto precisa estar vinculado.
- Título do projeto é obrigatório.
- Área de inscrição PDJ é obrigatória.
- Resumo do projeto é obrigatório.
- Área CNPq é obrigatória.
- Linha Fiocruz é obrigatória.
- Justificativa da bolsa é obrigatória.
- Anexo tipo `5` ativo no projeto é obrigatório.

================================================================================

6. Coorientador

A etapa de coorientador é usada quando a renovação precisa de coorientador ou quando o orientador inclui um coorientador voluntariamente.

Obrigatoriedade:

- Na geração do termo, se o vínculo do orientador não tiver `servidor = 1`, o coorientador é obrigatório.
- Se houver coorientador vinculado, todos os anexos ativos do bloco `C` são obrigatórios.

Inclusão ou alteração:

- O coorientador é informado por CPF.
- O CPF precisa ser válido.
- Se o CPF não existir na base de usuários, o sistema redireciona para cadastro de usuário.
- O coorientador não pode ser o próprio orientador.
- Se o orientador não for servidor, o coorientador precisa ter vínculo de servidor.
- O coorientador precisa ter escolaridade de doutorado.
- Não pode existir inscrição em andamento do mesmo coorientador no mesmo programa, exceto a própria renovação.
- Ao vincular ou alterar coorientador, os anexos do bloco `C` da renovação são limpos.

Exclusão:

- Ao excluir coorientador, o campo `coorientador` é limpo.
- Os anexos do bloco `C` da renovação são limpos.
- O histórico registra a exclusão.

Anexos do coorientador:

- Só é permitido anexar documentos após vincular um coorientador.
- A tela lista todos os anexos ativos do bloco `C`.
- A geração do termo exige todos os anexos do bloco `C` quando existe coorientador vinculado.

================================================================================

7. Geração do termo

A geração do termo consolida as validações de todos os blocos. A ação só é permitida nas fases `1` e `3`.

Blocos validados:

- Bolsista.
- Súmula, somente quando o edital tem `origem = N`.
- Orientador.
- Coorientador.
- Projeto.
- Anexos da súmula.
- Anexos do bolsista.
- Anexos do coorientador.
- Anexos do projeto.

Validações do orientador:

- Orientador precisa existir.
- Vínculo do orientador deve ser `1`.
- Escolaridade do orientador deve ser `10`.
- Ano de conclusão do orientador deve estar preenchido e ser menor que o ano atual.
- SIAPE deve estar preenchido.
- Laboratório deve estar preenchido.
- Departamento deve estar preenchido.

Resultado da validação:

- Se houver pendências, elas são salvas na sessão em `Inscricoes.gerar_termo_falhas.{inscricao_id}`.
- A tela de pendências agrupa os erros por bloco.
- Cada bloco possui link para a tela de correção correspondente.
- Sem pendências, a renovação passa para a fase `5`.
- O histórico registra a geração do termo.

================================================================================

8. Download do termo

Depois da geração, o termo fica disponível na etapa de finalização.

- O termo só pode ser baixado quando a renovação estiver na fase `5`.
- Somente o orientador dono da renovação ou perfil técnico autorizado pode baixar.
- O sistema monta o HTML do termo com os dados da renovação.
- O sistema tenta gerar PDF com mPDF.
- Se mPDF não estiver disponível ou falhar, o sistema baixa o termo em HTML.
- O arquivo baixado usa o prefixo `termo_inscricao_`.

================================================================================

9. Finalização

A finalização exige o envio do termo assinado.

- A renovação precisa estar na fase `5`.
- O arquivo do termo assinado é obrigatório.
- O termo assinado é anexado como tipo `24`.
- Após salvar o termo, a renovação passa para a fase `4`.
- O histórico registra a finalização com termo assinado.
- Após finalizar, o sistema redireciona para a visualização padrão da inscrição.

================================================================================

10. Fases e direcionamento

Fases usadas no fluxo:

- `3`: renovação criada e editável.
- `1`: fase editável aceita pelo fluxo compartilhado.
- `5`: termo gerado, aguardando assinatura/anexo.
- `4`: renovação finalizada com termo assinado.
- `11`: inscrição vigente usada como referência.
- `19`: inscrição de referência marcada como usada em renovação.

Direcionamento por tipo:

- Tipo `E` direciona para dados do bolsista.
- Tipo `T` direciona para geração do termo.
- Tipo `F` direciona para finalização.

Etapas principais exibidas no elemento de navegação:

- Dados do bolsista.
- Projeto.
- Gerar termo.
- Anexar o termo.

Observação:

- As telas de súmula e coorientador existem no fluxo e são acessadas por redirecionamento ou por links de pendências, mas não aparecem como etapas principais no elemento atual de navegação.
