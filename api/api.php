<?php

include('MP3File.php');

header("Content-type: application/json");

class API {
  public function __construct() {
    self::mysql_connect();
  }

  private function mysql_connect(){
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $db = 'poly';

    $res = mysql_connect($host, $username, $password) or die(mysql_error());
    mysql_select_db($db) or die(mysql_error());

    mysql_set_charset('utf8', $res);
  }

  public function get_user_data($params = null) {
    if(!isset($params)) return;

    $sql = mysql_query('SELECT * FROM users WHERE id="'.$params['id'].'"');
    $res = mysql_fetch_assoc($sql);

    echo json_encode($res, JSON_PRETTY_PRINT);
  }

  public function check_token($params) {
    if(!isset($params) || $params['action'] == "login" || $params['action'] == "signup") return;

    $sql = mysql_query('SELECT * FROM users WHERE id="'.$params['userId'].'" AND token="'.$params['token'].'"');
    $res = mysql_fetch_assoc($sql);

    if(count($res) == 1 || $res['token'] == "") {
      mysql_query('UPDATE users SET token="" WHERE id="'.$params['userId'].'"');
      exit();
    }
  }

  public function set_d() {
    $sql = mysql_query("SELECT * FROM songs WHERE duration IS NULL");

    while($res = mysql_fetch_assoc($sql)) {

      $mp3file = new MP3File('../assets/songs/'.$res['dir']);
      $d = $mp3file->getDuration();

      mysql_query("UPDATE songs SET duration='".$d."' WHERE id='".$res['id']."' ");
    }
  }

  public function get_artists(){

    $sql = mysql_query('SELECT * FROM artists ORDER BY RAND()');
    $arts = array();

    while($res = mysql_fetch_assoc($sql)){
      $songs = array();

      $res['genre'] = explode(',',$res['genre']);

      for( $i = 0; $i < count($res['genre']); $i++ ) {
        $qsql = mysql_query("SELECT * FROM genres WHERE id='".$res['genre'][$i]."' ");
        $qres = mysql_fetch_assoc($qsql);

        $res['genre'][$i] = $qres['name'];
      }

      $msql = mysql_query("SELECT * FROM songs WHERE artistId='".$res['id']."'");

      while($mres = mysql_fetch_assoc($msql)) {

        $mres['artist'] = $res['name'];
        $mres['pic'] = $res['ppic'];

        $songs[] = $mres;
      }

      $res['songs'] = $songs;

      $arts[] = $res;
    }

    echo json_encode($arts, JSON_PRETTY_PRINT);
  }
  public function add_to_music($params = null) {
    if(!isset($params)) return;

    $sql = mysql_query('SELECT COUNT(*) FROM user_music WHERE userId="'.$params['userId'].'" AND songId="'.$params['songId'].'"');
    $res = mysql_fetch_assoc($sql);

    if($res['COUNT(*)'] == 0) {
      $isql = mysql_query("INSERT INTO user_music(userId, songId) VALUES ('".$params['userId']."','".$params['songId']."')");
      echo json_encode($isql, JSON_PRETTY_PRINT);
    }
  }

  public function remove_from_music($params = null) {
    if(!isset($params)) return;

    $sql = mysql_query('SELECT COUNT(*) FROM user_music WHERE userId="'.$params['userId'].'" AND songId="'.$params['songId'].'"');
    $res = mysql_fetch_assoc($sql);

    if($res['COUNT(*)'] != 0) {
      $isql = mysql_query("DELETE FROM user_music WHERE userId = '".$params['userId']."' AND songId = '".$params['songId']."'");
      echo json_encode($isql, JSON_PRETTY_PRINT);
    }
  }

  public function get_user_music($params = null) {
    if(!isset($params)) return;

    $sql = mysql_query("SELECT songs.id, songs.name, songs.duration, songs.dir, artists.id AS artistId, artists.ppic AS pic, artists.name AS artist FROM `user_music` INNER JOIN `songs` INNER JOIN `artists` ON user_music.songId = songs.id AND songs.artistId = artists.id WHERE user_music.userId = '".$params['userId']."' ORDER BY user_music.id DESC");
    $songs = array();

    while($res = mysql_fetch_assoc($sql)) {
      $songs[] = $res;
    }

    echo json_encode($songs, JSON_PRETTY_PRINT);
  }

  public function signup($params = null) {
    if(!isset($params)) return;

    $sql = mysql_query("SELECT * FROM users WHERE email='".$params['email']."'");
    $res = mysql_fetch_assoc($sql);

    if(count($res) == 1) {
      $password = md5($params['password']);
      $sql = mysql_query("INSERT INTO users(email, name, username, password) VALUES ('".$params['email']."', '".$params['name']."', '".$params['username']."', '".$password."') ") or die(mysql_error());

      if($sql) {
        self::login($params);
      }
    } else {
      self::login($params);
    }
  }

  public function login($params = null) {
    if(!isset($params)) return;

    $sql = mysql_query("SELECT * FROM users WHERE email='".$params['email']."' AND password='".md5($params['password'])."'");
    $res = mysql_fetch_assoc($sql);

    if(count($res) != 1) {
      $token = md5(microtime());
      mysql_query("UPDATE users SET token = '".$token."' WHERE email='".$params['email']."'");

      $res['token'] = $token;
    }

    echo json_encode($res, JSON_PRETTY_PRINT);
  }

  public function get_all_users($params = null){
    if(!isset($params)) return;

    $sql = mysql_query('SELECT * FROM users ORDER BY RAND()');
    $arts = array();

    while($res = mysql_fetch_assoc($sql)){
      if($res['id'] == $params['userId']) continue;

      $songs = array();
      $msql = mysql_query('SELECT songs.id, songs.name, songs.duration, songs.dir, artists.id AS artistId, artists.ppic AS pic, artists.name AS artist FROM `user_music` INNER JOIN `songs` INNER JOIN `artists` ON user_music.songId = songs.id AND songs.artistId = artists.id WHERE user_music.userId = "'.$res['id'].'" ORDER BY songs.id DESC');

      while($mres = mysql_fetch_assoc($msql)) {

        $songs[] = $mres;
      }

      $res['songs'] = $songs;

      $arts[] = $res;
    }

    echo json_encode($arts, JSON_PRETTY_PRINT);

  }
}

$app = new API();
$r = $_REQUEST;

if(isset($r['action']) && $r['action'] == "set_d") {
  $app->set_d();
}

if(isset($r['action'])) {
  $app->check_token($r);

  switch($r['action']) {
    case 'get_user_data':
      $app->get_user_data($r);
      break;
    case 'get_artists':
      $app->get_artists();
      break;
    case 'add_to_music':
      $app->add_to_music($r);
      break;
    case 'get_user_music':
      $app->get_user_music($r);
      break;
    case 'remove_from_music':
      $app->remove_from_music($r);
      break;
    case 'signup':
      $app->signup($r);
      break;
    case 'login':
      $app->login($r);
      break;
    case 'get_all_users':
      $app->get_all_users($r);
      break;
    default:
      echo 'Wrong action name';
      break;
  }
}
?>
