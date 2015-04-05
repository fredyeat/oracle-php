<?php

/**
 * *ac_show_equip.php: Show an employee's equipment
 * @package ShowEquipment
 */

session_start();
require('ac_db.inc.php');
require('ac_equip.inc.php');

$sess = new \Equipment\Session;
$sess->getSession();
if (!isset($sess->username) || empty($sess->username)
        || !isset($_GET['empid'])) {
    header('Location: index.php');
    exit;
        }
        
$empid = (int) $_GET['empid'];

$page =new \Equipment\Page;
$page->printheader("AnyCo Corp. Show Equipment");
$page->printMenu($sess->username, $sess->isPrivilegedUser());
ob_start();
try {
    printcontent($sess, $empid);
} catch (Exception $e) {
    ob_end_clean();
    echo "<div id='content'>\n";
    echo "Sorry, an error occurred...";
    echo "<div>";
}
ob_end_flush();

$page->printFooter();

//Functions

/** Get an Employee Name
 * 
 * @param DB $db
 * @param integer $empid
 * @return string An employee name
 */
function getempname($db, $empid){
    $sql = "SELECT first_name || ' ' || last_name AS emp_name
        FROM employees
        WHERE employee_id = :id";
    $res = $db->execFetchAll($sql, "Get Ename", array(array(":id", $empid, -1)));
    $empname = $res[0]['EMP_NAME'];
    return($empname);

}
/**
 * Print the main body of the page
 * 
 * 
 * @param Session $sess
 * @param integer $empid Employee identifier
 */
function printcontent($sess, $empid){
    echo "<div id='content'>\n";
    $db = new \Oracle\Db("Equipment", $sess->username);
    $empname = htmlspecialchars(getempname($db, $empid), ENT_NOQUOTES, 'UTF-8');
    echo "$empname has: ";
    /*throw new Exception;*/
    $sql = "BEGIN get_equip(:id, :rc); END;";
    $res = $db->refcurExecFetchAll($sql, "Get Equipment Lis",
            "rc", array(array(":id", $empid, -1)));
    if (empty($res['EQUIP_NAME'])){
        echo "no equipmet";
    }
    else {
        echo "<table border='1'>\n";
        foreach ($res['EQUIP_NAME'] as $item){
            $item = htmlspecialchars($item, ENT_NOQUOTES,'UTF-8');
            echo "<tr><td>$item</td></tr>\n";
            
        }
    
        echo "</table>\n";
    }
    echo "</div>"; //content    
}       

?>

