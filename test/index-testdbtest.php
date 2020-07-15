<?php 
    // GLOBAL VARS 
    $priv_date = 'June 10, 2020'; 
    $tou_date = 'June 11, 2020';

    // $date = new DateTime(null, new DateTimeZone('Europe/Amsterdam'));                      // DEBUG // 
    // _log('\nThis session started on: ' . $date->format('d M Y, H:i:s e'));                 // DEBUG // 

    // switch on minify for remote servers
    if ($_SERVER['SERVER_PORT'] == '443' || $_SERVER['SERVER_PORT'] == '80') // default SSL port number OR http: port number
    {
        $min_url = '.min';
    } else {
        $min_url = '';
    }
    
    // Get locale from the url cookie (url query), and if there is none, then get it from <html> element (with double quotes stripped)
    $locale = isset( $_GET['wp_lang'] )? $_GET['wp_lang'] : "en";

    // This file contains all language versions of the text, for readablity reasons. Use the main language code as a switch
    $lng = strtolower( substr($locale, 0, 2) );

    $connect = mysqli_connect('localhost','gp_testROuser','testROpwd', 'gp_testdb_test_cms');   // TEST db
    if (!$connect)
    {
        die("Could not connect to MySQL server: " . mysqli_connect_error());
    }    
    // change character set to utf8 
    if (!mysqli_set_charset($connect, 'utf8')) {
        die("Error loading character set utf8: " . mysqli_error($connect));
    }
?>

<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- font awesome inserted for icons on buttons etc. -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" rel="stylesheet" />
    <!-- <script src="https://kit.fontawesome.com/a02f8b3e52.js" crossorigin="anonymous"></script> -->

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous" />

    <!-- Groen Productions branding -->
    <link rel="stylesheet" href="./assets/groenp-sites<?php echo $min_url; ?>.css" />
    <link rel="shortcut icon" href="favicon.ico" />
    <!-- Groen Productions globals -->
    <script type="text/javascript">
        var PrivDate = "<?php echo $priv_date; ?>";
    </script>

<?php if ($lng == "es"): ?>
    <title>Groen Productions - Prueba de base de datos</title>
<?php elseif ($lng == "nl"): ?>
    <title>Groen Productions - Test van gegevensbank</title>
<?php else: ?>
    <title>Groen Productions - Database Test</title>
<?php endif; ?>
</head>

<body class="login" data-offset="50">
    <!-- <body class="modal-open"> -->

    <div class="container-flex">

        <!-- content section -->
        <div class="container-md content">

            <div class="row py-2">
                <div class="intro col-12">
                    <a class="" href="./wp-admin/">
                        <h1><img src="./assets/GroenProductionsPath.svg" alt="Groen Productions | Site Management Tool" /><br /></h1>
                    </a>
                    <p><a type="button" class="btn float-right" href="https://admin.groenproductions.com/test/">To Live</a></p>

                    <h2>Test Database</h2>
                    <h3>Contents</h3>
                    <p>These are the contents of gp_test in the TEST database:</p>

                    <table class='manage' style='width: 100%; table-layout: fixed; overflow: hidden; white-space: nowrap;'><thead style='text-align: left'>
                        <tr style='text-align: left'>
                            <th style='width:75px'>ID</th>
                            <th>Test name</th>
                        </tr></thead><tbody>

<?php 
    // prepare statement in similar way as edit, but no parameters
    $stmt = mysqli_prepare($connect, 'SELECT pk_test_id, test_name FROM gp_test ORDER BY test_name');
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $row['pk_test_id'], $row['test_name']);

    // Retrieve row by row the project data in the DB
    while ( mysqli_stmt_fetch($stmt) )
    {
        // Build row
        echo "<tr>
                <td class='numb'>". $row['pk_test_id'] ."</td>
                <td>". $row['test_name'] ."</td>
            </tr>";
    } // End of: while result
    mysqli_stmt_close($stmt);
    mysqli_close($connect);
    // finalize table
?>                  
                    </tbody></table>
                </div>
            </div> <!-- end of row -->

            <!-- Groen Productions.com -->
            <div class="row  groenp">
                <div class="col-12">
                    <a class="" href="https://www.linkedin.com/in/pietergroen" target="_blank">Made by
                        <?php include("./assets/GroenProductions.min.svg"); ?></a>
                </div>
            </div>
            
        </div> <!-- end of content section -->
    </div> <!-- end of page container -->

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"
        integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n"
        crossorigin="anonymous"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
        crossorigin="anonymous"></script> -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"
        integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6"
        crossorigin="anonymous"></script>
    <!-- My script has to wait for jQuery to load -->
    <script type="text/javascript" src="./assets/groenp-sites<?php echo $min_url; ?>.js"></script>
</body>

</html>