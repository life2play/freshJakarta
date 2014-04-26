<!DOCTYPE html>
<html lang="en">
  <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="shortcut icon" href="<?php echo $T->getResourceUrl('ico/favicon.ico') ?>">    
  <title><?php echo $config->getProjectTitle(); ?></title>

  <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

  <link href="<?php echo $T->getResourceUrl('css/bootstrap.min.css') ?>" rel="stylesheet">
  <link href="<?php echo $T->getResourceUrl('css/font-awesome.min.css') ?>" rel="stylesheet">
  
  <?php $T->getCss() ?>

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
  <![endif]-->
  
  <script src="<?php echo $T->getResourceUrl('js/jquery.min.js') ?>"></script>
  <script src="<?php echo $T->getResourceUrl('js/bootstrap.min.js') ?>"></script>

  <?php $T->getJs() ?>
  </head>

  <body>
    <!-- Fixed navbar -->
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header navbar-right">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#"><?php echo $config->getProjectTitle() ?></a>
        </div>
        <div class="navbar-collapse collapse">          
          <ul class="nav navbar-nav">
            <?php echo $UI->navMenu(array('Home' => 'Home/index', 'Informasi Transport Umum' => 'Home/about', 'About' => 'Home/about')) ?>
          </ul>          
        </div><!--/.nav-collapse -->
      </div>
    </div>

    <?php echo $UI->subNavMenu() ?>
    <?php echo $T->block('content') ; ?>
    
    <div class="container">
      <hr/>
      <footer>
        Copyright &copy; <?php echo date('Y') ; ?>
      </footer>
    </div>

    
  </body>
</html>