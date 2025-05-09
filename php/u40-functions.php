<?php
/*
activity u40: manage filter
=======================
Group D3. Authors:
Jakub Kobedza
Bol Daoudov
=======================
*/


/*
* This function requires an array containing all available categories and two boolean parameters.
* The boolean parameters enable the display of the subtitles "Category" and "Priority".
* When $show_categories is set to true, the categories array is listed in the menu.
* Interacting with a subtitle button opens the corresponding list.
* Interacting with a filter option saves the filter.
*/

function u40_show_filter_menu($show_priorities, $show_categories):void{

  echo
  "<div class='p200'>

</div><form method='post' class='u40-filter-menu'>
    <div class='u40-title'>Filtern nach</div>
    ";

  if($show_priorities){
    echo "<button type='submit'  name='u00_menu' value='filter-menu-0-";
    echo $show_categories?"1": "0";
    echo "' name='u00_menu'><div class='u40-subtitle'>Priorität</div><img src='../images/Pfeil-nach-unten-icon.png' alt='open' class='u40-arrow-down'></button>
        <button type='submit' name='active-filter' value='p_Prio 1'><div class='u40-filter-option'>Prio 1</div></button>
        <button type='submit' name='active-filter' value='p_Prio 2'><div class='u40-filter-option'>Prio 2</div></button>
        <button type='submit' name='active-filter' value='p_Prio 3'><div class='u40-filter-option'>Prio 3</div></button>
        <button type='submit' name='active-filter' value='p_Keine Prio'><div class='u40-filter-option'>Keine Prio</div></button>";
  }else{
    echo "<button type='submit' value='filter-menu-1-";
    echo $show_categories?"1": "0";
    echo "' name='u00_menu'><div class='u40-subtitle'>Priorität</div><img src='../images/Pfeil-nach-links-icon.png' alt='closed' class='u40-arrow-left'></button>";
  }

  $sql = "select name from kategorie";
  $categories = mysqli_query($GLOBALS['database'], $sql);
  if($show_categories){
    echo "<button type='submit' value='filter-menu-";
    echo $show_priorities?"1": "0";
    echo "-0' name='u00_menu'><div class='u40-subtitle'>Kategorie</div><img src='../images/Pfeil-nach-unten-icon.png' alt='open' class='u40-arrow-down'></button>";
    foreach ($categories as $category){
      echo "<button type='submit' name='active-filter' value='k_".htmlspecialchars($category["name"])."'><div class='u40-filter-option'>".htmlspecialchars($category["name"])."</div></button>";
    }
    echo "<button type='submit'  name='active-filter' value='k_Keine Kategorie'><div class='u40-filter-option'>Keine Kategorie</div></button>";
  }
  else
  {
    echo "<button type='submit' value='filter-menu-";
    echo $show_priorities?"1": "0";
    echo "-1' name='u00_menu'><div class='u40-subtitle'>Kategorie</div><img src='../images/Pfeil-nach-links-icon.png' alt='closed' class='u40-arrow-left'></button>";
  }
  echo "</form></div>";
  mysqli_free_result($categories);
}
