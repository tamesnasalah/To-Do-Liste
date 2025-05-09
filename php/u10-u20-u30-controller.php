<?php

function manage_menus(): void
{
  $menu = $_COOKIE['task-menu'] ?? null;

  if($menu == 'u10'){
    u10_pop_up_menu();
  }elseif ($menu == 'u20' && isset($_COOKIE['task-id'])){
    u20_print_menu($_COOKIE['task-id']);
  }else if ($menu == 'u30' && isset($_COOKIE['task-id'])){
    u30_print_subtask_menu($_COOKIE['task-id']);
  }else if ($menu == 'u30'){
    u30_print_subtask_menu(404);
  }

  if(isset($_COOKIE['error_message'])){
    u50_print_error($_COOKIE['error_message']);
    setcookie('error_message', '', time() - 60 * 60);
  }
}

function reload_page(): void
{
  ob_start(); // Starts Output Buffering
  header('Location: /'); // Redirects user to given location.
}

function set_menu(string $menu = null): void
{
  if($menu == null){
    setcookie('task-menu', '', time() - 60 * 60);
    setcookie('task-id', '', time() - 60 * 60);
    setcookie('tmp_subtask_list', '', time() - 60 * 60);
    setcookie('subtask_list', '', time() - 60 * 60);
    u20_unset_cookies();
  }else{
    setcookie('task-menu', $menu, time() + 60 * 60);
  }

  reload_page();
}

function set_array_as_cookie(string $name, array $array): void
{
  setcookie($name, json_encode($array), time()+3600);
}

function get_array_from_cookie(string $name):array{
  if(!isset($_COOKIE[$name])){
    return [];
  }
  return json_decode($_COOKIE[$name], true);
}
