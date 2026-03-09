<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Cake\Http\Session;
use Cake\ORM\TableRegistry;
use App\Service\DashboardUserService;
use Cake\Database\Expression\QueryExpression;
use Cake\I18n\FrozenTime;







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
        $vitrinesTable = TableRegistry::getTableLocator()->get('Vitrines');

        $vitrines = $vitrinesTable->find()
            ->where([
                'Vitrines.deleted IS' => null,
                'Vitrines.divulgacao <=' => FrozenTime::now(),
            ])
            ->orderBy([
                'Vitrines.divulgacao' => 'DESC',
                'Vitrines.id' => 'DESC',
            ]);

        $this->set(compact('vitrines'));
    }

    //ok
    public function talentos()
    { }

    // grafico movido para GraficoController

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
    // contador de inscrições do logado
    public function dashboard()
    {
        $this->viewBuilder()->setLayout('admin');
        $editaisTable = TableRegistry::getTableLocator()->get('Editais');

        $editais = $editaisTable->find()
        ->where([
            'inicio_inscricao <= NOW()',
            //'programa_id != 7',
            'visualizar' => 'E',
            'deleted' => 0,
            'fim_inscricao >= NOW()',
        ])
        ->contain([
            'Erratas' => ['sort' => ['Erratas.id' => 'DESC']],
        ])
        ->orderBy(['origem'=>'DESC', 'fim_inscricao' => 'DESC']);


        $userId = (int)$this->Authentication->getIdentity()->id;

        $dashboardService = new DashboardUserService();
        $dashboard = $dashboardService->contador($userId);

        $this->set(compact('dashboard', 'editais'));
        


    }


    //ok
    // contador de inscrições do logado
    public function dashdetalhes(string $tipo)
    {
        $this->viewBuilder()->setLayout('admin');

        $identity = $this->Authentication->getIdentity();
        $userId   = (int)$identity->id;

        $tipo = strtoupper(trim($tipo));
        $conditions = [];

        // Filtros específicos por tipo
        if ($tipo === 'V') {
            $conditions['Dashdetalhes.fim_vigencia >=' ] = FrozenTime::now();
            $conditions['Dashdetalhes.vigente'] = 1;
        } elseif ($tipo === 'A') {
            $conditions['Dashdetalhes.fim_vigencia >=' ] = FrozenTime::now();
            $conditions['Dashdetalhes.bloco IN'] = ['I', 'F', 'H', 'R'];
        } elseif ($tipo !== 'T') {
            $this->Flash->error(
                'Erro na URL <br> Acesse a tela correta e tente novamente',
                ['escape' => false]
            );
            return $this->redirect(['controller' => 'Index', 'action' => 'index']);
        }

        // Busca via Service
        $dashboardService = new \App\Service\DashboardUserService();
        $detalhesQuery = $dashboardService->detalhes($conditions, $userId, $identity)
            ->orderBy(['Dashdetalhes.id' => 'DESC']);
        
        // Transformar em array
        $detalhes = $detalhesQuery->enableHydration(false)->toArray();
        if (!empty($detalhes)) {
            $ids = [];
            foreach ($detalhes as $linha) {
                $idLinha = (int)($linha['id'] ?? 0);
                if ($idLinha > 0) {
                    $ids[] = $idLinha;
                }
            }
            $ids = array_values(array_unique($ids));
            if (!empty($ids)) {
                $origensMap = $this->fetchTable('ProjetoBolsistas')->find()
                    ->select(['id', 'origem'])
                    ->where(['ProjetoBolsistas.id IN' => $ids])
                    ->enableHydration(false)
                    ->all()
                    ->combine('id', 'origem')
                    ->toArray();

                foreach ($detalhes as $idx => $linha) {
                    $idLinha = (int)($linha['id'] ?? 0);
                    $detalhes[$idx]['origem'] = $origensMap[$idLinha] ?? null;
                    if ($detalhes[$idx]['origem'] === null) {
                        $fallback = $this->fetchTable('ProjetoBolsistas')->find()
                            ->select(['origem'])
                            ->where([
                                'ProjetoBolsistas.projeto_id' => (int)($linha['projeto_id'] ?? 0),
                                'ProjetoBolsistas.orientador' => (int)($linha['orientador'] ?? 0),
                                'ProjetoBolsistas.editai_id' => (int)($linha['editai_id'] ?? 0),
                                'ProjetoBolsistas.deleted IS' => null,
                            ])
                            ->orderBy(['ProjetoBolsistas.id' => 'DESC'])
                            ->first();
                        if ($fallback) {
                            $detalhes[$idx]['origem'] = $fallback->origem;
                        }
                    }
                }
            }
        }

        $this->set(compact('detalhes', 'tipo'));
    }



    //ok
    //contador geral do painel Yoda
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

            if (in_array($r['bloco'], ['F', 'H', 'R'], true)) {
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
