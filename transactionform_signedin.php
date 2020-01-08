<?php
$sql = "SELECT date_shift, id_user, shift FROM shifts_assigned WHERE done = 0";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$arrayShiftsByDate = $stmt->fetchAll(PDO::FETCH_GROUP);

?>

<main>
    <div class="container px-1">
        <hr>
        <section>
            <form action="" method="POST">
                <div class="form-item" id="form-item1">
                    <div class="row no-gutters">
                        <div class="col-md-4">
                            <div class="form-group row no-gutters">
                                <div class="col-3 text-center">
                                    <label for="id_from">From</label>
                                </div>
                                <div class="col-9">
                                    <select name="id_from" class="form-control">
                                        <option value="0">Whom?</option>
                                        <option value="1">Member1</option>
                                        <option value="2">Member2</option>
                                        <option value="3">Member3</option>
                                        <option value="4">Member4</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group row no-gutters">
                                <div class="col-4">
                                    <select name="month" class="form-control" disabled>
                                        <option value="0">Month</option>
                                        <option value="1">Jan</option>
                                        <option value="2">Feb</option>
                                        <option value="3" disabled>Mar</option>
                                        <option value="4" disabled>Apr</option>
                                        <option value="5" disabled>May</option>
                                        <option value="6" disabled>Jun</option>
                                        <option value="7" disabled>Jul</option>
                                        <option value="8" disabled>Aug</option>
                                        <option value="9" disabled>Sep</option>
                                        <option value="10" disabled>Oct</option>
                                        <option value="11" disabled>Nov</option>
                                        <option value="12" disabled>Dec</option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <select name="day" class="form-control" disabled>
                                        <option value="0">day</option>
                                        <option value="1">01</option>
                                        <option value="2">02</option>
                                        <option value="3">03</option>
                                        <option value="4" disabled>04</option>
                                        <option value="5" disabled>05</option>
                                        <option value="6" disabled>06</option>
                                        <option value="7">07</option>
                                        <option value="8" disabled>08</option>
                                        <option value="9" disabled>09</option>
                                        <option value="10" disabled>10</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                                        <option value="13" disabled>13</option>
                                        <option value="14" disabled>14</option>
                                        <option value="15" disabled>15</option>
                                        <option value="16">16</option>
                                        <option value="17">17</option>
                                        <option value="18">18</option>
                                        <option value="19">19</option>
                                        <option value="20" disabled>20</option>
                                        <option value="21" disabled>21</option>
                                        <option value="22" disabled>22</option>
                                        <option value="23">23</option>
                                        <option value="24" disabled>24</option>
                                        <option value="25" disabled>25</option>
                                        <option value="26" disabled>26</option>
                                        <option value="27">27</option>
                                        <option value="28" disabled>28</option>
                                        <option value="29" disabled>29</option>
                                        <option value="30" disabled>30</option>
                                        <option value="31">31</option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <select name="shift" id="shift" class="form-control" disabled>
                                        <option value="0">Shift</option>
                                        <option value="1" disabled>A</option>
                                        <option value="2" disabled>B</option>
                                        <option value="3">H</option>
                                        <option value="4" disabled>C</option>
                                        <option value="5" disabled>D</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group row no-gutters">
                                <div class="col-2 text-center">
                                    <label for="id_to">To</label>
                                </div>
                                <div class="col-8">
                                    <select name="id_to" class="form-control select-id-to" disabled>
                                        <option value="0">Whom?</option>
                                        <option value="1">Member1</option>
                                        <option value="2">Member2</option>
                                        <option value="3">Member3</option>
                                        <option value="4">Member4</option>
                                    </select>
                                </div>
                                <div class="col-2 d-flex flex-row-reverse">
                                    <button class="btn btn-danger btn-delete" title="Delete transaction"><i class="fas fa-minus"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row no-gutters">
                        <div class="col-12">
                            <hr>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="modal-confirm">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title">Confirm Requests</h1>
                            </div>
                            <div class="modal-body">
                                <table class="table table-responsive-sm table-sm table-hover text-center">
                                    <thead>
                                        <tr>
                                            <th>From</th>
                                            <th>Month</th>
                                            <th>Day</th>
                                            <th>Shift</th>
                                            <th>To</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-danger" type="button" title="Back" data-dismiss="modal"><i class="fas fa-undo"></i></button>
                                <button class="btn btn-primary" type="submit" title="Check"><i class="fas fa-file-export"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
            <div id="buttons" class="text-right">
                <button id="btn-add-item" class="btn btn-primary" title="Add"><i class="fas fa-plus"></i></button>
                <a id="btn-confirm" class="btn btn-primary disabled" href="#modal-confirm" data-toggle="modal"><i class="fas fa-check"></i></a>
            </div>
        </section>
    </div>
</main>
<footer></footer>
<script src="./js/transactionform.js"></script>
<script>
arrayShiftsByDate = <?=json_encode($arrayShiftsByDate)?>
</script>