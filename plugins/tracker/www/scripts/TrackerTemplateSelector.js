/**
 * 
 */

var codendi = codendi || { };
codendi.tracker = codendi.tracker || { };

codendi.tracker.TemplateSelector = Class.create({
    /**
     * Constructor
     * 
     * @param Element form The form that holds the selector
     */
    initialize: function (form) {
        this.form = form;
        this.observeProjects();
    },
    observeProjects: function () {
        $('tracker_new_project_list').observe('click', this.selectOneProject.bindAsEventListener(this));
    },
    selectOneProject: function (evt) {
        var target = evt.target;
        
        // Highlight selected project
        //this.selectedOneElement(evt, '.tracker_selected_project', 'group_id_template');
        
        // Ajax call
        var groupId = target.value;
        new Ajax.Updater($('tracker_list_trackers_from_project'), '/plugins/tracker/template_selector.php?func=plugin_tracker&target='+groupId, {
            onLoading: function () {
                /*var img = Builder.node('img', {
                    'src': "/themes/common/images/ic/spinner.gif",
                    'alt': 'Working...'});
                var span_img = Builder.node('span', {id: 'search_indicator'});
                span_img.appendChild(img);
                $('tracker_list_trackers_from_project').appendChild(img);*/
            },
            onSuccess: function (response) {
                /*$A(response.responseJSON).each(function (element) {
                    console.log(element.name+" "+element.id);
                }*/
            }.bind(this)
        });
        
        Event.stop(evt);
    },
    observeTrackers: function () {
        $$('.tracker_selector_tracker').each(function (link) {
            link.observe('click', this.selectOneTracker.bindAsEventListener(this));
        }.bind(this));
    },
    selectOneTracker: function (evt) {
        this.selectedOneElement(evt, '.tracker_selector_tracker', 'atid_template');
    },
    // Highlight
    selectedOneElement: function (evt, elementClass, formElement) {
        // Selected DOM element
        var target = evt.target;
        
        // Highlight selected element
        $$(elementClass).each(function (prj) {
            prj.removeClassName('highlight_list_element');
        });
        target.addClassName('highlight_list_element');
        
        // Store value in a hidden form element
        var hiddenStorage = $(formElement);
        if (!hiddenStorage) {
            hiddenStorage = Builder.node('input', {
                'type': 'hidden',
                'id': formElement});
            this.form.appendChild(hiddenStorage);
        }
        hiddenStorage.setValue(target.attributes['rel'].nodeValue);
        //console.log(hiddenStorage.getValue());
    }
});

document.observe('dom:loaded', function () {
    new codendi.tracker.TemplateSelector($('tracker_create_new'));
    
    /*var acc = new accordion('tracker_new_accordion', {
        classNames : {
            toggle: 'tracker_new_accordion_toggle',
            toggleActive: 'tracker_new_accordion_toggle_active',
            content: 'tracker_new_accordion_content'
        }
    });
    
    $$('.tracker_new_accordion_toggle').each(function(accordion) {
        $(accordion.next(0)).setStyle({
            height: '0px'
        });
    });

    acc.activate($$('#tracker_new_accordion .tracker_new_accordion_toggle')[0]);*/

});
