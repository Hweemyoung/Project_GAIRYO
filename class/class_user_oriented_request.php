<?php
class userOrientedRequest
{
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
        $this->nicknameFrom = $arrayMemberObjectsByIdUser[$arrayRequest["id_from"]]->nickname;
        $this->nicknameTo = $arrayMemberObjectsByIdUser[$arrayRequest["id_to"]]->nickname;
        if ($arrayRequest["id_created"] === $id_user){
            $this->nicknameCreated = 'YOU';
        } else {
            $this->nicknameCreated = $arrayMemberObjectsByIdUser[$arrayRequest["id_created"]]->nickname;
        }
        $this->idShift = $arrayRequest["id_shift"];
        $sql = "SELECT date_shift, shift FROM shifts_assigned WHERE id_shift=$this->idShift;";
        // echo $sql;
        // var_dump($this->dbh->query($sql)->errorInfo());
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->dateTime = DateTime::createFromFormat('Y-m-d', $result[0]["date_shift"]);
        // var_dump($this->dateTime->format('M j (D)'));
        $this->shift = $result[0]["shift"];
        $this->status = $arrayRequest["status"];
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
            $this->counterpart = NULL;
            $this->script = NULL;
        }

        // Notification script
        switch ($this->status) {
            case '0':
                $this->scriptNotice = 'Denied: ' . $this->script;
                break;
            case '1':
                $this->scriptNotice = 'Accepted: ' . $this->script;
                break;
            case '2':
                $this->scriptNotice = 'Awaiting: ' . $this->script;
                break;
        }
        $this->dbh = NULL;
    }
}
