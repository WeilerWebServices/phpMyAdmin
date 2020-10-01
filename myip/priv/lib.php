<?php
  if (!file_exists("./priv/config.db")) {
      $redirect = "https://" . $_SERVER["HTTP_HOST"] . "/setup.php";
      header('HTTP/1.1 302 Found');
      header('Location: ' . $redirect);
  }
  if (empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] === "off") {
      $redirect = "https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
      header('HTTP/1.1 308 Permanent Redirect');
      header('Location: ' . $redirect);
  }
?>
