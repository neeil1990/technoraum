<?
class CRamWatermarkImage
{
	public $image;
	public $width;
	public $height;
	public $type;
	
	public function __construct()
    {
        $this->image = null;
        $this->width = 0;
        $this->height = 0;
        $this->type = '';
    }
	
	public function luminance()
	{
		$luminance = 0;
		$pixels = 0;

		for ($x = 0; $x < $this->width; $x++)
		{
			for ($y = 0; $y < $this->height; $y++)
			{
				$rgb = imagecolorat($this->image, $x, $y);

				$red = ($rgb >> 16) & 0xFF;
				$green = ($rgb >> 8) & 0xFF;
				$blue = $rgb & 0xFF;
				
				$color = imagecolorat($this->image, $x, $y);
				if ($color != -1)
				{
					$luminance += 0.2126 * $red + 0.7152 * $green + 0.0722 * $blue;
					$pixels++;
				}
			}
		}

        return $luminance / $pixels;
	}
	
	public function from($source)
	{
		$imageSize = getimagesize($source);
		
		switch ($imageSize['mime'])
		{
			case 'image/jpeg':
			{
				$this->image = imagecreatefromjpeg($source);
				break;
			}
			case 'image/png':
			{
				$this->image = imagecreatefrompng($source);
				break;
			}
			case 'image/gif':
			{
				$this->image = imagecreatefromgif($source);
				break;
			}
			case 'image/bmp':
			{
				$this->image = imagecreatefrombmp($source);
				break;
			}
			case 'image/webp':
			{
				$this->image = imagecreatefromwebp($source);
				break;
			}
		}
		
		if ($this->image != null)
		{
			$this->width = imagesx($this->image);
			$this->height = imagesy($this->image);
			$this->type = $imageSize['mime'];
			
			if (!imageistruecolor($this->image))
			{
				$image = imagecreatetruecolor($this->width, $this->height);
				imagefill($image, 0, 0, -1);
				imagealphablending($image, true);
				imagecopy($image, $this->image, 0, 0, 0, 0, $this->width, $this->height);
				$this->image = $image;
			}
			
			imagesavealpha($this->image, true);
		}
	}
	
	public function margin($margins)
	{
		$width = $this->width + $margins['MARGIN_LEFT'] + $margins['MARGIN_RIGHT'];
		$height = $this->height + $margins['MARGIN_TOP'] + $margins['MARGIN_BOTTOM'];
		$image = imagecreatetruecolor($width, $height);
		imagefill($image, 0, 0, -1);
		imagesavealpha($image, true);
		imagecopy($image, $this->image, $margins['MARGIN_LEFT'], $margins['MARGIN_TOP'], 0, 0, $this->width, $this->height);
		$this->image = $image;
		$this->width = $width;
		$this->height = $height;
	}
	
	public function trim()
	{
		$this->image = \CRamWatermarkImage::trimming($this->image);
		
		$this->width = imagesx($this->image);
		$this->height = imagesy($this->image);
	}
	
	public static function trimming($sourceImage)
	{
		$width = imagesx($sourceImage);
		$height = imagesy($sourceImage);
		
		$left = 0;
		$right = 0;
		$top = 0;
		$bottom = 0;
		$check = false;
		for ($x = 0; $x < $width; $x++)
		{
			for ($y = 0; $y < $height; $y++)
			{
				$color = imagecolorat($sourceImage, $x, $y);
				if ($color != -1)
				{
					$left = $x;
					$check = true;
					break;
				}
			}
			if ($check) break;
		}
		$check = false;
		for ($x = $width-1; $x >= $left; $x--)
		{
			for ($y = 0; $y < $height; $y++)
			{
				$color = imagecolorat($sourceImage, $x, $y);
				if ($color != -1)
				{
					$right = $x;
					$check = true;
					break;
				}
			}
			if ($check) break;
		}
		$check = false;
		for ($y = 0; $y < $height; $y++)
		{
			for ($x = $left; $x < $right; $x++)
			{
				$color = imagecolorat($sourceImage, $x, $y);
				if ($color != -1)
				{
					$top = $y;
					$check = true;
					break;
				}
			}
			if ($check) break;
		}
		$check = false;
		for ($y = $height-1; $y > $top; $y--)
		{
			for ($x = $left; $x < $right; $x++)
			{
				$color = imagecolorat($sourceImage, $x, $y);
				if ($color != -1)
				{
					$bottom = $y;
					$check = true;
					break;
				}
			}
			if ($check) break;
		}
		$width = $right + 1 - $left;
		$height = $bottom + 1 - $top;
		
		$image = imagecreatetruecolor($width, $height);
		imagefill($image, 0, 0, -1);
		imagesavealpha($image, true);
		imagecopy($image, $sourceImage, 0, 0, $left, $top, $width, $height);
		
		return $image;
	}
	
	public function transparent($transparent)
	{
		if ($transparent == 0) return;
		
		$transparentColor = imagecolortransparent($this->image);		
		$image = imagecreatetruecolor($this->width, $this->height);
		imagefill($image, 0, 0, imagecolortransparent($image));
		imagesavealpha($image, true);
		
		for ($x = 0; $x < $this->width; $x++)
		{
			for ($y = 0; $y < $this->height; $y++)
			{
				$color = imagecolorat($this->image, $x, $y);
				if ($color != $transparentColor)
				{
					$colorIndex = imagecolorsforindex($this->image, $color);
					if ($colorIndex['alpha'] != 127)
					{
						$alpha = $colorIndex['alpha'] + (127 - $colorIndex['alpha']) / 100 * $transparent;
						imagesetpixel($image, $x, $y, imagecolorallocatealpha($image, $colorIndex['red'], $colorIndex['green'], $colorIndex['blue'], $alpha));
					}
				}
			}
		}
		$this->image = $image;
	}
	
	public function rotate($angle)
	{
		if ($angle == 0) return;
		
		if ($angle == 90 || $angle == 270)
		{
			$image = imagecreatetruecolor($this->height, $this->width);
		}
		else if ($angle == 180)
		{
			$image = imagecreatetruecolor($this->width, $this->height);
		}
		else
		{
			$rad = deg2rad(-$angle);
			$size_angle = $angle > 270 ? 360 - $angle : ($angle > 180 ? $angle : ($angle > 90 ? 180 - $angle : $angle));
			$size_angle = deg2rad($size_angle);
			$width = ceil(abs(sqrt($this->width*$this->width + $this->height*$this->height) * sin(atan($this->width / $this->height) + $size_angle)));
			$height = ceil(abs(sqrt($this->width*$this->width + $this->height*$this->height) * sin(atan($this->height / $this->width) + $size_angle)));
			$mx = $width / 2;
			$my = $height / 2;
			$dx = ($this->width - $width) / 2;
			$dy = ($this->height - $height) / 2;
			$sin = sin($rad);
			$cos = cos($rad);
			$image = imagecreatetruecolor($width, $height);
		}
		
		imagefill($image, 0, 0, -1);
		imagesavealpha($image, true);
		
		if ($angle == 90 || $angle == 180 || $angle == 270)
		{
			for ($x = 0; $x < $this->width; $x++)
			{
				for ($y = 0; $y < $this->height; $y++)
				{
					$color_index = imagecolorat($this->image, $x, $y);
					if ($color_index != -1)
					{
						$color = imagecolorsforindex($this->image, $color_index);
						if ($color['alpha'] < 127)
						{
							if ($angle == 90)
							{
								imagesetpixel($image, $this->height-$y-1, $x, $color_index);
							}
							else if ($angle == 180)
							{
								imagesetpixel($image, $this->width-$x-1, $this->height-$y-1, $color_index);
							}
							else if ($angle == 270)
							{
								imagesetpixel($image, $y, $this->width-$x-1, $color_index);
							}
						}
					}
				}
			}
			
			if ($angle == 90 || $angle == 270)
			{
				$width = $this->height;
				$height = $this->width;
			}
			else if ($angle == 180)
			{
				$width = $this->width;
				$height = $this->height;
			}
		}
		else
		{
			for ($x = 0; $x < $width; $x++)
			{
				for ($y = 0; $y < $height; $y++)
				{
					$sx = $mx + ($x - $mx) * $cos - ($y - $my) * $sin + $dx;
					$sy = $my + ($x - $mx) * $sin + ($y - $my) * $cos + $dy;
					
					if ($sx > -1 && $sx <= $this->width && $sy > -1 && $sy <= $this->height)
					{
						$sx_fraction = $sx - floor($sx);
						$sy_fraction = $sy - floor($sy);
						$pixel = null;
						$bilinears = Array
						(
							Array(-0.5, -0.5, (1 - $sx_fraction) * (1 - $sy_fraction)),
							Array(0.5, -0.5, $sx_fraction * (1 - $sy_fraction)),
							Array(0.5, 0.5, $sx_fraction * $sy_fraction),
							Array(-0.5, 0.5, (1 - $sx_fraction) * $sy_fraction)
						);
						
						foreach ($bilinears as $k => $bilinear)
						{
							if ($bilinear[2])
							{
								$bilinear_x = round($sx + $bilinear[0]);
								$bilinear_y = round($sy + $bilinear[1]);
								
								if ($bilinear_x >= 0 && $bilinear_x < $this->width && $bilinear_y >= 0 && $bilinear_y < $this->height)
								{
									$color_index = imagecolorat($this->image, $bilinear_x, $bilinear_y);
									
									if ($color_index != -1)
									{
										$color = imagecolorsforindex($this->image, $color_index);
										
										if ($color['alpha'] < 127)
										{
											$color['alpha'] = 127 - (127 - $color['alpha']) * $bilinear[2];
											$color['alpha'] = (127 - $color['alpha']) / 127;
											
											if ($color['alpha'])
											{
												if (!$pixel) $pixel = $color;
												else
												{
													$alpha = $pixel['alpha'] + $color['alpha'];
													$red = $pixel['red'] + ($color['red'] - $pixel['red']) * $color['alpha'];
													$green = $pixel['green'] + ($color['green'] - $pixel['green']) * $color['alpha'];
													$blue = $pixel['blue'] + ($color['blue'] - $pixel['blue']) * $color['alpha'];
													$pixel = Array('red' => $red, 'green' => $green, 'blue' => $blue, 'alpha' => $alpha);
												}
											}
										}
									}
								}
							}
						}
						
						if ($pixel)
						{
							$pixel['alpha'] = 127 - $pixel['alpha'] * 127;
							imagesetpixel($image, $x, $y, imagecolorallocatealpha($image, round($pixel['red']), round($pixel['green']), round($pixel['blue']), round($pixel['alpha'])));
						}
					}
				}
			}
		}
		$this->image = $image;
		$this->width = $width;
		$this->height = $height;
	}
	
	public function resize($params)
	{
		if ($params['width'] <= 0 || $params['height'] <= 0) return;
		
		if ($params['resizeType'] == BX_RESIZE_IMAGE_EXACT)
		{
			$coeff = max($params['width'] / $this->width, $params['height'] / $this->height);
			
			$width = intval($this->width * $coeff);
			$height = intval($this->height * $coeff);
			
			$x = intval(abs(($params['width'] - $width) / 2));
			$y = intval(abs(($params['height'] - $height) / 2));
		}
		else
		{
			$coeff = min($params['width'] / $this->width, $params['height'] / $this->height);
			
			$width = intval($this->width * $coeff);
			$height = intval($this->height * $coeff);
			
			$x = 0;
			$y = 0;
			
			$params['width'] = $width;
			$params['height'] = $height;
		}
		
		$image = imagecreatetruecolor($params['width'], $params['height']);
		imagefill($image, 0, 0, -1);
		imagesavealpha($image, true);
		imagecopyresampled($image, $this->image, 0, 0, $x, $y, $width, $height, $this->width, $this->height);
		$this->image = $image;
		$this->width = $params['width'];
		$this->height = $params['height'];
		
		if ($this->type !== 'image/jpeg')
		{
			$this->clear();
		}
	}
	
	public function text($params)
	{
		if ($params['TEXT_SIZE'] < 1) return false;
		
		$color_hex = str_replace("#", "", $params['TEXT_COLOR']);
		
		$text = explode("\n", $params['TEXT']);
		$text_images = Array();
		$text_images_width = 0;
		
		foreach ($text as $textline)
		{
			if (strlen(trim($textline)) > 0)
			{
				$box = imagettfbbox($params['TEXT_SIZE'], 0, $params['TEXT_FONT'], $textline);
				
				$width = abs($box[4] + $box[0])*1.5;
				$height = abs($box[5] - $box[1])*1.5;
				
				$textline_img = imagecreatetruecolor($width, $height);
				
				imagefill($textline_img, 0, 0, -1);
				
				imagesavealpha($textline_img, true);
				
				$x = $width/6;
				$y = $height - $height/6 - $box[1];
				
				$fontcolor = imagecolorallocate($textline_img, hexdec(substr($color_hex, 0, 2)), hexdec(substr($color_hex, 2, 2)), hexdec(substr($color_hex, 4, 2)));
				imagettftext($textline_img, $params['TEXT_SIZE'], 0, $x, $y, $fontcolor, $params['TEXT_FONT'], $textline);
				
				$textline_img = \CRamWatermarkImage::trimming($textline_img);
				
				$text_images_width = max($text_images_width, imagesx($textline_img));
				
				$text_images[] = Array('img' => $textline_img, 'width' => imagesx($textline_img), 'height' => imagesy($textline_img), 'box' => $box);
			}
		}
		
		if (count($text_images) == 0) return false;
		
		$letter_box = imagettfbbox($params['TEXT_SIZE'], 0, $params['TEXT_FONT'], "x");
		$letter_width = ($letter_box[4] == -$letter_box[0])?abs($letter_box[4] - $letter_box[0]):abs($letter_box[4] + $letter_box[0])*1.5;
		$letter_height = abs($letter_box[5] - $letter_box[1])*1.5;
		
		$letter_img = imagecreatetruecolor($letter_width, $letter_height);
		imagefill($letter_img, 0, 0, -1);
		imagesavealpha($letter_img, true);
		$letter_x = $letter_width/6;
		$letter_y = $letter_height - $letter_height/6 - $letter_box[1];
		$letter_color = imagecolorallocate($letter_img, 255, 255, 255);
		imagettftext($letter_img, $params['TEXT_SIZE'], 0, $letter_x, $letter_y, $letter_color, $params['TEXT_FONT'], "x");
		$letter_img = \CRamWatermarkImage::trimming($letter_img);
		$letter_height = imagesy($letter_img);
		imagedestroy($letter_img);
		
		$one_line_height = $params['TEXT_SIZE'] * $params['TEXT_LEADING'];
		$text_bottom = $one_line_height - ($one_line_height - $letter_height) / 2;
		
		$text_images_height = 0;
		foreach ($text_images as $k => $text_image)
		{
			if ($params['TEXT_ALIGN'] === 'left')
			{
				$text_images[$k]['x'] = 0;
			}
			else if ($params['TEXT_ALIGN'] === 'center')
			{
				if ($text_image['width'] < $text_images_width)
				{
					$text_images[$k]['x'] = intval(($text_images_width - $text_image['width']) / 2);
				}
				else $text_images[$k]['x'] = 0;
			}
			else
			{
				if ($text_image['width'] < $text_images_width)
				{
					$text_images[$k]['x'] = intval($text_images_width - $text_image['width']);
				}
				else $text_images[$k]['x'] = 0;
			}
			
			$text_image_height = $text_image['height'] - $text_image['box'][1];
			
			
			$text_images[$k]['y'] = $text_images_height + $text_bottom - $text_image_height;
			$text_images_height += $one_line_height;
		}
		
		if (count($text_images) == 1)
		{
			$text_images[0]['y'] = ($text_images_height - $text_images[0]['height']) / 2;
		}
		
		$image = imagecreatetruecolor($text_images_width, $text_images_height);
		if (!$params['TEXT_BACK_COLOR']) imagefill($image, 0, 0, -1);
		else
		{
			$background_hex = str_replace("#", "", $params['TEXT_BACK_COLOR']);
			$background_color = imagecolorallocate($image, hexdec(substr($background_hex, 0, 2)), hexdec(substr($background_hex, 2, 2)), hexdec(substr($background_hex, 4, 2)));
			imagefill($image, 0, 0, $background_color);
		}
		
		imagesavealpha($image, true);
		
		foreach ($text_images as $text_image)
		{
			imagecopy($image, $text_image['img'], $text_image['x'], $text_image['y'], 0, 0, $text_image['width'], $text_image['height']);
		}
		
		$this->image = $image;
		$this->width = imagesx($this->image);
		$this->height = imagesy($this->image);
	}
	
	public function clear()
	{
		$image = imagecreatetruecolor($this->width, $this->height);
		imagefill($image, 0, 0, -1);
		imagesavealpha($image, true);
		
		for ($y = 0; $y < $this->height; $y++)
		{
			for ($x = 0; $x < $this->width; $x++)
			{
				$color_index = imagecolorat($this->image, $x, $y);
				
				if ($color_index != -1)
				{
					$color = imagecolorsforindex($this->image, $color_index);
					if ($color['red'] == 0 && $color['green'] == 0 && $color['blue'] == 0 && $color['alpha'] == 126)
					{
						imagesetpixel($image, $x, $y, -1);
					}
					else
					{
						imagesetpixel($image, $x, $y, $color_index);
					}
				}
			}
		}
		
		$this->image = $image;
	}
	
	public function optimize($destination)
	{
		switch ($this->type)
		{
			case 'image/jpeg':
			{
				if (strlen(exec('which jpegtran')))
				{
					exec('jpegtran -progressive -copy none -optimize -outfile '.$destination.' '.$destination);
				}
				else if (strlen(exec('which jpegoptim')))
				{
					exec('jpegoptim '.$destination.' -q --strip-all -–all-progressive');
				}
				break;
			}
			case 'image/png':
			{
				if (strlen(exec('which optipng')))
				{
					exec('optipng -o2 -strip all '.$destination);
				}
				break;
			}
			case 'image/webp':
			{
				if (strlen(exec('which cwebp')))
				{
					exec('cwebp -lossless '.$destination.' -o '.$destination);
				}
				break;
			}
		}
	}
	
	public function save($destination, $jpegQuality = 100)
	{
		$result = false;
		imageinterlace($this->image);
		switch ($this->type)
		{
			case 'image/jpeg':
			{
				$result = imagejpeg($this->image, $destination, $jpegQuality);
				break;
			}
			case 'image/png':
			{
				$result = imagepng($this->image, $destination, 9);
				break;
			}
			case 'image/gif':
			{
				$result = imagegif($this->image, $destination);
				break;
			}
			case 'image/bmp':
			{
				$result = imagebmp($this->image, $destination, true);
				break;
			}
			case 'image/webp':
			{
				$result = imagewebp($this->image, $destination, 100);
				break;
			}
		}
		
		return $result;
	}
	
	public function append($wm, $params, $cachePath)
	{
		if ($params['SCALE'] > 0)
		{
			$widthPercent = $this->width / ($wm->width + $params['MARGIN_LEFT'] + $params['MARGIN_RIGHT']);
			$heightPercent = $this->height / ($wm->height + $params['MARGIN_TOP'] + $params['MARGIN_BOTTOM']);
			
			if ($widthPercent < $heightPercent)
			{
				$width = intval($this->width * $params['SCALE'] / 100) - $params['MARGIN_LEFT'] - $params['MARGIN_RIGHT'];
				$height = intval($width * $wm->height / $wm->width);
			}
			else
			{
				$height = intval($this->height * $params['SCALE'] / 100) - $params['MARGIN_TOP'] - $params['MARGIN_BOTTOM'];
				$width = intval($height * $wm->width / $wm->height);
			}
			
			$cacheFile = $cachePath.'_'.$width.'_'.$height.'.png';
			
			if (file_exists($cacheFile))
			{
				$wm->destroy();
				unset($wm);
				$wm = new \CRamWatermarkImage();
				$wm->from($cacheFile);
			}
			else
			{
				$wm->resize(Array('resizeType' => BX_RESIZE_IMAGE_PROPORTIONAL, 'width' => $width, 'height' => $height));
				$wm->margin($params);
				$wm->save($cacheFile);
			}
		}
		
		if ($params['POSITION'] === 'all')
		{
			if ($params['SCALE'] > 0)
			{
				$width = floor($this->width * $params['SCALE'] / 100);
				$height = floor($this->height * $params['SCALE'] / 100);
			}
			else
			{
				$width = $wm->width;
				$height = $wm->height;
			}
			
			$horizontal = floor($this->width / $width);
			$vertical = floor($this->height / $height);
			
			if ($horizontal < 1) $horizontal = 1;
			if ($vertical < 1) $vertical = 1;
			
			for ($i=0; $i<$horizontal; $i++)
			{
				for ($j=0; $j<$vertical; $j++)
				{
					$x = intval(($this->width - $horizontal * $width) / 2) + $width * $i;
					$y = intval(($this->height - $vertical * $height) / 2) + $height * $j;
					
					imagecopy($this->image, $wm->image, $x + floor(($width - $wm->width) / 2), $y + floor(($height - $wm->height) / 2), 0, 0, $wm->width, $wm->height);
				}
			}
		}
		else if ($params['POSITION'] === 'random')
		{
			$x = rand(0, $this->width - $wm->width);
			$y = rand(0, $this->height - $wm->height);
			
			imagecopy($this->image, $wm->image, $x, $y, 0, 0, $wm->width, $wm->height);
		}
		else
		{
			switch ($params['POSITION'])
			{
				case "tl":
				{
					$x = 0;
					$y = 0;
					break;
				}
				case "tc":
				{
					$x = ($this->width - $wm->width) / 2;
					$y = 0;
					break;
				}
				case "tr":
				{
					$x = $this->width - $wm->width;
					$y = 0;
					break;
				}
				case "ml":
				{
					$x = 0;
					$y = ($this->height - $wm->height) / 2;
					break;
				}
				case "mc":
				{
					$x = ($this->width - $wm->width) / 2;
					$y = ($this->height - $wm->height) / 2;
					break;
				}
				case "mr":
				{
					$x = $this->width - $wm->width;
					$y = ($this->height - $wm->height) / 2;
					break;
				}
				case "bl":
				{
					$x = 0;
					$y = $this->height - $wm->height;
					break;
				}
				case "bc":
				{
					$x = ($this->width - $wm->width) / 2;
					$y = $this->height - $wm->height;
					break;
				}
				case "br":
				{
					$x = $this->width - $wm->width;
					$y = $this->height - $wm->height;
					break;
				}
			}
			
			imagecopy($this->image, $wm->image, $x, $y, 0, 0, $wm->width, $wm->height);
		}
	}
	
	public function destroy()
	{
		imagedestroy($this->image);
	}
}
?>