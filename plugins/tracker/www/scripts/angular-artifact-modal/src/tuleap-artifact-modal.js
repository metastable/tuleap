import angular from 'angular';
import ngSanitize from 'angular-sanitize';

import 'imports-loader?CKEDITOR=>window.CKEDITOR!ng-ckeditor';
import 'angular-moment';
import 'angular-gettext';
import '../po/fr.po';

import fields from './tuleap-artifact-modal-fields/fields.js';
import model from './model/model.js';
import quota_display from './quota-display/quota-display.js';
import rest from './rest/rest.js';
import tuleap_highlight from './tuleap-highlight/highlight.js';
import angular_tlp from 'angular-tlp';

import FieldDependenciesService from './field-dependencies-service.js';
import FormatDateDirective from './format-date-directive.js';
import FormatDateTimeDirective from './format-datetime-directive.js';
import ParentService from './parent-service.js';
import ValidateService from './validate-service.js';
import ArtifactModalService from './tuleap-artifact-modal-service.js';
import ArtifactModalController from './tuleap-artifact-modal-controller.js';

angular.module('tuleap.artifact-modal', [
    'angularMoment',
    'ng.ckeditor',
    'gettext',
    angular_tlp,
    fields,
    model,
    ngSanitize,
    quota_display,
    rest,
    tuleap_highlight
])
.controller('TuleapArtifactModalController', ArtifactModalController)
.directive('tuleapArtifactModalFormatDate', FormatDateDirective)
.directive('tuleapArtifactModalFormatDatetime', FormatDateTimeDirective)
.value('TuleapArtifactModalLoading', {
    loading: false
})
.service('TuleapArtifactModalFieldDependenciesService', FieldDependenciesService)
.service('TuleapArtifactModalValidateService', ValidateService)
.service('TuleapArtifactModalParentService', ParentService)
.service('NewTuleapArtifactModalService', ArtifactModalService);

export default 'tuleap.artifact-modal';
