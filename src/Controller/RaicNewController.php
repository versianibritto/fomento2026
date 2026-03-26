<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\I18n\FrozenTime;

class RaicNewController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->viewBuilder()->setLayout('admin');
    }

    public function painel()
    {
        $identity = $this->request->getAttribute('identity');
        $usuarioId = (int)($identity['id'] ?? 0);
        $minha = $this->fetchTable('Raics')->find()
            ->contain([
                'Usuarios',
                'Orientadores',
                'Unidades',
                'ProjetoBolsistas',
                'Editais',
            ])
            ->where(['Raics.deleted' => 0])
            ->andWhere(function ($exp) use ($usuarioId) {
                return $exp->or([
                    'Raics.usuario_id' => $usuarioId,
                    'Raics.orientador' => $usuarioId,
                ]);
            })
            ->orderBy(['Raics.id' => 'DESC'])
            ->all();

        $this->set(compact('minha'));
    }

    public function ver($id = null)
    {
        $raic = $this->fetchTable('Raics')->get($id, [
            'contain' => [
                'Usuarios',
                'ProjetoBolsistas',
                'Projetos' => ['Areas', 'Linhas'],
                'Orientadores',
                'Coorientadores',
                'Cadastro',
                'Libera',
                'Unidades',
            ],
        ]);

        if (!$this->request->getAttribute('identity')['yoda']) {
            if ($this->request->getAttribute('identity')['jedi']) {
                $unidadesPermitidas = array_filter(array_map('trim', explode(',', (string)$this->request->getAttribute('identity')['jedi'])));
                if (!in_array((string)$raic->unidade_id, $unidadesPermitidas, true)) {
                    $this->Flash->error('Esta RAIC é da gestão de outra unidade.');
                    return $this->redirect(['controller' => 'RaicNew', 'action' => 'painel']);
                }
            } else {
                if (!in_array($this->request->getAttribute('identity')['id'], [$raic->orientador, $raic->usuario_id])) {
                    $this->Flash->error('Somente a gestão, orientador e bolsista tem acesso a este módulo');
                    return $this->redirect(['controller' => 'Index', 'action' => 'index']);
                }
            }
        }

        $lista = $this->fetchTable('AvaliadorBolsistas')->find('all')
            ->contain([
                'Avaliadors' => 'Usuarios',
            ])
            ->where([
                'AvaliadorBolsistas.bolsista' => $id,
                'AvaliadorBolsistas.tipo' => 'V',
            ]);

        $this->set(compact('raic', 'lista'));
    }

    public function liberacertificado($id = null)
    {
        $raicsTable = $this->fetchTable('Raics');
        $raic = $raicsTable->get($id);

        if (!$this->request->getAttribute('identity')['yoda']) {
            if ($this->request->getAttribute('identity')['jedi']) {
                $unidadesPermitidas = array_filter(array_map('trim', explode(',', (string)$this->request->getAttribute('identity')['jedi'])));
                if (!in_array((string)$raic->unidade_id, $unidadesPermitidas, true)) {
                    $this->Flash->error('Esta RAIC é da gestão de outra unidade.');
                    return $this->redirect(['controller' => 'Index', 'action' => 'index']);
                }
            } else {
                $this->Flash->error('Somente a Coordenação da Unidade e a Gestão de Fomento pode liberar certificado da RAIC.');
                return $this->redirect(['controller' => 'Index', 'action' => 'index']);
            }
        }

        if ((int)$raic->deleted === 1) {
            $this->Flash->error('A RAIC foi deletada.');
            return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
        }

        if ($raic->data_apresentacao == null) {
            $this->Flash->error('A RAIC ainda não foi agendada.');
            return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
        }

        if (date('Ymd') <= $raic->data_apresentacao->i18nFormat('YMMdd')) {
            $this->Flash->error('A RAIC ainda não ocorreu. A presença só poderá ser registrada após o evento.');
            return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
        }

        if ($raic->presenca === 'S') {
            $this->Flash->error('O certificado já estava liberado.');
            return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
        }

        $this->request->allowMethod(['post', 'put']);

        $raic->presenca = 'S';
        $raic->usuario_libera = $this->request->getAttribute('identity')['id'];
        $raic->data_liberacao = date('Y-m-d H:i:s');

        if ($raicsTable->save($raic)) {
            $this->historico($raic->id, 'Certificado Liberado', 'Certificado Liberado', 'Usuário responsável registrou a presença do aluno');
            $this->Flash->success('Certificado liberado.');
            return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
        }

        $this->Flash->error('Houve um erro durante a liberação. Tente novamente.');
        return $this->redirect(['controller' => 'RaicNew', 'action' => 'ver', $raic->id]);
    }

    public function historico($id, $original, $atual, $just, bool $throw = true): bool
    {
        $raicHistoricos = $this->fetchTable('RaicHistoricos');
        $novo = $raicHistoricos->newEmptyEntity();
        $novo->raic_id = $id;
        $novo->usuario_id = $this->request->getAttribute('identity')->id;
        $novo->alteracao = $original;
        $novo->justificativa = $just;

        $nv = $raicHistoricos->save($novo);
        if (!$nv) {
            $this->Flash->error('Houve um erro na gravação. Tente novamente');
            return false;
        }

        return true;
    }
}
