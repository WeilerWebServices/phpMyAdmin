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

    $stmt = $loginDB -> prepare("SELECT hostname, address FROM ipaddrs ORDER BY hostname ASC");
    $result = $stmt -> execute();
    while ($r = $result -> fetchArray(SQLITE3_ASSOC)) {
        echo $r["hostname"] . " " . $r["address"] . "<br/>";
    }
  }
  $result -> finalize();
  $stmt -> close();
  $loginDB -> close();
}
else {
  header('WWW-Authenticate: Basic realm="My Realm"');
  header('HTTP/1.0 401 Unauthorized');
  echo '';
  exit;
}
?>
