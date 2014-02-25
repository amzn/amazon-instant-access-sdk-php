<?php
/**
 * Copyright 2014 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */
namespace Amazon\InstantAccess\Utils;

/**
 * @internal
 *
 * Contains utility functions related to IO
 */
class IOUtils
{
    /**
     * Returns the file path based on the file handle
     *
     * @param string $fileHandle the file handle obtained from fopen, tmpfile, etc...
     *
     * @return string a string representation of the file path
     */
    public static function getFilePathFromHandle($fileHandle)
    {
        if (!$fileHandle) {
            throw new \InvalidArgumentException('Invalid file handle.');
        }

        $metadata = stream_get_meta_data($fileHandle);
        return $metadata['uri'];
    }
}
