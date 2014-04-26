<?php

namespace frontend\Form;

use PetakUmpet\Form;
use PetakUmpet\Validator;
use PetakUmpet\Validator\Required;

class SearchForm extends Form {

  public function __construct($name='Login')
  {
    parent::__construct($name);

    $this->add('text', 'start', array('required' => 'true', 'placeholder' => 'cari alamat..'));
    $this->add('text', 'finish', array('required' => 'true', 'placeholder' => 'cari alamat..'));

    $this->addAction(new Form\Field\Submit('Search'));

    $vld = new Validator;
    $vld->add('start', new Required);
    $vld->add('finish', new Required);

    $this->setValidator($vld);

  } 

}
