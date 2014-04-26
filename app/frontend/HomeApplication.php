<?php

namespace frontend;

use PetakUmpet\Application;

class HomeApplication extends Application {

  public function indexAction()
  {
    $form = new Form\SearchForm;

    if ($this->request->isPost()) {

      if ($form->bindValidate($this->request)) {

        $search = new AppUser($form->getFieldValue('start'), $form->getFieldValue('end'));

        if ($search->validate()) {
          

          $this->redirect('Home/dashboard');
        }
        // failed login
        $this->session->setFlash('wrong input.');
      }
    }

    return $this->render(array('form' => $form));;
  }

  public function aboutAction()
  {
    return $this->render();
  }
}
