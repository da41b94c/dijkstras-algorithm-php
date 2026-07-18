<?php

declare(strict_types=1);

require_once __DIR__ . '/src/Dijkstra.php';

/**
 * Backward-compatible adapter for the original API.
 *
 * @deprecated Use Da41b94c\Dijkstra\Dijkstra instead.
 */
if (!class_exists('dijkstra', false)) {
	class dijkstra
	{
		/** @var array<array-key, true> */
		private array $checked = [];

		/**
		 * @param array<array-key, int|float> $times
		 */
		public function findFastestNode(array $times): int|string|null
		{
			$minTime = INF;
			$fastestNode = null;

			foreach ($times as $node => $time) {
				if (!is_int($time) && !is_float($time)) {
					throw new \InvalidArgumentException('Every time must be an integer, float or INF.');
				}

				if (($time !== INF && !is_finite((float) $time)) || $time < 0) {
					throw new \InvalidArgumentException('Every time must be non-negative, finite or INF.');
				}

				if ($time < $minTime && !isset($this->checked[$node])) {
					$minTime = $time;
					$fastestNode = $node;
				}
			}

			return $fastestNode;
		}

		/**
		 * @param array<array-key, array<array-key, int|float>> $graph
		 * @param array<array-key, int|float> $times
		 * @param array<array-key, array-key|null> $parents
		 */
		public function find(array $graph, array $times, array $parents, int|string $target): int|float
		{
			$this->checked = [];

			if (!array_key_exists($target, $graph) && !array_key_exists($target, $times)) {
				throw new \InvalidArgumentException(sprintf(
					'Target vertex "%s" does not exist.',
					(string) $target,
				));
			}

			$node = $this->findFastestNode($times);

			while ($node !== null) {
				if (!isset($graph[$node]) || !is_array($graph[$node])) {
					throw new \InvalidArgumentException(sprintf(
						'Neighbors of vertex "%s" must be an array.',
						(string) $node,
					));
				}

				$time = $times[$node];

				foreach ($graph[$node] as $neighbor => $weight) {
					if ((!is_int($weight) && !is_float($weight)) || !is_finite((float) $weight) || $weight < 0) {
						throw new \InvalidArgumentException(sprintf(
							'Weight of edge "%s" -> "%s" must be finite and non-negative.',
							(string) $node,
							(string) $neighbor,
						));
					}

					$graph[$neighbor] ??= [];
					$times[$neighbor] ??= INF;
					$newTime = $time + $weight;

					if ($newTime < $times[$neighbor]) {
						$times[$neighbor] = $newTime;
						$parents[$neighbor] = $node;
					}
				}

				$this->checked[$node] = true;
				$node = $this->findFastestNode($times);
			}

			return $times[$target] ?? INF;
		}
	}
}

if (
	PHP_SAPI === 'cli'
	&& isset($_SERVER['SCRIPT_FILENAME'])
	&& realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__
) {
	$graph = [
		'A' => ['B' => 9, 'C' => 3],
		'C' => ['B' => 4, 'D' => 7],
		'B' => ['D' => 1],
		'D' => [],
	];

	$algorithm = new Da41b94c\Dijkstra\Dijkstra();
	$result = $algorithm->findShortestPath($graph, 'A', 'D');

	echo sprintf(
		"Minimum distance from A to D: %s; path: %s\n",
		(string) $result['distance'],
		implode(' -> ', $result['path']),
	);
}
