<?php
const ERROR_TASK_NAME_ALREADY_USED = 'Eine andere Aufgabe besitzt diesen Namen bereits!!!';
const ERROR_TASK_NAME_INVALID = 'Aufgabe muss mind. ein Buchstaben beinhalten!!!';

function u10_show_category($category): void
{
  global $database;

  $sql = "SELECT id, name FROM kategorie";
  $result = $database->query($sql);

  if ($result->num_rows > 0) {
    echo "<option value=''>Kategorie wählen</option>";
    while ($row = $result->fetch_assoc()) {
      if($row['id'] == $category){
        echo "<option selected value='" . $row["id"] . "'>" . htmlspecialchars($row["name"]) . "</option>";
      }else{
        echo "<option value='" . $row["id"] . "'>" . htmlspecialchars($row["name"]) . "</option>";
      }

    }
  }
  else{
    echo "<option value=''>Kategorie ändern</option>";
  }

}

function u10_check_submits():void
{
  global $database;
  //exit
  if(isset($_POST['u10-exit-button'])) {
    u20_unset_cookies();
    set_menu();
  }
  //subtask
  if(isset($_POST['u10-manage-subtask-submit'])){
    set_cookies();
    set_menu('u30');
  }
  //save (geänderte funktion)
  if(isset($_POST['u10-verify-button'])){

    // Holen der Benutzereingaben und sicherstellen, dass sie sicher sind
    $task_name = mysqli_real_escape_string($database, $_POST['task-name']);

    $pattern1 = '/^.*[a-zA-Z].*/';

    if(!preg_match($pattern1,$task_name)){
      setcookie('error_message', ERROR_TASK_NAME_INVALID, time() + 60 * 60);
      set_cookies();
      set_menu('u10');
      return;
    }

    $deadline = mysqli_real_escape_string($database, $_POST['deadline-input']);
    $category = mysqli_real_escape_string($database, $_POST['category-input']);
    $priority = mysqli_real_escape_string($database, $_POST['prio-input']);

    if($deadline == ''){
      $deadline = 'null';
    } else{
      $deadline = "'$deadline'";
    }

    if($category == ''){
      $category = 'null';
    } else{
      $category = "'$category'";
    }


    try{
      // SQL-Abfrage zum Einfügen der Daten
      $insert_query = "insert into aufgaben (name, deadline, category_id, priority) values ('$task_name', $deadline, $category, $priority)";
      mysqli_query($database, $insert_query);

      if(isset($_COOKIE['subtask_list'])){
        $subtasks = get_array_from_cookie('subtask_list');
        $result = $database->query("select id from aufgaben where name = '$task_name';");
        $id = $result->fetch_all()[0][0];

        foreach ($subtasks as $create_name){
          $database->query("insert into unteraufgabe (name, task_id) values ('$create_name', $id);");
        }
      }
      u20_unset_cookies();
      set_menu();

    }catch(Exception $ex){
      set_cookies();
      setcookie('error_message',ERROR_TASK_NAME_ALREADY_USED, time() + 60 * 60);
      set_menu('u10');
    }

  }
}

function u10_pop_up_menu(): void
{
  if(isset($_COOKIE['prio-input'])){
    $task_name_input = htmlspecialchars($_COOKIE['task-name'] ?? '');
    $task_priority = $_COOKIE['prio-input'];
    $task_category = $_COOKIE['category-input'] ?? '';
    $task_deadline = htmlspecialchars($_COOKIE['deadline-input'] ?? '');
  }else{
    $task_name_input = '';
    $task_priority = '';
    $task_category = '';
    $task_deadline = '';
  }

  echo '
<!--for test purposes-->
<form method="post" id="u20-all">
<div class="u20-popup-model">
    <div class="u20-popup-content">
        <!--al: shows the name of the task and the user is able to change that-->
        <label>
            <input placeholder="Neue Aufgabe..." type="text" class="new-task-input" name="task-name" value="'.$task_name_input.'">
        </label>
        <hr>
       <div class="deadline">
            <!--al: user can change the deadline or shows the old deadline or user can make a new deadline if there was no deadline -->
            <label for="date-input" id="date-label">Deadline hinzufügen</label>
            <input type="date" name="deadline-input" id="date-input" value="'.$task_deadline.'">
        </div>
        <hr>
        <div class="drop-down-menu">';

  echo '<select name="category-input" id="drop-down-category" >';
  u10_show_category($task_category);
  echo '</select>';


  echo '<hr>
            <!--al: user can change/set priority of the task, shows the priority before, fix with back end-->
            <select name="prio-input" id="drop-down-priority">
                <option value="0" disabled>Priorität setzen</option>';

  if($task_priority == 0){
    echo '<option selected value="0">Keine Priorität</option>';
  }else{
    echo '<option value="0">Keine Priorität</option>';
  }


  for ($i = 1; $i <= 3; $i++) {
    if($task_priority == $i)
    {
      echo '<option value="'.$i.'" selected="selected">Prio '.$i.'</option>';
    }
    else
    {
      echo '<option value="'.$i.'">Prio '.$i.'</option>';
    }
  }

  echo '</select>
        </div><hr>
        <!--al: submit button to u30 -->
        <button name = "u10-manage-subtask-submit" class="manage-subtask-submit" type="submit" id="open-subtasks">Teilaufgaben bearbeiten/hinzufügen</button>
        <hr>
        <div id="u20-button-row">
            <!--al: Button for exit, but with icon-->
            <button name="u10-exit-button" id="exit-button" class="setting-button" type="submit" value=""><img src="../images/Abbrechen-Button-schwarz.png" alt="X"></button>
            <!--al: Button for delete icon, image source later -->
            <!--al: Button for verify and verify changes-->
            <button name="u10-verify-button" id="verify-button" class="setting-button" type="submit" value=""><img src="../images/Haken-Button.png" alt="V"></button>
        </div>
    </div>
</div>
</form>
<script src="../js/u20-script.js"></script>';
}

