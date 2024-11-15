<?php

require 'import_csv.php';

$stmt = $pdo->query("
    SELECT p.name AS product_name, p.price, GROUP_CONCAT(c.name SEPARATOR ',') AS categories
    FROM products p
    LEFT JOIN product_category pc ON p.id = pc.product_id
    LEFT JOIN categories c ON pc.category_id = c.id
    GROUP BY p.id
");
$products = $stmt->fetchAll();

$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><products/>');

foreach ($products as $product) {
    $productXml = $xml->addChild('product');
    $productXml->addChild('title', htmlspecialchars($product['product_name']));
    $productXml->addChild('price', $product['price']);

    $categoriesXml = $productXml->addChild('categories');
    $categories = explode(',', $product['categories']);
    foreach ($categories as $category) {
        if (!empty($category)) {
            $categoriesXml->addChild('category', htmlspecialchars($category));
        }
    }
}

$xmlFilePath = 'products_feed.xml';
$dom = dom_import_simplexml($xml)->ownerDocument;
$dom->encoding = 'UTF-8';
$dom->formatOutput = true; // Formázott XML
$dom->save($xmlFilePath);

echo $xml->asXML();