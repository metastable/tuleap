<?php
/**
 * Copyright (c) Enalean, 2020 - present. All Rights Reserved.
 *
 *  This file is a part of Tuleap.
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
 *
 */

declare(strict_types=1);

namespace Tuleap\Tracker\Creation\JiraImporter\Import;

use Mockery;
use Tracker_FormElement_Field_ArtifactId;
use Tracker_FormElement_Field_String;
use Tuleap\Tracker\Creation\JiraImporter\Import\Artifact\ArtifactsXMLExporter;
use Tuleap\Tracker\Creation\JiraImporter\Import\Permissions\PermissionsXMLExporter;
use Tuleap\Tracker\Creation\JiraImporter\Import\Reports\XmlReportExporter;
use Tuleap\Tracker\Creation\JiraImporter\Import\Semantic\SemanticsXMLExporter;
use Tuleap\Tracker\Creation\JiraImporter\Import\Structure\FieldMappingCollection;
use Tuleap\Tracker\Creation\JiraImporter\Import\Structure\FieldXmlExporter;
use Tuleap\Tracker\Creation\JiraImporter\Import\Structure\JiraFieldRetriever;
use Tuleap\Tracker\Creation\JiraImporter\Import\Structure\JiraToTuleapFieldTypeMapper;

final class JiraXmlExporterTest extends \PHPUnit\Framework\TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|XmlReportExporter
     */
    private $report_exporter;

    /**
     * @var JiraXmlExporter
     */
    private $jira_exporter;
    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|JiraToTuleapFieldTypeMapper
     */
    private $field_type_mapper;
    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|JiraFieldRetriever
     */
    private $jira_field_retriever;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|FieldXmlExporter
     */
    private $field_xml_exporter;

    /**
     * @var FieldMappingCollection
     */
    private $jira_field_mapping_collection;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|PermissionsXMLExporter
     */
    private $permissions_xml_exporter;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|ArtifactsXMLExporter
     */
    private $artifacts_xml_exporter;

    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|SemanticsXMLExporter
     */
    private $semantics_xml_exporter;

    protected function setUp(): void
    {
        $this->field_xml_exporter            = Mockery::mock(FieldXmlExporter::class);
        $this->jira_field_retriever          = Mockery::mock(JiraFieldRetriever::class);
        $error_collector                     = new ErrorCollector();
        $this->field_type_mapper             = Mockery::mock(JiraToTuleapFieldTypeMapper::class);
        $this->report_exporter               = Mockery::mock(XmlReportExporter::class);
        $this->jira_field_mapping_collection = new FieldMappingCollection();
        $this->permissions_xml_exporter      = Mockery::mock(PermissionsXMLExporter::class);
        $this->artifacts_xml_exporter        = Mockery::mock(ArtifactsXMLExporter::class);
        $this->semantics_xml_exporter        = Mockery::mock(SemanticsXMLExporter::class);

        $this->jira_exporter = new JiraXmlExporter(
            $this->field_xml_exporter,
            $error_collector,
            $this->jira_field_retriever,
            $this->field_type_mapper,
            $this->report_exporter,
            $this->jira_field_mapping_collection,
            $this->permissions_xml_exporter,
            $this->artifacts_xml_exporter,
            $this->semantics_xml_exporter
        );
    }

    public function testItProcessExport(): void
    {
        $xml          = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><project />');
        $trackers_xml = $xml->addChild('trackers');

        $fieldset_xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><formElement type="fieldset"/>');
        $this->field_xml_exporter->shouldReceive('exportFieldsetWithName')->andReturn($fieldset_xml)->times(2);
        $this->field_xml_exporter->shouldReceive('exportField')->withArgs(
            [
                $fieldset_xml,
                Tracker_FormElement_Field_ArtifactId::TYPE,
                Mockery::type('string'),
                Mockery::type('string'),
                Mockery::type('string'),
                1,
                Mockery::type('bool'),
                $this->jira_field_mapping_collection
            ]
        )->once();

        $this->field_xml_exporter->shouldReceive('exportField')->withArgs(
            [
                $fieldset_xml,
                Tracker_FormElement_Field_String::TYPE,
                Mockery::type('string'),
                Mockery::type('string'),
                Mockery::type('string'),
                2,
                Mockery::type('bool'),
                $this->jira_field_mapping_collection
            ]
        )->once();

        $this->field_xml_exporter->shouldReceive('exportField')->withArgs(
            [
                $fieldset_xml,
                \Tracker_FormElement_Field_LastUpdateDate::TYPE,
                Mockery::type('string'),
                Mockery::type('string'),
                Mockery::type('string'),
                5,
                Mockery::type('bool'),
                $this->jira_field_mapping_collection
            ]
        )->once();

        $this->report_exporter->shouldReceive('exportReports')->once();
        $this->permissions_xml_exporter->shouldReceive('exportFieldsPermissions')->once();
        $this->artifacts_xml_exporter->shouldReceive('exportArtifacts')->once();
        $this->semantics_xml_exporter->shouldReceive('exportSemantics')->once();

        $this->jira_field_retriever->shouldReceive('getAllJiraFields')->once();
        $this->jira_exporter->exportJiraToXml($trackers_xml, "URLinstance", "TEST", "Story");
    }
}
