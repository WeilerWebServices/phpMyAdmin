<?php
if (empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] === "off") {
    $redirect = "https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
    header('HTTP/1.1 308 Permanent Redirect');
    header('Location: ' . $redirect);
}
?>
<html>
<head>

</head>
<body>
<h1>Setup</h1>
<?php
  $status = (isset($_POST["status"]) ? $_POST["status"] : "begin");

  $db = new SQLite3('./priv/config.db');

  if ($status == "done") {
    if (isset($_POST["password"]) && isset($_POST["confirm"])) {
      $pass = $_POST["password"];
      if ($pass !== $_POST["confirm"]) {
        echo "<p>Passwords did not match.</p>";
        $status = "begin";
      }
      else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $insert = $db -> prepare("INSERT INTO login(hostname, hash) VALUES(?, ?)");
        $insert -> bindValue(1, 'admin', SQLITE3_TEXT);
        $insert -> bindParam(2, $hash, SQLITE3_TEXT);
        $insert -> execute();
        $insert -> close();
        echo "<p>Setup complete.</p>";
      }
    }
  }

  if ($status == "begin") {
    $db -> exec("CREATE TABLE IF NOT EXISTS login(" .
                "hostname TEXT PRIMARY KEY NOT NULL UNIQUE, " .
                "hash TEXT UNIQUE" .
                ")");
    $db -> exec("CREATE TABLE IF NOT EXISTS ipaddrs(" .
                "hostname TEXT PRIMARY KEY NOT NULL UNIQUE, " .
                "address TEXT UNIQUE, " .
                "FOREIGN KEY(hostname) REFERENCES login(hostname)" .
                ")");
    $stmt = $db -> prepare("SELECT COUNT(*) AS count FROM login WHERE hostname = 'admin'");
    $result = $stmt->execute();
    $r = $result -> fetchArray(SQLITE3_ASSOC);
    if ($r["count"] < 1) {
      echo "No admin account found";
      echo <<<EOT
      <h2>Create Admin</h2>
      <form method="POST" action="setup.php">
        <input type="hidden" name="status" value="done"/>
        <div>Password: <input type="password" name="password" placeholder="Password"></div>
        <div>Confirm: <input type="password" name="confirm" placeholder="Confirm Password"></div>
        <button type="submit">Submit</button>
      </form>
EOT;
    }
    else {
      echo "<p>Admin account found</p>";
    }
    $result -> finalize();
    $stmt -> close();
  }
  $db -> close();
?>
</body>
</html>
