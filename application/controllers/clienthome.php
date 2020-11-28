<?php
global $fuzzy_set, $fuzzy_input, $fuzzy_output, $output, $fuzzy_rules;

$fuzzy_set = array(
    array("", "RENDAH", "SEDANG", "TINGGI"),
    array("", "RENDAH", "SEDANG", "TINGGI"),
    array("", "RENDAH", "SEDANG", "TINGGI"),
);
$fuzzy_rules = array(
    array('x' => array(1, 3), 'y' => 1),
    array('x' => array(1, 2), 'y' => 1),
    array('x' => array(1, 1), 'y' => 1),
    array('x' => array(2, 3), 'y' => 1),
    array('x' => array(2, 2), 'y' => 2),
    array('x' => array(2, 1), 'y' => 3),
    array('x' => array(3, 3), 'y' => 3),
    array('x' => array(3, 2), 'y' => 3),
    array('x' => array(3, 1), 'y' => 3),
);
$fuzzy_input = array();
$fuzzy_output = array();
$output = array();

function muTrapesium($a, $x)
{
    error_reporting(E_ALL ^ E_WARNING);
    $mu = null;
    if ($x <= $a[0])
        $mu = 0;
    else if ($x > $a[0] && $x <= $a[1])
        $mu = (($x - $a[0]) / ($a[1] - $a[0]));
    else if ($x > $a[1] && $x <= $a[2])
        $mu = 1;
    else if ($x > $a[2] && $x <= $a[3])
        $mu = (($a[3] - $x) / ($a[3] - $a[2]));
    else if ($x > $a[3])
        $mu = 0;

    return $mu;
}

function muSegitiga($a, $x)
{
    error_reporting(E_ALL ^ E_WARNING);
    $mu = null;
    if ($x <= $a[0] || $x >= $a[2])
        $mu = 0;
    else if ($x > $a[0] && $x < $a[1])
        $mu = (($x - $a[0]) / ($a[1] - $a[0]));
    else if ($x == $a[1])
        $mu = 1;
    else if ($x > $a[1] && $x < $a[2])
        $mu = (($a[2] - $x) / ($a[2] - $a[1]));

    return $mu;
}

function muPermintaan($n)
{
    error_reporting(E_ALL ^ E_WARNING);
    global $fuzzy_input;

    $var_ling = array();
    if ($n >= 50 && $n <= 83) {
        $var_ling[] = 1;
    }

    if ($n >= 70 && $n <= 100) {
        $var_ling[] = 2;
    }

    if ($n >= 88 && $n <= 120) {
        $var_ling[] = 3;
    }

    $mu = null;
    foreach ($var_ling as $v) {
        if ($v == 1) {
            $a = array(50, 50, 68, 83);
            $mu = muTrapesium($a, $n);

            $fuzzy_input[] = array('in' => 'Permintaan', 'vl' => ("0|" . $v), 'mu' => $mu);
        } else if ($v == 2) {
            $a = array(70, 85, 100);
            $mu = muSegitiga($a, $n);

            $fuzzy_input[] = array('in' => 'Permintaan', 'vl' => ("0|" . $v), 'mu' => $mu);
        } else if ($v == 3) {
            $a = array(88, 103, 120, 120);
            $mu = muTrapesium($a, $n);

            $fuzzy_input[] = array('in' => 'Permintaan', 'vl' => ("0|" . $v), 'mu' => $mu);
        }
    }

    //echo json_encode($fuzzy_input)."</br>";
}

function muPersediaan($n)
{
    error_reporting(E_ALL ^ E_WARNING);
    global $fuzzy_input;

    $var_ling = array();
    if ($n >= 10 && $n <= 23) {
        $var_ling[] = 1;
    }

    if ($n >= 22 && $n <= 28) {
        $var_ling[] = 2;
    }

    if ($n >= 27 && $n <= 40) {
        $var_ling[] = 3;
    }

    $mu = null;
    foreach ($var_ling as $v) {
        if ($v == 1) {
            $a = array(10, 10, 20, 23);
            $mu = muTrapesium($a, $n);

            $fuzzy_input[] = array('in' => 'Persediaan', 'vl' => ("1|" . $v), 'mu' => $mu);
        } else if ($v == 2) {
            $a = array(22, 25, 28);
            $mu = muSegitiga($a, $n);

            $fuzzy_input[] = array('in' => 'Persediaan', 'vl' => ("1|" . $v), 'mu' => $mu);
        } else if ($v == 3) {
            $a = array(27, 30, 40, 40);
            $mu = muTrapesium($a, $n);

            $fuzzy_input[] = array('in' => 'Persediaan', 'vl' => ("1|" . $v), 'mu' => $mu);
        }
    }

    //echo json_encode($fuzzy_input)."</br>";
}
function inferensi($perm, $pers)
{
    error_reporting(E_ALL ^ E_WARNING);
    global $fuzzy_rules;

    $rules = null;

    $ketemu = false;
    $i = 0;

    while (!$ketemu && $i < count($fuzzy_rules)) {
        if ($fuzzy_rules[$i]['x'][0] == $perm) {
            if ($fuzzy_rules[$i]['x'][1] == $pers) {

                $rules = $i;
                $ketemu = true;
            }
        }

        $i++;
    }

    return $rules;
}

function inferensi_rules()
{
    error_reporting(E_ALL ^ E_WARNING);
    global $fuzzy_input, $fuzzy_output, $fuzzy_rules;

    $set_rules_1 = array();
    $set_rules_2 = array();
    $set_rules_3 = array();

    foreach ($fuzzy_input as $input) {
        $v = explode('|', $input['vl']);

        if ($input['in'] == 'Permintaan')
            $set_rules_1[] = array('p' => $v[1], 'x' => $input['mu']);
        else if ($input['in'] == 'Persediaan')
            $set_rules_2[] = array('p' => $v[1], 'x' => $input['mu']);
    }

    $x = 0;
    while ($x < count($set_rules_1)) {
        $y = 0;
        while ($y < count($set_rules_2)) {

            $result = inferensi(
                $set_rules_1[$x]['p'],
                $set_rules_2[$y]['p']
            );
            $res = min(
                $set_rules_1[$x]['x'],
                $set_rules_2[$y]['x']
            );
            $fuzzy_output[] = array('rules' => $fuzzy_rules[$result], 'mu' => array(
                $set_rules_1[$x]['x'], $set_rules_2[$y]['x'], $res
            ));

            $y++;
        }
        $x++;
    }
}

function inferensi_adv_rules()
{
    error_reporting(E_ALL ^ E_WARNING);
    global $fuzzy_output, $output, $fuzzy_set;

    $set_output1 = array();
    $set_output2 = array();
    foreach ($fuzzy_output as $out) {
        if ($out['rules']['y'] == 1) {
            $set_output1[] = $out['mu'][2];
        } else if ($out['rules']['y'] == 2) {
            $set_output2[] = $out['mu'][2];
        } else if ($out['rules']['y'] == 3) {
            $set_output2[] = $out['mu'][2];
        }
    }

    if (!empty($set_output1))
        $output[] = array('p' => $fuzzy_set[2][1], 'x' => max($set_output1));
    if (!empty($set_output2))
        $output[] = array('p' => $fuzzy_set[2][2], 'x' => max($set_output2));
    if (!empty($set_output2))
        $output[] = array('p' => $fuzzy_set[2][3], 'x' => max($set_output2));
}

function defuzzyfication()
{
    error_reporting(E_ALL ^ E_WARNING);
    //metode centoid
    global $output, $fuzzy_set;
    $range1 = array(60, 93);
    $range2 = array(80, 110);
    $range3 = array(98, 130);


    $acak = array();

    if (count($output) == 1) {
        $count = 10;
    } else {
        $count = (int)(10 / count($output));
    }

    $i = 0;
    while ($i < count($output)) {
        if ($output[$i]['p'] == $fuzzy_set[2][1]) {
            $x = 0;
            while ($x <= $count) {
                $y = rand($range1[0], $range1[1]);
                $acak[] = array('y' => $y, 'yxmu' => ($y * $output[$i]['x']), 'mu' => $output[$i]['x']);

                $x++;
            }
        } else if ($output[$i]['p'] == $fuzzy_set[2][2]) {
            $x = 0;
            while ($x <= $count) {
                $y = rand($range2[0], $range2[1]);
                $acak[] = array('y' => $y, 'yxmu' => ($y * $output[$i]['x']), 'mu' => $output[$i]['x']);

                $x++;
            }
        } else if ($output[$i]['p'] == $fuzzy_set[2][3]) {
            $x = 0;
            while ($x <= $count) {
                $y = rand($range3[0], $range3[1]);
                $acak[] = array('y' => $y, 'yxmu' => ($y * $output[$i]['x']), 'mu' => $output[$i]['x']);

                $x++;
            }
        }
        $i++;
    }

    $yxmu = 0;
    $mu = 0;
    for ($a = 0; $a < count($acak); $a++) {
        $yxmu += $acak[$a]['yxmu'];
        $mu += $acak[$a]['mu'];
    }

    return ($yxmu / $mu);
}

if (isset($_POST['btn_proses'])) {

    $crisp_permintaan     = $_POST['permintaan'];
    $crisp_persediaan     = $_POST['persediaan'];

    muPermintaan($crisp_permintaan);
    muPersediaan($crisp_persediaan);

    inferensi_rules();
    inferensi_adv_rules();

    $def = number_format(defuzzyfication(), 3, ',', '.');

    echo "<br>";
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<header>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
        google.charts.load('current', {
            'packages': ['corechart']
        });
        google.charts.setOnLoadCallback(drawChart);
        google.charts.setOnLoadCallback(drawChart2);
        google.charts.setOnLoadCallback(drawChart3);

        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ['range', 'Rendah', 'sedang', 'tinggi'],
                ['60', 1, 0, 0],
                ['77,5', 1, 0, 0],
                ['80', 0.5, 0, 0],
                ['92,5', 0, 0.5, 0],
                ['95', 0, 1, 0],
                ['97,5', 0, 0.5, 0],
                ['110', 0, 0, 0.5],
                ['112,5', 0, 0, 1],
                ['130', 0, 0, 1],
            ]);

            var options = {
                title: 'Produksi',
                hAxis: {
                    titleTextStyle: {
                        color: '#333'
                    }
                },
                vAxis: {
                    minValue: 0
                }
            };

            var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
            chart.draw(data, options);
        }

        function drawChart2() {
            var data = google.visualization.arrayToDataTable([
                ['range', 'Rendah', 'sedang', 'tinggi'],
                ['50', 1, 0, 0],
                ['68', 1, 0, 0],
                ['70', 0.5, 0, 0],
                ['83', 0, 0.5, 0],
                ['85', 0, 1, 0],
                ['88', 0, 0.5, 0],
                ['100', 0, 0, 0.5],
                ['103', 0, 0, 1],
                ['120', 0, 0, 1],
            ]);

            var options = {
                title: 'Permintaan',
                hAxis: {
                    titleTextStyle: {
                        color: '#333'
                    }
                },
                vAxis: {
                    minValue: 0
                }
            };

            var chart = new google.visualization.AreaChart(document.getElementById('chart_divr'));
            chart.draw(data, options);
        }

        function drawChart3() {
            var data = google.visualization.arrayToDataTable([
                ['range', 'Rendah', 'sedang', 'tinggi'],
                ['10', 1, 0, 0],
                ['20', 1, 0, 0],
                ['22', 0.5, 0, 0],
                ['23', 0, 0.5, 0],
                ['25', 0, 1, 0],
                ['27', 0, 0.5, 0],
                ['28', 0, 0, 0.5],
                ['30', 0, 0, 1],
                ['40', 0, 0, 1],
            ]);

            var options = {
                title: 'Persediaan',
                hAxis: {
                    titleTextStyle: {
                        color: '#333'
                    }
                },
                vAxis: {
                    minValue: 0
                }
            };

            var chart = new google.visualization.AreaChart(document.getElementById('chart_dive'));
            chart.draw(data, options);
        }
    </script>
    <meta charset="utf-8">
    <style>
        #container {
            display: flex;
            /* establish flex container */
            flex-direction: row;
            /* default value; can be omitted */
            flex-wrap: nowrap;
            /* default value; can be omitted */
            justify-content: space-between;
            /* switched from default (flex-start, see below) */
            width: fit-content;
            height: fit-content;
        }

        #container>div {
            width: fit-content;
            height: fit-content;
        }

        .btn {
            overflow: visible;
            padding: 10px 15px;
            background-color: nero;
            margin-top: 20px;
            margin-bottom: 10px;
            margin-left: 10px;
            border-radius: 10px black;
            border: none;

        }

        input[type=number] {
            border-radius: 5px;
            border: 1px solid black;
            height: 30px;
            width: 500px;
            margin-top: 5px;
            margin-left: 30px;

        }

        #judul {
            margin-top: 15px;

            margin-left: 20px;
            font-size: 27px;
        }

        .jarak {
            margin-left: 10px;
        }
    </style>
</header>

<body style="margin-top: -10px;">
    <form method="POST" action="">
        <div>
            <div id="Container">

                <div>
                    <div id="Container">
                        <div>
                            <p id="judul">Logika Fuzzy</p>
                            <br>
                            <label class="jarak">Permintaan</label><br> <input name="permintaan" type="number" min="50" max="120" placeholder="range 50 .. 120 (dalam satuan masa)"><br><br>
                            <label class="jarak">Persediaan</label> <br> <input name="persediaan" type="number" min="10" max="40" placeholder="range 10 .. 40 (dalam satuan masa)"><br>
                            <button name="btn_proses" class="btn" type="submit">PROSES</button>
                        </div>
                    </div>
                    <div id="Container">
                        <div style="margin-left: 20px;">
                            <h2>Produksi</h2>
                            <h2 style="margin-left: 20px;">
                                <?php echo $def; ?>
                            </h2>
                        </div>
                        <div>
                            <div id="chart_div" style="width: 420px; height: 300px;"></div>
                        </div>
                    </div>
                </div>
                <div>
                    <div id="Container">
                        <div style="margin-left: 20px;">
                            <h3>Input</h3>
                            <p>Permintaan = <?php echo $crisp_permintaan; ?></p>
                            <p>Persediaan = <?php echo $crisp_persediaan; ?></p>
                        </div>
                        <div>
                            <div id="chart_divr" style="width: 300px; height: 200px;"></div>
                        </div>
                        <div>
                            <div id="chart_dive" style="width: 300px; height: 200px;"></div>
                        </div>
                    </div>
                    <div id="Container">
                        <div style="margin-left: 20px;">
                            <h3>Fuzzy Input</h3>
                            <?php foreach ($fuzzy_input as $input) : ?>
                                <?php $x = explode("|", $input['vl']); ?>
                                <?php $predikat = $fuzzy_set[$x[0]][$x[1]]; ?>
                                <p>
                                    <?php echo $input['in'] . " = " . $predikat . "[" . $input['mu'] . "]<br>"; ?>
                                </p>
                            <?php endforeach; ?>
                        </div>
                        <div style="margin-left: 20px;">
                            <h3>Output</h3>
                            <?php foreach ($output as $x) : ?>
                                <?php $i = 1; ?>
                                <p>
                                    <?php echo "Output $i = " . $x['p'] . "[" . $x['x'] . "]<br>"; ?>
                                </p>
                                <?php $i++; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div id="Container">
                        <div style="margin-left: 20px;">
                            <h3>Fuzzy Output</h3>
                            <?php foreach ($fuzzy_output as $output) : ?>
                                <?php $permintaan = $output['rules']['x'][0]; ?>
                                <?php $predikat1 = $fuzzy_set[0][$permintaan]; ?>
                                <?php $mu1 = $output['mu'][0]; ?>

                                <?php $persediaan = $output['rules']['x'][1]; ?>
                                <?php $predikat2 = $fuzzy_set[1][$persediaan]; ?>
                                <?php $mu2 = $output['mu'][1]; ?>

                                <?php $produksi = $output['rules']['y']; ?>
                                <?php $predikat4 = $fuzzy_set[2][$produksi]; ?>
                                <?php $mu4 = $output['mu'][2]; ?>
                                <p> IF permintaan = <?php echo $predikat1; ?> [ <?php echo $mu1 ?> ] AND persediaan = <?php echo $predikat2; ?> [ <?php echo $mu2 ?> ] THEN produksi = <?php echo  $predikat4; ?> [ <?php echo $mu4 ?> ] </p>
                            <?php endforeach; ?>
                        </div>

                    </div>


                </div>

            </div>

        </div>



    </form>
</body>

</html>