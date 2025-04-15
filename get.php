<?php
$dsn = "mysql:host=localhost;dbname=lb_pdo_goods;charset=utf8";
$user = 'root';
$pass = '';

$vendorName = $_GET["vendorName"] ?? null;
$categoryName = $_GET["categoryName"] ?? null;
$pricerange = $_GET["pricerange"] ?? null;
$format = $_GET["format"] ?? 'html';

try {
    $dbh = new PDO($dsn, $user, $pass);

    if ($vendorName) {
        $sql = "SELECT id_Vendors, v_name, items.name FROM vendors JOIN items ON id_vendors = fid_vendor WHERE v_name = :vendorName";
        $sth = $dbh->prepare($sql);
        $sth->bindValue(":vendorName", $vendorName, PDO::PARAM_STR);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        echo "<table border='1'><thead><tr><th>Номер</th><th>Виробник</th><th>Товари</th></tr></thead><tbody>";
        foreach ($result as $item) {
            echo "<tr><td>{$item['id_Vendors']}</td><td>{$item['v_name']}</td><td>{$item['name']}</td></tr>";
        }
        echo "</tbody></table>";

    } elseif ($categoryName && $format === 'xml') {
        header('Content-Type: application/xml');
        $sql = "SELECT id_category, c_name, items.name FROM category JOIN items ON id_category = fid_category WHERE c_name = :categoryName";
        $sth = $dbh->prepare($sql);
        $sth->bindValue(":categoryName", $categoryName);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        $xml = new SimpleXMLElement('<data/>');
        foreach ($result as $row) {
            $item = $xml->addChild('row');
            $item->addChild('id', $row['id_category']);
            $item->addChild('name', $row['c_name']);
            $item->addChild('item', $row['name']);
        }
        echo $xml->asXML();

    } elseif ($pricerange && $format === 'json') {
        header('Content-Type: application/json');
        list($minPrice, $maxPrice) = explode("-", $pricerange);

        $sql = "SELECT id_items, name, price FROM items WHERE price BETWEEN :minPrice AND :maxPrice ORDER BY price ASC";
        $sth = $dbh->prepare($sql);
        $sth->bindValue(":minPrice", $minPrice);
        $sth->bindValue(":maxPrice", $maxPrice);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($result);
    }

} catch (PDOException $ex) {
    echo $ex->getMessage();
}
?>