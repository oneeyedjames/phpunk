<?php

use PHPUnit\Framework\TestCase;

use PHPunk\Database\table;
use PHPunk\Database\relation;

class databaseRelationTest extends TestCase {
	public function setUp() {
		$lists = new table('lists');
		$tasks = new table('tasks');

		$this->task_list = new relation('task_list', $lists, $tasks, 'list_id');

		$votes = new table('votes', ['user_id', 'item_id']);
		$cards = new table('cards');

		$this->card_vote = new relation('card_vote', $votes, $cards, ['user_id', 'item_id']);
	}

	public function tearDown() {
		unset($this->task_list);
		unset($this->card_vote);
	}

	public function testFields() {
		$this->assertSame('lists', $this->task_list->ptable);
		$this->assertSame('tasks', $this->task_list->ftable);
		$this->assertSame('id', $this->task_list->pkey);
		$this->assertSame('list_id', $this->task_list->fkey);

		$this->assertSame('votes', $this->card_vote->ptable);
		$this->assertSame('cards', $this->card_vote->ftable);
		$this->assertSame(['user_id', 'item_id'], $this->card_vote->pkey);
		$this->assertSame(['user_id', 'item_id'], $this->card_vote->fkey);
	}

	public function testJoin() {
		$this->assertSame('`tasks` INNER JOIN `lists` ON '
			. '`tasks`.`list_id` = `lists`.`id`', $this->task_list->join);
		$this->assertSame('`cards` INNER JOIN `votes` ON '
			. '`cards`.`user_id` = `votes`.`user_id` AND '
			. '`cards`.`item_id` = `votes`.`item_id`', $this->card_vote->join);
	}

	public function testMatch() {
		$this->assertSame('`tasks`.`list_id` = `lists`.`id`', $this->task_list->match);
		$this->assertSame('`cards`.`user_id` = `votes`.`user_id` AND '
			. '`cards`.`item_id` = `votes`.`item_id`', $this->card_vote->match);
	}
}
