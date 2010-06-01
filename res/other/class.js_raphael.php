<?php
/*
 * Javascript Raphael Class for ke_questionnaire
 *
 * Copyright (C) 2009 kennziffer.com / Nadine Schwingler
 * All rights reserved.
 * License: GNU/GPL License
 *
 */
class js_raphael {
    /**
     * Generates the JavaScript includes for BE
     *
     * @param $mod      Module calling the function
     * @param $type     Type of Chart-Script to inlcude
     */
    function be_js_includes($mod,$type = 'all'){
        $includes = array();
        $includes[] = '../res/other/raphael/raphael.js';
        $includes[] = '../res/other/raphael/g.raphael-min.js';

        switch ($type){
            case 'bar': //$includes[] = '../res/other/raphael/g.bar-min.js';
                        $includes[] = '../res/other/raphael/g.bar.js';
                break;
            case 'dot': $includes[] = '../res/other/raphael/g.dot-min.js';
                break;
            case 'line': $includes[] = '../res/other/raphael/g.line-min.js';
                break;
            case 'pie': $includes[] = '../res/other/raphael/g.pie-min.js';
                break;
            default:
                    //$includes[] = '../res/other/raphael/g.bar-min.js';
                    $includes[] = '../res/other/raphael/g.bar.js';
                    $includes[] = '../res/other/raphael/g.dot-min.js';
                    $includes[] = '../res/other/raphael/g.line-min.js';
                    $includes[] = '../res/other/raphael/g.pie-min.js';
                break;
        }

        foreach ($includes as $incl){
            $mod->doc->JScode .= '<script src="';
            $mod->doc->JScode .= $incl;
            $mod->doc->JScode .= '" type="text/javascript" charset="utf-8"></script>'."\n";
        }
    }

    /**
     * Wraps the Script in Onload
     *
     * @param $script
     *
     * @return string   returns the javscript
     */
    function wrapIt($script){
        $chart = '<script type="text/javascript" charset="utf-8">
                        window.onload = function () {'.
                        $script;
        $chart .= '};</script>';
        return $chart;
    }

    /**
     * Generates a Bar Chart
     *
     * @param $marker   Marker to be rendered to
     * @param $title    Title of Chart
     *
     * @return string   returns the javscript
     */
    function getBarChart($marker,$title,$labels,$values,$max,$colors="#A2BF2F",$onload = true){
        $stacked = 'false';
        if (is_array($values)) {
            $linecount = 0;
            $temp = array();
            foreach ($values as $x){
                if (is_array($x)){
                    $temp[] = implode(',',$x);
                } else {
                    $linecount = 1;
                    break;
                }
            }
            if ($linecount == 1) $values = '['.implode(',',$values).']';
            else {
                $values = '';
                foreach ($temp as $line){
                    if ($values != '') $values .= ', ';
                    $values .= '['.$line.']';
                }
            }
        }
        //t3lib_div::devLog('values', 'ke_questionnaire auswert Mod', 0, array($values));
        //t3lib_div::devLog('max', 'ke_questionnaire auswert Mod', 0, array($max));
        if (is_array($colors)){
            $temp = array();
            foreach ($colors as $color){
                $temp[] = '"'.$color.'"';
            }
            $colors = implode(',',$temp);
        } else {
            $colors = '"'.$colors.'"';
        }
        //t3lib_div::devLog('colors', 'ke_questionnaire auswert Mod', 0, array($colors));
        if (is_array($labels)){
            $temp = array();
            foreach ($labels as $label){
                $temp[] = '"'.$label.'"';
            }
            $labels = '['.implode(',',$temp).']';
        } else {
            $labels = '["'.$labels.'"]';
        }
        //t3lib_div::devLog('labels', 'ke_questionnaire auswert Mod', 0, array($labels));
        $chart = '
                bc = Raphael("'.$marker.'", 1240, 600);

                var ea = function () {
                        this.flag = bc.g.popup(this.bar.x, this.bar.y, this.bar.value || "0").insertBefore(this).attr({"font-size": 11});
                    };

                var fin = function () {
                        this.flag = bc.g.popup(this.bar.x, this.bar.y, this.bar.value || "0").insertBefore(this);
                    };
                var fout = function () {
                        this.flag.animate({opacity: 0}, 300, function () {this.remove();});
                    };

                bc.g.text(300, 10, "'.$title.'").attr({"font-size": 20});
                bc.g.barchart(
                    0, 0, 550, 300,
                    ['.$values.'],
                    { stacked: '.$stacked.',
                    colors: ['.$colors.'],
                    type: "soft", //abgerundete Ecken
                    to: "'.(intval($max)).'", //maximum-Wert
                    vgutter: "40" //Abstand nach unten
                    })
                    .each(ea)
                    .label(['.$labels.']);
        ';

        if ($onload){
            $chart = $this->wrapIt($chart);
        }
        return $chart;
    }

    /**
     * Generates a Pie Chart with Legend
     *
     * @param $marker   Marker to be rendered to
     * @param $title    Title of Chart
     *
     * @return string   returns the javscript
     */
    function getPieLegendChart($marker,$title,$parts,$labels,$colors,$onload = true,$legend_pos = 'south',$left=250){
        if (is_array($parts)) $parts = implode(',',$parts);
        if (is_array($colors)){
            $temp = array();
            foreach ($colors as $color){
                $temp[] = '"'.$color.'"';
            }
            $colors = implode(',',$temp);
        } else {
            $colors = '"'.$colors.'"';
        }
        $chart = '
            var plc = Raphael("'.$marker.'");
            plc.g.txtattr.font = "12px \'Fontin Sans\', Fontin-Sans, sans-serif";

            plc.g.text('.$left.', 10, "'.$title.'").attr({"font-size": 20});

            var pie = plc.g.piechart(
                    '.$left.',
                    140,
                    100,
                    ['.$parts.'],
                    {legend: [';
        $temp = '';
        foreach ($labels as $label){
            if ($temp != '') $temp .= ', ';
            $temp .= '"'.$label.'"';
        }
        $chart .= $temp;
        $chart .= '],
                    legendpos: "'.$legend_pos.'",
                    colors: ['.$colors.']}
                );
            pie.hover(function () {
                this.sector.stop();
                this.sector.scale(1.1, 1.1, this.cx, this.cy);
                if (this.label) {
                    this.label[0].stop();
                    this.label[0].scale(1.5);
                    this.label[1].attr({"font-weight": 800});
                }
            }, function () {
                this.sector.animate({scale: [1, 1, this.cx, this.cy]}, 500, "bounce");
                if (this.label) {
                    this.label[0].animate({scale: 1}, 500, "bounce");
                    this.label[1].attr({"font-weight": 400});
                }
            });
        ';

        if ($onload){
            $chart = $this->wrapIt($chart);
        }
        return $chart;
    }

    /**
     * Generates a Pie Chart with Legend
     *
     * @param $marker   Marker to be rendered to
     * @param $title    Title of Chart
     *
     * @return string   returns the javscript
     */
    function getPieChart($marker,$title,$parts,$colors,$onload = true){
        if (is_array($parts)) $parts = implode(',',$parts);
        if (is_array($colors)){
            $temp = array();
            foreach ($colors as $color){
                $temp[] = '"'.$color.'"';
            }
            $colors = implode(',',$temp);
        } else {
            $colors = '"'.$colors.'"';
        }
        $chart = '
            var plc = Raphael("'.$marker.'");
            plc.g.txtattr.font = "12px \'Fontin Sans\', Fontin-Sans, sans-serif";

            plc.g.text(320, 10, "'.$title.'").attr({"font-size": 20});

            var pie = plc.g.piechart(
                    170,
                    140,
                    100,
                    ['.$parts.'],
                    {colors: ['.$colors.']}
                );
            pie.hover(function () {
                this.sector.stop();
                this.sector.scale(1.1, 1.1, this.cx, this.cy);
                if (this.label) {
                    this.label[0].stop();
                    this.label[0].scale(1.5);
                    this.label[1].attr({"font-weight": 800});
                }
            }, function () {
                this.sector.animate({scale: [1, 1, this.cx, this.cy]}, 500, "bounce");
                if (this.label) {
                    this.label[0].animate({scale: 1}, 500, "bounce");
                    this.label[1].attr({"font-weight": 400});
                }
            });
        ';

        if ($onload){
            $chart = $this->wrapIt($chart);
        }
        return $chart;
    }

    /**
     * Generates a Line Chart
     *
     * @param $marker   Marker to be rendered to
     * @param $title    Title of Chart
     * @param $x_axis   Values for x_axis
     * @param $x_step   Step-Amount for x_axis
     * @param $y_axis   Values for y_axis
     *
     * @return string   returns the javscript
     */
    function getLineChart($marker,$title,$x_axis,$x_step,$y_axis,$y_step,$labels=array(),$colors='#00f',$onload = true){
        if ($y_step > 15)$y_step = 0;
        if (is_array($x_axis)) {
            $linecount = 0;
            $temp = array();
            foreach ($x_axis as $x){
                if (is_array($x)){
                    $temp[] = implode(',',$x);
                } else {
                    $linecount = 1;
                    break;
                }
            }
            if ($linecount == 1) $x_axis = implode(',',$x_axis);
            else {
                $x_axis = '';
                foreach ($temp as $line){
                    if ($x_axis != '') $x_axis .= ', ';
                    $x_axis .= '['.$line.']';
                }
            }
        }
        if (is_array($y_axis)) {
            $linecount = 0;
            $temp = array();
            foreach ($y_axis as $y){
                if (is_array($y)){
                    $temp[] = implode(',',$y);
                } else {
                    $linecount = 1;
                    break;
                }
            }
            if ($linecount == 1) $y_axis = implode(',',$y_axis);
            else {
                $y_axis = '';
                foreach ($temp as $line){
                    if ($y_axis != '') $y_axis .= ', ';
                    $y_axis .= '['.$line.']';
                }
            }
        }
        if (is_array($colors)){
            $temp = array();
            foreach ($colors as $color){
                $temp[] = '"'.$color.'"';
            }
            $colors = implode(',',$temp);
        } else {
            $colors = '"'.$colors.'"';
        }
        if (is_array($labels)){
            $temp = array();
            foreach ($labels as $label){
                $temp[] = '"'.$label.'"';
            }
            $labels = '['.implode(',',$temp).']';
        } else {
            $labels = '["'.$labels.'"]';
        }
        //t3lib_div::devLog('params', 'ke_questionnaire auswert Mod', 0, array($colors,$labels));
        $chart = '
                var lc = Raphael("'.$marker.'");

                lc.g.txtattr.font = "18px \'Arial\', Arial, sans-serif";

                var x = [], y = [];
                x = ['.$x_axis.'];
                y = ['.$y_axis.'];

                lc.g.text(240, 10, "'.$title.'");
                lc.g.txtattr.font = "12px \'Arial\', Arial, sans-serif";

                var lines = lc.g.linechart(
                        25, 25, 550, 300,
                        x,
                        y,
                        {nostroke: false,
                        colors: ['.$colors.'],
                        axis: "0 0 1 1",
                        symbol: "o",
                        shade:true,
                        axisxstep: '.$x_step.',
                        axisystep: '.$y_step.'})
                    .hoverColumn(function () {
                        this.tags = lc.set();
                        for (var i = 0, ii = this.y.length; i < ii; i++) {
                            this.tags.push(
                                lc.g.tag(
                                    this.x, this.y[i],
                                    this.values[i],
                                    -15, 8).insertBefore(this).attr([{fill: "#000"},
                                                                    {fill: this.symbols[i].attr("fill")}]));
                        }
                    }, function () {
                        this.tags && this.tags.remove();
                    });
                var i = 0;
                var labels = '.$labels.';
                for each (var lab in lines.axis[0].text.items) {
                    if (labels[i-1]) lab.attr({"text": labels[i-1]})
                    i ++;
                };
            ';
        if ($onload){
            if ($onload){
                $chart = $this->wrapIt($chart);
            }
        }

        return $chart;
    }

    /**
     * Generates a TestChart to display
     *
     * @return string   returns the javscript
     */
    function getTest() {
        $content = '
	<script type="text/javascript" charset="utf-8">
             window.onload = function () {
                var r = Raphael("chart"),
                    xs = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23],
                    ys = [7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 7, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 6, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 4, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1],
                    data = [294, 300, 204, 255, 348, 383, 334, 217, 114, 33, 44, 26, 41, 39, 52, 17, 13, 2, 0, 2, 5, 6, 64, 153, 294, 313, 195, 280, 365, 392, 340, 184, 87, 35, 43, 55, 53, 79, 49, 19, 6, 1, 0, 1, 1, 10, 50, 181, 246, 246, 220, 249, 355, 373, 332, 233, 85, 54, 28, 33, 45, 72, 54, 28, 5, 5, 0, 1, 2, 3, 58, 167, 206, 245, 194, 207, 334, 290, 261, 160, 61, 28, 11, 26, 33, 46, 36, 5, 6, 0, 0, 0, 0, 0, 0, 9, 9, 10, 7, 10, 14, 3, 3, 7, 0, 3, 4, 4, 6, 28, 24, 3, 5, 0, 0, 0, 0, 0, 0, 4, 3, 4, 4, 3, 4, 13, 10, 7, 2, 3, 6, 1, 9, 33, 32, 6, 2, 1, 3, 0, 0, 4, 40, 128, 212, 263, 202, 248, 307, 306, 284, 222, 79, 39, 26, 33, 40, 61, 54, 17, 3, 0, 0, 0, 3, 7, 70, 199],
                    axisy = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
                    axisx = ["12am", "1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12pm", "1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11"];
                r.g.txtattr.font = "11px \'Fontin Sans\', Fontin-Sans, sans-serif";

                r.g.dotchart(10, 10, 620, 260, xs, ys, data, {symbol: "o", max: 10, heat: true, axis: "0 0 1 1", axisxstep: 23, axisystep: 6, axisxlabels: axisx, axisxtype: " ", axisytype: " ", axisylabels: axisy}).hover(function () {
                    this.tag = this.tag || r.g.tag(this.x, this.y, this.value, 0, this.r + 2).insertBefore(this);
                    this.tag.show();
                }, function () {
                    this.tag && this.tag.hide();
                });
            };

        </script>';

        return $content;
    }
}
?>
