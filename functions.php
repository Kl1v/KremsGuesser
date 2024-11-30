<?php
function generateUniqueLobbyCode($conn) {
    do {
        $code = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

        $stmt = $conn->prepare("SELECT COUNT(*) FROM lobbies WHERE code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
    } while ($count > 0);

    return $code;
}
?>