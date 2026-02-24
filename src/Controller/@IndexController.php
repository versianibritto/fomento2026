<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\Http\Session;
use Cake\ORM\TableRegistry;
use App\Service\DashboardService;




class IndexController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Authentication.Authentication');

       
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated([
            'home',
            'unidades',
            'manuais',
            'manutencao',
            'talentos',
            'programas',
            'editais'
        ]);

    }

    //ok
    public function manutencao()
    {}

    //ok
    public function index()
    {        
        $this->viewBuilder()->setLayout('admin');

    }

    //ok
    public function home()
    {}

    //ok
    public function unidades()
    {
        $unidadesTable = TableRegistry::getTableLocator()->get('Unidades');

        $unidades = $unidadesTable
            ->find()
            ->contain([
                'Coordenadores',
                'Subcoordenadores'
            ])
            ->where([
                'Unidades.deleted' => 0
            ])
            ->orderBy([
                'Unidades.sigla' => 'ASC'
            ]);

        $this->set(compact('unidades'));
    }


    //ok
    public function programas($tipo)
    {
        if($tipo===null){
            $this->Flash->error('Erro');
            return $this->redirect(['controller'=>'Index', 'action'=>'home']);
        }
        if($tipo != 'PDJ' && $tipo != 'IC'){
            $this->Flash->error('Erro. Programa não localizado');
            return $this->redirect(['controller'=>'Index', 'action'=>'home']);
        }
        $this->set(compact('tipo'));

                
    }

    //ok
    public function editais()
    {
        $editaisTable = TableRegistry::getTableLocator()->get('Editais');

        $editais = $editaisTable->find()->contain([
            'Erratas' => ['sort' => ['Erratas.id' => 'DESC']],
            'Programas',
        ])
        ->where([
            'visualizar != "I"', 'deleted'=>0
           ])
            ->orderBy(['fim_inscricao' => 'DESC']);
        $this->set(compact('editais'));
    }

    //ok
    public function talentos()
    { }

    public function grafico()
    {
        $this->viewBuilder()->setLayout('admin');
        if(!$this->request->getAttribute('identity')['yoda']) {
            $this->Flash->error('Restrito a administradores');
            return $this->redirect(['controller'=>'Index', 'action'=>'dashboard']);
        }
        $qtd = TableRegistry::getTableLocator()->get('QuantInscricoesReport')->find();
        $qtd_vigente = TableRegistry::getTableLocator()->get('QtdVigentesReport')->find()/*->where(['programa <> "J"'])*/->orderBy(['quantidade'=>'desc']);
        $totalQuantidade = array_sum(array_column($qtd_vigente->toArray(), 'quantidade'));

        $qtd_unidade = TableRegistry::getTableLocator()->get('IcUnidadeReport')->find()->orderBy(['quantidade'=>'desc']);



        // Agrupando os dados
        $programas = [];
        foreach ($qtd as $q) {
            $programa = $q->programa;
            $origem = $q->origem;
            $situacao = $q->situacao;
            $quantidade = $q->quantidade;

            if (!isset($programas[$programa])) {
                $programas[$programa] = [];
            }

            if (!isset($programas[$programa][$origem])) {
                $programas[$programa][$origem] = [];
            }

            $programas[$programa][$origem][$situacao] = $quantidade;
        }


        // Agrupando os dados
        $editais = [];
        foreach ($qtd as $q) {
            $edital = $q->edital;
            $situacao = $q->situacao;
            $quantidade = $q->quantidade;

            if (!isset($editais[$edital])) {
                $editais[$edital] = [];
            }

            $editais[$edital][$situacao] = $quantidade;
        }


        $prog = $this->programa;
        asort($prog);

        
        //dd($unidades->first());
        $this->set(compact('editais', 'qtd', 'prog', 'programas', 'qtd_vigente', 'totalQuantidade', 'qtd_unidade'));
    }


    //ok
    public function manuais()
    {
        $manuais = TableRegistry::getTableLocator()->get('Manuais')->find()->where([
            '(deleted is null)',
            'restrito'=>0
        ])->orderBy([
            'Manuais.id' => 'DESC'
        ]);
        $this->set(compact('manuais'));               
    }

    //ok
    public function dashboard()
    {
        $this->viewBuilder()->setLayout('admin');
        $editaisTable = TableRegistry::getTableLocator()->get('Editais');

        $editais = $editaisTable->find()
        ->where([
            'inicio_inscricao < NOW()',
            'programa_id != 7',
            'visualizar != "I"',
            '(fim_inscricao > NOW())'])
        ->contain(['Erratas' => ['sort' => ['Erratas.id' => 'DESC']]])
        ->orderBy(['fim_inscricao' => 'DESC']);


        $userId = (int)$this->Authentication->getIdentity()->id;

        $dashcountTable = TableRegistry::getTableLocator()->get('Dashcounts');

        $rows = $dashcountTable->find()
            ->where([
                'OR' => [
                    'bolsista'     => $userId,
                    'orientador'   => $userId,
                    'coorientador' => $userId
                ],
            ])
            ->enableHydration(false)
            ->toArray();


        
        $dashboard = [
            'bolsasAtivas' => 0,
            'andamento'    => 0,
            'finalizadas'  => 0,
            'total'        => 0,
        ];

       
        foreach ($rows as $r) {
            $qtd = (int)$r['qtd'];

            if ((int)$r['vigente'] === 1) {
                $dashboard['bolsasAtivas'] += $qtd;
            }

            if ($r['bloco'] === 'I') {
                $dashboard['andamento'] += $qtd;
            }

            if (in_array($r['bloco'], ['F', 'H'], true)) {
                $dashboard['finalizadas'] += $qtd;
            }
        }


        $dashboard['total'] =
            $dashboard['andamento'] + $dashboard['finalizadas'];

        $this->set(compact('dashboard', 'editais'));
        


    }

    //ok
    public function dashdetalhes(string $tipo)
    {
        $this->viewBuilder()->setLayout('admin');

        $userId = (int)$this->Authentication->getIdentity()->id;

        $dashdetTable = TableRegistry::getTableLocator()->get('Dashdetalhes');

        $w=[];
        if($tipo==='V'){
            array_push($w , ['Dashdetalhes.vigente'=>1]);

        }
        if($tipo==='A'){
            array_push($w , ["Dashdetalhes.bloco IN ('I', 'F', 'H')"]);

        }
        if(!in_array($tipo, ['A', 'V' ])) {
             $this->Flash->error(
            'Erro na URL <br> Acesse a tela correta e tente novamentes',
            ['escape' => false]
        );
                return $this->redirect(['controller' => 'Index', 'action' => 'index']);

        }



        $detalhes = $dashdetTable->find()
        ->where([
                'OR' => [
                    'bolsista'     => $userId,
                    'orientador'   => $userId,
                    'coorientador' => $userId
                ],
            ])
        ->where([$w]);
        $this->set(compact('detalhes', 'tipo'));
        


    }


    /*
    public function dashboard()
    {
        $this->viewBuilder()->setLayout('admin');
        $editaisTable = TableRegistry::getTableLocator()->get('Editais');

        $editais = $editaisTable->find()
        ->where([
            'inicio_inscricao < NOW()',
            'programa_id != 7',
            'visualizar != "I"',
            '(fim_inscricao > NOW())'])
        ->contain(['Erratas' => ['sort' => ['Erratas.id' => 'DESC']]])
        ->orderBy(['fim_inscricao' => 'DESC']);

        $usuario = TableRegistry::getTableLocator()
            ->get('Usuarios')
            ->get($this->Authentication->getIdentity()->id);

        $this->Authentication->setIdentity($usuario);

        $dashboardService = new \App\Service\DashboardService(
            TableRegistry::getTableLocator()->get('DashboardSource')
        );

        $dashboard = $dashboardService->buildBase($usuario);
        //dd($dashboard);

        $this->set(compact('dashboard', 'usuario', 'editais'));
        


    }
        

    //ok
    public function dashdetalhes(string $tipo)
    {
        $this->viewBuilder()->setLayout('admin');

        $usuario = $this->Authentication->getIdentity();

        $dashboardService = new DashboardService(
            $this->fetchTable('DashboardSource')
        );

        $detalhes = $dashboardService->getInscricoesDetalhadas(
            (int)$usuario->id,
            $tipo
        );



        $this->set(compact('detalhes', 'tipo'));
    }
        

    //ok
    public function dashyoda()
    {
        if (!$this->request->getAttribute('identity')->yoda) {
            $this->Flash->error(
                'A funcionalidade acessada é restrita à Gestão de Fomento.<br><br>
                Use o menu lateral esquerdo para navegar pelo site.',
                ['escape' => false]
            );

            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        $this->viewBuilder()->setLayout('admin');

        $dashboardService = new DashboardService(
            TableRegistry::getTableLocator()->get('DashboardSource')
        );

        $dashboard = $dashboardService->buildYoda();

        $this->set(compact('dashboard'));
    }
        */

    public function dashyoda()
    {
        $this->viewBuilder()->setLayout('admin');
        


        $userId = (int)$this->Authentication->getIdentity()->id;

        $dashcountTable = TableRegistry::getTableLocator()->get('Dashyodacounts');

        $rows = $dashcountTable->find()
            ->enableHydration(false)
            ->toArray();


        
        $dashboard = [
            'bolsasAtivas' => 0,
            'andamento'    => 0,
            'finalizadas'  => 0,
            'total'        => 0,
            'cancel'       => 0,
            'subst'        => 0,
            'aceito'       => 0,
            'recusado'     => 0,
            
        ];

       
        foreach ($rows as $r) {
            $qtd = (int)$r['qtd'];

            if ((int)$r['vigente'] === 1) {
                $dashboard['bolsasAtivas'] += $qtd;
            }

            if ($r['bloco'] === 'I') {
                $dashboard['andamento'] += $qtd;
            }

            if (in_array($r['bloco'], ['F', 'H'], true)) {
                $dashboard['finalizadas'] += $qtd;
            }

             if (in_array($r['fase_id'], [12, 21], true)) {
                $dashboard['cancel'] += $qtd;
            }

             if (in_array($r['fase_id'], [15], true)) {
                $dashboard['subst'] += $qtd;
            }

             if (in_array($r['fase_id'], [6], true)) {
                $dashboard['aceito'] += $qtd;
            }

             if (in_array($r['fase_id'], [7], true)) {
                $dashboard['recusado'] += $qtd;
            }
        }


        $dashboard['total'] =
            $dashboard['andamento'] + $dashboard['finalizadas'];

        $this->set(compact('dashboard'));
        


    }


    

    


    

}
