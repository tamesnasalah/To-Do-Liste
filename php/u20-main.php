<?php


// verbesser mal dein css du hure ;)


/*
 * to do liste:
 * -bestätigen und alle änderungen speichern
 *
 * //cookie ist ein globales array → deswegen die array stellen mit richtigen namen setzen
 * //HIER EIGENTLICHER CODE
 */
function u20_print_menu($task_id):void{//al: instead of task name, how about th task id? bc what if two tasks have the same name

    global $database;

    if(isset($_COOKIE['prio-input'])){
      $task_name_input = htmlspecialchars($_COOKIE['task-name'] ?? null);
      $task_priority = $_COOKIE['prio-input'];
      $task_category = $_COOKIE['category-input'] ?? null;
      $task_deadline = htmlspecialchars($_COOKIE['deadline-input'] ?? null);
    }else{
      $task_name_query = "SELECT a.name as name, priority, deadline, category_id as category FROM aufgaben a  WHERE a.id= $task_id ;";

      $task_query_result = mysqli_query($database,$task_name_query);
      $task = mysqli_fetch_assoc($task_query_result);

      $task_name_input = htmlspecialchars($task['name']);
      $task_priority = $task['priority'];
      $task_category = $task['category'];
      $task_deadline = htmlspecialchars($task['deadline']);
      $task_query_result->free_result();
    }

    echo '
<!--for test purposes-->
<form method="post" id="u20-all">
<div class="u20-popup-model">
    <div class="u20-popup-content">
        <!--al: shows the name of the task and the user is able to change that-->
        <label>
            <input type="text" class="new-task-input" name="task-name" value="'.$task_name_input.'">
        </label>
        <hr>
       <div class="deadline">
            <!--al: user can change the deadline or shows the old deadline or user can make a new deadline if there was no deadline -->
            <label for="date-input" id="date-label">Deadline ändern</label>
            <input type="date" name="deadline-input" id="date-input" value="'.$task_deadline.'">
        </div>
        <hr>
        <div class="drop-down-menu">';

            echo '<select name="category-input" id="drop-down-category" >';
            u20_show_category($task_category);
            echo '</select>';


            echo '<hr>
            <!--al: user can change/set priority of the task, shows the priority before, fix with back end-->
            <select name="prio-input" id="drop-down-priority">';

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
        <button name="manage-subtask-submit" class="manage-subtask-submit" type="submit" id="open-subtasks">Teilaufgaben bearbeiten/hinzufügen</button>
        <hr>
        <div id="u20-button-row">
            <!--al: Button for exit, but with icon-->
            <button name="exit-button" id="exit-button" class="setting-button" type="submit" value=""><img src="../images/Abbrechen-Button-schwarz.png" alt="X"></button>
            <!--al: Button for delete icon, image source later -->
            <button name="delete-button" id="delete-button" class="setting-button" type="submit" value="'.$task_id.'"><img src="../images/Löschen-icon.png" alt="Müll"></button>
            <!--al: Button for verify and verify changes-->
            <button name="verify-button" id="verify-button" class="setting-button" type="submit" value="'.$task_id.'"><img src="../images/Haken-Button.png" alt="V"></button>
        </div>
    </div>
</div>
</form>
<script src="../js/u20-script.js"></script>';
}
