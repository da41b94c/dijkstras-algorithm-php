<?php

declare(strict_types=1);

namespace Da41b94c\Dijkstra;

use InvalidArgumentException;
use SplPriorityQueue;

final class Dijkstra
{
	/**
	 * Finds the shortest path between two vertices.
	 *
	 * @param array<array-key, array<array-key, int|float>> $graph
	 * @return array{distance: int|float, path: list<array-key>}
	 */
	public function findShortestPath(array $graph, int|string $start, int|string $target): array
	{
		$graph = $this->normalizeGraph($graph);
		$start = $this->resolveVertex($graph, $start, 'Start');
		$target = $this->resolveVertex($graph, $target, 'Target');

		[$distances, $previous] = $this->calculate($graph, $start, $target);
		$distance = $distances[$target];

		if (is_infinite((float) $distance)) {
			return [
				'distance' => INF,
				'path' => [],
			];
		}

		return [
			'distance' => $distance,
			'path' => $this->buildPath($previous, $start, $target),
		];
	}

	/**
	 * Finds the shortest distance from the start vertex to every graph vertex.
	 *
	 * Unreachable vertices have INF as their distance.
	 *
	 * @param array<array-key, array<array-key, int|float>> $graph
	 * @return array<array-key, int|float>
	 */
	public function findAllShortestDistances(array $graph, int|string $start): array
	{
		$graph = $this->normalizeGraph($graph);
		$start = $this->resolveVertex($graph, $start, 'Start');

		[$distances] = $this->calculate($graph, $start);

		return $distances;
	}

	/**
	 * @param array<array-key, array<array-key, int|float>> $graph
	 * @return array{
	 *     0: array<array-key, int|float>,
	 *     1: array<array-key, array-key|null>
	 * }
	 */
	private function calculate(array $graph, int|string $start, int|string|null $target = null): array
	{
		$distances = [];
		$previous = [];

		foreach ($graph as $vertex => $_neighbors) {
			$distances[$vertex] = INF;
			$previous[$vertex] = null;
		}

		$distances[$start] = 0;

		$queue = new SplPriorityQueue();
		$queue->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
		$queue->insert($start, 0);

		while (!$queue->isEmpty()) {
			$current = $queue->extract();
			$currentVertex = $current['data'];
			$queuedDistance = -$current['priority'];

			if ($queuedDistance > $distances[$currentVertex]) {
				continue;
			}

			if ($target !== null && $currentVertex === $target) {
				break;
			}

			foreach ($graph[$currentVertex] as $neighbor => $weight) {
				$newDistance = $distances[$currentVertex] + $weight;

				if ($newDistance >= $distances[$neighbor]) {
					continue;
				}

				$distances[$neighbor] = $newDistance;
				$previous[$neighbor] = $currentVertex;
				$queue->insert($neighbor, -$newDistance);
			}
		}

		return [$distances, $previous];
	}

	/**
	 * @param array<array-key, array<array-key, int|float>> $graph
	 * @return array<array-key, array<array-key, int|float>>
	 */
	private function normalizeGraph(array $graph): array
	{
		if ($graph === []) {
			throw new InvalidArgumentException('Graph must contain at least one vertex.');
		}

		$normalizedGraph = [];

		foreach ($graph as $vertex => $neighbors) {
			if (!is_array($neighbors)) {
				throw new InvalidArgumentException(sprintf(
					'Neighbors of vertex "%s" must be an array.',
					(string) $vertex,
				));
			}

			$normalizedGraph[$vertex] ??= [];

			foreach ($neighbors as $neighbor => $weight) {
				if (!is_int($weight) && !is_float($weight)) {
					throw new InvalidArgumentException(sprintf(
						'Weight of edge "%s" -> "%s" must be an integer or float.',
						(string) $vertex,
						(string) $neighbor,
					));
				}

				if (!is_finite((float) $weight) || $weight < 0) {
					throw new InvalidArgumentException(sprintf(
						'Weight of edge "%s" -> "%s" must be finite and non-negative.',
						(string) $vertex,
						(string) $neighbor,
					));
				}

				$normalizedGraph[$vertex][$neighbor] = $weight;
				$normalizedGraph[$neighbor] ??= [];
			}
		}

		return $normalizedGraph;
	}

	/**
	 * @param array<array-key, array<array-key, int|float>> $graph
	 */
	private function resolveVertex(array $graph, int|string $vertex, string $role): int|string
	{
		if (!array_key_exists($vertex, $graph)) {
			throw new InvalidArgumentException(sprintf(
				'%s vertex "%s" does not exist in the graph.',
				$role,
				(string) $vertex,
			));
		}

		foreach ($graph as $graphVertex => $_neighbors) {
			if ($graphVertex === $vertex || (string) $graphVertex === (string) $vertex) {
				return $graphVertex;
			}
		}

		throw new InvalidArgumentException(sprintf(
			'%s vertex "%s" does not exist in the graph.',
			$role,
			(string) $vertex,
		));
	}

	/**
	 * @param array<array-key, array-key|null> $previous
	 * @return list<array-key>
	 */
	private function buildPath(array $previous, int|string $start, int|string $target): array
	{
		$path = [];
		$current = $target;

		while ($current !== null) {
			$path[] = $current;

			if ($current === $start) {
				return array_reverse($path);
			}

			$current = $previous[$current];
		}

		return [];
	}
}
