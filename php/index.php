<?php
/*
activity u00: main page
=======================
Group D3. Authors:
Jakub Kobedza
Bol Daoudov
=======================
*/
$database = mysqli_connect(
  'swed3.ddns.net',
  'SWE_D3',
  'password',
  'SWE_D3',
  '3306'
);

if(!$database){
  echo mysqli_connect_error();
}

$u00_active_menu = null;

include('u10-u20-u30-controller.php');
include('u50_functions.php');
include('u10-functions.php');
include('u30-main.php');
include('u20-functions.php');
include('u20-main.php');
include('u40-functions.php');
include('u60_functions.php');
include('u00-functions.php');

u10_check_submits();
u20_check_submits();
u30_check_submits();

//JK - Sets the active menu (u40, u50 or u60)
if(isset($_POST['u00_menu'])){
    if($_POST['u00_menu'] != "close"){
        $u00_active_menu = $_POST['u00_menu'];
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToDo-Liste</title>

    <script>
        // Füge eine CSS-Klasse basierend auf der Plattform hinzu (iOS oder nicht)
        document.documentElement.classList.add(navigator.platform);
    </script>

    <link href="../css/u00-main.css" rel="stylesheet">
    <link href="../css/u00-tasks.css" rel="stylesheet">
    <link href="../css/u10-stylesheet.css" rel="stylesheet">
    <link href="../css/u20-Stylesheet.css" rel="stylesheet">
    <link href="../css/U30.css" rel="stylesheet">
    <link href="../css/u40-stylesheet.css" rel="stylesheet">
    <link href="../css/u50_stylesheet.css" rel="stylesheet">
    <link href="../css/u60_style.css" rel="stylesheet">
</head>
<body>

<header>
    <form method="post" class="u00-top-first-row">
        <button type="submit" name="u00_menu" <?php if($GLOBALS['u00_active_menu'] == null || !str_contains($GLOBALS['u00_active_menu'], "sort"))
        {
            echo 'value="sort-menu-closed"';
        }
        else
        {
            echo "value='close'";
        }?>>
            <img src="../images/Sortieren-icon.png" alt="Sortier-Icon not Found" class="u00-sortier-icon">
        </button>
        <div></div>
        <button type="submit" name="u00_menu"
            <?php if($GLOBALS['u00_active_menu'] == null || !str_contains($GLOBALS['u00_active_menu'], "filter"))
            {
                echo 'value="filter-menu-0-0"';
            }
            else
            {
                echo "value='close'";
            }?>
        >
            <img src="../images/Filter-icon.png" alt="Filter-Icon not Found" class="u00-filter-icon">
        </button>
        <button type="submit" name="u00_menu" <?php if($GLOBALS['u00_active_menu'] == null || !str_contains($GLOBALS['u00_active_menu'], "category"))
        {
          echo 'value="category-menu"';
        }
        else
        {
          echo "value='close'";
        }?>>
          <img src="../images/Kategorie-Einstellungen.png" alt="Filter-Icon not Found" class="u00-kategorie-icon">
        </button>
    </form>
    <form method="post" class="u00-top-second-row">
        <?php
        u00_show_active_filter();
        ?>
    </form>
</header>

<main>
    <?php
        manage_menus();
        u00_show_menu();
        if(isset($_POST['u00-add-task'])){
          set_menu('u10');
        }


        if(isset($_POST['task-settings'])){
          setcookie('task-id', $_POST['task-settings'], time() + 60 * 60);
          set_menu('u20');
        }

        u00_check_input_and_mark_as_finished();
        u00_print_task_list();
    ?>
  <div id="empty-space"></div>
</main>
<form method="post">
  <button type="submit" id="add-task-button" name="u00-add-task">
    +
  </button>
</form>


</body>
</html>
