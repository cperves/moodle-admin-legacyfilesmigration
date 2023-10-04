M.tool_legacyfilesmigration = {
    init_migrate_table: function(Y) {

        Y.use('node', function(Y) {
            checkboxes = Y.all('td.c0 input');
            checkboxes.each(function(node) {
                node.on('change', function(e) {
                    rowelement = e.currentTarget.get('parentNode').get('parentNode');
                    if (e.currentTarget.get('checked')) {
                        rowelement.setAttribute('class', 'selectedrow');
                    } else {
                        rowelement.setAttribute('class', 'unselectedrow');
                    }
                });

                rowelement = node.get('parentNode').get('parentNode');
                if (node.get('checked')) {
                    rowelement.setAttribute('class', 'selectedrow');
                } else {
                    rowelement.setAttribute('class', 'unselectedrow');
                }
            });
        });

        var selectall = Y.one('th.c0 input');
        selectall.on('change', function(e) {
            if (e.currentTarget.get('checked')) {
                checkboxes = Y.all('td.c0 input');
                checkboxes.each(function(node) {
                    rowelement = node.get('parentNode').get('parentNode');
                    node.set('checked', true);
                    rowelement.setAttribute('class', 'selectedrow');
                });
            } else {
                checkboxes = Y.all('td.c0 input');
                checkboxes.each(function(node) {
                    rowelement = node.get('parentNode').get('parentNode');
                    node.set('checked', false);
                    rowelement.setAttribute('class', 'unselectedrow');
                });
            }
        });
        var copycoursefilesall = Y.one('th.c3 input');
        copycoursefilesall.on('change', function(e) {
            if (e.currentTarget.get('checked')) {
                checkboxes = Y.all('td.c3 input');
                checkboxes.each(function(node) {
                    rowelement = node.get('parentNode').get('parentNode');
                    node.set('checked', true);
                });
            } else {
                checkboxes = Y.all('td.c3 input');
                checkboxes.each(function(node) {
                    rowelement = node.get('parentNode').get('parentNode');
                    node.set('checked', false);
                });
            }
        });
        var preselect_owner = Y.one('th.c4 input');
        preselect_owner.on('change', function(e){
            if(Y.one('td.c1').get('text').trim() == ''){
                alert(M.str.tool_legacyfilesmigration.coursidnotcollapse);
                return;
            }
            var emptyowner = Y.one('#empty_owner');
            emptyowner = emptyowner.get('value');
            // Select courses.
            var courses = Y.all('td.c1');
            courses.each(function(course){
                var idcourse = course.get('text');
                var firstradio = Y.one('#selectedowner_' + idcourse);
                var selectedownerusernameradio = Y.all('#selectedowner_' + idcourse).slice(-1).get(0)[0];
                if(emptyowner != ''){
                    vartextfield = Y.one('#selectedownerusername_' + idcourse);
                    if(vartextfield){
                        vartextfield.set('value',(e.currentTarget.get('checked') ? emptyowner : ''));
                        selectedownerusernameradio.set('checked', (e.currentTarget.get('checked') ? true : false));
                    }
                }else if(firstradio){
                    firstradio.set('checked', (e.currentTarget.get('checked') ? true : false))
                }
            });
        });
        var migrateselectedbutton = Y.one('#id_migrateselected');
        migrateselectedbutton.on('click', function(e) {
            checkboxes = Y.all('td.c0 input');
            oinputs = Y.all('td.c5 input'); // Owner inputs.
            var selectedcourses = [];
            var copycoursefiles = [];
            var selectedowners = {};
            checkboxes.each(function(node) {
                if (node.get('checked')) {
                    var nodevalue = node.get('value');
                    selectedcourses[selectedcourses.length] = nodevalue;
                    // Looking for associated cell.
                    // Copy coursefiles.
                    copycoursefilesentry = node.get('parentNode').get('parentNode').all('td.c3 input');
                    if(copycoursefilesentry.size() == 1 && copycoursefilesentry.get(0)[0].getAttribute('type') == 'checkbox'){
                        if(copycoursefilesentry.get(0)[0].get('checked')){
                            copycoursefiles[copycoursefiles.length] = nodevalue;
                        }
                    }
                    // Owners.
                    owners = node.get('parentNode').get('parentNode').all('td.c4 input');
                    if(owners.size() == 1 && owners.get(0)[0].getAttribute('type') == 'text'){
                        // Input text.
                        selectedowners[nodevalue] = owners.get(0)[0].get('value');
                    }else{
                        // Radio.
                        owners.each(function(rnode) {
                            if (rnode.get('checked')) {
                                if(rnode.get('value') == - 1){
                                    selectownerassociatedusername = Y.one('#' + rnode.get('id').replace("selectedowner", "selectedownerusername"));
                                    selectedowners[nodevalue] = selectownerassociatedusername.get('value');
                                }else{
                                    selectedowners[nodevalue] = rnode.get('value');
                                }
                            }
                        });
                    }
                }
            });
            operation = Y.one('#id_operation');
            coursesinput = Y.one('input.selectedcourses');
            coursesinput.set('value', selectedcourses.join(','));
            copycoursefilesinput = Y.one('input.copycoursefiles');
            copycoursefilesinput.set('value', copycoursefiles.join(','));
            ownersinput = Y.one('input.selectedowners');
            ownersinput.set('value', JSON.stringify(selectedowners));
            if (selectedcourses.length == 0) {
                alert(M.str.tool_legacyfilesmigration.nocoursesselected);
                e.preventDefault();
            }
        });

        var perpage = Y.one('#id_perpage');
        perpage.on('change', function(e) {
            window.onbeforeunload = null;
            Y.one('.tool_legacyfilesmigration_paginationform form').submit();
        });

    }
}
