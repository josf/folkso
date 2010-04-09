<?php
  /**
   * @package Folkso
   * @author Joseph Fahey
   * @copyright 2009 Gnu Public Licence (GPL)
   * @subpackage Tagserv
   */
/**
 * @package Folkso
 */
class folksoRight {

  private $service;
  private $right;


  /**
   * @param 
   */
  public function __construct ($service, $right) {
    if ((strlen($right) < 3) ||
        (strlen($service) < 3)){
      throw new Exception('Bad data on right creation');
    }

    $this->service = $service;
    $this->right = $right;
   }
  

  /**
   * @param $right String
   */
  public function validateRight ($right) {
    if (is_string($right) && 
        (strlen($right) > 2) &&
        preg_match('/^[a-z_]+$/', $right)) {
      return true;
    }
    return false;
  }


   /**
    * Returns name of right
    */
    public function getRight () {
      return $this->right;
    }
    public function getService () {
      return $this->service;
    }

    /**
     * Returns the full name of the right: service + right.
     *
     * To be used for indexing in fkRightStore
     */
     public function fullName () {
       return $this->getService() . '/' . $this->getRight();
     }

     public function asXmlFrag (){
       return sprintf("<right>\n"
                      ."\t<service>%s</service>\n"
                      ."\t<type>%s</type>\n"
                      ."</right>",
                      $this->service, $this->right);
     }
    
}

/**
 * @package Folkso
 */
class folksoRightStore {
  private $store;
  public $aliases;
  private $loc;

  /**
   *
   */
   public function __construct () {
     $this->store = array();
     $this->aliases = array('folkso/create' => array('folkso/redac', 'folkso/admin'),
                            'folkso/delete' => array('folkso/redac', 'folkso/admin'),
                            'folkso/tagdelete' => array('folkso/admin'),
                            'folkso/redac' => array('folkso/admin')
                            );
   }
   
   /**
    * @return Bool False if the rightStore is empty, true otherwise.
    */
    public function hasRights () {
      if (count($this->store) === 0){
        return false;
      }
      return true;
    }
   

   /**
    * @param folksoRight $dr
    */
    public function addRight (folksoRight $dr) {
      if ($this->store[$dr->fullName()] instanceof folksoRight){
        throw new Exception('Cannot add right because it is already present');
      }
      $this->store[$dr->fullName()] = $dr;
    }
   

    /**
     * Deletes the right. Exception on missing right.
     * 
     * @param folksoRight $dr
     */
    public function removeRight (folksoRight $dr) {
      if (isset($this->store[$dr->fullName()])){
        unset($this->store[$dr->fullName()]);
      }
      else {
        throw new Exception('Right not present, cannot be removed');
      }
    }

    /**
     * Throws exception if right to modifiy has not been assigned yet.
     *
     * @param folksoRight $dr
     */
     public function modifyRight (folksoRight $dr) {
       if (isset($this->store[$dr->fullName()])){
         $this->store[$dr->fullName()] = $dr;
       }
       else {
         throw new Exception('Cannot modify right that has not been added');
       }
     }

     /**
      * @param $service String Name of the service we are checking
      * @param $right String Name of the right we are checking
      * @return Bool True if authorized, false if not.
      */
     public function checkRight ($service, $right) {
       if ($this->store[$service . '/' . $right] instanceof folksoRight){
         return true;
       }
       elseif ($this->getAliases($service, $right))
         foreach ($this->getAliases($service, $right) as $alias) {
           if ($this->store[$alias] instanceof folksoRight) {
             return true;
           }
         }
       return false;
       }

     /**
      * Like checkRight() but returns a folksoRight object when
      * present. (False when not)
      *
      * @param $service
      * @param $right
      * @return mixed folksoRight object, or false
      */
     public function getRight ($service, $right) {
       if ($this->checkRight($service, $right)) {
         return $this->store[$service . '/' . $right];
       }
       return false;
     }

     /**
      * @param $service String
      * @param $right String
      */
     public function getAliases ($service, $right) {
       $fullname = $service . '/' . $right;
       if (! is_array($this->aliases[$fullname])){
         return false;
       }
       return $this->aliases[$fullname];
     }

     /**
      * @return String : XML representation of a user's rights.
      * @param $doctype Boolean True if you want the xml doctype, defaults to false.
      */
     public function xmlRights ($doctype = null) {
       $xml = '<?xml version="1.0"?>';
       if (is_null($doctype)) {
         $xml = '';
       }
       $xml .= "<userRights>";
       foreach (array_keys($this->store) as $serviceRight) {
         $right = $this->store[$serviceRight];
         $xml .= "\n" . $right->asXmlFrag();
        }
       $xml .= "</userRights>";
       return $xml;
      }
     
}
