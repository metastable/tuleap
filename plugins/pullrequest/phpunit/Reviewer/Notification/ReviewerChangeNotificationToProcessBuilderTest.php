<?php
/**
 * Copyright (c) Enalean, 2019-Present. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Tuleap\PullRequest\Reviewer\Notification;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PFUser;
use PHPUnit\Framework\TestCase;
use Tuleap\PullRequest\PullRequest;
use Tuleap\PullRequest\Reviewer\Change\ReviewerChange;
use Tuleap\PullRequest\Reviewer\Change\ReviewerChangeEvent;
use Tuleap\PullRequest\Reviewer\Change\ReviewerChangePullRequestAssociation;
use Tuleap\PullRequest\Reviewer\Change\ReviewerChangeRetriever;
use UserHelper;

final class ReviewerChangeNotificationToProcessBuilderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|ReviewerChangeRetriever
     */
    private $reviewer_change_retriever;
    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|UserHelper
     */
    private $user_helper;

    /**
     * @var ReviewerChangeNotificationToProcessBuilder
     */
    private $builder;

    protected function setUp(): void
    {
        $this->reviewer_change_retriever = \Mockery::mock(ReviewerChangeRetriever::class);
        $this->user_helper               = \Mockery::mock(UserHelper::class);

        $this->builder = new ReviewerChangeNotificationToProcessBuilder(
            $this->reviewer_change_retriever,
            $this->user_helper
        );
    }

    public function testBuildReviewerAddedNotificationFromReviewerChangeEvent(): void
    {
        $pull_request  = \Mockery::mock(PullRequest::class);
        $change_user   = \Mockery::mock(PFUser::class);
        $new_reviewers = [\Mockery::mock(PFUser::class), \Mockery::mock(PFUser::class)];

        $reviewer_change_event = ReviewerChangeEvent::fromID(147);

        $this->user_helper->shouldReceive('getDisplayNameFromUser')->andReturn('Display name');
        $this->reviewer_change_retriever->shouldReceive('getChangeWithTheAssociatedPullRequestByID')
            ->with($reviewer_change_event->getChangeID())
            ->andReturn(
                new ReviewerChangePullRequestAssociation(
                    new ReviewerChange(
                        new \DateTimeImmutable('@10'),
                        $change_user,
                        $new_reviewers,
                        []
                    ),
                    $pull_request
                )
            );

        $notifications = $this->builder->getNotificationsToProcess($reviewer_change_event);
        $this->assertCount(count($new_reviewers), $notifications);
    }

    public function testNoNotificationAreBuiltWhenTheCorrespondingChangeCannotBeFound(): void
    {
        $reviewer_change_event = ReviewerChangeEvent::fromID(404);
        $this->reviewer_change_retriever->shouldReceive('getChangeWithTheAssociatedPullRequestByID')
            ->with($reviewer_change_event->getChangeID())
            ->andReturn(null);

        $notifications = $this->builder->getNotificationsToProcess($reviewer_change_event);
        $this->assertEmpty($notifications);
    }
}