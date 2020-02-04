<?php
class MarketPurchaseProcessor extends DBHandler
{
    //
    public function __construct(array $arrParams, MasterHandler $master_handler)
    {
        $arrPropNames = ['mode', 'id_request', 'id_transaction', 'id_shift'];
        // both modes require id_request
        // mode='call' requires id_shift: id_shift as call target.
        // id_transaction is an option
        $this->dbh = $master_handler->dbh;
        $this->id_user = $master_handler->id_user;
        foreach($arrPropNames as $prop){
            if (array_key_exists($prop, $arrParams)){
                $this->$prop = $arrParams[$prop];
            }
        }
        $this->process();
    }

    public function process()
    {
        $this->beginTransactionIfNotIn();
        $this->handleRequest();
    }

    private function handleRequest()
    {
        if ($this->mode === 'put') { // User buying put item
            if (!isset($this->id_transaction)){
                $sql = "SELECT id_transaction FROM requests_pending WHERE id_request=$this->id_request AND `status`=2 AND id_to IS NULL FOR UPDATE;";
                echo $sql . '<br>';
                $stmt = $this->querySql($sql);
                $result = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_NUM);
                $stmt->closeCursor();
                if (!count($result)) {
                    echo 'No valid put request found. Exit!';
                    exit;
                }
                $this->id_transaction = $result[0];
            }
            $sql = "UPDATE requests_pending SET id_to=$this->id_user WHERE id_request=$this->id_request;";
            echo $sql . '<br>';
            $stmt = $this->executeSql($sql);
            
        } elseif ($this->mode === 'call') { // User buying call item
            if(!isset($this->id_shift)){
                echo 'Purchasing call item requires id_shift!<br>';
                exit;
            }
            if(!isset($this->id_transaction)){
                $sql = "SELECT id_transaction FROM requests_pending WHERE id_request=$this->id_request AND `status`=2 AND id_from IS NULL FOR UPDATE;";
                echo $sql . '<br>';
                $stmt = $this->querySql($sql);
                $result = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_NUM);
                $stmt->closeCursor();
                if (!count($result)) {
                    echo 'No valid call request found. Exit!';
                    exit;
                }
                $this->id_transaction = $result[0];
            }
            $sql = "UPDATE requests_pending SET id_from=$this->id_user, id_shift=$this->id_shift WHERE id_request=$this->id_request;";
            echo $sql . '<br>';
            $stmt = $this->executeSql($sql);
        } else {
            echo 'Mode not understood.<br>';
            exit;
        }
    }
}
