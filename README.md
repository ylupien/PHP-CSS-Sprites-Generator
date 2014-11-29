PHP-CSS-Sprites-Generator
===============================

Simple CSS Sprite Generator PHP Class. Generate sprites from pictures folder.

This script loop over all picture in folder and place it where it can fit.

## Dependencies
[Image Processing and GD](http://php.net/manual/en/book.image.php#book.image)

## Api usage example

```php
$spriteGrid = new yl\gfx\SpriteGrid();
$spriteGrid->importImagesFromFolder(new DirectoryIterator('images'));

$spriteGrid->writeCss("output/sprite.css");
$spriteGrid->writePng("output/sprite.png");
```
