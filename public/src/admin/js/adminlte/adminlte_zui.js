/*!
 * ZUI: Standard edition - v1.9.0 - 2019-03-04
 * http://zui.sexy
 * GitHub: https://github.com/easysoft/zui.git 
 * Copyright (c) 2019 cnezsoft.com; Licensed MIT
 */

/*! Some code copy from Bootstrap v3.0.0 by @fat and @mdo. (Copyright 2013 Twitter, Inc. Licensed under http://www.apache.org/licenses/)*/

/* ========================================================================
 * ZUI: jquery.extensions.js
 * http://zui.sexy
 * ========================================================================
 * Copyright (c) 2014-2016 cnezsoft.com; Licensed MIT
 * ======================================================================== */
(function($, window, undefined) {
    'use strict';

    /* Check jquery */
    if(typeof($) === 'undefined') throw new Error('ZUI requires jQuery');

    // ZUI shared object
    if(!$.zui) $.zui = function(obj) {
        if($.isPlainObject(obj)) {
            $.extend($.zui, obj);
        }
    };

    var MOUSE_BUTTON_CODES = {
        all: -1,
        left: 0,
        middle: 1,
        right: 2
    };

    var lastUuidAmend = 0;
    $.zui({
        uuid: function(asNumber) {
            var uuidNumber = (new Date()).getTime() * 1000 + (lastUuidAmend++) % 1000;
            return asNumber ? uuidNumber : uuidNumber.toString(36);
        },

        callEvent: function(func, event, proxy) {
            if($.isFunction(func)) {
                if(proxy !== undefined) {
                    func = $.proxy(func, proxy);
                }
                var result = func(event);
                if(event) event.result = result;
                return !(result !== undefined && (!result));
            }
            return 1;
        },

        clientLang: function() {
            var lang;
            var config = window.config;
            if(typeof(config) != 'undefined' && config.clientLang) {
                lang = config.clientLang;
            }
            if(!lang) {
                var hl = $('html').attr('lang');
                lang = hl ? hl : (navigator.userLanguage || navigator.userLanguage || 'zh_cn');
            }
            return lang.replace('-', '_').toLowerCase();
        },

        strCode: function(str) {
            var code = 0;
            if(str && str.length) {
                for(var i = 0; i < str.length; ++i) {
                    code += i * str.charCodeAt(i);
                }
            }
            return code;
        },

        getMouseButtonCode: function(mouseButton) {
            if(typeof mouseButton !== 'number') {
                mouseButton = MOUSE_BUTTON_CODES[mouseButton];
            }
            if(mouseButton === undefined || mouseButton === null) mouseButton = -1;
            return mouseButton;
        }
    });

    $.fn.callEvent = function(name, event, model) {
        var $this = $(this);
        var dotIndex = name.indexOf('.zui.');
        var shortName = dotIndex < 0 ? name : name.substring(0, dotIndex);
        var e = $.Event(shortName, event);

        if((model === undefined) && dotIndex > 0) {
            model = $this.data(name.substring(dotIndex + 1));
        }

        if(model && model.options) {
            var func = model.options[shortName];
            if($.isFunction(func)) {
                e.result = $.zui.callEvent(func, e, model);
            }
        }
        $this.trigger(e);
        return e;
    };

    $.fn.callComEvent = function(component, eventName, params) {
        if (params !== undefined && !$.isArray(params)) {
            params = [params];
        }
        var $this = this;
        var result = $this.triggerHandler(eventName, params);

        var eventCallback = component.options[eventName];
        if (eventCallback) {
            result = eventCallback.apply(component, params);
        }
        return result;
    };
}(jQuery, window, undefined));

/* ========================================================================
 * ZUI: typography.js
 * http://zui.sexy
 * ========================================================================
 * Copyright (c) 2014-2016 cnezsoft.com; Licensed MIT
 * ======================================================================== */
(function($) {
    'use strict';

    $.fn.fixOlPd = function(pd) {
        pd = pd || 10;
        return this.each(function() {
            var $ol = $(this);
            $ol.css('paddingLeft', Math.ceil(Math.log10($ol.children().length)) * pd + 10);
        });
    };

    $(function() {
        $('.ol-pd-fix,.article ol').fixOlPd();
    });
}(jQuery));
/* ========================================================================
 * ZUI: string.js
 * String Polyfill.
 * http://zui.sexy
 * ========================================================================
 * Copyright (c) 2014-2016 cnezsoft.com; Licensed MIT
 * ======================================================================== */


(function() {
    'use strict';

    /**
     * Format string with argument list or object
     * @param  {object | arguments} args
     * @return {String}
     */
    if(!String.prototype.format) {
        String.prototype.format = function(args) {
            var result = this;
            if(arguments.length > 0) {
                var reg;
                if(arguments.length <= 2 && typeof(args) == 'object') {
                    for(var key in args) {
                        if(args[key] !== undefined) {
                            reg = new RegExp('(' + (arguments[1] ? arguments[1].replace('0', key) : '{' + key + '}') + ')', 'g');
                            result = result.replace(reg, args[key]);
                        }
                    }
                } else {
                    for(var i = 0; i < arguments.length; i++) {
                        if(arguments[i] !== undefined) {
                            reg = new RegExp('({[' + i + ']})', 'g');
                            result = result.replace(reg, arguments[i]);
                        }
                    }
                }
            }
            return result;
        };
    }

    /**
     * Judge the string is a integer number
     *
     * @access public
     * @return bool
     */
    if(!String.prototype.isNum) {
        String.prototype.isNum = function(s) {
            if(s !== null) {
                var r, re;
                re = /\d*/i;
                r = s.match(re);
                return(r == s) ? true : false;
            }
            return false;
        };
    }

    if(!String.prototype.endsWith) {
        String.prototype.endsWith = function(searchString, position) {
            var subjectString = this.toString();
            if(position === undefined || position > subjectString.length) {
                position = subjectString.length;
            }
            position -= searchString.length;
            var lastIndex = subjectString.indexOf(searchString, position);
            return lastIndex !== -1 && lastIndex === position;
        };
    }

    if(!String.prototype.startsWith) {
        String.prototype.startsWith = function(searchString, position) {
            position = position || 0;
            return this.lastIndexOf(searchString, position) === position;
        };
    }

    if(!String.prototype.includes) {
        String.prototype.includes = function() {
            return String.prototype.indexOf.apply(this, arguments) !== -1;
        };
    }

})();

/* ========================================================================
 * ZUI: droppable.js
 * http://zui.sexy
 * ========================================================================
 * Copyright (c) 2014-2016 cnezsoft.com; Licensed MIT
 * ======================================================================== */


(function($, document, Math) {
    'use strict';

    var NAME     = 'zui.droppable',
        DEFAULTS = {
        // container: '',
        // selector: '',
        // handle: '',
        // flex: false,
        // nested: false,
        target: '.droppable-target',
        deviation: 5,
        sensorOffsetX: 0,
        sensorOffsetY: 0,
        dropToClass: 'drop-to',
         // mouseButton: -1 // 0, 1, 2, -1, all, left,  right, middle
    };
    var idIncrementer = 0;

    var Droppable = function(element, options) {
        var that     = this;
        that.id      = idIncrementer++;
        that.$       = $(element);
        that.options = $.extend({}, DEFAULTS, that.$.data(), options);
        that.init();
    };

    Droppable.DEFAULTS = DEFAULTS;
    Droppable.NAME     = NAME;

    Droppable.prototype.trigger = function(name, params) {
        return $.zui.callEvent(this.options[name], params, this);
    };

    Droppable.prototype.init = function() {
        var that           = this,
            $root          = that.$,
            setting        = that.options,
            deviation      = setting.deviation,
            eventSuffix    = '.' + NAME + '.' + that.id,
            mouseDownEvent = 'mousedown' + eventSuffix,
            mouseUpEvent   = 'mouseup' + eventSuffix,
            mouseMoveEvent = 'mousemove' + eventSuffix,
            selector       = setting.selector,
            handle         = setting.handle,
            flex           = setting.flex,
            container      = setting.container,
            canMoveHere    = setting.canMoveHere,
            dropToClass    = setting.dropToClass,
            $ele           = $root,
            isMouseDown    = false,
            $container     = container ? $(setting.container).first() : (selector ? $root : $('body')),
            $targets,
            $target,
            $shadow,
            isIn,
            isSelf,
            oldCssPosition,
            startOffset,
            startMouseOffset,
            containerOffset,
            clickOffset,
            mouseOffset,
            lastMouseOffset,
            mouseDownBackEventCall;

        var mouseMove = function(event) {
            if(!isMouseDown) return;

            mouseOffset = {left: event.pageX, top: event.pageY};

            // ignore small move
            if(Math.abs(mouseOffset.left - startMouseOffset.left) < deviation && Math.abs(mouseOffset.top - startMouseOffset.top) < deviation) return;

            if($shadow === null) // create shadow
            {
                var cssPosition = $container.css('position');
                if(cssPosition != 'absolute' && cssPosition != 'relative' && cssPosition != 'fixed') {
                    oldCssPosition = cssPosition;
                    $container.css('position', 'relative');
                }

                $shadow = $ele.clone().removeClass('drag-from').addClass('drag-shadow').css({
                    position:   'absolute',
                    width:      $ele.outerWidth(),
                    transition: 'none'
                }).appendTo($container);
                $ele.addClass('dragging');

                that.trigger('start', {
                    event:   event,
                    element: $ele,
                    targets: $targets
                });
            }

            var offset = {
                left: mouseOffset.left - clickOffset.left,
                top:  mouseOffset.top - clickOffset.top
            };
            var position = {
                left: offset.left - containerOffset.left,
                top:  offset.top - containerOffset.top
            };
            $shadow.css(position);
            $.extend(lastMouseOffset, mouseOffset);

            var isNew = false;
                isIn = false;

            if(!flex) {
                $targets.removeClass(dropToClass);
            }

            var $newTarget = null;
            $targets.each(function() {
                var t    = $(this),
                    tPos = t.offset(),
                    tW   = t.outerWidth(),
                    tH   = t.outerHeight(),
                    tX   = tPos.left + setting.sensorOffsetX,
                    tY   = tPos.top + setting.sensorOffsetY;

                if(mouseOffset.left > tX && mouseOffset.top > tY && mouseOffset.left < (tX + tW) && mouseOffset.top < (tY + tH)) {
                    if($newTarget) $newTarget.removeClass(dropToClass);
                    $newTarget = t;
                    if(!setting.nested) return false;
                }
            });

            if($newTarget) {
                isIn = true;
                var id = $newTarget.data('id');
                if($ele.data('id') != id) isSelf = false;
                if($target === null || ($target.data('id') !== id && (!isSelf))) isNew = true;
                $target = $newTarget;
                if(flex) {
                    $targets.removeClass(dropToClass);
                }
                $target.addClass(dropToClass);
            }


            if(!flex) {
                $ele.toggleClass('drop-in', isIn);
                $shadow.toggleClass('drop-in', isIn);
            } else if($target !== null && $target.length) {
                isIn = true;
            }

            if(!canMoveHere || canMoveHere($ele, $target) !== false) {
                that.trigger('drag', {
                    event: event,
                    isIn: isIn,
                    target: $target,
                    element: $ele,
                    isNew: isNew,
                    selfTarget: isSelf,
                    clickOffset: clickOffset,
                    offset: offset,
                    position: {
                        left: offset.left - containerOffset.left,
                        top: offset.top - containerOffset.top
                    },
                    mouseOffset: mouseOffset
                });
            }

            event.preventDefault();
        };

        var mouseUp = function(event) {
            $(document).off(eventSuffix);
            clearTimeout(mouseDownBackEventCall);
            if(!isMouseDown) return;

            isMouseDown = false;

            if(oldCssPosition) {
                $container.css('position', oldCssPosition);
            }

            if($shadow === null) {
                $ele.removeClass('drag-from');
                that.trigger('always', {
                    event: event,
                    cancel: true
                });
                return;
            }

            if(!isIn) $target = null;
            var isSure = true;
            mouseOffset = event ? {
                left: event.pageX,
                top: event.pageY
            } : lastMouseOffset;
            var offset = {
                left: mouseOffset.left - clickOffset.left,
                top: mouseOffset.top - clickOffset.top
            };
            var moveOffset = {
                left: mouseOffset.left - lastMouseOffset.left,
                top: mouseOffset.top - lastMouseOffset.top
            };
            lastMouseOffset.left = mouseOffset.left;
            lastMouseOffset.top = mouseOffset.top;
            var eventOptions = {
                event: event,
                isIn: isIn,
                target: $target,
                element: $ele,
                isNew: (!isSelf) && $target !== null,
                selfTarget: isSelf,
                offset: offset,
                mouseOffset: mouseOffset,
                position: {
                    left: offset.left - containerOffset.left,
                    top: offset.top - containerOffset.top
                },
                lastMouseOffset: lastMouseOffset,
                moveOffset: moveOffset
            };

            isSure = that.trigger('beforeDrop', eventOptions);

            if(isSure && isIn) {
                that.trigger('drop', eventOptions);
            }

            $targets.removeClass(dropToClass);
            $ele.removeClass('dragging').removeClass('drag-from');
            $shadow.remove();
            $shadow = null;

            that.trigger('finish', eventOptions);
            that.trigger('always', eventOptions);

            if(event) event.preventDefault();
        };

        var mouseDown = function(event) {
            var mouseButton = $.zui.getMouseButtonCode(setting.mouseButton);
            if(mouseButton > -1 && event.button !== mouseButton) {
                return;
            }

            var $mouseDownEle = $(this);
            if(selector) {
                $ele = handle ? $mouseDownEle.closest(selector) : $mouseDownEle;
            }

            if($ele.hasClass('drag-shadow')) {
                return;
            }

            if(setting['before']) {
                if(setting['before']({
                    event: event,
                    element: $ele
                }) === false) return;
            }

            isMouseDown = true;
            $targets         = $.isFunction(setting.target) ? setting.target($ele, $root) : $container.find(setting.target),
            $target          = null,
            $shadow          = null,
            isIn             = false,
            isSelf           = true,
            oldCssPosition   = null,
            startOffset      = $ele.offset(),
            containerOffset  = $container.offset();
            containerOffset.top = containerOffset.top - $container.scrollTop();
            containerOffset.left = containerOffset.left - $container.scrollLeft();
            startMouseOffset = {left: event.pageX, top: event.pageY};
            lastMouseOffset  = $.extend({}, startMouseOffset);
            clickOffset      = {
                left: startMouseOffset.left - startOffset.left,
                top: startMouseOffset.top - startOffset.top
            };

            $ele.addClass('drag-from');
            $(document).on(mouseMoveEvent, mouseMove).on(mouseUpEvent, mouseUp);
            mouseDownBackEventCall = setTimeout(function() {
                $(document).on(mouseDownEvent, mouseUp);
            }, 10);
            event.preventDefault();
            if(setting.stopPropagation) {
                event.stopPropagation();
            }
        };

        if(handle) {
            $root.on(mouseDownEvent, handle, mouseDown);
        } else if(selector) {
            $root.on(mouseDownEvent, selector, mouseDown);
        } else {
            $root.on(mouseDownEvent, mouseDown);
        }
    };

    Droppable.prototype.destroy = function() {
        var eventSuffix = '.' + NAME + '.' + this.id;
        this.$.off(eventSuffix);
        $(document).off(eventSuffix);
        this.$.data(NAME, null);
    };

    Droppable.prototype.reset = function() {
        this.destroy();
        this.init();
    };

    $.fn.droppable = function(option) {
        return this.each(function() {
            var $this = $(this);
            var data = $this.data(NAME);
            var options = typeof option == 'object' && option;

            if(!data) $this.data(NAME, (data = new Droppable(this, options)));

            if(typeof option == 'string') data[option]();
        });
    };

    $.fn.droppable.Constructor = Droppable;
}(jQuery, document, Math));


/* ========================================================================
 * ZUI: messager.js
 * http://zui.sexy
 * ========================================================================
 * Copyright (c) 2014-2016 cnezsoft.com; Licensed MIT
 * ======================================================================== */


(function($, window, undefined) {
    'use strict';

    var id = 0;
    var template = '<div class="messager messager-{type} {placement}" style="display: none"><div class="messager-content"></div><div class="messager-actions"></div></div>';
    var DEFAULTS = {
        type: 'default',
        placement: 'top',
        time: 4000,
        parent: 'body',
        // clear: false,
        icon: null,
        close: true,
        // actions: [{icon, name, action, title}],
        // contentClass: null,
        // cssClass: null,
        // onAction: function,
        fade: true,
        scale: true
    };
    var all = {};

    var Messager = function(message, options) {
        if($.isPlainObject(message)) {
            options = message;
            message = options.message;
        }

        var that = this;
        options = that.options = $.extend({}, DEFAULTS, options);

        that.id = options.id || (id++);
        var oldMessager = all[that.id];
        if(oldMessager) oldMessager.destroy();
        all[that.id] = that;
        that.message = (options.icon ? '<i class="icon-' + options.icon + ' icon"></i> ' : '') + message;

        that.$ = $(template.format(options)).toggleClass('fade', options.fade).toggleClass('scale', options.scale).attr('id', 'messager-' + that.id);

        if(options.cssClass) that.$.addClass(options.cssClass);

        var hasCloseAction = false;
        var $actions = that.$.find('.messager-actions');
        var appendAction = function(action) {
            var $btn = $('<button type="button" class="action action-' + action.name + '"/>');
            if(action.name === 'close') $btn.addClass('close');
            if(action.html !== undefined) {
                $btn.html(action.html);
            }
            if(action.icon !== undefined) {
                $btn.append('<i class="action-icon icon-' + action.icon + '"/>');
            }
            if(action.text !== undefined) {
                $btn.append('<span class="action-text">' + action.text + '</span>');
            }
            if(action.tooltip !== undefined) {
                $btn.attr('title', action.tooltip).tooltip();
            }
            $btn.data('action', action);
            $actions.append($btn);
        };
        if(options.actions) {
            $.each(options.actions, function(idx, action) {
                if(action.name === undefined) action.name = idx;
                if(action.name == 'close') hasCloseAction = true;
                appendAction(action);
            });
        }
        if(!hasCloseAction && options.close) {
            appendAction({name: 'close', html: '&times;'});
        }

        that.$.on('click', '.action', function(e) {
            var action = $(this).data('action'), result;
            if(options.onAction) {
                result = options.onAction.call(this, action.name, action, that);
                if(result === false) return;
            }
            if($.isFunction(action.action)) {
                result = action.action.call(this, that);
                if(result === false) return;
            }
            that.hide();
            e.stopPropagation();
        });

        that.$.on('click', function(e) {
            if(options.onAction) {
                var result = options.onAction.call(this, 'content', null, that);
                if(result === true) that.hide();
            }
        });

        var $content = that.$.find('.messager-content').html(that.message);
        if(options.contentClass) $content.addClass(options.contentClass);

        that.$.data('zui.messager', that);

        if(options.show && that.message !== undefined) {
            that.show();
        }
    };

    Messager.prototype.update = function(message, newOptions) {
        var that = this;
        var options = that.options;
        that.$.removeClass('messager-' + options.type);
        if(newOptions) {
            options = $.extend(options, newOptions);
        }
        that.$.addClass('messager-' + options.type);
        if(message) {
            that.message = (options.icon ? '<i class="icon-' + options.icon + ' icon"></i> ' : '') + message;
            that.$.find('.messager-content').html(that.message);
        }
    };

    Messager.prototype.show = function(message, callback) {
        var that = this,
            options = this.options;

        if($.isFunction(message)) {
            var oldCallback = callback;
            callback = message;
            if(oldCallback !== undefined) {
                message = oldCallback;
            }
        }

        if(that.isShow) {
            that.hide(function() {
                that.show(message, callback);
            });
            return;
        }

        if(that.hiding) {
            clearTimeout(that.hiding);
            that.hiding = null;
        }

        that.update(message);

        var placement = options.placement;
        var $parent = $(options.parent);
        var $holder = $parent.children('.messagers-holder.' + placement);
        if(!$holder.length) {
            $holder = $('<div/>').attr('class', 'messagers-holder ' + placement).appendTo($parent);
        }
        $holder.append(that.$);
        if(placement === 'center') {
            var offset = $(window).height() - $holder.height();
            $holder.css('top', Math.max(-offset, offset/2));
        }

        that.$.show().addClass('in');

        if(options.time) {
            that.hiding = setTimeout(function() {
                that.hide();
            }, options.time);
        }

        that.isShow = true;
        callback && callback();
        return that;
    };

    Messager.prototype.hide = function(callback, immediately) {
        if(callback === true) {
            immediately = true;
            callback = null;
        }
        var that = this;
        if(that.$.hasClass('in')) {
            that.$.removeClass('in');
            var removeMessager = function() {
                var $parent = that.$.parent();
                that.$.detach();
                if(!$parent.children().length) $parent.remove();
                callback && callback(true);
            };
            if(immediately) removeMessager();
            else setTimeout(removeMessager, 200);
        } else {
            callback && callback(false);
        }

        that.isShow = false;
    };

    Messager.prototype.destroy = function() {
        var that = this;
        that.hide(function()
        {
            that.$.remove();
            that.$ = null;
        }, true);
        delete all[that.id];
    };

    Messager.all = all;
    Messager.DEFAULTS = DEFAULTS;

    var hideMessage = function() {
        $('.messager').each(function() {
            var msg = $(this).data('zui.messager');
            if(msg && msg.hide) msg.hide(true);
        });
    };

    var showMessage = function(message, options) {
        if(typeof options === 'string') {
            options = {
                type: options
            };
        }
        options = $.extend({}, options);
        if(options.id === undefined) hideMessage();
        var msg = all[options.id] || new Messager(message, options);
        msg.show();
        return msg;
    };

    var getOptions = function(options) {
        return(typeof options === 'string') ? {
            placement: options
        } : options;
    };

    var zuiMessager = {
        show: showMessage,
        hide: hideMessage
    };

    $.each({
        primary  : 0,
        success  : 'ok-sign',
        info     : 'info-sign',
        warning  : 'warning-sign',
        danger   : 'exclamation-sign',
        important: 0,
        special  : 0
    }, function(name, icon){
        zuiMessager[name] = function(message, options) {
            return showMessage(message, $.extend({
                type: name,
                icon: icon || null
            }, getOptions(options)));
        };
    });

    $.zui({
        Messager: Messager,
        showMessager: showMessage,
        messager: zuiMessager
    });
}(jQuery, window, undefined));