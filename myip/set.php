<?php
require "./priv/lib.php";
if (isset($_SERVER['PHP_AUTH_USER'])) {
  $user = $_SERVER['PHP_AUTH_USER'];
  $pass = $_SERVER['PHP_AUTH_PW'];
  $loginDB = new \SQLite3("./priv/config.db", SQLITE3_OPEN_READWRITE);
  $stmt = $loginDB -> prepare("SELECT hash FROM login WHERE hostname = ?");
  $stmt -> bindParam(1, $user, SQLITE3_TEXT);
  $result = $stmt -> execute();
  $r = $result -> fetchArray(SQLITE3_ASSOC);
  if (password_verify($pass, $r["hash"])) {
    $result -> finalize();
    $stmt -> close();
    $stmt = $loginDB -> prepare("INSERT INTO ipaddrs(hostname, address) VALUES(?, ?)");
    $stmt -> bindParam(1, $user, SQLITE3_TEXT);
    $stmt -> bindParam(2, $_SERVER['REMOTE_ADDR'], SQLITE3_TEXT);
    $result = $stmt -> execute();
    echo 'Success';
  }
  else echo 'Failure';
  $result -> finalize();
  $stmt -> close();
  $db -> close();
}
else {
  header('WWW-Authenticate: Basic realm="My Realm"');
  header('HTTP/1.0 401 Unauthorized');
  echo 'Failure';
  exit;
}
?>
