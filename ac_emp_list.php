
<?php
/**
 * ac_emp_list.php: Listade empleados
 * @package Employee
 */

define('NUMRECORDSPERPAGE', 5);

session_start();
require('ac_db.inc.php');
require('ac_equip.inc.php');

$sess = new \Equipment\Session;
$sess->getSession();
if (!isset($sess->username) || empty($sess->username)){
    header('Location: index.php');
    exit;
    
    }
$page = new \Equipment\Page;
$page->printHeader("Any Corp. Employee List");
$page->printMenu($sess->username, $sess->isPrivilegedUser());
printcontent($sess, calcstartrow($sess));
$page->printFooter();
    
//Functions
/**
 * Muestra la parte pricipal de la pagina
 * @param Session $sess
 * @param integer $startrow La primera linea de la tabla se imprime
  */
/* @var $startrow type */
 function printcontent($sess, $startrow) {
     echo "<div id='content'>";
     
     $DB = new \Oracle\DB("Equipment", $sess->username);
     $sql = "SELECT employee_id, first_name || ' ' || last_name AS name,
             phone_number FROM employees ORDER BY employee_id";
    $res = $db->execFetchPage($sql, "Equipment Query", $startrow,
 NUMRECORDSPERPAGE);
 if ($res){
     printrecords($sess, ($startrow === 1),$res);
      }
 else {
        printnorecords();
}
echo "<div>"; //content
//Guarda la sesion, incluyendo la linea actual de datos
$sess->empstartrow = $startrow;
$sess->setSession();
 }
/**
 * Returno el numero de fila del primenr registro a mostrar
 *
 * El calculo es basado en l aposocion actual y
 * no imorta si es clickeado el boton Next o Previous
 * 
 * @param Session $sess
 * @return integer El numero de la fila en la pagina se miostrara aca
 *  
 */

function calcstartrow($sess){
    if (empty($sess->empstartrow)){
    $startrow = 1;
    }
    else {
        $startrow = $sess->empstartrow;
        if (isset($_POST['prevemps'])){
            $startrow -= NUMRECORDSPERPAGE;
            if ($startrow < 1){
                $startrow = 1;
            }
        } else if(isset($_POST['nextemps'])){
                $startrow += NUMRECORDSPERPAGE;
     
 }
    }
    return($startrow);
}

/**
 * Mostrar los registros de los empleados
 * 
 * @param Session $sess
 * @param boolean $atfirstrow True if the first array entry is the first table row
 * @param array $res Array of rows to print 
 * 
 */
function printrecords($sess, $atfirstrow, $res){
    echo <<< EOF
    <table border='1'>
    <tr><th>Name</th><th>Phone<th><th>Equipment</th></tr>
EOF;
    foreach ($res as $row){
        $name = htmlspecialchars($row['NAME'], ENT_NOQUOTES, 'UTF-8');
        $pn = htmlspecialchars($row['PHONE_NUMBER'], ENT_NOQUOTES, 'UTF-8');
        $eid = (int)$row['EMPLOYEE_ID'];
        echo "<tr><td>$name</td>";
        echo "<td>$pn</td>";
        echo "<td><<a href='ac_show_equip.php?empid=$eid'>Show</a>";
      if ($sess->isPrivilegedUser()){
            echo "<a href='ac_add_one.php?empid=$eid'>Add One</a>";
           echo "<a href='ac_add_multi.php?empid=$eid'>Add Multiple</a>";
   }

echo "</td></tr>\n";
}
echo "</table>";
printnextprev($atfirstrow, count($res));
}

/**
 * Muestra los botones Next y Previous uando sea necesario navegar los registros
 * 
 * @param boolean $atfirstrow True if the first array entry is the first table row
 * @param integer $numrows Number of rows the current query retrieved
 */

function printnextprev($atfirstrow, $numrows){
    if (!$atfirstrow || $numrows == NUMRECORDSPERPAGE){
        echo "<form method='post' action='ac_emp_list.php'><div>";
        if (!$atfirstrow)
            echo "<input type='submit' value='< Previous' name='prevemps'>";
        if ($numrows == NUMRECORDSPERPAGE)
            echo "<input type='submit' value='Next >' name='nextemps'>";
        echo "</div></form>\n";
        
    }
}

/**
 * muestra un mensaje cuando no haya mas registros que mostrar
 *lo cual puede ser causado porque la tabla esta vacia o se ha llegado al final
 * de los resulados y no hay mas registros. 
 */

function printnorecords(){
    if (!isset($_POST['nextemps'])){
       echo  "<p>No records found..</p>";
    }
    else {
        echo <<<EOF
        <p>No MOre Records</p>
        <form method='post' action='ac_emp_list.php'>
        <input type='submit' value='< Previous' name='prevemps'></form>
EOF;
    }
}
?>