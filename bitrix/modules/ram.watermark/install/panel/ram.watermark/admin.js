var ramwmCache = {};
var ramwmOver = {};
var ramwmParams = null;
var ramwmUploadDir = '';
function RamWmAdminV1Ajax(steps, data)
{
	if (steps.length)
	{
		var step = steps[0];
		if (step != 'uninstall')
		{
			$.ajax({url: '/bitrix/tools/ram.watermark.php', type: 'POST', data: {action: 'v1_ajax', step: step, data: data}}).done(function(msg)
			{
				msg = $.parseJSON(msg);
				
				if (msg.data) data = msg.data;
				else data = null;
				
				if (!msg.title)
				{
					alert(ramwmadmin_error);
				}
				else
				{
					if ($('.ramwmadmin-v1-ajax-item[data-step="'+step+'"]').length)
					{
						$('.ramwmadmin-v1-ajax-item[data-step="'+step+'"]').html(msg.title);
					}
					else
					{
						$('.ramwmadmin-v1-ajax').append('<p class="ramwmadmin-v1-ajax-item" data-step="'+step+'">'+msg.title+'</p>');
					}
					
					if (msg.status == 'next')
					{
						setTimeout(function(){RamWmAdminV1Ajax(steps.slice(1), data);}, 100);
					}
					else
					{
						setTimeout(function(){RamWmAdminV1Ajax(steps, data);}, 100);
					}
				}
			});
		}
		else
		{
			$('.ramwmadmin-v1-ajax').append('<p class="ramwmadmin-v1-ajax-item">'+ramwmadmin_v1_finish+'</p>');
			setTimeout(function(){document.location = document.location + "&step=2";}, 2000);
		}
	}
	else
	{
		$('.ramwmadmin-v1-ajax').append('<p class="ramwmadmin-v1-ajax-item">'+ramwmadmin_v1_finish+'</p>');
	}
}
function RamWmAdminOnReady()
{
	var form = $('#ram-watermark-form').serializeArray();
	var params = {};
	var fontsDir = '/'+ramwmUploadDir+'/ram.watermark/fonts/';
	var wmDir = '/'+ramwmUploadDir+'/ram.watermark/images/watermarks/';
	for (var i in form)
	{
		if (form[i].name.search('PARAMS') == 0)
		{
			params[form[i].name.replace('PARAMS\[', '').replace('\]', '')] = form[i].value;
		}
	}
	if (params.TYPE == 'text')
	{
		
	}
	else
	{
		var img = new Image();
		img.onload = function()
		{
			RamWmAdminUpdateParams(null);
		};
		img.src = wmDir+params.IMAGE;
	}
}
$(window).load(function()
{
	if ($('.ramwmadmin-params').length)
	{
		RamWmAdminUpdateParams(null);
	}
});
$(document).ready(function()
{
	if ($('.ramwmadmin-params').length)
	{
		RamWmAdminTypeChange($('[onchange^="RamWmAdminTypeChange(this);"]').first());
		RamWmAdminScaleChange($('[oninput^="RamWmAdminScaleChange(this);"]').first());
		RamWmAdminReduceSizeChange($('[onchange^="RamWmAdminReduceSizeChange(this);"]').first());
		RamWmAdminScrollFonts();
		RamWmAdminScrollImages();
		$(window).on('resize', function()
		{
			RamWmAdminUpdateParams(null);
		});
		$(window).on('scroll', function()
		{
			RamWmAdminScrollPreview();
		});
		BX.addCustomEvent("onAdminMenuResize", function(width)
		{
			RamWmAdminUpdateParams(null);
		});
		BX.addCustomEvent("onAdminTabsChange", function(width)
		{
			RamWmAdminUpdateParams(null);
		});
		$('body').on('click', '.ramwmadmin-scheme', function()
		{
			$('.ramwmadmin-preview').toggleClass('scheme');
		});
		$('body').on('click', '.ramwmadmin-background', function()
		{
			$('.ramwmadmin-preview').attr('data-color', $(this).attr('data-color'));
		});
		$('body').on('input', '.ramwmadmin-range input[type="range"]', function()
		{
			$(this).parent().parent().find('input[type="text"]').val($(this).val());
		});
		$('body').on('change', '.ramwmadmin-range + input[type="text"]', function(e)
		{
			var rangeItem = $(this).parent().find('input[type="range"]');
			var min = parseFloat($(rangeItem).attr('min'));
			var max = parseFloat($(rangeItem).attr('max'));
			var step = parseFloat($(rangeItem).attr('step'));
			var value = $(this).val().length && !isNaN($(this).val()) ? parseFloat($(this).val()) : min;
			if (value < min) value = min;
			else if (value > max) value = max;
			else if (step)
			{
				var stepPart = value % step;
				if (stepPart > 0)
				{
					if (stepPart > step / 2) value = value - stepPart + step;
					else value = value - stepPart;
				}
				else if (stepPart < 0)
				{
					if (Math.abs(stepPart) > step / 2) value = value - stepPart - step;
					else value = value - stepPart;
				}
			}
			$(this).val(value);
			$(rangeItem).val(value).change().trigger('input');
			$(this).blur();
		});
		$('body').on('keydown', '#ram-watermark-form', function(e)
		{
			if (e.keyCode == 13 && e.target.type == 'text')
			{
				if (e.target.name == 'range')
				{
					$(e.target).trigger('change');
				}
				e.preventDefault();
				return false;
			}
		});
		$('body').on('change', '.ramwmadmin-color input[type="text"]', function()
		{
			$(this).parent().css('background-color', $(this).val());
		});
		
		$('input[name="PARAMS[TEXT_COLOR]"]').ColorPicker(
		{
			onChange: function(hsb, hex, rgb, el)
			{
				$(el).val('#'+hex).change();
			},
			onBeforeShow: function()
			{
				$(this).ColorPickerSetColor(this.value);
			}
		}).bind('keyup', function()
		{
			$(this).ColorPickerSetColor(this.value);
		});
		$('body').on('click', '.ramwmadmin-object', function()
		{
			$(this).toggleClass('ramwmadmin-filterobject_opened');
		});
		RamWmAdminLimitTypeChange($('[onchange^="RamWmAdminLimitTypeChange(this);"]').first());
		RamWmAdminLimitSizesChange($('[onchange^="RamWmAdminLimitSizesChange(this);"]').first());
		RamWmAdminLimitDateChange($('[onchange^="RamWmAdminLimitDateChange(this);"]').first());
	}
});
function RamWmAdminOnOver(position)
{
	ramwmOver[position] = true;
	$('.ramwmadmin-preview'+position).addClass('ramwmadmin-previewhover');
}
function RamWmAdminOnOut(position)
{
	ramwmOver[position] = false;
	$('.ramwmadmin-preview'+position).removeClass('ramwmadmin-previewhover');
}
function RamWmAdminTypeChange(item)
{
	var v = $(item).val();
	if (v == 'text')
	{
		$('.ramwmadmin-paramgroup_text').removeClass('ramwmadmin-paramgroup_text_hide');
		$('.ramwmadmin-paramgroup_image').addClass('ramwmadmin-paramgroup_image_hide');
	}
	else
	{
		$('.ramwmadmin-paramgroup_image').removeClass('ramwmadmin-paramgroup_image_hide');
		$('.ramwmadmin-paramgroup_text').addClass('ramwmadmin-paramgroup_text_hide');
	}
}
function RamWmAdminScaleChange(item)
{
	var v = $(item).val();
	if (v == 0)
	{
		$('.ramwmadmin-paramgroup_scale').removeClass('ramwmadmin-paramgroup_scale_hide');
	}
	else
	{
		$('.ramwmadmin-paramgroup_scale').addClass('ramwmadmin-paramgroup_scale_hide');
	}
}
function RamWmAdminReduceSizeChange(item)
{
	var v = $(item).prop('checked');
	if (v)
	{
		$('.ramwmadmin-paramgroup_reducesize').removeClass('ramwmadmin-paramgroup_reducesize_hide');
	}
	else
	{
		$('.ramwmadmin-paramgroup_reducesize').addClass('ramwmadmin-paramgroup_reducesize_hide');
	}
}
function RamWmAdminUpdateParams(item)
{
	var form = $('#ram-watermark-form').serializeArray();
	var params = {};
	var fontsDir = '/'+ramwmUploadDir+'/ram.watermark/fonts/';
	var wmDir = '/'+ramwmUploadDir+'/ram.watermark/images/watermarks/';
	for (var i in form)
	{
		if (form[i].name.search('PARAMS') == 0)
		{
			params[form[i].name.replace('PARAMS\[', '').replace('\]', '')] = form[i].value;
		}
	}
	if (params.SCALE > 0 && params.SCALE < 10)
	{
		params.SCALE = 10;
	}
	var geometryUpdate = true;
	if (item != null && ramwmParams != null)
	{
		if ($(item).attr('name') == 'PARAMS[TEXT_COLOR]')
		{
			geometryUpdate = false;
			$('.ramwmadmin-previewmarkcontent').css('color', params.TEXT_COLOR);
		}
		else if ($(item).attr('name') == 'PARAMS[TRANSPARENT]')
		{
			geometryUpdate = false;
			$('.ramwmadmin-previewmarkcontent').css('opacity', (100 - params.TRANSPARENT)/100);
		}
		if ($(item).attr('name') == 'PARAMS[TYPE]')
		{
			if (params.TYPE == 'text')
			{
				RamWmAdminScrollFonts();
			}
			else
			{
				RamWmAdminScrollImages();
			}
		}
	}
	ramwmParams = params;
	if (geometryUpdate)
	{
		if (params.TYPE == 'text')
		{
			RamWmAdminPrepareParams(params, wmDir, fontsDir);
		}
		else
		{
			if (ramwmCache[wmDir+params.IMAGE])
			{
				RamWmAdminPrepareParams(params, wmDir, fontsDir);
			}
			else
			{
				var img = new Image();
				img.onload = function()
				{
					ramwmCache[wmDir+params.IMAGE] = {width: parseInt(this.width), height: parseInt(this.height)};
					RamWmAdminPrepareParams(params, wmDir, fontsDir);
				};
				img.src = wmDir+params.IMAGE;
			}
		}
	}
}
function RamWmAdminPrepareParams(params, wmDir, fontsDir)
{
	if (params.TYPE == 'text')
	{
		params.TEXT = params.TEXT.replace(new RegExp('\r\n', 'g'), '<br/>');
		var wm = $('<div class="ramwmadmin-previewmark"><div class="ramwmadmin-previewmarkcontent">'+params.TEXT+'</div></div>');
		wm.css('color', params.TEXT_COLOR);
		wm.css('line-height', params.TEXT_LEADING);
		wm.css('text-align', params.TEXT_ALIGN);
		wm.css('font-family', params.TEXT_FONT.replace('.ttf', '').replace(/[^\w\s]/g, '').toLowerCase());
		if (parseInt(params.SCALE) > 0)
		{
			wm.css('font-size', (30+parseInt(params.SCALE)/params.TEXT.length*10)+'px');
		}
		else
		{
			wm.css('font-size', params.TEXT_SIZE+'px');
		}
		wm.find('.ramwmadmin-previewmarkcontent').css('opacity', 0);
		$('.ramwmadmin-preview').append(wm);
		RamWmAdminUpdatePreview(
		{
			data: wm,
			dataType: 'text',
			imgWidth: parseInt($('.ramwmadmin-preview').width()),
			imgHeight: parseInt($('.ramwmadmin-preview').height()),
			wmWidth: wm.width(),
			wmHeight: wm.height(),
			scale: parseInt(params.SCALE),
			rotate: parseInt(params.ROTATE),
			transparent: parseInt(params.TRANSPARENT),
			top: parseInt(params.MARGIN_TOP),
			right: parseInt(params.MARGIN_RIGHT),
			bottom: parseInt(params.MARGIN_BOTTOM),
			left: parseInt(params.MARGIN_LEFT),
			position: params.POSITION,
		});
	}
	else
	{
		RamWmAdminUpdatePreview(
		{
			data: $('<div class="ramwmadmin-previewmark"><img class="ramwmadmin-previewmarkcontent" src="'+wmDir+params.IMAGE+'" /></div>'),
			dataType: 'image',
			imgWidth: parseInt($('.ramwmadmin-preview').width()),
			imgHeight: parseInt($('.ramwmadmin-preview').height()),
			wmWidth: ramwmCache[wmDir+params.IMAGE].width,
			wmHeight: ramwmCache[wmDir+params.IMAGE].height,
			scale: parseInt(params.SCALE),
			rotate: parseInt(params.ROTATE),
			transparent: parseInt(params.TRANSPARENT),
			top: parseInt(params.MARGIN_TOP),
			right: parseInt(params.MARGIN_RIGHT),
			bottom: parseInt(params.MARGIN_BOTTOM),
			left: parseInt(params.MARGIN_LEFT),
			position: params.POSITION,
		});
	}
}
function RamWmAdminUpdatePreview(params)
{
	var wmRatio = params.wmWidth / params.wmHeight;
	var mark = params.data;
	var rotate;
	if (params.rotate == 270 || params.rotate == 180 || params.rotate == 90) rotate = params.rotate;
	else if (params.rotate > 270) rotate = 360 - params.rotate;
	else if (params.rotate > 180) rotate = params.rotate % 90;
	else if (params.rotate > 90) rotate = 180 - params.rotate;
	else rotate = params.rotate;
	var radians = rotate * Math.PI / 180;
	var sin = Math.sin(radians);
	var cos = Math.cos(radians);
	var wmWidth = params.wmWidth;
	var wmHeight = params.wmHeight;
	var boundWidth = 0;
	var boundHeight = 0;
	if (params.scale > 0)
	{
		boundWidth = Math.floor(params.imgWidth * params.scale / 100 - (params.left + params.right));
		boundHeight = Math.floor(params.imgHeight * params.scale / 100 - (params.top + params.bottom));
		wmWidth = Math.floor(Math.abs(boundWidth / (wmRatio * cos + sin) * wmRatio));
		wmHeight = Math.floor(wmWidth / wmRatio);
		var boundTestWidth = Math.floor(Math.abs(wmWidth * cos + wmHeight * sin));
		var boundTestHeight = Math.floor(Math.abs(wmWidth * sin + wmHeight * cos));
		if (boundTestWidth - boundWidth > 1 || boundTestHeight - boundHeight > 1)
		{
			wmWidth = Math.abs(boundHeight / (wmRatio * sin + cos) * wmRatio);
			wmHeight = wmWidth / wmRatio;
		}
	}
	if (params.scale == 0 || params.position != 'all')
	{
		if (params.rotate > 0)
		{
			boundWidth = Math.floor(Math.abs(wmWidth * cos + wmHeight * sin));
			boundHeight = Math.floor(Math.abs(wmWidth * sin + wmHeight * cos));
		}
		else
		{
			boundWidth = wmWidth;
			boundHeight = wmHeight;
		}
	}
	var transform = 'rotate('+params.rotate+'deg)';
	if (params.dataType == 'image')
	{
		mark.css('width', wmWidth+'px');
		mark.css('left', (params.left + (boundWidth - wmWidth) / 2) + 'px');
		mark.css('top', (params.top + (boundHeight - wmHeight) / 2) + 'px');
	}
	else
	{
		mark.css('left', (params.left + (boundWidth - params.wmWidth) / 2) + 'px');
		mark.css('top', (params.top + (boundHeight - params.wmHeight) / 2) + 'px');
		if (params.scale > 0)
		{
			transform += 'scale(' + (wmWidth / params.wmWidth) + ')';
		}
	}
	mark.css('transform', transform);
	mark.find('.ramwmadmin-previewmarkcontent').css('opacity', (100 - params.transparent)/100);
	var previewTop = $('<div class="ramwmadmin-previewtop"></div>');
	var previewRight = $('<div class="ramwmadmin-previewright"></div>');
	var previewBottom = $('<div class="ramwmadmin-previewbottom"></div>');
	var previewLeft = $('<div class="ramwmadmin-previewleft"></div>');
	previewTop.css('height', params.top+'px');
	previewRight.css('width', params.right+'px');
	previewBottom.css('height', params.bottom+'px');
	previewLeft.css('width', params.left+'px');
	var previewWrap = $('<div class="ramwmadmin-previewwrap"></div>');
	var wrapWidth = boundWidth + params.left + params.right;
	var wrapHeight = boundHeight + params.top + params.bottom;
	previewWrap.css('width', wrapWidth + 'px');
	previewWrap.css('height', wrapHeight + 'px');
	previewWrap.append(previewTop).append(previewRight).append(previewBottom).append(previewLeft).append(mark);
	var positions = [];
	switch (params.position)
	{
		case 'all':
		{
			var wCount = Math.floor(params.imgWidth / wrapWidth);
			var hCount = Math.floor(params.imgHeight / wrapHeight);
			if (wCount < 1) wCount = 1;
			if (hCount < 1) hCount = 1;
			var start = {x: params.imgWidth / 2 - (wCount * wrapWidth) / 2, y: params.imgHeight / 2 - (hCount * wrapHeight) / 2};
			for (var i=0; i<hCount; i++)
			{
				for (var j=0; j<wCount; j++)
				{
					positions.push([start.x + j * wrapWidth, start.y + i * wrapHeight]);
				}
			}
			break;
		}
		case 'random':
		{
			// positions.push([Math.floor(Math.random() * Math.floor(params.imgWidth - wrapWidth - params.right)), Math.floor(Math.random() * Math.floor(params.imgHeight - wrapHeight - params.bottom))]);
			positions.push([(params.imgWidth - wrapWidth) / 2, (params.imgHeight - wrapHeight) / 2]);
			break;
		}
		case 'tl':
		{
			positions.push([0, 0]);
			break
		}
		case 'tc':
		{
			positions.push([(params.imgWidth - wrapWidth) / 2, 0]);
			break
		}
		case 'tr':
		{
			positions.push([params.imgWidth - wrapWidth, 0]);
			break
		}
		case 'ml':
		{
			positions.push([0, (params.imgHeight - wrapHeight) / 2]);
			break
		}
		case 'mc':
		{
			positions.push([(params.imgWidth - wrapWidth) / 2, (params.imgHeight - wrapHeight) / 2]);
			break
		}
		case 'mr':
		{
			positions.push([params.imgWidth - wrapWidth, (params.imgHeight - wrapHeight) / 2]);
			break
		}
		case 'bl':
		{
			positions.push([0, params.imgHeight - wrapHeight]);
			break
		}
		case 'bc':
		{
			positions.push([(params.imgWidth - wrapWidth) / 2, params.imgHeight - wrapHeight]);
			break
		}
		case 'br':
		{
			positions.push([params.imgWidth - wrapWidth, params.imgHeight - wrapHeight]);
			break
		}
	}
	$('.ramwmadmin-preview .ramwmadmin-previewwrap').remove();
	for (var i in positions)
	{
		var wrapCopy = previewWrap.clone(true);
		wrapCopy.css('left', positions[i][0]);
		wrapCopy.css('top', positions[i][1]);
		$('.ramwmadmin-preview').append(wrapCopy);
	}
	for (var i in ramwmOver)
	{
		if (ramwmOver[i])
		{
			RamWmAdminOnOver(i);
		}
	}
}
function RamWmAdminScrollPreview()
{
	var wScroll = $(window).scrollTop();
	var previewOffset = $('.ramwmadmin-preview').parent().offset().top;
	var previewHeight = $('.ramwmadmin-preview').parent().height();
	var tabsHeight = $('.adm-detail-tabs-block-fixed.bx-fixed-top').height() || 0;
	var top = 0;
	if (wScroll > (previewOffset - tabsHeight))
	{
		if (wScroll - previewOffset + tabsHeight + $('.ramwmadmin-preview').outerHeight() < previewHeight)
		{
			top = wScroll - previewOffset + tabsHeight;
		}
		else
		{
			top = previewHeight - $('.ramwmadmin-preview').outerHeight();
		}
	}
	if (top < 0) top = 0;
	$('.ramwmadmin-preview').stop().animate({'marginTop': top}, 200);
}
function RamWmAdminScrollFonts()
{
	$('.ramwmadmin-fonts').scrollTop(0);
	$('.ramwmadmin-fonts').scrollTop($('input[name="PARAMS[TEXT_FONT]"]:checked').parent().offset().top - $('.ramwmadmin-fonts').offset().top - 4);
}
function RamWmAdminScrollImages()
{
	$('.ramwmadmin-images').scrollTop(0);
	$('.ramwmadmin-images').scrollTop($('input[name="PARAMS[IMAGE]"]:checked').parent().offset().top - $('.ramwmadmin-images').offset().top - 4);
}
function RamWmAdminUploadFont(item)
{
	var files = item.files;
	if (files.length)
	{
		var arData = new FormData();
		$.each(files, function(key, value)
		{
			arData.append(key, value);
		});
		$.ajax({url: '/bitrix/tools/ram.watermark.php?action=uploadfont&font='+$('input[name="PARAMS[TEXT_FONT]"]:checked').val(), type: 'POST', data: arData, cache: false, dataType: 'json', processData: false, contentType: false}).done(function(msg)
		{
			if (msg.status == 'error')
			{
				alert(msg.message);
			}
			else if (msg.status == 'success')
			{
				$('.ramwmadmin-fonts').append(msg.font);
				alert(msg.message);
			}
			$('.ramwmadmin-fontupload input').val('');
			BX.fireEvent(BX('ramwmadmin-fontuploadinput'), 'change');
		});
	}
	return false;
}
function RamWmAdminUploadImage(item)
{
	var files = item.files;
	if (files.length)
	{
		var arData = new FormData();
		$.each(files, function(key, value)
		{
			arData.append(key, value);
		});
		$.ajax({url: '/bitrix/tools/ram.watermark.php?action=uploadimage&image='+$('input[name="PARAMS[IMAGE]"]:checked').val(), type: 'POST', data: arData, cache: false, dataType: 'json', processData: false, contentType: false}).done(function(msg)
		{
			if (msg.status == 'error')
			{
				alert(msg.message);
			}
			else if (msg.status == 'success')
			{
				$('.ramwmadmin-images').append(msg.image);
				alert(msg.message);
			}
			$('.ramwmadmin-imageupload input').val('');					
			BX.fireEvent(BX('ramwmadmin-imageuploadinput'), 'change');
		});
	}
	return false;
}
function RamWmAdminFilterDelete(item)
{
	$(item).parent().remove();
}
function RamWmAdminFilterCopy(item)
{
	var filterClone = $(item).parent().clone(true, true);
	
	$(item).parent().find('select').each(function(index)
	{
		$(filterClone).find('select:eq('+index+')').val($(this).val());
	});
	
	$(item).parent().find('input[type="checkbox"]').each(function(index)
	{
		if ($(this).prop('checked'))
		{
			$(filterClone).find('input[type="checkbox"]:eq('+index+')').prop('checked', true);
		}
	});
	
	$(filterClone).insertAfter($(item).parent());
}
function RamWmAdminFilterSelectChange(item)
{
	if ($(item).val())
	{
		$.ajax({url: '/bitrix/tools/ram.watermark.php', type: 'POST', data: {action: 'admin_filter', id: $(item).val()}}).done(function(result)
		{
			$(item).parent().nextAll('.ramwmadmin-filterselect').remove();
			$(item).parentsUntil('.ramwmadmin-filter').last().parent().find('.ramwmadmin-filterfields').html('<div class="ramwmadmin-filteremptyfield">'+ramwmEmptySelect+'</div>');
			result = $.parseJSON(result);
			if (result.list)
			{
				$(result.list).insertAfter($(item).parent());
			}
			if (result.fields)
			{
				$(item).parentsUntil('.ramwmadmin-filter').last().parent().find('.ramwmadmin-filterfields').html(result.fields);
				BX.adminPanel.modifyFormElements(BX('filter_edit_table'));
			}
		});
	}
	else
	{
		$(item).parent().nextAll('.ramwmadmin-filterselect').remove();
		$(item).parentsUntil('.ramwmadmin-filter').last().parent().find('.ramwmadmin-filterfields').html('<div class="ramwmadmin-filteremptyfield">'+ramwmEmptySelect+'</div>');
	}
}
function RamWmAdminFilterAdd(item, type)
{
	$.ajax({url: '/bitrix/tools/ram.watermark.php', type: 'POST', data: {action: 'admin_add_filter', type: type}}).done(function(result)
	{
		$(result).insertBefore(item);
	});
}
function RamWmAdminLimitTypeChange(item)
{
	var v = $(item).prop('checked');
	if (v)
	{
		$('.ramwmadmin-paramgroup_limittype').removeClass('ramwmadmin-paramgroup_limittype_hide');
	}
	else
	{
		$('.ramwmadmin-paramgroup_limittype').addClass('ramwmadmin-paramgroup_limittype_hide');
	}
}
function RamWmAdminLimitSizesChange(item)
{
	var v = $(item).prop('checked');
	if (v)
	{
		$('.ramwmadmin-paramgroup_limitsizes').removeClass('ramwmadmin-paramgroup_limitsizes_hide');
	}
	else
	{
		$('.ramwmadmin-paramgroup_limitsizes').addClass('ramwmadmin-paramgroup_limitsizes_hide');
	}
}
function RamWmAdminLimitDateChange(item)
{
	var v = $(item).prop('checked');
	if (v)
	{
		$('.ramwmadmin-paramgroup_limitdate').removeClass('ramwmadmin-paramgroup_limitdate_hide');
	}
	else
	{
		$('.ramwmadmin-paramgroup_limitdate').addClass('ramwmadmin-paramgroup_limitdate_hide');
	}
}