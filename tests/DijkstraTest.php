<?php

declare(strict_types=1);

namespace Da41b94c\Dijkstra\Tests;

use Da41b94c\Dijkstra\Dijkstra;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DijkstraTest extends TestCase
{
	public function testFindsShortestDistanceAndPath(): void
	{
		$graph = [
			'A' => ['B' => 9, 'C' => 3],
			'C' => ['B' => 4, 'D' => 7],
			'B' => ['D' => 1],
			'D' => [],
		];

		$result = (new Dijkstra())->findShortestPath($graph, 'A', 'D');

		self::assertSame(8, $result['distance']);
		self::assertSame(['A', 'C', 'B', 'D'], $result['path']);
	}

	public function testStartCanEqualTarget(): void
	{
		$result = (new Dijkstra())->findShortestPath(['A' => []], 'A', 'A');

		self::assertSame(0, $result['distance']);
		self::assertSame(['A'], $result['path']);
	}

	public function testReturnsInfinityAndEmptyPathForUnreachableTarget(): void
	{
		$result = (new Dijkstra())->findShortestPath([
			'A' => ['B' => 1],
			'B' => [],
			'C' => [],
		], 'A', 'C');

		self::assertInfinite($result['distance']);
		self::assertSame([], $result['path']);
	}

	public function testFindsDistancesToEveryVertex(): void
	{
		$distances = (new Dijkstra())->findAllShortestDistances([
			'A' => ['B' => 2, 'C' => 10],
			'B' => ['C' => 3],
			'D' => [],
		], 'A');

		self::assertSame(0, $distances['A']);
		self::assertSame(2, $distances['B']);
		self::assertSame(5, $distances['C']);
		self::assertInfinite($distances['D']);
	}

	public function testAcceptsVertexDeclaredOnlyAsNeighbor(): void
	{
		$result = (new Dijkstra())->findShortestPath([
			'A' => ['B' => 2],
		], 'A', 'B');

		self::assertSame(2, $result['distance']);
		self::assertSame(['A', 'B'], $result['path']);
	}

	public function testRejectsNegativeWeight(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('finite and non-negative');

		(new Dijkstra())->findShortestPath([
			'A' => ['B' => -1],
			'B' => [],
		], 'A', 'B');
	}

	public function testRejectsNonNumericWeight(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('integer or float');

		(new Dijkstra())->findShortestPath([
			'A' => ['B' => '1'],
			'B' => [],
		], 'A', 'B');
	}

	public function testRejectsMissingVertex(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Target vertex "C"');

		(new Dijkstra())->findShortestPath([
			'A' => ['B' => 1],
			'B' => [],
		], 'A', 'C');
	}

	public function testOneInstanceCanBeReused(): void
	{
		$algorithm = new Dijkstra();
		$graph = [
			'A' => ['B' => 1],
			'B' => ['C' => 1],
			'C' => [],
		];

		self::assertSame(2, $algorithm->findShortestPath($graph, 'A', 'C')['distance']);
		self::assertSame(1, $algorithm->findShortestPath($graph, 'B', 'C')['distance']);
	}

	public function testLegacyAdapterRemainsCompatibleAndReusable(): void
	{
		$graph = [
			'A' => ['B' => 9, 'C' => 3],
			'C' => ['B' => 4, 'D' => 7],
			'B' => ['D' => 1],
			'D' => [],
		];
		$times = ['B' => 9, 'C' => 3, 'D' => INF];
		$parents = ['B' => 'A', 'C' => 'A', 'D' => null];
		$algorithm = new \dijkstra();

		self::assertSame(8, $algorithm->find($graph, $times, $parents, 'D'));
		self::assertSame(8, $algorithm->find($graph, $times, $parents, 'D'));
	}
}
