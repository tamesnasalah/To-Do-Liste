<?php
/*
activity u00: main page
=======================
Group D3. Authors:
Jakub Kobedza
Bol Daoudov
=======================
*/

//JK - Setting a Cookie for the filter
if(isset($_POST['active-filter'])){
    setcookie('active-filter', $_POST['active-filter'], time()+24*60*60);
}
else if(!isset($_COOKIE['active-filter'])){
    setcookie('active-filter', 'no-filter', time()+24*60*60);
}

function u00_db_check_task($task_id, $value = null):void{
  if($value == null){
    $sql = "update aufgaben set completed = not completed where id = $task_id;";
  }else{
    $sql = "update aufgaben set completed = " . (($value == 1)?"true":"false") . " where id = $task_id;";
  }
  mysqli_query($GLOBALS['database'], $sql);


}

function u00_db_check_subtask($subtask_id, $value = null):void{
  if($value == null){
    $sql = "update unteraufgabe set completed = not completed where id = $subtask_id";
  }else{
    $sql = "update unteraufgabe set completed = ".(($value == 1)?"true":"false")." where id = $subtask_id";
  }
  mysqli_query($GLOBALS['database'], $sql);

}

function u00_db_has_subtasks($task_id):bool{
  $sql = "select count(*) as anzahl from unteraufgabe where task_id = $task_id";
  $result = mysqli_query($GLOBALS['database'], $sql);
  $count = mysqli_fetch_assoc($result)['anzahl'];
  mysqli_free_result($result);
  return ($count != 0);
}

function u00_db_get_uncompleted_subtask_count($task_id):int{
  $sql = "select count(*) as anzahl from unteraufgabe where task_id = $task_id and completed = false";
  $result = mysqli_query($GLOBALS['database'], $sql);
  $count = mysqli_fetch_assoc($result)['anzahl'];
  mysqli_free_result($result);
  return $count;
}

function u00_db_get_taskID_by_subtaskID($subtask_id):int
{
  $sql = "select task_id from unteraufgabe where id = $subtask_id";
  $result = mysqli_query($GLOBALS['database'], $sql);
  $task_id = mysqli_fetch_assoc($result)['task_id'];
  mysqli_free_result($result);
  return $task_id;
}

function u00_check_input_and_mark_as_finished():void{

  //If subtask-complete was pressed
    if(isset($_POST['check-subtask'])){
      $subtask_id = $_POST['check-subtask'];
      $task_id = u00_db_get_taskID_by_subtaskID($subtask_id);
      u00_db_check_subtask($subtask_id);
      $count = u00_db_get_uncompleted_subtask_count($task_id);

      u00_db_check_task($task_id, (($count == 0)?1:2));

    }elseif (isset($_POST['check-task'])){ //If task-complete was pressed
      //Save id
      $task_id = $_POST['check-task'];

      //If there are no subtask, then check the task
      if(!u00_db_has_subtasks($task_id)){
        u00_db_check_task($task_id);
      }
    }

}

function u00_create_filter_string():string{
  if(isset($_POST['active-filter'])){
    $filter = $_POST['active-filter'];
  }else if(isset($_COOKIE['active-filter'])){
    $filter = $_COOKIE['active-filter'];
  }else{
    return "";
  }

  $filter_query = ' where ';

  if(str_starts_with($filter, 'p_')){
    $filter_query .= 'a.priority';
    switch ($filter){
      case 'p_Prio 1':
        $filter_query .= " = 1 ";
        return $filter_query;
      case 'p_Prio 2':
        $filter_query .= " = 2 ";
        return $filter_query;
      case 'p_Prio 3':
        $filter_query .= " = 3 ";
        return $filter_query;
      case 'p_Keine Prio':
        $filter_query .= " = 0 ";
        return $filter_query;
    }
  }
  else{
    if($filter == "no-filter"){
      return '';
    }else if($filter == "k_Keine Kategorie"){
      $filter_query .= " k.name is null ";
      return $filter_query;
    }
    $filter_without_key = substr($filter,2);
    $filter_query .= " k.name = '$filter_without_key'";
    return $filter_query;
  }

  return "";
}

function u00_create_order_string():string{
  //set sorting equal post_sorting, cookie_sorting or default, depending on which is null and which not
  $sorting = $_POST['sorting'] ?? $_COOKIE['sorting'] ?? 'default';

  if($sorting == 'default'){
    return " order by a.completed, a.priority desc, a.deadline is null, a.deadline, a.name";
  }

  return " order by a.completed, $sorting";
}

function u00_print_task_list():void{

  $all_tasks_query = "select a.id as id, a.name as task_name, deadline, priority, completed, k.name as categorie_name, f.name as farbe
from aufgaben a
         left join kategorie k on a.category_id = k.id
         left join SWE_D3.farbenListe f on k.colorID = f.id" . u00_create_filter_string() . u00_create_order_string() . ";";

  $all_subtasks_query = "select s.id as id,s.task_id as taskID, s.name as subtask_name, completed from unteraufgabe s order by task_id, completed, subtask_name";
  //echo $all_tasks_query;    //Debug-Echo
  $all_tasks = mysqli_query($GLOBALS['database'], $all_tasks_query);
  $all_subtasks = mysqli_query($GLOBALS['database'], $all_subtasks_query);

  $subtask_by_taskID = array();
  while($row = mysqli_fetch_assoc($all_subtasks)){
    $var = [
      'id' => $row['id'],
      'subtask_name' => $row['subtask_name'],
      'completed' => $row['completed']
    ];
    if(!array_key_exists($row['taskID'],$subtask_by_taskID)){
      $subtask_by_taskID[$row['taskID']] = array();
    }
      array_push($subtask_by_taskID[$row['taskID']], $var);

  }

  mysqli_free_result($all_subtasks);

  while($task = mysqli_fetch_assoc($all_tasks)){
    u00_print_task(
        $task['id'],
        htmlspecialchars($task['task_name']),
        htmlspecialchars($task['deadline'] ?? ''),
        htmlspecialchars($task['categorie_name'] ?? ''),
        htmlspecialchars($task['farbe'] ?? ''),
        $task['priority'],
        $task['completed']);

    if(!array_key_exists($task['id'], $subtask_by_taskID)){
      continue;
    }

    foreach($subtask_by_taskID[$task['id']] as $subtask){
      u00_print_subtask(
          $subtask['id'],
          htmlspecialchars($subtask['subtask_name']),
          $subtask['completed']);
    }
  }

  mysqli_free_result($all_tasks);
}

/* -JK
 * This funktion allows to pass 2 values for the subtask: Titel and a bool for finished or not finished.
 * After posting those values, the system shows the subtask on the website.
 * */
function u00_print_subtask($id, $title, $finished):void{
    echo "<form method='post' class='u00-sub-task ";
    if($finished){
        echo "u00-finished";
    }
    echo "'>
        <input type='submit' name='check-subtask' class='u00-task-check' value='$id'>
        <div class='u00-mid-sec'>
            <h3>$title</h3>
        </div>
    </form>";
}

/* -JK
 * This funktion allows to pass 6 values for the main task: Titel, Deadline, Kategorie, Kategorie Color, Priority and a bool for finished or not finished.
 * After posting those values, the system shows the task on the website.
 * If the task does not contain a kategorie, priority or deadline, then it is possible to pass the value "".
 * In this case the system registers that there is no value. The tag will not be printed.
 * */
function u00_print_task($id, $title, $deadline, $category, $color, $priority, $finished):void{
    echo "<form method='post' class='u00-main-task";
    if($finished){
        echo " u00-finished";
    }
    echo "'>
        <input type='submit' name='check-task' class='u00-task-check' value='$id'>
        <div class='u00-mid-sec'>
            <h2>$title</h2>
            <ul class='u00-tag-container'>";

    if($category != ''){
        echo "<li class='u00-Kategorie ";
            if(!$finished){
                switch ($color){
                    case 'blue':
                        echo 'blue-tag';
                        break;

                    case 'green':
                        echo 'green-tag';
                        break;

                    case 'red':
                        echo 'red-tag';
                        break;

                    case 'yellow':
                        echo 'yellow-tag';
                        break;

                    case 'orange':
                        echo 'orange-tag';
                        break;

                    case 'purple':
                        echo 'purple-tag';
                        break;

                    default:
                        break;
                }
            }
        echo "'>$category</li>";
    }

    if($deadline != ''){
        if(strtotime($deadline) < time() && !$finished){
          echo "<li class='u00-Deadline' style='color: red; border-color: black'>".u00_transform_date($deadline)."</li>";
        }else{
          echo "<li class='u00-Deadline'>".u00_transform_date($deadline)."</li>";
        }

    }

    if($priority != 0){
        echo "<li class='u00-priority' >";
        for($i = 0; $i < $priority; $i++){
          echo '!';
        }
        echo "</li>";
    }

    echo "</ul>
        </div>
        <button type='submit' name='task-settings' value='$id'>
            <svg xmlns='http://www.w3.org/2000/svg'  class='u00-gear-icon'
                 width='50'
                 height='50'
                 viewBox='0 0 50 50'
                     >
                <path d='M25 34c-5 0-9-4-9-9s4-9 9-9s9 4 9 9s-4 9-9 9zm0-16c-3.9 0-7 3.1-7 7s3.1 7 7 7s7-3.1 7-7s-3.1-7-7-7z'/>
                <path d='M27.7 44h-5.4l-1.5-4.6c-1-.3-2-.7-2.9-1.2l-4.4 2.2l-3.8-3.8l2.2-4.4c-.5-.9-.9-1.9-1.2-2.9L6 27.7v-5.4l4.6-1.5c.3-1 .7-2 1.2-2.9l-2.2-4.4l3.8-3.8l4.4 2.2c.9-.5 1.9-.9 2.9-1.2L22.3 6h5.4l1.5 4.6c1 .3 2 .7 2.9 1.2l4.4-2.2l3.8 3.8l-2.2 4.4c.5.9.9 1.9 1.2 2.9l4.6 1.5v5.4l-4.6 1.5c-.3 1-.7 2-1.2 2.9l2.2 4.4l-3.8 3.8l-4.4-2.2c-.9.5-1.9.9-2.9 1.2L27.7 44zm-4-2h2.6l1.4-4.3l.5-.1c1.2-.3 2.3-.8 3.4-1.4l.5-.3l4 2l1.8-1.8l-2-4l.3-.5c.6-1 1.1-2.2 1.4-3.4l.1-.5l4.3-1.4v-2.6l-4.3-1.4l-.1-.5c-.3-1.2-.8-2.3-1.4-3.4l-.3-.5l2-4l-1.8-1.8l-4 2l-.5-.3c-1.1-.6-2.2-1.1-3.4-1.4l-.5-.1L26.3 8h-2.6l-1.4 4.3l-.5.1c-1.2.3-2.3.8-3.4 1.4l-.5.3l-4-2l-1.8 1.8l2 4l-.3.5c-.6 1-1.1 2.2-1.4 3.4l-.1.5L8 23.7v2.6l4.3 1.4l.1.5c.3 1.2.8 2.3 1.4 3.4l.3.5l-2 4l1.8 1.8l4-2l.5.3c1.1.6 2.2 1.1 3.4 1.4l.5.1l1.4 4.3z'/>
            </svg>
        </button>
    </form>";
}

/* -JK
     * if the name of the active filter is equal "no-filter", then the page shows the title "All Tasks" without the remove-filter-button
     * on every other filter name the page shows the name of the filter that is saved in the cookie 'active-filter' or the post-value 'active-filter' and also shows the remove-filter-button
     *
     * u40 is already implemented and changes the active filter title.
     * Because cookies are not instantly available we need o workaround where we first check the post-value and after that the cookie
     * */
function u00_show_active_filter():void{

    if (isset($_POST['active-filter'])) {
        if($_POST['active-filter'] != "no-filter"){
            echo '<button type="submit" name="active-filter" value="no-filter">
        <svg
                xmlns="http://www.w3.org/2000/svg" class="u00-remove-filter-icon"
                width="256"
                height="256"
                viewBox="0 0 256 256"
        >
            <path
                    fill="#ff0000"
                    d="M165.66 101.66L139.31 128l26.35 26.34a8 8 0 0 1-11.32 11.32L128 139.31l-26.34 26.35a8 8 0 0 1-11.32-11.32L116.69 128l-26.35-26.34a8 8 0 0 1 11.32-11.32L128 116.69l26.34-26.35a8 8 0 0 1 11.32 11.32ZM232 128A104 104 0 1 1 128 24a104.11 104.11 0 0 1 104 104Zm-16 0a88 88 0 1 0-88 88a88.1 88.1 0 0 0 88-88Z"
            />
        </svg>
        </button>';

            echo "<h1> ". htmlspecialchars(substr($_POST['active-filter'],2)) ." </h1>";
        }else{
            echo '<h1>All Tasks</h1>';
        }


    }
    else if(isset($_COOKIE['active-filter'])){
        if($_COOKIE['active-filter'] != "no-filter"){
            echo '<button type="submit" name="active-filter" value="no-filter">
        <svg
                xmlns="http://www.w3.org/2000/svg" class="u00-remove-filter-icon"
                width="256"
                height="256"
                viewBox="0 0 256 256"
        >
            <path
                    fill="#ff0000"
                    d="M165.66 101.66L139.31 128l26.35 26.34a8 8 0 0 1-11.32 11.32L128 139.31l-26.34 26.35a8 8 0 0 1-11.32-11.32L116.69 128l-26.35-26.34a8 8 0 0 1 11.32-11.32L128 116.69l26.34-26.35a8 8 0 0 1 11.32 11.32ZM232 128A104 104 0 1 1 128 24a104.11 104.11 0 0 1 104 104Zm-16 0a88 88 0 1 0-88 88a88.1 88.1 0 0 0 88-88Z"
            />
        </svg>
        </button>';

            echo "<h1> ". htmlspecialchars(substr($_COOKIE['active-filter'],2)) ." </h1>";
        }else{
            echo '<h1>All Tasks</h1>';
        }
    }

}

/* - JK
 * Allows to manage the visibility of the filter and sorting menus
 */
function u00_show_menu():void{
    u50_manage_submenus();
    switch ($GLOBALS['u00_active_menu']){
        case 'filter-menu-0-0':
            u40_show_filter_menu(false,false);
            break;
        case 'filter-menu-0-1':
            u40_show_filter_menu(false,true);
            break;
        case 'filter-menu-1-0':
            u40_show_filter_menu(true,false);
            break;
        case 'filter-menu-1-1':
            u40_show_filter_menu(true,true);
            break;
        case 'category-menu':
          u50_category_menu();
            break;
        case 'sort-menu-closed':
            u60_sort_open(false);
            break;
        case 'sort-menu-open':
            u60_sort_open(true);
            break;
        default:
            break;
    }


}

function u00_transform_date($date):string{
  $year = substr($date, 0, 4);
  $month = substr($date, 5, 2);
  $day = substr($date,-2);
  return $day . "." . $month . "." . $year;
}



