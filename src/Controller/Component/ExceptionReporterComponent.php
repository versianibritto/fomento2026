<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Mailer;
use Throwable;

class ExceptionReporterComponent extends Component
{
    public function report(Throwable $e, string $titulo, ?string $contexto = null): array
    {
        $controller = $this->getController();
        $request = $controller->getRequest();
        $identity = $request->getAttribute('identity');

        $userId = $identity->id ?? $identity['id'] ?? null;
        $userNome = $identity->nome ?? $identity['nome'] ?? null;
        $userEmail = $identity->email ?? $identity['email'] ?? null;
        $userAlternativo = $identity->email_alternativo ?? $identity['email_alternativo'] ?? null;
        $userContato = $identity->email_contato ?? $identity['email_contato'] ?? null;

        $dataHora = FrozenTime::now();
        $urlAcessada = $request->getUri()->getPath();
        $mensagemErro = $e->getMessage();
        $host = $request->host();
        $linhaErro = $e->getLine();
        $arquivoErro = $e->getFile();

        $hash = hash('sha256', implode('|', [
            (string)$userId,
            (string)$urlAcessada,
            (string)$mensagemErro,
            (string)$arquivoErro,
            (string)$linhaErro,
        ]));

        $table = TableRegistry::getTableLocator()->get('TiExceptions');
        $janela = $dataHora->subMinutes(5);
        $existente = $table->find()
            ->where([
                'usuario_id' => $userId,
                'url' => $urlAcessada,
                'created >=' => $janela,
            ])
            ->orderBy(['id' => 'DESC'])
            ->first();

        $respondido = $table->find()
            ->where([
                'usuario_id' => $userId,
                'url' => $urlAcessada,
                'created >=' => $janela,
                'status' => 'R',
            ])
            ->orderBy(['id' => 'DESC'])
            ->first();

        $primeiro = $table->find()
            ->select(['id'])
            ->where([
                'usuario_id' => $userId,
                'url' => $urlAcessada,
                'created >=' => $janela,
            ])
            ->orderBy(['id' => 'ASC'])
            ->first();

        $isRepeticao = (bool)$existente;

        $enviarEmail = Configure::read('debug') === false;
        $prefixo = '';
        if ($host === 'fomento2026.local' || $host === 'homolog.pibic.fiocruz.br') {
            $prefixo = 'TESTE EM AMBIENTE DE DESENVOLVIMENTO/HOMOLOGAÇÃO (' . $host . '). FAVOR DESCONSIDERAR ESTE EMAIL.<br><br>';
        }

        if ($enviarEmail && !$isRepeticao) {
            $corpoEmail = "
            {$prefixo}
            <h3>{$titulo}</h3>
            <p><strong>Usuário ID:</strong> {$userId}</p>
            <p><strong>Usuário Nome:</strong> {$userNome}</p>
            <p><strong>Usuário Email:</strong> {$userEmail}</p>
            <p><strong>Usuário Email Alternativo:</strong> {$userAlternativo}</p>
            <p><strong>Usuário Email Contato:</strong> {$userContato}</p>
            <p><strong>Data e Hora:</strong> {$dataHora->format('Y-m-d H:i:s')}</p>
            <p><strong>Servidor:</strong> {$host}</p>
            <p><strong>URL Acessada:</strong> {$urlAcessada}</p>
            <p><strong>Mensagem de Erro:</strong> {$mensagemErro}</p>
            <p><strong>Arquivo:</strong> {$arquivoErro}</p>
            <p><strong>Linha:</strong> {$linhaErro}</p>
            ";

            $to = ['mariaversiani@yahoo.com.br', 'maria.britto@fiocruz.br'];
            $assunto = $titulo;

            (new Mailer('Projetos'))->send('erroTi', [$to, $assunto, $corpoEmail]);
        }

        $nextCount = 1;
        if ($existente) {
            $nextCount = ((int)$existente->repeticoes) + 1;
        }
        $limiteRepeticao = 12;
        $bloqueado = $nextCount >= $limiteRepeticao;

        $registro = $table->newEmptyEntity();
        $registro->status = 'N';
        $registro->classificacao = null;
        $registro->usuario_id = $userId;
        $registro->usuario_nome = $userNome;
        $registro->usuario_email = $userEmail;
        $registro->usuario_email_alternativo = $userAlternativo;
        $registro->usuario_email_contato = $userContato;
        $registro->url = $urlAcessada;
        $registro->host = $host;
        $registro->mensagem = $mensagemErro;
        $registro->arquivo = $arquivoErro;
        $registro->linha = $linhaErro;
        $registro->hash = $hash;
        $registro->repeticoes = $nextCount;
        $registro->repeticao = $existente ? 1 : 0;
        if ($respondido) {
            $registro->repeticao_de_id = $primeiro->id ?? $respondido->id;
            $registro->status = 'R';
            $registro->classificacao_id = $respondido->classificacao_id;
            $registro->resposta = $respondido->resposta;
            $registro->respondido_por = $respondido->respondido_por;
            $registro->respondido_em = $respondido->respondido_em;
        } else {
            $registro->repeticao_de_id = $existente ? ($primeiro->id ?? $existente->id) : null;
        }
        $registro->ultima_ocorrencia = $dataHora;
        $table->save($registro);

        return [
            'id' => $registro->id,
            'repeticao' => (bool)$registro->repeticao,
            'repeticoes' => (int)$registro->repeticoes,
            'bloqueado' => $bloqueado,
            'limite_repeticao' => $limiteRepeticao,
        ];
    }
}
