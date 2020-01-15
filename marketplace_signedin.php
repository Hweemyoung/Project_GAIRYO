<?php

class MarketItemHandler
{
    public function __construct($master_handler)
    {
        $this->master_handler = $master_handler;
        $this->dbh = $master_handler->dbh;
        $this->arrayMemberObjectsByIdUser = $master_handler->arrayMemberObjectsByIdUser;
        $this->load_market_items();
    }
    private function load_market_items()
    {
        $sql = "SELECT id_shift, id_transaction, id_from, id_to, id_shift, `status` FROM requests_pending WHERE `status`=2 AND id_to=NULL;";
        $arrayShiftObjects = $this->dbh->query($sql)->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_CLASS, 'ShiftObject');
        foreach ($arrayShiftObjects as $shiftObject) {
            $shiftObject->setMemberObj($this->arrayMemberObjectsByIdUser);
        }
    }
}
?>

<div class="bs4-timeline">
    <!--first section-->
    <div class="row align-items-center how-it-works d-flex">
        <div class="col-2 text-center bottom d-inline-flex justify-content-center align-items-center">
            <div class="circle">
                <p>Feb 28<br>土</p>
            </div>
        </div>
        <div class="col-6">
            <div class="row">
                <div class="col-12 col-put">
                    <ul type="none">
                        <li>H: Member1</li>
                        <li>D: Member3, Member4</li>
                    </ul>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-call">
                    <ul type="none">
                        <li>B: Member2, Member5</li>
                        <li>A: Member3</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!--path between 1-2-->
    <div class="row timeline">
        <div class="col-2">
            <div class="corner top-right"></div>
        </div>
        <div class="col-8">
            <hr />
        </div>
        <div class="col-2">
            <div class="corner left-bottom"></div>
        </div>
    </div>
    <!--second section-->
    <div class="row align-items-center justify-content-end how-it-works d-flex">
        <div class="col-6 text-right">
            <div class="row">
                <div class="col-12 col-put">
                    <ul type="none">
                        <li>H: Member1</li>
                        <li>D: Member3, Member4</li>
                    </ul>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-call">
                    <ul type="none">
                        <li>B: Member2, Member5</li>
                        <li>A: Member3</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-2 text-center full d-inline-flex justify-content-center align-items-center">
            <div class="circle">
                <p>Mar 2<br>月</p>
            </div>
        </div>
    </div>
    <!--path between 2-3-->
    <div class="row timeline">
        <div class="col-2">
            <div class="corner right-bottom"></div>
        </div>
        <div class="col-8">
            <hr />
        </div>
        <div class="col-2">
            <div class="corner top-left"></div>
        </div>
    </div>
    <!--third section-->
    <div class="row align-items-center how-it-works d-flex">
        <div class="col-2 text-center top d-inline-flex justify-content-center align-items-center">
            <div class="circle">
                <p>Mar 4<br>水</p>
            </div>
        </div>
        <div class="col-6">
            <div class="row">
                <div class="col-12 col-put">
                    <div class="btn-group">
                        <button class="btn" type="button">A</button>
                        <button class="btn" type="button">C</button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-call">
                    <ul type="none">
                        <li>B: Member2, Member5</li>
                        <li>A: Member3</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>