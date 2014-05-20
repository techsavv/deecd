YUI.add('moodle-mod_tsblog-accordion', function(Y) {
    M.mod_tsblog = M.mod_tsblog || {};
    M.mod_tsblog.accordion = M.mod_tsblog.accordion || {
            closetab : function(tab) {
                tab.title.set('tabindex', -1);
                tab.removeClass('tsblog-accordion-open');
                tab.addClass('tsblog-accordion-closed');
                tab.content.removeClass('tsblog-accordion-open');
                tab.content.addClass('tsblog-accordion-closed');
                tab.state = 0;
            },
            opentab : function(tab) {
                // Shut all other tabs.
                var tabs = tab.get('parentNode').all('li.tsblog-accordion-open');
                tabs.each(function(tabref){M.mod_tsblog.accordion.closetab(tabref);});

                // Now open this one.
                tab.title.set('tabindex', 0);
                tab.removeClass('tsblog-accordion-closed');
                tab.addClass('tsblog-accordion-open');
                tab.content.removeClass('tsblog-accordion-closed');
                tab.content.addClass('tsblog-accordion-open');
                tab.state = 1;
                if (!Y.one('body').hasClass('notloggedin')) {
                    M.util.set_user_preference('tsblog_accordion_' + tab.container_class + '_open', tab.number);
                }
            },
            // Init an 'accordion' style widget - ul with two divs in each li (title,content).
            init: function(container_class, default_open) {
                var container = Y.one('ul.tsblog-accordion-' + container_class);
                if (!container) {
                    return;
                }
                container.set('tabindex', 0);
                var counter = 1;
                // Setup UI.
                var tabs = container.all('li');
                if (!default_open || tabs.size() < default_open) {
                    default_open = 1;
                }
                tabs.each(
                        function(tab) {
                            tab.container_class = container_class;
                            tab.title = tab.one('.tsblog_statsview_title h2');
                            tab.content = tab.one('.tsblog_statsview_content');
                            tab.number = counter;
                            tab.title.setAttribute('aria-controls', tab.content.generateID());
                            tab.title.setAttribute('role', 'tab');

                            tab.content.setAttribute('aria-labeledby', tab.title.generateID());
                            tab.content.setAttribute('role', 'tabpanel');
                            tab.state = 1;
                            if (default_open != counter) {
                                M.mod_tsblog.accordion.closetab(tab);
                            } else {
                                // Manual open to stop others being closed.
                                tab.content.addClass('tsblog-accordion-open');
                                tab.addClass('tsblog-accordion-open');
                                tab.title.set('tabindex', 0);
                            }
                            tab.title.on(['click', 'keypress'], function(e) {
                                if (!e.keyCode || (e.keyCode && e.keyCode == 13)) {
                                    if (tab.state == 1) {
                                        M.mod_tsblog.accordion.closetab(tab);
                                    } else {
                                        M.mod_tsblog.accordion.opentab(tab);
                                    }
                                }
                                e.preventDefault();
                                e.stopPropagation();
                            });
                            counter ++;
                        }
                    );
                // Setup keyboard focus.
                container.plug(Y.Plugin.NodeFocusManager, {
                    descendants: 'li .tsblog_statsview_title h2',
                    keys: { next: 'down:40', previous: 'down:38' },
                    focusClass: {
                        className: 'focus', // The class name to use.
                        fn: function (node) {
                            // The Node instance to which the class should be applied.
                            return node.get('parentNode');
                        }
                    }
                });
            }
        };
    }, '@VERSION@', {requires: ['node', 'node-focusmanager']}
);
