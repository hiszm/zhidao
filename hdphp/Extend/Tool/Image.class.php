<?php
// .-----------------------------------------------------------------------------------
// |  Software: [HDPHP framework]
// |   Version: 2013.01
// |      Site: http://www.hdphp.com
// |-----------------------------------------------------------------------------------
// |    Author: 向军 <houdunwangxj@gmail.com>
// | Copyright (c) 2012-2013, http://houdunwang.com. All Rights Reserved.
// |-----------------------------------------------------------------------------------
// |   License: http://www.apache.org/licenses/LICENSE-2.0
// '-----------------------------------------------------------------------------------

/**
 * 图像处理类
 * @package     tools_class
 * @author      后盾向军 <houdunwangxj@gmail.com>
 */
class Image{

    //是否应用水印
    private $waterOn;
    //水印图片
    public $waterImg;
    //水印的位置
    public $waterPos;
    //水印的透明度
    public $waterPct;
    //图像的压缩比
    public $waterQuality;
    //水印文字内容
    public $waterText;
    //水印文字大小
    public $waterTextSize;
    //水印文字的颜色
    public $waterTextColor;
    //水印的文字的字体
    public $waterTextFont;
    //是否开启缩略图功能
//    private $thumbOn;
    //生成缩略图的方式
    public $thumbType;
    //缩略图的宽度
    public $thumbWidth;
    //缩略图的高度
    public $thumbHeight;
    //生成缩略图文件名后缀
    public $thumbEndFix;
    //缩略图文件前缀
    public $thumbPreFix;

    /**
     * 构造函数
     */
    public function __construct() {
        //水印参数
        $this->waterOn = C("WATER_ON");
        $this->waterImg = C("WATER_IMG");
        $this->waterPos = C("WATER_POS");
        $this->waterPct = C("WATER_PCT");
        $this->waterQuality = C("WATER_QUALITY");
        $this->waterText = C("WATER_TEXT");
        $this->waterTextColor = C("WATER_TEXT_COLOR");
        $this->waterTextSize = C("WATER_TEXT_SIZE");
        $this->waterTextFont = C("WATER_FONT");
        //缩略图参数
        $this->thumbType = C("THUMB_TYPE");
        $this->thumbWidth = C("THUMB_WIDTH");
        $this->thumbHeight = C("THUMB_HEIGHT");
        $this->thumbPreFix = C("THUMB_PREFIX");
        $this->thumbEndFix = C("THUMB_ENDFIX");
    }

    /**
     * 环境验证
     * @param $img			图像路径
     * return boolean
     */
    private function check($img) {
        $type = array(".jpg", ".jpeg", ".png", ".gif");
        $imgType = strtolower(strrchr($img, '.'));
        return extension_loaded('gd') && file_exists($img) && in_array($imgType, $type);
    }

    /**
     *
     * 获得缩略图的尺寸信息
     * @param  $imgWidth		原图宽度
     * @param  $imgHeight		原图高度
     * @param  $thumbWidth			缩略图宽度
     * @param  $thumbHeight			缩略图的高度
     * @parrm  $thunbType    	处理方式
     * return array
     */
    private function thumbSize($imgWidth, $imgHeight, $thumbWidth, $thumbHeight, $thunbType) {
        //初始化缩略图尺寸
        $w = $thumbWidth;
        $h = $thumbHeight;
        //初始化原图尺寸
        $cuthumbWidth = $imgWidth;
        $cuthumbHeight = $imgHeight;

        if ($imgWidth <= $thumbWidth && $imgHeight <= $thumbHeight) {
            $w = $imgWidth;
            $h = $imgHeight;
        } else {
            switch ($thunbType) {
                case 1 :
                    //固定宽度  高度自增
                    $h = $thumbWidth / $imgWidth * $imgHeight;
                    break;
                case 2 :
                    //固定高度  宽度自增
                    $w = $thumbHeight / $imgHeight * $imgWidth;
                    break;
                case 3 :
                    //固定宽度  高度裁切
                    $cuthumbHeight = $imgWidth / $thumbWidth * $thumbHeight;
                    break;
                case 4 :
                    //固定高度  宽度裁切
                    $cuthumbWidth = $imgHeight / $thumbHeight * $thumbWidth;
                    break;
                case 5 :
                    //缩放最大边 原图不裁切
                    if (($imgWidth / $thumbWidth) > ($imgHeight / $thumbHeight)) {
                        $h = $thumbWidth / $imgWidth * $imgHeight;
                    } elseif (($imgWidth / $thumbWidth) < ($imgHeight / $thumbHeight)) {
                        $w = $thumbHeight / $imgHeight * $imgWidth;
                    } else {
                        $w = $thumbWidth;
                        $h = $thumbHeight;
                    }
                    break;
                default:
                    //缩略图尺寸不变，自动裁切图片
                    if (($imgHeight / $thumbHeight) < ($imgWidth / $thumbWidth)) {
                        $cuthumbWidth = $imgHeight / $thumbHeight * $thumbWidth;
                    } elseif (($imgHeight / $thumbHeight) > ($imgWidth / $thumbWidth)) {
                        $cuthumbHeight = $imgWidth / $thumbWidth * $thumbHeight;
                    }
            }
        }
        $arr [0] = $w;
        $arr [1] = $h;
        $arr [2] = $cuthumbWidth;
        $arr [3] = $cuthumbHeight;
        return $arr;
    }

    /**
     *
     * 图片裁切处理
     * @param $img		操作的图片文件路径
     * @param $outFile		另存文件名
     * @param path              文件存放路径
     * @param $thumbWidth	缩略图宽度
     * @param $thumbHeight	缩略图高度
     * @param $thunbType        裁切图片的方式
     * return $string 		处理后的文件名
     */
    public function thumb($img, $outFile = '', $path = '', $thumbWidth = '', $thumbHeight = '', $thunbType = '') {
        if (!$this->check($img)) {
            return false;
        }
        //基础配置
        $thunbType = $thunbType ? $thunbType : $this->thumbType;
        $thumbWidth = $thumbWidth ? $thumbWidth : $this->thumbWidth;
        $thumbHeight = $thumbHeight ? $thumbHeight : $this->thumbHeight;
        $path = $path ? $path : C("THUMB_PATH");
        //获得图像信息
        $imgInfo = getimagesize($img);
        $imgWidth = $imgInfo [0];
        $imgHeight = $imgInfo [1];
        $imgType = image_type_to_extension($imgInfo [2]);
        //获得相关尺寸
        $thumb_size = $this->thumbSize($imgWidth, $imgHeight, $thumbWidth, $thumbHeight, $thunbType);
        //原始图像资源
        $func = "imagecreatefrom" . substr($imgType, 1);
        $resImg = $func($img);
        //缩略图的资源
        if ($imgType == '.gif') {
            $res_thumb = imagecreate($thumb_size [0], $thumb_size [1]);
            $color = imagecolorallocate($res_thumb, 255, 0, 0);
        } else {
            $res_thumb = imagecreatetruecolor($thumb_size [0], $thumb_size [1]);
            imagealphablending($res_thumb, false); //关闭混色
            imagesavealpha($res_thumb, true); //储存透明通道
        }
        //绘制缩略图X
        if (function_exists("imagecopyresampled")) {
            imagecopyresampled($res_thumb, $resImg, 0, 0, 0, 0, $thumb_size [0], $thumb_size [1], $thumb_size [2], $thumb_size [3]);
        } else {
            imagecopyresized($res_thumb, $resImg, 0, 0, 0, 0, $thumb_size [0], $thumb_size [1], $thumb_size [2], $thumb_size [3]);
        }
        //处理透明色
        if ($imgType == '.gif') {
            imagecolortransparent($res_thumb, $color);
        }
        //配置输出文件名
        $imgInfo = pathinfo($img);
        $outFile = $outFile ? $outFile : $this->thumbPreFix . $imgInfo['filename'] . $this->thumbEndFix . "." . $imgInfo['extension'];
        $upload_dir = $path ? $path : dirname($img);
        Dir::create($upload_dir);
        $outFile = $upload_dir . '/' . $outFile;
        $func = "image" . substr($imgType, 1);
        $func($res_thumb, $outFile);
        if (isset($resImg))
            imagedestroy($resImg);
        if (isset($res_thumb))
            imagedestroy($res_thumb);

        return $outFile;
    }

    /**
     * 水印处理
     * @param $img 			操作的图像
     * @param $outImg                                  另存的图像
     * @param $waterImg                                水印图片
     * @param $pos			水印位置
     * @param $text			文字水印内容
     * @param $pct			透明度
     * return boolean
     */
    public function water($img, $outImg = '', $pos = '', $waterImg = '', $pct = '', $text = "") {
        //验证原图像
        if (!$this->check($img) || !$this->waterOn)
            return false;
        //验证水印图像
        $waterImg = $waterImg ? $waterImg : $this->waterImg;
        $waterImgOn = $this->check($waterImg) ? 1 : 0;
        //判断另存图像
        $outImg = $outImg ? $outImg : $img;
        //水印位置
        $pos = $pos ? $pos : $this->waterPos;
        //水印文字
        $text = $text ? $text : $this->waterText;
        //水印透明度
        $pct = $pct ? $pct : $this->waterPct;
        $imgInfo = getimagesize($img);
        $imgWidth = $imgInfo [0];
        $imgHeight = $imgInfo [1];
        //获得水印信息
        if ($waterImgOn) {
            $waterInfo = getimagesize($waterImg);
            $waterWidth = $waterInfo [0];
            $waterHeight = $waterInfo [1];
            switch ($waterInfo [2]) {
                case 1 :
                    $w_img = imagecreatefromgif($waterImg);
                    break;
                case 2 :
                    $w_img = imagecreatefromjpeg($waterImg);
                    break;
                case 3 :
                    $w_img = imagecreatefrompng($waterImg);
                    break;
            }
        } else {
            if (empty($text) || strlen($this->waterTextColor) != 7)
                return false;
            $textInfo = imagettfbbox($this->waterTextSize, 0, $this->waterTextFont, $text);
            $waterWidth = $textInfo [2] - $textInfo [6];
            $waterHeight = $textInfo [3] - $textInfo [7];
        }
        //建立原图资源
        if ($imgHeight < $waterHeight || $imgWidth < $waterWidth)
            return false;
        switch ($imgInfo [2]) {
            case 1 :
                $resImg = imagecreatefromgif($img);
                break;
            case 2 :
                $resImg = imagecreatefromjpeg($img);
                break;
            case 3 :
                $resImg = imagecreatefrompng($img);
                break;
        }
        //水印位置处理方法
        switch ($pos) {
            case 1 :
                $x = $y = 25;
                break;
            case 2 :
                $x = ($imgWidth - $waterWidth) / 2;
                $y = 25;
                break;
            case 3 :
                $x = $imgWidth - $waterWidth;
                $y = 25;
                break;
            case 4 :
                $x = 25;
                $y = ($imgHeight - $waterHeight) / 2;
            case 5 :
                $x = ($imgWidth - $waterWidth) / 2;
                $y = ($imgHeight - $waterHeight) / 2;
                break;
            case 6 :
                $x = $imgWidth - $waterWidth;
                $y = ($imgHeight - $waterHeight) / 2;
                break;
            case 7 :
                $x = 25;
                $y = $imgHeight - $waterHeight;
                break;
            case 8 :
                $x = ($imgWidth - $waterWidth) / 2;
                $y = $imgHeight - $waterHeight;
                break;
            case 9 :
                $x = $imgWidth - $waterWidth - 10;
                $y = $imgHeight - $waterHeight;
                break;
            default :
                $x = mt_rand(25, $imgWidth - $waterWidth);
                $y = mt_rand(25, $imgHeight - $waterHeight);
        }
        if ($waterImgOn) {
            if ($waterInfo [2] == 3) {
                imagecopy($resImg, $w_img, $x, $y, 0, 0, $waterWidth, $waterHeight);
            } else {
                imagecopymerge($resImg, $w_img, $x, $y, 0, 0, $waterWidth, $waterHeight, $pct);
            }
        } else {
            $r = hexdec(substr($this->waterTextColor, 1, 2));
            $g = hexdec(substr($this->waterTextColor, 3, 2));
            $b = hexdec(substr($this->waterTextColor, 5, 2));
            $color = imagecolorallocate($resImg, $r, $g, $b);
            $charset = strtoupper(C('CHARSET')) === "UTF8" ? "UTF-8" : strtoupper(C('CHARSET'));
            imagettftext($resImg, $this->waterTextSize, 0, $x, $y, $color, $this->waterTextFont, iconv($charset, 'utf-8', $text));
        }
        switch ($imgInfo [2]) {
            case 1 :
                imagegif($resImg, $outImg);
                break;
            case 2 :
                imagejpeg($resImg, $outImg, $this->waterQuality);
                break;
            case 3 :
                imagepng($resImg, $outImg);
                break;
        }
        if (isset($resImg))
            imagedestroy($resImg);
        if (isset($w_img))
            imagedestroy($w_img);
        return true;
    }

}
