<?php

namespace frontend;

use PetakUmpet\Application;

class HomeApplication extends Application {

  public function indexAction()
  {
    return $this->render();
  }

  public function aboutAction()
  {
    $this->request->setSubNavMenu(false);
    return $this->render();
  }
}
