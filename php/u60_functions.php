<?php
/*
activity u60: manage sorting
=======================
Group D3. Authors:
Jakub Kobedza
Bol Daoudov
=======================
*/

//save sorting value
if(isset($_POST['sorting'])){
    setcookie('sorting', $_POST['sorting'], time()+24*60*60);
}
else if(!isset($_COOKIE['sorting'])){
    setcookie('sorting', 'default', time()+24*60*60);
}

/* - JK
 * After calling this function it will show the sorting-drop-down-window on the website.
 * It needs a bool to decide if the drop-down needs to be open or closed.
 */
function u60_sort_open($open):void
{
    if($open)
    {
        echo
        '<form class="Content big" method="post">
        <button type="submit" class= "u60_top" value="sort-menu-closed" name="u00_menu">Sortieren nach<img class="arrows" src = "../images/Pfeil-nach-unten-icon.png" alt="arrow-down"></button>
        <button type="submit" value="default" name="sorting">Default Sortierung</button>
        <button type="submit" value="a.name" name="sorting">Name(Alphabetisch) aufsteigend</button>
        <button type="submit" value="a.name desc" name="sorting">Name(Alphabetisch) absteigend</button>
        <button type="submit" value="k.name, a.name" name="sorting">Kategorie(Alphabetisch) aufsteigend</button>
        <button type="submit" value="k.name desc, a.name" name="sorting">Kategorie(Alphabetisch) absteigend</button>
        <button type="submit" value="a.deadline, a.name" name="sorting">Deadline aufsteigend</button>
        <button type="submit" value="a.deadline desc, a.name" name="sorting">Deadline absteigend</button>
        <button type="submit" value="a.priority, a.name" name="sorting">Priorität aufsteigend</button>
        <button type="submit" value="a.priority desc, a.name" name="sorting">Priorität absteigend</button>
    </form>';
    }
    else{
        echo
        '
    <form class="Content small" method="post">
        <button type="submit" class= "u60_top" value="sort-menu-open" name="u00_menu">Sortieren nach<img class="arrows" src = "../images/Pfeil-nach-links-icon.png" alt="arrow-left"></button>
    </form>';
    }
}

