/*
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
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

import { getStatusFromMapping, getStatusMetadata } from "./hardcoded-metadata-mapping-helper.js";

export function transformFolderMetadataForRecursionAtUpdate(item) {
    let folder_to_update = JSON.parse(JSON.stringify(item));

    const metadata = getStatusMetadata(folder_to_update.metadata);
    folder_to_update.status = {
        value: !metadata.list_value[0] ? "none" : getStatusFromMapping(metadata.list_value[0].id),
        recursion: "none"
    };

    return folder_to_update;
}

export function transformItemMetadataForCreation(
    document_to_create,
    parent,
    is_item_status_metadata_used
) {
    if (!is_item_status_metadata_used) {
        return;
    }

    const metadata = getStatusMetadata(parent.metadata);
    updateItemMetadata(metadata, document_to_create);
}

export function transformDocumentMetadataForUpdate(
    document_to_update,
    is_item_status_metadata_used
) {
    if (!is_item_status_metadata_used) {
        return;
    }

    const metadata = getStatusMetadata(document_to_update.metadata);
    updateItemMetadata(metadata, document_to_update);
}

function updateItemMetadata(metadata, item) {
    let status = "none";
    if (metadata.list_value[0].id) {
        status = getStatusFromMapping(metadata.list_value[0].id);
    }

    item.status = status;
}

export function transformCustomMetadataForItemCreation(parent_metadata) {
    if (parent_metadata.length === 0) {
        return [];
    }

    let formatted_metadata_list = [];
    parent_metadata.forEach(parent_metadata => {
        let formatted_metadata = {
            short_name: parent_metadata.short_name,
            type: parent_metadata.type,
            name: parent_metadata.name,
            is_multiple_value_allowed: parent_metadata.is_multiple_value_allowed
        };

        switch (parent_metadata.type) {
            case "text":
            case "string":
                formatted_metadata.value = parent_metadata.value;
                formatted_metadata_list.push(formatted_metadata);
                break;
            default:
                break;
        }
    });

    return formatted_metadata_list;
}
