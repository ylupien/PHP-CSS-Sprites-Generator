<?php
require_once('libs/yl/gfx/SpriteGrid.php');

$spriteGrid = new yl\gfx\SpriteGrid();
$spriteGrid->importImagesFromFolder(new DirectoryIterator('images'));

$spriteGrid->writeCss("output/sprite.css");
$spriteGrid->writePng("output/sprite.png");
