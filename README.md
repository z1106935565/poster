# 海报生成器

## github
> https://github.com/a1154082497/poster

## 安装
> composer require hktk/poster

## 使用
### 初始化
```php
use hktk\poster\Poster;

$poster = new Poster();
```
### 创建画布
```php
//创建一个600*1000白色的画布
$poster->createCanvas(600, 1000, 255, 255, 255);
//根据图片或者图片字符串创建一个画布
$poster->createCanvasByImage('./images/xxx.png');
```
### 创建一个颜色
```php
//创建一个灰色的颜色值
$color  = $poster->createColor(50, 50, 50);
//创建一个白色并透明的颜色值
$color  = $poster->createColor(255, 255, 255, 100);
```

### 写入文字
```php
$poster->setText(15, 'center', 140, $white, 'xxx/xxx.ttf', null, 'PHP是世界上最美的语言')
```

### 写入图片
```php
$poster->setImg('./xxx/xxx.png', 400, 620, 150)
```

### 输出图片
```php
//true 获取图片流(用来上传至阿里云) false:直接显示图片
$poster->showImage(true);
```
