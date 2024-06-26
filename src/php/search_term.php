<?php
function search_term($conn, $count) {

    $term = "%" . $_GET["term"] . "%";
    if (empty($term)) {
        echo "Hiba keresés közben: üres keresés szöveg";
        return;
    }

    $search = oci_parse($conn,
        "SELECT VIDEO.ID, VIDEO.CIM, VIDEO.THUMBNAIL, VIDEO.VIEWS, FELHASZNALO.NEV, FELTOLTO.DATUM
        FROM VIDEO
        INNER JOIN FELTOLTO
        ON VIDEO.ID = FELTOLTO.VIDEO_ID
        INNER JOIN FELHASZNALO
        ON FELHASZNALO.ID = FELTOLTO.FELHASZNALO_ID
        WHERE LOWER(CONVERT(VIDEO.CIM, 'US7ASCII'))
        LIKE LOWER(CONVERT(:term, 'US7ASCII'))
        ORDER BY VIDEO.ID
        FETCH FIRST :count ROWS ONLY");
    oci_bind_by_name($search, ":term", $term);
    oci_bind_by_name($search, ":count", $count);
    oci_execute($search);

    $isFound = FALSE;
    while (oci_fetch($search)) {
        echo "
        <div class='search_result' id='" . oci_result($search, "ID") . "_vid' style='width: 200px;'>
            <img src='/media/thumbnails/" . oci_result($search, "THUMBNAIL") . "' height=100 width=100/ ><br />
            " . oci_result($search, "CIM") . "<br />
            Nézettség: " . oci_result($search, "VIEWS") . "<br />
            Feltöltötte: <a href='channel_page.php?user=" . oci_result($search, "NEV") . "'>" . oci_result($search, "NEV") . "</a><br />
            Ekkor: " . oci_result($search, "DATUM") . "<br />
        </div>
        ";

        $isFound = TRUE;
    }

    if (!$isFound) {
        echo "
        <p>Nincs találat.</p>
        ";
    }
    

    echo "
    <script>
    let results = document.getElementsByClassName('search_result');
    Array.from(results).forEach((res) => {
        let video_id = res.id.split('_')[0];
        res.addEventListener('click', function() {
            window.location.href = '/video_page.php?video_id=' + video_id;
        });
    });
    </script>
    ";
    oci_close($conn);
}

function search_term_by_user($conn, $user) {

    $term = "%" . $_GET["term"] . "%";
    if (empty($term)) {
        echo "Hiba keresés közben: üres keresés szöveg";
        return;
    }

    $search = oci_parse($conn,
        "SELECT VIDEO.ID, VIDEO.CIM, VIDEO.VIEWS, VIDEO.THUMBNAIL, FELHASZNALO.NEV, FELTOLTO.DATUM
        FROM VIDEO
        INNER JOIN FELTOLTO ON VIDEO.ID = FELTOLTO.VIDEO_ID
        INNER JOIN FELHASZNALO ON FELHASZNALO.ID = FELTOLTO.FELHASZNALO_ID
        WHERE LOWER(CONVERT(VIDEO.CIM, 'US7ASCII'))
        LIKE LOWER(CONVERT(:term, 'US7ASCII')) AND
        FELHASZNALO.NEV = :username
        ORDER BY VIDEO.ID DESC
        ");
    oci_bind_by_name($search, ":term", $term);
    oci_bind_by_name($search, ":username", $user);

    oci_execute($search);


    $isFound = FALSE;
    while (oci_fetch($search)) {
        echo "
        <div class='search_result' id='" . oci_result($search, "ID") . "_vid' style='width: 200px;'>
            <img src='/media/thumbnails/" . oci_result($search, "THUMBNAIL") . "' height=100 width=100/ ><br />
            " . oci_result($search, "CIM") . "<br />
            Nézettség: " . oci_result($search, "VIEWS") . "<br />
            Feltöltötte: <a href='channel_page.php?user=" . oci_result($search, "NEV") . "'>" . oci_result($search, "NEV") . "</a><br />
            Ekkor: " . oci_result($search, "DATUM") . "<br />
        </div>
        ";

        $isFound = TRUE;
    }

    if (!$isFound) {
        echo "
        <p>Nincs találat.</p>
        ";
    }
    oci_close($conn);
}