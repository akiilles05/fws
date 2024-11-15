<?php

$servername = "localhost";
$username = "root";
$password = "";
$database = "products_db";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$csvFile = fopen("termekek.csv", "r");
fgetcsv($csvFile);

while (($data = fgetcsv($csvFile)) !== FALSE) {
    $name = $data[0];
    $price = (int)$data[1];
    $categories = array_slice($data, 2);

    $stmt = $pdo->prepare("SELECT id FROM products WHERE name = ?");
    $stmt->execute([$name]);
    $product_id = $stmt->fetchColumn();

    if ($product_id){
        $stmt = $pdo->prepare("UPDATE products SET price = ? WHERE id = ?");
        $stmt->execute([$price, $product_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (name, price) VALUES (?, ?)");
        $stmt->execute([$name, $price]);
        $product_id = $pdo->lastInsertId();
    }

    foreach ($categories as $category) {
        if (empty($category)) continue;

        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->execute([$category]);
        $category_id = $stmt->fetchColumn();

          if (!$category_id) {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$category]);
            $category_id = $pdo->lastInsertId();
        }

        $stmt = $pdo->prepare("SELECT 1 FROM product_category WHERE product_id = ? AND category_id = ?");
        $stmt->execute([$product_id, $category_id]);

        if (!$stmt->fetchColumn()) {
            $stmt = $pdo->prepare("INSERT INTO product_category (product_id, category_id) VALUES (?, ?)");
            $stmt->execute([$product_id, $category_id]);
        }
    }
}

fclose($csvFile);
echo 'CSV import completed';

    