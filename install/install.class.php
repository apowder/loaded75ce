<?php

/**
 * This file is part of Loaded Commerce.
 *
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */
class install {

    public $log = [];
    private $current_step = 'start';
    private $previous = 'start';
    private $steps = array();
    private $order = array();
    private $done = array();
    private $retry_step = false;
    public $data = array();

    public function log($type, $message) {
        $this->log[] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    public function init() {
        $this->current_step = $_POST['current_step'];
        if (empty($this->current_step)) {
            $this->current_step = 'start';
        }

        if (defined('TL_INSTALLED') && TL_INSTALLED) {
            if ($this->current_step == 'end' && !isset($_POST['next'])) {

            } elseif ($this->current_step != 'end') {
                echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                    <html>
                        <head>
                            <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
                            <link rel="shortcut icon" href="data:image/gif;base64,R0lGODlhQAA5APf/AL3BxPP5/LG2uStorLW6vebq7N7h5Diq4Ojs7pKYnOLm6MbKzV6Bua2xtSuNydba3VmTyS15uSyIxSmh2ipiqKSqrjtanaassfH2+SttsCxprSmk3HqWyClSnCx+vZieouTq9aCmquru8Tt4tmSQxqKorCuW0bm+wZqgpStmqypgpoWZxoOIjCem3khhpZyktuzw84OMpo2Sl9bh8YmXqsHFyGuPxCqa1M7R1LvV7dre4FhyqdHW2S1xs52jqMrO0EhcnCdElJ6kqCtqriuSzUaj2Zyipiqd1ixusZuoye/z9ilaooiNkSpepCtlqipaomN5rCyEwihOmilWnyhMmClQmylYoSlhp/H19+Dk5tjc3s3b79LW2CyGw8DDxihJl298syxtsChRnNDT1i1mqjNOmilbo6ius+Xo68jMzyqY0iyCwCp2tytnrCljqSpcpChVnix/vixytCx2tyhUnZedoSlepaSrsCie2CqUzjddoSlXnypkqdzg4yyOyqqvsihDk36Ch6ClqS11tnCPxCpWnyt6uildpCdHlTNkqaitsipYodPX2itwsyx9vCpztSpUnilLmO7y9Ctsrylfpix0tauyt8zQ05mfo620uayzuK+2uy58u6qxttzf4rO4uyuPy7C3vKmwtSphp+Tn6illqq+0typqraesr/P6/tTY2+Ll6K21uq61uqivtCdLmN/l8srW6yxvscTIy+3x8yuHxNPY2+Pn6ipOmix3t+ns7ylOmSuLx+nv8zRgpSt8u4OIvNDZ5N7j6Njc6unv+be/zo/A37i9z3i12O/1/ClprYW73D9trVBfmsLI1qXN6Kqww8bQ1dDX3aCrudLf8SpZoS5SnCyBv5rH436FumaArmaCssjM23CRsurv9+Pp7K290i1UnrXG3Nrg5ZadpsXT6sbZ7/H3/CyUz+Hl6Le7vuvv8S5vsk+b0pCZp+/095aboK7Q6lCFvImVuC5gppKarqS53K+018TH4YeSsoOZu5ifroqYvymo4PT7/gAAACH/C1hNUCBEYXRhWE1QPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS4zLWMwMTEgNjYuMTQ1NjYxLCAyMDEyLzAyLzA2LTE0OjU2OjI3ICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIiB4bXBNTTpPcmlnaW5hbERvY3VtZW50SUQ9InV1aWQ6NUQyMDg5MjQ5M0JGREIxMTkxNEE4NTkwRDMxNTA4QzgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NThEMTQ2MkQ0MkYxMTFFMkE0RUREODE4QkNFQjYwM0YiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NThEMTQ2MkM0MkYxMTFFMkE0RUREODE4QkNFQjYwM0YiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgSWxsdXN0cmF0b3IgQ1M2IChXaW5kb3dzKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ1dWlkOjVjOTUyYTQzLWFhZGYtNGNjNi05ZGU3LTlmOGE0MWZlYjliNSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpGNzFBMTE4MTcyM0VFMjExQUMyRkM1OENGNzc5MjlFMyIvPiA8ZGM6dGl0bGU+IDxyZGY6QWx0PiA8cmRmOmxpIHhtbDpsYW5nPSJ4LWRlZmF1bHQiPmxvYWRlZDcgbG9nbyBpY29uPC9yZGY6bGk+IDwvcmRmOkFsdD4gPC9kYzp0aXRsZT4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz4B//79/Pv6+fj39vX0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" type="image/x-icon" />
                            <link rel="stylesheet" type="text/css" media="screen" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css" />
                            <title>Installation - Error</title>
                        </head>
                        <body>
                        <div class="ui-widget">
                            <div class="ui-state-error ui-corner-all" style="padding: 0pt 0.7em;">
                                <p>
                                <strong>Alert:</strong> Already Installed. Remove the \'install\' folder, or remove your configure.php to install again.</p>
                            </div>
                        </div>
                        </body>
                    </html>';
                die();
            }
        }

        $this->data = isset($_POST['step_data']) ? unserialize(base64_decode($_POST['step_data'])) : array();
        if (isset($_POST['next']) && $this->current_step == 'end')
            $this->parse_end();
        if (isset($_POST['install_done'])) {
            $this->done = (strpos($_POST['install_done'], ',') !== false) ? explode(',', $_POST['install_done']) : (($_POST['install_done'] != '') ? array($_POST['install_done']) : array());
        }

        $this->init_language();
        $this->scan_steps();
        if (!(in_array($this->current_step, array_keys($this->steps)) || $this->current_step == 'start') && $this->current_step != 'end') {
            $this->log('install_error', 'invalid current step');
        }
        if (isset($_POST['select'])) {
            $this->current_step = $_POST['select'];
        } elseif (isset($_POST['next']) || isset($_POST['prev']) || $this->current_step == 'start' || isset($_POST['skip'])) {
            if ($this->current_step == 'start' || isset($_POST['skip']) || ($this->current_step != 'end' && $this->parse_step() && isset($_POST['next'])))
                $this->next_step();
            if (isset($_POST['prev']) && !$this->retry_step)
                $this->current_step = $_POST['prev'];
        }
        $this->show();
    }

    private function init_language() {
        if (!isset($_POST['inst_lang'])) {
            $usersprache = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
            $usersprache = explode(";", $usersprache[0]);

            if (strlen($usersprache[0]) == "5") {
                $code = substr($usersprache[0], 3, 2);
            } elseif (strlen($usersprache[0]) == "2") {
                $code = $usersprache[0];
            } else {
                $code = "";
            }
            $code = strtolower($code);
            $language = $this->translate_iso_langcode($code);
            if (!is_file($this->root_path . 'language/' . $language . '/lang_install.php')) {
                $language = "english";
            }
        } else {
            $language = $_POST['inst_lang'];
        }

        if (!include_once($this->root_path . '/install/language/' . $language . '/install.php')) {
            die('Could not include the language files! Check to make sure that "' . $this->root_path . 'language/' . $language . '/install.php" exists!');
        }
        $this->lang = $lang;
    }

    private function scan_steps() {
        $steps = scandir($this->root_path . 'install/install_steps');
        foreach ($steps as $file) {
            if (substr($file, -10) != '.class.php')
                continue;
            $step = substr($file, 0, -10);
            include_once($this->root_path . 'install/install_steps/' . $file);
            if (!class_exists($step)) {
                $this->log('install_error', 'invalid step-file');
            }
            if (empty($this->data[$step])) {
                $this->data[$step] = array();
            }
            $this->steps[] = $step;
            $this->order[call_user_func(array($step, 'before'))] = $step;
            $ajax = call_user_func(array($step, 'ajax'));
            if ($ajax && isset($_POST[$ajax])) {
                $_step = new $step();
                if (method_exists($_step, 'ajax_out'))
                    $_step->ajax_out();
            }
        }
        $this->order = $this->sort_steps();
    }

    private function sort_steps() {
        $arrOut = array();
        $current = 'start';
        for ($i = 0; $i < count($this->order); $i++) {
            $arrOut[$current] = $this->order[$current];
            $current = $this->order[$current];
        }
        return $arrOut;
    }

    private function parse_step() {
        $step = end($this->order);
        while ($step != $this->current_step) {
            if (in_array($step, $this->done, true)) {
                $_step = new $step();
                if (method_exists($_step, 'undo'))
                    $_step->undo();
                unset($this->done[array_search($step, $this->done)]);
            }
            $step = array_search($step, $this->order);
            if (!in_array($step, $this->steps)) {
                $this->pdl->log('install_error', $this->lang['step_order_error']);
                return false;
            }
        }
        $step = $this->current_step;
        $_step = new $step();
        $back = $_step->parse_input();
        $this->data[$this->current_step] = $_step->data;
        if ($back && !in_array($this->current_step, $this->done))
            $this->done[] = $this->current_step;
        if (!$back && in_array($this->current_step, $this->done))
            unset($this->done[array_search($this->current_step, $this->done)]);
        if (!$back)
            $this->retry_step = true;
        return $back;
    }

    private function next_step() {
        $old_current = $this->current_step;
        foreach ($this->steps as $step) {
            if (call_user_func(array($step, 'before')) == $this->current_step) {
                $this->current_step = $step;
                break;
            }
        }
        if ($old_current == $this->current_step)
            $this->current_step = 'end';
    }

    private function next_button() {
        if ($this->current_step == 'end')
            return $this->lang['inst_finish'];
        if ($this->retry_step)
            return $this->lang['retry'];
        $step = $this->current_step;
        $_step = new $step();
        return $this->lang[$_step->next_button];
    }

    private function end() {
        $config = file_get_contents($this->root_path . 'includes/local/configure.php');
        $config .= 'define(\'TL_INSTALLED\', true);' . "\n\n";
        $response = file_put_contents($this->root_path . 'includes/local/configure.php', $config);
        if ($response === false) {
            $this->log('install_error', 'Cant save config file.');
            return false;
        }
        @chmod($this->root_path . 'includes/local/configure.php', 0644);
        @chmod($this->root_path . 'admin/includes/local/configure.php', 0644);

        return $this->lang['install_end_text'];
    }

    private function parse_end() {
        include $this->root_path . 'includes/local/configure.php';
        if (defined('TL_INSTALLED') && TL_INSTALLED) {
            $path = dirname($_SERVER['SCRIPT_FILENAME']);
            @unlink($path);
        }
        header('Location: ' . $this->root_path);
        exit;
    }

    private function get_content() {
        $this->previous = array_search($this->current_step, $this->order);
        if ($this->current_step == 'end')
            return $this->end();
        $step = $this->current_step;
        $_step = new $step();
        if (in_array($this->current_step, $this->done))
            $content = $_step->get_filled_output();
        else
            $content = $_step->get_output();
        $this->data[$this->current_step] = $_step->data;
        return $content;
    }

    private function gen_menu() {
        $menu = '';
        $count_step = '1';
        foreach ($this->order as $step) {
            $class = (in_array($step, $this->done)) ? 'done' : 'notactive';
            if (in_array(array_search($step, $this->order), $this->done))
                $class .= ' done2';
            if ($step == $this->current_step)
                $class = 'now';
            $menu .= "\n\t\t\t\t\t" . '<li class="' . $class . '" id="' . $step . '"><span class="countStep">' . $count_step . '</span><span>' . $this->lang[$step] . '<input type="hidden" name="select" id="back_' . $step . '" disabled="disabled" value="' . $step . '" /></span></li>';
            $count_step++;
        }
        return $menu;
    }

    private function lang_drop() {
        $drop = '<select name="inst_lang" id="language_drop">';
        $options = array();
        $files = scandir($this->root_path . '/install/language');
        foreach ($files as $file) {
            if (file_exists($this->root_path . '/install/language/' . $file . '/install.php'))
                $options[] = $file;
        }
        sort($options);
        foreach ($options as $option) {
            $selected = ($this->langcode == $option) ? ' selected="selected"' : '';
            $drop .= '<option value="' . $option . '"' . $selected . '>' . ucfirst($option) . '</option>';
        }
        return $drop . '</select>';
    }

    private function show() {
        if (class_exists($this->current_step)) {
            $step = $this->current_step;
            $_step = new $step();
        }
        $progress = round(100 * (count($this->done) / count($this->order)), 0);
        $content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
                <link rel="shortcut icon" href="data:image/gif;base64,R0lGODlhQAA5APf/AL3BxPP5/LG2uStorLW6vebq7N7h5Diq4Ojs7pKYnOLm6MbKzV6Bua2xtSuNydba3VmTyS15uSyIxSmh2ipiqKSqrjtanaassfH2+SttsCxprSmk3HqWyClSnCx+vZieouTq9aCmquru8Tt4tmSQxqKorCuW0bm+wZqgpStmqypgpoWZxoOIjCem3khhpZyktuzw84OMpo2Sl9bh8YmXqsHFyGuPxCqa1M7R1LvV7dre4FhyqdHW2S1xs52jqMrO0EhcnCdElJ6kqCtqriuSzUaj2Zyipiqd1ixusZuoye/z9ilaooiNkSpepCtlqipaomN5rCyEwihOmilWnyhMmClQmylYoSlhp/H19+Dk5tjc3s3b79LW2CyGw8DDxihJl298syxtsChRnNDT1i1mqjNOmilbo6ius+Xo68jMzyqY0iyCwCp2tytnrCljqSpcpChVnix/vixytCx2tyhUnZedoSlepaSrsCie2CqUzjddoSlXnypkqdzg4yyOyqqvsihDk36Ch6ClqS11tnCPxCpWnyt6uildpCdHlTNkqaitsipYodPX2itwsyx9vCpztSpUnilLmO7y9Ctsrylfpix0tauyt8zQ05mfo620uayzuK+2uy58u6qxttzf4rO4uyuPy7C3vKmwtSphp+Tn6illqq+0typqraesr/P6/tTY2+Ll6K21uq61uqivtCdLmN/l8srW6yxvscTIy+3x8yuHxNPY2+Pn6ipOmix3t+ns7ylOmSuLx+nv8zRgpSt8u4OIvNDZ5N7j6Njc6unv+be/zo/A37i9z3i12O/1/ClprYW73D9trVBfmsLI1qXN6Kqww8bQ1dDX3aCrudLf8SpZoS5SnCyBv5rH436FumaArmaCssjM23CRsurv9+Pp7K290i1UnrXG3Nrg5ZadpsXT6sbZ7/H3/CyUz+Hl6Le7vuvv8S5vsk+b0pCZp+/095aboK7Q6lCFvImVuC5gppKarqS53K+018TH4YeSsoOZu5ifroqYvymo4PT7/gAAACH/C1hNUCBEYXRhWE1QPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS4zLWMwMTEgNjYuMTQ1NjYxLCAyMDEyLzAyLzA2LTE0OjU2OjI3ICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIiB4bXBNTTpPcmlnaW5hbERvY3VtZW50SUQ9InV1aWQ6NUQyMDg5MjQ5M0JGREIxMTkxNEE4NTkwRDMxNTA4QzgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NThEMTQ2MkQ0MkYxMTFFMkE0RUREODE4QkNFQjYwM0YiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NThEMTQ2MkM0MkYxMTFFMkE0RUREODE4QkNFQjYwM0YiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgSWxsdXN0cmF0b3IgQ1M2IChXaW5kb3dzKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ1dWlkOjVjOTUyYTQzLWFhZGYtNGNjNi05ZGU3LTlmOGE0MWZlYjliNSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpGNzFBMTE4MTcyM0VFMjExQUMyRkM1OENGNzc5MjlFMyIvPiA8ZGM6dGl0bGU+IDxyZGY6QWx0PiA8cmRmOmxpIHhtbDpsYW5nPSJ4LWRlZmF1bHQiPmxvYWRlZDcgbG9nbyBpY29uPC9yZGY6bGk+IDwvcmRmOkFsdD4gPC9kYzp0aXRsZT4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz4B//79/Pv6+fj39vX0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" type="image/x-icon" />
		<link rel="stylesheet" type="text/css" media="screen" href="libraries/jquery/core/core.min.css" />
		<script type="text/javascript" language="javascript" src="libraries/jquery/core/core.min.js"></script>
		<link href="libraries/FontAwesome/font-awesome.min.css" rel="stylesheet">
		<link rel="stylesheet" type="text/css" media="screen" href="style/install.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="style/jquery_tmpl.css" />
		<script type="text/javascript">
			//<![CDATA[
		$(function() {
			$("#language_drop").change(function(){
				$("#form_install").submit();
			});
			$("#progressbar").progressbar({
				value: ' . $progress . '
			});
			$(".done, .done2, #previous_step").click(function(){
				$("#back_"+$(this).attr("id")).removeAttr("disabled");
				$("#form_install").submit();
			});
			
			$("#form_install").on("submit", function(){
			    $("#content").append("<div class=\'preloader\'></div>")
			})

			' . $_step->head_js . '
		});
			//]]>
		</script>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>' . sprintf($this->lang['page_title'], VERSION_EXT) . '</title>
	</head>

	<body>
		<form action="index.php" method="post" id="form_install">
		<div id="outerWrapper">
			<div id="header">
				<div id="logo"></div>
				<div id="languageselect"><i class="fa fa-globe"></i> ' . $this->lang['language'] . ': ' . $this->lang_drop() . '</div>
				<div id="logotext">Installation ' . VERSION_EXT . '</div>
			</div>

		<div id="installer">
			<div id="steps">
				<ul class="steps">' . $this->gen_menu() . '</ul>
        <div id="progressbar"><span class="install_label">' . $progress . '%</span></div>
			</div>
			<div id="main">
				<div id="content">
					';
        if (count($this->log) > 0) {
            $error = "<br />";
            foreach ($this->log as $log) {
                $type = $log['type'];
                $error .= $this->$type($log['message']);
            }
            $content .= '<h1 class="hicon home">' . $this->lang[$_POST['current_step']] . '</h1>' . $error;
        }

        $content .= '
					<h1 class="hicon home">' . (($this->current_step == 'licence') ? sprintf($this->lang['page_title'], VERSION_EXT) : $this->lang[$this->current_step]) . '</h1>
					' . $this->get_content() . '
					<div class="buttonbar">';
        if ($this->previous != 'start' && $this->current_step != 'end')
            $content .= '
						<button type="button" id="previous_step" class="prevstep">' . $this->lang['back'] . '</button>
						<input type="hidden" name="prev" value="' . $this->previous . '" id="back_previous_step" disabled="disabled" />';
        if ($_step->skippable)
            $content .= '
						<input type="submit" name="' . (($_step->parseskip) ? 'next' : 'skip') . '" value="' . $this->lang['skip'] . '" class="' . (($_step->parseskip) ? 'nextstep' : 'skipstep') . '" />';
        $content .= '
						<button type="submit" name="next" class="blue-btn" />' . $this->next_button() . '</button>
						<input type="hidden" name="current_step" value="' . $this->current_step . '" />
						<input type="hidden" name="install_done" value="' . implode(',', $this->done) . '" />
						<input type="hidden" name="step_data" value="' . base64_encode(serialize($this->data)) . '" />
					</div>
				</div>
			</div>
		</div>
		<div id="footer">
        Copyright (c) ' . date('Y', time()) . ' <a target="_blank" href="http://loadedx.com">Loadedx.com</a>
		</div>
		</div>
		</form>
	</body>
</html>';
        echo $content;
    }

    public function install_error($log) {
        return '<div class="infobox infobox-large infobox-red clearfix">
		<i class="fa fa-exclamation-triangle fa-4x pull-left"></i><span>' . $this->lang['error'] . '. ' . $log . '</span>
	</div>';
    }

    public function install_warning($log) {
        return '<div class="infobox infobox-large infobox-red clearfix">
			<i class="fa fa-exclamation-triangle fa-4x pull-left"></i><span>' . $this->lang['warning'] . '. ' . $log . '</span>
		</div>';
    }

    public function install_success($log) {
        return '<div class="infobox infobox-large infobox-green clearfix">
		<i class="fa fa-check-circle" aria-hidden="true"></i><span>' . $this->lang['success'] . '. ' . $log . '</span>
	</div>';
    }

    public function translate_iso_langcode($isoCode) {
        $language_codes = array(
            'en' => 'English',
        );
        if (isset($language_codes[$isoCode])) {
            return mb_strtolower($str,  mb_detect_encoding($str));
//            return utf8_strtolower($language_codes[$isoCode]);
        } else {
            return "english";
        }
    }

}

abstract class install_generic {

    public static $before = 'start';
    public static $ajax = false;
    public $head_js = '';
    public $next_button = 'continue';
    public $skippable = false;
    public $parseskip = false;
    public $data = array();

    public function __construct() {
        global $install;
        $this->lang = $install->lang;
        $this->data = $install->data[get_class($this)];
        $this->root_path = $install->root_path;
    }

    public static function before() {
        return self::$before;
    }

    public static function ajax() {
        return self::$ajax;
    }

    public function log($type, $message) {
        global $install;
        $install->log($type, $message);
    }

    public function prepare_input($string) {
        $string = stripslashes($string);
        $string = preg_replace('/ +/', ' ', trim($string));
        $string = preg_replace("/[<>]/", '_', $string);
        return addslashes($string);
    }

    abstract public function get_output();

    abstract public function get_filled_output();

    abstract public function parse_input();
}
