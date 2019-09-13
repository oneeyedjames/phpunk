<?php

use PHPUnit\Framework\TestCase;

use PHPunk\url_schema;

class urlTest extends TestCase {
	const HOST = 'test.com';
	const PATH = 'root/path';

	const RESOURCE = 'document';
	const RESOURCE_ALIAS = 'documents';

	const SAVE_ACTION = 'save';
	const DELETE_ACTION = 'delete';

	const LIST_VIEW = 'list';
	const GRID_VIEW = 'grid';
	const ITEM_VIEW = 'item';
	const FORM_VIEW = 'form';

	const GLOBAL_ACTION = 'login';
	const GLOBAL_VIEW = 'dashboard';

	const FORMAT = 'xml';

	var $schema;

	public function setUp() {
		$this->schema = new url_schema(self::HOST);
	}

	public function testResource() {
		$this->assertFalse($this->schema->is_resource(self::RESOURCE));

		$this->schema->add_resource(self::RESOURCE);

		$this->assertEquals(self::RESOURCE, $this->schema->is_resource(self::RESOURCE));
	}

	public function testResourceAlias() {
		$this->assertFalse($this->schema->is_resource(self::RESOURCE));
		$this->assertFalse($this->schema->is_resource(self::RESOURCE_ALIAS));
		$this->assertFalse($this->schema->is_alias(self::RESOURCE_ALIAS));

		$this->schema->add_resource(self::RESOURCE, self::RESOURCE_ALIAS);

		$this->assertEquals(self::RESOURCE, $this->schema->is_resource(self::RESOURCE));
		$this->assertEquals(self::RESOURCE, $this->schema->is_resource(self::RESOURCE_ALIAS));
		$this->assertEquals(self::RESOURCE, $this->schema->is_alias(self::RESOURCE_ALIAS));
	}

	public function testSeparateAlias() {
		$this->assertFalse($this->schema->is_resource(self::RESOURCE));
		$this->assertFalse($this->schema->is_resource(self::RESOURCE_ALIAS));
		$this->assertFalse($this->schema->is_alias(self::RESOURCE_ALIAS));

		$this->schema->add_resource(self::RESOURCE);

		$this->assertEquals(self::RESOURCE, $this->schema->is_resource(self::RESOURCE));
		$this->assertFalse($this->schema->is_resource(self::RESOURCE_ALIAS));
		$this->assertFalse($this->schema->is_alias(self::RESOURCE_ALIAS));

		$this->schema->add_alias(self::RESOURCE_ALIAS, self::RESOURCE);

		$this->assertEquals(self::RESOURCE, $this->schema->is_resource(self::RESOURCE));
		$this->assertEquals(self::RESOURCE, $this->schema->is_resource(self::RESOURCE_ALIAS));
		$this->assertEquals(self::RESOURCE, $this->schema->is_alias(self::RESOURCE_ALIAS));
	}

	public function testResourceAction() {
		$this->assertFalse($this->schema->is_resource(self::RESOURCE));
		$this->assertFalse($this->schema->is_action(self::SAVE_ACTION, self::RESOURCE));
		$this->assertFalse($this->schema->is_action(self::DELETE_ACTION, self::RESOURCE));
		$this->assertFalse($this->schema->is_action('custom', self::RESOURCE));

		$this->schema->add_resource(self::RESOURCE);

		$this->assertEquals(self::RESOURCE, $this->schema->is_resource(self::RESOURCE));
		$this->assertEquals(self::SAVE_ACTION, $this->schema->is_action(self::SAVE_ACTION, self::RESOURCE));
		$this->assertEquals(self::DELETE_ACTION, $this->schema->is_action(self::DELETE_ACTION, self::RESOURCE));
		$this->assertFalse($this->schema->is_action('custom', self::RESOURCE));

		$this->schema->add_action('custom', self::RESOURCE);

		$this->assertEquals(self::RESOURCE, $this->schema->is_resource(self::RESOURCE));
		$this->assertEquals(self::SAVE_ACTION, $this->schema->is_action(self::SAVE_ACTION, self::RESOURCE));
		$this->assertEquals(self::DELETE_ACTION, $this->schema->is_action(self::DELETE_ACTION, self::RESOURCE));
		$this->assertEquals('custom', $this->schema->is_action('custom', self::RESOURCE));
	}

	public function testResourceView() {
		$this->assertFalse($this->schema->is_resource(self::RESOURCE));
		$this->assertFalse($this->schema->is_view(self::LIST_VIEW, self::RESOURCE));
		$this->assertFalse($this->schema->is_view(self::GRID_VIEW, self::RESOURCE));
		$this->assertFalse($this->schema->is_view(self::ITEM_VIEW, self::RESOURCE));
		$this->assertFalse($this->schema->is_view(self::FORM_VIEW, self::RESOURCE));
		$this->assertFalse($this->schema->is_view('custom', self::RESOURCE));

		$this->schema->add_resource(self::RESOURCE);

		$this->assertEquals(self::RESOURCE, $this->schema->is_resource(self::RESOURCE));
		$this->assertEquals(self::LIST_VIEW, $this->schema->is_view(self::LIST_VIEW, self::RESOURCE));
		$this->assertEquals(self::GRID_VIEW, $this->schema->is_view(self::GRID_VIEW, self::RESOURCE));
		$this->assertEquals(self::ITEM_VIEW, $this->schema->is_view(self::ITEM_VIEW, self::RESOURCE));
		$this->assertEquals(self::FORM_VIEW, $this->schema->is_view(self::FORM_VIEW, self::RESOURCE));
		$this->assertFalse($this->schema->is_view('custom', self::RESOURCE));

		$this->schema->add_view('custom', self::RESOURCE);

		$this->assertEquals(self::RESOURCE, $this->schema->is_resource(self::RESOURCE));
		$this->assertEquals(self::LIST_VIEW, $this->schema->is_view(self::LIST_VIEW, self::RESOURCE));
		$this->assertEquals(self::GRID_VIEW, $this->schema->is_view(self::GRID_VIEW, self::RESOURCE));
		$this->assertEquals(self::ITEM_VIEW, $this->schema->is_view(self::ITEM_VIEW, self::RESOURCE));
		$this->assertEquals(self::FORM_VIEW, $this->schema->is_view(self::FORM_VIEW, self::RESOURCE));
		$this->assertEquals('custom', $this->schema->is_view('custom', self::RESOURCE));
	}

	public function testGlobalAction() {
		$this->assertFalse($this->schema->is_action(self::GLOBAL_ACTION));

		$this->schema->add_action(self::GLOBAL_ACTION);

		$this->assertEquals(self::GLOBAL_ACTION, $this->schema->is_action(self::GLOBAL_ACTION));
	}

	public function testGlobalView() {
		$this->assertFalse($this->schema->is_view(self::GLOBAL_VIEW));

		$this->schema->add_view(self::GLOBAL_VIEW);

		$this->assertEquals(self::GLOBAL_VIEW, $this->schema->is_view(self::GLOBAL_VIEW));
	}

	public function testBuild() {
		$this->assertEquals('http://test.com/', $this->schema->build([]));
		$this->assertEquals('https://test.com/', $this->schema->build([], true));

		$other_schema = new url_schema(self::HOST, self::PATH);

		$this->assertEquals('http://test.com/root/path/', $other_schema->build([]));
		$this->assertEquals('https://test.com/root/path/', $other_schema->build([], true));
	}

	public function testBuildResourcePath() {
		$this->schema->add_resource(self::RESOURCE, self::RESOURCE_ALIAS);

		$this->assertEquals(self::RESOURCE, $this->schema->is_resource(self::RESOURCE));
		$this->assertEquals(self::SAVE_ACTION, $this->schema->is_action(self::SAVE_ACTION, self::RESOURCE));
		$this->assertEquals(self::LIST_VIEW, $this->schema->is_view(self::LIST_VIEW, self::RESOURCE));

		$this->assertEquals('document/save', $this->schema->build_path([
			'resource' => self::RESOURCE,
			'action'   => self::SAVE_ACTION,
			'view'     => self::LIST_VIEW // ignore view when action is valid
		]));

		$this->assertEquals('document/list', $this->schema->build_path([
			'resource' => self::RESOURCE,
			'action'   => self::GLOBAL_ACTION, // ignore invalid resource action
			'view'     => self::LIST_VIEW
		]));

		$this->assertEquals('document/111', $this->schema->build_path([
			'resource' => self::RESOURCE,
			'id'       => 111
		]));

		$this->assertEquals('document/111/save', $this->schema->build_path([
			'resource' => self::RESOURCE,
			'action'   => self::SAVE_ACTION,
			'view'     => self::LIST_VIEW, // ignore view when action is valid
			'id'       => 111
		]));

		$this->assertEquals('document/111/list', $this->schema->build_path([
			'resource' => self::RESOURCE,
			'action'   => self::GLOBAL_ACTION, // ignore invalid resource action
			'view'     => self::LIST_VIEW,
			'id'       => 111
		]));
	}

	public function testBuildGlobalPath() {
		$this->schema->add_action(self::GLOBAL_ACTION);
		$this->schema->add_view(self::GLOBAL_VIEW);

		$this->assertEquals(self::GLOBAL_ACTION, $this->schema->is_action(self::GLOBAL_ACTION));
		$this->assertEquals(self::GLOBAL_VIEW, $this->schema->is_view(self::GLOBAL_VIEW));

		$this->assertEquals(self::GLOBAL_ACTION, $this->schema->build_path([
			'action' => self::GLOBAL_ACTION,
			'view'   => self::GLOBAL_VIEW // ignore view when action is valid
		]));

		$this->assertEquals(self::GLOBAL_VIEW, $this->schema->build_path([
			'action' => self::SAVE_ACTION, // ignore invalid global action
			'view'   => self::GLOBAL_VIEW
		]));
	}

	public function testBuildPaginationPath() {
		$this->assertEquals('page/3', $this->schema->build_path([
			'page' => 3
		]));

		$this->assertEquals('per_page/12', $this->schema->build_path([
			'per_page' => 12
		]));

		$this->assertEquals('page/3/per_page/12', $this->schema->build_path([
			'page'     => 3,
			'per_page' => 12
		]));

		// parameter  order is normalized
		$this->assertEquals('page/3/per_page/12', $this->schema->build_path([
			'per_page' => 12,
			'page'     => 3
		]));
	}

	public function testBuildSortPath() {
		$this->assertEquals('sort~asc/first_name/sort~desc/last_name', $this->schema->build_path([
			'sort' => [
				'first_name' => 'asc',
				'last_name'  => 'desc',
				'salutation' => 'none' // ignore invalid order
			]
		]));

		// parameter order is preserved
		$this->assertEquals('sort~desc/last_name/sort~asc/first_name', $this->schema->build_path([
			'sort' => [
				'last_name'  => 'desc',
				'first_name' => 'asc',
				'salutation' => 'none' // ignore invalid order
			]
		]));
	}

	public function testBuildFilterPath() {
		$this->assertEquals('first_name/John/last_name/Smith', $this->schema->build_path([
			'filter' => [
				'first_name' => 'John',
				'last_name'  => 'Smith'
			]
		]));

		// parameter order is preserved
		$this->assertEquals('last_name/Smith/first_name/John', $this->schema->build_path([
			'filter' => [
				'last_name'  => 'Smith',
				'first_name' => 'John'
			]
		]));
	}

	public function testBuildCompletePath() {
		$path = 'document/list/page/3/per_page/12/sort~asc/title/author/John+Smith';
		$params = [
			'resource' => self::RESOURCE,
			'view'     => self::LIST_VIEW,
			'page'     => 3,
			'per_page' => 12,
			'sort'     => [ 'title' => 'asc' ],
			'filter'   => [ 'author' => 'John Smith' ]
		];

		$this->schema->add_resource(self::RESOURCE, self::RESOURCE_ALIAS);
		$this->assertEquals($path, $this->schema->build_path($params));
	}

	public function testParseResourcePath() {
		$this->schema->add_resource(self::RESOURCE, self::RESOURCE_ALIAS);

		$this->assertEquals(self::RESOURCE, $this->schema->is_resource(self::RESOURCE));
		$this->assertEquals(self::SAVE_ACTION, $this->schema->is_action(self::SAVE_ACTION, self::RESOURCE));
		$this->assertEquals(self::LIST_VIEW, $this->schema->is_view(self::LIST_VIEW, self::RESOURCE));

		$params = $this->schema->parse_path('document.xml');

		$this->assertEquals(self::RESOURCE, $params['resource']);
		$this->assertEquals(self::FORMAT, $params['format']);

		$params = $this->schema->parse_path('document/111.xml');

		$this->assertEquals(self::RESOURCE, $params['resource']);
		$this->assertEquals(111, $params['id']);
		$this->assertEquals(self::FORMAT, $params['format']);

		$this->schema->add_resource('version', 'versions');

		$params = $this->schema->parse_path('document/111/versions');

		$this->assertEquals('version', $params['resource']);
		$this->assertNull(@$params['format']);
		$this->assertEquals(111, $params['filter'][self::RESOURCE]);

		$params = $this->schema->parse_path('document/111/versions.xml');

		$this->assertEquals('version', $params['resource']);
		$this->assertEquals(self::FORMAT, $params['format']);
		$this->assertEquals(111, $params['filter'][self::RESOURCE]);
	}

	public function testResourceActionPath() {
		$this->schema->add_resource(self::RESOURCE, self::RESOURCE_ALIAS);

		$this->assertEquals(self::RESOURCE, $this->schema->is_resource(self::RESOURCE));
		$this->assertEquals(self::SAVE_ACTION, $this->schema->is_action(self::SAVE_ACTION, self::RESOURCE));
		$this->assertEquals(self::DELETE_ACTION, $this->schema->is_action(self::DELETE_ACTION, self::RESOURCE));

		$params = $this->schema->parse_path('document/111/save');

		$this->assertEquals(self::RESOURCE, $params['resource']);
		$this->assertEquals(self::SAVE_ACTION, $params['action']);
		$this->assertEquals(111, $params['id']);
		$this->assertNull(@$params['format']);

		$params = $this->schema->parse_path('document/111/save.xml');

		$this->assertEquals(self::RESOURCE, $params['resource']);
		$this->assertEquals(self::SAVE_ACTION, $params['action']);
		$this->assertEquals(111, $params['id']);
		$this->assertEquals(self::FORMAT, $params['format']);

		$this->schema->add_resource('version', 'versions');

		$params = $this->schema->parse_path('document/111/versions/save');

		$this->assertEquals('version', $params['resource']);
		$this->assertEquals(self::SAVE_ACTION, $params['action']);
		$this->assertNull(@$params['format']);
		$this->assertEquals(111, $params['filter'][self::RESOURCE]);

		$params = $this->schema->parse_path('document/111/versions/save.xml');

		$this->assertEquals('version', $params['resource']);
		$this->assertEquals(self::SAVE_ACTION, $params['action']);
		$this->assertEquals(self::FORMAT, $params['format']);
		$this->assertEquals(111, $params['filter'][self::RESOURCE]);
	}

	public function testResourceViewPath() {
		$this->schema->add_resource(self::RESOURCE, self::RESOURCE_ALIAS);

		$this->assertEquals(self::RESOURCE, $this->schema->is_resource(self::RESOURCE));
		$this->assertEquals(self::SAVE_ACTION, $this->schema->is_action(self::SAVE_ACTION, self::RESOURCE));
		$this->assertEquals(self::DELETE_ACTION, $this->schema->is_action(self::DELETE_ACTION, self::RESOURCE));

		$params = $this->schema->parse_path('document/111/list');

		$this->assertEquals(self::RESOURCE, $params['resource']);
		$this->assertEquals(self::LIST_VIEW, $params['view']);
		$this->assertEquals(111, $params['id']);
		$this->assertNull(@$params['format']);

		$params = $this->schema->parse_path('document/111/list.xml');

		$this->assertEquals(self::RESOURCE, $params['resource']);
		$this->assertEquals(self::LIST_VIEW, $params['view']);
		$this->assertEquals(111, $params['id']);
		$this->assertEquals(self::FORMAT, $params['format']);

		$this->schema->add_resource('version', 'versions');

		$params = $this->schema->parse_path('document/111/versions/list');

		$this->assertEquals('version', $params['resource']);
		$this->assertNull(self::FORMAT, $params['format']);
		$this->assertEquals(self::LIST_VIEW, $params['view']);
		$this->assertEquals(111, $params['filter'][self::RESOURCE]);

		$params = $this->schema->parse_path('document/111/versions/list.xml');

		$this->assertEquals('version', $params['resource']);
		$this->assertEquals(self::FORMAT, $params['format']);
		$this->assertEquals(self::LIST_VIEW, $params['view']);
		$this->assertEquals(111, $params['filter'][self::RESOURCE]);
	}

	public function testParseGlobalPath() {
		$this->schema->add_action(self::GLOBAL_ACTION);
		$this->schema->add_view(self::GLOBAL_VIEW);

		$this->assertEquals(self::GLOBAL_ACTION, $this->schema->is_action(self::GLOBAL_ACTION));
		$this->assertEquals(self::GLOBAL_VIEW, $this->schema->is_view(self::GLOBAL_VIEW));

		$params = $this->schema->parse_path(self::GLOBAL_ACTION);

		$this->assertEquals(self::GLOBAL_ACTION, $params['action']);
		$this->assertNull(@$params['view']);

		$params = $this->schema->parse_path(self::GLOBAL_VIEW);

		$this->assertNull(@$params['action']);
		$this->assertEquals(self::GLOBAL_VIEW, $params['view']);

		$extended = implode('.', [self::GLOBAL_ACTION, self::FORMAT]);

		$params = $this->schema->parse_path($extended);

		$this->assertEquals(self::GLOBAL_ACTION, $params['action']);
		$this->assertEquals(self::FORMAT, $params['format']);

		$extended = implode('.', [self::GLOBAL_VIEW, self::FORMAT]);

		$params = $this->schema->parse_path($extended);

		$this->assertEquals(self::GLOBAL_VIEW, $params['view']);
		$this->assertEquals(self::FORMAT, $params['format']);
	}

	public function testParsePaginationPath() {
		$params = $this->schema->parse_path('page/3/per_page/12');

		$this->assertEquals(3, $params['page']);
		$this->assertEquals(12, $params['per_page']);

		$params = $this->schema->parse_path('per_page/12/page/3');

		$this->assertEquals(3, $params['page']);
		$this->assertEquals(12, $params['per_page']);
	}

	public function testParseSortPath() {
		$params = $this->schema->parse_path('sort~asc/first_name/sort~desc/last_name');

		$this->assertEquals('asc', $params['sort']['first_name']);
		$this->assertEquals('desc', $params['sort']['last_name']);

		$params = $this->schema->parse_path('sort/first_name/sort~desc/last_name');

		$this->assertEquals('asc', $params['sort']['first_name']); // default to ascending
		$this->assertEquals('desc', $params['sort']['last_name']);
	}

	public function testParseFilterPath() {
		$params = $this->schema->parse_path('first_name/John/last_name/Smith');

		$this->assertEquals('John', $params['filter']['first_name']);
		$this->assertEquals('Smith', $params['filter']['last_name']);
	}

	public function testParseCompletePath() {
		$this->schema->add_resource(self::RESOURCE, self::RESOURCE_ALIAS);

		$params = $this->schema->parse_path('documents/list/page/3/per_page/12/sort~asc/title/author/John+Smith');

		$this->assertEquals(self::RESOURCE, $params['resource']);
		$this->assertEquals(self::LIST_VIEW, $params['view']);
		$this->assertEquals(3, $params['page']);
		$this->assertEquals(12, $params['per_page']);
		$this->assertEquals('asc', $params['sort']['title']);
		$this->assertEquals('John Smith', $params['filter']['author']);
	}
}
