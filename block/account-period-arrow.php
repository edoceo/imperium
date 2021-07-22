<?php
/**
 * Account Period Back & Next from Now
 */

// Make a Back & Next
$d0 = new \DateTime($data);
$m0 = intval($d0->format('m'));
$q0 = floor(($m0 / 4) + 1);


//$d0y = clone $d0;
//$d0y->sub(new \DateInterval(sprintf('P%dM', $m0)));

// -1 Month
$d0->sub(new \DateInterval('P1M'));
$d_back_m0 = $d0->format('Y-m-01');
$d_back_m1 = $d0->format('Y-m-t');

$d1 = clone $d0;
$d1->add(new \DateInterval('P2M'));
$d_next_m0 = $d1->format('Y-m-1');
$d_next_m1 = $d1->format('Y-m-t');

// Back one Quarter
$q = clone $d0;
$q->sub(new \DateInterval(sprintf('P%dM', 3)));
$back_q0 = $q->format('Y-m-01');
$back_q0_f = sprintf('%04dq%d', $q->format('Y'), ceil($q->format('m') / 3));

// And the end of Back one Quarter
$q->add(new \DateInterval('P3M'));
$back_q1 = $q->format('Y-m-t');

// Next Quarter Alpha/Omega
$q = clone $d0;
$q->add(new \DateInterval('P3M'));
$next_q0 = $q->format('Y-m-t');
$next_q0_f = sprintf('%04dq%d', $q->format('Y'), floor($q->format('m') / 3) + 1);

$q->add(new \DateInterval('P3M'));
$next_q1 = $q->format('Y-m-t');

// $d_back_y0 = $d0->format('Y-01-01');
// $d_back_y1 = $d0->format('Y-12-31');

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

echo '<div class="row">';
echo '<div class="col-md-6 c">';
// echo '<a class="btn btn-outline-secondary" href="?' . http_build_query(array('id' => $_GET['id'], 'd0' => $d_back_y0, 'd1' => $d_back_y1)) . '" title="Back to First of Year"><i class="fas fa-arrow-left"></i><i class="fas fa-arrow-left"></i><i class="fas fa-arrow-left"></i></a></div>';
//echo '<div class="c"><a href="?' . http_build_query(array('id' => $_GET['id'], 'p' => 'q', 'd0' => $back_q0, 'd1' => $back_q1)) . '" title="Back to First of Quarter"><i class="fas fa-arrow-left"></i>' . $back_q0_f . '<i class="fas fa-arrow-left"></i></a></div>';
echo '<a class="btn btn-outline-secondary" href="?' . http_build_query(array('id' => $_GET['id'], 'p' => 'm', 'd0' => $d_back_m0, 'd1' => $d_back_m1)) . '" title="Back to Previous Month"><i class="fas fa-arrow-left"></i></a>';
echo '</div>';

//echo "D:?; M:$m0; Q:$q0";

echo '<div class="col-md-6 c">';
echo '<a class="btn btn-outline-secondary" href="?' . http_build_query(array('id' => $_GET['id'], 'p' => 'm', 'd0' => $d_next_m0, 'd1' => $d_next_m1)) . '" title="Advance to Next Month"><i class="fas fa-arrow-right"></i></a>';
echo '</div>';

//echo '<div class="c"><a href="?' . http_build_query(array('id' => $_GET['id'], 'p' => 'q', 'd0' => $next_q0, 'd1' => $next_q1)) . '" title="Advance to Next Quarter"><i class="fas fa-arrow-right"></i>' . $next_q0_f . '<i class="fas fa-arrow-right"></i></a></div>';
//echo '<div class="c"><a href="?' . http_build_query(array('id' => $_GET['id'], 'd0' => $d_next_y0, 'd1' => $d_next_y1)) . '" title="Advance to Next Year"><i class="fas fa-arrow-right"></i><i class="fas fa-arrow-right"></i><i class="fas fa-arrow-right"></i></a></div>';

echo '</div>';
