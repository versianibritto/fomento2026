<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;

class EnderecosController extends AppController
{
    public function beforeFilter(EventInterface $event) {
        parent::beforeFilter($event);
        $this->Authentication->addUnauthenticatedActions(['buscaEnderecoCompleto']);
    }

    public function buscaEstados()
    {
        $id = $this->request->getData('id');
        $estados = TableRegistry::get('States');
        $query = $estados->find('all')->select();
        if($id!=null){
            $query->where(['States.country_id'=>$id]);
        }
        $query->hydrate();
        $estados = $query->toList();
        $this->viewBuilder()->template('ajax');
        $this->set('retorno', $estados);
    }

    public function buscaCidades()
    {
        $id = $this->request->getData('id');
        $cidades = TableRegistry::get('Cities');
        $query = $cidades->find('all')->select();
        if($id!=null){
            $query->where(['Cities.state_id'=>$id]);
        }
        $query->hydrate();
        $cidades = $query->toList();
        $this->viewBuilder()->template('ajax');
        $this->set('retorno', $cidades);
    }

    public function buscaBairros()
    {
        $id = $this->request->getData('id');
        $bairros = TableRegistry::get('Districts');
        $query = $bairros->find('all')->select();
        if($id!=null){
            $query->where(['Districts.city_id'=>$id]);
        }
        $query->hydrate();
        $bairros = $query->toList();
        $this->viewBuilder()->template('ajax');
        $this->set('retorno', $bairros);
    }

    public function buscaEndereco()
    {
        $cep = $this->request->getData('txtCEP');
        $endereco = TableRegistry::get('Streets');
        $query = $endereco->find('all')->select();

        $query->enableHydration();
        $endereco = $query->all()->toList();
        $this->viewBuilder()->setTemplate('ajax');
        $this->set('retorno', $endereco);
    }

    public function buscaEnderecoCompleto()
    {
        $cepRaw = trim((string)$this->request->getData('txtCEP'));
        $cep = preg_replace('/\D/', '', $cepRaw);
        $cepFormatado = strlen($cep) === 8 ? substr($cep, 0, 5) . '-' . substr($cep, 5) : $cep;
        $id =  $this->request->getData('id');
        $endereco = TableRegistry::getTableLocator()->get('Streets');
        $query = $endereco->find('all')->select()->contain(['Districts'=>['Cities'=>['States']]]);
        if ($cep !== '') {
            $cepsBusca = array_values(array_unique(array_filter([
                $cepRaw,
                $cep,
                $cepFormatado,
            ])));
            $query->where([
                'Streets.cep IN' => $cepsBusca,
            ]);
            $query->orderBy([
                'Streets.nome' => 'ASC',
            ]);
        }else{
            $query->where(['Streets.id'=>$id]);
        }
        $endereco = $query
            ->enableHydration(false)
            ->all()
            ->toArray();

        return $this->response
            ->withType('application/json')
            ->withStringBody((string)json_encode($endereco, JSON_UNESCAPED_UNICODE));
    }
}
