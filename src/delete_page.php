<?php
session_start();

require "./php/oracle_conn.php";

if (!isset($_SESSION['user_isadmin']) || $_SESSION['user_isadmin'] == 0) {
  header("Location: index.php");
}
?>

<!DOCTYPE html>
<html lang="hu">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Videó törlése - Videómegosztó</title>
</head>

<body>
  <?php
    require_once "menu.php";

    if (isset($_SESSION['delete_success'])) {
      echo $_SESSION['delete_success'];
      unset($_SESSION['delete_success']);
    }
    
    if (isset($_SESSION['delete_error'])) {
      echo $_SESSION['delete_error'];
      unset($_SESSION['delete_error']);
    }
  ?>

  <br>

  <form action="./delete_page.php" method="get">
    <label for="video_cim">Videó címe:</label>
    <input type="text" name="video_cim" id="video_cim">
    <input type="submit" value="Keresés" name="video_kereses" id="video_kereses">
  </form>

  <table border="1">
    <th>ID</th>
    <th>Cím</th>
    <th>Feltöltő</th>
    <th>Videó törlése</th>
    <?php
    // User keres-e cím alapján
    // Ezeket a sorokat fel lehet használni a videók kereséséhez is indexen
    if (isset($_GET['video_kereses']) && trim($_GET['video_cim']) != "") {
      $list_videos = oci_parse($conn, "SELECT video.id, video.cim, felhasznalo.nev AS felhasznalo_nev FROM video
      INNER JOIN feltolto ON video.id = feltolto.video_id 
      INNER JOIN felhasznalo ON feltolto.felhasznalo_id = felhasznalo.id
      WHERE LOWER(video.cim) LIKE LOWER('%' || :cim || '%')");

      oci_bind_by_name($list_videos, ':cim', $_GET['video_cim']);
      oci_execute($list_videos);
    } else {
      $list_videos = oci_parse($conn, "SELECT video.id, video.cim, felhasznalo.nev AS felhasznalo_nev FROM video
      INNER JOIN feltolto ON video.id = feltolto.video_id 
      INNER JOIN felhasznalo ON feltolto.felhasznalo_id = felhasznalo.id");

      oci_execute($list_videos);
    }

    // Belenyomjuk az eredményeket ebbe az arraybe, mert így az 1. sort nem skipeli
    $rows = array();
    while ($row = oci_fetch_array($list_videos, OCI_ASSOC + OCI_RETURN_NULLS)) {
      $rows[] = $row;
    }

    // Undorító HTML kiiratás
    if (count($rows) == 0) {
      echo "<p>Nincs találat.</p>";
    } else {
      foreach ($rows as $row) {
        echo "<tr><td>" . $row['ID'] . "</td>";
        echo "<td>" . $row['CIM'] . "</td>";
        echo "<td>" . $row['FELHASZNALO_NEV'] . "</td>";
        echo '<td>
        <form action="./php/delete_video.php" method="POST">
          <input type="hidden" id="video_id" name="video_id" value="' . $row['ID'] . '">
          <input type="submit" value="Törlés" name="video_torles" id="video_torles">
        </form>
      </td></tr>';
      }
    }
    ?>
  </table>

  <br>

  <form action="./delete_page.php" method="get">
    <label for="user_email">Felhasználó e-mail címe:</label>
    <input type="text" name="user_email" id="user_email">
    <input type="submit" value="Keresés" name="user_kereses" id="User_kereses">
  </form>

  <table border="1">
    <th>ID</th>
    <th>Név</th>
    <th>E-mail</th>
    <?php
    // User keres-e cím alapján
    // Ezeket a sorokat fel lehet használni a videók kereséséhez is indexen
    if (isset($_GET['user_kereses']) && trim($_GET['user_email']) != "") {
      $list_users = oci_parse($conn, "SELECT ID, NEV, EMAIL FROM FELHASZNALO
      WHERE LOWER(EMAIL) LIKE LOWER('%' || :email || '%') AND ADMIN = 0");

      oci_bind_by_name($list_users, ':email', $_GET['user_email']);
      oci_execute($list_users);
    } else {
      $list_users = oci_parse($conn, "SELECT ID, NEV, EMAIL FROM FELHASZNALO WHERE ADMIN = 0");

      oci_execute($list_users);
    }

    // Belenyomjuk az eredményeket ebbe az arraybe, mert így az 1. sort nem skipeli
    $rows = array();
    while ($row = oci_fetch_array($list_users, OCI_ASSOC + OCI_RETURN_NULLS)) {
      $rows[] = $row;
    }

    // Undorító HTML kiiratás
    if (count($rows) == 0) {
      echo "<p>Nincs találat.</p>";
    } else {
      foreach ($rows as $row) {
        echo "<tr><td>" . $row['ID'] . "</td>";
        echo "<td>" . $row['NEV'] . "</td>";
        echo "<td>" . $row['EMAIL'] . "</td>";
        echo '<td>
        <form action="./php/delete_user.php" method="POST">
          <input type="hidden" id="user_id" name="user_id" value="' . $row['ID'] . '">
          <input type="submit" value="Törlés" name="user_torles" id="user_torles">
        </form>
      </td></tr>';
      }
    }
    ?>
  </table>

  <?php echo '<a href="index.php">Vissza</a>'; ?>

</body>

</html>
<?php oci_close($conn);
