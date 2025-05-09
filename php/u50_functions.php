<?php
$link = mysqli_connect("swed3.ddns.net", // Host der Datenbank
  "SWE_D3",                 // Benutzername zur Anmeldung
  "password",   // Passwort
  "SWE_D3"      // Auswahl der Datenbanken (bzw. des Schemas)
// optional port der Datenbank
);
if ($link->connect_error) {
  die("Connection failed: " . $link->connect_error);
}
const ERROR_CATEGORY_ALREADY_USED = "Diese Kategorie existiert bereits!!!";
const ERROR_CATEGORY_NAME_INVALID = "Kategorie muss mind. ein Buchstaben beinhalten!!!";

/* -JK
 * U00 calls this function. It prints the main-category-menu and leeds to u50_manage_submenus on input
 */

function u50_category_menu():void{
  echo "
  <form method='post' id='u50_category_menu' class='u50_category'>
    <button type='submit' name='category_menu' value='create'>Neue Kategorie erstellen</button>
    <button type='submit' name='category_menu' value='change'>Kategorie ändern</button>
    <button type='submit' name='category_menu' value='delete'>Kategorie löschen</button>
  </form>
  ";
}

/* -JK
 * This function manages all subtasks, based on the input.
 */
function u50_manage_submenus():void
{
  //When the submit button was pressed while creating a new category
  global $link;
  if (isset($_POST['u50_create_submit'])) {
    if (u50_check_new($_POST['u50_category_create'])) {
      $newCategoryName = $link->real_escape_string($_POST['u50_category_create']);
      $sql = "select id from farbenListe";
      $colors = $link->query($sql);
      $colors = $colors->fetch_all();
      $colorID = $colors[rand(1, count($colors) - 1)][0];
      $sql = "INSERT INTO kategorie (name, colorID) VALUES ('$newCategoryName', $colorID)";
      $link->query($sql);
    }
    else {
      u50_input_field(true, htmlspecialchars($_POST['u50_category_create']));
    }
  } //When the submit button was pressed while changing the name of an already existing category
  else if (isset($_POST['u50_change_submit'])) {
      //return if the old name and the new name are the same
      if($_POST['u50_change_submit'] == $_POST['u50_category_change']){
          return;
      }

      if (u50_check_new($_POST['u50_category_change'])) {
          $oldCategoryName = $_POST['u50_change_submit'];
          $newCategoryName = $link->real_escape_string($_POST['u50_category_change']);
          $sql = "UPDATE kategorie SET name = '$newCategoryName' WHERE name = '$oldCategoryName'";
          $link->query($sql);
      } else {
          u50_input_field(false, htmlspecialchars($_POST['u50_category_change']), htmlspecialchars($_POST['u50_change_submit']));
      }
  } //When the submit button was pressed while deleting a category
  else if (isset($_POST['u50_delete_submit'])) {
// Sanitize the input to prevent SQL injection
    $categoryToDelete = $_POST['u50_delete_submit'];
    $sql = "DELETE FROM kategorie WHERE name = '$categoryToDelete'";
    $link->query($sql);
  } //After choosing a category to change the name of it
  else if (isset($_POST['rename_category'])) {
    u50_input_field(false, htmlspecialchars($_POST['rename_category']), htmlspecialchars($_POST['rename_category']));
  } //After choosing a category to delete it
  else if (isset($_POST['delete_category'])) {
    u50_delete_warning(htmlspecialchars($_POST['delete_category']));
  } //After the choice: add new, rename, delete
  else if (isset($_POST['category_menu'])) {
    switch ($_POST['category_menu']) {
      case 'create':
        u50_input_field(true);
        break;

      case 'change':
        u50_category_list(false);
        break;

      case 'delete':
        u50_category_list(true);
        break;
      default:
        break;
    }
  }
}

/* -JK
 * This function prints a warning to make sure that the user wants to delete a category.
 * It needs the name of the category that was chosen to continue.
 */
function u50_delete_warning($category_name):void{
  echo "<div id='u50_background_black'>";

  echo "<form method='post' id='u50_delete_warning'>
    Soll &quot;$category_name&quot; wirklich gelöscht werden?
    <div>
        <button type='submit' name='u50_cancel'><img src='../images/Abbrechen-Button-schwarz.png' alt='Cancel'></button>
        <div id='u50_space'></div>
        <button type='submit' name='u50_delete_submit' value='$category_name'><img src='../images/Haken-Button.png' alt='Submit'></button>
    </div>
</form>";
  echo "</div>";

}


/* -JK
 * This function creates a list of all categories to choose from. It is called for deleting and changing categories.
 * It needs a bool to define what the next action must be, after choosing a category.
 * */
function u50_category_list($delete):void
{
  global $link;
  echo "<div id='u50_background_black'>";
  echo "</div>";

  $title = $delete?"Kategorie löschen":"Kategorie ändern";
  $submit_name = $delete?"delete_category":"rename_category";
  $sql = "SELECT name FROM kategorie";
  $result = mysqli_query($link, $sql);
  echo "<form method='post' id='u50_category_list'>
<div id='u50_title'>$title</div>";
  while ($categoryName = mysqli_fetch_assoc($result)){
    $name = htmlspecialchars($categoryName["name"]);
    echo "<button type='submit' name='$submit_name' value='$name'>$name</button>";
  }
  echo "<button type='submit' name='u50_cancel'>Abbrechen</button>
</form>";

}

/*  -JK
 * This function checks if the category name that was transmitted is valid or not.
 */
function checkifcategorieexists($Kategorie):bool{
  global $link;
  $categoryNameToCheck = $Kategorie;

// SQL-Abfrage zur Überprüfung, ob die Kategorie bereits existiert
  $sql = "SELECT COUNT(*) as count FROM kategorie WHERE name = '$categoryNameToCheck'";
  $result = $link->query($sql);

  if ($result === false) {
    die("Fehler bei der SQL-Abfrage: " . $link->error);
  }

// Anzahl der Zeilen im Ergebnis abrufen
  $row = $result->fetch_assoc();
  $rowCount = $row['count'];

// Überprüfen, ob die Kategorie existiert
  if ($rowCount > 0) {
    return true;
  } else {
    return false;
  }
}
function u50_check_new($category_name):bool{


  $pattern1 = '/^.*[a-zA-Z].*/';

  if(!preg_match($pattern1,$category_name) )
  {
    u50_print_error(ERROR_CATEGORY_NAME_INVALID);
    return false;
  }
  else if(checkifcategorieexists($category_name)){
    u50_print_error(ERROR_CATEGORY_ALREADY_USED);
    return false;
  }
  else
  {
    return true;
  }
}

/*  -JK
 * This function prints an input field for creating or changing a category.
 * The first parameter defines if you want to rename or create a category(true = create, false = rename).
 * The second parameter can be used to define the value that is inside the text box at the beginning.
 * If you want to change the name of an already existing category, then you need to pass the old name via the third parameter.
 */
function u50_input_field($create = false, $input_value = "", $old_name = ""):void{
  echo "<div id='u50_background_black'>";

  if($create){
    echo "<form method='post' id='u50_input_field'>
    <input type='text' name='u50_category_create' placeholder='Neue Kategorie...' value='$input_value'>
    <div>
        <button type='submit' name='u50_cancel'><img src='../images/Abbrechen-Button-schwarz.png' alt='Cancel'></button>
        <div id='u50_space'></div>
        <button type='submit' name='u50_create_submit'><img src='../images/Haken-Button.png' alt='Submit'></button>
    </div>
</form>
";
  }else{
    echo "<form method='post' id='u50_input_field'>
    <input type='text' name='u50_category_change' placeholder='Kategoriename...' value='$input_value'>
    <div>
        <button type='submit' name='u50_cancel'><img src='../images/Abbrechen-Button-schwarz.png' alt='Cancel'></button>
        <div id='u50_space'></div>
        <button type='submit' name='u50_change_submit' value='$old_name'><img src='../images/Haken-Button.png' alt='Submit'></button>
    </div>
</form>
";
  }

  echo "</div>";
}

/*  -JK
 * This function prints an error-message in the bottom left corner.
 * At the top of the script are two const strings for the possible error messages.
 */
function u50_print_error($message):void{
  echo "<flex id='u50_error_message'>
    $message
</flex>";
}


