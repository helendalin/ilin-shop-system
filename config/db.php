<?php
// define('BASE_URL', '/ilin-shop-system');

// $host = "localhost";
// $user = "root";
// $pass = "";
// $dbname = "ilin_shop_db";

// $conn = new mysqli($host, $user, $pass, $dbname);

// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }
?>

<?php
// define('BASE_URL', '/ilin-shop-system');

// $host = "localhost";
// $user = "root";
// $pass = "";
// $dbname = "ilin_shop_db";

// $conn = new mysqli($host, $user, $pass, $dbname);

// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }

// $conn->set_charset("utf8mb4");
?>
<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "ilin_shop_db"; // use your real database name

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/ilin-shop-system');
}
?>