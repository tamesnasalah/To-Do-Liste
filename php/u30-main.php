<?php

const ERROR_SUBTASK_NAME_INVALID = 'Teilaufgabe muss mind. einen Buchstaben beinhalten!!!';
const ERROR_SUBTASK_NAME_ALREADY_USED = 'Eine andere Teilaufgabe besitzt diesen Namen bereits!!!';
function u30_check_submits():void{

  if (isset($_POST['addSubtaskButton'])) {
    $newSubtask = trim($_POST['addSubtask']);

    $pattern1 = '/^.*[a-zA-Z].*/';

    if(!preg_match($pattern1,$newSubtask)){
      setcookie('error_message', ERROR_SUBTASK_NAME_INVALID, time() + 60 * 60);
      set_menu('u30');
      return;
    }elseif (isset($_COOKIE['tmp_subtask_list']) && in_array($newSubtask, get_array_from_cookie('tmp_subtask_list'))){
      setcookie('error_message', ERROR_SUBTASK_NAME_ALREADY_USED, time() + 60 * 60);
      set_menu('u30');
      return;
    }

    if (!empty($newSubtask)) {
      $subtasks = get_array_from_cookie('tmp_subtask_list');
      $subtasks[] = $newSubtask;
      set_array_as_cookie('tmp_subtask_list', $subtasks);
    }
    set_menu('u30');
  }

  if (isset($_POST['deleteTaskButton'])) {
    $del_subtask = $_POST['deleteTask'];
    var_dump($del_subtask);
    if (!empty($del_subtask)) {
      $subtasks = get_array_from_cookie('tmp_subtask_list');
      $subtasks = array_diff($subtasks, [$del_subtask]);
      set_array_as_cookie('tmp_subtask_list', $subtasks);
    }
    set_menu('u30');
  }

  if(isset($_POST['saveChanges'])){
    if(isset($_COOKIE['tmp_subtask_list'])){
      set_array_as_cookie('subtask_list', get_array_from_cookie('tmp_subtask_list'));
    }
    setcookie('tmp_subtask_list', '', time() - 60 * 60);

    if(!isset($_COOKIE['task-id'])){
      set_menu('u10');
    }else{
      set_menu('u20');
    }
  }

  if(isset($_POST['discardChanges'])){
    setcookie('tmp_subtask_list', '', time() - 3600);
    if(!isset($_COOKIE['task-id'])){
      set_menu('u10');
    }else{
      set_menu('u20');
    }
  }
}

function u30_print_subtask_menu(int $id):void
{
  $teilaufgaben = [];
  if(isset($_COOKIE['tmp_subtask_list'])){
    $teilaufgaben = get_array_from_cookie('tmp_subtask_list');
  }else if(isset($_COOKIE['subtask_list'])){
    $teilaufgaben = get_array_from_cookie('subtask_list');
    set_array_as_cookie('tmp_subtask_list', $teilaufgaben);
  }else if(isset($_COOKIE['task-id'])){
    global $database;
    $sql = "SELECT name FROM unteraufgabe WHERE task_id=$id;";
    $result = $database->query($sql);

    while ($row = $result->fetch_assoc()) {
      $teilaufgaben[] = $row['name'];
    }
    set_array_as_cookie('tmp_subtask_list', $teilaufgaben);
  }


  echo '<div class="u30-popup-model" id="u30-popup-model">
    <div class="u30-popup-content">
<div class="Window">
<form method="post">
    <div class="Add">
        <input  name ="addSubtask" type="text" id="taskInput" placeholder="Neue Teilaufgabe...">
        <button name="addSubtaskButton" class="plus-button"><img class="subtasks-button" src="../images/Neue-Teilaufgabe-Button.png" alt="Plus"></button>
    </div>
</form>

<ul id="subtaskList">';

    foreach ($teilaufgaben as $taskName):
        echo '<li>
            <span class="t-text">' . htmlspecialchars($taskName) . '</span><br>
            <form method="post">
                <input type="hidden" name="deleteTask" value="'.htmlspecialchars($taskName).'">
                <button class="trash" name="deleteTaskButton" type="submit" ><img class="delete-for-now-button" src="../images/Löschen-icon.png" alt="bin"></button>
            </form>
        </li>';
    endforeach;
echo '</ul>

</div>
<form method="post">
    <div class="button-select">
        <button class="ds-button"  name="discardChanges" ><img class="discard-save-button" src="../images/Abbrechen-Button-schwarz.png" alt="x"></button>
        <button class="ds-button"  name="saveChanges" ><img class="discard-save-button" src="../images/Haken-Button.png" alt="check"></button>
    </div>
</form>

</div>
</div>';
}
