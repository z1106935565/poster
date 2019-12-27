# 海报生成器

## github
> https://github.com/a1154082497/poster

## 安装
> composer require hktk/poster

## 使用
### 初始化
```php
use hktk\poster\Poster;

//创建一个宽1000高500的画布
$poster = new Poster(1000, 500);
```

### 写入文字
```php
$poster->setText(15, 'center', 140, $white, 'xxx/xxx.ttf', 0, '识别下方二维码为我投上宝贵的一票吧')
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
