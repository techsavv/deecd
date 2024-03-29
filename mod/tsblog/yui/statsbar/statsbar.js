YUI.add('moodle-mod_tsblog-statsbar', function(Y) {
    M.mod_tsblog = M.mod_tsblog || {};
    M.mod_tsblog.statsbar = {
            init: function(container_class) {
                var bars = Y.all('.' + container_class + ' .tsblog_statsinfo_bar span');
                if (bars) {
                    bars.each(
                            function(bar) {
                                var classes = bar.get('className');
                                if (classes.indexOf('percent') === 0) {
                                    var percent = parseInt(classes.substring(8), 10);
                                    var curwidth = parseInt(bar.get('offsetWidth'), 10);
                                    bar.addClass('tsblogbar');
                                    var maxwidth = parseInt(bar.get('parentNode').getStyle('width'), 10);
                                    var padding = parseInt(bar.getStyle('padding-left'), 10);
                                    if (!padding) {
                                        padding = 5;
                                    }
                                    maxwidth = maxwidth - padding;
                                    var newwidth = (maxwidth - curwidth) / 100 * percent;
                                    bar.setStyle('width', (curwidth + newwidth) + 'px');
                                }
                            }
                    );
                }
            }
        };
    }, '@VERSION@', {requires: ['node']}
);
