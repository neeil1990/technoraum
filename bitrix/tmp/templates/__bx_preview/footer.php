<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile(__FILE__);
if($_SERVER["REQUEST_URI"] != "/")
{
  ?></div></section></section><?
}
?>
      <footer>
        <div class="inner_footer inner_section clearfix">
          
          <div class="footer_left">
            <nav class="footer_menu">
              <div class="footer_menu_div">
                <p class="title">��������</p>
                <? $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH."/include/footer_phones.php",Array(),Array("MODE"=>"html")); ?>
              </div>
              
              <div class="footer_menu_div">  
                <p class="title">�������</p>
                <?$APPLICATION->IncludeComponent(
                      "bitrix:menu",
                      "bottom_menu",
                      array(
                        "ROOT_MENU_TYPE" => "bcat",
                        "MAX_LEVEL" => "1",
                        "CHILD_MENU_TYPE" => "left",
                        "USE_EXT" => "Y",
                        "MENU_CACHE_TYPE" => "A",
                        "MENU_CACHE_TIME" => "36000000",
                        "MENU_CACHE_USE_GROUPS" => "Y",
                        "MENU_CACHE_GET_VARS" => array(
                        ),
                        "COMPONENT_TEMPLATE" => "bottom_menu",
                        "DELAY" => "N",
                        "ALLOW_MULTI_SELECT" => "N"
                      ),
                      false,
                      array(
                        "ACTIVE_COMPONENT" => "Y"
                      )
                    );
                ?>
              </div>
              
              <div class="footer_menu_div">  
                <p class="title">���������</p>
                <?$APPLICATION->IncludeComponent("bitrix:menu", "bottom_menu", Array(
                      "ROOT_MENU_TYPE" => "bottom",  // ��� ���� ��� ������� ������
                        "MAX_LEVEL" => "1",  // ������� ����������� ����
                        "CHILD_MENU_TYPE" => "left",  // ��� ���� ��� ��������� �������
                        "USE_EXT" => "Y",  // ���������� ����� � ������� ���� .���_����.menu_ext.php
                        "MENU_CACHE_TYPE" => "A",  // ��� �����������
                        "MENU_CACHE_TIME" => "36000000",  // ����� ����������� (���.)
                        "MENU_CACHE_USE_GROUPS" => "Y",  // ��������� ����� �������
                        "MENU_CACHE_GET_VARS" => "",  // �������� ���������� �������
                        "COMPONENT_TEMPLATE" => "top_menu",
                        "DELAY" => "N",  // ����������� ���������� ������� ����
                        "ALLOW_MULTI_SELECT" => "N",  // ��������� ��������� �������� ������� ������������
                      ),
                      false,
                      array(
                      "ACTIVE_COMPONENT" => "Y"
                      )
                    );
                ?>
              </div>
            </nav>
          </div><!--/footer_left-->
          
          <div class="footer_right clearfix">
                <p class="title">�� � ��������</p>
                
                <div class="social">
                  <noindex>
                    <a href="https://vk.com/karcher_technoraum" rel="nofollow" target="_blank"><img src="<?=SITE_TEMPLATE_PATH?>/img/vk.png" alt="" /></a>
					  <a href="https://www.instagram.com/technoraum/" rel="nofollow" target="_blank"><img style="width:20px" src="<?=SITE_TEMPLATE_PATH?>/img/instagram.png" alt="" /></a>
                    <a href="https://www.facebook.com/karcher.technoraum" rel="nofollow" target="_blank"><img src="<?=SITE_TEMPLATE_PATH?>/img/fb.png" alt="" /></a>
					  <a href="https://ok.ru/technoraum" rel="nofollow" target="_blank"><img style="width:22px" src="<?=SITE_TEMPLATE_PATH?>/img/ok.png" alt="" /></a>
                  </noindex>
                </div>
          </div><!--/footer_right-->
          
          <div class="footer_bottom">
            <p class="copy">
              � ��� ����������, 2019
            </p>            
          </div><!--/footer_bottom-->
        </div>
      </footer>
    </div><!--container end-->

    <div id="dialog-form" title="������ �� ������">
        <p class="validateTips"></p>

        <form>
            <fieldset>
                <input type="hidden" name="sectionProd" id="sectionProd" value="">
                <input type="text" name="nameCredit" id="nameCredit" value="" placeholder="���� ���" class="text ui-widget-content ui-corner-all" required>
                <input type="text" name="phoneCredit" id="phoneCredit" value="" placeholder="+7 (9��) ���-��-��" class="text ui-widget-content ui-corner-all" required>
                <select name="locationCredit" id="locationCredit" required>
                    <option value="347344637">�. ���������, ������� �������� ��, 371</option>
                    <option value="347345501">�. ���������, �������� ��, 15/2</option>
                    <option value="9211625">�. ���������, ��������� �� 87</option>
                    <option value="347347013">�. ��������-��-������, ���������� ��, 262</option>
                    <option value="347346613">�. ����-�������, ������� ��, 110</option>
                    <option value="347347433">�. ������-��-����, �������� ��������, 62</option>
                </select>
                <label>
                    <input required type="checkbox" name="check" id="rule" checked="checked">
                    � �������� � <a href="/soglasie-na-obrabotku-personalnykh-dannykh/" target=_blank>��������� �������������</a> ���� ������������ ������.
                </label>
            </fieldset>
        </form>
    </div>

    <div class="popup callback_popup" id="spares_not_found_product">
        <form method="post" class="mform">
            <div class="the_form">
                <input type="hidden" name="form_id" value="10" />
                <p class="form_title">�� ����� ��, ��� ������?</p>
                <div class="the_form_div">
                    <input type="text" name="model" placeholder="������">
                </div>
                <div class="the_form_div">
                    <input type="text" name="article" placeholder="�������">
                </div>
                <div class="the_form_div">
                    <input required type="text" name="name" placeholder="���� ���">
                </div>
                <div class="the_form_div">
                    <input required type="text" name="tel" placeholder="+7 (9��) ���-��-��">
                </div>
                <div class="the_form_div the_form_div_accept">
                    <label><input required type="checkbox" name="check" checked="checked"><span>� �������� � <a href="/soglasie-na-obrabotku-personalnykh-dannykh/" target=_blank>��������� �������������</a> ���� ������������ ������.</span></label>
                </div>
                <div class="the_form_div the_form_div_submit clearfix">
                    <input type="submit" name="submit1" value="���������">
                </div>
            </div>
        </form>
    </div>
    <!--/callback_popup-->

    <div class="popup callback_popup" id="callback_popup">
      <form method="post" class="mform">
          <input type="hidden" name="city" value="<?=$_SESSION['IPOLSDEK_city']?>">
          <input type="hidden" name="link" value="<?=$APPLICATION->GetCurPage();?>">
          <input type="hidden" name="call_header" value="�������� ������">
        <div class="the_form">
          <input type="hidden" name="form_id" value="7" />
          <p class="form_title">�������� ������</p>
          <div class="the_form_div">
            <input required type="text" name="name" placeholder="���� ���">
          </div>
          <div class="the_form_div">
            <input required type="text" name="phone" placeholder="+7 (9��) ���-��-��">
          </div>
          <div class="the_form_div the_form_div_accept">
            <label><input required type="checkbox" name="check" checked="checked"><span>� �������� � <a href="/soglasie-na-obrabotku-personalnykh-dannykh/" target=_blank>��������� �������������</a> ���� ������������ ������.</span></label>
          </div>
          <div class="the_form_div the_form_div_submit clearfix">
            <input type="submit" name="submit1" value="���������">
          </div>
        </div>
      </form>
    </div>
    <!--/callback_popup-->
    
    <div class="popup callback_popup" id="login">
      <form method="post" class="login_form">
        <div class="the_form">
          <p class="form_title">�����������</p>
          <div class="error"></div>
          <div class="the_form_div">
            <label>E-mail</label>
            <input type="text" required name="login" placeholder="������� e-mail">
          </div>
          <div class="the_form_div">
            <label>������</label>
            <input type="password" required name="password" placeholder="**********">
          </div>
          <div class="the_form_div the_form_div_submit clearfix">
            <input type="submit" name="submit1" value="�����">                               
          </div>
        </div>
      </form>
      <br>
      <a href="/login/?forgot_password=yes" class="open_p">������ ������?</a>
    </div>
    
    <div class="popup callback_popup" id="reg">
      <form method="post" class="reg_form">
        <div class="the_form">
          <p class="form_title">�����������</p>
          <div class="error"></div>
          <div class="the_form_div">
            <label>���</label>
            <input type="text" required name="name" placeholder="���">
          </div>
          <div class="the_form_div">
            <label>Email</label>
            <input type="email" required name="email" placeholder="��� E-mail">
          </div>
          <div class="the_form_div">
            <label>������</label>
            <input type="password" required name="password" placeholder="������">
          </div>
          <div class="the_form_div">
            <label>��������� ������</label>
            <input type="password" required name="rpassword" placeholder="��������� ������">
          </div>
          <div class="the_form_div">
            <div class="g-recaptcha" data-sitekey="6Lf8KEkUAAAAAKBM615YBWb_V11KzgYbJ_2GrOVK"></div>
          </div>
          <div class="the_form_div the_form_div_submit clearfix">
            <input type="submit" name="submit1" value="�����������">                               
          </div>
        </div>
      </form>
    </div>

    <a class="open_thanks fancy" href="#thanks_popup"></a>
    <a class="open_thanks2 fancy" href="#thanks_popup2"></a>
    <a class="sub_error fancy" href="#sub_error"></a>
    <a class="reg_ok fancy" href="#reg_ok"></a>
    <a class="s_pass fancy" href="#s_pass"></a>
    
    <div class="popup thanks_popup" id="thanks_popup">
        
        <p class="title">������� �� ������</p>
        
        <p>
          �� �������� � ���� � ��������� �����
        </p>
        
        <img class="thanks_check" src="<?=SITE_TEMPLATE_PATH?>/images/success.svg" alt="" />
        
         
    </div>
    <div class="popup thanks_popup" id="thanks_popup2">
        
        <p class="title">�������!</p>
        
        <p>
          �� ��� e-mail ����� ���� ���������� ������ ��� ������������� ��������
        </p>
        
        <img class="thanks_check" src="<?=SITE_TEMPLATE_PATH?>/images/success.svg" alt="" />
        
         
    </div>
    <div class="popup thanks_popup" id="sub_error">
        
        <p class="title">������</p>
        
        <p>
          ������ e-mail ����� ��� ������� � ������ ��������
        </p>
        
        <div class="error"><span>X</span></div>
        
         
    </div>
    <div class="popup thanks_popup" id="reg_ok">
        
        <p class="title">������� �� �����������</p>
        
        <p>
          ������ �� ������ ����� � �������, ��������� ���� ����� � ������
        </p>
        
        <img class="thanks_check" src="<?=SITE_TEMPLATE_PATH?>/images/success.svg" alt="" />

         
    </div>


<!-- BEGIN JIVOSITE CODE {literal} -->

<!-- {/literal} END JIVOSITE CODE -->

		<script>new WOW().init();</script>
		<style>
			body{overflow-x:hidden}
			.news-detail{font-weight:100}
			.card_page_descr .text_toggling_div.desktop{height: auto !important}
			.card_page_descr .read_more_toggler{display:none !important}

			@media(max-width:800px)
			{
				.card_page_descr .read_more_toggler{display:block !important}
			}
			@media(min-width:1000px)
			{
				.card_page_img .big_img{height:auto}
				.header_menu_dropdown .inner_section > ul{width:100% !important}
				.header_menu_dropdown li.has_ul{display:flex;width:100%}
				.header_menu_dropdown li.has_ul a{width:40%}
				.header_menu_dropdown > .inner_section{display:flex}
				.header_menu_dropdown > .inner_section ul:first-child{width:50% !important}
				.header_menu_dropdown > .inner_section ul:last-child{width:60% !important}
				.header_menu_dropdown .header_menu_dropdown_level2 ul{padding: 0 0 55px;width:50%}
				.header_menu_dropdown .header_menu_dropdown_level2 ul li a{width:100%}
				.header_menu_dropdown .header_menu_dropdown_level2{width:100%;padding:0 30px;opacity:1;display:none;position:static}
				.header_menu_dropdown .header_menu_dropdown_level2.open{display:flex}
			}
			.ez-checked {background-position: 0px -24px !important}
		</style>
		<script>
			$(document).ready(function()
			{
				if(screen.width <= 800)
					$(".card_page_descr .text_toggling_div.desktop").removeClass("desktop");

				$(".header_menu a.opensub").mouseenter(function()
				{
					var id = $(this).attr("data-index");
					$(this).closest(".header_menu_dropdown").find(".header_menu_dropdown_level2").removeClass("open");
					$(this).closest(".header_menu_dropdown").find(".header_menu_dropdown_level2[data-index='"+id+"']").addClass("open");
				});
			});
		</script>

        <script src="//code.jivosite.com/widget.js" data-jv-id="3Kdh1V3ZKW" async></script>

  </body>
<?
			$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/jquery.fancybox.css");
			$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/flexslider.css");
			$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/jquery.selectBox.css");
			$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/animate.css");
			$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/slick.css");
			$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/slick-theme.css");
			$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/jquery.nouislider.min.css");
			$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/jquery.custom-scrollbar.css");
			$APPLICATION->SetAdditionalCss('/bitrix/css/main/bootstrap.min.css');
			$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/css/style.css?ver=1.01");
			//tree menu
			$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/js/tree-menu/css/dtree.css");

			//alertifyjs
			$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/js/alertifyjs/css/alertify.min.css");
			$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/js/alertifyjs/css/themes/default.min.css");

			$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/css/jquery-1.11.0.min.js");
			$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/css/bootstrap.min.js");
			$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/css/jquery.fancybox.js");
			$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/css/jquery.flexslider-min.js");
			$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/css/jquery.validate.js");
			$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/css/scriptjava_sender.js");
			$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/css/jquery.selectBox.js");
			$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/css/maskedinput.js");
			$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/css/nouislider.min.js");
			$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/css/wow.min.js");
			$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/css/jquery.custom-scrollbar.min.js");
			$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/css/slick.js");
			$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/css/main.js?ver=1.01");
			$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/css/news_filter.js");
			$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/css/catalog_filter.js");
			$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/css/fos.js");


            //jquery-ui
            $APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/js/jquery-ui/css/jquery-ui.css");
			$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/jquery-ui/js/jquery-ui.js");
            //tinytoggle js checkbox manager
            $APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/js/tinytoggle/css/tiny-toggle.css");
            $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/tinytoggle/js/tiny-toggle.js");

            //anchor scroll
            $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/jquery.anchorlink.js");

            //jquery.maskinput
            $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/jquery.maskinput.js");

            //tree menu
            $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/tree-menu/js/dtree.js");

            //cookie
            $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/jquery.cookie.js");


            //alertify js
            $APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/alertifyjs/alertify.min.js");

            //custom script
			$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/js/script.js");


		?>

<!--schema.org-->
<script type="application/ld+json">
    {
        "@context": "http://schema.org",
        "@type": "LocalBusiness",
        "name": "���������",
        "image": "https://technoraum.ru/bitrix/templates/TechnoRaum/img/logo.png",
        "url": "https://technoraum.ru",
        "telephone": "8-800-777-57-01",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "��������, 15/2",
            "addressLocality": "���������",
            "addressCountry": "Russia",
            "addressRegion": "������������� ����"
        },
        "openingHours": [
            "��-��: 10:00 - 21:00"
        ],
        "priceRange": "100-5000RUB"
    }
</script>
<!--//schema.org-->

<div style="display: none;">
<!-- Top DragoWeb for: `technoraum.ru` id: `373891` -->
<a href="https://dragoweb.ru/" rel="nofollow" 
target="_blank" title="DragoWeb">
<noscript charset="utf-8" type="text/javascript"
src="//count.dragoweb.ru/jsn.js"></noscript>
</a><!-- /Top DragoWeb -->
</div>
</html>