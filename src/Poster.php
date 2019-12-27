<?php
namespace hktk\poster;

use Exception;

class Poster
{
    //图像标识符
    private $image;

    //画布宽度
    private $canvas_width;

    //画布高度
    private $canvas_height;

    //额外的Y轴高度
    private $y = 0;

    //错误代码
    const ERROR_CODE = 0;

    /**
     * 创建固定尺寸的画布
     *
     * @param int $canvas_width 画布宽度
     * @param int $canvas_height 画布高度
     * @return self
     */
    public function createCanvas(int $canvas_width, int $canvas_height, int $red = null, int $green = null, int $blue = null): self
    {
        $this->canvas_width = $canvas_width;
        $this->canvas_height = $canvas_height;

        $this->image = imagecreatetruecolor($canvas_width, $canvas_height);

        if ($red !== null && $green !== null && $blue !== null) {
            imagefill($this->image, 0, 0, imagecolorallocate($this->image, $red, $green, $blue));
        }

        return $this;
    }

    /**
     * 根据指定图片创建画布
     *
     * @param string $path 图片路径
     * @return self
     */
    public function createCanvasByImage(string $path): self
    {
        $this->image = $this->createResource($path);
        $this->canvas_width = imagesx($this->image);
        $this->canvas_height = imagesy($this->image);

        return $this;
    }

    /**
     * 创建颜色
     * @param int $red 红色
     * @param int $green 绿色
     * @param int $blue 蓝色
     * @param int $alpha 透明度,0-127,0表示完全不透明
     */
    public function createColor(int $red, int $green, int $blue, int $alpha = 0)
    {
        return imagecolorallocatealpha($this->image, $red, $green, $blue, $alpha);
    }

    /**
     * 设置文本
     *
     * @param int $size 字体大小
     * @param mixed $x X轴 center:居中 数字:坐标
     * @param mixed $y Y轴
     * @param object $color 颜色值
     * @param string $font_path 字体路径
     * @param int $max_width 字符串占屏幕最大宽度 0:画布宽度 大于0:指定宽度
     * @param string $text 字符串
     * @return self
     */
    public function setText(int $size, $x, int $y, int $color, string $font_path, int $max_width = null, string $text): self
    {
        if (file_exists($font_path)) {
            //增加额外的Y轴
            $y += $this->y;
            //将路径设置为绝对路径
            $font_file_info = pathinfo($font_path);
            $font_file = $_SERVER['DOCUMENT_ROOT'] .'/' . $font_file_info['dirname'] . '/' . $font_file_info['basename'];
            //获取字符串宽度
            $box_info = imagettfbbox($size, 0, $font_file, $text);
            //字符串高度
            $text_h = $box_info[1] - $box_info[7]; //左上角Y - 左下角Y
            //计算字符串运行的最大宽度
            $max_width = $max_width !== null && $max_width > $this->canvas_width ? $this->canvas_width : $max_width;
            //字符串分段数组
            $text_array = [];
            //字符串字数
            $text_length = mb_strlen($text);
            //剩余字数
            $surplus_length = $text_length;
            //要截取字符串开始的位置
            $start = 0;
            while ($start < $text_length) {
                $text_item = mb_substr($text, $start, $surplus_length);
                //计算截取字符串的宽度
                $text_item_info = imagettfbbox($size, 0, $font_file, $text_item);
                //总字符串宽度
                $text_item_info_w = $text_item_info[2] - $text_item_info[0]; //右下角X - 左下角X
                if ($text_item_info_w < $max_width) {
                    $text_array[] = $text_item;
                    $start += $surplus_length;
                } else {
                    $surplus_length--;
                }
            }

            foreach ($text_array as $index => $item) {
                $item_box_info = imagettfbbox($size, 0, $font_file, $item);
                $item_text_w   = $item_box_info[2] - $item_box_info[0];
                //计算X轴
                if (is_string($x)) {
                    switch ($x) {
                        case 'center':
                            $item_x = ($max_width - $item_text_w) / 2;
                            //兼容文字居中并且限制最大宽度的情况
                            $item_x += (($this->canvas_width - $max_width) / 2);
                            //增加额外的Y轴
                            if ($index > 0) {
                                $this->y += $text_h;
                            }
                            break;
                    }
                } else {
                    $item_x = $x;
                }
                imagettftext($this->image, $size, 0, $item_x, $y, $color, $font_file, $item);
                //Y轴增加
                $y += $text_h;
            }
        } else {
            throw new Exception('字体不存在', self::ERROR_CODE);
        }

        return $this;
    }

    /**
     * 设置图片
     *
     * @param string $path 图片路径
     * @param string|int $dst_x X轴 center:居中 right:右对齐 数字:坐标
     * @param string|int $dst_y Y轴 center:居中 right:底部 数字:坐标
     * @param int $src_w 目标图像宽度 0:原始宽度 大于0:指定宽度
     * @param int $src_h 目标图像高度 0:根据宽度缩放比例缩放 大于0:指定高度
     * @return self
     */
    public function setImg(string $path, $dst_x = 0, $dst_y = 0, $src_w = 0, $src_h = 0): self
    {
        $image = $this->createResource($path);
        //获取图像宽高
        $image_width    = imagesx($image);
        $image_height   = imagesy($image);
        if ($src_w == 0) {
            $src_w = $image_width;
        }
        if ($src_h == 0) {
            $src_h = floor($image_height / $image_width * $src_w);
        }
        //计算X轴
        if (is_string($dst_x)) {
            switch ($dst_x) {
                case 'center':
                    $dst_x = ($this->canvas_width - $src_w) / 2;
                    break;
                case 'right':
                    $dst_x = $this->canvas_width - $src_w;
                    break;
            }
        }
        //计算Y轴
        if (is_string($dst_y)) {
            switch ($dst_y) {
                case 'center':
                    $dst_y = ($this->canvas_height - $src_h) / 2;
                    break;
                case 'right':
                    $dst_y = $this->canvas_height - $src_h;
                    break;
            }
        }
        //增加额外的Y轴
        $dst_y += $this->y;
        //拷贝图片
        if ($src_w) {
            //等比缩小拷贝
            imagecopyresampled($this->image, $image, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $image_width, $image_height);
        } else {
            //原始比例拷贝
            imagecopy($this->image, $image, $dst_x, $dst_y, 0, 0, $image_width, $image_height);
        }
        imagedestroy($image);

        return $this;
    }

    /**
     * 显示图片
     *
     * @param bool $image_flow 是否输出图像流 true:是(可以用来上传至AliOSS) false:否(直接显示图片)
     * @return void|string
     */
    public function showImage(bool $image_flow = true)
    {
        if ($image_flow) {
            ob_start();
            imagepng($this->image);
            $string_data = ob_get_contents();
            ob_end_clean();
            return $string_data;
        } else {
            header('content-type:image/png');
            imagepng($this->image);
            imagedestroy($this->image);
            exit;
        }
    }

    /**
     * 根据图片路径或图片流字符串创建图像资源
     * 
     * @param string $path
     * @return void
     */
    private function createResource(string $path)
    {
        try {
            //获取文件后缀
            $suffix = pathinfo($path, PATHINFO_EXTENSION);
            //创建图像资源
            switch ($suffix) {
                case 'jpg':
                    $image = imagecreatefromjpeg($path);
                    break;
                case 'png':
                    $image = imagecreatefrompng($path);
                    break;
                case 'gif':
                    $image = imagecreatefromgif($path);
                    break;
                default:
                    $image = imagecreatefromstring($path);
                    break;
            }
            return $image;
        } catch (Exception $e) {
            throw new Exception('创建失败', self::ERROR_CODE);
        }
    }

}