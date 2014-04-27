<?php

namespace backend;

use PetakUmpet\Application;
use PetakUmpet\UI\DataTables;

use PetakUmpet\Pager\DataTablePager;
use PetakUmpet\Database\Accessor;

use PetakUmpet\Form;
use PetakUmpet\Form\Field;
use PetakUmpet\Form\Component\TableAdapterForm;

use PetakUmpet\Request;

class InfoApplication extends Application {

  public function etaAction()
  {
    $dt = new DataTables($this->request);
    $dt->setDataSourceAction('Info/source');
    $dt->setColumnNames(array('id', 'checktime', 'direction', 'koridorno', 'fromhalte', 'tohalte', 'avg_etatime'));
    return $this->render(array('dt' => $dt), 'infoLayout');
  }

  public function sourceAction()
  {
    $tablename = 'busway_eta_final';

    $form = new TableAdapterForm($tablename, array(), array(), '?dtact=Save');         

    switch($this->request->get('dtact')) {
      case 'Add':
        return $this->renderView('Info/form', array('form' => $form));
        break;
      case 'Edit':
      case 'View':
        $form->setValuesById($this->request->get('id'));
        return $this->renderView('Info/form', array('form' => $form));
        break;
      case 'Save':
        $request = new Request;
        if ($request->isPost()) {
          if (($retId = $form->bindValidateSave($request))) {
            if ($form->isSaveAndAdd($request)) {
              return $this->renderView('Home/form', array('form' => $form));
            }
            $this->session->setFlash('Data is saved.');
          }
        }
        return $this->redirect('backend/info');
    }
    // default act
    $pager = new DataTablePager($tablename, array('id', 'checktime', 'direction', 'koridorno', 'fromhalte', 'tohalte', 'avg_etatime'), $this->request);
    return (string) $pager;

  }

  public function dashboardAction()
  {
    return $this->render();
  }
}
