<?php
class userOrientedRequest
{
    public $dbh;
    public $idUser;
    public $arrayRequest;
    public $idTrans;
    public $idRequest;
    public $nicknameCreated;
    public $timeProceeded;
    public $nicknameFrom;
    public $nicknameTo;
    public $idShift;
    public $dateTime;
    public $classTextColorDay;
    public $shift;
    public $status;
    public $position;
    public $agreedUser;
    public $checkedUser;
    public $script;

    // This object is not for market item i.e. id_to cannot be NULL.
    public function __construct($id_user, $arrayRequest, $arrayMemberObjectsByIdUser, $dbh)
    {
        $this->dbh = $dbh;
        $this->idUser = $id_user;
        $this->arrayRequest = $arrayRequest;
        $this->idTrans = $arrayRequest["id_transaction"];
        $this->idRequest = $arrayRequest["id_request"];
        $this->nicknameCreated = $arrayMemberObjectsByIdUser[$arrayRequest["id_created"]]->nickname;
        $this->timeProceeded = $arrayRequest["time_proceeded"];
        $this->status = $arrayRequest["status"];
        $this->nicknameFrom = $arrayMemberObjectsByIdUser[$arrayRequest["id_from"]]->nickname;
        $this->nicknameTo = $arrayMemberObjectsByIdUser[$arrayRequest["id_to"]]->nickname;
        if ($arrayRequest["id_created"] === $id_user) {
            $this->nicknameCreated = 'YOU';
        } else {
            $this->nicknameCreated = $arrayMemberObjectsByIdUser[$arrayRequest["id_created"]]->nickname;
        }
        $this->idShift = $arrayRequest["id_shift"];
        $sql = "SELECT date_shift, shift FROM shifts_assigned WHERE id_shift=$this->idShift;";
        // echo $sql;
        // var_dump($this->dbh->query($sql)->errorInfo());
        $stmt = $this->dbh->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // var_dump($result);
        $stmt->closeCursor();
        $this->dateTime = DateTime::createFromFormat('Y-m-d', $result[0]["date_shift"]);
        switch ($this->dateTime->format('w')) {
            case 0:
                $this->classTextColorDay = 'text-danger';
                break;
            case 6:
                $this->classTextColorDay = 'text-primary';
                break;
            default:
                $this->classTextColorDay = '';
        }
        // var_dump($this->dateTime->format('M j (D)'));
        $this->shift = $result[0]["shift"];
        if ($arrayRequest["id_from"] === $id_user) {
            $this->position = 'from';
            $this->nicknameFrom = 'YOU';
            $this->agreedUser = $arrayRequest["agreed_from"];
            $this->checkedUser = $arrayRequest["checked_from"];
            $this->script = 'Your ' . $this->dateTime->format('M j (D)') . ' ' . $this->shift . ' to ' . $this->nicknameTo;
        } else if ($arrayRequest["id_to"] === $id_user) {
            $this->position = 'to';
            $this->nicknameTo = 'YOU';
            $this->agreedUser = $arrayRequest["agreed_to"];
            $this->checkedUser = $arrayRequest["checked_to"];
            $this->script = $this->nicknameFrom . '\'s ' . $this->dateTime->format('M j (D)') . ' ' . $this->shift . ' to you';
        } else {
            $this->position = '3rd';
            $this->agreedUser = NULL;
            $this->checkedUser = NULL;
            $this->script = NULL;
        }

        // Notification script
        switch ($this->status) {
            case '0':
                $this->colorStatus = 'danger';
                $this->scriptNotice = "<span class='text-$this->colorStatus'>Denied: </span>" . $this->script;
                break;
            case '1':
                $this->colorStatus = 'success';
                $this->scriptNotice = "<span class='text-$this->colorStatus'>Accepted: </span>" . $this->script;
                break;
            case '2':
                $this->colorStatus = 'warning';
                $this->scriptNotice = "<span class='text-$this->colorStatus'>Awaiting: </span>" . $this->script;
                break;
        }
        $this->dbh = NULL;
    }
}
