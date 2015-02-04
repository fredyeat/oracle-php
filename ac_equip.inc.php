<?php

/**
 * ac_equip.inc.php: PHP calsses for the employee equipment example
 * @package Equipment
 */
namespace Equipment;

/**
 * URL of the company logo
 */
//define('LOGO_URL', 'http://localhost/ac_logo_image.php');

/**
 * @package Equipment
 * @package Session
 */

class Session {
    /**
     * 
     *@var string Web User's name
     */
    public $username = null;
    /**
     * @var integer current record number for paged employee results
     */
    public $empstartrow = 1;
    /**
     * 
     * @var string CSRF token for HTML  forms
     */
 public $csrftoken = null;
 
 /**
  * Simple authentication of the web end-user
  * 
  * @param string $username 
  * @return boolean True if dthe user is allowed to use the application
  */
 public function authenticateUser($username){
     switch ($username){
         case 'admin':
         case 'simon':
             $this->username = $username;
             return (true); //OK to login
         default:
             $this->username = null;
             return(false); // Not OK
     }
 } 
 /**
  * Check if the current user is allowed to do administrator tasks
  * 
  * @return boolean
  */
 public function isPrivilegedUser() {
     if ($this->username === 'admin')
         return(true);
     else
         return(false);
 }
 /**
  * Store the session to provide a stateful webn experiene
  */
 public function setSession(){
     $_SESSION['username']  =$this->username;
     $_SESSION['empstartrow']  =(int)$this->empstartrow;
     $_SESSION['csrftoken']  =$this->csrftoken;
      }
 
      /**
       * Get session data ton provide a stateful web experience
       */
 public function getSession(){
     $this->username = isset($_SESSION['username'])?
             $_SESSION['username'] : null;
     $this->empstartrow = isset($_SESSION['empstartrow'])?
             (int)$_SESSION['empstartrow'] : 1;
     $this->csrftoken = isset($_SESSION['csrftoken'])?
             $_SESSION['csrftoken'] : null;
 }
 
 /**
  * Logout the current user
  */
 public function clearSession(){
     $_SESSION = array();
     $this->username = null;
     $this->empstartrow = 1;
     $this->csrftoken = null;
 }
 /**
  * Records a token to check that any submitted form was generated
  * by the application.
  * 
  * For real systems the CSRF token shoul be securely,
  * ramdonly generated so it can't be guessed by a hacker
  * mt_rand() is not suffiecient for production systems.
  */
public function setCsrfToken(){
    $this->csrftoken = mt_rand();
    $this->setSession();
}

   

}
 /**
  * @package Equipment
  * @subpackage Page
  */        
 class Page{
     /**
      * Print the top section of each HTML page
      *@param string $title The page title
      */
     public function printHeader($title){
         $title = htmlspecialchars($title, ENT_NOQUOTES, 'UTF-8');
         echo <<<EOF
         <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
         "http://www.w3.org/TR/html4/strict.dtd">
         <html>
         <head>
            <meta http-equiv="Content-Type"
            content="text/html; charset=utf-8">
            <link rel="stylesheet" type="text/css" href="style.css">
            <title>$title</title>
         </head>
         <body>
         <div id="header">
EOF;
   //          
   if (defined('LOGO_URL')) {
            echo '<img src="' . LOGO_URL . '" alt="Company Icon">&nbsp;';
         }
         echo "$title</div>";
         }
        /**
        *Print the bottom of each HTML page
        */
           public function printFooter(){
                 echo "</body></html>\n";
                 }
    /**
     * Menu de la izquierda de la paginaHTML
     * 
     * @param string $username The current web user
     * @param type $isprivilegeduser True if the web user is privileged
     */
     public function printMenu($username, $isprivilegeduser){
         $username = htmlspecialchars($username, ENT_NOQUOTES, 'UTF-8');
         echo <<<EOF
         <div id='menu'>
         <div id='user'>Logged in as: $username </div>
         <ul> 
         <li><a href='ac_emp_list.php'>Employee List</a></li>        
EOF;
        if ($isprivilegeduser){
         echo <<<EOF
                 
         <li><a href='ac_report.php'>Equipment Report</a></li>
         <li><a href='ac_graph_page.php'>Equipment Graph</a></li>
         <li><a href='ac_logo_upload.php'>Upload Logo</a></li>        
EOF;
         }
         
         echo <<<EOF
         <li><a href="index.php">Logout</a></li>
         </ul>
         </div>
EOF;
        
         
     }    
   }
              
?>



