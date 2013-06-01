/*
 * ContextMenu - jQuery plugin for right-click context menus
 *
 * Author: Chris Domigan
 * Contributors: Dan G. Switzer, II
 * Parts of this plugin are inspired by Joern Zaefferer's Tooltip plugin
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * Version: r2
 * Date: 16 July 2007
 *
 * For documentation visit http://www.trendskitchens.co.nz/jquery/contextmenu/
 *
 */

(function($) {
    var menu, shadow, trigger, content, hash, currentTarget;
    var defaults = {
        menuStyle: {
            listStyle: 'none',
            padding: '5px 0px',
            margin: '0px',
            backgroundColor: '#FFFFFF',
            border: '1px solid #CCCCCC',
            width: '170px'
        },
        itemStyle: {
            margin: '0px',
            color: '#848485',
            display: 'block',
            cursor: 'pointer',
            padding: '8px',
            fontSize: '11px',
            backgroundColor: 'transparent'
        },
        itemHoverStyle: {
            backgroundColor: '#F3F3F3'
        },
        eventPosX: 'pageX',
        eventPosY: 'pageY',
        shadow : false,
        onContextMenu: null,
        onShowMenu: null
    };

    $.fn.contextMenu = function(id, options) {
        if (!menu) { // Create singleton menu
            menu = $('<div id="jqContextMenu"></div>')
                    .hide()
                    .css({position:'absolute', zIndex:'9999'})
                    .appendTo('body')
                    .bind('click', function(e) {
                        e.stopPropagation();
                    });
        }
        if (!shadow) {
            shadow = $('<div></div>')
                     .css({backgroundColor:'#000',position:'absolute',opacity:0.2,zIndex:499})
                     .appendTo('body')
                     .hide();
        }
        
        hash = hash || [];
        hash.push({
            id : id,
            menuStyle: $.extend({}, defaults.menuStyle, options.menuStyle || {}),
            itemStyle: $.extend({}, defaults.itemStyle, options.itemStyle || {}),
            itemHoverStyle: $.extend({}, defaults.itemHoverStyle, options.itemHoverStyle || {}),
            bindings: options.bindings || {},
            shadow: options.shadow || options.shadow === false ? options.shadow : defaults.shadow,
            onContextMenu: options.onContextMenu || defaults.onContextMenu,
            onShowMenu: options.onShowMenu || defaults.onShowMenu,
            eventPosX: options.eventPosX || defaults.eventPosX,
            eventPosY: options.eventPosY || defaults.eventPosY
        });

        var index = hash.length - 1;
        $(this).bind('click', function(e) {
            // Check if onContextMenu() defined
            var bShowContext = (!!hash[index].onContextMenu) ? hash[index].onContextMenu(e) : true;
            if (bShowContext) display(index, this, e, options);
            return false;
        });
        return this;
    };

    function display(index, trigger, e, options) {
        var cur = hash[index];
        content = $('#'+cur.id).find('ul:first').clone(true);
        content.css(cur.menuStyle).find('li').css(cur.itemStyle).hover(
            function() {
                $(this).css(cur.itemHoverStyle);
            },
            function(){
                $(this).css(cur.itemStyle);
            }).find('img').css({verticalAlign:'middle',paddingRight:'2px'});

        // Send the content to the menu
        menu.html("");
        menu.html(content);

        // if there's an onShowMenu, run it now -- must run after content has been added
        // if you try to alter the content variable before the menu.html(), IE6 has issues
        // updating the content
        if (!!cur.onShowMenu) menu = cur.onShowMenu(e, menu);

        $.each(cur.bindings, function(id, func) {
            $('#'+id, menu).bind('click', function(e) {
                hide();
                func(trigger, currentTarget);
            });
        });
        var xa = e[cur.eventPosY];
        var aux = $("body").css('height').split("px");
        aux = aux[0] - xa;
        if(xa > 360){
            menu.css({'left':e[cur.eventPosX],'bottom':aux, 'top':'auto'}).show();
        }else{
            menu.css({'left':e[cur.eventPosX],'top':e[cur.eventPosY],'bottom':'auto'}).show();
        }
        if (cur.shadow) shadow.css({width:menu.width(),height:menu.height(),left:e.pageX+2,top:e.pageY+2}).show();
        $(document).one('click', hide);
    }

    function hide() {
        menu.hide();
        shadow.hide();
    }

    // Apply defaults
    $.contextMenu = {
        defaults : function(userDefaults) {
            $.each(userDefaults, function(i, val) {
                if (typeof val == 'object' && defaults[i]) {
                    $.extend(defaults[i], val);
                }else defaults[i] = val;
            });
        }
    };
})(jQuery);
$(function() {
    $('div.contextMenu').hide();
});
