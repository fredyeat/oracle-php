<?php
/**
 * index.php Inicia la pagina para la aplicacion
 * 
 * @package Application 
 */        

session_start();
require('ac_equip.inc.php');

$sess = new \Equipment\Session;
$sess->clearSession();

if(!isset($_POST['username'])){
   $page = new \Equipment\Page;
   $page->printHeader("Welcome to AnyCo Corp.");
   echo <<<EOF
   <div id="content">
   <h3>Select User</h3>
   <form method="post" action="index.php">
   <div>
   <input type="radio" name="username" value="admin">Aministrator<br>
   <input type="radio" name="username" value="simon">Simon<br>
   <input type="submit" value="Login">
   </div>
   </form>
   </div>
EOF;
   
   $page->printFooter();
   
}  else {
    if ($sess->authenticateUser($_POST['username'])){
        $sess->setSession();
        header('Location: ac_emp_list.php');
    }  else {
        header('Location: index.php');
    
        
    }
}

?>
