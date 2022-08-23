<?php

$target = 'D';

// Граф для моделирования связи между узлами (определения соседей)
$graph = [
	'A' => [
		'B' => 9,
		'C' => 3,
	],
	'C' => [
		'B' => 4,
		'D' => 7,
	],
	'B' => [
		'D' => 1,		
	],	
	'D' => []
];

// Хранит время, которое потребуется для перехода к узлу от начального узла A
$times = [
	'B' => 9, // Из точки А в точку B можно добраться за 9 минут
	'C' => 3,
	'D' => INF // Неизвестно (равно бесконечности) время, которое потребуется, чтобы добраться из точки А в точку D
];

$parents = [
	'B' => 'A',
	'C' => 'A',
	'D' => null,
];


class dijkstra {

	private $checked;
	
	public function __construct()
	{
		$this->checked = [];
	}
	
	function findFastestNode( $times )
	{
		$minTime = INF;
		$fastestNode = null;
		foreach( $times as $n => $time )
		{		
			if( $time < $minTime AND !in_array( $n, $this->checked ) )
			{
				$minTime = $time;
				$fastestNode = $n;
			}
		}
		return $fastestNode;
	}

	public function find( $graph, $times, $parents, $target )
	{	
		$node = $this->findFastestNode( $times );		
		while( $node != null )
		{
			$time = $times[ $node ];
			$neighbors = $graph[ $node ];
			foreach( $neighbors as $neighborNode => $neighborTime )
			{
				$newTime = $time + $neighborTime;
				if( $times[ $neighborNode ] > $newTime )
				{
					$times[ $neighborNode ] = $newTime;
					$parents[ $neighborNode ] = $node;
				}
			}
			array_push( $this->checked, $node );
			$node = $this->findFastestNode( $times );
		}
		// echo '<pre>', var_dump( $times ) ,'</pre>';		
		// echo '<pre>', var_dump( $parents ) ,'</pre>';		
		return $times[ $target ];
	}
		
}

$alg = new dijkstra();
$minTime = $alg->find( $graph, $times, $parents, $target );
echo 'Минимальное время из А в '. $target .' составляет: '. $minTime;
	
