<?php
/**
 * Copyright (c) Enalean, 2019 - present. All Rights Reserved.
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
 * along with Tuleap. If not, see http://www.gnu.org/licenses/.
 *
 */

declare(strict_types=1);


namespace Tuleap\Docman\REST\v1\Files;

use Luracast\Restler\RestException;
use Tuleap\Docman\REST\v1\ExceptionItemIsLockedByAnotherUser;
use Tuleap\Docman\Upload\UploadCreationConflictException;
use Tuleap\Docman\Upload\UploadCreationFileMismatchException;
use Tuleap\Docman\Upload\UploadMaxSizeExceededException;
use Tuleap\Docman\Upload\Version\VersionToUploadCreator;

class DocmanFileVersionCreator
{
    /**
     * @var VersionToUploadCreator
     */
    private $creator;
    /**
     * @var \Docman_PermissionsManager
     */
    private $permissions_manager;

    public function __construct(VersionToUploadCreator $creator, \Docman_PermissionsManager $permissions_manager)
    {
        $this->creator = $creator;
        $this->permissions_manager = $permissions_manager;
    }

    /**
     * @throws RestException
     * @throws \Tuleap\Docman\REST\v1\ExceptionItemIsLockedByAnotherUser
     */
    public function createFileVersion(
        \Docman_Item $item,
        \PFUser $user,
        DocmanFileVersionPOSTRepresentation $representation,
        \DateTimeImmutable $current_time
    ): CreatedItemFilePropertiesRepresentation {

        if ($this->permissions_manager->_itemIsLockedForUser($user, (int)$item->getId())) {
            throw new ExceptionItemIsLockedByAnotherUser();
        }

        try {
            $document_to_upload = $this->creator->create(
                $item,
                $user,
                $current_time,
                $representation->version_title,
                $representation->change_log,
                $representation->file_properties->file_name,
                $representation->file_properties->file_size,
                $representation->should_lock_file,
                $item->getStatus(),
                $item->getObsolescenceDate(),
                $item->getTitle(),
                $item->getDescription(),
                $representation->approval_table_action
            );
        } catch (UploadCreationConflictException $exception) {
            throw new RestException(409, $exception->getMessage());
        } catch (UploadCreationFileMismatchException $exception) {
            throw new RestException(409, $exception->getMessage());
        } catch (UploadMaxSizeExceededException $exception) {
            throw new RestException(400, $exception->getMessage());
        }

        $file_properties_representation = new CreatedItemFilePropertiesRepresentation();
        $file_properties_representation->build($document_to_upload->getUploadHref());

        return $file_properties_representation;
    }
}