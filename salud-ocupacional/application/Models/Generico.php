<?php
class Models_Generico{
    protected $_dbMSSQL;
    protected $_dbMSSQLs;
    
    protected $aResult = array(
        "status"    =>  "",
        "code"      =>  "",
        "message"   =>  ""
    );
    
    public function connectMSSQL(){
        try{
            $this->_dbMSSQL = Zend_Registry::get('dbMSSQL');
            $this->_dbMSSQL->getConnection();
        }catch(Zend_Exception $e){
            header("Location: /error/error/");
        }
    }
    public function connectMSSQLs(){
        try{
            $this->_dbMSSQLs = Zend_Registry::get('dbMSSQLs');
            $this->_dbMSSQLs->getConnection();
        }catch(Zend_Exception $e){
            header("Location: /error/error/");
        }
    }
    public function convertAnsi2UTF8($array){ 
        foreach ($array as  $key=>$item) { 
            if(is_array($item)) $array[$key] = $this->convertAnsi2UTF8($item); 
            else $array[$key]=utf8_encode($item);         
        } 
        return $array; 
    } 
}
