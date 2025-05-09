<?php
//"ROAD WORK AHEAD", "ah yeah I sure hope it does!"

//FERTIG
function u20_show_category($category): void
{

  //for connection testpurposes
  global $database;

  $sql = "SELECT id, name FROM kategorie";
  $result = $database->query($sql);

  if ($result->num_rows > 0) {
    echo "<option value=''>Kategorie ändern</option>";
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

function u20_delete_task():void{                  //al: deletes the current task
  //for connection purposes
  global $database;
  $task_to_delete = $_POST['delete-button'];
  $delete_query = "DELETE FROM aufgaben WHERE id = $task_to_delete ";
  if(!mysqli_query($database,$delete_query)){
    echo "Fehler beim Löschen".mysqli_error($database);
  }

}

function set_cookies():void{
  //setting post values in cookies
  if(isset($_POST['task-name'])){
    setcookie('task-name', $_POST['task-name'], time() + 60*60);
  }
  if(isset($_POST['deadline-input'])){
    setcookie('deadline-input', $_POST['deadline-input'], time() + 60*60);
  }
  if(isset($_POST['category-input'])){
    setcookie('category-input', $_POST['category-input'], time() + 60*60);
  }
  if(isset($_POST['prio-input'])) {
    setcookie('prio-input', $_POST['prio-input'], time() + 60 * 60);
  }
}
function u20_unset_cookies():void{
  //setting the dates of cookies to the past will inform the browser to delete the cookies
  setcookie('task-menu', '', time() - 3600);
  setcookie('task-name', '', time() - 3600);
  setcookie('deadline-input', '', time() - 3600);
  setcookie('category-input', '', time() - 3600);
  setcookie('prio-input', '', time() - 3600);
  setcookie('subtask_list', '', time() - 3600);
}

function u20_check_submits(): void
{
  global $database;
  //delete funktion aufrufen
  if (isset($_POST['delete-button'])) {
    u20_unset_cookies();
    u20_delete_task(); //Funktionsaufruf hier
    set_menu();
  }
  //exit
  if (isset($_POST['exit-button'])) {
    u20_unset_cookies();
    set_menu();
  }
  //subtask
  if(isset($_POST['manage-subtask-submit'])){
    set_cookies();
    set_menu('u30');
  }
  //save (geänderte funktion)
  if(isset($_POST['verify-button'])){

    $task_id = $_POST['verify-button'];

    // Holen der Benutzereingaben und sicherstellen, dass sie sicher sind
    $task_name = mysqli_real_escape_string($database, $_POST['task-name']);

    $pattern1 = '/^.*[a-zA-Z].*/';

    if(!preg_match($pattern1,$task_name)){
      setcookie('error_message', ERROR_TASK_NAME_INVALID, time() + 60 * 60);
      set_cookies();
      set_menu('u20');
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

    // SQL-Abfrage zum Einfügen der Daten
    $insert_query = "update aufgaben set name = '$task_name', deadline = $deadline, category_id = $category, priority = $priority where id = $task_id";


    try{
      mysqli_query($database, $insert_query);
      $task_id = $_COOKIE['task-id'];
      if(isset($_COOKIE['subtask_list'])){
        $result = $database->query("select name from unteraufgabe where task_id = $task_id;");
        $subtasks = [];
        if($result){
          $raw_subtasks = $result->fetch_all();
          foreach ($raw_subtasks as $raw_subtask){
            $subtasks[] = $raw_subtask[0];
          }

        }
        $cookie_subtasks = get_array_from_cookie('subtask_list');
        $subtasks_to_delete = array_diff($subtasks, $cookie_subtasks);
        $subtasks_to_create = array_diff($cookie_subtasks, $subtasks);

        $subtasks_added = false;

        foreach ($subtasks_to_create as $create_name){
          $database->query("insert into unteraufgabe (name, task_id) values ('$create_name', $task_id);");
          $subtasks_added = true;
        }

        foreach ($subtasks_to_delete as $delete_name){
          $database->query("delete from unteraufgabe where name = '$delete_name' and task_id = $task_id;");
        }

        if($subtasks_added){
          $database->query("update aufgaben set completed = false where id = $task_id;");
        }
      }


      u20_unset_cookies();
      set_menu();
    }catch(Exception $ex){
      set_cookies();
      setcookie('error_message',ERROR_TASK_NAME_ALREADY_USED, time() + 60 * 60);
      set_menu('u20');
    }

  }
}
