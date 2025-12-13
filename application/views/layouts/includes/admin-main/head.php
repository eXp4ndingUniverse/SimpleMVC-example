<?php 
use ItForFree\SimpleAsset\SimpleAssetManager;
use application\assets\BootstrapAsset;
use application\assets\CustomCSSAsset;
use application\assets\AdminAsset;
AdminAsset::add();

BootstrapAsset::add();
CustomCSSAsset::add();
SimpleAssetManager::printCss();
?>

<head>
    <title>Admin Login</title>
</head>