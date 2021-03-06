<?php
/**
 * @author: Ching-Ya Lin
 * @since: 2014/9/28
 *
 */
include_once("../../config.php");

if (defined("ADMIN_USER") && ADMIN_USER != "" && (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] != ADMIN_USER))
    die("Go away, you evil hacker!");

include_once("../query_manager.php");
include_once("../../common/functions.php");
include_once("../../capture/common/functions.php");

create_error_logs();

$captureroles = unserialize(CAPTUREROLES);

$querybins = getSavedBins();
?>
<html>
<head>
    <title>FFtcat - Flood Fire Twitter Capturing and Analysis Toolset</title>
    <meta charset='<?php echo mb_internal_encoding(); ?>'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
    <script type='text/javascript' src='../../analysis/scripts/jquery-1.7.1.min.js'></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="http://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    <style type="text/css">
        body, html {
            font-family: serif, sans-serif, fantasy, monospace;
            padding: 10px;
            font-size: 12px;
        }

        h1 {
            font-size: 34px;
            margin-bottom: 10px;
            margin-top: 0px;
        }

        .navbar {
            font-size: 14px;
        }

        .brand {
            font-size: 22px;
        }

        table {
            overflow: hidden;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-control {
            width: 400px;
            margin: 5px;
        }

        th {
            background-color: #ccc;
            padding: 8px;
        }

        td {
            background-color: #eee;
            padding: 8px;
        }

        .row {
            padding-left: 30px;
            padding-right: 30px;
            margin-top: 20px;
        }

        .keywords {
            width: 600px;
        }
    </style>

</head>
<body>
<div class="navbar navbar-default" role="navigation">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="#">Flood and Fire TCAT</a>
    </div>
    <div class="navbar-collapse collapse">
        <ul class="nav navbar-nav">
            <li><a href="index.php">Search</a></li>
            <li class="active"><a href="#archived">Saved Archive</a></li>
            <li><a href="../../analysis/index.php">Analysis</a></li>
        </ul>
    </div>
</div>

<div class="tab-content">
    <div class="tab-pane active" id="archived">
        <h1>FFtcat - Saved Archive</h1>

        <div class="row">
            <table align="center" class="table table-hover">
                <thead>
                <tr>
                    <th>Querybin</th>
                    <th class="keywords">Phrases</th>
                    <th>No. tweets</th>
                    <th>Created Time</th>
                    <th>Saved Time</th>
                    <th>Description</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($querybins as $bin) {
                    echo '<tr>';
                    echo '<td>' . $bin->name . '</td>';
                    echo '<td>' . implode(', ', explode("OR", $bin->phrases)) . '</td>';
                    echo '<td align="center"> ' . number_format($bin->nrOfTweets) . '</td>';
                    echo '<td align="center"> ' . $bin->createtime . '</td>';
                    echo '<td align="center"> ' . $bin->savedtime . '</td>';
                    echo '<td> ' . $bin->description . '</td>';
                    echo '</tr>';
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


</body>
</html>
