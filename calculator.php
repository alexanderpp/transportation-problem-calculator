<?php 
/* Use manufacturer
----------------------------------------------------------------
        | Client1 | Client2 | Client3 | Client4 | Nalichna stoka
----------------------------------------------------------------
Dealer1 |   60    |    80   |    70   |   50    |      80  
----------------------------------------------------------------
Dealer2 |   40    |    30   |   50    |   20    |      60
----------------------------------------------------------------
Dealer3 |   45    |    15   |    29   |   36    |      50
----------------------------------------------------------------
Needs   |   45    |    60   |    55   |   30    |
---------------------------------------------------------------- 
*/

$clients = $_POST["clients"];
$dealers = $_POST["dealers"];

$clients = 4;
$dealers = 3;

$step_counter = 1;
$iterator = 0;
$steps = array();

$costs = array(
        1 => array(1 => 60, 2 => 80, 3 => 70, 4 => 50),
        2 => array(1 => 40, 2 => 30, 3 => 50, 4 => 20),
        3 => array(1 => 45, 2 => 15, 3 => 29, 4 => 36)
    );
        
$supply = array(1 => 80, 2 => 60, 3 => 50);
$demand = array(1 => 45, 2 => 60, 3 => 55, 4 => 30);

$costs = json_decode($_POST["cost"], true);

$default_supply = json_decode($_POST["supply"], true);
$default_demand = json_decode($_POST["demand"], true);

/*
$costs = array(
        1 => array(1 => 2, 2 => 3, 3 => 11, 4 => 7),
        2 => array(1 => 1, 2 => 0, 3 => 6, 4 => 1),
        3 => array(1 => 5, 2 => 8, 3 => 15, 4 => 9)
    );
        
$default_supply = array(1 => 6, 2 => 1, 3 => 10);
$default_demand = array(1 => 7, 2 => 5, 3 => 3, 4 => 3);
*/
$supply = $default_supply;
$demand = $default_demand;

$steps[$step_counter]["title"] = "Проверяване дали проблемът е балансиран.";

$steps[$step_counter]["content"][] = "Общо количество налична стока при доставчиците - ".array_sum($supply)."<br />";
$steps[$step_counter]["content"][] = "Общо количество стока, от която се нуждаят клиентите - ".array_sum($demand).".<br /><br />";

if(array_sum($supply) == array_sum($demand)){
    $steps[$step_counter]["content"][] = "<b>Следователно проблемът е балансиран.</b>";
} else {
    $steps[$step_counter]["content"][] = "<b>Следователно проблемът НЕ е балансиран.</b>";
    
    $step_counter++;
    $steps[$step_counter]["title"] = "Балансиране на проблема.";
    
    if(array_sum($supply) > array_sum($demand)){

        $steps[$step_counter]["content"][] = "Доставчиците имат повече стока, отколкото е необходима на клиентите. Това няма да попречи на нормалното решаване на проблема.<br />";
    } else {
        $steps[$step_counter]["content"][] = "Трябва да бъде добавена еден допълнителен ред с фиктивен доставчик.<br />";
        $dealers++;
        $default_supply[] = array_sum($demand) - array_sum($supply);

        $supply = $default_supply;
        for($i = 1; $i <= $clients; $i++){
            $costs[$clients][$i] = 0;
        }
        
        $steps[$step_counter]["content"][] = '<table class="result-table">';

        for($i = 1; $i <= $dealers; $i++){
            $steps[$step_counter]["content"][] = '<tr>';
            for($j = 1; $j <= $clients; $j++){
                $steps[$step_counter]["content"][] .= "<td>".$costs[$i][$j]."</td>";
            }
            $steps[$step_counter]["content"][] .= '<td>'.$default_supply[$i].'</td>';
            $steps[$step_counter]["content"][] .= '</tr>';
            
        }
    
        $steps[$step_counter]["content"][] = '<tr>';
        foreach($default_demand as $j){
            $steps[$step_counter]["content"][] .= '<td>'.$j.'</td>';
        }
        $steps[$step_counter]["content"][] .= '<td></td>';
        $steps[$step_counter]["content"][] .= '</tr>';
        $steps[$step_counter]["content"][] = '</table>';
        
    }
}   

$step_counter++;

$steps[$step_counter]["title"] = "Определяне на неизвестните (относно количествата продукция, която се доставя от всеки доставчик до всеки клиент).";


for($i = 1; $i <= $dealers; $i++){
    for($j = 1; $j <= $clients; $j++){
        $steps[$step_counter]["content"][] = "x<sub>".$i.$j."</sub> - Стока, кято трябва да се достави от Доставчик ".$i." до Клиент ".$j."<br />";
    }
}
$step_counter++;
$steps[$step_counter]["title"] = "Определяне на ограничителните условия (спрямо доставчиците и клиентите).";

$i = 1;
foreach($supply as $d){
    $steps[$step_counter]["content"][$i] = "<br />Доставчик ".$i.": ";
    
    for($j = 1; $j <= $clients; $j++){
        $steps[$step_counter]["content"][$i] .= "x<sub>".$i.$j."</sub>";
        if($j < $clients) $steps[$step_counter]["content"][$i] .= " + ";
    }
    $steps[$step_counter]["content"][$i] .= " = ".$d;
    $i++;
}

$offset = count($supply);
$i = 1;
foreach($demand as $n){
    $steps[$step_counter]["content"][++$offset] = "<br />Клиент ".$i.": ";
    for($j = 1; $j <= $dealers; $j++){
        $steps[$step_counter]["content"][$offset] .= "x<sub>".$i.$j."</sub>";
        if($j < $dealers) $steps[$step_counter]["content"][$offset] .= " + ";
    }
    $steps[$step_counter]["content"][$offset] .= " = ".$n;
    $i++;
}

$steps[$step_counter]["content"][] .=  "<br /><br />x<sub>ij</sub> - цели числа";
$steps[$step_counter]["content"][] .=  "<br />x<sub>ij</sub> >= 0";

$step_counter++;
$steps[$step_counter]["title"] = "Определяне на целевата функция (относно транспортните разходи).";

$offset = 0;
$steps[$step_counter]["content"][$offset] = "L(";
for($i = 1; $i <= $dealers; $i++){
    for($j = 1; $j <= $clients; $j++){
        if(!($i == 1 && $j == 1)) $steps[$step_counter]["content"][$offset] .= ", ";
        $steps[$step_counter]["content"][$offset] .= "x<sub>".$i.$j."</sub>";
    }
}
$steps[$step_counter]["content"][$offset] .= ") = <br />";
$offset++;
$steps[$step_counter]["content"][$offset] = "= ";
for($i = 1; $i <= $dealers; $i++){
    for($j = 1; $j <= $clients; $j++){
        if(!($i == 1 && $j == 1)) $steps[$step_counter]["content"][$offset] .= " + ";
        $steps[$step_counter]["content"][$offset] .= $costs[$i][$j]."*x<sub>".$i.$j."</sub>";
    }
    if($i < $dealers){
        $steps[$step_counter]["content"][$offset] .= " + <br />";
        $offset++;
    }
    else $steps[$step_counter]["content"][$offset] .= " -> min";
}



//Fill with zeros
$x;
for($i = 1; $i <= $dealers; $i++){
    for($j = 1; $j <= $clients; $j++){
        $x[$i][$j] = 0;
    }
}

$xs = array();
$k = 1;
for($i = 1; $i <= $dealers; $i++){
    for($j = 1; $j <= $clients; $j++){
        $xs[$k] = array("position" => array($i, $j), "cost" => $costs[$i][$j]);
        $k++;
    }
}
usort($xs, 'compareCost');


foreach($xs as $a){
    $i = $a["position"][0];
    $j = $a["position"][1];
    
    if($demand[$j] == 0){
        continue;
    } else {
        $remains = ($supply[$i] > $demand[$j]) ? $supply[$i] - $demand[$j] : 0;
        $grabbed = $supply[$i] - $remains;
        $x[$i][$j] = $grabbed;
        $supply[$i] = $remains;
        $demand[$j] -= $grabbed;
    }
}
//print_r($xs);die;
while(true){
    $iterator++;
    
    $u = array_fill(1, $dealers, "NaN");
    $v = array_fill(1, $clients, "NaN");
    $s = array_fill(1, $dealers, array_fill(1,$clients, 0));
    
    $xx;
    $xy;
    $k = 0;
    for($i = 1; $i <= $dealers; $i++){
        for($j = 1; $j <= $clients; $j++){
            if($x[$i][$j] > 0){
                $xx[$k] = $i;
                $xy[$k] = $j;
                $k++;
            }
        }
    }
    
    $nonzero = array_map(null, $xx, $xy);
    $f = $nonzero[0][0];
    $u[$f] = 0;
    
    while(in_array("NaN", $u, true) || in_array("NaN", $v, true)){
        foreach($nonzero as $k=>$vi){
            $i = $nonzero[$k][0];
            $j = $nonzero[$k][1];

            if(strcmp($u[$i], "NaN") == 0 && strcmp($v[$j], "NaN") != 0){
                $u[$i] = $costs[$i][$j] - $v[$j];
            } else if (strcmp($u[$i], "NaN") != 0 && strcmp($v[$j], "NaN") == 0){
                $v[$j] = $costs[$i][$j] - $u[$i];
            } else {
                continue;
            }
        }
    }
    
    
    $step_counter++;
    $steps[$step_counter]["iteration"] = $iterator;
    if($iterator == 1){
        $steps[$step_counter]["title"] = "Избор на начално базисно решение.";
    } else {
        $steps[$step_counter]["title"] = "Намиране на ново базисно решение след промяна на пропорциите по базисните маршрути.";
    }
    $steps[$step_counter]["content"][0] = '<table class="result-table">';
    
    $total_expenses = 0;
    for($i = 1; $i <= $dealers; $i++){
        $steps[$step_counter]["content"][$i] = '<tr>';
        for($j = 1; $j <= $clients; $j++){
            $steps[$step_counter]["content"][$i] .= "<td>".$x[$i][$j]."</td>";
            $total_expenses += $x[$i][$j] * $costs[$i][$j];
        }
        $steps[$step_counter]["content"][$i] .= '<td>'.$default_supply[$i].'</td>';
        $steps[$step_counter]["content"][$i] .= '</tr>';
        
    }
    $i = count($steps[$step_counter]["content"]);

    $steps[$step_counter]["content"][$i] = '<tr>';
    foreach($default_demand as $j){
        $steps[$step_counter]["content"][$i] .= '<td>'.$j.'</td>';
    }
    $steps[$step_counter]["content"][$i] .= '<td><b>'.$total_expenses.'<b></td>';
    $steps[$step_counter]["content"][$i] .= '</tr>';
    $steps[$step_counter]["content"][] = '</table>';
    $steps[$step_counter]["content"][] = '<br>';
    $steps[$step_counter]["content"][] = '<b>Обща стойност на разходите*: '.$total_expenses.'</b><br>';
    
    //Finding S-matrix
    for($i = 1; $i <= $dealers; $i++){
        for($j = 1; $j <= $clients; $j++){
            $s[$i][$j] = $costs[$i][$j] - $u[$i] - $v[$j];
        }
    } 
    
    
    $step_counter++;
    $steps[$step_counter]["title"] = "Оценки на променливите.";
    $steps[$step_counter]["content"][] = 'Система:<br />';
    
    for($i = 1; $i <= $dealers; $i++){
        for($j = 1; $j <= $clients; $j++){
            if($x[$i][$j] > 0){
                $steps[$step_counter]["content"][] = '|   v<sub>'.$i.'</sub> + w<sub>'.$j.'</sub> = '.$costs[$i][$j].'<br />';
            }
        }
    }
    $steps[$step_counter]["content"][] = '<br />Решение на системата:<br />';
    
    foreach($u as $k=>$val){
        $steps[$step_counter]["content"][] = 'v<sub>'.$k.'</sub> = '.$val.'<br />';
    }
    $steps[$step_counter]["content"][] = '<br />';
    foreach($v as $k=>$val){
        $steps[$step_counter]["content"][] = 'w<sub>'.$k.'</sub> = '.$val.'<br />';
    }
    $steps[$step_counter]["content"][] = '<br /> Оценки за свободните променливи са (v<sub>i</sub> + w<sub>j</sub> - c<sub>ij</sub>):<br>';
    for($i = 1; $i <= $dealers; $i++){
        for($j = 1; $j <= $clients; $j++){
            if($x[$i][$j] == 0){
                $steps[$step_counter]["content"][] = 'x<sub>'.$i.$j.'</sub>: '.$u[$i].' + '.$v[$j].' - '.$costs[$i][$j].' = '.($u[$i] + $v[$j] - $costs[$i][$j]).'<br />';
            }
        }
    }
    
    $current_s = min( array_map("min", $s) );

    if($current_s >= 0){
        $steps[$step_counter - 1]["title"] = "<b><u>Решение.</u></b>";
        $steps[$step_counter]["title"] = "<b><u>Проверка.</u></b>";
        $steps[$step_counter]["content"][] = "<br>";
        $steps[$step_counter]["content"][] = "<br>";
        $steps[$step_counter]["content"][] = "<br>";
        $steps[$step_counter]["content"][] = '<small>* Общатата стойност на разходите се изчислява, като се умножат стойностите от клетките на базисното решение, по стойностите на клетките от началната таблица (разходите).</small><br>';
    
        break;
    }
    
    
    
    
    
    
    $start_i;
    $start_j;
    for($i = 1; $i <= $dealers; $i++){
        for($j = 1; $j <= $clients; $j++){
            if($s[$i][$j] == $current_s){
                $start_i = $i;
                $start_j = $j;
            }
        } 
    }
    
    $t = $x;
    
    $t[$start_i][$start_j] = 1;
    
    while(true){
        $xs = array();
        $ys = array();
        for($i = 1; $i <= $dealers; $i++){
            for($j = 1; $j <= $clients; $j++){
                if($t[$i][$j] != 0){
                    $xs[] = $i;
                    $ys[] = $j;
                }
            }
        }
        
        $xcount = array_count_values($xs);
        $ycount = array_count_values($ys);

        foreach($xcount as $xi=>$count){
            if($count <= 1){
                /* change
                foreach($t as &$val){
                    $val = 0;
                }
                */
                for($i = 1; $i <= $dealers; $i++){
                    $t[$xi][$i] = 0;
                }
            }
        }
        
        foreach($ycount as $y=>$count){
            if($count <= 1){
                for($i = 1; $i <= $dealers; $i++){
                    $t[$i][$y] = 0;
                }
            }
        }
        
        $bx = true;
        $by = true;
        
        foreach($xcount as $v){
            if($v <= 1){
                $bx = false;
                break;
            }
        }
        
        foreach($ycount as $y){
            if($y <= 1){
                $by = false;
                break;
            }
        }
   
        if($bx && $by) break;
    }
    
    
    $fringe = array();
    
    for($i = 1; $i <= $dealers; $i++){
        for($j = 1; $j <= $clients; $j++){
            if($t[$i][$j] > 0){
                $fringe[] = array($i, $j);
            }
        }
    }
    
    $size = count($fringe);
    $path = array();
    $path[] = array($start_i, $start_j);
    
    while(count($path) < $size){
        $last = end($path);
        if(in_array($last, $fringe, true)){
            if(($key = array_search($last, $fringe)) !== false) {
                unset($fringe[$key]);
            }
        }
        $min = compareDist($last, reset($fringe));
        $next = reset($fringe);
        
        foreach($fringe as $k=>$v){
            if(($val = compareDist($last, $fringe[$k])) < $min){
                $min = $val;
                $next = $fringe[$k];
            }
        }
        $path[] = $next;
    } 
    
    $neg = array();
    
    for($i = 1; $i < count($path); $i += 2){
        $neg[] = $path[$i];
    }
    
    $pos =array();
    
    for($i = 0; $i < count($path); $i += 2){
        $pos[] = $path[$i];
    }
    
    /*
    $zip_neg = array();
    foreach($neg as $v){
        $zip_neg[0][] = $v[0];
        $zip_neg[1][] = $v[1];
    }
    
    $zip_pos = array();
    foreach($pos as $v){
        $zip_pos[0][] = $v[0];
        $zip_pos[1][] = $v[1];
    }
    */
    
    $q = array();
    foreach($neg as $v){
        $q[] = $x[$v[0]][$v[1]];
    }

    $q = min($q);
    
    
    $step_counter++;
    $steps[$step_counter]["title"] = "Въвеждане на дъга.";

    $steps[$step_counter]["content"][0] = '<table class="result-table">';
    
    $curr_neg = 0;
    $curr_pos = 0;
    
    for($i = 1; $i <= $dealers; $i++){
        $steps[$step_counter]["content"][$i] = '<tr>';
        for($j = 1; $j <= $clients; $j++){
            $steps[$step_counter]["content"][$i] .= "<td>";
            $steps[$step_counter]["content"][$i] .= $x[$i][$j];
            if($curr_neg <= count($neg) && $neg[$curr_neg][0] == $i && $neg[$curr_neg][1] == $j){
                $steps[$step_counter]["content"][$i] .= " - ".$q;
                $curr_neg++;
            } else if($curr_pos <= count($pos) && $pos[$curr_pos][0] == $i && $pos[$curr_pos][1] == $j){
                $steps[$step_counter]["content"][$i] .= " + ".$q;
                $curr_pos++;
            }
            $steps[$step_counter]["content"][$i] .= "</td>";
        }
        $steps[$step_counter]["content"][$i] .= '<td>'.$default_supply[$i].'</td>';
        $steps[$step_counter]["content"][$i] .= '</tr>';
    }
    $i = count($steps[$step_counter]["content"]);

    $steps[$step_counter]["content"][$i] = '<tr>';
    foreach($default_demand as $j){
        $steps[$step_counter]["content"][$i] .= '<td>'.$j.'</td>';
    }
    $steps[$step_counter]["content"][$i] .= '<td></td>';
    $steps[$step_counter]["content"][$i] .= '</tr>';
    $steps[$step_counter]["content"][] = '</table>';
    
    
    foreach($neg as $v){
        $x[$v[0]][$v[1]] -= $q;
    }
    
    foreach($pos as $v){
        $x[$v[0]][$v[1]] += $q;
    }
    
   
    //echo "Решението е:\n";
    for($i = 1; $i <= $dealers; $i++){
        for($j = 1; $j <= $clients; $j++){
      //      echo $x[$i][$j]."\t";
        }
        //echo "\n";
    }
   
}

echo json_encode($steps);


function compareDist($a, $b){
    return abs($a[0] - $b[0]) + abs($a[1] - $b[1]);
}


function compareCost($a, $b)
{
  return $a['cost'] - $b['cost'];
}
?>
