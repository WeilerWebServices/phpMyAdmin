<?php require "./priv/lib.php"; ?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Add New Machine</title>
  </head>
  <body>
    <?php
      if (isset($_POST["admin_pass"]) && isset($_POST["machine_name"]) && isset($_POST["machine_pass"]) && isset($_POST["confirm_pass"])) {
        $admin_pass = $_POST["admin_pass"];
        $machine_name = $_POST["machine_name"];
        $machine_pass = $_POST["machine_pass"];
        $confirm_pass = $_POST["confirm_pass"];

        if (preg_match("/[a-z][a-z0-9]{2,}/i", $machine_name) != 1) {
          echo "<p>Machine name was not valid. Valid machine names can only contain letters and numbers.</p>";
        }
        else if ($machine_pass != $confirm_pass) {
          echo "<p>Passwords did not match</p>";
        }
        else {
          $db = new \SQLite3("./priv/config.db", SQLITE3_OPEN_READWRITE);
          // This also checks trying to assign "admin"
          $stmt = $db -> prepare("SELECT COUNT(*) AS count FROM login WHERE hostname = ?");
          $stmt -> bindParam(1, $machine_name, SQLITE3_TEXT);
          $result = $stmt -> execute();
          $r = $result -> fetchArray(SQLITE3_ASSOC);
          if ($r["count"] > 0) {
            echo "<p>Machine already exists. Please provide a different name.</p>";
          }
          else {
            $result -> finalize();
            $stmt -> close();
            $stmt = $db -> prepare("SELECT hash FROM login WHERE hostname = ?");
            $stmt -> bindValue(1, 'admin', SQLITE3_TEXT);
            $result = $stmt -> execute();
            $r = $result -> fetchArray(SQLITE3_ASSOC);
            if (!password_verify($admin_pass, $r["hash"])) {
              echo "<p>Invalid password.</p>";
            }
            else {
              $result -> finalize();
              $stmt -> close();
              $stmt = $db -> prepare("INSERT INTO login(hostname, hash) VALUES(?, ?)");
              $stmt -> bindParam(1, $machine_name, SQLITE3_TEXT);
              $stmt -> bindValue(2, password_hash($machine_pass, PASSWORD_DEFAULT), SQLITE3_TEXT);
              $stmt -> execute();
              echo "<p>Successfully added new machine \"" . $machine_name . "\".</p>";
            }
          }
          $result -> finalize();
          $stmt -> close();
          $db -> close();
        }
      }
    ?>
    <form method="POST" action="add.php">
      <div>Admin password: <input name="admin_pass" type="password" placeholder="Admin Password"/></div>
      <div>Machine name: <input name="machine_name" type="text" placeholder="localhost" pattern="[A-Za-z][A-Za-z0-9]{2,}"/></div>
      <div>Machine password: <input name="machine_pass" type="password" placeholder="Machine Password"/></div>
      <div>Confirm password: <input name="confirm_pass" type="password" placeholder="Confirm Password"/></div>
      <button type="submit">Submit</button>
    </form>
  </body>
</html>
