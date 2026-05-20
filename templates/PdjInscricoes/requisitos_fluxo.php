Requisitos reversos - Inscrição PDJ

Este documento descreve o fluxo atual da inscrição PDJ, com regras de negócio, campos, validações, anexos e transições usadas pelo sistema.

================================================================================

1. Criação da inscrição

O orientador inicia uma inscrição PDJ a partir de um edital disponível. A inscrição fica vinculada ao edital, ao orientador logado e ao fluxo definido no controller do edital.

- Somente o orientador dono da inscrição, ou perfil técnico autorizado, pode editar o processo.
- A inscrição é editável nas fases permitidas pelo fluxo PDJ.
- O controller do edital define para onde o sistema direciona as ações de edição, termo e finalização.

================================================================================

2. Dados do bolsista

Nesta etapa o orientador vincula, altera ou exclui o bolsista da inscrição. O vínculo é feito pelo CPF.

Campos principais:

- CPF do bolsista.
- Cota do bolsista.
- Campo "Bolsista, possui filhos menores de 8 anos?", exibido somente se o bolsista for do sexo feminino.
- Anexos do bloco B.

Regras do vínculo:

- O CPF informado deve ser válido.
- Se o usuário já existir, o sistema tenta vinculá-lo diretamente.
- Se o usuário não existir, ele pode ser cadastrado antes do vínculo.
- Mesmo quando o usuário for recém-cadastrado, o vínculo PDJ deve passar pela validação do bolsista.
- Se o bolsista não cumprir as regras do edital, ele não deve ser vinculado à inscrição.

Validações do bolsista:

- Escolaridade do usuário deve estar dentro das escolaridades permitidas do edital, quando o edital definir essa lista.
- Ano de conclusão do bolsista deve ser menor ou igual ao ano atual.
- Na geração do termo, a cota do bolsista é obrigatória.
- Se a bolsista for do sexo feminino, o campo de filhos menores de 8 anos deve ser respondido.
- Se a resposta sobre filhos menores for maior que zero, o anexo tipo 11 é obrigatório.

Regras de anexos do bolsista:

- Anexos do bloco B com `condicional = 0`, sem regra de programa e sem regra de cota, são obrigatórios gerais.
- Anexos do bloco B com regra de programa são obrigatórios quando o programa do edital estiver na lista do anexo.
- Anexos do bloco B com regra de cota são obrigatórios quando a cota do bolsista estiver na lista do anexo.
- Anexos condicionais com regra de cota também são exigidos quando a cota do bolsista bater com a regra.
- O anexo tipo 11 é tratado separadamente: só é exigido para bolsista do sexo feminino com filhos menores de 8 anos.

Alteração ou exclusão do bolsista:

- Ao alterar ou excluir o bolsista, os dados dependentes do bolsista devem ser limpos.
- Os anexos do bloco B vinculados ao bolsista devem ser removidos do fluxo atual.
- O campo de filhos menores do bolsista deve ser limpo.

================================================================================

3. Súmula do bolsista

A súmula do bolsista é preenchida de forma independente da súmula do orientador.

Regras de gravação:

- Os registros são gravados na tabela `inscricao_sumulas`.
- O vínculo com a inscrição PDJ usa `projeto_bolsista_id`.
- Para súmula do bolsista, o campo `bolsista` em `inscricao_sumulas` deve ser gravado como `1`.
- Essa separação impede sobrescrever a súmula do orientador.

Regras de preenchimento:

- Todos os itens de súmula cadastrados para o bloco do bolsista devem ser preenchidos.
- Quando o bolsista não possuir produção em um item, deve ser informado `0`.
- Campo vazio é tratado como pendência na geração do termo.

================================================================================

4. Súmula do orientador

A súmula do orientador possui campos próprios, anexos específicos e gravação separada da súmula do bolsista.

Regras de gravação:

- Os registros são gravados na tabela `inscricao_sumulas`.
- Para súmula do orientador, o campo `bolsista` deve ficar `NULL`.
- Essa regra preserva o comportamento antigo de inscrições e avaliações.

Campos da súmula do orientador:

- Quantidade para cada item da súmula.
- Ano de conclusão do doutorado.
- Resposta sobre ingresso na Fiocruz por meio dos concursos de 2016 e 2024.
- Para orientadora do sexo feminino, resposta sobre filhos menores.

Anexos da súmula do orientador:

- Anexo tipo 9 é obrigatório.
- Anexo tipo 27 é obrigatório quando a orientadora possuir filhos menores, conforme resposta informada.
- Anexo tipo 29 é obrigatório quando o orientador informar ingresso na Fiocruz pelos concursos de 2016 e 2024.

Validações na geração do termo:

- Todos os itens da súmula do orientador devem estar preenchidos.
- Quantidade `0` é aceita.
- Ano de conclusão do doutorado é obrigatório e deve ser um ano válido.
- A resposta sobre recém-servidor é obrigatória.
- Quando recém-servidor for `1`, o anexo tipo 29 é obrigatório.
- Quando o orientador for do sexo feminino, a resposta sobre filhos menores é obrigatória.
- Quando filhos menores for maior que zero, o anexo tipo 27 é obrigatório.

================================================================================

5. Projeto

A etapa de projeto registra os dados do projeto do orientador e os complementos necessários para a inscrição PDJ.

Campos obrigatórios na geração do termo:

- Título do projeto.
- Área de inscrição PDJ.
- Área CNPq.
- Linha de pesquisa Fiocruz.
- Resumo do projeto.
- Justificativa da bolsa.

Campos opcionais:

- Instituições financiadoras.
- Palavras-chave.

Área de inscrição PDJ:

- O campo lista grandes áreas com `id > 9`.
- O valor é salvo em `projeto_bolsistas.area_pdj`.
- O relacionamento usa a tabela de grandes áreas.

Anexos do projeto:

- Na geração do termo, somente o anexo tipo 5 é obrigatório.
- Demais anexos do bloco do projeto podem existir, mas não são obrigatórios para gerar termo.

================================================================================

6. Geração do termo

A geração do termo consolida as validações de todos os blocos. Se houver pendências, o sistema não libera a fase de assinatura e apresenta uma tela agrupada por bloco.

Blocos validados:

- Projeto.
- Bolsista.
- Orientador.
- Súmula do bolsista.
- Súmula do orientador.

Validações do orientador:

- Orientador deve existir.
- Vínculo do orientador deve ser `1`.
- Escolaridade do orientador deve ser `10`.
- Ano de conclusão do orientador deve estar preenchido e ser menor que o ano atual.
- SIAPE deve estar preenchido.
- Laboratório deve estar preenchido.
- Departamento deve estar preenchido.

Resultado da validação:

- Se houver pendências, a tela mostra cada bloco com seus erros.
- Cada bloco possui botão para ir diretamente à tela de correção.
- Mensagens de súmula usam o texto da súmula, não apenas o ID.
- Mensagens de anexo usam o nome cadastrado em `AnexosTipos`.
- Sem pendências, a inscrição passa para a fase `5`, liberando a assinatura do termo.

================================================================================

7. Download do termo

Após a geração, o termo fica disponível na aba de finalização.

- O termo só pode ser baixado quando a inscrição estiver na fase `5`.
- O layout do termo segue o mesmo modelo usado no fluxo de inscrições.
- O sistema tenta gerar PDF com mPDF.
- Se mPDF não estiver disponível ou falhar, o sistema baixa o termo em HTML.

================================================================================

8. Finalização

A finalização exige o envio do termo assinado.

- O arquivo do termo assinado é obrigatório.
- O termo assinado é anexado como tipo 24.
- Após salvar o termo, a inscrição passa para a fase `4`.
- O histórico registra a finalização da inscrição PDJ com termo assinado.

================================================================================

9. Homologação

A homologação exibe dados e anexos para conferência após a finalização.

Anexos do bloco B na homologação:

- A tela monta dinamicamente os anexos do bloco B a partir de `AnexosTipos`.
- Anexos obrigatórios gerais aparecem sempre.
- Anexos obrigatórios por programa aparecem quando o programa do edital corresponde à regra.
- Anexos obrigatórios por cota aparecem quando a cota da inscrição corresponde à regra.
- Anexo tipo 11 aparece como condicional quando `filhos_menor_bolsista > 0`.
- Anexo tipo 16 aparece como condicional quando `primeiro_periodo = 0`, respeitando a regra implementada no fluxo legado.
- Anexos tipo 18 e 19 aparecem como condicionais para bolsista menor de idade, respeitando a regra implementada no fluxo legado.

Ações na homologação:

- Perfil autorizado pode homologar ou não homologar a inscrição.
- Para não homologar, o motivo deve ter pelo menos 20 caracteres.
- A homologação registra data, usuário e resultado no registro da inscrição.
- O histórico registra a decisão de homologação.

================================================================================

10. Regras de compatibilidade com fluxos antigos

- A tabela `inscricao_sumulas` continua sendo usada pelos fluxos antigos.
- Registros antigos permanecem com `bolsista = NULL`.
- No PDJ, a súmula do orientador também usa `bolsista = NULL`.
- No PDJ, somente a súmula do bolsista usa `bolsista = 1`.
- Consultas antigas devem filtrar `bolsista IS NULL` quando precisarem ignorar a súmula do bolsista PDJ.
