<?php
namespace application\assets;

use ItForFree\SimpleAsset\SimpleAsset;

class AdminAsset extends SimpleAsset {
    
    public $basePath = '/';
    
    public $css = [
        'CSS/style.css'  // стили для админки
    ];
    
    // можно добавить зависимости, если нужно
    public $needs = [    
        // например, BootstrapAsset::class
    ];     
}
