<?php
/**
	Account Period Back & Next from Now
*/

// Make a Back & Next
$d0 = new \DateTime($data);
$m0 = intval($d0->format('m'));
$q0 = floor($m0 / 4);
// echo "m0:$m0; q0:$q0";

$d0q = clone $d0;
//$d0q->sub(new \DateInterval($sub));

//$d0y = clone $d0;
//$d0y->sub(new \DateInterval(sprintf('P%dM', $m0)));

$d0->sub(new \DateInterval('P1M'));
$d_back_m0 = $d0->format('Y-m-01');
$d_back_m1 = $d0->format('Y-m-t');

// Back one Quarter
$sub = sprintf('P%dM', floor($m0/3) + ($m0 % 3));
// echo "sub:$sub\n";
$d0q->sub(new \DateInterval($sub));
$d_back_q0 = $d0q->format('Y-m-01');
$d_back_q0_f = sprintf('%04dq%d', $d0q->format('Y'), ceil($d0q->format('m') / 3));

// And the end of Back one Quarter
$d0q->add(new \DateInterval('P2M'));
$d_back_q1 = $d0q->format('Y-m-t');

$d0q->add(new \DateInterval('P3M'));
$d_next_q0 = $d0q->format('Y-m-t');
$d_next_q0_f = sprintf('%04dq%d', $d0q->format('Y'), floor($d0q->format('m') / 3) + 1);

$d0q->add(new \DateInterval('P3M'));
$d_next_q1 = $d0q->format('Y-m-t');
// $d_back_y0 = $d0->format('Y-01-01');
//$d_back_y1 = $d0->format('Y-12-31');

$d0->add(new \DateInterval('P2M'));
$d_next_m0 = $d0->format('Y-m-1');
$d_next_m1 = $d0->format('Y-m-t');

// $d0m->format('Y-m-1'), 'd1' => $d0m->format('Y-m-t')

//$d0q = new \DateTime($data);
//$d0q->sub(new \DateInterval('P1Q'));
//
//$d0y = new \DateTime($data);
//$d0y->sub(new \DateInterval('P1Y'));

// $d_back_0 = $d0->format('Y-m-1');
// $d_back_1 = $d->format('Y-m-t');


$d0 = new \DateTime($data);
$d0->add(new \DateInterval('P1Y'));
$d_next_y0 = $d0->format('Y-m-1');
$d_next_y1 = $d0->format('Y-m-t');

echo '<div style="border:1px solid #333; display:flex; margin:0; padding:0.25em; vertical-align:bottom;">';

//echo '<div class="c" style="flex: 1 1 auto;"><a href="?' . http_build_query(array('id' => $_GET['id'], 'd0' => $d_back_y0, 'd1' => $d_back_y1)) . '" title="Back to First of Year"><i class="fa fa-arrow-left"></i><i class="fa fa-arrow-left"></i><i class="fa fa-arrow-left"></i></a></div>';
//echo '<div class="c" style="flex: 1 1 auto;"><a href="?' . http_build_query(array('id' => $_GET['id'], 'p' => 'q', 'd0' => $d_back_q0, 'd1' => $d_back_q1)) . '" title="Back to First of Quarter"><i class="fa fa-arrow-left"></i>' . $d_back_q0_f . '<i class="fa fa-arrow-left"></i></a></div>';
echo '<div class="c" style="flex: 1 1 auto;"><a href="?' . http_build_query(array('id' => $_GET['id'], 'p' => 'm', 'd0' => $d_back_m0, 'd1' => $d_back_m1)) . '" style="display:block;" title="Back to Previous Month"><i class="fa fa-arrow-left"></i></a></div>';

echo '<div class="c" style="flex: 1 1 auto;"><a href="?' . http_build_query(array('id' => $_GET['id'], 'p' => 'm', 'd0' => $d_next_m0, 'd1' => $d_next_m1)) . '" style="display:block;" title="Advance to Next Month"><i class="fa fa-arrow-right"></i></a></div>';
//echo '<div class="c" style="flex: 1 1 auto;"><a href="?' . http_build_query(array('id' => $_GET['id'], 'p' => 'q', 'd0' => $d_next_q0, 'd1' => $d_next_q1)) . '" title="Advance to Next Quarter"><i class="fa fa-arrow-right"></i>' . $d_next_q0_f . '<i class="fa fa-arrow-right"></i></a></div>';
//echo '<div class="c" style="flex: 1 1 auto;"><a href="?' . http_build_query(array('id' => $_GET['id'], 'd0' => $d_next_y0, 'd1' => $d_next_y1)) . '" title="Advance to Next Year"><i class="fa fa-arrow-right"></i><i class="fa fa-arrow-right"></i><i class="fa fa-arrow-right"></i></a></div>';

echo '</div>';
