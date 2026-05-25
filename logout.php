<?php
session_start();
// Hapus semua session variabel
session_unset();
// Hapus session dari server
session_destroy();

// Redirect ke landing page utama
header("Location: index.php");
exit();
?>