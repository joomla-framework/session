<?php
/**
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Github\Tests;

use Joomla\Github\Package\Issues;
use Joomla\Github\Tests\Stub\GitHubTestCase;

/**
 * Test class for Issues.
 *
 * @since  1.0
 */
class IssuesTest extends GitHubTestCase
{
	/**
	 * @var    Issues  Object under test.
	 * @since  1.0
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->object = new Issues($this->options, $this->client);
	}

	/**
	 * Tests the create method with assignee
	 *
	 * @return void
	 */
	public function testCreate()
	{
		$this->response->code = 201;
		$this->response->body = $this->sampleString;

		$issue = new \stdClass;
		$issue->title = '{title}';
		$issue->milestone = '{milestone}';
		$issue->labels = ['{label1}'];
		$issue->body = '{body}';
		$issue->assignee = '{assignee}';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/{user}/{repo}/issues', json_encode($issue))
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->create('{user}', '{repo}', '{title}', '{body}', '{assignee}', '{milestone}', ['{label1}']),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the create method with assignees
	 *
	 * @return void
	 */
	public function testCreate2()
	{
		$this->response->code = 201;
		$this->response->body = $this->sampleString;

		$issue = new \stdClass;
		$issue->title = '{title}';
		$issue->milestone = '{milestone}';
		$issue->labels = ['{label1}'];
		$issue->body = '{body}';
		$issue->assignees = ['{assignee1}'];

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/{user}/{repo}/issues', json_encode($issue))
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->create('{user}', '{repo}', '{title}', '{body}', null, '{milestone}', ['{label1}'], ['{assignee1}']),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the create method - failure
	 *
	 * @expectedException  \DomainException
	 *
	 * @return void
	 */
	public function testCreateFailure()
	{
		$this->response->code = 501;
		$this->response->body = $this->errorString;

		$issue = new \stdClass;
		$issue->title = '{title}';
		$issue->milestone = '{milestone}';
		$issue->labels = [];
		$issue->body = '{body}';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/{user}/{repo}/issues', json_encode($issue))
			->will($this->returnValue($this->response));

		$this->object->create('{user}', '{repo}', '{title}', '{body}', null, '{milestone}');
	}

	/**
	 * Tests the create method - failure assigning both assignee and assignees.
	 *
	 * @expectedException  \UnexpectedValueException
	 * @expectedExceptionMessage You cannot pass both assignee and assignees. Only one may be provided.
	 *
	 * @return void
	 */
	public function testCreateFailure2()
	{
		$this->object->create('{user}', '{repo}', '{title}', '{body}', '{assignee]', '{milestone}', ['{label1}'], ['{assignee1]']);
	}

	/**
	 * Tests the createComment method
	 *
	 * @return void
	 */
	public function testCreateComment()
	{
		$this->response->code = 201;
		$this->response->body = $this->sampleString;

		$issue = new \stdClass;
		$issue->body = 'My Insightful Comment';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/issues/523/comments', json_encode($issue))
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->comments->create('joomla', 'joomla-platform', 523, 'My Insightful Comment'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createComment method - failure
	 *
	 * @expectedException  \DomainException
	 *
	 * @return void
	 */
	public function testCreateCommentFailure()
	{
		$this->response->code = 501;
		$this->response->body = $this->errorString;

		$issue = new \stdClass;
		$issue->body = 'My Insightful Comment';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/issues/523/comments', json_encode($issue))
			->will($this->returnValue($this->response));

		$this->object->comments->create('joomla', 'joomla-platform', 523, 'My Insightful Comment');
	}

	/**
	 * Tests the createLabel method
	 * @todo move
	 * @return void
	 *
	public function testCreateLabel()
	{
		$this->response->code = 201;
		$this->response->body = $this->sampleString;

		$issue = new \stdClass;
		$issue->name = 'My Insightful Label';
		$issue->color = 'My Insightful Color';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/labels', json_encode($issue))
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->createLabel('joomla', 'joomla-platform', 'My Insightful Label', 'My Insightful Color'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}
	 */

	/**
	 * Tests the createLabel method - failure
	 * @todo move
	 * @expectedException  \DomainException
	 *
	 * @return void
	 *
	public function testCreateLabelFailure()
	{
		$this->response->code = 501;
		$this->response->body = $this->errorString;

		$issue = new \stdClass;
		$issue->name = 'My Insightful Label';
		$issue->color = 'My Insightful Color';

		$this->client->expects($this->once())
			->method('post')
			->with('/repos/joomla/joomla-platform/labels', json_encode($issue))
			->will($this->returnValue($this->response));

		$this->object->createLabel('joomla', 'joomla-platform', 'My Insightful Label', 'My Insightful Color');
	}
	 */

	/**
	 * Tests the deleteComment method
	 * @todo move
	 * @return void
	 *
	public function testDeleteComment()
	{
		$this->response->code = 204;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/repos/joomla/joomla-platform/issues/comments/254')
			->will($this->returnValue($this->response));

		$this->object->deleteComment('joomla', 'joomla-platform', 254);
	}
	 */

	/**
	 * Tests the deleteComment method - failure
	 * @todo move
	 * @expectedException  \DomainException
	 *
	 * @return void
	 *
	public function testDeleteCommentFailure()
	{
		$this->response->code = 504;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/repos/joomla/joomla-platform/issues/comments/254')
			->will($this->returnValue($this->response));

		$this->object->deleteComment('joomla', 'joomla-platform', 254);
	}
	 */

	/**
	 * Tests the deleteLabel method
	 * @todo move
	 * @return void
	 *
	public function testDeleteLabel()
	{
		$this->response->code = 204;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/repos/joomla/joomla-platform/labels/254')
			->will($this->returnValue($this->response));

		$this->object->deleteLabel('joomla', 'joomla-platform', 254);
	}
	 */

	/**
	 * Tests the deleteLabel method - failure
	 * @todo move
	 * @expectedException  \DomainException
	 *
	 * @return void
	 *
	public function testDeleteLabelFailure()
	{
		$this->response->code = 504;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/repos/joomla/joomla-platform/labels/254')
			->will($this->returnValue($this->response));

		$this->object->deleteLabel('joomla', 'joomla-platform', 254);
	}
	 */

	/**
	 * Tests the edit method
	 *
	 * @return void
	 */
	public function testEdit()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$issue = new \stdClass;
		$issue->title = 'My issue';
		$issue->body = 'These are my changes - please review them';
		$issue->state = 'Closed';
		$issue->assignee = 'JoeAssignee';
		$issue->milestone = '12.2';
		$issue->labels = array('Fixed');

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/issues/523', json_encode($issue))
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->edit('joomla', 'joomla-platform', 523, 'Closed', 'My issue', 'These are my changes - please review them',
				'JoeAssignee', '12.2', array('Fixed')
			),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the edit method - failure
	 *
	 * @expectedException  \DomainException
	 *
	 * @return void
	 */
	public function testEditFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$issue = new \stdClass;
		$issue->title = 'My issue';
		$issue->body = 'These are my changes - please review them';
		$issue->state = 'Closed';

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/issues/523', json_encode($issue))
			->will($this->returnValue($this->response));

		$this->object->edit('joomla', 'joomla-platform', 523, 'Closed', 'My issue', 'These are my changes - please review them');
	}

	/**
	 * Tests the editComment method
	 * @todo move
	 * @return void
	 *
	public function testEditComment()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$issue = new \stdClass;
		$issue->body = 'This comment is now even more insightful';

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/issues/comments/523', json_encode($issue))
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->editComment('joomla', 'joomla-platform', 523, 'This comment is now even more insightful'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}
	 */

	/**
	 * Tests the editComment method - failure
	 * @todo move
	 * @expectedException  \DomainException
	 *
	 * @return void
	 *
	public function testEditCommentFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$issue = new \stdClass;
		$issue->body = 'This comment is now even more insightful';

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/issues/comments/523', json_encode($issue))
			->will($this->returnValue($this->response));

		$this->object->editComment('joomla', 'joomla-platform', 523, 'This comment is now even more insightful');
	}
	 */

	/**
	 * Tests the editLabel method
	 * @todo move
	 * @return void
	 *
	public function testEditLabel()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$issue = new \stdClass;
		$issue->name = 'This label is now even more insightful';
		$issue->color = 'This color is now even more insightful';

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/labels/523', json_encode($issue))
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->editLabel('joomla', 'joomla-platform', 523, 'This label is now even more insightful', 'This color is now even more insightful'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}
	 */

	/**
	 * Tests the editLabel method - failure
	 * @todo move
	 * @expectedException  \DomainException
	 *
	 * @return void
	 *
	public function testEditLabelFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$issue = new \stdClass;
		$issue->name = 'This label is now even more insightful';
		$issue->color = 'This color is now even more insightful';

		$this->client->expects($this->once())
			->method('patch')
			->with('/repos/joomla/joomla-platform/labels/523', json_encode($issue))
			->will($this->returnValue($this->response));

		$this->object->editLabel('joomla', 'joomla-platform', 523, 'This label is now even more insightful', 'This color is now even more insightful');
	}
	 */

	/**
	 * Tests the get method
	 *
	 * @return void
	 */
	public function testGet()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/issues/523')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->get('joomla', 'joomla-platform', 523),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the get method - failure
	 *
	 * @expectedException  \DomainException
	 *
	 * @return void
	 */
	public function testGetFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/issues/523')
			->will($this->returnValue($this->response));

		$this->object->get('joomla', 'joomla-platform', 523);
	}

	/**
	 * Tests the getComment method
	 * @todo move
	 * @return void
	 *
	public function testGetComment()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/issues/comments/523')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getComment('joomla', 'joomla-platform', 523),
			$this->equalTo(json_decode($this->sampleString))
		);
	}
	 */

	/**
	 * Tests the getComment method - failure
	 * @todo move
	 * @expectedException  \DomainException
	 *
	 * @return void
	 *
	public function testGetCommentFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/issues/comments/523')
			->will($this->returnValue($this->response));

		$this->object->getComment('joomla', 'joomla-platform', 523);
	}
	 */

	/**
	 * Tests the getComments method
	 * @todo move
	 * @return void
	 *
	public function testGetComments()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/issues/523/comments')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getComments('joomla', 'joomla-platform', 523),
			$this->equalTo(json_decode($this->sampleString))
		);
	}
	 */

	/**
	 * Tests the getComments method - failure
	 * @todo move
	 * @expectedException  \DomainException
	 *
	 * @return void
	 *
	public function testGetCommentsFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/issues/523/comments')
			->will($this->returnValue($this->response));

		$this->object->getComments('joomla', 'joomla-platform', 523);
	}
	 */

	/**
	 * Tests the getLabel method
	 * @todo move
	 * @return void
	 *
	public function testGetLabel()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/labels/My Insightful Label')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getLabel('joomla', 'joomla-platform', 'My Insightful Label'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}
	 */

	/**
	 * Tests the getLabel method - failure
	 * @todo move
	 * @expectedException  \DomainException
	 *
	 * @return void
	 *
	public function testGetLabelFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/labels/My Insightful Label')
			->will($this->returnValue($this->response));

		$this->object->getLabel('joomla', 'joomla-platform', 'My Insightful Label');
	}
	 */

	/**
	 * Tests the getLabels method
	 * @todo move
	 * @return void
	 *
	public function testGetLabels()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/labels')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getLabels('joomla', 'joomla-platform'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}
	 */

	/**
	 * Tests the getLabels method - failure
	 * @todo move
	 * @expectedException  \DomainException
	 *
	 * @return void
	 *
	public function testGetLabelsFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/labels')
			->will($this->returnValue($this->response));

		$this->object->getLabels('joomla', 'joomla-platform');
	}
	 */

	/**
	 * Tests the getList method
	 *
	 * @return void
	 */
	public function testGetList()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/issues')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getList(),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getList method - failure
	 *
	 * @expectedException  \DomainException
	 *
	 * @return void
	 */
	public function testGetListFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/issues')
			->will($this->returnValue($this->response));

		$this->object->getList();
	}

	/**
	 * Tests the getListByRepository method
	 *
	 * @return void
	 */
	public function testGetListByRepository()
	{
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/issues')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getListByRepository('joomla', 'joomla-platform'),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getListByRepository method with all parameters
	 *
	 * @return void
	 */
	public function testGetListByRepositoryAll()
	{
		$date = new \DateTime('January 1, 2012 12:12:12', new \DateTimeZone('UTC'));
		$this->response->code = 200;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('get')
			->with(
				'/repos/joomla/joomla-platform/issues?milestone=25&state=closed&assignee=none&' .
				'mentioned=joomla-jenkins&labels=bug&sort=created&direction=asc&since=2012-01-01T12:12:12+00:00'
			)
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->getListByRepository(
				'joomla',
				'joomla-platform',
				'25',
				'closed',
				'none',
				'joomla-jenkins',
				'bug',
				'created',
				'asc',
				$date
			),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getListByRepository method - failure
	 *
	 * @expectedException  \DomainException
	 *
	 * @return void
	 */
	public function testGetListByRepositoryFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('get')
			->with('/repos/joomla/joomla-platform/issues')
			->will($this->returnValue($this->response));

		$this->object->getListByRepository('joomla', 'joomla-platform');
	}

	/**
	 * Tests the lock method
	 *
	 * @return void
	 */
	public function testLock()
	{
		$this->response->code = 204;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('put')
			->with('/repos/joomla/joomla-platform/issues/523/lock')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->lock('joomla', 'joomla-platform', 523),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the lock method - failure
	 *
	 * @expectedException  \DomainException
	 *
	 * @return void
	 */
	public function testLockFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('put')
			->with('/repos/joomla/joomla-platform/issues/523/lock')
			->will($this->returnValue($this->response));

		$this->object->lock('joomla', 'joomla-platform', 523);
	}

	/**
	 * Tests the unlock method
	 *
	 * @return void
	 */
	public function testUnlock()
	{
		$this->response->code = 204;
		$this->response->body = $this->sampleString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/repos/joomla/joomla-platform/issues/523/lock')
			->will($this->returnValue($this->response));

		$this->assertThat(
			$this->object->unlock('joomla', 'joomla-platform', 523),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the unlock method - failure
	 *
	 * @expectedException  \DomainException
	 *
	 * @return void
	 */
	public function testUnlockFailure()
	{
		$this->response->code = 500;
		$this->response->body = $this->errorString;

		$this->client->expects($this->once())
			->method('delete')
			->with('/repos/joomla/joomla-platform/issues/523/lock')
			->will($this->returnValue($this->response));

		$this->object->unlock('joomla', 'joomla-platform', 523);
	}
}
